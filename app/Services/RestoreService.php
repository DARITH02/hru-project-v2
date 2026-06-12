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
            DB::unprepared(file_get_contents($extractDir . '/database.sql'));
            $this->restorePublicStorage($extractDir . '/storage_public');

            Artisan::call('optimize:clear');
            Artisan::call('up');

            $log->update([
                'status' => 'success',
                'message' => 'Restore completed successfully.',
                'completed_at' => now(),
            ]);

            BackupRestoreLog::create([
                'user_id' => $userId,
                'action' => 'restore',
                'file_name' => $fileName,
                'storage_disk' => 'local',
                'status' => 'success',
                'message' => 'Restore completed successfully.',
                'started_at' => $log->started_at,
                'completed_at' => now(),
            ]);

            $this->notify("✅ <b>Restore succeeded</b>\nFile: " . e($fileName));
        } catch (Throwable $e) {
            try {
                Artisan::call('up');
            } catch (Throwable) {
            }

            $log->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            BackupRestoreLog::create([
                'user_id' => $userId,
                'action' => 'restore',
                'file_name' => $fileName,
                'storage_disk' => 'local',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'started_at' => $log->started_at,
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
            if (str_starts_with($name, '/') || str_contains($name, '../') || str_contains($name, '..\\')) {
                $zip->close();
                throw new RuntimeException('Backup zip contains unsafe paths.');
            }
        }

        $zip->close();
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
