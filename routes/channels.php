<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('teacher-attendance', fn () => true);
Broadcast::channel('teacher-attendance.{date}', fn () => true);
Broadcast::channel('teacher-attendance.teacher.{teacherId}', function ($user, int $teacherId) {
    return $user->isAdmin() || (int) $user->teacher?->id === $teacherId;
});
