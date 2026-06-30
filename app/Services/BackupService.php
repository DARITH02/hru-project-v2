<?php

namespace App\Services;

use App\Models\BackupRestoreLog;
use App\Models\TelegramBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupService
{
    public const TYPE_FULL = 'full';
    public const TYPE_INCREMENTAL = 'incremental';
    public const TYPE_WEEKLY = 'weekly';
    public const TYPE_MONTHLY = 'monthly';

    public const PREFIX_FULL = 'hru_ats_backup';
    public const PREFIX_EMERGENCY = 'emergency_backup';
    public const PREFIX_INCREMENTAL = 'hru_ats_incremental';
    public const PREFIX_WEEKLY = 'hru_ats_weekly';
    public const PREFIX_MONTHLY = 'hru_ats_monthly';

    private const INCREMENTAL_TABLES = [
        'attendance',
        'attendance_sessions',
        'teacher_attendance_sessions',
        'teacher_attendance_logs',
        'teacher_attendance_qr_tokens',
    ];

    public function __construct(
        private GoogleDriveService $googleDrive,
        private TelegramService $telegram,
    ) {
    }

    public function createBackup(
        ?int $userId = null,
        bool $uploadToGoogleDrive = true,
        string $prefix = self::PREFIX_FULL,
        bool $writeLog = true,
        ?array $onlyTables = null,
        bool $includeStorage = true,
        string $backupType = self::TYPE_FULL,
    ): array
    {
        $this->ensureZipAvailable();
        $this->ensureBackupDirectory();

        $fileName = $prefix . '_' . now()->format('Y_m_d_H_i_s') . '.zip';
        $zipPath = $this->backupPath($fileName);
        $tempDir = $this->backupDirectory() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . uniqid('backup_', true);
        File::ensureDirectoryExists($tempDir);

        try {
            $sqlPath = $tempDir . '/database.sql';
            $tables = $this->dumpDatabase($sqlPath, $onlyTables);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create backup zip file.');
            }

            $zip->addFile($sqlPath, 'database.sql');
            $zip->addFromString('manifest.json', json_encode([
                'app' => config('app.name'),
                'server' => gethostname() ?: php_uname('n'),
                'backup_type' => $backupType,
                'created_at' => now()->toIso8601String(),
                'database' => config('database.default'),
                'tables' => $tables,
                'includes' => $includeStorage ? ['database.sql', 'storage_public'] : ['database.sql'],
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            $publicRoot = storage_path('app/public');
            if ($includeStorage && is_dir($publicRoot)) {
                foreach (File::allFiles($publicRoot) as $file) {
                    $relative = 'storage_public/' . ltrim(str_replace($publicRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                    $zip->addFile($file->getPathname(), str_replace(DIRECTORY_SEPARATOR, '/', $relative));
                }
            }

            if (!$zip->close()) {
                throw new RuntimeException('Unable to finalize backup zip file.');
            }

            $size = filesize($zipPath) ?: 0;
            $driveFile = $uploadToGoogleDrive ? $this->googleDrive->upload($zipPath, $fileName) : null;

            if ($writeLog) {
                BackupRestoreLog::create([
                    'user_id' => $userId,
                    'action' => $prefix === self::PREFIX_EMERGENCY ? 'emergency_backup' : 'backup',
                    'file_name' => $fileName,
                    'storage_disk' => $driveFile ? 'local+google_drive' : 'local',
                    'backup_size' => $size,
                    'status' => 'success',
                    'message' => $driveFile ? 'Backup saved locally and uploaded to Google Drive.' : 'Backup saved locally.',
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);
            }

            return [
                'file_name' => $fileName,
                'path' => $zipPath,
                'size' => $size,
                'google_drive_file' => $driveFile,
                'storage_disk' => $driveFile ? 'local+google_drive' : 'local',
                'message' => $driveFile ? 'Backup saved locally and uploaded to Google Drive.' : 'Backup saved locally.',
            ];
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function createScheduledBackup(string $type, ?int $userId = null): array
    {
        return match ($type) {
            self::TYPE_INCREMENTAL => $this->createBackup(
                userId: $userId,
                uploadToGoogleDrive: true,
                prefix: self::PREFIX_INCREMENTAL,
                writeLog: true,
                onlyTables: self::INCREMENTAL_TABLES,
                includeStorage: false,
                backupType: self::TYPE_INCREMENTAL,
            ),
            self::TYPE_WEEKLY => $this->createBackup(
                userId: $userId,
                uploadToGoogleDrive: true,
                prefix: self::PREFIX_WEEKLY,
                writeLog: true,
                backupType: self::TYPE_WEEKLY,
            ),
            self::TYPE_MONTHLY => $this->createBackup(
                userId: $userId,
                uploadToGoogleDrive: true,
                prefix: self::PREFIX_MONTHLY,
                writeLog: true,
                backupType: self::TYPE_MONTHLY,
            ),
            default => $this->createBackup(
                userId: $userId,
                uploadToGoogleDrive: true,
                prefix: self::PREFIX_FULL,
                writeLog: true,
                backupType: self::TYPE_FULL,
            ),
        };
    }

    public function localBackups(): array
    {
        $this->ensureBackupDirectory();
        $currentDriver = DB::connection()->getDriverName();

        return collect(File::files($this->backupDirectory()))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) use ($currentDriver) {
                $databaseDriver = $this->backupDatabaseDriver($file->getPathname());

                return [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'database_driver' => $databaseDriver,
                    'is_compatible' => $databaseDriver === null || $databaseDriver === $currentDriver,
                ];
            })
            ->values()
            ->all();
    }

    private function backupDatabaseDriver(string $path): ?string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return null;
        }

        try {
            $manifest = $zip->getFromName('manifest.json');
            if ($manifest === false) {
                return null;
            }

            $metadata = json_decode($manifest, true);
            return is_array($metadata) ? ($metadata['database'] ?? null) : null;
        } finally {
            $zip->close();
        }
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
        $prefixes = implode('|', array_map('preg_quote', [
            self::PREFIX_FULL,
            self::PREFIX_EMERGENCY,
            self::PREFIX_INCREMENTAL,
            self::PREFIX_WEEKLY,
            self::PREFIX_MONTHLY,
        ]));

        if (!preg_match('/^(' . $prefixes . ')_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}(?:_\d{2})?\.zip$/', $fileName)) {
            throw new RuntimeException('Invalid backup file name.');
        }
    }

    public function latestLocalBackup(?array $prefixes = null): ?array
    {
        return collect($this->localBackups())
            ->when($prefixes, fn ($items) => $items->filter(
                fn ($backup) => collect($prefixes)->contains(fn ($prefix) => str_starts_with($backup['name'], $prefix . '_'))
            ))
            ->sortByDesc('modified_at')
            ->first();
    }

    public function verifyLocalBackup(string $fileName): array
    {
        $path = $this->backupPath($fileName);
        $zip = new ZipArchive();
        $openResult = $zip->open($path, ZipArchive::CHECKCONS);

        if ($openResult !== true) {
            throw new RuntimeException('Backup zip integrity check failed.');
        }

        $hasDatabase = $zip->locateName('database.sql') !== false;
        $hasManifest = $zip->locateName('manifest.json') !== false;
        $zip->close();

        if (!$hasDatabase) {
            throw new RuntimeException('Backup verification failed: database.sql is missing.');
        }

        return [
            'file_name' => $fileName,
            'path' => $path,
            'size' => filesize($path) ?: 0,
            'sha256' => hash_file('sha256', $path),
            'has_database_dump' => $hasDatabase,
            'has_manifest' => $hasManifest,
        ];
    }

    private function dumpDatabase(string $sqlPath, ?array $onlyTables = null): array
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => $this->dumpPostgresDatabase($sqlPath, $onlyTables),
            'mysql' => $this->dumpMysqlDatabase($sqlPath, $onlyTables),
            default => throw new RuntimeException('Unsupported database driver for backup: ' . DB::connection()->getDriverName()),
        };
    }

    private function dumpMysqlDatabase(string $sqlPath, ?array $onlyTables = null): array
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $handle = fopen($sqlPath, 'wb');
        if (!$handle) {
            throw new RuntimeException('Unable to create database dump file.');
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $dumpedTables = [];
        $transactionStarted = false;

        try {
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
                $transactionStarted = true;
            }

            $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $tableKey = 'Tables_in_' . DB::connection()->getDatabaseName();
            $allowedTables = $onlyTables ? array_flip(array_filter($onlyTables, fn ($table) => Schema::hasTable($table))) : null;

            foreach ($tables as $tableRow) {
                $table = $tableRow->{$tableKey} ?? array_values((array) $tableRow)[0] ?? null;
                if (!$table) {
                    continue;
                }

                if ($allowedTables !== null && !isset($allowedTables[$table])) {
                    continue;
                }

                $dumpedTables[] = $table;

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

            if ($transactionStarted) {
                $pdo->commit();
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (Throwable $e) {
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        } finally {
            fclose($handle);
        }

        return $dumpedTables;
    }

    private function dumpPostgresDatabase(string $sqlPath, ?array $onlyTables = null): array
    {
        $tables = $this->postgresTables($onlyTables);

        if ($this->dumpPostgresWithPgDump($sqlPath, $tables)) {
            return $tables;
        }

        return $this->dumpPostgresDatabaseWithPhp($sqlPath, $tables);
    }

    private function dumpPostgresDatabaseWithPhp(string $sqlPath, array $tables): array
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $handle = fopen($sqlPath, 'wb');
        if (!$handle) {
            throw new RuntimeException('Unable to create database dump file.');
        }

        $dumpedTables = [];
        $transactionStarted = false;

        try {
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
                $transactionStarted = true;
            }

            $dumpedTables = $tables;

            fwrite($handle, "BEGIN;\n\n");

            foreach ($tables as $table) {
                $this->writePostgresCreateTable($handle, $table);
            }

            if ($tables !== []) {
                $quotedTables = implode(', ', array_map(fn ($table) => $this->quotePostgresIdentifier($table), $tables));
                fwrite($handle, "TRUNCATE TABLE {$quotedTables} RESTART IDENTITY CASCADE;\n\n");
            }

            foreach ($tables as $table) {
                $quotedTable = $this->quotePostgresIdentifier($table);
                $statement = $pdo->query("SELECT * FROM {$quotedTable}");

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_map(fn ($column) => $this->quotePostgresIdentifier($column), array_keys($row));
                    $values = array_map(fn ($value) => $this->quotePostgresValue($pdo, $value), array_values($row));
                    fwrite($handle, 'INSERT INTO ' . $quotedTable . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
                }

                $this->writePostgresSequenceResets($handle, $table);
                fwrite($handle, "\n");
            }

            fwrite($handle, "COMMIT;\n");

            if ($transactionStarted) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if ($transactionStarted && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        } finally {
            fclose($handle);
        }

        return $dumpedTables;
    }

    private function dumpPostgresWithPgDump(string $sqlPath, array $tables): bool
    {
        if ($tables === []) {
            return false;
        }

        $pgDump = $this->pgDumpBinary();
        if (!$pgDump) {
            return false;
        }

        $config = config('database.connections.' . config('database.default'));
        $command = [
            $pgDump,
            '--clean',
            '--if-exists',
            '--no-owner',
            '--no-privileges',
            '--format=plain',
            '--schema=public',
            '--file=' . $sqlPath,
        ];

        if (!empty($config['host'])) {
            $command[] = '--host=' . $config['host'];
        }

        if (!empty($config['port'])) {
            $command[] = '--port=' . $config['port'];
        }

        if (!empty($config['username'])) {
            $command[] = '--username=' . $config['username'];
        }

        foreach ($tables as $table) {
            $command[] = '--table=public.' . $table;
        }

        $command[] = $config['database'];

        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = $_ENV;
        if (!empty($config['password'])) {
            $env['PGPASSWORD'] = $config['password'];
        }

        $process = @proc_open($command, $descriptors, $pipes, base_path(), $env);
        if (!is_resource($process)) {
            return false;
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            report(new RuntimeException('pg_dump failed; falling back to PHP PostgreSQL dump. ' . trim($stderr ?: $stdout)));
            return false;
        }

        return is_file($sqlPath) && filesize($sqlPath) !== false;
    }

    private function pgDumpBinary(): ?string
    {
        foreach (['/usr/bin/pg_dump', '/usr/local/bin/pg_dump'] as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function postgresTables(?array $onlyTables = null): array
    {
        $tables = collect(DB::select(<<<'SQL'
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
                AND table_type = 'BASE TABLE'
            ORDER BY table_name
        SQL))->pluck('table_name')->all();

        $allowedTables = $onlyTables ? array_flip(array_filter($onlyTables, fn ($table) => Schema::hasTable($table))) : null;

        return array_values(array_filter($tables, fn ($table) => $allowedTables === null || isset($allowedTables[$table])));
    }

    private function writePostgresCreateTable($handle, string $table): void
    {
        $columns = DB::select(<<<'SQL'
            SELECT
                column_name,
                data_type,
                udt_name,
                character_maximum_length,
                numeric_precision,
                numeric_scale,
                datetime_precision,
                is_nullable,
                column_default
            FROM information_schema.columns
            WHERE table_schema = 'public'
                AND table_name = ?
            ORDER BY ordinal_position
        SQL, [$table]);

        if ($columns === []) {
            return;
        }

        $definitions = [];
        foreach ($columns as $column) {
            $definition = '    ' . $this->quotePostgresIdentifier($column->column_name) . ' ' . $this->postgresColumnType($column);

            if ($column->column_default !== null) {
                $definition .= ' DEFAULT ' . $column->column_default;
            }

            if ($column->is_nullable === 'NO') {
                $definition .= ' NOT NULL';
            }

            $definitions[] = $definition;
        }

        $primaryKeys = DB::select(<<<'SQL'
            SELECT kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
                AND tc.table_name = kcu.table_name
            WHERE tc.table_schema = 'public'
                AND tc.table_name = ?
                AND tc.constraint_type = 'PRIMARY KEY'
            ORDER BY kcu.ordinal_position
        SQL, [$table]);

        if ($primaryKeys !== []) {
            $definitions[] = '    PRIMARY KEY (' . implode(', ', array_map(
                fn ($key) => $this->quotePostgresIdentifier($key->column_name),
                $primaryKeys
            )) . ')';
        }

        fwrite($handle, 'CREATE TABLE IF NOT EXISTS ' . $this->quotePostgresIdentifier($table) . " (\n");
        fwrite($handle, implode(",\n", $definitions));
        fwrite($handle, "\n);\n\n");
    }

    private function writePostgresSequenceResets($handle, string $table): void
    {
        $sequenceColumns = DB::select(<<<'SQL'
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
                AND table_name = ?
                AND column_default LIKE 'nextval(%'
            ORDER BY ordinal_position
        SQL, [$table]);

        foreach ($sequenceColumns as $column) {
            $quotedTable = $this->quotePostgresIdentifier($table);
            $quotedColumn = $this->quotePostgresIdentifier($column->column_name);
            $sequenceLiteral = str_replace("'", "''", 'public.' . $table);
            $columnLiteral = str_replace("'", "''", $column->column_name);

            fwrite(
                $handle,
                "SELECT setval(pg_get_serial_sequence('{$sequenceLiteral}', '{$columnLiteral}'), COALESCE((SELECT MAX({$quotedColumn}) FROM {$quotedTable}), 1), (SELECT MAX({$quotedColumn}) IS NOT NULL FROM {$quotedTable}));\n"
            );
        }
    }

    private function postgresColumnType(object $column): string
    {
        return match ($column->data_type) {
            'character varying' => $column->character_maximum_length ? 'varchar(' . (int) $column->character_maximum_length . ')' : 'varchar',
            'character' => $column->character_maximum_length ? 'char(' . (int) $column->character_maximum_length . ')' : 'char',
            'numeric' => $column->numeric_precision
                ? 'numeric(' . (int) $column->numeric_precision . ($column->numeric_scale !== null ? ', ' . (int) $column->numeric_scale : '') . ')'
                : 'numeric',
            'timestamp without time zone' => 'timestamp' . ($column->datetime_precision !== null ? '(' . (int) $column->datetime_precision . ')' : '') . ' without time zone',
            'timestamp with time zone' => 'timestamp' . ($column->datetime_precision !== null ? '(' . (int) $column->datetime_precision . ')' : '') . ' with time zone',
            'time without time zone' => 'time' . ($column->datetime_precision !== null ? '(' . (int) $column->datetime_precision . ')' : '') . ' without time zone',
            'time with time zone' => 'time' . ($column->datetime_precision !== null ? '(' . (int) $column->datetime_precision . ')' : '') . ' with time zone',
            'ARRAY' => $column->udt_name && str_starts_with($column->udt_name, '_') ? substr($column->udt_name, 1) . '[]' : $column->data_type,
            'USER-DEFINED' => $column->udt_name ?: $column->data_type,
            default => $column->data_type,
        };
    }

    private function quotePostgresIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function quotePostgresValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return $pdo->quote((string) $value);
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
