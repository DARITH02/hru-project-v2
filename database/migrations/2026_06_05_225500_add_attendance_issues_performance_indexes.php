<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('attendance_sessions', 'idx_att_sessions_period_status_class_start', [
            'academic_year',
            'semester',
            'status',
            'class_id',
            'start_time',
        ]);

        $this->addIndexIfMissing('class_class_group', 'idx_class_group_lookup', [
            'class_group_id',
            'class_room_id',
        ]);

        $this->addIndexIfMissing('student_restore_histories', 'idx_restore_period_student_created', [
            'academic_year',
            'semester',
            'student_id',
            'created_at',
        ]);

        $this->addIndexIfMissing('student_permissions', 'idx_permissions_student_dates', [
            'student_id',
            'start_date',
            'end_date',
        ]);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('attendance_sessions', 'idx_att_sessions_period_status_class_start');
        $this->dropIndexIfExists('class_class_group', 'idx_class_group_lookup');
        $this->dropIndexIfExists('student_restore_histories', 'idx_restore_period_student_created');
        $this->dropIndexIfExists('student_permissions', 'idx_permissions_student_dates');
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $index)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $index) {
                $blueprint->index($columns, $index);
            });
        } catch (Throwable $e) {
            if (DB::getDriverName() === 'pgsql') {
                throw $e;
            }

            // The index may already exist on manually restored databases.
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $index)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        } catch (Throwable $e) {
            if (DB::getDriverName() === 'pgsql') {
                throw $e;
            }

            // The index may already have been removed.
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('schemaname', 'public')
                ->where('tablename', $table)
                ->where('indexname', $index)
                ->exists();
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
