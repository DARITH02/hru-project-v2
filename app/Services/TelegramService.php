<?php

namespace App\Services;

use App\Models\TelegramBot;
use App\Models\AttendanceSession;
use App\Exports\AttendanceExport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    /**
     * Send attendance report to active Telegram bot.
     *
     * @param int $sessionId
     * @return bool
     */
    public function sendAttendanceReport($sessionId)
    {
        $session = AttendanceSession::with(['classRoom.subject', 'attendanceRecords.student'])->findOrFail($sessionId);

        // Conditions
        if ($session->telegram_sent) {
            return false;
        }

        // Mark as sent immediately to prevent duplicate triggers during long-running network tasks
        $session->update(['telegram_sent' => true]);

        $bot = TelegramBot::where('is_active', true)->first();
        if (!$bot || !$bot->chat_id) {
            Log::warning("Telegram report skipped for session {$sessionId}. No active bot or chat_id found.");
            return false;
        }

        try {
            // 1. Prepare Summary (Support Multi-Group)
            $groupIds = $session->classRoom->groups->pluck('id');
            $total = \App\Models\Student::whereIn('group_id', $groupIds)->count();
            if ($total === 0) $total = $session->attendanceRecords->count();
            
            $present = $session->attendanceRecords->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
            $absent = max(0, $total - $present);
            
            $subjectName = $session->classRoom->subject->name ?? 'Unknown Subject';
            $subjectCode = $session->classRoom->subject->code ?? 'N/A';
            $className = $session->classRoom->groups->pluck('name')->join(', ') ?: 'Unknown Class';
            $room = $session->classRoom->room_number ?? 'TBD';
            $teacher = $session->classRoom->teacher->user->name ?? 'Unknown';
            $date = $session->start_time;

            // Use HTML for better reliability with names containing special chars
            $message = "📊 <b>Attendance Report Ready</b>\n\n"
                     . "📖 <b>Subject:</b> " . e($subjectName) . " (" . e($subjectCode) . ")\n"
                     . "🏫 <b>Class:</b> " . e($className) . "\n"
                     . "📍 <b>Room:</b> " . e($room) . "\n"
                     . "👨‍🏫 <b>Instructor:</b> " . e($teacher) . "\n"
                     . "📅 <b>Date:</b> " . e($date) . "\n\n"
                     . "👥 <b>Total Class Size:</b> {$total}\n"
                     . "✅ <b>Marked Present:</b> {$present}\n"
                     . "❌ <b>Total Missing:</b> {$absent}\n\n"
                     . "Please find the detailed Excel report attached below.";

            // 2. Send Text Summary
            $this->sendMessage($bot, $message);

            // 3. Generate and Send Excel
            $fileName = "attendance_report_{$sessionId}_" . date('Ymd_His') . ".xlsx";
            $filePath = "temp/{$fileName}";
            
            Excel::store(new AttendanceExport($sessionId), $filePath, 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            $this->sendDocument($bot, $fullPath, $fileName);

            // Cleanup
            Storage::disk('local')->delete($filePath);

            return true;

        } catch (\Exception $e) {
            Log::error("Telegram Service Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send global system attendance summary to Telegram.
     */
    public function sendSystemSummaryReport($academicYear, $semester, $type = 'full')
    {
        $bot = TelegramBot::where('is_active', true)->first();
        if (!$bot || !$bot->chat_id) return false;

        $fileName = "system_summary_{$type}_" . str_replace('/', '-', $academicYear) . ".xlsx";
        $filePath = "temp/{$fileName}";

        // Use standard disk (local)
        Excel::store(new \App\Exports\SystemSummaryExport($academicYear, $semester, $type), $filePath, 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        $typeName = ($type === 'half') ? 'MID-TERM' : 'FULL SEMESTER';
        $message = "📑 <b>System Attendance Summary</b>\n\n"
                 . "📅 <b>Year:</b> " . e($academicYear) . "\n"
                 . "🔢 <b>Semester:</b> " . e($semester) . "\n"
                 . "🎯 <b>Scope:</b> " . e($typeName) . "\n\n"
                 . "Attached is the comprehensive school-wide attendance report.";

        $this->sendMessage($bot, $message);
        $this->sendDocument($bot, $fullPath, $fileName);

        // Cleanup
        Storage::disk('local')->delete($filePath);
        return true;
    }

    public function sendMessage($botOrChatId, $text)
    {
        $botToken = null;
        $chatId = null;

        if ($botOrChatId instanceof TelegramBot) {
            $botToken = $botOrChatId->bot_token;
            $chatId = $botOrChatId->chat_id;
        } else {
            // If it's a string/int (chat_id), we need the active bot token
            $activeBot = TelegramBot::where('is_active', true)->first();
            if (!$activeBot) return false;
            $botToken = $activeBot->bot_token;
            $chatId = $botOrChatId;
        }

        if (!$botToken || !$chatId) return false;

        return Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    protected function sendDocument($bot, $filePath, $fileName)
    {
        return Http::attach(
            'document', file_get_contents($filePath), $fileName
        )->post("https://api.telegram.org/bot{$bot->bot_token}/sendDocument", [
            'chat_id' => $bot->chat_id,
            'caption' => 'Detailed Attendance Excel Report'
        ]);
    }

    /**
     * Check if any student in the class has hit 10 or 20 absences and notify Telegram.
     */
    public function checkAbsenceThresholds($classId)
    {
        try {
            $class = \App\Models\ClassRoom::with(['groups', 'subject'])->find($classId);
            if (!$class) return;

            $completedSessionIds = AttendanceSession::where('class_id', $classId)
                ->where('status', 'completed')
                ->pluck('id');
            
            $sessionCount = $completedSessionIds->count();
            if ($sessionCount < 10) return;

            $groupIds = $class->groups->pluck('id');
            $students = \App\Models\Student::with('user')->whereIn('group_id', $groupIds)->get();

            foreach ($students as $student) {
                // Get current academic year and semester of the class context
                $activeAssignment = \App\Models\SemesterAssignment::where('class_id', $classId)
                    ->where('status', 'active')
                    ->first();
                $academicYear = $activeAssignment ? $activeAssignment->academic_year : (date('Y') . '-' . (date('Y') + 1));
                $semester = $activeAssignment ? $activeAssignment->semester : 1;

                // Find latest restore for student in this semester/year
                $latestRestore = \App\Models\StudentRestoreHistory::where('student_id', $student->id)
                    ->where('academic_year', $academicYear)
                    ->where('semester', $semester)
                    ->latest()
                    ->first();

                // Check 1: Per-Subject Absence (Alert at 10)
                $subjectSessionsQuery = AttendanceSession::where('class_id', $classId)
                    ->where('academic_year', $academicYear)
                    ->where('semester', $semester)
                    ->where('status', 'completed');

                if ($latestRestore) {
                    $subjectSessionsQuery->where('start_time', '>', $latestRestore->created_at);
                }

                $subjectSessionIds = $subjectSessionsQuery->pluck('id');
                $subjectSessionCount = $subjectSessionIds->count();

                $attendedCount = \App\Models\Attendance::where('student_id', $student->id)
                    ->whereIn('session_id', $subjectSessionIds)
                    ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
                    ->count();
                    
                $absentCount = max(0, $subjectSessionCount - $attendedCount);

                if ($absentCount == 10) {
                    $bot = TelegramBot::where('is_active', true)->first();
                    if ($bot && $bot->chat_id) {
                        $message = "⚠️ <b>PER-SUBJECT ABSENCE ALERT</b> ⚠️\n\n"
                                 . "👤 <b>Student:</b> " . e($student->user->name) . " (" . e($student->student_code) . ")\n"
                                 . "📖 <b>Subject:</b> " . e($class->subject->name) . "\n"
                                 . "❌ <b>Total Absences in this Subject:</b> <b>" . $absentCount . "</b> sessions\n\n"
                                 . "This student has reached the threshold for this subject.";
                        $this->sendMessage($bot, $message);
                    }
                }

                // Check 2: Global Absence (Alert at 20 across all subjects)
                $studentClasses = \App\Models\ClassRoom::whereHas('groups', function($q) use ($student) {
                        $q->where('class_groups.id', $student->group_id);
                    })->pluck('id');

                $relevantSessionQuery = AttendanceSession::whereIn('class_id', $studentClasses)
                    ->where('academic_year', $academicYear)
                    ->where('semester', $semester)
                    ->where('status', 'completed');

                if ($latestRestore) {
                    $relevantSessionQuery->where('start_time', '>', $latestRestore->created_at);
                }

                $relevantSessionIds = $relevantSessionQuery->pluck('id');
                $totalPossibleSessions = $relevantSessionIds->count();

                $totalAttended = \App\Models\Attendance::where('student_id', $student->id)
                    ->whereIn('session_id', $relevantSessionIds)
                    ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
                    ->count();
                
                $totalAbsents = max(0, $totalPossibleSessions - $totalAttended);

                if ($totalAbsents >= 30) {
                    if (!$student->isBlacklistedInSemester($academicYear, $semester)) {
                        $student->blacklistInSemester($academicYear, $semester);
                    }
                    $bot = TelegramBot::where('is_active', true)->first();
                    if ($bot && $bot->chat_id) {
                        $message = "🚫 <b>STUDENT BLACKLISTED</b> 🚫\n\n"
                                 . "👤 <b>Student:</b> " . e($student->user->name) . " (" . e($student->student_code) . ")\n"
                                 . "📊 <b>Total Absences (All Subjects):</b> <b style='color:red'>" . $totalAbsents . "</b> sessions\n"
                                 . "🎓 <b>Major:</b> " . e($student->major->name ?? $student->group->major->name ?? 'N/A') . "\n\n"
                                 . "❌ This student has accumulated 30 or more absences and is now <b>BLACKLISTED</b>.";
                        $this->sendMessage($bot, $message);
                    }
                } elseif ($totalAbsents == 20) {
                    $bot = TelegramBot::where('is_active', true)->first();
                    if ($bot && $bot->chat_id) {
                        $message = "🚨 <b>SYSTEM-WIDE ABSENCE ALERT</b> 🚨\n\n"
                                 . "👤 <b>Student:</b> " . e($student->user->name) . " (" . e($student->student_code) . ")\n"
                                 . "📊 <b>Total Absences (All Subjects):</b> <b style='color:red'>" . $totalAbsents . "</b> sessions\n\n"
                                 . "⚠️ This student has reached the <b>CRITICAL</b> system-wide absence limit.";
                        $this->sendMessage($bot, $message);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Absence alert check failed: " . $e->getMessage());
        }
    }
}
