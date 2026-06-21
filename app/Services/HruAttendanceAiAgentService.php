<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HruAttendanceAiAgentService
{
    public function enhance(array $analysis, ?string $question = null): array
    {
        $analysis['agent'] = [
            'mode' => 'rules',
            'model' => null,
            'provider' => null,
            'used_external_ai' => false,
        ];
        $analysis['answer_mode'] = $this->inferAnswerMode($question);
        $analysis['ai_answer'] = $this->localAnswer($analysis['answer_mode'], $question);

        if (in_array($analysis['answer_mode'], ['system', 'unsupported'], true)) {
            $analysis['agent']['mode'] = 'local';
            $analysis['agent']['provider'] = 'system_context';
            $analysis['confidence'] = 'High';

            return $analysis;
        }

        if (!$this->isEnabled()) {
            $analysis['agent']['reason'] = 'AI assistant is disabled or provider API key is missing.';
            return $analysis;
        }

        try {
            $provider = 'groq';
            $ai = $this->callGroq($analysis, $question);

            if (!$ai) {
                $analysis['agent']['reason'] = strtoupper($provider) . ' returned an empty or invalid response.';
                return $analysis;
            }

            $analysis['summary']['overview'] = $ai['summary'] ?? $analysis['summary']['overview'];
            $analysis['risk_assessment']['reason'] = $ai['risk_reason'] ?? $analysis['risk_assessment']['reason'];
            $analysis['recommendations'] = $ai['recommendations'] ?? $analysis['recommendations'];
            $analysis['ai_answer'] = $ai['answer'] ?? $analysis['ai_answer'] ?? null;
            $analysis['ai_analysis'] = $ai['analysis'] ?? null;
            $analysis['answer_mode'] = $ai['answer_mode'] ?? $analysis['answer_mode'];
            $analysis['confidence'] = $ai['confidence'] ?? $analysis['confidence'];
            $analysis['agent'] = [
                'mode' => $provider,
                'model' => $this->model(),
                'provider' => $provider,
                'used_external_ai' => true,
            ];
        } catch (\Throwable $e) {
            $providerName = strtoupper($this->provider());
            $failureReason = $this->failureReason($providerName, $e->getMessage());
            Log::warning('HRU attendance AI agent failed', [
                'provider' => $this->provider(),
                'model' => $this->model(),
                'message' => $e->getMessage(),
            ]);

            $analysis['agent']['reason'] = $failureReason;

            if ($analysis['answer_mode'] === 'general') {
                $analysis['confidence'] = 'Low';
            }
        }

        return $analysis;
    }

    private function failureReason(string $providerName, string $message): string
    {
        if (Str::contains($message, '429')) {
            return "{$providerName} rate limit or free quota was reached; returned database rule analysis.";
        }

        if (Str::contains($message, ['401', '403'])) {
            return "{$providerName} API key was rejected or does not have access; returned database rule analysis.";
        }

        if (Str::contains($message, '404')) {
            return "{$providerName} model was not found or is unavailable; returned database rule analysis.";
        }

        if (Str::contains($message, ['timeout', 'timed out', '503', '502'])) {
            return "{$providerName} is temporarily unavailable; returned database rule analysis.";
        }

        return "{$providerName} request failed; returned database rule analysis.";
    }

    private function isEnabled(): bool
    {
        if (!(bool) config('services.ai_assistant.enabled')) {
            return false;
        }

        return filled($this->apiKey());
    }

    private function provider(): string
    {
        return 'groq';
    }

    private function apiKey(): ?string
    {
        return config('services.groq.api_key');
    }

    private function model(): string
    {
        return config('services.groq.model');
    }

    private function aiRequestPayload(array $analysis, ?string $question): array
    {
        return [
            'question' => $question ?: 'Analyze HRU attendance risk and provide recommendations.',
            'locale' => app()->getLocale(),
            'output_language' => app()->getLocale() === 'km' ? 'Khmer' : 'English',
            'attendance_data' => $analysis['answer_mode'] === 'general' ? null : $this->safeAnalysisPayload($analysis),
            'system_context' => $this->systemContext(),
            'required_output_json_shape' => [
                'answer_mode' => 'attendance, system, general, or unsupported.',
                'answer' => 'Direct answer to the user question based only on attendance_data or system_context.',
                'summary' => 'Brief overview based only on attendance_data.',
                'analysis' => 'Detailed findings based only on attendance_data.',
                'risk_level' => 'Low Risk, Medium Risk, or High Risk.',
                'risk_reason' => 'Reason for the risk level.',
                'recommendations' => ['Actionable recommendation strings.'],
                'confidence' => 'High, Medium, or Low.',
            ],
        ];
    }

    private function callGroq(array $analysis, ?string $question): ?array
    {
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');

        $response = Http::withToken($this->apiKey())
            ->acceptJson()
            ->timeout(45)
            ->post("{$baseUrl}/chat/completions", [
                'model' => $this->model(),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemInstructions(),
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($this->aiRequestPayload($analysis, $question), JSON_UNESCAPED_UNICODE),
                    ],
                ],
                'response_format' => [
                    'type' => 'json_object',
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Groq API error: ' . $response->status());
        }

        return $this->decodeChatCompletionResponse($response->json());
    }

    private function systemInstructions(): string
    {
        return <<<'PROMPT'
You are HRU Attendance Management AI Assistant.

Use only the attendance_data JSON provided by the system.
For questions about the ATS system itself, use only system_context.
For general knowledge questions, answer from general knowledge and set answer_mode to "general".
Never invent students, teachers, classes, records, or statistics.
If data is missing, state what data is required.
Do not reveal passwords, tokens, credentials, API keys, or hidden system data.
Classify risk only as Low Risk, Medium Risk, or High Risk.
Explain the reason for every risk assessment.
Give concise, professional, actionable recommendations.
Use the requested output_language for answer, summary, analysis, risk_reason, and recommendations.
If output_language is Khmer, write those fields fully in Khmer. Keep proper names unchanged.
Keep answer_mode, risk_level, and confidence in the exact English enum values requested by the JSON schema.
First answer the user's exact question. If the available attendance_data cannot answer it, say what data is required.
If the question is about the system/developer/app identity, set answer_mode to "system" and do not summarize attendance risk.
If the question is a general knowledge question, set answer_mode to "general" and do not summarize attendance risk.
If the question asks for private credentials, secrets, hidden system data, or data not present in attendance_data/system_context, set answer_mode to "unsupported" and say what data is required or why it cannot be provided.
If the question is about attendance records, risk, students, teachers, classes, departments, reports, or trends, set answer_mode to "attendance".

Return JSON only with these keys:
answer_mode, answer, summary, analysis, risk_level, risk_reason, recommendations, confidence.
PROMPT;
    }

    private function systemContext(): array
    {
        return [
            'system_name' => 'HRU Attendance Tracking System',
            'short_name' => 'HRU ATS',
            'developer' => 'Darith',
            'purpose' => 'Manage student and teacher attendance, risk analysis, reports, and attendance recommendations.',
        ];
    }

    private function inferAnswerMode(?string $question): string
    {
        $question = Str::lower((string) $question);

        if (Str::contains($question, ['developer', 'developed', 'creator', 'created', 'built', 'made', 'who make', 'who made', 'darith', 'system name', 'ats'])) {
            return 'system';
        }

        if ($this->isHelpQuestion($question)) {
            return 'system';
        }

        if (Str::contains($question, ['attendance', 'student', 'teacher', 'class', 'department', 'major', 'absence', 'absent', 'late', 'risk', 'report', 'semester', 'monthly', 'yearly', 'trend'])) {
            return 'attendance';
        }

        return $this->isUnsafeQuestion($question) ? 'unsupported' : 'general';
    }

    private function localAnswer(string $answerMode, ?string $question): ?string
    {
        if ($answerMode === 'system') {
            $question = Str::lower((string) $question);

            if ($this->isHelpQuestion($question)) {
                return 'Yes. You can ask about HRU attendance records, high-risk students, teacher attendance issues, monthly or semester reports, departments, classes, absence trends, late attendance, recommendations, and ATS system information such as the developer name.';
            }

            return 'The HRU Attendance Tracking System was developed by Darith.';
        }

        if ($answerMode === 'unsupported') {
            return 'I cannot provide credentials, secrets, hidden system data, or information that is not available to this assistant.';
        }

        if ($answerMode === 'general') {
            return 'The general AI provider is currently unavailable. Please try again shortly.';
        }

        return null;
    }

    private function isHelpQuestion(string $question): bool
    {
        $question = trim(Str::lower($question));

        if (preg_match('/^(hi|hello|hey)\b/', $question)) {
            return true;
        }

        return Str::contains($question, [
            'help',
            'can i ask',
            'what can you do',
            'how can you help',
            'capability',
            'capabilities',
        ]);
    }

    private function isUnsafeQuestion(string $question): bool
    {
        return Str::contains($question, [
            'password',
            'api key',
            'secret key',
            'token',
            'credential',
            '.env',
            'database password',
            'private key',
        ]);
    }

    private function safeAnalysisPayload(array $analysis): array
    {
        $students = $analysis['analysis']['students'] ?? [];
        $teachers = $analysis['analysis']['teachers'] ?? [];

        return [
            'summary' => $analysis['summary'] ?? [],
            'risk_assessment' => $analysis['risk_assessment'] ?? [],
            'student_counts' => [
                'total_students_analyzed' => $students['total_students_analyzed'] ?? 0,
                'risk_counts' => $students['counts'] ?? [],
                'required_data' => $students['required_data'] ?? [],
            ],
            'teacher_counts' => [
                'total_teachers_analyzed' => $teachers['total_teachers_analyzed'] ?? 0,
                'risk_counts' => $teachers['counts'] ?? [],
                'required_data' => $teachers['required_data'] ?? [],
            ],
            'high_risk_students' => $this->studentRows($students['high_risk_students'] ?? []),
            'medium_risk_students' => $this->studentRows($students['medium_risk_students'] ?? []),
            'high_risk_teachers' => $this->teacherRows($teachers['high_risk_teachers'] ?? []),
            'medium_risk_teachers' => $this->teacherRows($teachers['medium_risk_teachers'] ?? []),
            'rule_recommendations' => $analysis['recommendations'] ?? [],
            'rule_confidence' => $analysis['confidence'] ?? 'Low',
        ];
    }

    private function studentRows($rows): array
    {
        return collect($rows)->take(15)->map(fn($row) => [
            'name' => $row['name'] ?? 'Unknown Student',
            'group' => $row['group'] ?? null,
            'major' => $row['major'] ?? null,
            'department' => $row['department'] ?? null,
            'total_sessions' => $row['total_sessions'] ?? 0,
            'absence_count' => $row['absence_count'] ?? 0,
            'late_count' => $row['late_count'] ?? 0,
            'attendance_rate' => $row['attendance_rate'] ?? 0,
            'absent_rate' => $row['absent_rate'] ?? 0,
            'max_consecutive_absences' => $row['max_consecutive_absences'] ?? 0,
            'risk_level' => $row['risk_level'] ?? null,
            'risk_reason' => $row['risk_reason'] ?? null,
        ])->values()->all();
    }

    private function teacherRows($rows): array
    {
        return collect($rows)->take(15)->map(fn($row) => [
            'name' => $row['name'] ?? 'Unknown Teacher',
            'total_sessions' => $row['total_sessions'] ?? 0,
            'late_check_ins' => $row['late_check_ins'] ?? 0,
            'missed_classes' => $row['missed_classes'] ?? 0,
            'missed_rate' => $row['missed_rate'] ?? 0,
            'risk_level' => $row['risk_level'] ?? null,
            'risk_reason' => $row['risk_reason'] ?? null,
        ])->values()->all();
    }

    private function decodeChatCompletionResponse(array $payload): ?array
    {
        $text = collect($payload['choices'] ?? [])
            ->pluck('message.content')
            ->filter()
            ->join("\n");

        if (!$text) {
            return null;
        }

        return $this->normalizeDecodedResponse($text);
    }

    private function normalizeDecodedResponse(string $text): ?array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text);
        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            return null;
        }

        $confidence = $decoded['confidence'] ?? null;
        if (!in_array($confidence, ['High', 'Medium', 'Low'], true)) {
            $decoded['confidence'] = 'Medium';
        }

        $riskLevel = $decoded['risk_level'] ?? null;
        if (!in_array($riskLevel, ['High Risk', 'Medium Risk', 'Low Risk'], true)) {
            unset($decoded['risk_level']);
        }

        $answerMode = $decoded['answer_mode'] ?? null;
        if (!in_array($answerMode, ['attendance', 'system', 'general', 'unsupported'], true)) {
            unset($decoded['answer_mode']);
        }

        $decoded['recommendations'] = collect($decoded['recommendations'] ?? [])
            ->filter(fn($item) => is_string($item) && trim($item) !== '')
            ->map(fn($item) => Str::limit(trim($item), 260, '...'))
            ->values()
            ->all();

        foreach (['answer', 'summary', 'analysis', 'risk_reason'] as $key) {
            if (isset($decoded[$key]) && is_string($decoded[$key])) {
                $decoded[$key] = Str::limit(trim($decoded[$key]), 1200, '...');
            }
        }

        return $decoded;
    }
}
