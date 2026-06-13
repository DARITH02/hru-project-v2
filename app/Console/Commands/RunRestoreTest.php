<?php

namespace App\Console\Commands;

use App\Jobs\RestoreTestJob;
use Illuminate\Console\Command;

class RunRestoreTest extends Command
{
    protected $signature = 'backup:restore-test';

    protected $description = 'Dispatch monthly restore test job.';

    public function handle(): int
    {
        RestoreTestJob::dispatch();
        $this->info('Restore test job dispatched.');

        return self::SUCCESS;
    }
}
