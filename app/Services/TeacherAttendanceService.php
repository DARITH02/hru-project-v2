<?php

namespace App\Services;

use App\Events\TeacherAttendanceUpdated;
use App\Models\ActivityLog;
use App\Models\AttendanceSession;
use App\Models\SemesterAssignment;
use App\Models\Setting;
use App\Models\Teacher;
use App\Models\TeacherAttendanceCorrection;
use App\Models\TeacherAttendanceLog;
use App\Models\TeacherAttendanceQrToken;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherClassChangeRequest;
use App\Models\TeacherSchedule;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceService
{
    public const VALID_PRESENT_STATUSES = ['present', 'on_time', 'late', 'very_late', 'teaching', 'completed', 'early_leave', 'permission'];
    private const LATE_GRACE_MINUTES = 15;

    public function syncFromStudentAttendanceSessions(?int $actorId = null): int
    {
        $created = 0;

        AttendanceSession::with(['classRoom.subject', 'classRoom.groups'])
            ->whereHas('classRoom', fn($q) => $q->whereNotNull('teacher_id'))
            ->orderBy('start_time')
            ->chunk(100, function ($sessions) use (&$created, $actorId) {
                foreach ($sessions as $session) {
                    $class = $session->classRoom;
                    if (!$class || !$class->teacher_id) {
                        continue;
                    }

                    if (in_array($session->status, ['skipped', 'cancelled'], true)) {
                        $this->deleteScheduleForCourseSession((int) $session->id);
                        continue;
                    }

                    $start = Carbon::parse($session->start_time);
                    $end = Carbon::parse($session->end_time);
                    $group = $class->groups->first();

                    $schedule = TeacherSchedule::firstOrCreate(
                        ['source_attendance_session_id' => $session->id],
                        [
                            'teacher_id' => $class->teacher_id,
                            'subject_id' => $class->subject_id,
                            'class_id' => $class->id,
                            'class_group_id' => $group?->id ?? $class->group_id,
                            'room_name' => $class->room_number,
                            'schedule_date' => $start->toDateString(),
                            'scheduled_start_time' => $start,
                            'scheduled_end_time' => $end,
                            'session_number' => $this->nextSessionNumber($class->teacher_id, $start),
                            'check_in_opens_at' => $start->copy()->subMinutes(30),
                            'check_in_closes_at' => $end->copy(),
                            'check_out_opens_at' => $end->copy()->subMinutes(15),
                            'check_out_closes_at' => $end->copy()->addMinutes(60),
                            'semester' => $session->semester ?? $class->semester,
                            'academic_year' => $session->academic_year ?? $class->academic_year,
                            'status' => in_array($session->status, ['skipped', 'cancelled'], true) ? 'cancelled' : 'scheduled',
                            'source' => 'generated',
                            'created_by' => $actorId,
                        ]
                    );

                    if ($schedule->wasRecentlyCreated) {
                        $created++;
                    } elseif ($schedule->source === 'generated') {
                        $schedule->forceFill([
                            'teacher_id' => $class->teacher_id,
                            'subject_id' => $class->subject_id,
                            'class_id' => $class->id,
                            'class_group_id' => $group?->id ?? $class->group_id,
                            'room_name' => $class->room_number,
                            'schedule_date' => $start->toDateString(),
                            'scheduled_start_time' => $start,
                            'scheduled_end_time' => $end,
                            'session_number' => $this->nextSessionNumber($class->teacher_id, $start, $schedule->id),
                            'check_in_opens_at' => $start->copy()->subMinutes(30),
                            'check_in_closes_at' => $end->copy(),
                            'check_out_opens_at' => $end->copy()->subMinutes(15),
                            'check_out_closes_at' => $end->copy()->addMinutes(60),
                            'semester' => $session->semester ?? $class->semester,
                            'academic_year' => $session->academic_year ?? $class->academic_year,
                        ])->save();
                    }

                    $this->ensureAttendanceSession($schedule, $actorId);
                }
            });

        return $created;
    }

    private function deleteScheduleForCourseSession(int $courseSessionId): void
    {
        $schedule = TeacherSchedule::where('source_attendance_session_id', $courseSessionId)->first();
        if (!$schedule) {
            return;
        }

        TeacherAttendanceSession::where('schedule_id', $schedule->id)->delete();
        $schedule->delete();
    }

    public function ensureAttendanceSession(TeacherSchedule $schedule, ?int $actorId = null): TeacherAttendanceSession
    {
        $session = TeacherAttendanceSession::firstOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'class_id' => $schedule->class_id,
                'class_group_id' => $schedule->class_group_id,
                'room_name' => $schedule->room_name,
                'attendance_date' => $schedule->schedule_date,
                'scheduled_start_time' => $schedule->scheduled_start_time,
                'scheduled_end_time' => $schedule->scheduled_end_time,
                'session_number' => $schedule->session_number,
                'attendance_status' => $schedule->status === 'cancelled' ? 'cancelled' : 'scheduled',
                'created_by' => $actorId,
            ]
        );

        if (!$session->wasRecentlyCreated && $session->session_number !== $schedule->session_number) {
            $session->forceFill(['session_number' => $schedule->session_number])->save();
        }

        return $session;
    }

    public function generateQrToken(TeacherAttendanceSession $session, int $ttlSeconds = 60): array
    {
        if ($session->attendance_status === 'permission') {
            throw ValidationException::withMessages(['session' => 'This session is already marked as permission.']);
        }

        $now = now();
        $schedule = $session->schedule;
        if ($schedule) {
            $opensAt = Carbon::parse($schedule->check_in_opens_at);
            $closesAt = $schedule->check_out_closes_at
                ? Carbon::parse($schedule->check_out_closes_at)
                : Carbon::parse($session->scheduled_end_time)->addMinutes(60);

            if ($now->lt($opensAt) || $now->gt($closesAt)) {
                throw ValidationException::withMessages([
                    'session' => 'QR is available from 30 minutes before class until the checkout window closes.',
                ]);
            }
        }

        $expiresAt = $now->copy()->addSeconds($ttlSeconds);

        $raw = implode('|', [
            'teacher-attendance',
            $session->id,
            $session->teacher_id,
            $session->subject_id,
            $session->attendance_date->toDateString(),
            $session->session_number,
            Str::random(40),
        ]);

        $token = base64_encode($raw);

        TeacherAttendanceQrToken::create([
            'teacher_attendance_session_id' => $session->id,
            'teacher_id' => $session->teacher_id,
            'schedule_id' => $session->schedule_id,
            'subject_id' => $session->subject_id,
            'attendance_date' => $session->attendance_date,
            'session_number' => $session->session_number,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'ttl_seconds' => $ttlSeconds,
        ];
    }

    public function qrWindowTtlSeconds(TeacherAttendanceSession $session): int
    {
        $closesAt = $session->schedule?->check_out_closes_at
            ? Carbon::parse($session->schedule->check_out_closes_at)
            : Carbon::parse($session->scheduled_end_time)->addMinutes(60);

        return (int) max(1, now()->diffInSeconds($closesAt, false));
    }

    public function findQrToken(string $token): ?TeacherAttendanceQrToken
    {
        return TeacherAttendanceQrToken::with(['attendanceSession.teacher.user', 'attendanceSession.subject', 'attendanceSession.classRoom', 'attendanceSession.classGroup'])
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    public function qrCheckIn(string $token, Teacher $teacher, ?Request $request = null, ?Carbon $time = null): TeacherAttendanceSession
    {
        $time ??= now();
        $qr = TeacherAttendanceQrToken::with('attendanceSession.schedule')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (!$qr || $qr->expires_at->lt($time) || $qr->used_at) {
            throw ValidationException::withMessages(['token' => 'The QR token is invalid, expired, or already used.']);
        }

        if ($qr->teacher_id !== $teacher->id) {
            throw new AuthorizationException('This QR code does not belong to the authenticated teacher.');
        }

        $session = $qr->attendanceSession;
        if (!$session || $session->session_number !== 1) {
            throw ValidationException::withMessages(['session' => 'Only session 1 accepts QR check-in.']);
        }

        if ($session->teacher_id !== $teacher->id ||
            $session->schedule_id !== $qr->schedule_id ||
            $session->subject_id !== $qr->subject_id ||
            !$session->attendance_date->isSameDay($qr->attendance_date) ||
            $session->session_number !== $qr->session_number) {
            throw ValidationException::withMessages(['token' => 'The QR token does not match the teacher, subject, schedule, date, and session.']);
        }

        return DB::transaction(function () use ($qr, $session, $request, $time) {
            $checkedIn = $this->checkIn($session, $request, 'qr', $time);
            $qr->update([
                'used_at' => $time,
                'used_ip_address' => $request?->ip(),
            ]);
            $this->autoCheckInLaterSameSubjectSessions($checkedIn, $request, $time);

            return $checkedIn;
        });
    }

    public function qrSubmit(string $token, Teacher $teacher, string $action, ?Request $request = null, ?Carbon $time = null): TeacherAttendanceSession
    {
        $time ??= now();
        $qr = TeacherAttendanceQrToken::with('attendanceSession.schedule')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (!$qr || $qr->expires_at->lt($time)) {
            throw ValidationException::withMessages(['token' => 'The QR token is invalid or expired.']);
        }

        if ($qr->teacher_id !== $teacher->id) {
            throw new AuthorizationException('This QR code does not belong to the authenticated teacher.');
        }

        $session = $qr->attendanceSession;
        if (!$session) {
            throw ValidationException::withMessages(['session' => 'The QR code session could not be found.']);
        }

        if ($session->teacher_id !== $teacher->id ||
            $session->schedule_id !== $qr->schedule_id ||
            $session->subject_id !== $qr->subject_id ||
            !$session->attendance_date->isSameDay($qr->attendance_date) ||
            $session->session_number !== $qr->session_number) {
            throw ValidationException::withMessages(['token' => 'The QR token does not match the teacher, subject, schedule, date, and session.']);
        }

        if ($action === 'check_out') {
            return $this->qrCheckOutOpenSameSubjectSession($qr, $session, $request, $time);
        }

        if ($session->session_number !== 1) {
            throw ValidationException::withMessages([
                'attendance_action' => 'Only session 1 QR can check in. Use check-out for later session QR codes.',
            ]);
        }

        return $this->qrCheckInSessionOne($qr, $session, $request, $time);
    }

    private function qrCheckInSessionOne(TeacherAttendanceQrToken $qr, TeacherAttendanceSession $session, ?Request $request, Carbon $time): TeacherAttendanceSession
    {
        if ($session->check_in_time) {
            throw ValidationException::withMessages(['attendance_action' => 'This teacher is already checked in for session 1.']);
        }

        return DB::transaction(function () use ($qr, $session, $request, $time) {
            $checkedIn = $this->checkIn($session, $request, 'qr', $time);
            $qr->update([
                'used_at' => $time,
                'used_ip_address' => $request?->ip(),
            ]);
            $this->autoCheckInLaterSameSubjectSessions($checkedIn, $request, $time);

            return $checkedIn;
        });
    }

    private function qrCheckOutOpenSameSubjectSession(TeacherAttendanceQrToken $qr, TeacherAttendanceSession $source, ?Request $request, Carbon $time): TeacherAttendanceSession
    {
        if ($source->session_number === 1) {
            throw ValidationException::withMessages([
                'attendance_action' => 'Session 1 QR is only for check-in. Use the later session QR to check out.',
            ]);
        }

        $session = TeacherAttendanceSession::where('teacher_id', $source->teacher_id)
            ->where('subject_id', $source->subject_id)
            ->whereDate('attendance_date', $source->attendance_date)
            ->where('id', $source->id)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission'])
            ->first();

        if (!$session) {
            throw ValidationException::withMessages(['attendance_action' => 'This session is not checked in yet, or check-out was already submitted.']);
        }

        $this->validateCheckoutLocation($session, $request);

        return DB::transaction(function () use ($qr, $session, $request, $time) {
            $checkedOut = $this->checkOut($session, $request, 'qr', $time);
            $qr->update([
                'used_at' => $time,
                'used_ip_address' => $request?->ip(),
            ]);

            return $checkedOut;
        });
    }

    public function checkInFromPriorSameSubjectSession(TeacherAttendanceSession $session, ?Request $request = null, ?Carbon $time = null): TeacherAttendanceSession
    {
        if ($session->session_number <= 1) {
            throw ValidationException::withMessages(['session' => 'Session 1 must check in by QR scan.']);
        }

        $source = TeacherAttendanceSession::where('teacher_id', $session->teacher_id)
            ->where('subject_id', $session->subject_id)
            ->whereDate('attendance_date', $session->attendance_date)
            ->where('session_number', 1)
            ->whereNotNull('check_in_time')
            ->whereIn('attendance_status', self::VALID_PRESENT_STATUSES)
            ->first();

        if (!$source) {
            throw ValidationException::withMessages([
                'session' => 'Session 2 cannot auto check in until session 1 has a valid QR check-in for the same subject and date.',
            ]);
        }

        $checkInTime = $this->autoSessionCheckInTime($source, $session);
        $lateMinutes = $this->checkInLateMinutes($session, $checkInTime);

        $old = $session->toArray();
        $session->fill([
            'check_in_time' => $checkInTime,
            'check_in_method' => 'auto_session',
            'auto_check_in_source_session_id' => $source->id,
            'attendance_status' => $source->attendance_status === 'permission' ? 'permission' : ($lateMinutes > 0 ? 'late' : 'present'),
            'late_minutes' => $lateMinutes,
            'check_in_latitude' => $source->check_in_latitude,
            'check_in_longitude' => $source->check_in_longitude,
        ]);

        $this->recalculate($session);
        $session->save();
        $this->log($session, 'auto_checked_in_from_session_1', $old, $session->fresh()->toArray(), $request, 'Session ' . $session->session_number . ' used session 1 check-in.');
        event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'auto_checked_in'));

        return $session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule', 'autoCheckInSourceSession']);
    }

    private function autoSessionCheckInTime(TeacherAttendanceSession $source, TeacherAttendanceSession $session): Carbon
    {
        $sourceCheckIn = Carbon::parse($source->check_in_time);
        $sessionStart = Carbon::parse($session->scheduled_start_time);

        return $sourceCheckIn->gt($sessionStart) ? $sourceCheckIn : $sessionStart;
    }

    public function autoCheckInLaterSameSubjectSessions(TeacherAttendanceSession $source, ?Request $request = null, ?Carbon $time = null): int
    {
        if ($source->session_number !== 1 || !$source->check_in_time) {
            return 0;
        }

        $sessions = TeacherAttendanceSession::where('teacher_id', $source->teacher_id)
            ->where('subject_id', $source->subject_id)
            ->whereDate('attendance_date', $source->attendance_date)
            ->where('session_number', '>', 1)
            ->whereNull('check_in_time')
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission'])
            ->orderBy('session_number')
            ->get();

        $count = 0;
        foreach ($sessions as $session) {
            $this->checkInFromPriorSameSubjectSession($session, $request, $time);
            $count++;
        }

        return $count;
    }

    public function checkIn(TeacherAttendanceSession $session, ?Request $request = null, string $method = 'manual', ?Carbon $time = null): TeacherAttendanceSession
    {
        $time ??= now();
        if ($session->check_in_time) {
            throw ValidationException::withMessages(['session' => 'This session is already checked in.']);
        }

        if (in_array($method, ['qr', 'location'], true)) {
            $this->validateCheckInWindow($session, $time);
            $this->validateAttendanceLocation($request, 'check-in');
        }

        $old = $session->toArray();

        $lateMinutes = $this->checkInLateMinutes($session, $time);
        $status = $lateMinutes > 0 ? 'late' : 'present';

        $session->fill([
            'check_in_time' => $time,
            'check_in_method' => $method,
            'attendance_status' => $status,
            'late_minutes' => $lateMinutes,
            'check_in_latitude' => $request?->input('latitude'),
            'check_in_longitude' => $request?->input('longitude'),
        ]);

        $this->recalculate($session);
        $session->save();
        $this->log($session, 'checked_in', $old, $session->fresh()->toArray(), $request);
        if ($status === 'late') {
            app(TeacherAttendanceNotificationService::class)->lateCheckIn($session->fresh(['teacher.user', 'subject']));
        }
        event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'checked_in'));

        return $session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']);
    }

    private function checkInLateMinutes(TeacherAttendanceSession $session, Carbon $time): int
    {
        $lateStartsAt = Carbon::parse($session->scheduled_start_time)->addMinutes(self::LATE_GRACE_MINUTES);

        if (!$time->gt($lateStartsAt)) {
            return 0;
        }

        return max(0, $lateStartsAt->diffInMinutes($time, false));
    }

    private function validateCheckInWindow(TeacherAttendanceSession $session, Carbon $time): void
    {
        $attendanceDate = Carbon::parse($session->attendance_date);

        if (!$time->isSameDay($attendanceDate)) {
            throw ValidationException::withMessages([
                'session' => 'Teacher attendance can only be checked in on the scheduled date.',
            ]);
        }

        $opensAt = $session->schedule?->check_in_opens_at
            ? Carbon::parse($session->schedule->check_in_opens_at)
            : Carbon::parse($session->scheduled_start_time);
        $closesAt = Carbon::parse($session->scheduled_end_time);

        if ($time->lt($opensAt) || $time->gt($closesAt)) {
            throw ValidationException::withMessages([
                'session' => 'Teacher attendance check-in is only allowed during the scheduled class time.',
            ]);
        }
    }

    public function checkOut(TeacherAttendanceSession $session, ?Request $request = null, string $method = 'manual', ?Carbon $time = null): TeacherAttendanceSession
    {
        $time ??= now();
        if (!$session->check_in_time && $session->session_number > 1) {
            $session = $this->checkInFromPriorSameSubjectSession($session, $request, $time);
        }

        if (!$session->check_in_time) {
            throw ValidationException::withMessages(['session' => 'Check-in is required before checkout.']);
        }

        if ($session->check_out_time) {
            throw ValidationException::withMessages(['session' => 'This session is already checked out.']);
        }

        $old = $session->toArray();
        $end = Carbon::parse($session->scheduled_end_time);

        $session->fill([
            'check_out_time' => $time,
            'check_out_method' => $method,
            'attendance_status' => $time->lt($end) ? 'early_leave' : 'completed',
            'check_out_latitude' => $request?->input('latitude'),
            'check_out_longitude' => $request?->input('longitude'),
        ]);

        $this->recalculate($session);
        $session->save();
        $this->log($session, 'checked_out', $old, $session->fresh()->toArray(), $request);
        event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'checked_out'));

        if ($session->session_number > 1) {
            $this->autoCheckOutSessionOneForLaterSession($session, $request, $time);
        }

        return $session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']);
    }

    private function autoCheckOutSessionOneForLaterSession(TeacherAttendanceSession $laterSession, ?Request $request, Carbon $time): void
    {
        $sessionOne = TeacherAttendanceSession::where('teacher_id', $laterSession->teacher_id)
            ->where('subject_id', $laterSession->subject_id)
            ->whereDate('attendance_date', $laterSession->attendance_date)
            ->where('session_number', 1)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission', 'absent'])
            ->first();

        if (!$sessionOne) {
            return;
        }

        $old = $sessionOne->toArray();
        $checkOutTime = Carbon::parse($sessionOne->scheduled_end_time);
        if ($checkOutTime->gt($time)) {
            $checkOutTime = $time;
        }

        $sessionOne->fill([
            'check_out_time' => $checkOutTime,
            'check_out_method' => 'system',
            'attendance_status' => $checkOutTime->lt(Carbon::parse($sessionOne->scheduled_end_time)) ? 'early_leave' : 'completed',
            'check_out_latitude' => $request?->input('latitude'),
            'check_out_longitude' => $request?->input('longitude'),
        ]);

        $this->recalculate($sessionOne);
        $sessionOne->save();
        $this->log($sessionOne, 'auto_checked_out_from_later_session', $old, $sessionOne->fresh()->toArray(), $request, 'Session 1 was closed after session ' . $laterSession->session_number . ' checkout.');
        event(new TeacherAttendanceUpdated($sessionOne->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'checked_out'));
    }

    private function validateCheckoutLocation(TeacherAttendanceSession $session, ?Request $request): void
    {
        if (!$this->requiresLocation()) {
            return;
        }

        $this->validateAttendanceLocation($request, 'checkout');

        $checkInLat = $session->check_in_latitude;
        $checkInLng = $session->check_in_longitude;
        $checkOutLat = $request?->input('latitude');
        $checkOutLng = $request?->input('longitude');

        if ($checkOutLat === null || $checkOutLng === null) {
            throw ValidationException::withMessages(['location' => 'Phone location is required for checkout. Allow location access and try again.']);
        }

        if ($checkInLat === null || $checkInLng === null) {
            throw ValidationException::withMessages(['location' => 'This session has no check-in location, so checkout location cannot be verified.']);
        }

        if ($this->distanceMeters((float) $checkInLat, (float) $checkInLng, (float) $checkOutLat, (float) $checkOutLng) > 120) {
            throw ValidationException::withMessages(['location' => 'Checkout location must match the original check-in location.']);
        }
    }

    private function validateAttendanceLocation(?Request $request, string $action): void
    {
        if (!$this->requiresLocation()) {
            return;
        }

        $lat = $request?->input('latitude');
        $lng = $request?->input('longitude');
        $accuracy = $request?->input('accuracy');

        if ($lat === null || $lng === null) {
            throw ValidationException::withMessages(['location' => 'Phone location is required for ' . $action . '. Allow location access and try again.']);
        }

        $campusLat = (float) Setting::get('campus_lat', 11.524012);
        $campusLng = (float) Setting::get('campus_lng', 104.876273);
        $radius = (float) Setting::get('campus_radius_meters', 250);
        $distance = $this->distanceMeters($campusLat, $campusLng, (float) $lat, (float) $lng);

        if ($distance > $radius) {
            throw ValidationException::withMessages(['location' => 'Out of campus range. You are ' . round($distance) . 'm away from the allowed location.']);
        }

        if ($accuracy !== null && (float) $accuracy > 150) {
            throw ValidationException::withMessages(['location' => 'Location accuracy is too low (' . round((float) $accuracy) . 'm). Wait for better GPS signal and try again.']);
        }
    }

    private function requiresLocation(): bool
    {
        return Setting::get('require_location', 'true') === 'true';
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function recalculate(TeacherAttendanceSession $session): void
    {
        $start = Carbon::parse($session->scheduled_start_time);
        $end = Carbon::parse($session->scheduled_end_time);
        $requiredMinutes = max(1, $start->diffInMinutes($end));

        if (in_array($session->attendance_status, ['permission', 'absent'], true)) {
            $session->late_minutes = 0;
            $session->early_leave_minutes = 0;
            $session->teaching_duration_minutes = 0;
            $session->actual_teaching_hours = 0;
            $session->attendance_percentage = 0;

            return;
        }

        if ($session->check_in_time) {
            $checkIn = Carbon::parse($session->check_in_time);
            $session->late_minutes = $this->checkInLateMinutes($session, $checkIn);
        }

        if ($session->check_out_time) {
            $checkOut = Carbon::parse($session->check_out_time);
            $session->early_leave_minutes = max(0, $checkOut->diffInMinutes($end, false));
        }

        if ($session->check_in_time && $session->check_out_time) {
            $checkIn = Carbon::parse($session->check_in_time);
            $checkOut = Carbon::parse($session->check_out_time);
            $session->teaching_duration_minutes = max(0, $checkIn->diffInMinutes($checkOut));
            $session->actual_teaching_hours = round($session->teaching_duration_minutes / 60, 2);
            $session->attendance_percentage = min(100, round(($session->teaching_duration_minutes / $requiredMinutes) * 100, 2));
        } elseif ($session->check_in_time) {
            $now = min(now()->timestamp, $end->timestamp);
            $checkIn = Carbon::parse($session->check_in_time);
            $session->teaching_duration_minutes = max(0, $checkIn->diffInMinutes(Carbon::createFromTimestamp($now)));
            $session->actual_teaching_hours = round($session->teaching_duration_minutes / 60, 2);
        }
    }

    public function markAutomatedStatuses(): array
    {
        $absentSessions = TeacherAttendanceSession::with('schedule')
            ->where('attendance_status', 'scheduled')
            ->whereHas('schedule', fn($q) => $q->where('check_in_closes_at', '<', now()))
            ->get();

        $absent = 0;
        foreach ($absentSessions as $session) {
            $session->attendance_status = 'absent';
            $this->normalizeNonTeachingSession($session);
            event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'absent'));
            $absent++;
        }

        $missingCheckoutSessions = TeacherAttendanceSession::with(['teacher.user', 'subject'])
            ->whereIn('attendance_status', ['present', 'on_time', 'late', 'very_late', 'teaching'])
            ->whereNull('check_out_time')
            ->whereHas('schedule', fn($q) => $q->where('check_out_closes_at', '<', now()))
            ->get();

        $missingCheckout = 0;
        foreach ($missingCheckoutSessions as $session) {
            $session->update(['attendance_status' => 'missing_check_out']);
            app(TeacherAttendanceNotificationService::class)->missingCheckout($session);
            event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'missing_check_out'));
            $missingCheckout++;
        }

        return compact('absent', 'missingCheckout');
    }

    public function approveCorrection(TeacherAttendanceCorrection $correction, Request $request): TeacherAttendanceCorrection
    {
        return DB::transaction(function () use ($correction, $request) {
            $session = $correction->attendanceSession
                ?: ($correction->schedule ? $this->ensureAttendanceSession($correction->schedule, $request->user()?->id) : null);
            if ($session) {
                $old = $session->toArray();
                if ($correction->request_type === 'schedule_change') {
                    $this->applyScheduleChangeCorrection($correction, $session, $request);
                } elseif ($correction->requested_check_in_time) {
                    $session->check_in_time = $correction->requested_check_in_time;
                    $session->check_in_method = 'manual';
                }

                if ($correction->request_type !== 'schedule_change' && $correction->requested_check_out_time) {
                    $session->check_out_time = $correction->requested_check_out_time;
                    $session->check_out_method = 'manual';
                }
                if ($correction->requested_status) {
                    $session->attendance_status = $correction->requested_status;
                }
                if (in_array($session->attendance_status, ['permission', 'absent'], true)) {
                    $this->endNonTeachingSession($session);
                }
                $session->approved_by = $request->user()?->id;
                $this->recalculate($session);
                $session->save();
                $session->schedule?->update([
                    'status' => 'completed',
                    'approved_by' => $request->user()?->id,
                ]);
                if ($session->attendance_status === 'permission') {
                    $this->moveLinkedCourseSessionToEnd($session);
                }
                $this->log($session, 'correction_approved', $old, $session->fresh()->toArray(), $request, $correction->reason);
                event(new TeacherAttendanceUpdated($session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']), 'correction_approved'));
            }

            $correction->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
                'review_note' => $request->input('review_note'),
            ]);
            app(TeacherAttendanceNotificationService::class)->correctionReviewed($correction->fresh(['teacher.user']));

            return $correction->fresh(['teacher.user', 'attendanceSession']);
        });
    }

    private function applyScheduleChangeCorrection(TeacherAttendanceCorrection $correction, TeacherAttendanceSession $session, Request $request): void
    {
        $schedule = $correction->schedule ?: $session->schedule;
        if (!$schedule) {
            return;
        }

        $start = $correction->requested_check_in_time
            ? Carbon::parse($correction->requested_check_in_time)
            : Carbon::parse($schedule->scheduled_start_time);
        $end = $correction->requested_check_out_time
            ? Carbon::parse($correction->requested_check_out_time)
            : Carbon::parse($schedule->scheduled_end_time);

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'requested_check_out_time' => 'The requested end time must be after the requested start time.',
            ]);
        }

        $schedule->update([
            'schedule_date' => $start->toDateString(),
            'scheduled_start_time' => $start,
            'scheduled_end_time' => $end,
            'check_in_opens_at' => $start->copy()->subMinutes(30),
            'check_in_closes_at' => $end->copy(),
            'check_out_opens_at' => $end->copy()->subMinutes(15),
            'check_out_closes_at' => $end->copy()->addMinutes(60),
            'approved_by' => $request->user()?->id,
        ]);

        $session->attendance_date = $start->toDateString();
        $session->scheduled_start_time = $start;
        $session->scheduled_end_time = $end;
    }

    private function moveLinkedCourseSessionToEnd(TeacherAttendanceSession $teacherSession): void
    {
        $sourceSession = $teacherSession->schedule?->sourceAttendanceSession;
        if (!$sourceSession) {
            return;
        }

        $nextSlot = $this->calculateNextCourseSlotData($sourceSession);
        if (!$nextSlot) {
            throw ValidationException::withMessages([
                'session' => 'Permission approved, but the linked course session could not be moved to the end of the semester.',
            ]);
        }

        $sourceSession->update([
            'status' => 'skipped',
        ]);

        $replacementSession = AttendanceSession::firstOrCreate([
            'class_id' => $sourceSession->class_id,
            'start_time' => $nextSlot['start_time'],
            'end_time' => $nextSlot['end_time'],
            'semester' => $sourceSession->semester,
            'academic_year' => $sourceSession->academic_year,
        ], [
            'checkin_open_time' => $nextSlot['checkin_open_time'],
            'checkin_close_time' => $nextSlot['checkin_close_time'],
            'status' => 'scheduled',
            'qr_token' => bin2hex(random_bytes(8)),
        ]);

        if (!$replacementSession->wasRecentlyCreated) {
            $replacementSession->forceFill([
                'checkin_open_time' => $nextSlot['checkin_open_time'],
                'checkin_close_time' => $nextSlot['checkin_close_time'],
                'status' => 'scheduled',
            ])->save();
        }

        $this->createTeacherScheduleForReplacementCourseSession($teacherSession, $replacementSession);

        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "session#{$sourceSession->id}.teacher_permission_skipped_replacement#{$replacementSession->id}",
        ]);
    }

    private function createTeacherScheduleForReplacementCourseSession(TeacherAttendanceSession $sourceTeacherSession, AttendanceSession $replacementSession): void
    {
        $sourceSchedule = $sourceTeacherSession->schedule;
        $class = $replacementSession->classRoom;

        if (!$sourceSchedule || !$class || !$class->teacher_id) {
            return;
        }

        $start = Carbon::parse($replacementSession->start_time);
        $end = Carbon::parse($replacementSession->end_time);
        $group = $class->groups?->first();

        $schedule = TeacherSchedule::firstOrCreate(
            ['source_attendance_session_id' => $replacementSession->id],
            [
                'teacher_id' => $class->teacher_id,
                'subject_id' => $class->subject_id,
                'class_id' => $class->id,
                'class_group_id' => $group?->id ?? $class->group_id ?? $sourceSchedule->class_group_id,
                'room_name' => $class->room_number ?? $sourceSchedule->room_name,
                'schedule_date' => $start->toDateString(),
                'scheduled_start_time' => $start,
                'scheduled_end_time' => $end,
                'session_number' => $this->nextSessionNumber($class->teacher_id, $start),
                'check_in_opens_at' => $start->copy()->subMinutes(30),
                'check_in_closes_at' => $end->copy(),
                'check_out_opens_at' => $end->copy()->subMinutes(15),
                'check_out_closes_at' => $end->copy()->addMinutes(60),
                'semester' => $replacementSession->semester ?? $sourceSchedule->semester,
                'academic_year' => $replacementSession->academic_year ?? $sourceSchedule->academic_year,
                'status' => 'scheduled',
                'source' => 'generated',
                'created_by' => $sourceTeacherSession->approved_by,
                'approved_by' => $sourceTeacherSession->approved_by,
                'remarks' => 'Replacement for teacher permission session #' . $sourceTeacherSession->id,
            ]
        );

        if (!$schedule->wasRecentlyCreated) {
            $schedule->forceFill([
                'teacher_id' => $class->teacher_id,
                'subject_id' => $class->subject_id,
                'class_id' => $class->id,
                'class_group_id' => $group?->id ?? $class->group_id ?? $sourceSchedule->class_group_id,
                'room_name' => $class->room_number ?? $sourceSchedule->room_name,
                'schedule_date' => $start->toDateString(),
                'scheduled_start_time' => $start,
                'scheduled_end_time' => $end,
                'session_number' => $this->nextSessionNumber($class->teacher_id, $start, $schedule->id),
                'check_in_opens_at' => $start->copy()->subMinutes(30),
                'check_in_closes_at' => $end->copy(),
                'check_out_opens_at' => $end->copy()->subMinutes(15),
                'check_out_closes_at' => $end->copy()->addMinutes(60),
                'semester' => $replacementSession->semester ?? $sourceSchedule->semester,
                'academic_year' => $replacementSession->academic_year ?? $sourceSchedule->academic_year,
                'status' => 'scheduled',
                'approved_by' => $sourceTeacherSession->approved_by,
            ])->save();
        }

        $this->ensureAttendanceSession($schedule, $sourceTeacherSession->approved_by);
    }

    private function calculateNextCourseSlotData(AttendanceSession $session): ?array
    {
        $class = $session->classRoom;
        if (!$class) {
            return null;
        }

        $lastSession = AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $session->academic_year)
            ->where('semester', $session->semester)
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$lastSession) {
            return null;
        }

        [$allowedDays, $timeSlots] = $this->parseCourseSchedule($class->schedule);
        if (empty($allowedDays) || empty($timeSlots)) {
            return null;
        }

        $currentDate = Carbon::parse($lastSession->start_time)->startOfDay();
        $lastSlotIndex = -1;
        $lastStartTime = Carbon::parse($lastSession->start_time)->format('H:i');

        foreach ($timeSlots as $index => $slot) {
            if ($slot['start'] === $lastStartTime) {
                $lastSlotIndex = $index;
                break;
            }
        }

        $nextSlotIndex = $lastSlotIndex + 1;
        if ($nextSlotIndex >= count($timeSlots)) {
            $nextSlotIndex = 0;
            $currentDate->addDay();
        }

        $assignment = SemesterAssignment::where('class_id', $class->id)
            ->where('academic_year', $session->academic_year)
            ->where('semester', $session->semester)
            ->first();

        $maxIterations = 60;
        while ($maxIterations > 0) {
            $maxIterations--;

            if (in_array($currentDate->dayOfWeek, $allowedDays, true)) {
                $inHoliday = $assignment
                    && $assignment->holiday_start
                    && $assignment->holiday_end
                    && $currentDate->between($assignment->holiday_start, $assignment->holiday_end);

                if (!$inHoliday) {
                    $slot = $timeSlots[$nextSlotIndex];
                    $start = $currentDate->copy()->setTimeFromTimeString($slot['start']);
                    $end = $currentDate->copy()->setTimeFromTimeString($slot['end']);

                    return [
                        'start_time' => $start,
                        'end_time' => $end,
                        'checkin_open_time' => $start->copy()->subMinutes(20),
                        'checkin_close_time' => $start->copy()->addMinutes(20),
                    ];
                }
            }

            $currentDate->addDay();
            $nextSlotIndex = 0;
        }

        return null;
    }

    private function parseCourseSchedule(?string $schedule): array
    {
        $schedule = strtolower($schedule ?? '');
        if (!$schedule) {
            return [[], []];
        }

        $daysMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $allowedDays = [];

        if (str_contains($schedule, 'mon-fri') || str_contains($schedule, 'weekday')) {
            $allowedDays = [1, 2, 3, 4, 5];
        } elseif (str_contains($schedule, 'sat/sun') || str_contains($schedule, 'weekend')) {
            $allowedDays = [6, 0];
        } elseif (str_contains($schedule, 'everyday') || str_contains($schedule, 'full-week')) {
            $allowedDays = [0, 1, 2, 3, 4, 5, 6];
        } elseif (preg_match('/(mon|tue|wed|thu|fri|sat|sun)\s?[-–—]\s?(mon|tue|wed|thu|fri|sat|sun)/i', $schedule, $matches)) {
            $start = $daysMap[strtolower($matches[1])];
            $end = $daysMap[strtolower($matches[2])];

            if ($start <= $end) {
                for ($day = $start; $day <= $end; $day++) {
                    $allowedDays[] = $day;
                }
            } else {
                for ($day = $start; $day <= 6; $day++) {
                    $allowedDays[] = $day;
                }
                for ($day = 0; $day <= $end; $day++) {
                    $allowedDays[] = $day;
                }
            }
        } else {
            foreach ($daysMap as $dayName => $dayNumber) {
                if (str_contains($schedule, $dayName)) {
                    $allowedDays[] = $dayNumber;
                }
            }
        }

        if (empty($allowedDays)) {
            $allowedDays = [1, 2, 3, 4, 5];
        }

        preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s?([AP]M)?\s?[-–—]\s?(\d{1,2}:\d{2}(?::\d{2})?)\s?([AP]M)?/i', $schedule, $matches, PREG_SET_ORDER);
        $timeSlots = [];

        foreach ($matches as $match) {
            try {
                $timeSlots[] = [
                    'start' => Carbon::parse($match[1] . ($match[2] ?? ''))->format('H:i'),
                    'end' => Carbon::parse($match[3] . ($match[4] ?? ''))->format('H:i'),
                ];
            } catch (\Exception) {
                continue;
            }
        }

        return [$allowedDays, $timeSlots];
    }

    public function rejectCorrection(TeacherAttendanceCorrection $correction, Request $request): TeacherAttendanceCorrection
    {
        $correction->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note'),
        ]);

        if ($correction->attendanceSession) {
            $this->log($correction->attendanceSession, 'correction_rejected', null, $correction->toArray(), $request, $request->input('review_note'));
        }
        app(TeacherAttendanceNotificationService::class)->correctionReviewed($correction->fresh(['teacher.user']));

        return $correction->fresh(['teacher.user', 'attendanceSession']);
    }

    public function normalizeNonTeachingSession(TeacherAttendanceSession $session, ?int $actorId = null): TeacherAttendanceSession
    {
        if (!in_array($session->attendance_status, ['permission', 'absent'], true)) {
            return $session;
        }

        $this->endNonTeachingSession($session);
        $this->recalculate($session);
        $session->approved_by = $actorId;
        $session->save();
        $session->schedule?->update([
            'status' => 'completed',
            'approved_by' => $actorId,
        ]);

        return $session->fresh(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']);
    }

    private function endNonTeachingSession(TeacherAttendanceSession $session): void
    {
        $session->check_in_time = $session->scheduled_start_time;
        $session->check_out_time = $session->scheduled_end_time;
        $session->check_in_method = $session->check_in_method ?: 'system';
        $session->check_out_method = $session->check_out_method ?: 'system';
        $session->late_minutes = 0;
        $session->early_leave_minutes = 0;
        $session->teaching_duration_minutes = 0;
        $session->actual_teaching_hours = 0;
        $session->attendance_percentage = 0;
    }

    public function approveClassChange(TeacherClassChangeRequest $changeRequest, Request $request): TeacherClassChangeRequest
    {
        return DB::transaction(function () use ($changeRequest, $request) {
            $schedule = $changeRequest->schedule;
            $session = $schedule->attendanceSession ?: $this->ensureAttendanceSession($schedule, $request->user()?->id);
            $sessionAction = $request->input('session_action', 'skip_reschedule');

            if (!in_array($sessionAction, ['skip_reschedule', 'skip_only'], true)) {
                $sessionAction = 'skip_reschedule';
            }

            if ($changeRequest->request_type === 'cancellation' || $sessionAction === 'skip_only') {
                $schedule->update(['status' => 'cancelled', 'approved_by' => $request->user()?->id]);
                $session->update(['attendance_status' => 'cancelled', 'approved_by' => $request->user()?->id]);
                $this->log($session, 'schedule_cancelled', null, $changeRequest->toArray(), $request, $changeRequest->reason);
            } else {
                $date = $changeRequest->requested_date ?: $schedule->schedule_date;
                $start = $changeRequest->requested_start_time ? Carbon::parse($changeRequest->requested_start_time) : Carbon::parse($schedule->scheduled_start_time);
                $end = $changeRequest->requested_end_time ? Carbon::parse($changeRequest->requested_end_time) : Carbon::parse($schedule->scheduled_end_time);

                $replacement = TeacherSchedule::create([
                    'teacher_id' => $schedule->teacher_id,
                    'subject_id' => $schedule->subject_id,
                    'class_id' => $schedule->class_id,
                    'class_group_id' => $schedule->class_group_id,
                    'room_name' => $changeRequest->requested_room_name ?: $schedule->room_name,
                    'schedule_date' => Carbon::parse($date)->toDateString(),
                    'scheduled_start_time' => $start,
                    'scheduled_end_time' => $end,
                    'session_number' => $this->nextSessionNumber($schedule->teacher_id, $start),
                    'check_in_opens_at' => $start->copy()->subMinutes(30),
                    'check_in_closes_at' => $end->copy(),
                    'check_out_opens_at' => $end->copy()->subMinutes(15),
                    'check_out_closes_at' => $end->copy()->addMinutes(60),
                    'semester' => $schedule->semester,
                    'academic_year' => $schedule->academic_year,
                    'status' => 'scheduled',
                    'source' => 'manual',
                    'original_schedule_id' => $schedule->id,
                    'created_by' => $request->user()?->id,
                    'approved_by' => $request->user()?->id,
                    'remarks' => 'Replacement for schedule #' . $schedule->id,
                ]);

                $this->ensureAttendanceSession($replacement, $request->user()?->id);
                $schedule->update(['status' => 'rescheduled', 'approved_by' => $request->user()?->id]);
                $session->update(['attendance_status' => 'rescheduled', 'approved_by' => $request->user()?->id]);
                $changeRequest->replacement_schedule_id = $replacement->id;
                $this->log($session, 'schedule_rescheduled', null, $changeRequest->toArray(), $request, $changeRequest->reason);
            }

            $changeRequest->status = 'approved';
            $changeRequest->reviewed_by = $request->user()?->id;
            $changeRequest->reviewed_at = now();
            $changeRequest->review_note = $request->input('review_note');
            $changeRequest->save();
            app(TeacherAttendanceNotificationService::class)->classChangeReviewed($changeRequest->fresh(['teacher.user']));

            return $changeRequest->fresh(['teacher.user', 'schedule', 'replacementSchedule']);
        });
    }

    public function rejectClassChange(TeacherClassChangeRequest $changeRequest, Request $request): TeacherClassChangeRequest
    {
        $changeRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note'),
        ]);

        if ($changeRequest->schedule?->attendanceSession) {
            $this->log($changeRequest->schedule->attendanceSession, 'schedule_change_rejected', null, $changeRequest->toArray(), $request, $request->input('review_note'));
        }
        app(TeacherAttendanceNotificationService::class)->classChangeReviewed($changeRequest->fresh(['teacher.user']));

        return $changeRequest->fresh(['teacher.user', 'schedule']);
    }

    public function teacherAttendancePercentage(Teacher $teacher, Carbon $from, Carbon $to): float
    {
        $sessions = TeacherAttendanceSession::where('teacher_id', $teacher->id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled'])
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $valid = $sessions->whereIn('attendance_status', self::VALID_PRESENT_STATUSES)->count();
        return round(($valid / $sessions->count()) * 100, 2);
    }

    public function log(TeacherAttendanceSession $session, string $action, ?array $oldValues = null, ?array $newValues = null, ?Request $request = null, ?string $remarks = null): void
    {
        TeacherAttendanceLog::create([
            'teacher_attendance_session_id' => $session->id,
            'teacher_id' => $session->teacher_id,
            'actor_id' => $request?->user()?->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }

    private function nextSessionNumber(int $teacherId, Carbon $start, ?int $ignoreScheduleId = null): int
    {
        $query = TeacherSchedule::where('teacher_id', $teacherId)
            ->whereDate('schedule_date', $start->toDateString());

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query
            ->whereDate('scheduled_start_time', $start->toDateString())
            ->whereTime('scheduled_start_time', '<', $start->format('H:i:s'))
            ->exists() ? 2 : 1;
    }
}
