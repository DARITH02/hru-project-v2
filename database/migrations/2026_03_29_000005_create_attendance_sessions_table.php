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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('qr_token')->unique();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('checkin_open_time');
            $table->enum('session_type', ['morning', 'afternoon', 'evening', 'other'])->default('other');
            $table->enum('status', ['scheduled', 'active', 'completed'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
