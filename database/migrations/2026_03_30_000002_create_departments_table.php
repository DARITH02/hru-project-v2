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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique()->nullable();
            $table->timestamps();
        });
        
        // Populate with common departments
        DB::table('departments')->insert([
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Sciences', 'code' => 'SCI'],
            ['name' => 'Humanities', 'code' => 'HUM'],
            ['name' => 'Business', 'code' => 'BUS'],
            ['name' => 'Health', 'code' => 'HEA'],
            ['name' => 'Technology', 'code' => 'TEC'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
