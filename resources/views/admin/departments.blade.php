@extends('layouts.app')

@section('content')

{{-- ════════════════════════════════════════════
     TOAST
     ════════════════════════════════════════════ --}}
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">Message</span>
</div>

{{-- ════════════════════════════════════════════
     DELETE MODAL
     ════════════════════════════════════════════ --}}
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-body" style="text-align:center;padding:32px 24px 20px">
            <div class="delete-modal-icon">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">Delete Department?</div>
            <div id="deleteSubtitle" style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                All associated subjects and faculty assignments will be unlinked.
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('deleteModal')" class="btn-secondary">CANCEL</button>
            <button id="confirmDeleteBtn"
                style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 14px rgba(239,68,68,.25)">
                CONFIRM DELETE
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     CREATE / EDIT MODAL
     ════════════════════════════════════════════ --}}
<div id="deptModal" class="modal-overlay">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-head">
            <div style="display:flex;align-items:center;gap:10px">
                <div id="modalAvatarPreview"
                    style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--violet));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;letter-spacing:.04em">
                    ?
                </div>
                <span id="deptModalTitle" class="modal-title">New Department</span>
            </div>
            <button onclick="closeModal('deptModal')" class="modal-close">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="deptForm">
            @csrf
            <input type="hidden" id="modalDeptId">
            <input type="hidden" id="modalMode" value="create">
            <div class="modal-body" style="display:flex;flex-direction:column;gap:16px">
                <div class="form-group">
                    <label class="form-label">Department Name <span class="req">*</span></label>
                    <input id="modalName" class="form-input" type="text" required
                        placeholder="e.g. Faculty of Engineering"
                        oninput="updateAvatarPreview(this.value)">
                </div>
                <div class="form-group">
                    <label class="form-label">Dept Code <span class="req">*</span></label>
                    <input id="modalCode" class="form-input" type="text" required placeholder="e.g. ENG or SCI">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('deptModal')" class="btn-secondary">CANCEL</button>
                <button type="submit" id="modalSubmitBtn" class="btn-primary">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span id="modalSubmitLabel">SAVE DEPARTMENT</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════
     VIEW DETAIL MODAL
     ════════════════════════════════════════════ --}}
<div id="deptDetailModal" class="modal-overlay">
    <div class="modal-box" style="max-width:680px; max-height:90vh; display:flex; flex-direction:column;">
        <div class="modal-head" style="flex-shrink:0;">
            <span class="modal-title">Department Portfolio</span>
            <button onclick="closeModal('deptDetailModal')" class="modal-close">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0; flex:1; min-height:0; display:flex; flex-direction:column; overflow:hidden;">
            {{-- Profile hero --}}
            <div style="flex-shrink:0; padding:24px; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:18px">
                <div id="detailAvatar"
                    style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--violet));display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;box-shadow:0 0 20px rgba(37,99,235,.3);flex-shrink:0">
                    ?
                </div>
                <div>
                    <div id="detailName" style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--text)">—</div>
                    <div id="detailCode" style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.1em;margin-top:3px">—</div>
                    <div style="margin-top:8px; display: flex; gap: 8px;">
                        <span id="detailFacultyCount" class="status-tag tag-ready">0 FACULTY</span>
                        <span id="detailModuleCount" class="status-tag tag-active">0 MODULES</span>
                    </div>
                </div>
            </div>
            {{-- Split Lists Grid --}}
            <div style="flex:1; min-height:0; display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid var(--border);">
                {{-- Faculty list --}}
                <div style="border-right:1px solid var(--border); display: flex; flex-direction: column; overflow: hidden;">
                    <div style="flex-shrink:0; padding:12px 20px; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px">
                        <div style="width:5px; height:5px; border-radius:50%; background:var(--accent)"></div>
                        <span style="font-family:var(--font-mono); font-size:9px; font-weight:700; letter-spacing:.08em; color:var(--text2)">FACULTY MEMBERS</span>
                    </div>
                    <div id="detailFacultyList" style="flex:1; overflow-y:auto; padding:10px 0;">
                        {{-- Populated dynamically --}}
                    </div>
                </div>
                {{-- Modules list --}}
                <div style="display: flex; flex-direction: column; overflow: hidden;">
                    <div style="flex-shrink:0; padding:12px 20px; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px">
                        <div style="width:5px; height:5px; border-radius:50%; background:var(--green)"></div>
                        <span style="font-family:var(--font-mono); font-size:9px; font-weight:700; letter-spacing:.08em; color:var(--text2)">ACADEMIC MODULES</span>
                    </div>
                    <div id="detailModuleList" style="flex:1; overflow-y:auto; padding:10px 0;">
                        {{-- Populated dynamically --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="background:var(--surface2); flex-shrink:0;">
            <button onclick="closeModal('deptDetailModal')" class="btn-secondary">CLOSE</button>
            <button id="detailEditBtn" class="btn-primary">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                EDIT DETAILS
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     PAGE HEADER
     ════════════════════════════════════════════ --}}
