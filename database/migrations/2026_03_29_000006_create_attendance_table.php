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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('session_id')->constrained('attendance_sessions')->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'late']);
            $table->enum('method', ['qr', 'manual']);
            $table->dateTime('scan_time')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
