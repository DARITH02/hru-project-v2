<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_permissions', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('type');
            $table->foreignId('requested_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('requested_by_teacher_id')->nullable()->after('requested_by')->constrained('teachers')->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->after('requested_by_teacher_id');
            $table->timestamp('approved_at')->nullable()->after('expires_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('approved_by');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });

        DB::table('student_permissions')->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('student_permissions', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['requested_by_teacher_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'status',
                'requested_by',
                'requested_by_teacher_id',
                'expires_at',
                'approved_at',
                'approved_by',
                'reviewed_at',
                'reviewed_by',
            ]);
        });
    }
};
