<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_assignments', function (Blueprint $table) {
            $table->decimal('admin_score', 8, 2)->nullable()->after('notes');
            $table->string('grading_status')->default('pending')->after('admin_score'); // pending, reviewed, finalized
            $table->text('grading_notes')->nullable()->after('grading_status');
            $table->decimal('final_attendance_rate', 5, 2)->nullable()->after('grading_notes');
            $table->integer('final_total_sessions')->nullable()->after('final_attendance_rate');
            $table->timestamp('finalized_at')->nullable()->after('final_total_sessions');
        });
    }

    public function down(): void
    {
        Schema::table('semester_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'admin_score', 'grading_status', 'grading_notes',
                'final_attendance_rate', 'final_total_sessions', 'finalized_at'
            ]);
        });
    }
};
