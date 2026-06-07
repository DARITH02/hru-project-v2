<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\StudentPermission;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SemesterAttendanceScoreService
{
    private const FULL_SCORE = 20;
    private const SEMESTER_SESSIONS = 30;
    private const CREDIT_STATUSES = ['present', 'late', 'PRESENT', 'LATE'];

    public function calculate(int $studentId, Collection $sessions): array
    {
        $sessionIds = $sessions->pluck('id')->filter()->values();

        if ($sessionIds->isEmpty()) {
            return [
                'credited_sessions' => 0,
                'attended_sessions' => 0,
                'permission_sessions' => 0,
                'rate' => 0,
                'score' => 0,
            ];
        }

        $attendedSessionIds = Attendance::where('student_id', $studentId)
            ->whereIn('session_id', $sessionIds)
            ->whereIn('status', self::CREDIT_STATUSES)
            ->pluck('session_id')
            ->unique();

        $permissions = StudentPermission::where('student_id', $studentId)
            ->where('start_date', '<=', $sessions->max(fn($session) => Carbon::parse($session->start_time)->toDateString()))
            ->where('end_date', '>=', $sessions->min(fn($session) => Carbon::parse($session->start_time)->toDateString()))
            ->get();

        $permissionSessionIds = $sessions
            ->reject(fn($session) => $attendedSessionIds->contains($session->id))
            ->filter(function ($session) use ($permissions) {
                $sessionDate = Carbon::parse($session->start_time)->toDateString();

                return $permissions->contains(function ($permission) use ($sessionDate) {
                    return $permission->start_date <= $sessionDate && $permission->end_date >= $sessionDate;
                });
            })
            ->pluck('id')
            ->unique();

        $creditedSessions = min(
            self::SEMESTER_SESSIONS,
            $attendedSessionIds->count() + $permissionSessionIds->count()
        );

        return [
            'credited_sessions' => $creditedSessions,
            'attended_sessions' => $attendedSessionIds->count(),
            'permission_sessions' => $permissionSessionIds->count(),
            'rate' => round(($creditedSessions / self::SEMESTER_SESSIONS) * 100, 1),
            'score' => round(($creditedSessions / self::SEMESTER_SESSIONS) * self::FULL_SCORE, 2),
        ];
    }
}
