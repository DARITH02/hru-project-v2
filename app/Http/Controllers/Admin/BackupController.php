<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BackupJob;
use App\Models\BackupRestoreLog;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(BackupService $backups, GoogleDriveService $googleDrive)
    {
        return view('admin.backup_restore', [
            'localBackups' => $backups->localBackups(),
            'cloudBackups' => $googleDrive->listBackups(),
            'logs' => BackupRestoreLog::with('user')->latest()->paginate(20),
            'googleDriveConfigured' => $googleDrive->configured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        BackupJob::dispatch($request->user()->id, true);

        return back()->with('success', 'Backup started. You can monitor progress in the backup logs.');
    }

    public function download(string $fileName, BackupService $backups): BinaryFileResponse
    {
        $path = $backups->backupPath($fileName);
        abort_unless(is_file($path), 404);

        return response()->download($path, $fileName);
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
