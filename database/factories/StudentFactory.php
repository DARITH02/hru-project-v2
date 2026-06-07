<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'student_code' => fake()->unique()->numerify('STD-#####'),
            'class_id' => fn() => ClassRoom::factory(),
            'major' => fake()->randomElement(['Software Engineering', 'Data Science', 'Business IT', 'Fine Arts', 'Accounting']),
            'year_level' => fake()->numberBetween(1, 4),
            'status' => 'active'
        ];
    }
}
