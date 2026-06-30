<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('teacher_schedules')
            ->whereColumn('check_in_closes_at', '<', 'scheduled_end_time')
            ->orderBy('id')
            ->chunkById(200, function ($schedules) {
                foreach ($schedules as $schedule) {
                    DB::table('teacher_schedules')
                        ->where('id', $schedule->id)
                        ->update([
                            'check_in_closes_at' => $schedule->scheduled_end_time,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
