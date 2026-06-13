<?php

namespace App\Jobs;

use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class BackupCleanupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function handle(BackupService $backupService, GoogleDriveService $googleDrive): void
    {
        $server = gethostname() ?: php_uname('n');
        $startedAt = now();

        try {
            $localDeleted = $backupService->deleteOldLocalBackups(30);
            $cloudDeleted = $googleDrive->deleteOldBackupsByPolicy();
            $cloudTotal = array_sum($cloudDeleted);

            BackupRestoreLog::create([
                'action' => 'cleanup',
                'storage_disk' => 'local+google_drive',
                'status' => 'success',
                'message' => "Cleanup completed. Local deleted: {$localDeleted}. Google Drive deleted: {$cloudTotal}.",
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify(
                "🧹 <b>Cleanup Completed</b>\n"
                . "Local deleted: {$localDeleted}\n"
                . "Google Drive deleted: {$cloudTotal}\n"
                . "Monthly archive deleted: {$cloudDeleted['monthly']}\n"
                . "Server: {$server}\n"
                . "Time: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Status: Success"
            );
        } catch (Throwable $e) {
            BackupRestoreLog::create([
                'action' => 'cleanup',
                'storage_disk' => 'local+google_drive',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify("❌ <b>Cleanup Failed</b>\nServer: {$server}\nTime: " . now()->format('Y-m-d H:i:s') . "\nError: " . e($e->getMessage()));

            throw $e;
        }
    }
}
