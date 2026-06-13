<?php

namespace App\Console\Commands;

use App\Jobs\BackupVerificationJob;
use Illuminate\Console\Command;

class RunBackupVerification extends Command
{
    protected $signature = 'backup:verify';

    protected $description = 'Dispatch backup verification job.';

    public function handle(): int
    {
        BackupVerificationJob::dispatch();
        $this->info('Backup verification job dispatched.');

        return self::SUCCESS;
    }
}
