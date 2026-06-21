<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAttendanceSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HruAttendanceAiAssistantService
{
    private const STUDENT_EXCESSIVE_LATE_COUNT = 3;
    private const STUDENT_FREQUENT_LATE_COUNT = 2;
    private const STUDENT_CONSECUTIVE_ABSENCE_COUNT = 2;
    private const TEACHER_REPEATED_LATE_COUNT = 3;
    private const TEACHER_FREQUENT_MISSED_COUNT = 2;

    public function analyze(array $filters = []): array
    {
        $studentAnalysis = $this->analyzeStudents($filters);
        $teacherAnalysis = $this->analyzeTeachers($filters);
        $overallRisk = $this->highestRisk([
            $studentAnalysis['risk_level'],
            $teacherAnalysis['risk_level'],
        ]);

        return [
            'summary' => [
                'overview' => $this->buildSummary($studentAnalysis, $teacherAnalysis, $overallRisk),
                'filters' => $this->publicFilters($filters),
                'generated_at' => now()->toDateTimeString(),
            ],
            'analysis' => [
                'students' => $studentAnalysis,
                'teachers' => $teacherAnalysis,
            ],
            'risk_assessment' => [
                'level' => $overallRisk,
                'reason' => $this->riskReason($overallRisk, $studentAnalysis, $teacherAnalysis),
            ],
            'recommendations' => $this->recommendations($studentAnalysis, $teacherAnalysis),
            'confidence' => $this->confidence($studentAnalysis, $teacherAnalysis),
        ];
    }

    private function analyzeStudents(array $filters): array
    {
        $sessions = $this->studentSessions($filters);

        if ($sessions->isEmpty()) {
            return $this->insufficientResult($this->text('student_sessions_required'));
        }

        $students = $this->students($filters);

        if ($students->isEmpty()) {
            return $this->insufficientResult($this->text('student_records_required'));
        }

        $classGroupMap = $this->classGroupMap($sessions->pluck('class_id')->unique()->values());
        $sessionsByStudent = $this->sessionsByStudent($sessions, $students, $classGroupMap);
        $sessionIds = $sessions->pluck('id');
        $attendanceByStudent = Attendance::whereIn('session_id', $sessionIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $student) use ($sessionsByStudent, $attendanceByStudent) {
            $studentSessions = $sessionsByStudent[$student->id] ?? collect();
            $totalSessions = $studentSessions->count();

            if ($totalSessions === 0) {
                return null;
            }

            $records = ($attendanceByStudent[$student->id] ?? collect())->keyBy('session_id');
            $presentCount = 0;
            $lateCount = 0;
            $absenceCount = 0;
            $currentAbsenceRun = 0;
            $maxConsecutiveAbsences = 0;

            foreach ($studentSessions->sortBy('start_time') as $session) {
                $record = $records->get($session->id);
                $status = strtolower((string) ($record?->status ?? 'absent'));

                if (in_array($status, ['present', 'late'], true)) {
                    $presentCount++;
                    $currentAbsenceRun = 0;
                } else {
                    $absenceCount++;
                    $currentAbsenceRun++;
                    $maxConsecutiveAbsences = max($maxConsecutiveAbsences, $currentAbsenceRun);
                }

                if ($status === 'late') {
                    $lateCount++;
                }
            }

            $attendanceRate = round(($presentCount / $totalSessions) * 100, 2);
            $absentRate = round(($absenceCount / $totalSessions) * 100, 2);
            $risk = $this->studentRisk($attendanceRate, $absentRate, $lateCount, $maxConsecutiveAbsences);

            return [
                'student_id' => $student->id,
                'student_code' => $student->student_code,
                'name' => $student->user?->name ?? 'Unknown Student',
                'group' => $student->group?->name,
                'major' => $student->major?->name ?? $student->group?->major?->name,
                'department' => $student->major?->department?->name ?? $student->group?->major?->department?->name,
                'total_sessions' => $totalSessions,
                'present_count' => $presentCount,
                'absence_count' => $absenceCount,
                'late_count' => $lateCount,
                'attendance_rate' => $attendanceRate,
                'absent_rate' => $absentRate,
                'max_consecutive_absences' => $maxConsecutiveAbsences,
                'risk_level' => $risk['level'],
                'risk_reason' => $risk['reason'],
            ];
        })->filter()->values();

        return [
            'risk_level' => $this->highestRisk($rows->pluck('risk_level')->all()),
            'counts' => $this->riskCounts($rows),
            'total_students_analyzed' => $rows->count(),
            'high_risk_students' => $rows->where('risk_level', 'High Risk')->values(),
            'medium_risk_students' => $rows->where('risk_level', 'Medium Risk')->values(),
            'low_risk_students' => $rows->where('risk_level', 'Low Risk')->values(),
            'required_data' => [],
        ];
    }

    private function analyzeTeachers(array $filters): array
    {
        $query = TeacherAttendanceSession::query()->with(['teacher.user', 'subject', 'classGroup']);

        $this->applyDateFilters($query, $filters, 'attendance_date');

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['group_id'])) {
            $query->where('class_group_id', $filters['group_id']);
        }

        $sessions = $query->get();

        if ($sessions->isEmpty()) {
            return $this->insufficientResult($this->text('teacher_sessions_required'));
        }

        $teacherIds = $sessions->pluck('teacher_id')->filter()->unique()->values();
        $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get()->keyBy('id');

        $rows = $sessions->groupBy('teacher_id')->map(function (Collection $teacherSessions, $teacherId) use ($teachers) {
            $lateCount = $teacherSessions->filter(function (TeacherAttendanceSession $session) {
                return (int) $session->late_minutes > 0 || strtolower((string) $session->attendance_status) === 'late';
            })->count();
            $missedCount = $teacherSessions->filter(function (TeacherAttendanceSession $session) {
                return in_array(strtolower((string) $session->attendance_status), ['absent', 'missed'], true);
            })->count();
            $totalSessions = $teacherSessions->count();
            $missedRate = $totalSessions > 0 ? round(($missedCount / $totalSessions) * 100, 2) : 0.0;
            $risk = $this->teacherRisk($lateCount, $missedCount, $missedRate);
            $teacher = $teachers->get($teacherId);

            return [
                'teacher_id' => $teacherId,
                'name' => $teacher?->user?->name ?? 'Unknown Teacher',
                'total_sessions' => $totalSessions,
                'late_check_ins' => $lateCount,
                'missed_classes' => $missedCount,
                'missed_rate' => $missedRate,
                'risk_level' => $risk['level'],
                'risk_reason' => $risk['reason'],
            ];
        })->values();

        return [
            'risk_level' => $this->highestRisk($rows->pluck('risk_level')->all()),
            'counts' => $this->riskCounts($rows),
            'total_teachers_analyzed' => $rows->count(),
            'high_risk_teachers' => $rows->where('risk_level', 'High Risk')->values(),
            'medium_risk_teachers' => $rows->where('risk_level', 'Medium Risk')->values(),
            'low_risk_teachers' => $rows->where('risk_level', 'Low Risk')->values(),
            'required_data' => [],
        ];
    }

    private function studentSessions(array $filters): Collection
    {
        $query = AttendanceSession::query()->with(['classRoom.groups', 'classRoom.subject']);

        if (!empty($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (!empty($filters['semester'])) {
            $query->where('semester', (int) $filters['semester']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        $this->applyDateFilters($query, $filters, 'start_time');

        return $query->whereIn('status', ['active', 'completed', 'passed'])->get();
    }

    private function students(array $filters): Collection
    {
        $query = Student::query()->with(['user', 'group.major.department', 'major.department']);

        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        if (!empty($filters['major_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('major_id', $filters['major_id'])
                    ->orWhereHas('group', fn($group) => $group->where('major_id', $filters['major_id']));
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('major', fn($major) => $major->where('department_id', $filters['department_id']))
                    ->orWhereHas('group.major', fn($major) => $major->where('department_id', $filters['department_id']));
            });
        }

        return $query->get();
    }

    private function classGroupMap(Collection $classIds): array
    {
        if ($classIds->isEmpty()) {
            return [];
        }

        if (Schema::hasTable('class_class_group')) {
            return \DB::table('class_class_group')
                ->whereIn('class_room_id', $classIds)
                ->get()
                ->groupBy('class_room_id')
                ->map(fn(Collection $rows) => $rows->pluck('class_group_id')->all())
                ->all();
        }

        return \DB::table('classes')
            ->whereIn('id', $classIds)
            ->pluck('group_id', 'id')
            ->map(fn($groupId) => $groupId ? [$groupId] : [])
            ->all();
    }

    private function sessionsByStudent(Collection $sessions, Collection $students, array $classGroupMap): array
    {
        $studentsByGroup = $students->groupBy('group_id');
        $result = [];

        foreach ($sessions as $session) {
            $groupIds = $classGroupMap[$session->class_id] ?? [];

            foreach ($groupIds as $groupId) {
                foreach (($studentsByGroup[$groupId] ?? collect()) as $student) {
                    $result[$student->id] ??= collect();
                    $result[$student->id]->push($session);
                }
            }
        }

        return $result;
    }

    private function studentRisk(float $attendanceRate, float $absentRate, int $lateCount, int $maxConsecutiveAbsences): array
    {
        if ($absentRate > 20 || $maxConsecutiveAbsences >= self::STUDENT_CONSECUTIVE_ABSENCE_COUNT || $lateCount >= self::STUDENT_EXCESSIVE_LATE_COUNT) {
            return [
                'level' => 'High Risk',
                'reason' => $this->text('student_high_reason'),
            ];
        }

        if (($absentRate >= 10 && $absentRate <= 20) || $lateCount >= self::STUDENT_FREQUENT_LATE_COUNT) {
            return [
                'level' => 'Medium Risk',
                'reason' => $this->text('student_medium_reason'),
            ];
        }

        return [
            'level' => 'Low Risk',
            'reason' => $attendanceRate > 90
                ? $this->text('student_low_strong_reason')
                : $this->text('no_medium_high_reason'),
        ];
    }

    private function teacherRisk(int $lateCount, int $missedCount, float $missedRate): array
    {
        if ($lateCount >= self::TEACHER_REPEATED_LATE_COUNT || $missedCount >= self::TEACHER_FREQUENT_MISSED_COUNT || $missedRate > 20) {
            return [
                'level' => 'High Risk',
                'reason' => $this->text('teacher_high_reason'),
            ];
        }

        if ($lateCount > 0 || $missedCount > 0) {
            return [
                'level' => 'Medium Risk',
                'reason' => $this->text('teacher_medium_reason'),
            ];
        }

        return [
            'level' => 'Low Risk',
            'reason' => $this->text('teacher_low_reason'),
        ];
    }

    private function applyDateFilters($query, array $filters, string $column): void
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate($column, '>=', Carbon::parse($filters['date_from'])->toDateString());
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate($column, '<=', Carbon::parse($filters['date_to'])->toDateString());
        }
    }

    private function insufficientResult(string $requiredData): array
    {
        return [
            'risk_level' => 'Low Risk',
            'counts' => ['high' => 0, 'medium' => 0, 'low' => 0],
            'required_data' => [$requiredData],
        ];
    }

    private function riskCounts(Collection $rows): array
    {
        return [
            'high' => $rows->where('risk_level', 'High Risk')->count(),
            'medium' => $rows->where('risk_level', 'Medium Risk')->count(),
            'low' => $rows->where('risk_level', 'Low Risk')->count(),
        ];
    }

    private function highestRisk(array $levels): string
    {
        if (in_array('High Risk', $levels, true)) {
            return 'High Risk';
        }

        if (in_array('Medium Risk', $levels, true)) {
            return 'Medium Risk';
        }

        return 'Low Risk';
    }

    private function buildSummary(array $students, array $teachers, string $risk): string
    {
        $studentHigh = $students['counts']['high'] ?? 0;
        $teacherHigh = $teachers['counts']['high'] ?? 0;

        if (!empty($students['required_data']) && !empty($teachers['required_data'])) {
            return $this->text('insufficient_complete');
        }

        return $this->text('overall_summary', [
            'risk' => $this->riskLabel($risk),
            'students' => $studentHigh,
            'teachers' => $teacherHigh,
        ]);
    }

    private function riskReason(string $risk, array $students, array $teachers): string
    {
        if ($risk === 'High Risk') {
            return $this->text('overall_high_reason');
        }

        if ($risk === 'Medium Risk') {
            return $this->text('overall_medium_reason');
        }

        return $this->text('overall_low_reason');
    }

    private function recommendations(array $students, array $teachers): array
    {
        $items = [];

        if (($students['counts']['high'] ?? 0) > 0) {
            $items[] = $this->text('recommend_student_high_1');
            $items[] = $this->text('recommend_student_high_2');
        }

        if (($students['counts']['medium'] ?? 0) > 0) {
            $items[] = $this->text('recommend_student_medium');
        }

        if (($teachers['counts']['high'] ?? 0) > 0) {
            $items[] = $this->text('recommend_teacher_high');
        }

        if (($teachers['counts']['medium'] ?? 0) > 0) {
            $items[] = $this->text('recommend_teacher_medium');
        }

        if (empty($items)) {
            $items[] = $this->text('recommend_routine');
        }

        foreach (array_merge($students['required_data'] ?? [], $teachers['required_data'] ?? []) as $requiredData) {
            $items[] = $this->text('provide_missing', ['data' => $requiredData]);
        }

        return array_values(array_unique($items));
    }

    private function confidence(array $students, array $teachers): string
    {
        $missing = count($students['required_data'] ?? []) + count($teachers['required_data'] ?? []);

        if ($missing >= 2) {
            return 'Low';
        }

        if ($missing === 1) {
            return 'Medium';
        }

        return 'High';
    }

    private function publicFilters(array $filters): array
    {
        return collect($filters)
            ->only(['academic_year', 'semester', 'date_from', 'date_to', 'class_id', 'group_id', 'major_id', 'department_id', 'teacher_id'])
            ->filter(fn($value) => $value !== null && $value !== '')
            ->all();
    }

    private function riskLabel(string $risk): string
    {
        if (app()->getLocale() !== 'km') {
            return $risk;
        }

        return match ($risk) {
            'High Risk' => 'ហានិភ័យខ្ពស់',
            'Medium Risk' => 'ហានិភ័យមធ្យម',
            'Low Risk' => 'ហានិភ័យទាប',
            default => $risk,
        };
    }

    private function text(string $key, array $replace = []): string
    {
        $lines = app()->getLocale() === 'km'
            ? [
                'student_sessions_required' => 'ត្រូវការទិន្នន័យសេសសិនវត្តមានសិស្សសម្រាប់វិសាលភាពដែលបានជ្រើស។',
                'student_records_required' => 'ត្រូវការទិន្នន័យសិស្សសម្រាប់វិសាលភាពដែលបានជ្រើស។',
                'teacher_sessions_required' => 'ត្រូវការទិន្នន័យសេសសិនវត្តមានគ្រូសម្រាប់វិសាលភាពដែលបានជ្រើស។',
                'student_high_reason' => 'អត្រាអវត្តមានលើស ២០% ឬមានអវត្តមានជាប់ៗគ្នា ឬការមកយឺតមានច្រើនពេក។',
                'student_medium_reason' => 'អត្រាអវត្តមានស្ថិតចន្លោះ ១០% ដល់ ២០% ឬមានការមកយឺតញឹកញាប់។',
                'student_low_strong_reason' => 'វត្តមានលើស ៩០% និងមានការមកយឺតតិចតួច។',
                'no_medium_high_reason' => 'មិនរកឃើញលំនាំហានិភ័យខ្ពស់ ឬមធ្យមពីទិន្នន័យដែលមាន។',
                'teacher_high_reason' => 'រកឃើញការចូលយឺតជាញឹកញាប់ ឬការខកខានថ្នាក់ច្រើន។',
                'teacher_medium_reason' => 'រកឃើញបញ្ហាវត្តមានគ្រូម្តងម្កាល។',
                'teacher_low_reason' => 'កំណត់ត្រាវត្តមានគ្រូមានស្ថិរភាពក្នុងវិសាលភាពដែលបានជ្រើស។',
                'insufficient_complete' => 'ទិន្នន័យវត្តមានមិនគ្រប់គ្រាន់សម្រាប់ការវិភាគ HRU ពេញលេញ។',
                'overall_summary' => 'ស្ថានភាពទូទៅ៖ :risk។ រកឃើញសិស្សហានិភ័យខ្ពស់ចំនួន :students នាក់ និងគ្រូហានិភ័យខ្ពស់ចំនួន :teachers នាក់ ពីទិន្នន័យដែលមាន។',
                'overall_high_reason' => 'មានយ៉ាងហោចណាស់លំនាំវត្តមានហានិភ័យខ្ពស់មួយ សម្រាប់សិស្ស ឬគ្រូ ក្នុងវិសាលភាពដែលបានជ្រើស។',
                'overall_medium_reason' => 'មិនរកឃើញលំនាំហានិភ័យខ្ពស់ ប៉ុន្តែមានបញ្ហាវត្តមានកម្រិតហានិភ័យមធ្យម។',
                'overall_low_reason' => 'មិនរកឃើញលំនាំហានិភ័យខ្ពស់ ឬមធ្យមពីទិន្នន័យដែលមាន។',
                'recommend_student_high_1' => 'ផ្តល់អាទិភាពដល់ការប្រឹក្សា និងការតាមដានពីអ្នកប្រឹក្សាសម្រាប់សិស្សហានិភ័យខ្ពស់។',
                'recommend_student_high_2' => 'ពិនិត្យករណីអវត្តមានជាប់ៗគ្នា ហើយទាក់ទងសិស្សមុនសេសសិនបន្ទាប់។',
                'recommend_student_medium' => 'តាមដានសិស្សហានិភ័យមធ្យមរៀងរាល់សប្តាហ៍ និងជូនដំណឹងមុនអវត្តមានលើសកម្រិត ២០%។',
                'recommend_teacher_high' => 'បញ្ជូនករណីវត្តមានគ្រូហានិភ័យខ្ពស់ទៅរដ្ឋបាល ដើម្បីចាត់វិធានការកែតម្រូវ។',
                'recommend_teacher_medium' => 'ស្នើឱ្យគ្រូដែលមានបញ្ហាម្តងម្កាល បញ្ជាក់កាលវិភាគ និងនីតិវិធីចូលវត្តមាន។',
                'recommend_routine' => 'បន្តតាមដានវត្តមានជាប្រចាំ និងរក្សាទិន្នន័យឱ្យទាន់សម័យ។',
                'provide_missing' => 'សូមផ្តល់ទិន្នន័យដែលខ្វះ៖ :data',
            ]
            : [
                'student_sessions_required' => 'Student attendance sessions are required for the selected scope.',
                'student_records_required' => 'Student records are required for the selected scope.',
                'teacher_sessions_required' => 'Teacher attendance session records are required for the selected scope.',
                'student_high_reason' => 'Absent rate is greater than 20%, consecutive absences were detected, or late attendance is excessive.',
                'student_medium_reason' => 'Absent rate is between 10% and 20%, or lateness is frequent.',
                'student_low_strong_reason' => 'Attendance is above 90% with minimal lateness.',
                'no_medium_high_reason' => 'No high or medium risk attendance pattern was detected from available records.',
                'teacher_high_reason' => 'Repeated late check-ins or frequent missed classes were detected.',
                'teacher_medium_reason' => 'Occasional teacher attendance issues were detected.',
                'teacher_low_reason' => 'Teacher attendance records are consistent in the selected scope.',
                'insufficient_complete' => 'Insufficient attendance data is available for a complete HRU attendance analysis.',
                'overall_summary' => 'Overall :risk. :students high-risk student(s) and :teachers high-risk teacher(s) were identified from available records.',
                'overall_high_reason' => 'At least one high-risk student or teacher attendance pattern exists in the selected scope.',
                'overall_medium_reason' => 'No high-risk pattern was found, but medium-risk attendance issues exist in the selected scope.',
                'overall_low_reason' => 'No high-risk or medium-risk attendance pattern was detected from available records.',
                'recommend_student_high_1' => 'Prioritize counseling and advisor follow-up for high-risk students.',
                'recommend_student_high_2' => 'Review consecutive absence cases and contact students before the next class session.',
                'recommend_student_medium' => 'Monitor medium-risk students weekly and warn them before absences exceed the 20% threshold.',
                'recommend_teacher_high' => 'Escalate high-risk teacher attendance cases to administration for corrective action.',
                'recommend_teacher_medium' => 'Ask teachers with occasional issues to confirm schedules and check-in procedures.',
                'recommend_routine' => 'Continue routine attendance monitoring and keep records updated.',
                'provide_missing' => 'Provide missing data: :data',
            ];

        $text = $lines[$key] ?? $key;

        foreach ($replace as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }
}
