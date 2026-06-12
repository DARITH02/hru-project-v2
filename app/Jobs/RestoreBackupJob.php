<?php

namespace App\Jobs;

use App\Services\RestoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RestoreBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function __construct(
        public string $fileName,
        public ?int $userId = null,
    ) {
    }

    public function handle(RestoreService $restoreService): void
    {
        $restoreService->restore($this->fileName, $this->userId);
    }
}