<div class="page-header">
    <div>
        <div class="breadcrumb">
            <span>MANAGEMENT</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">DEPARTMENTS</span>
        </div>
        <h1 class="page-title">Organization Registry</h1>
        <p class="page-subtitle">FACULTIES, DEPARTMENTS & ACADEMIC UNITS</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <button onclick="window.open('{{ route('admin.export.departments') }}', '_blank')" class="btn-secondary" style="gap:7px; background:var(--surface3); border:1px solid var(--border)">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            EXPORT ALL
        </button>
        <button onclick="openCreateModal()" class="btn-primary" style="gap:7px">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            ADD DEPARTMENT
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════════
     STATS
     ════════════════════════════════════════════ --}}
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-glow"></div>
        <div class="stat-label">TOTAL DEPARTMENTS</div>
        <div class="stat-value">{{ $departments->total() }}</div>
        <span class="stat-pill">Organization</span>
    </div>
    <div class="stat-card green">
        <div class="stat-glow"></div>
        <div class="stat-label">TOTAL FACULTY</div>
        <div class="stat-value">{{ $departments->sum('teachers_count') }}</div>
        <span class="stat-pill">Personnel</span>
    </div>
    <div class="stat-card violet">
        <div class="stat-glow"></div>
        <div class="stat-label">TOTAL MODULES</div>
        <div class="stat-value">{{ $departments->sum('subjects_count') }}</div>
        <span class="stat-pill">Curriculum</span>
    </div>
</div>

{{-- ════════════════════════════════════════════
     TABLE PANEL
     ════════════════════════════════════════════ --}}
<div class="panel">
    <div class="catalog-toolbar">
        <div style="display:flex;align-items:center;gap:7px">
            <div style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent);"></div>
            <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2)">ORGANIZATION CHART</span>
        </div>
        
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="search-wrap" style="width: 250px; height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; padding: 0 12px;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="searchInput" value="{{ request('search') }}" type="text" placeholder="Search departments..." onkeyup="filterDepts(event)" style="border: none; background: transparent; color: var(--text); font-size: 11px; padding-left: 10px; width: 100%; outline: none;">
            </div>
            <div class="toolbar-count" style="background:var(--surface2); padding: 0 14px; height:36px; display:flex; align-items:center; border-radius:10px; border:1px solid var(--border)">
                <span style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--accent)">{{ $departments->total() }}</span>
                <span style="font-family:var(--font-mono); font-size:8px; color:var(--muted2); margin-left:6px; letter-spacing:.05em">UNITS</span>
            </div>
        </div>
    </div>

    <table class="att-table">
        <thead>
            <tr>
                <th>DEPT IDENTITY</th>
                <th>CODE</th>
                <th>FACULTY COUNT</th>
                <th>MODULES</th>
                <th style="text-align:right">ACTIONS</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @foreach($departments as $dept)
            @php
                $colors = ['#2563EB', '#8B5CF6', '#F59E0B', '#22C55E', '#EF4444'];
                $clr = $colors[$loop->index % count($colors)];
                $init = strtoupper(substr($dept->name, 0, 2));
            @endphp
            <tr data-id="{{ $dept->id }}" data-name="{{ strtolower($dept->name) }}" data-code="{{ $dept->code }}" class="fade-up">
                <td>
                    <div class="subject-cell">
                        <div class="subject-avatar" style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}33">
                            {{ $init }}
                        </div>
                        <div>
                            <div class="subject-name">{{ $dept->name }}</div>
                            <div class="subject-id">ID: #{{ str_pad($dept->id, 3, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                </td>
                <td><span style="font-family:var(--font-mono);font-size:11px;color:var(--accent)">{{ $dept->code }}</span></td>
                <td><span style="font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--text2)">{{ $dept->teachers_count }}</span></td>
                <td><span style="font-family:var(--font-mono);font-size:11px;color:var(--muted)">{{ $dept->subjects_count }} MODULES</span></td>
                <td style="text-align:right">
                    <button class="action-btn btn-view" title="View details" onclick="openDetailModal({{ $dept->id }})">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <button class="action-btn btn-edit" title="Edit" onclick="openEditModal(this.closest('tr'))">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @if(Auth::user()->isSuperAdmin())
                    <button class="action-btn btn-del" title="Delete" onclick="openDeleteModal({{ $dept->id }}, '{{ addslashes($dept->name) }}')">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="padding:12px 18px;border-top:1px solid var(--border)">
        {{ $departments->links('vendor.pagination.academy') }}
    </div>
</div>

