<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('semester_assignments');
        
        Schema::create('semester_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('academic_year', 20);
            $table->integer('semester'); // 1 or 2
            $table->date('start_date');
            $table->date('end_date');
            $table->date('holiday_start')->nullable();
            $table->date('holiday_end')->nullable();
            $table->string('status')->default('upcoming'); // upcoming, active, completed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'academic_year', 'semester'], 'uniq_class_sem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_assignments');
    }
};
