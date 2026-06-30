<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use Carbon\Carbon;
use Exception;

class AttendanceService
{
    /** 
     * Core validation for check-in timing.
     */
    public function validateCheckinWindow(AttendanceSession $session)
    {
        $now = now();
        $open = Carbon::parse($session->checkin_open_time);
        // Use the new column, or fallback to 20 mins after end_time if null
        $close = $session->checkin_close_time ? Carbon::parse($session->checkin_close_time) : Carbon::parse($session->end_time)->addMinutes(20);

        if ($now->lt($open)) {
            throw new Exception("Too early! Check-in starts at " . $open->format('H:i'));
        }

        if ($now->gt($close)) {
            throw new Exception("Attendance Closed at " . $close->format('H:i') . ". The grace period has ended.");
        }

        return true;
    }

    /**
     * Process a student check-in with University-grade (HRU) token verification and Geofencing.
     */
    public function processCheckin($sessionId, $studentCode, $qrToken = null, $latitude = null, $longitude = null, $accuracy = null)
    {
        $session = AttendanceSession::with('classRoom')->findOrFail($sessionId);

        // 1. Validate Time Window
        $this->validateCheckinWindow($session);

        // 2. 🛡️ SECURITY: Validate QR Token Integrity
        if (!$qrToken || $qrToken !== $session->qr_token) {
            throw new Exception("Invalid or expired QR token. Please scan the latest code on the teacher's screen.");
        }

        // 3. 📍 LOCATION VALIDATION (GEOFENCING)
        $this->validateLocation($latitude, $longitude, $accuracy);

        // 4. Validate Student via Groups
        $groupIds = $session->classRoom->groups->pluck('id');
        if ($groupIds->isEmpty() && $session->classRoom->group_id) {
            $groupIds = collect([$session->classRoom->group_id]);
        }
        $student = Student::where('student_code', $studentCode)
            ->whereIn('group_id', $groupIds)
            ->first();

        if (!$student) {
            throw new Exception("Invalid Student Code or not enrolled in this class.");
        }

        // 5. Prevent Duplicates
        $existing = Attendance::where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->first();

        if ($existing && in_array($existing->status, ['present', 'late'])) {
            throw new Exception("Attendance already recorded.");
        }

        // 6. Determine Status (Late after 15 minutes - using setting if available)
        $graceMinutes = \App\Models\Setting::get('grace_period', 15);
        $start = Carbon::parse($session->start_time);
        $status = now()->gt($start->addMinutes($graceMinutes)) ? 'late' : 'present';

        // 7. Create/Update Attendance Record
        $attendance = Attendance::updateOrCreate(
            ['student_id' => $student->id, 'session_id' => $session->id],
            [
                'status'    => $status,
                'method'    => 'qr',
                'scan_time' => now()
            ]
        );

        // 8. 📝 Audit Log: Record the User Location
        if ($latitude && $longitude) {
            \App\Models\UserLocation::create([
                'user_id'    => null, // Student is anonymous via code usually, or we can find user if linked
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'accuracy'   => $accuracy,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        }

        return $attendance;
    }

    public function processCheckinByStudentId($sessionId, $studentId, $qrToken = null, $latitude = null, $longitude = null, $accuracy = null)
    {
        $session = AttendanceSession::with('classRoom.groups')->findOrFail($sessionId);

        $this->validateCheckinWindow($session);

        if (!$qrToken || $qrToken !== $session->qr_token) {
            throw new Exception("Invalid or expired QR token. Please scan the latest code on the teacher's screen.");
        }

        $this->validateLocation($latitude, $longitude, $accuracy);

        $groupIds = $session->classRoom?->groups?->pluck('id') ?? collect();
        if ($groupIds->isEmpty() && $session->classRoom?->group_id) {
            $groupIds = collect([$session->classRoom->group_id]);
        }
        $student = Student::where('id', $studentId)
            ->whereIn('group_id', $groupIds)
            ->first();

        if (!$student) {
            throw new Exception("This student is not enrolled in this class.");
        }

        $existing = Attendance::where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->first();

        if ($existing && in_array($existing->status, ['present', 'late'])) {
            throw new Exception("Attendance already recorded.");
        }

        $graceMinutes = \App\Models\Setting::get('grace_period', 15);
        $start = Carbon::parse($session->start_time);
        $status = now()->gt($start->addMinutes($graceMinutes)) ? 'late' : 'present';

        $attendance = Attendance::updateOrCreate(
            ['student_id' => $student->id, 'session_id' => $session->id],
            [
                'status'    => $status,
                'method'    => 'qr',
                'scan_time' => now()
            ]
        );

        if ($latitude && $longitude) {
            \App\Models\UserLocation::create([
                'user_id'    => $student->user_id,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'accuracy'   => $accuracy,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        }

        return $attendance;
    }

    /**
     * Validate if the provided coordinates are within campus bounds.
     */
    protected function validateLocation($lat, $lng, $accuracy)
    {
        // 1. Check if location data is required
        $isLocationRequired = \App\Models\Setting::get('require_location', 'true') === 'true';
        if (!$isLocationRequired) return true;

        if (!$lat || !$lng) {
            throw new Exception("Location access is required for check-in. Please enable GPS.");
        }

        // 2. Get Campus Settings (Default to Human Resources University coordinates if empty)
        $campusLat = \App\Models\Setting::get('campus_lat', 11.524012);
        $campusLng = \App\Models\Setting::get('campus_lng', 104.876273);
        $radius    = \App\Models\Setting::get('campus_radius_meters', 250); // 250m radius

        // 3. Create a temporary UserLocation instance for calculation
        $loc = new \App\Models\UserLocation([
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy
        ]);

        // 4. Perform distance check
        $distance = $loc->getDistanceTo($campusLat, $campusLng);

        if ($distance > $radius) {
            $readableDist = round($distance);
            throw new Exception("Out of range! You are {$readableDist}m away from campus. Location check failed.");
        }

        // 5. Accuracy check (Optional but recommended)
        if ($accuracy && $accuracy > 150) {
            throw new Exception("Location accuracy is too low ({$accuracy}m). Please wait for a better GPS signal.");
        }

        return true;
    }
}
