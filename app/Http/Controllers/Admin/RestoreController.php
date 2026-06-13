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
use Illuminate\Support\Facades\Hash;

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

        $restoreService->validateBackup($data['file_name']);
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
}
