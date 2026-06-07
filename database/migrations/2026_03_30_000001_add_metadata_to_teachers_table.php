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
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('department')->nullable()->after('user_id');
            $table->string('specialization')->nullable()->after('department');
            $table->string('phone')->nullable()->after('specialization');
            $table->string('status')->default('active')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['department', 'specialization', 'phone', 'status']);
        });
    }
};
