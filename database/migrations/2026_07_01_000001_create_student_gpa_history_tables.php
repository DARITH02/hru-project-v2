<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_semester_gpa_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignId('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->string('student_name');
            $table->string('student_code');
            $table->string('class_group_name')->nullable();
            $table->string('major_name')->nullable();
            $table->string('academic_year', 20);
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->unsignedTinyInteger('semester');
            $table->decimal('total_credits', 8, 2)->default(0);
            $table->decimal('total_grade_points', 10, 2)->default(0);
            $table->decimal('semester_gpa', 4, 2)->default(0);
            $table->decimal('cumulative_credits', 8, 2)->default(0);
            $table->decimal('cumulative_grade_points', 10, 2)->default(0);
            $table->decimal('cumulative_gpa', 4, 2)->default(0);
            $table->string('result_status')->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year', 'semester'], 'uniq_student_semester_gpa');
            $table->index(['academic_year', 'semester']);
        });

        Schema::create('student_subject_grade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_gpa_history_id')
                ->constrained('student_semester_gpa_histories')
                ->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('semester_assignments')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('class_name')->nullable();
            $table->string('subject_name');
            $table->string('subject_code')->nullable();
            $table->decimal('credit', 5, 2)->default(1);
            $table->decimal('attendance_score', 5, 2)->default(0);
            $table->decimal('midterm_score', 5, 2)->default(0);
            $table->decimal('assignment_score', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('letter_grade', 5);
            $table->decimal('grade_point', 4, 2)->default(0);
            $table->decimal('quality_points', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['semester_gpa_history_id', 'assignment_id'], 'uniq_history_assignment_grade');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_grade_histories');
        Schema::dropIfExists('student_semester_gpa_histories');
    }
};
