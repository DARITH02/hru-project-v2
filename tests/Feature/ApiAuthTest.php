<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_login_returns_teacher_payload(): void
    {
        $department = Department::factory()->create();

        $user = User::factory()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'password' => 'password',
            'role' => 'teacher',
            'is_approved' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => 'Programming',
            'status' => 'active',
        ]);

        $this->postJson('/api/login', [
            'email' => 'teacher@example.com',
            'password' => 'password',
            'device_name' => 'react-localhost',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.role', 'teacher')
            ->assertJsonPath('user.student', null)
            ->assertJsonPath('user.teacher.id', $teacher->id)
            ->assertJsonPath('user.teacher.department', $department->name)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'student',
                    'teacher',
                ],
            ]);
    }
}
