<?php

namespace App\Filesystem;

use App\Services\GoogleDriveService;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use Throwable;

class GoogleDriveAdapter implements FilesystemAdapter
{
    public function __construct(
        private GoogleDriveService $googleDrive,
    ) {
    }

    public function fileExists(string $path): bool
    {
        throw UnableToCheckExistence::forLocation($path);
    }

    public function directoryExists(string $path): bool
    {
        return trim($path, '/') === '';
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'google_drive_');

        if ($tempPath === false || file_put_contents($tempPath, $contents) === false) {
            throw UnableToWriteFile::atLocation($path, 'Unable to create temporary upload file.');
        }

        try {
            $this->googleDrive->upload($tempPath, $this->fileName($path));
        } catch (Throwable $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'google_drive_');

        if ($tempPath === false || !is_resource($contents)) {
            throw UnableToWriteFile::atLocation($path, 'Unable to create temporary upload file.');
        }

        $target = fopen($tempPath, 'wb');
        if (!$target) {
            throw UnableToWriteFile::atLocation($path, 'Unable to open temporary upload file.');
        }

        try {
            stream_copy_to_stream($contents, $target);
            fclose($target);
            $target = null;

            $this->googleDrive->upload($tempPath, $this->fileName($path));
        } catch (Throwable $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        } finally {
            if (is_resource($target)) {
                fclose($target);
            }

            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function read(string $path): string
    {
        throw UnableToReadFile::fromLocation($path, 'Google Drive disk is configured for backup uploads only.');
    }

    public function readStream(string $path)
    {
        throw UnableToReadFile::fromLocation($path, 'Google Drive disk is configured for backup uploads only.');
    }

    public function delete(string $path): void
    {
        throw UnableToDeleteFile::atLocation($path, 'Delete by path is not supported for this Google Drive disk.');
    }

    public function deleteDirectory(string $path): void
    {
        throw UnableToDeleteDirectory::atLocation($path, 'Directories are not supported for this Google Drive disk.');
    }

    public function createDirectory(string $path, Config $config): void
    {
        if (trim($path, '/') !== '') {
            throw UnableToCreateDirectory::atLocation($path, 'Directories are not supported for this Google Drive disk.');
        }
    }

    public function setVisibility(string $path, string $visibility): void
    {
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'private');
    }

    public function mimeType(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::mimeType($path, 'Metadata lookup is not supported for this Google Drive disk.');
    }

    public function lastModified(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::lastModified($path, 'Metadata lookup is not supported for this Google Drive disk.');
    }

    public function fileSize(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::fileSize($path, 'Metadata lookup is not supported for this Google Drive disk.');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        if (trim($path, '/') !== '') {
            return [];
        }

        foreach ($this->googleDrive->listBackups() as $file) {
            yield new FileAttributes(
                $file['name'] ?? $file['id'],
                isset($file['size']) ? (int) $file['size'] : null,
                'private',
                isset($file['modifiedTime']) ? strtotime($file['modifiedTime']) : null
            );
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        throw UnableToMoveFile::because('Move is not supported for this Google Drive disk.', $source, $destination);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        throw UnableToCopyFile::because('Copy is not supported for this Google Drive disk.', $source, $destination);
    }

    private function fileName(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }
}
