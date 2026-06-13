<?php

namespace App\Jobs;

use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

class RestoreTestJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function handle(BackupService $backupService): void
    {
        $server = gethostname() ?: php_uname('n');
        $startedAt = now();
        $tempDatabase = 'restore_test_' . now()->format('YmdHis');

        try {
            if (DB::connection()->getDriverName() !== 'mysql') {
                throw new RuntimeException('Restore testing currently supports MySQL connections only.');
            }

            $latest = $backupService->latestLocalBackup([
                BackupService::PREFIX_FULL,
                BackupService::PREFIX_WEEKLY,
                BackupService::PREFIX_MONTHLY,
            ]);

            if (!$latest) {
                throw new RuntimeException('No local full backup found for restore testing.');
            }

            $extractDir = storage_path('app/restore_test_tmp/' . uniqid('restore_test_', true));
            $sqlPath = $extractDir . '/database.sql';
            $this->extractDatabaseDump($backupService->backupPath($latest['name']), $extractDir);

            DB::statement("CREATE DATABASE `{$tempDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->importSqlIntoDatabase($sqlPath, $tempDatabase);
            $counts = $this->verifyRestoredTables($tempDatabase);

            BackupRestoreLog::create([
                'action' => 'restore_test',
                'file_name' => $latest['name'],
                'storage_disk' => 'local',
                'status' => 'success',
                'message' => 'Restore test completed. ' . json_encode($counts, JSON_THROW_ON_ERROR),
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify(
                "✅ <b>Restore Test Completed</b>\n"
                . "File: " . e($latest['name']) . "\n"
                . "Students: {$counts['students']}\n"
                . "Teachers: {$counts['teachers']}\n"
                . "Users: {$counts['users']}\n"
                . "Attendance: {$counts['attendance']}\n"
                . "Server: {$server}\n"
                . "Time: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Status: Success"
            );
        } catch (Throwable $e) {
            BackupRestoreLog::create([
                'action' => 'restore_test',
                'storage_disk' => 'local',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify("❌ <b>Restore Test Failed</b>\nServer: {$server}\nTime: " . now()->format('Y-m-d H:i:s') . "\nError: " . e($e->getMessage()));

            throw $e;
        } finally {
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$tempDatabase}`");
            } catch (Throwable) {
            }

            if (isset($extractDir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($extractDir);
            }
        }
    }

    private function extractDatabaseDump(string $zipPath, string $extractDir): void
    {
        \Illuminate\Support\Facades\File::ensureDirectoryExists($extractDir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Unable to open backup zip for restore test.');
        }

        if ($zip->locateName('database.sql') === false || !$zip->extractTo($extractDir, 'database.sql')) {
            $zip->close();
            throw new RuntimeException('Unable to extract database.sql for restore test.');
        }

        $zip->close();
    }

    private function importSqlIntoDatabase(string $sqlPath, string $database): void
    {
        $config = config('database.connections.mysql');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $database);
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $statement = '';
        $handle = fopen($sqlPath, 'rb');

        if (!$handle) {
            throw new RuntimeException('Unable to read database.sql for restore test.');
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $statement .= $line;

                if (str_ends_with(rtrim($line), ';')) {
                    $pdo->exec($statement);
                    $statement = '';
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function verifyRestoredTables(string $database): array
    {
        $counts = [];

        foreach (['students', 'teachers', 'users', 'attendance'] as $table) {
            $row = DB::selectOne("SELECT COUNT(*) as count FROM `{$database}`.`{$table}`");
            $counts[$table] = (int) ($row->count ?? 0);
        }

        return $counts;
    }
}
