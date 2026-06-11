<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->string('room_name')->nullable();
            $table->date('schedule_date');
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->unsignedSmallInteger('session_number')->default(1);
            $table->dateTime('check_in_opens_at');
            $table->dateTime('check_in_closes_at');
            $table->dateTime('check_out_opens_at')->nullable();
            $table->dateTime('check_out_closes_at')->nullable();
            $table->string('semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->enum('status', ['scheduled', 'cancelled', 'rescheduled', 'completed'])->default('scheduled');
            $table->enum('source', ['manual', 'import', 'generated'])->default('manual');
            $table->foreignId('source_attendance_session_id')->nullable()->unique()->constrained('attendance_sessions')->nullOnDelete();
            $table->foreignId('original_schedule_id')->nullable()->constrained('teacher_schedules')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'schedule_date'], 'teacher_schedule_teacher_date_idx');
            $table->index(['teacher_id', 'subject_id', 'schedule_date', 'session_number'], 'teacher_schedule_subject_date_session_idx');
            $table->index(['semester', 'academic_year'], 'teacher_schedule_period_idx');
            $table->index('status');
        });

        Schema::create('teacher_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('schedule_id')->unique()->constrained('teacher_schedules')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->string('room_name')->nullable();
            $table->date('attendance_date');
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->unsignedSmallInteger('session_number')->default(1);
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->unsignedBigInteger('auto_check_in_source_session_id')->nullable();
            $table->enum('attendance_status', [
                'scheduled',
                'present',
                'on_time',
                'late',
                'very_late',
                'teaching',
                'completed',
                'early_leave',
                'absent',
                'permission',
                'cancelled',
                'rescheduled',
                'missing_check_out',
            ])->default('scheduled');
            $table->enum('check_in_method', ['qr', 'manual', 'location', 'system', 'auto_session'])->nullable();
            $table->enum('check_out_method', ['qr', 'manual', 'location', 'system'])->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('teaching_duration_minutes')->default(0);
            $table->decimal('actual_teaching_hours', 6, 2)->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_id', 'attendance_date']);
            $table->index(['teacher_id', 'subject_id', 'attendance_date', 'session_number'], 'teacher_subject_date_session_idx');
            $table->index('attendance_status');
            $table->foreign('auto_check_in_source_session_id', 'teacher_att_auto_source_fk')->references('id')->on('teacher_attendance_sessions')->nullOnDelete();
        });

        Schema::create('teacher_attendance_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_attendance_session_id');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('teacher_schedules')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('attendance_date');
            $table->unsignedSmallInteger('session_number')->default(1);
            $table->string('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->string('used_ip_address')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'subject_id', 'attendance_date', 'session_number'], 'teacher_qr_subject_date_session_idx');
            $table->index('expires_at');
            $table->foreign('teacher_attendance_session_id', 'teacher_qr_session_fk')->references('id')->on('teacher_attendance_sessions')->cascadeOnDelete();
        });

        Schema::create('teacher_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_attendance_session_id')->nullable()->constrained('teacher_attendance_sessions')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['teacher_id', 'created_at']);
            $table->index('action');
        });

        Schema::create('teacher_attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('attendance_session_id')->nullable()->constrained('teacher_attendance_sessions')->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('teacher_schedules')->nullOnDelete();
            $table->enum('request_type', ['missing_check_in', 'missing_check_out', 'wrong_status', 'internet_problem', 'schedule_change', 'other']);
            $table->dateTime('requested_check_in_time')->nullable();
            $table->dateTime('requested_check_out_time')->nullable();
            $table->string('requested_status')->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_class_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('teacher_schedules')->onDelete('cascade');
            $table->enum('request_type', ['cancellation', 'reschedule', 'replacement']);
            $table->date('requested_date')->nullable();
            $table->dateTime('requested_start_time')->nullable();
            $table->dateTime('requested_end_time')->nullable();
            $table->string('requested_room_name')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('replacement_schedule_id')->nullable()->constrained('teacher_schedules')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index('request_type');
        });

        Schema::create('teacher_attendance_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('report_type', ['daily', 'monthly', 'semester', 'academic_year', 'teaching_hours', 'late', 'absent']);
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->json('filters')->nullable();
            $table->json('summary');
            $table->string('file_path')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('generated_at');
            $table->timestamps();

            $table->index(['report_type', 'date_from', 'date_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_reports');
        Schema::dropIfExists('teacher_class_change_requests');
        Schema::dropIfExists('teacher_attendance_corrections');
        Schema::dropIfExists('teacher_attendance_logs');
        Schema::dropIfExists('teacher_attendance_qr_tokens');
        Schema::dropIfExists('teacher_attendance_sessions');
        Schema::dropIfExists('teacher_schedules');
    }
};
