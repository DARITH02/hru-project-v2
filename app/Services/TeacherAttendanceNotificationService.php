<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\TelegramBot;
use App\Models\TeacherAttendanceCorrection;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherClassChangeRequest;
use Illuminate\Support\Facades\Log;

class TeacherAttendanceNotificationService
{
    public function __construct(private TelegramService $telegram)
    {
    }

    public function lateCheckIn(TeacherAttendanceSession $session): void
    {
        if (!in_array($session->attendance_status, ['late', 'very_late'], true)) {
            return;
        }

        $this->notify(
            'Teacher late attendance',
            sprintf(
                "Late Attendance\nTeacher: %s\nSubject: %s\nDate: %s\nLate: %s minutes\nStatus: %s",
                $session->teacher->user->name ?? 'Unknown',
                $session->subject->name ?? 'Subject',
                $session->attendance_date?->format('Y-m-d'),
                $session->late_minutes,
                str_replace('_', ' ', $session->attendance_status)
            )
        );
    }

    public function missingCheckout(TeacherAttendanceSession $session): void
    {
        $this->notify(
            'Teacher missing checkout',
            sprintf(
                "Missing Check-Out\nTeacher: %s\nSubject: %s\nDate: %s\nScheduled End: %s",
                $session->teacher->user->name ?? 'Unknown',
                $session->subject->name ?? 'Subject',
                $session->attendance_date?->format('Y-m-d'),
                $session->scheduled_end_time?->format('H:i')
            )
        );
    }

    public function correctionReviewed(TeacherAttendanceCorrection $correction): void
    {
        $this->notify(
            'Teacher correction ' . $correction->status,
            sprintf(
                "Attendance Correction %s\nTeacher: %s\nType: %s\nNote: %s",
                strtoupper($correction->status),
                $correction->teacher->user->name ?? 'Unknown',
                str_replace('_', ' ', $correction->request_type),
                $correction->review_note ?: 'No note'
            )
        );
    }

    public function classChangeReviewed(TeacherClassChangeRequest $request): void
    {
        $this->notify(
            'Teacher class change ' . $request->status,
            sprintf(
                "Class Change %s\nTeacher: %s\nType: %s\nReason: %s",
                strtoupper($request->status),
                $request->teacher->user->name ?? 'Unknown',
                str_replace('_', ' ', $request->request_type),
                $request->reason
            )
        );
    }

    private function notify(string $activityAction, string $message): void
    {
        try {
            ActivityLog::create([
                'action' => $activityAction,
                'target' => mb_substr(str_replace("\n", ' | ', $message), 0, 255),
            ]);

            if ($bot = TelegramBot::where('is_active', true)->first()) {
                $this->telegram->sendMessage($bot, $message);
            }
        } catch (\Throwable $e) {
            Log::warning('Teacher attendance notification failed: ' . $e->getMessage());
        }
    }
}
