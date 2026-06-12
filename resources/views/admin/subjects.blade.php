@extends('layouts.app')

@section('content')
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">{{ __('admin_subjects.message') }}</span>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-body" style="text-align:center;padding:32px 24px 20px">
            <div class="delete-modal-icon">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">{{ __('admin_subjects.delete_subject') }}</div>
            <div id="deleteSubtitle" style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                {{ __('admin_subjects.delete_subject_desc') }}
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('deleteModal')" class="btn-secondary">{{ __('admin_subjects.cancel') }}</button>
            <button id="confirmDeleteBtn" class="btn-primary" style="background:var(--red)">{{ __('admin_subjects.confirm_delete') }}</button>
        </div>
    </div>
</div>

<div id="subjectModal" class="modal-overlay">
    <div class="modal-box" style="max-width:500px">
        <div class="modal-head">
            <span id="modalTitle" class="modal-title">{{ __('admin_subjects.new_subject_module') }}</span>
            <button onclick="closeModal('subjectModal')" class="modal-close"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form id="subjectForm">
            @csrf
            <input type="hidden" id="modalId">
            <input type="hidden" id="modalMode" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ __('admin_subjects.subject_title') }} <span class="req">*</span></label>
                    <input id="modalName" name="name" class="form-input" type="text" required placeholder="{{ __('admin_subjects.name_placeholder') }}">
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_subjects.course_code') }}</label>
                        <input id="modalCode" name="code" class="form-input" type="text" placeholder="{{ __('admin_subjects.code_placeholder') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_subjects.department') }} <span class="req">*</span></label>
                        <select id="modalDept" name="department_id" class="form-input" required>
                            <option value="">{{ __('admin_subjects.select_department') }}</option>
                            @foreach($depts as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('subjectModal')" class="btn-secondary">{{ __('admin_subjects.cancel') }}</button>
                <button type="submit" id="modalSubmitBtn" class="btn-primary">{{ __('admin_subjects.save_subject') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="page-header">
    <div>
        <div class="breadcrumb"><span>{{ __('admin_subjects.academic') }}</span><span class="breadcrumb-sep">/</span><span class="breadcrumb-current">{{ __('admin_subjects.subjects') }}</span></div>
        <h1 class="page-title">{{ __('admin_subjects.subject_catalog') }}</h1>
        <p class="page-subtitle">{{ __('admin_subjects.subtitle') }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <button onclick="window.open('{{ route('admin.export.subjects') }}', '_blank')" class="btn-secondary" style="gap:7px; background:var(--surface3); border:1px solid var(--border)">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('admin_subjects.export_all') }}
        </button>
        <button onclick="openCreateModal()" class="btn-primary" style="gap:7px"><svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>{{ __('admin_subjects.new_subject') }}</button>
    </div>
</div>

<div class="panel">
    <div class="catalog-toolbar">
        <div style="display:flex;align-items:center;gap:7px">
            <div style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent)"></div>
            <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2)">{{ __('admin_subjects.module_registry') }}</span>
        </div>
        
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="search-wrap" style="width: 250px; height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; padding: 0 12px;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="searchInput" value="{{ request('search') }}" type="text" placeholder="{{ __('admin_subjects.search_modules') }}" onkeyup="filterSubjects(event)" style="border: none; background: transparent; color: var(--text); font-size: 11px; padding-left: 10px; width: 100%; outline: none;">
            </div>
            <div class="toolbar-count" style="background:var(--surface2); padding: 0 14px; height:36px; display:flex; align-items:center; border-radius:10px; border:1px solid var(--border)">
                <span style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--accent)">{{ $subjects->total() }}</span>
                <span style="font-family:var(--font-mono); font-size:8px; color:var(--muted2); margin-left:6px; letter-spacing:.05em">{{ __('admin_subjects.subjects') }}</span>
            </div>
        </div>
    </div>

    <table class="att-table">
        <thead>
            <tr>
                <th>{{ __('admin_subjects.code') }}</th>
                <th>{{ __('admin_subjects.subject_title') }}</th>
                <th>{{ __('admin_subjects.department') }}</th>
                <th>{{ __('admin_subjects.classes') }}</th>
                <th style="text-align:right">{{ __('admin_subjects.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $sub)
            <tr data-id="{{ $sub->id }}" data-name="{{ $sub->name }}" data-code="{{ $sub->code }}" data-dept="{{ $sub->department_id }}">
                <td><span style="font-family:var(--font-mono);font-size:11px;color:var(--accent)">{{ $sub->code ?? '—' }}</span></td>
                <td><div style="font-weight:600;color:var(--text)">{{ $sub->name }}</div></td>
                <td><span class="status-tag" style="background:var(--surface3);color:var(--text2)">{{ $sub->department->name ?? __('admin_subjects.unassigned') }}</span></td>
                <td><span style="font-family:var(--font-mono);color:var(--muted)">{{ $sub->classes_count }} CLS</span></td>
                <td style="text-align:right">
                    <button class="action-btn btn-edit" title="{{ __('admin_subjects.edit') }}" onclick="openEditModal(this.closest('tr'))"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                    <button class="action-btn btn-del" title="{{ __('admin_subjects.delete') }}" onclick="openDeleteModal({{ $sub->id }}, '{{ addslashes($sub->name) }}')"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:12px 18px;border-top:1px solid var(--border)">
        {{ $subjects->links('vendor.pagination.academy') }}
    </div>
</div>

<script>
const subjectTranslations = @json(__('admin_subjects'));
let filterTimeout = null;
function filterSubjects(e) {
    if (e && e.type === 'keyup' && e.key !== 'Enter') {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => filterSubjects(), 800);
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
    t.className = `toast show toast-${type}`;
    document.getElementById('toastMsg').textContent = subjectTranslations[msg] || msg;
    setTimeout(() => t.classList.remove('show'), 3000);
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = subjectTranslations.new_subject_module;
    document.getElementById('modalMode').value = 'create';
    document.getElementById('subjectForm').reset();
    openModal('subjectModal');
}

function openEditModal(row) {
    document.getElementById('modalTitle').textContent = subjectTranslations.edit_subject;
    document.getElementById('modalMode').value = 'edit';
    document.getElementById('modalId').value = row.dataset.id;
    document.getElementById('modalName').value = row.dataset.name;
    document.getElementById('modalCode').value = row.dataset.code || '';
    document.getElementById('modalDept').value = row.dataset.dept || '';
    openModal('subjectModal');
}

let pendingDeleteId = null;
function openDeleteModal(id, name) {
    pendingDeleteId = id;
    document.getElementById('deleteSubtitle').textContent = subjectTranslations.permanent_delete.replace(':name', name);
    openModal('deleteModal');
}

document.getElementById('confirmDeleteBtn').onclick = async () => {
    try {
        const res = await fetch(`/api/admin/subjects/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        if (res.ok) {
            showToast('subject_deleted');
            location.reload();
        }
    } catch(e) { showToast('error_deleting', 'error'); }
};

document.getElementById('subjectForm').onsubmit = async e => {
    e.preventDefault();
    const mode = document.getElementById('modalMode').value;
    const id = document.getElementById('modalId').value;
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    
    try {
        const url = mode === 'create' ? '/api/admin/subjects' : `/api/admin/subjects/${id}`;
        const res = await fetch(url, {
            method: mode === 'create' ? 'POST' : 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            showToast(mode === 'create' ? 'subject_created' : 'subject_updated');
            location.reload();
        }
    } catch(e) { showToast('error_saving', 'error'); }
};
</script>
@endsection
