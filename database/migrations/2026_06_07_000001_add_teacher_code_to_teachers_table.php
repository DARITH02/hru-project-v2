<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('teacher_code')->nullable()->unique()->after('user_id');
        });

        DB::table('teachers')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->each(function ($teacher) {
                DB::table('teachers')
                    ->where('id', $teacher->id)
                    ->update(['teacher_code' => $this->generateTeacherCode()]);
            });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['teacher_code']);
            $table->dropColumn('teacher_code');
        });
    }

    private function generateTeacherCode(): string
    {
        do {
            $code = 'TCH-' . random_int(100000, 999999);
        } while (DB::table('teachers')->where('teacher_code', $code)->exists());

        return $code;
    }
};
