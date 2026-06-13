<?php

namespace App\Console\Commands;

use App\Jobs\BackupCleanupJob;
use Illuminate\Console\Command;

class RunBackupCleanup extends Command
{
    protected $signature = 'backup:cleanup';

    protected $description = 'Dispatch backup cleanup job.';

    public function handle(): int
    {
        BackupCleanupJob::dispatch();
        $this->info('Backup cleanup job dispatched.');

        return self::SUCCESS;
    }
}
