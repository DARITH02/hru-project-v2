<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_class_group')) {
            Schema::create('class_class_group', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_room_id');
                $table->unsignedBigInteger('class_group_id');
                $table->timestamps();

                $table->foreign('class_room_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('class_group_id')->references('id')->on('class_groups')->onDelete('cascade');
            });
        }

        // Migrate existing data from classes.group_id to class_class_group pivot
        $classes = DB::table('classes')->whereNotNull('group_id')->get();
        foreach ($classes as $class) {
            $exists = DB::table('class_class_group')
                ->where('class_room_id', $class->id)
                ->where('class_group_id', $class->group_id)
                ->exists();
            
            if (!$exists) {
                DB::table('class_class_group')->insert([
                    'class_room_id' => $class->id,
                    'class_group_id' => $class->group_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_class_group');
    }
};
