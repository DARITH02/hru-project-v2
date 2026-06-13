<?php

namespace App\Services;

use App\Models\BackupRestoreLog;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;
use ZipArchive;

class RestoreService
{
    public function __construct(
        private BackupService $backupService,
        private TelegramService $telegram,
        private MaintenanceModeService $maintenance,
    ) {
    }

    public function restore(string $fileName, ?int $userId = null): void
    {
        $lock = Cache::lock('backup_restore:restore_running', 3600);

        if (!$lock->get()) {
            throw new RuntimeException('Another restore is already running.');
        }

        $log = BackupRestoreLog::create([
            'user_id' => $userId,
            'action' => 'restore',
            'file_name' => $fileName,
            'storage_disk' => 'local',
            'status' => 'started',
            'message' => 'Restore started.',
            'started_at' => now(),
        ]);

        $extractDir = storage_path('app/restore_tmp/' . uniqid('restore_', true));

        try {
            $this->notify("♻️ <b>Restore started</b>\nFile: " . e($fileName));
            $this->validateBackup($fileName);

            $this->backupService->createBackup($userId, false, 'emergency_backup');

            $downOptions = ['--retry' => 60];
            if (config('app.key')) {
                $downOptions['--secret'] = substr(hash('sha256', config('app.key')), 0, 32);
            }

            Artisan::call('down', $downOptions);

            File::ensureDirectoryExists($extractDir);
            $this->extractBackup($this->backupService->backupPath($fileName), $extractDir);
            $this->restoreDatabase($extractDir . '/database.sql');
            $this->restorePublicStorage($extractDir . '/storage_public');

            $this->maintenance->disable($userId);
            Artisan::call('optimize:clear');
            Artisan::call('up');

            $log->update([
                'status' => 'success',
                'message' => 'Restore completed successfully.',
                'completed_at' => now(),
            ]);

            $this->notify("✅ <b>Restore succeeded</b>\nFile: " . e($fileName));
        } catch (Throwable $e) {
            try {
                $this->maintenance->disable($userId);
                Artisan::call('up');
            } catch (Throwable) {
            }

            $log->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->notify("❌ <b>Restore failed</b>\nFile: " . e($fileName) . "\nError: " . e($e->getMessage()));
            throw $e;
        } finally {
            File::deleteDirectory($extractDir);
            optional($lock)->release();
        }
    }

    public function validateBackup(string $fileName): void
    {
        $this->backupService->assertValidBackupFileName($fileName);
        $path = $this->backupService->backupPath($fileName);

        if (!is_file($path)) {
            throw new RuntimeException('Backup file does not exist.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Backup zip cannot be opened.');
        }

        if ($zip->locateName('database.sql') === false) {
            $zip->close();
            throw new RuntimeException('Backup zip is missing database.sql.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$name || $this->isUnsafeZipPath($name)) {
                $zip->close();
                throw new RuntimeException('Backup zip contains unsafe paths.');
            }
        }

        $zip->close();
    }

    private function restoreDatabase(string $sqlPath): void
    {
        if (!is_file($sqlPath)) {
            throw new RuntimeException('Backup zip is missing database.sql.');
        }

        $handle = fopen($sqlPath, 'rb');
        if (!$handle) {
            throw new RuntimeException('Unable to read backup database dump.');
        }

        $statement = '';

        try {
            while (($line = fgets($handle)) !== false) {
                $statement .= $line;

                if (str_ends_with(rtrim($line), ';')) {
                    DB::unprepared($statement);
                    $statement = '';
                }
            }

            if (trim($statement) !== '') {
                DB::unprepared($statement);
            }
        } finally {
            fclose($handle);
        }
    }

    private function isUnsafeZipPath(string $name): bool
    {
        return str_starts_with($name, '/')
            || str_starts_with($name, '\\')
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $name)
            || preg_match('/(^|[\/\\\\])\.\.([\/\\\\]|$)/', $name);
    }

    private function extractBackup(string $path, string $extractDir): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open backup zip.');
        }

        if (!$zip->extractTo($extractDir)) {
            $zip->close();
            throw new RuntimeException('Unable to extract backup zip.');
        }

        $zip->close();
    }

    private function restorePublicStorage(string $sourceDir): void
    {
        if (!is_dir($sourceDir)) {
            return;
        }

        $targetDir = storage_path('app/public');
        File::ensureDirectoryExists($targetDir);

        foreach (File::allFiles($sourceDir) as $file) {
            $relative = ltrim(str_replace($sourceDir, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $target = $targetDir . DIRECTORY_SEPARATOR . $relative;
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getPathname(), $target);
        }
    }

    private function notify(string $message): void
    {
        try {
            $bot = TelegramBot::where('is_active', true)->first();
            if ($bot && $bot->chat_id) {
                $this->telegram->sendMessage($bot, $message);
            }
        } catch (Throwable) {
        }
    }
}
