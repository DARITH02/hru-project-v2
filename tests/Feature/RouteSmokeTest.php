<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Services\MaintenanceModeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_root_returns_status(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'HRU ATS API is running.');
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_student_overview_requires_authentication(): void
    {
        $this->get('/admin/students/overview')->assertRedirect('/login');
    }

    public function test_teacher_visiting_admin_redirects_to_teacher_attendance_without_loop(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'is_approved' => true,
        ]);

        $this->actingAs($teacher)
            ->get('/admin')
            ->assertRedirect(route('teacher.attendance'));
    }

    public function test_student_visiting_admin_redirects_to_student_overview_without_loop(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
        ]);

        $this->actingAs($student)
            ->get('/admin')
            ->assertRedirect(route('admin.students.overview'));
    }

    public function test_teacher_attendance_pages_render(): void
    {
        $teacher = Teacher::create([
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'is_approved' => true,
            ])->id,
            'status' => 'active',
        ]);

        foreach ([
            '/teacher/attendance',
            '/teacher/attendance/scan',
            '/teacher/attendance/checkout',
            '/teacher/reports',
        ] as $uri) {
            $this->actingAs($teacher->user)
                ->get($uri)
                ->assertOk();
        }
    }

    public function test_guest_dashboard_request_redirects_to_login_during_maintenance(): void
    {
        app(MaintenanceModeService::class)->enable('Restore in progress.');

        $this->get('/admin')
            ->assertRedirect('/login');
    }
}
