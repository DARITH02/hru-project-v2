<?php

namespace App\Jobs;

use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class BackupVerificationJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function handle(BackupService $backupService, GoogleDriveService $googleDrive): void
    {
        $server = gethostname() ?: php_uname('n');
        $startedAt = now();

        try {
            $latest = $backupService->latestLocalBackup([
                BackupService::PREFIX_FULL,
                BackupService::PREFIX_WEEKLY,
                BackupService::PREFIX_MONTHLY,
            ]);

            if (!$latest) {
                throw new RuntimeException('No local full backup found for verification.');
            }

            $verification = $backupService->verifyLocalBackup($latest['name']);
            $cloudExists = collect($googleDrive->listBackups())->contains(fn ($file) => ($file['name'] ?? null) === $latest['name']);

            if (!$cloudExists) {
                throw new RuntimeException('Google Drive upload is missing for latest backup: ' . $latest['name']);
            }

            BackupRestoreLog::create([
                'action' => 'verify_backup',
                'file_name' => $latest['name'],
                'storage_disk' => 'local+google_drive',
                'backup_size' => $verification['size'],
                'status' => 'success',
                'message' => 'Backup verification completed. SHA256: ' . $verification['sha256'],
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify(
                "✅ <b>Backup Verification Succeeded</b>\n"
                . "File: " . e($latest['name']) . "\n"
                . "Size: " . number_format($verification['size'] / 1024 / 1024, 2) . " MB\n"
                . "Storage: local+google_drive\n"
                . "Server: {$server}\n"
                . "Time: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Status: Success"
            );
        } catch (Throwable $e) {
            BackupRestoreLog::create([
                'action' => 'verify_backup',
                'storage_disk' => 'local+google_drive',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $backupService->notify("❌ <b>Verification Failed</b>\nServer: {$server}\nTime: " . now()->format('Y-m-d H:i:s') . "\nError: " . e($e->getMessage()));

            throw $e;
        }
    }
}
