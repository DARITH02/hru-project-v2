@extends('layouts.app')

@section('content')
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">Message</span>
</div>

<div id="accountModal" class="modal-overlay">
    <div class="modal-box" style="max-width:460px">
        <div class="modal-head">
            <span class="modal-title">Manage Account Access</span>
            <button onclick="closeModal('accountModal')" class="modal-close">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="accountForm">
            @csrf
            <input type="hidden" id="modalUserId">
            <div class="modal-body">
                <div style="background:var(--surface2); padding:16px; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:20px; display:flex; align-items:center; gap:12px">
                    <div id="modalUserAvatar" style="width:40px; height:40px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700">?</div>
                    <div>
                        <div id="modalUserName" style="font-weight:700; color:var(--text); font-size:14px">—</div>
                        <div id="modalUserEmail" style="font-size:11px; color:var(--muted); font-family:var(--font-mono); margin-top:2px">—</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">System Role</label>
                    <select id="modalRole" class="form-input">
                        <option value="teacher">TEACHER (Standard Access)</option>
                        <option value="admin">ADMIN (Root Access)</option>
                        <option value="student">STUDENT (Restricted)</option>
                    </select>
                    <p style="font-size:10px; color:var(--muted); margin-top:6px; line-height:1.4">Changing a teacher to ADMIN will grant them full system control including deletion rights.</p>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Reset Password</label>
                    <div style="position:relative">
                        <input id="modalPassword" class="form-input" type="text" placeholder="Leave blank to keep current">
                        <button type="button" onclick="generatePass()" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:var(--surface3); border:1px solid var(--border); border-radius:4px; padding:4px 8px; font-family:var(--font-mono); font-size:9px; color:var(--accent); cursor:pointer">GENERATE</button>
                    </div>
                    <p style="font-size:10px; color:var(--amber); margin-top:6px">⚠️ Security Note: Passwords are encrypted after saving. Ensure the user is notified of the new credentials.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('accountModal')" class="btn-secondary">CANCEL</button>
                <button type="submit" class="btn-primary" id="saveBtn">UPDATE CREDENTIALS</button>
            </div>
        </form>
    </div>
</div>

<div class="page-header">
    <div>
        <div class="breadcrumb"><span>MANAGEMENT</span><span class="breadcrumb-sep">/</span><span class="breadcrumb-current">TEACHER ACCOUNTS</span></div>
        <h1 class="page-title">Credential Authority</h1>
        <p class="page-subtitle">SECURE ACCESS CONTROL & PASSWORD MANAGEMENT</p>
    </div>
</div>

