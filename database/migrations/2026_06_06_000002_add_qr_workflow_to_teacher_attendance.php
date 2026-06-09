<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_schedules', 'session_number')) {
                $table->unsignedSmallInteger('session_number')->default(1)->after('scheduled_end_time');
            }
        });

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_attendance_sessions', 'session_number')) {
                $table->unsignedSmallInteger('session_number')->default(1)->after('scheduled_end_time');
            }

            if (!Schema::hasColumn('teacher_attendance_sessions', 'auto_check_in_source_session_id')) {
                $table->unsignedBigInteger('auto_check_in_source_session_id')
                    ->nullable()
                    ->after('check_out_time');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            try {
                Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
                    $table->foreign('auto_check_in_source_session_id', 'teacher_att_auto_source_fk')
                        ->references('id')
                        ->on('teacher_attendance_sessions')
                        ->nullOnDelete();
                });
            } catch (Throwable) {
                // Constraint already exists or the failed previous attempt left the column in place.
            }
        }

        if (DB::getDriverName() === 'mysql' && Schema::hasTable('teacher_attendance_sessions')) {
            DB::statement("ALTER TABLE teacher_attendance_sessions MODIFY attendance_status ENUM('scheduled','present','on_time','late','very_late','teaching','completed','early_leave','absent','permission','cancelled','rescheduled','missing_check_out') NOT NULL DEFAULT 'scheduled'");
            DB::statement("ALTER TABLE teacher_attendance_sessions MODIFY check_in_method ENUM('qr','manual','location','system','auto_session') NULL");
        }

        if (!Schema::hasTable('teacher_attendance_qr_tokens')) {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_qr_tokens');

        Schema::table('teacher_attendance_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_attendance_sessions', 'auto_check_in_source_session_id')) {
                $table->dropForeign('teacher_att_auto_source_fk');
                $table->dropColumn('auto_check_in_source_session_id');
            }

            if (Schema::hasColumn('teacher_attendance_sessions', 'session_number')) {
                $table->dropColumn('session_number');
            }
        });

        Schema::table('teacher_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_schedules', 'session_number')) {
                $table->dropColumn('session_number');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE teacher_attendance_sessions MODIFY attendance_status ENUM('scheduled','on_time','late','very_late','teaching','completed','early_leave','absent','permission','cancelled','rescheduled','missing_check_out') NOT NULL DEFAULT 'scheduled'");
            DB::statement("ALTER TABLE teacher_attendance_sessions MODIFY check_in_method ENUM('qr','manual','location','system') NULL");
        }
    }
};
