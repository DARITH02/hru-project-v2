<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('academic_year');           // e.g. "2025-2026"
            $table->tinyInteger('semester');           // 1 or 2
            $table->date('start_date');                // Semester start
            $table->date('end_date');                  // Semester end (auto: +4 months)
            $table->date('holiday_start')->nullable(); // Holiday block start
            $table->date('holiday_end')->nullable();   // Holiday block end (~3 weeks)
            $table->string('status')->default('upcoming'); // upcoming | active | completed
            $table->text('notes')->nullable();
            $table->timestamps();

            // A teacher can only have one assignment per semester per academic year
            $table->unique(['teacher_id', 'academic_year', 'semester'], 'uniq_teacher_sem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_assignments');
    }
};
