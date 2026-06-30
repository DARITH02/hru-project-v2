<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RestoreBackupJob;
use App\Jobs\RestoreCloudBackupJob;
use App\Services\BackupService;
use App\Services\RestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Throwable;

class RestoreController extends Controller
{
    public function store(Request $request, RestoreService $restoreService): RedirectResponse
    {
        $data = $request->validate([
            'file_name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        abort_unless($request->user()->isSuperAdmin(), 403);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->with('error', 'Password confirmation failed.');
        }

        if (Cache::has('backup_restore:restore_running')) {
            return back()->with('error', 'Another restore is already running.');
        }

        try {
            $restoreService->validateBackup($data['file_name']);
        } catch (Throwable $e) {
            return back()->with('error', 'Backup cannot be restored: ' . $e->getMessage());
        }

        RestoreBackupJob::dispatch($data['file_name'], $request->user()->id);

        return back()->with('success', 'Restore started. The app will enter maintenance mode while the restore runs.');
    }

    public function storeCloud(Request $request, BackupService $backupService): RedirectResponse
    {
        $data = $request->validate([
            'file_id' => ['required', 'string'],
            'file_name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        abort_unless($request->user()->isSuperAdmin(), 403);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->with('error', 'Password confirmation failed.');
        }

        if (Cache::has('backup_restore:restore_running')) {
            return back()->with('error', 'Another restore is already running.');
        }

        $backupService->assertValidBackupFileName($data['file_name']);
        RestoreCloudBackupJob::dispatch($data['file_id'], $data['file_name'], $request->user()->id);

        return back()->with('success', 'Cloud restore started. The backup will be downloaded from Google Drive before restore.');
    }

    public function upload(Request $request, BackupService $backupService, RestoreService $restoreService): RedirectResponse
    {
        $data = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:zip', 'max:512000'],
            'password' => ['required', 'string'],
        ]);

        abort_unless($request->user()->isSuperAdmin(), 403);

        if (!Hash::check($data['password'], $request->user()->password)) {
            return back()->with('error', 'Password confirmation failed.');
        }

        if (Cache::has('backup_restore:restore_running')) {
            return back()->with('error', 'Another restore is already running.');
        }

        $fileName = 'hru_ats_backup_' . now()->format('Y_m_d_H_i_s') . '.zip';
        $targetPath = $backupService->backupPath($fileName);

        File::ensureDirectoryExists($backupService->backupDirectory(), 0750, true);
        $request->file('backup_file')->move($backupService->backupDirectory(), $fileName);

        try {
            $restoreService->validateBackup($fileName);
        } catch (Throwable $e) {
            File::delete($targetPath);
            report($e);

            return back()->with('error', 'Uploaded backup is not valid: ' . $e->getMessage());
        }

        RestoreBackupJob::dispatch($fileName, $request->user()->id);

        return back()->with('success', 'Uploaded backup saved and restore started.');
    }
}
