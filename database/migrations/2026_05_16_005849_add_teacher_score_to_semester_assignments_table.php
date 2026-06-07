<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_assignments', function (Blueprint $table) {
            $table->decimal('teacher_score', 8, 2)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('semester_assignments', function (Blueprint $table) {
            $table->dropColumn('teacher_score');
        });
    }
};
