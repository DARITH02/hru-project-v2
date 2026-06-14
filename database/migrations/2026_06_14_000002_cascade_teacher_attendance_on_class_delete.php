<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
        });

        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });

        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
    }
};
