<?php

namespace App\Jobs;

use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class BackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(
        public ?int $userId = null,
        public bool $uploadToGoogleDrive = true,
    ) {
    }

    public function handle(BackupService $backupService): void
    {
        $log = BackupRestoreLog::create([
            'user_id' => $this->userId,
            'action' => 'backup',
            'storage_disk' => $this->uploadToGoogleDrive ? 'local+google_drive' : 'local',
            'status' => 'started',
            'message' => 'Backup job started.',
            'started_at' => now(),
        ]);

        try {
            $backupService->notify("🟦 <b>Backup started</b>\nHRU ATS backup is running.");

            $backup = $backupService->createBackup($this->userId, $this->uploadToGoogleDrive);

            $log->update([
                'file_name' => $backup['file_name'],
                'backup_size' => $backup['size'],
                'status' => 'success',
                'message' => 'Backup completed successfully.',
                'completed_at' => now(),
            ]);

            $backupService->notify("✅ <b>Backup succeeded</b>\nFile: " . e($backup['file_name']));
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $backupService->notify("❌ <b>Backup failed</b>\nError: " . e($e->getMessage()));
            throw $e;
        }
    }
}