<script>
let filterTimeout = null;
function filterDepts(e) {
    if (e && e.type === 'keyup' && e.key !== 'Enter') {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => filterDepts(), 800);
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

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const ic = document.getElementById('toastIcon');
    t.className = `toast show toast-${type}`;
    ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
    document.getElementById('toastMsg').textContent = msg;
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 3200);
}

function updateAvatarPreview(val) {
    const init = val.slice(0, 2).toUpperCase() || '?';
    document.getElementById('modalAvatarPreview').textContent = init;
}

function openCreateModal() {
    document.getElementById('deptModalTitle').textContent = 'New Department';
    document.getElementById('modalMode').value = 'create';
    document.getElementById('deptForm').reset();
    document.getElementById('modalAvatarPreview').textContent = '?';
    openModal('deptModal');
}

function openEditModal(row) {
    document.getElementById('deptModalTitle').textContent = 'Edit Department';
    document.getElementById('modalMode').value = 'edit';
    document.getElementById('modalDeptId').value = row.dataset.id;
    document.getElementById('modalName').value = row.querySelector('.subject-name').textContent;
    document.getElementById('modalCode').value = row.dataset.code;
    updateAvatarPreview(document.getElementById('modalName').value);
    openModal('deptModal');
}

let pendingDeleteId = null;
function openDeleteModal(id, name) {
    pendingDeleteId = id;
    document.getElementById('deleteSubtitle').innerHTML = `Deleting <strong style="color:var(--text2)">${name}</strong> will unlink all associated faculty and subjects.`;
    openModal('deleteModal');
}

