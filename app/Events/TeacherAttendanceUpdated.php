<?php

namespace App\Events;

use App\Models\TeacherAttendanceSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherAttendanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TeacherAttendanceSession $session,
        public string $action
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('teacher-attendance'),
            new Channel('teacher-attendance.' . $this->session->attendance_date->toDateString()),
            new Channel('teacher-attendance.teacher.' . $this->session->teacher_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'teacher.attendance.updated';
    }

    public function broadcastWith(): array
    {
        $session = $this->session->fresh(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup', 'schedule', 'autoCheckInSourceSession']);

        return [
            'action' => $this->action,
            'session' => [
                'id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'teacher_name' => $session->teacher?->user?->name,
                'department' => $session->teacher?->department?->name,
                'subject_id' => $session->subject_id,
                'subject_name' => $session->subject?->name,
                'class_name' => $session->classGroup?->name ?? $session->classRoom?->name,
                'room_name' => $session->room_name,
                'attendance_date' => $session->attendance_date?->toDateString(),
                'scheduled_start_time' => $session->scheduled_start_time?->toIso8601String(),
                'scheduled_end_time' => $session->scheduled_end_time?->toIso8601String(),
                'session_number' => $session->session_number,
                'check_in_time' => $session->check_in_time?->toIso8601String(),
                'check_out_time' => $session->check_out_time?->toIso8601String(),
                'attendance_status' => $session->attendance_status,
                'check_in_method' => $session->check_in_method,
                'check_out_method' => $session->check_out_method,
                'late_minutes' => $session->late_minutes,
                'early_leave_minutes' => $session->early_leave_minutes,
                'teaching_duration_minutes' => $session->teaching_duration_minutes,
                'actual_teaching_hours' => $session->actual_teaching_hours,
                'auto_check_in_source_session_id' => $session->auto_check_in_source_session_id,
            ],
        ];
    }
}
