<?php

namespace App\Console\Commands;

use App\Jobs\ScheduledBackupJob;
use App\Services\BackupService;
use Illuminate\Console\Command;

class RunScheduledBackup extends Command
{
    protected $signature = 'backup:run {type=full : full, incremental, weekly, or monthly}';

    protected $description = 'Dispatch a scheduled backup job.';

    public function handle(): int
    {
        $type = (string) $this->argument('type');
        $allowed = [BackupService::TYPE_FULL, BackupService::TYPE_INCREMENTAL, BackupService::TYPE_WEEKLY, BackupService::TYPE_MONTHLY];

        if (!in_array($type, $allowed, true)) {
            $this->error('Invalid backup type. Use: ' . implode(', ', $allowed));
            return self::FAILURE;
        }

        ScheduledBackupJob::dispatch($type);
        $this->info("{$type} backup job dispatched.");

        return self::SUCCESS;
    }
}
