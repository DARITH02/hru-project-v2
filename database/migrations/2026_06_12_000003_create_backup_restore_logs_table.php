<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_restore_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->string('file_name')->nullable();
            $table->string('storage_disk', 40)->default('local');
            $table->unsignedBigInteger('backup_size')->nullable();
            $table->string('status', 40)->default('started');
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['action', 'status']);
            $table->index('file_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_restore_logs');
    }
};
