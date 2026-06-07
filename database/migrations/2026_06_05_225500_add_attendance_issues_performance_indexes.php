<?php

use Illuminate\Database\Migrations\Migration;
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

        $wrappedColumns = collect($columns)
            ->map(fn ($column) => "`{$column}`")
            ->implode(', ');

        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$wrappedColumns})");
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $index)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
