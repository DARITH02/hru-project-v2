<?php

namespace App\Jobs;

use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class ScheduledBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function __construct(
        public string $type,
    ) {
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('backup-restore:backup'))->expireAfter($this->timeout)];
    }

    public function handle(BackupService $backupService): void
    {
        $server = gethostname() ?: php_uname('n');
        $label = ucfirst($this->type);

        $backupService->notify("🟦 <b>Backup Started</b>\nType: {$label}\nServer: {$server}\nTime: " . now()->format('Y-m-d H:i:s'));

        try {
            $backup = $backupService->createScheduledBackup($this->type);
            $size = number_format(($backup['size'] ?? 0) / 1024 / 1024, 2) . ' MB';

            $backupService->notify(
                "✅ <b>Backup Completed</b>\n"
                . "Type: {$label}\n"
                . "File: " . e($backup['file_name']) . "\n"
                . "Size: {$size}\n"
                . "Storage: " . e($backup['storage_disk']) . "\n"
                . "Server: {$server}\n"
                . "Time: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Status: Success"
            );
        } catch (Throwable $e) {
            BackupRestoreLog::create([
                'action' => 'backup',
                'storage_disk' => 'local+google_drive',
                'status' => 'failed',
                'message' => ucfirst($this->type) . ' backup failed: ' . $e->getMessage(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            $backupService->notify(
                "❌ <b>Backup Failed</b>\n"
                . "Type: {$label}\n"
                . "Server: {$server}\n"
                . "Time: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Status: Failed\n"
                . "Error: " . e($e->getMessage())
            );

            throw $e;
        }
    }
}
