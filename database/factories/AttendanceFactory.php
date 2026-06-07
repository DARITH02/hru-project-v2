<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'session_id' => AttendanceSession::factory(),
            'status' => fake()->randomElement(['present', 'absent', 'late']),
            'method' => fake()->randomElement(['qr', 'manual']),
            'scan_time' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
