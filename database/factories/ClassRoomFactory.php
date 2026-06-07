<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassRoomFactory extends Factory
{
    protected $model = ClassRoom::class;

    public function definition(): array
    {
        return [
            'name' => fake()->bothify('Class-##?'),
            'subject_id' => fn() => Subject::factory(),
            'teacher_id' => fn() => Teacher::factory(),
            'room_number' => fake()->randomElement(['Auditorium A', 'Lab 1', 'Room 302', 'Main Hall']),
            'semester' => 2,
            'academic_year' => '2025-2026',
        ];
    }
}
