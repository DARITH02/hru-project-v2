<?php

namespace App\Services;

use App\Models\BackupRestoreLog;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupService
{
    public function __construct(
        private GoogleDriveService $googleDrive,
        private TelegramService $telegram,
    ) {
    }

    public function createBackup(?int $userId = null, bool $uploadToGoogleDrive = true, string $prefix = 'hru_ats_backup'): array
    {
        $this->ensureZipAvailable();
        $this->ensureBackupDirectory();

        $fileName = $prefix . '_' . now()->format('Y_m_d_H_i') . '.zip';
        $zipPath = $this->backupPath($fileName);
        $tempDir = storage_path('app/backup_tmp/' . uniqid('backup_', true));
        File::ensureDirectoryExists($tempDir);

        try {
            $sqlPath = $tempDir . '/database.sql';
            $this->dumpDatabase($sqlPath);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create backup zip file.');
            }

            $zip->addFile($sqlPath, 'database.sql');
            $zip->addFromString('manifest.json', json_encode([
                'app' => config('app.name'),
                'created_at' => now()->toIso8601String(),
                'database' => config('database.default'),
                'includes' => ['database.sql', 'storage_public'],
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            $publicRoot = storage_path('app/public');
            if (is_dir($publicRoot)) {
                foreach (File::allFiles($publicRoot) as $file) {
                    $relative = 'storage_public/' . ltrim(str_replace($publicRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                    $zip->addFile($file->getPathname(), str_replace(DIRECTORY_SEPARATOR, '/', $relative));
                }
            }

            $zip->close();

            $size = filesize($zipPath) ?: 0;
            $driveFile = $uploadToGoogleDrive ? $this->googleDrive->upload($zipPath, $fileName) : null;

            BackupRestoreLog::create([
                'user_id' => $userId,
                'action' => $prefix === 'emergency_backup' ? 'emergency_backup' : 'backup',
                'file_name' => $fileName,
                'storage_disk' => $driveFile ? 'local+google_drive' : 'local',
                'backup_size' => $size,
                'status' => 'success',
                'message' => $driveFile ? 'Backup saved locally and uploaded to Google Drive.' : 'Backup saved locally.',
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return [
                'file_name' => $fileName,
                'path' => $zipPath,
                'size' => $size,
                'google_drive_file' => $driveFile,
            ];
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function localBackups(): array
    {
        $this->ensureBackupDirectory();

        return collect(File::files($this->backupDirectory()))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->values()
            ->all();
    }

    public function deleteLocalBackup(string $fileName): bool
    {
        $this->assertValidBackupFileName($fileName);

        $path = $this->backupPath($fileName);
        return is_file($path) && unlink($path);
    }

    public function deleteOldLocalBackups(int $days = 30): int
    {
        $this->ensureBackupDirectory();
        $cutoff = now()->subDays($days)->getTimestamp();
        $deleted = 0;

        foreach (File::files($this->backupDirectory()) as $file) {
            if ($file->getMTime() < $cutoff && str_ends_with($file->getFilename(), '.zip')) {
                if (@unlink($file->getPathname())) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    public function backupPath(string $fileName): string
    {
        $this->assertValidBackupFileName($fileName);
        return $this->backupDirectory() . DIRECTORY_SEPARATOR . $fileName;
    }

    public function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    public function notify(string $message): void
    {
        try {
            $bot = TelegramBot::where('is_active', true)->first();
            if ($bot && $bot->chat_id) {
                $this->telegram->sendMessage($bot, $message);
            }
        } catch (Throwable) {
            // Backup work must not fail because Telegram is unavailable.
        }
    }

    public function assertValidBackupFileName(string $fileName): void
    {
        if (!preg_match('/^(hru_ats_backup|emergency_backup)_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}\.zip$/', $fileName)) {
            throw new RuntimeException('Invalid backup file name.');
        }
    }

    private function dumpDatabase(string $sqlPath): void
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $handle = fopen($sqlPath, 'wb');
        if (!$handle) {
            throw new RuntimeException('Unable to create database dump file.');
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $tableKey = 'Tables_in_' . DB::connection()->getDatabaseName();

        foreach ($tables as $tableRow) {
            $table = $tableRow->{$tableKey} ?? array_values((array) $tableRow)[0] ?? null;
            if (!$table) {
                continue;
            }

            $quotedTable = '`' . str_replace('`', '``', $table) . '`';
            $create = DB::selectOne("SHOW CREATE TABLE {$quotedTable}");
            $createSql = $create->{'Create Table'} ?? null;

            fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
            fwrite($handle, $createSql . ";\n\n");

            $statement = $pdo->query("SELECT * FROM {$quotedTable}");
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(fn ($column) => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
                $values = array_map(fn ($value) => is_null($value) ? 'NULL' : $pdo->quote((string) $value), array_values($row));
                fwrite($handle, 'INSERT INTO ' . $quotedTable . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function ensureBackupDirectory(): void
    {
        File::ensureDirectoryExists($this->backupDirectory(), 0750, true);
    }

    private function ensureZipAvailable(): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension is required for backups.');
        }
    }
}
