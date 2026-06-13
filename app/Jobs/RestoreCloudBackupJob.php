<?php

namespace App\Jobs;

use App\Services\BackupService;
use App\Services\GoogleDriveService;
use App\Services\RestoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RestoreCloudBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function __construct(
        public string $fileId,
        public string $fileName,
        public ?int $userId = null,
    ) {
    }

    public function handle(
        BackupService $backupService,
        GoogleDriveService $googleDrive,
        RestoreService $restoreService,
    ): void {
        $backupService->assertValidBackupFileName($this->fileName);
        $googleDrive->download($this->fileId, $backupService->backupPath($this->fileName));

        $restoreService->restore($this->fileName, $this->userId);
    }
}
