<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => fn() => User::factory()->state(['role' => 'teacher']),
            'department_id' => fn() => \App\Models\Department::factory(),
            'specialization' => $this->faker->jobTitle(),
            'phone' => $this->faker->phoneNumber(),
            'status' => 'active'
        ];
    }
}
