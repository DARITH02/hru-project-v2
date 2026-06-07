@extends('layouts.app')

@section('content')

{{-- ═══ TOAST ═══ --}}
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">Message</span>
</div>

{{-- ═══ DELETE MODAL ═══ --}}
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-body" style="text-align:center;padding:28px 22px 20px;">
            <div class="delete-modal-icon">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">Delete Group?</div>
            <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.6">This will remove the group but NOT the students.<br>Students will become unassigned.</div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('deleteModal')" class="btn-secondary">CANCEL</button>
            <button id="confirmDeleteBtn" style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;">
                DELETE ENTRY
            </button>
        </div>
    </div>
</div>

{{-- ═══ CLASS GROUP MODAL ═══ --}}
<div id="groupModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <span id="groupModalTitle" class="modal-title">Create Class Group</span>
            <button onclick="closeModal('groupModal')" class="modal-close">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="groupForm">
            @csrf
            <input type="hidden" id="groupId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Group Name <span class="req">*</span></label>
                    <input id="groupName" name="name" class="form-input" type="text" required placeholder="e.g. CS Batch 2026-A">
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Specialized Major <span class="req">*</span></label>
                        <select id="groupMajor" name="major_id" class="form-input" required>
                            <option value="" disabled selected>Select Major...</option>
                            @foreach($majors as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->department->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Academic Year Level <span class="req">*</span></label>
                        <select id="groupYear" name="year_level" class="form-input" required>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                            <option value="5">Year 5</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('groupModal')" class="btn-secondary">CANCEL</button>
                <button type="submit" class="btn-primary">SAVE GROUP</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MAJOR MODAL ═══ --}}
<div id="majorModal" class="modal-overlay">
    <div class="modal-box" style="max-width:450px">
        <div class="modal-head">
            <span id="majorModalTitle" class="modal-title">Manage Major</span>
            <button onclick="closeModal('majorModal')" class="modal-close">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="majorForm">
            @csrf
            <input type="hidden" id="majorId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Major Name <span class="req">*</span></label>
                    <input id="majorNameInput" name="name" class="form-input" type="text" required placeholder="e.g. Software Engineering">
                </div>
                <div class="form-group">
                    <label class="form-label">Major Code</label>
                    <input id="majorCode" name="code" class="form-input" type="text" placeholder="e.g. SE-CS">
                </div>
                <div class="form-group">
                    <label class="form-label">Department <span class="req">*</span></label>
                    <select id="majorDept" name="department_id" class="form-input" required>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('majorModal')" class="btn-secondary">CANCEL</button>
                <button type="submit" class="btn-primary">SAVE MAJOR</button>
            </div>
        </form>
    </div>
