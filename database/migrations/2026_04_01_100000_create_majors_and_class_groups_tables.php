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
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('major_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('year_level')->default(1);
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('class_groups')->onDelete('set null');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('class_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['major_id']);
            $table->dropColumn(['group_id', 'major_id']);
        });

        Schema::dropIfExists('class_groups');
        Schema::dropIfExists('majors');
    }
};
