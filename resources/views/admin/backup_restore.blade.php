@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">
            <span>ADMIN</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">BACKUP & RESTORE</span>
        </div>
        <h1 class="page-title">Backup & Restore</h1>
        <p class="page-subtitle">LOCAL ZIP BACKUPS · GOOGLE DRIVE · RESTORE AUDIT LOGS</p>
    </div>
    <form method="POST" action="{{ route('admin.backup-restore.backup') }}">
        @csrf
        <button class="btn-primary" type="submit" style="height:42px;font-weight:800;">Backup Now</button>
    </form>
</div>

@foreach (['success' => 'green', 'error' => 'red'] as $flash => $color)
    @if(session($flash))
        <div class="panel" style="padding:14px 18px;border-color:var(--{{ $color }});color:var(--{{ $color }});font-weight:700;">
            {{ session($flash) }}
        </div>
    @endif
@endforeach

<div class="main-grid" style="grid-template-columns:minmax(0,1fr) 360px;">
    <div style="display:flex;flex-direction:column;gap:18px;min-width:0;">
        <section class="panel">
            <div class="panel-head" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="font-family:var(--font-mono);font-size:10px;font-weight:800;letter-spacing:.1em;color:var(--text2);">LOCAL BACKUPS</div>
                <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted);">{{ count($localBackups) }} files</div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localBackups as $backup)
                            <tr>
                                <td style="font-family:var(--font-mono);font-size:11px;">{{ $backup['name'] }}</td>
                                <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                <td>{{ $backup['modified_at'] }}</td>
                                <td>
                                    <div style="display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;">
                                        <a class="btn-secondary" href="{{ route('admin.backup-restore.download', $backup['name']) }}" style="height:32px;padding:0 12px;">Download</a>
                                        <button class="btn-secondary" type="button" onclick="openRestoreModal('{{ $backup['name'] }}')" style="height:32px;padding:0 12px;color:var(--amber);">Restore</button>
                                        <form method="POST" action="{{ route('admin.backup-restore.local.destroy', $backup['name']) }}" onsubmit="return confirm('Delete this local backup?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-secondary" type="submit" style="height:32px;padding:0 12px;color:var(--red);">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;padding:34px;color:var(--muted);">No local backups found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="font-family:var(--font-mono);font-size:10px;font-weight:800;letter-spacing:.1em;color:var(--text2);">GOOGLE DRIVE BACKUPS</div>
                <div style="font-family:var(--font-mono);font-size:10px;color:{{ $googleDriveConfigured ? 'var(--green)' : 'var(--red)' }};">{{ $googleDriveConfigured ? 'CONNECTED' : 'NOT CONFIGURED' }}</div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cloudBackups as $backup)
                            <tr>
                                <td style="font-family:var(--font-mono);font-size:11px;">{{ $backup['name'] ?? 'Unknown' }}</td>
                                <td>{{ isset($backup['size']) ? number_format(((int) $backup['size']) / 1024 / 1024, 2) . ' MB' : 'N/A' }}</td>
                                <td>{{ isset($backup['createdTime']) ? \Carbon\Carbon::parse($backup['createdTime'])->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td>
                                    <div style="display:flex;justify-content:flex-end;">
                                        <form method="POST" action="{{ route('admin.backup-restore.cloud.destroy', $backup['id']) }}" onsubmit="return confirm('Delete this Google Drive backup?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-secondary" type="submit" style="height:32px;padding:0 12px;color:var(--red);">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;padding:34px;color:var(--muted);">No Google Drive backups found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="panel" style="align-self:start;">
        <div class="panel-head" style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-family:var(--font-mono);font-size:10px;font-weight:800;letter-spacing:.1em;color:var(--text2);">BACKUP LOGS</div>
        </div>
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:10px;max-height:680px;overflow:auto;">
            @forelse($logs as $log)
                <div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:var(--surface2);">
                    <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                        <strong style="font-size:12px;color:var(--text);">{{ strtoupper(str_replace('_', ' ', $log->action)) }}</strong>
                        <span style="font-family:var(--font-mono);font-size:9px;color:{{ $log->status === 'success' ? 'var(--green)' : ($log->status === 'failed' ? 'var(--red)' : 'var(--amber)') }};">{{ strtoupper($log->status) }}</span>
                    </div>
                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted);margin-top:5px;word-break:break-all;">{{ $log->file_name ?: 'No file' }}</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:6px;">{{ $log->message }}</div>
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted2);margin-top:8px;">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</div>
                </div>
            @empty
                <div style="padding:28px;text-align:center;color:var(--muted);">No logs yet.</div>
            @endforelse
        </div>
        <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $logs->links() }}</div>
    </aside>
</div>

<div id="restoreModal" class="modal-overlay">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-head">
            <span class="modal-title">Confirm Restore</span>
            <button type="button" onclick="closeRestoreModal()" class="modal-close">×</button>
        </div>
        <form method="POST" action="{{ route('admin.backup-restore.restore') }}">
            @csrf
            <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;">
                <input type="hidden" name="file_name" id="restoreFileName">
                <div style="padding:14px;border:1px solid rgba(239,68,68,.3);border-radius:12px;background:rgba(239,68,68,.08);color:var(--red);font-size:12px;line-height:1.6;">
                    Restore will create an emergency backup, put the app into maintenance mode, restore database and storage files, clear cache, and bring the app online again.
                </div>
                <div>
                    <label class="form-label">Backup File</label>
                    <div id="restoreFileLabel" style="font-family:var(--font-mono);font-size:11px;color:var(--text);word-break:break-all;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Super Admin Password</label>
                    <input class="form-input" type="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeRestoreModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary" style="background:var(--red);border:none;">Start Restore</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRestoreModal(fileName) {
        document.getElementById('restoreFileName').value = fileName;
        document.getElementById('restoreFileLabel').textContent = fileName;
        document.getElementById('restoreModal').classList.add('open');
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.remove('open');
    }
</script>
@endsection