<div class="panel">
    <div class="catalog-toolbar">
        <div style="display:flex;align-items:center;gap:7px">
            <div style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent);animation:blink 2s infinite"></div>
            <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2)">ACCESS DIRECTORY</span>
        </div>
        
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="search-wrap" style="width: 250px; height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; padding: 0 12px;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="searchInput" value="{{ request('search') }}" type="text" placeholder="Search accounts..." onkeyup="filterAccounts(event)" style="border: none; background: transparent; color: var(--text); font-size: 11px; padding-left: 10px; width: 100%; outline: none;">
            </div>
            <div class="toolbar-count" style="background:var(--surface2); padding: 0 14px; height:36px; display:flex; align-items:center; border-radius:10px; border:1px solid var(--border)">
                <span style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--accent)">{{ $users->total() }}</span>
                <span style="font-family:var(--font-mono); font-size:8px; color:var(--muted2); margin-left:6px; letter-spacing:.05em">USERS</span>
            </div>
        </div>
    </div>

    <table class="att-table">
        <thead>
            <tr>
                <th>USER IDENTITY</th>
                <th>SYSTEM ROLE</th>
                <th>STATUS</th>
                <th>LAST UPDATED</th>
                <th style="text-align:right">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php
                $roleCol = $user->role === 'admin' ? 'var(--red)' : ($user->role === 'teacher' ? 'var(--accent)' : 'var(--muted)');
                $init = strtoupper(substr($user->name, 0, 1));
            @endphp
            <tr data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-role="{{ $user->role }}">
                <td>
                    <div style="display:flex; align-items:center; gap:12px">
                        <div style="width:32px; height:32px; border-radius:50%; background:{{ $roleCol }}22; color:{{ $roleCol }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px; border:1px solid {{ $roleCol }}33">
                            {{ $init }}
                        </div>
                        <div>
                            <div style="font-weight:600; color:var(--text); font-size:13px">{{ $user->name }}</div>
                            <div style="font-family:var(--font-mono); font-size:10px; color:var(--muted); margin-top:1px">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="status-tag" style="background:{{ $roleCol }}15; color:{{ $roleCol }}; border:1px solid {{ $roleCol }}30">
                        {{ strtoupper($user->role) }}
                    </span>
                </td>
                <td>
                    @if($user->is_approved)
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div style="width:6px; height:6px; border-radius:50%; background:var(--green)"></div>
                            <span style="font-size:10px; color:var(--text2)">APPROVED</span>
                        </div>
                    @else
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div style="width:6px; height:6px; border-radius:50%; background:var(--amber); animation: blink 1.5s infinite"></div>
                            <span style="font-size:10px; color:var(--amber)">PENDING</span>
                        </div>
                    @endif
                </td>
                <td>
                    <span style="font-family:var(--font-mono); font-size:10px; color:var(--muted)">{{ $user->updated_at->format('M d, Y H:i') }}</span>
                </td>
                <td style="text-align:right">
                    <div style="display:flex; justify-content:flex-end; gap:8px">
                        @if(auth()->user()->isSuperAdmin())
                            @if(!$user->is_approved)
                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button type="submit" class="action-btn" style="color:var(--green); background:color-mix(in srgb, var(--green) 8%, transparent)" title="Approve Account">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @endif
                            
                            <button class="action-btn btn-edit" title="Manage Credentials" onclick="openAccountModal(this.closest('tr'))">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-7.743-5.743L11 3l1 1 1-1 1 1 1-1 1 1 1-1 1 1"/></svg>
                            </button>

                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline" onsubmit="return confirmSubmit(event, 'Are you sure you want to delete this account? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn" style="color:var(--red); background:color-mix(in srgb, var(--red) 8%, transparent)" title="Delete Account">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @else
                             <button class="action-btn btn-edit" title="Manage Credentials" onclick="openAccountModal(this.closest('tr'))">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-7.743-5.743L11 3l1 1 1-1 1 1 1-1 1 1 1-1 1 1"/></svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:12px 18px; border-top:1px solid var(--border)">
        {{ $users->links('vendor.pagination.academy') }}
    </div>
</div>

<script>
let filterTimeout = null;
function filterAccounts(e) {
    if (e && e.type === 'keyup' && e.key !== 'Enter') {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => filterAccounts(), 800);
        return;
    }
    const q = document.getElementById('searchInput').value;
    const params = new URLSearchParams(window.location.search);
    if (q) params.set('search', q); else params.delete('search');
    params.set('page', 1);
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const ic = document.getElementById('toastIcon');
    t.className = `toast show toast-${type}`;
    ic.textContent = type === 'success' ? '✓' : '✕';
    document.getElementById('toastMsg').textContent = msg;
    setTimeout(() => t.classList.remove('show'), 3000);
}

function openAccountModal(row) {
    document.getElementById('modalUserId').value = row.dataset.id;
    document.getElementById('modalUserName').textContent = row.dataset.name;
    document.getElementById('modalUserEmail').textContent = row.dataset.email;
    document.getElementById('modalRole').value = row.dataset.role;
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalUserAvatar').textContent = row.dataset.name.charAt(0).toUpperCase();
    openModal('accountModal');
}

function generatePass() {
    const chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*";
    let pass = "";
    for (let i = 0; i < 10; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('modalPassword').value = pass;
}

document.getElementById('accountForm').onsubmit = async e => {
    e.preventDefault();
    const id = document.getElementById('modalUserId').value;
    const btn = document.getElementById('saveBtn');
    const ogText = btn.innerText;
    btn.innerText = 'UPDATING...';
    btn.disabled = true;

    try {
        const password = document.getElementById('modalPassword').value;
        const role = document.getElementById('modalRole').value;
        
        const res = await fetch(`/api/admin/accounts/${id}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ password, role })
        });
        
        const data = await res.json();
        if (data.success) {
            showToast('Account credentials updated');
            closeModal('accountModal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.error || 'Failed to update', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.innerText = ogText;
        btn.disabled = false;
    }
};
</script>
@endsection
