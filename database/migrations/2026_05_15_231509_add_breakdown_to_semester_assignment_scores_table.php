<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('semester_assignment_scores', function (Blueprint $table) {
            $table->decimal('attendance_score', 5, 2)->default(0)->after('student_id');
            $table->decimal('midterm_score', 5, 2)->default(0)->after('attendance_score');
            $table->decimal('assignment_score', 5, 2)->default(0)->after('midterm_score');
            $table->decimal('final_score', 5, 2)->default(0)->after('assignment_score');
        });
    }

    public function down(): void
    {
        Schema::table('semester_assignment_scores', function (Blueprint $table) {
            $table->dropColumn(['attendance_score', 'midterm_score', 'assignment_score', 'final_score']);
        });
    }
};
