<?php

namespace App\Console\Commands;

use App\Models\TelegramBot;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BackupProductionCheck extends Command
{
    protected $signature = 'backup:check';

    protected $description = 'Check production readiness for backup, restore, queue, Google Drive, and Telegram.';

    public function handle(GoogleDriveService $googleDrive): int
    {
        $checks = [
            'PHP ZipArchive extension' => class_exists(ZipArchive::class),
            'Supported database connection' => in_array(DB::connection()->getDriverName(), ['mysql', 'pgsql'], true),
            'Backup log table exists' => Schema::hasTable('backup_restore_logs'),
            'Queue jobs table exists' => Schema::hasTable('jobs'),
            'Failed jobs table exists' => Schema::hasTable('failed_jobs'),
            'Backup storage writable' => File::ensureDirectoryExists(storage_path('app/backups')) || is_writable(storage_path('app/backups')),
            'Google Drive configured' => $googleDrive->configured(),
            'Telegram active bot configured' => TelegramBot::where('is_active', true)->whereNotNull('chat_id')->exists(),
            'Queue connection is database' => config('queue.default') === 'database',
            'App timezone set' => config('app.timezone') === 'Asia/Phnom_Penh',
        ];

        $failed = 0;

        foreach ($checks as $label => $passed) {
            if ($passed) {
                $this->info("PASS  {$label}");
            } else {
                $failed++;
                $this->error("FAIL  {$label}");
            }
        }

        if ($failed > 0) {
            $this->warn("{$failed} backup readiness check(s) failed.");
            return self::FAILURE;
        }

        $this->info('Backup module is production-ready.');

        return self::SUCCESS;
    }
}
