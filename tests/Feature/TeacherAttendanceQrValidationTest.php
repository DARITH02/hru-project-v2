<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherSchedule;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherAttendanceQrValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('require_location', 'false');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_teacher_can_qr_check_in_during_scheduled_class_time(): void
    {
        $service = app(TeacherAttendanceService::class);
        $teacher = $this->teacher();
        $session = $this->teacherAttendanceSession($teacher);

        Carbon::setTestNow(Carbon::parse('2026-06-22 17:29:00'));
        $qr = $service->generateQrToken($session, 300);

        $checkedIn = $service->qrCheckIn($qr['token'], $teacher, null, Carbon::parse('2026-06-22 17:30:00'));

        $this->assertTrue($checkedIn->check_in_time->isSameMinute(Carbon::parse('2026-06-22 17:30:00')));
        $this->assertSame('late', $checkedIn->attendance_status);
    }

    public function test_teacher_cannot_qr_check_in_on_a_different_day(): void
    {
        $service = app(TeacherAttendanceService::class);
        $teacher = $this->teacher();
        $session = $this->teacherAttendanceSession($teacher);

        Carbon::setTestNow(Carbon::parse('2026-06-22 17:29:00'));
        $qr = $service->generateQrToken($session, 172800);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Teacher attendance can only be checked in on the scheduled date.');

        $service->qrCheckIn($qr['token'], $teacher, null, Carbon::parse('2026-06-23 17:30:00'));
    }

    private function teacherAttendanceSession(Teacher $teacher): TeacherAttendanceSession
    {
        $subject = Subject::factory()->create();

        $schedule = TeacherSchedule::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'schedule_date' => '2026-06-22',
            'scheduled_start_time' => '2026-06-22 17:00:00',
            'scheduled_end_time' => '2026-06-22 18:00:00',
            'session_number' => 1,
            'check_in_opens_at' => '2026-06-22 17:00:00',
            'check_in_closes_at' => '2026-06-22 18:00:00',
            'check_out_opens_at' => '2026-06-22 18:00:00',
            'check_out_closes_at' => '2026-06-22 19:00:00',
            'status' => 'scheduled',
            'source' => 'manual',
        ]);

        return TeacherAttendanceSession::create([
            'teacher_id' => $teacher->id,
            'schedule_id' => $schedule->id,
            'subject_id' => $subject->id,
            'attendance_date' => '2026-06-22',
            'scheduled_start_time' => '2026-06-22 17:00:00',
            'scheduled_end_time' => '2026-06-22 18:00:00',
            'session_number' => 1,
            'attendance_status' => 'scheduled',
        ]);
    }

    private function teacher(): Teacher
    {
        return Teacher::create([
            'user_id' => User::factory()->create(['role' => 'teacher'])->id,
            'status' => 'active',
        ]);
    }
}
