<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BackupJob;
use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(BackupService $backups, GoogleDriveService $googleDrive)
    {
        $googleDriveStatus = $googleDrive->configurationStatus();

        return view('admin.backup_restore', [
            'localBackups' => $backups->localBackups(),
            'cloudBackups' => $googleDrive->listBackups(),
            'logs' => BackupRestoreLog::with('user')->latest()->paginate(20),
            'googleDriveConfigured' => $googleDriveStatus['configured'],
            'googleDriveStatus' => $googleDriveStatus,
        ]);
    }

    public function store(Request $request, GoogleDriveService $googleDrive): RedirectResponse
    {
        try {
            BackupJob::dispatchSync($request->user()->id, $googleDrive->configured());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', __('admin.backup_restore.backup_failed', [
                'message' => $e->getMessage(),
            ]));
        }

        return back()->with('success', __('admin.backup_restore.backup_completed'));
    }

    public function download(string $fileName, BackupService $backups): BinaryFileResponse
    {
        $path = $backups->backupPath($fileName);
        abort_unless(is_file($path), 404);

        return response()->download($path, $fileName);
    }

    public function downloadCloud(
        string $fileId,
        string $fileName,
        BackupService $backups,
        GoogleDriveService $googleDrive,
    ): BinaryFileResponse {
        abort_unless($googleDrive->configured(), 404);

        $backups->assertValidBackupFileName($fileName);

        $tempDir = storage_path('app/cloud_download_tmp');
        File::ensureDirectoryExists($tempDir, 0750, true);

        $path = $tempDir . DIRECTORY_SEPARATOR . uniqid('cloud_backup_', true) . '_' . $fileName;
        $googleDrive->download($fileId, $path);

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    public function destroyLocal(string $fileName, BackupService $backups): RedirectResponse
    {
        $backups->deleteLocalBackup($fileName);

        BackupRestoreLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_backup',
            'file_name' => $fileName,
            'storage_disk' => 'local',
            'status' => 'success',
            'message' => 'Local backup deleted.',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Local backup deleted.');
    }

    public function destroyCloud(string $fileId, GoogleDriveService $googleDrive): RedirectResponse
    {
        abort_unless($googleDrive->configured(), 404);
        $googleDrive->delete($fileId);

        BackupRestoreLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_backup',
            'file_name' => $fileId,
            'storage_disk' => 'google_drive',
            'status' => 'success',
            'message' => 'Google Drive backup deleted.',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Google Drive backup deleted.');
    }
}
