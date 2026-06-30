<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Services\SemesterAttendanceScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterAttendanceScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_permission_sessions_credit_the_same_as_one_present_session(): void
    {
        $student = Student::factory()->create();
        $sessions = collect([
            $this->sessionOn('2026-07-01'),
            $this->sessionOn('2026-07-02'),
        ]);

        StudentPermission::withoutGlobalScope('approved')->create([
            'student_id' => $student->id,
            'attendance_session_id' => $sessions[0]->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-01',
            'status' => 'approved',
        ]);

        StudentPermission::withoutGlobalScope('approved')->create([
            'student_id' => $student->id,
            'attendance_session_id' => $sessions[1]->id,
            'start_date' => '2026-07-02',
            'end_date' => '2026-07-02',
            'status' => 'approved',
        ]);

        $result = app(SemesterAttendanceScoreService::class)->calculate($student->id, $sessions);

        $this->assertSame(2, $result['permission_sessions']);
        $this->assertSame(1.0, $result['permission_credit_sessions']);
        $this->assertSame(1.0, $result['permission_absence_units']);
        $this->assertSame(1.0, $result['credited_sessions']);
        $this->assertSame(3.3, $result['rate']);
        $this->assertSame(0.67, $result['score']);
    }

    public function test_one_present_session_scores_one_attendance_unit(): void
    {
        $student = Student::factory()->create();
        $session = $this->sessionOn('2026-07-01');

        Attendance::factory()->create([
            'student_id' => $student->id,
            'session_id' => $session->id,
            'status' => 'present',
        ]);

        $result = app(SemesterAttendanceScoreService::class)->calculate($student->id, collect([$session]));

        $this->assertSame(1, $result['attended_sessions']);
        $this->assertSame(1, $result['credited_sessions']);
        $this->assertSame(3.3, $result['rate']);
        $this->assertSame(0.67, $result['score']);
    }

    private function sessionOn(string $date): AttendanceSession
    {
        return AttendanceSession::factory()->create([
            'start_time' => "{$date} 08:00:00",
            'end_time' => "{$date} 10:00:00",
            'status' => 'completed',
        ]);
    }
}