document.getElementById('confirmDeleteBtn').onclick = async () => {
    const btn = document.getElementById('confirmDeleteBtn');
    const ogHtml = btn.innerHTML;
    btn.innerHTML = 'DELETING...';
    btn.disabled = true;
    try {
        const res = await fetch(`/api/admin/departments/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json().catch(() => ({ success: res.ok }));
        if (res.ok && data.success) {
            showToast('Department removed successfully', 'success');
            closeModal('deleteModal');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.error || 'Failed to remove department', 'error');
        }
    } catch(e) { 
        showToast('Network Error', 'error'); 
    } finally {
        btn.innerHTML = ogHtml;
        btn.disabled = false;
        pendingDeleteId = null;
    }
};

document.getElementById('deptForm').onsubmit = async e => {
    e.preventDefault();
    const mode = document.getElementById('modalMode').value;
    const id = document.getElementById('modalDeptId').value;
    const btn = document.getElementById('modalSubmitBtn');
    const ogHtml = btn.innerHTML;
    
    btn.innerHTML = '<span class="loading-spinner" style="width:12px;height:12px;border-width:2px;margin-right:8px"></span> SAVING...';
    btn.disabled = true;

    const payload = {
        name: document.getElementById('modalName').value,
        code: document.getElementById('modalCode').value,
    };

    try {
        const url = mode === 'create' ? '/api/admin/departments' : `/api/admin/departments/${id}`;
        const res = await fetch(url, {
            method: mode === 'create' ? 'POST' : 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
            showToast(mode === 'create' ? 'Department added successfully' : 'Department updated successfully', 'success');
            closeModal('deptModal');
            setTimeout(() => location.reload(), 900);
        } else if (data.errors) {
            const msgs = Object.values(data.errors).flat().join(' ');
            showToast(msgs, 'error');
        } else {
            showToast(data.message || 'Operation failed', 'error');
        }
    } catch(e) { 
        showToast('Network Error', 'error'); 
    } finally {
        btn.innerHTML = ogHtml;
        btn.disabled = false;
    }
};

async function openDetailModal(id) {
    try {
        const res = await fetch(`/api/admin/departments/${id}`);
        if (!res.ok) throw new Error('Failed to fetch details');
        const data = await res.json();
        if (data.success && data.department) {
            const dept = data.department;
            
            // Populate Hero info
            document.getElementById('detailName').textContent = dept.name;
            document.getElementById('detailCode').textContent = `CODE: ${dept.code}`;
            document.getElementById('detailFacultyCount').textContent = `${dept.teachers ? dept.teachers.length : 0} FACULTY`;
            document.getElementById('detailModuleCount').textContent = `${dept.subjects ? dept.subjects.length : 0} MODULES`;
            
            const init = dept.name.slice(0, 2).toUpperCase();
            document.getElementById('detailAvatar').textContent = init;
            
            // Populate Faculty List
            const facultyContainer = document.getElementById('detailFacultyList');
            facultyContainer.innerHTML = '';
            if (!dept.teachers || dept.teachers.length === 0) {
                facultyContainer.innerHTML = `
                    <div style="padding:40px 20px; text-align:center; color:var(--muted)">
                        <div style="font-size:24px; margin-bottom:8px">👨‍🏫</div>
                        <div style="font-family:var(--font-mono); font-size:9px; letter-spacing:.04em">NO FACULTY MEMBERS</div>
                    </div>
                `;
            } else {
                dept.teachers.forEach(teacher => {
                    const name = teacher.user ? teacher.user.name : teacher.name || 'Unknown Teacher';
                    const email = teacher.user ? teacher.user.email : 'No email address';
                    const classes = teacher.classes_count || 0;
                    const status = teacher.status || 'active';
                    const tInit = name.slice(0, 2).toUpperCase();
                    
                    let statusBadge = '';
                    if (status === 'active') {
                        statusBadge = '<span class="status-tag tag-active" style="padding:2px 6px; font-size:8px;">ACTIVE</span>';
                    } else if (status === 'on_leave') {
                        statusBadge = '<span class="status-tag tag-waiting" style="padding:2px 6px; font-size:8px;">LEAVE</span>';
                    } else {
                        statusBadge = '<span class="status-tag" style="padding:2px 6px; font-size:8px; background:var(--surface3); color:var(--muted2); border:1px solid var(--border2)">INACTIVE</span>';
                    }

                    const teacherRow = document.createElement('div');
                    teacherRow.style.cssText = "padding:12px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--border); transition:all .2s;";
                    teacherRow.onmouseenter = () => { teacherRow.style.background = "var(--surface2)"; };
                    teacherRow.onmouseleave = () => { teacherRow.style.background = "transparent"; };

                    teacherRow.innerHTML = `
                        <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--violet)); display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; color:#fff; flex-shrink:0; box-shadow:0 2px 6px rgba(37,99,235,.2)">
                            ${tInit}
                        </div>
                        <div style="flex:1; min-width:0">
                            <div style="font-size:12px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${name}</div>
                            <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${email}</div>
                        </div>
                        <div style="text-align:right; flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; gap:3px">
                            <span style="font-family:var(--font-mono); font-size:8px; color:var(--accent); font-weight:700; background:rgba(37,99,235,.08); padding:2px 6px; border-radius:4px; border:1px solid rgba(37,99,235,.12)">${classes} CLASSES</span>
                            ${statusBadge}
                        </div>
                    `;
                    facultyContainer.appendChild(teacherRow);
                });
            }
            
            // Populate Subjects/Modules List
            const moduleContainer = document.getElementById('detailModuleList');
            moduleContainer.innerHTML = '';
            if (!dept.subjects || dept.subjects.length === 0) {
                moduleContainer.innerHTML = `
                    <div style="padding:40px 20px; text-align:center; color:var(--muted)">
                        <div style="font-size:24px; margin-bottom:8px">📘</div>
                        <div style="font-family:var(--font-mono); font-size:9px; letter-spacing:.04em">NO ACADEMIC MODULES</div>
                    </div>
                `;
            } else {
                dept.subjects.forEach(sub => {
                    const name = sub.name;
                    const code = sub.code;
                    const classes = sub.classes_count || 0;
                    const mInit = code.slice(0, 2).toUpperCase();

                    const subRow = document.createElement('div');
                    subRow.style.cssText = "padding:12px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--border); transition:all .2s;";
                    subRow.onmouseenter = () => { subRow.style.background = "var(--surface2)"; };
                    subRow.onmouseleave = () => { subRow.style.background = "transparent"; };

                    subRow.innerHTML = `
                        <div style="width:30px; height:30px; border-radius:6px; background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.15); display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; color:var(--green); flex-shrink:0">
                            ${mInit}
                        </div>
                        <div style="flex:1; min-width:0">
                            <div style="font-size:12px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${name}</div>
                            <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${code}</div>
                        </div>
                        <div style="text-align:right; flex-shrink:0">
                            <span style="font-family:var(--font-mono); font-size:8px; color:var(--green); font-weight:700; background:rgba(34,197,94,.08); padding:2px 6px; border-radius:4px; border:1px solid rgba(34,197,94,.12)">${classes} CLASSES</span>
                        </div>
                    `;
                    moduleContainer.appendChild(subRow);
                });
            }

            // Edit button within detail portfolio
            document.getElementById('detailEditBtn').onclick = () => {
                closeModal('deptDetailModal');
                const tr = document.querySelector(`tr[data-id="${id}"]`);
                if (tr) {
                    openEditModal(tr);
                } else {
                    document.getElementById('deptModalTitle').textContent = 'Edit Department';
                    document.getElementById('modalMode').value = 'edit';
                    document.getElementById('modalDeptId').value = dept.id;
                    document.getElementById('modalName').value = dept.name;
                    document.getElementById('modalCode').value = dept.code;
                    updateAvatarPreview(dept.name);
                    openModal('deptModal');
                }
            };

            openModal('deptDetailModal');
        }
    } catch (e) {
        showToast('Error loading department details', 'error');
    }
}
</script>
@endsection
