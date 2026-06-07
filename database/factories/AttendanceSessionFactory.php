<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 days', '+10 days');
        $end = (clone $start)->modify('+2 hours');

        return [
            'class_id' => ClassRoom::factory(),
            'qr_token' => Str::random(32),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }
}