</div>

    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>ACADEMIC</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">STUDENT GROUPS</span>
            </div>
            <h1 class="page-title">Student Group Registry</h1>
            <p class="page-subtitle">STUDENT COHORTS & ACADEMIC PATHWAYS</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <button onclick="window.open('{{ route('admin.export.classes') }}', '_blank')" class="btn-secondary" style="gap:7px; background:var(--surface3); border:1px solid var(--border)">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                EXPORT ALL
            </button>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="main-grid">
        {{-- LEFT: Class Groups --}}
        <div class="panel">
            <div class="catalog-toolbar" style="padding: 16px 20px;">
                <div style="display:flex;align-items:center;gap:10px; flex: 1;">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 10px var(--accent)"></div>
                    <span style="font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.12em;color:var(--text2)">CLASS GROUPS</span>
                </div>
                
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="search-wrap" style="width: 220px; height: 34px; background: var(--surface3); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; padding: 0 10px;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        <input id="searchInput" value="{{ request('search') }}" type="text" placeholder="Search groups..." onkeyup="filterGroups(event)" style="border: none; background: transparent; color: var(--text); font-size: 10px; padding-left: 8px; width: 100%; outline: none;">
                    </div>
                    <button class="btn-primary" onclick="openCreateGroupModal()" style="gap:7px; height:34px; padding: 0 12px; font-size:10px">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        NEW GROUP
                    </button>
                </div>
            </div>

            <table class="att-table">
                <thead>
                    <tr>
                        <th>GROUP NAME</th>
                        <th>MAJOR / PATHWAY</th>
                        <th>YEAR</th>
                        <th>STUDENTS</th>
                        <th style="text-align:right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($classGroups as $group)
                    <tr>
                        <td>
                            <div style="font-weight:700;color:var(--text)">{{ $group->name }}</div>
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted)">ID: #{{ str_pad($group->id, 3, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td>
                            <div style="color:var(--text2)">{{ $group->major->name ?? 'Unassigned' }}</div>
                            <div style="font-size:9px;color:var(--muted)">{{ $group->major->department->name ?? 'General' }}</div>
                        </td>
                        <td>
                            <span style="background:var(--accent)18;color:var(--accent);padding:2px 8px;border-radius:6px;font-family:var(--font-mono);font-size:10px;font-weight:700">YEAR {{ $group->year_level }}</span>
                        </td>
                        <td>
                            <div style="font-family:var(--font-display);font-size:15px;font-weight:800;color:var(--text)">{{ $group->students_count }}</div>
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;justify-content:flex-end;gap:6px">
                                <button class="action-btn btn-edit" title="Edit" 
                                    onclick="openEditGroupModal({{ json_encode($group) }})">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button class="action-btn btn-del" onclick="openDeleteGroupModal({{ $group->id }})">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No class groups defined.</td></tr>
                @endforelse
                </tbody>
            </table>
            @if($classGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div style="padding:15px; border-top:1px solid var(--border)">
                    {{ $classGroups->links('vendor.pagination.academy') }}
                </div>
            @endif
        </div>

        {{-- RIGHT: Majors --}}
        <div style="display:flex;flex-direction:column;gap:16px; ">
            <div class="side-panel" style="padding:0">
                <div class="side-panel-head" style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--violet)"></span>
                        ACADEMIC MAJORS
                    </div>
                    <button class="action-btn" onclick="openCreateMajorModal()" style="width:24px;height:24px;background:var(--accent);color:#fff;border:none">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
                <div style="padding:10px">
                    @foreach($majors as $m)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:12px;margin-bottom:6px;background:var(--surface3);border:1px solid var(--border)">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:var(--text)">{{ $m->name }}</div>
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted)">{{ $m->code ?? 'NO-CODE' }} • {{ $m->department->name ?? 'N/A' }}</div>
                        </div>
                        <div style="display:flex;gap:4px">
                            <button class="action-btn" style="width:22px;height:22px;opacity:0.6" onclick="openEditMajorModal({{ json_encode($m) }})">
                                <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="action-btn" style="width:22px;height:22px;opacity:0.6" onclick="deleteMajor({{ $m->id }})">
                                <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Quick Insights --}}
            <div class="side-panel">
                <div class="side-panel-head">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--green)"></span>
                    GROUP INSIGHTS
                </div>
                <div style="padding:15px">
                    <div style="font-size:11px; color:var(--muted); line-height:1.6">
                        Class groups represent a fixed set of students. When you create a <span style="color:var(--accent);font-weight:700">Catalog Entry</span>, assign it to a group to automatically enroll all its members.
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type='success') {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

let filterTimeout = null;
function filterGroups(e) {
    if (e && e.type === 'keyup' && e.key !== 'Enter') {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => filterGroups(), 800);
        return;
    }
    const q = document.getElementById('searchInput').value;
    const params = new URLSearchParams(window.location.search);
    if (q) params.set('search', q); else params.delete('search');
    params.set('page', 1);
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

// GROUP LOGIC
function openCreateGroupModal() {
    document.getElementById('groupModalTitle').textContent = 'Create Class Group';
    document.getElementById('groupId').value = '';
    document.getElementById('groupForm').reset();
    openModal('groupModal');
}

function openEditGroupModal(group) {
    document.getElementById('groupModalTitle').textContent = 'Modify Group';
    document.getElementById('groupId').value = group.id;
    document.getElementById('groupName').value = group.name;
    document.getElementById('groupMajor').value = group.major_id;
    document.getElementById('groupYear').value = group.year_level;
    openModal('groupModal');
}

document.getElementById('groupForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const ogHtml = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner" style="width:12px;height:12px;border-width:2px;margin-right:8px"></span> SAVING...';
    btn.disabled = true;

    const id = document.getElementById('groupId').value;
    const url = id ? `/api/admin/class-groups/${id}` : '/api/admin/class-groups';
    const method = id ? 'PUT' : 'POST';
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        });
        const resData = await res.json();
        if (resData.success) {
            showToast(id ? 'Group updated successfully.' : 'New class group registered.');
            closeModal('groupModal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(resData.error || 'Failed to save group.', 'error');
        }
    } catch(err) { showToast('Network Error', 'error'); }
    btn.innerHTML = ogHtml;
    btn.disabled = false;
});

let pendingDelId = null;
function openDeleteGroupModal(id) {
    pendingDelId = id;
    openModal('deleteModal');
}
document.getElementById('confirmDeleteBtn').onclick = async () => {
    const btn = document.getElementById('confirmDeleteBtn');
    const ogHtml = btn.innerHTML;
    btn.innerHTML = 'DELETING...';
    btn.disabled = true;

    try {
        const res = await fetch(`/api/admin/class-groups/${pendingDelId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const resData = await res.json();
        if (resData.success) {
            showToast('Group removed from system.');
            closeModal('deleteModal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(resData.error || 'Deletion failed.', 'error');
        }
    } catch(err) { showToast('Error deleting', 'error'); }
    btn.innerHTML = ogHtml;
    btn.disabled = false;
};

// MAJOR LOGIC
function openCreateMajorModal() {
    document.getElementById('majorModalTitle').textContent = 'Create Major';
    document.getElementById('majorId').value = '';
    document.getElementById('majorForm').reset();
    openModal('majorModal');
}

function openEditMajorModal(major) {
    document.getElementById('majorModalTitle').textContent = 'Edit Major';
    document.getElementById('majorId').value = major.id;
    document.getElementById('majorNameInput').value = major.name;
    document.getElementById('majorCode').value = major.code || '';
    document.getElementById('majorDept').value = major.department_id;
    openModal('majorModal');
}

document.getElementById('majorForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const ogHtml = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner" style="width:12px;height:12px;border-width:2px;margin-right:8px"></span> SAVING...';
    btn.disabled = true;

    const id = document.getElementById('majorId').value;
    const url = id ? `/api/admin/majors/${id}` : '/api/admin/majors';
    const method = id ? 'PUT' : 'POST';
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        });
        const resData = await res.json();
        if (resData.success) {
            showToast(id ? 'Major updated.' : 'Academic major created.');
            closeModal('majorModal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(resData.error || 'Failed to save major.', 'error');
        }
    } catch(err) { showToast('Network Error', 'error'); }
    btn.innerHTML = ogHtml;
    btn.disabled = false;
});

async function deleteMajor(id) {
    if (!confirm('This action will remove the academic major. All linked student cohorts will be affected. Continue?')) return;
    
    try {
        const res = await fetch(`/api/admin/majors/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const resData = await res.json();
        if (resData.success) {
            showToast('Major removed successfully.');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(resData.error || 'Delete failed.', 'error');
        }
    } catch(err) { showToast('Error deleting', 'error'); }
}
</script>
@endpush
