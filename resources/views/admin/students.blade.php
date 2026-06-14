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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div
                    style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">
                    Expel Student?</div>
                <div id="deleteSubtitle"
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                    This student will be removed from the enrollment database.<br>All attendance records will be archived.
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('deleteModal')" class="btn-secondary">CANCEL</button>
                <button id="confirmDeleteBtn"
                    style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 14px rgba(239,68,68,.25)">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    CONFIRM EXPEL
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
    BULK DELETE MODAL
    ════════════════════════════════════════════ --}}
    <div id="bulkDeleteModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px">
            <div class="modal-body" style="text-align:center;padding:32px 24px 20px">
                <div class="delete-modal-icon">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16" />
                    </svg>
                </div>
                <div
                    style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">
                    Delete Selected Students?</div>
                <div id="bulkDeleteSubtitle"
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                    Selected students will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('bulkDeleteModal')" class="btn-secondary">CANCEL</button>
                <button id="confirmBulkDeleteBtn"
                    style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 14px rgba(239,68,68,.25)">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    DELETE SELECTED
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
    CREATE / EDIT MODAL
    ════════════════════════════════════════════ --}}
    <div id="studentModal" class="modal-overlay">
        <div class="modal-box" style="max-width:520px; overflow-y:auto; max-height:90vh;">
            <div class="modal-head">
                <div style="display:flex;align-items:center;gap:10px">
                    <div id="modalAvatarPreview"
                        style="width:32px;height:32px;border-radius:50%;background:#2C3E50;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;letter-spacing:.04em">
                        ?
                    </div>
                    <span id="studentModalTitle" class="modal-title">Add Student</span>
                </div>
                <button onclick="closeModal('studentModal')" class="modal-close">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="studentForm">
                @csrf
                <input type="hidden" id="modalStudentId">
                <input type="hidden" id="modalMode" value="create">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:0">

                    {{-- Name --}}
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input id="modalName" class="form-input" type="text" required placeholder="e.g. Jean Doe"
                            oninput="updateAvatarPreview(this.value)">
                    </div>

                    <div class="form-grid-2">
                        {{-- Student Code --}}
                        <div class="form-group">
                            <label class="form-label">Student Code <span class="req">*</span></label>
                            <input id="modalCode" class="form-input" type="text" required placeholder="STUD-202X-XXXX">
                        </div>
                        {{-- Major Selection --}}
                        <div class="form-group">
                            <label class="form-label">Major Selection <span class="req">*</span></label>
                            <select id="modalMajor" name="major_id" class="form-input" required>
                                <option value="">Select Major...</option>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}">{{ strtoupper($m->name) }} [{{ $m->id }}]</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        {{-- Class/Group Selection --}}
                        <div class="form-group">
                            <label class="form-label">Assign Group <span class="req">*</span></label>
                            <select id="modalGroup" name="group_id" class="form-input" required>
                                <option value="">Select Group...</option>
                                @foreach($classGroups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} (Year {{ $g->year_level }}) [{{ $g->id }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Status --}}
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Status</label>
                            <select id="modalStatus" class="form-input" name="status">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="graduated">Graduated</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2" style="margin-bottom:0;margin-top:12px;">
                        {{-- Email --}}
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Institutional Email</label>
                            <input id="modalEmail" class="form-input" type="email" placeholder="student@university.edu">
                        </div>
                        {{-- Phone --}}
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Phone Number</label>
                            <input id="modalPhone" class="form-input" type="text" placeholder="+855 12 345 678">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal('studentModal')" class="btn-secondary">CANCEL</button>
                    <button type="submit" id="modalSubmitBtn" class="btn-primary">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span id="modalSubmitLabel">ADD NEW STUDENT</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
    VIEW PROFILE MODAL
    ════════════════════════════════════════════ --}}
    {{-- ════ STUDENT RECORD MODAL ════ --}}
    <div id="profileModal" class="modal-overlay" style="z-index: 1100;">
        <div class="modal-box"
            style="max-width:480px; border-radius:28px; overflow-y:auto; max-height:90vh; border:none; padding:0">
            <div class="modal-body" style="padding:0; position:relative">
                {{-- Profile Header --}}
                <div style="background-color: #22C55E; padding:40px 30px 60px; color:white; position:relative">
                    <button onclick="closeModal('profileModal')"
                        style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.15); border:none; width:32px; height:32px; border-radius:50%; color:white; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div style="display:flex; align-items:center; gap:22px">
                        <div id="smInitials"
                            style="width:72px; height:72px; border-radius:24px; background:rgba(255,255,255,0.25); border:2px solid rgba(255,255,255,0.4); display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:800; text-shadow:0 2px 10px rgba(0,0,0,0.1)">
                            -</div>
                        <div>
                            <div id="smName"
                                style="font-size:20px; font-weight:800; letter-spacing:-0.01em; margin-bottom:4px">Loading
                                Name...</div>
                            <div id="smCode"
                                style="font-family:var(--font-mono); font-size:11px; font-weight:700; color:rgba(255,255,255,0.8); background:rgba(0,0,0,0.15); padding:3px 12px; border-radius:20px; display:inline-block">
                                ID - ???</div>
                        </div>
                    </div>
                </div>

                {{-- Info Cards (Floating) --}}
                <div style="margin-top:-10px; padding:0 24px 30px">
                    <div
                        style="background:var(--surface2); border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow-xl); padding:24px">
                        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:20px; align-items:center">
                            <div>
                                <div style="display:inline-block; font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--green); background:color-mix(in srgb, var(--green) 7%, transparent); padding:3px 12px; border-radius:20px; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:12px"
                                    id="smStatusBadge">ACTIVE STUDENT</div>
                                <div style="display:grid; grid-template-columns: 1fr; gap:15px">
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            DEPARTMENT</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--text2)" id="smDept">
                                            Technology</div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            MAJOR</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--accent)" id="smMajor">
                                            Computer Science</div>
                                    </div>
                                </div>
                                <div style="margin-top:15px">
                                    <div
                                        style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                        YEAR LEVEL</div>
                                    <div style="font-size:14px; font-weight:800; color:var(--text2)" id="smYear">1st Year
                                    </div>
                                </div>
                                <div style="margin-top:15px; display:grid; grid-template-columns: 1fr 1fr; gap:15px">
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            EMAIL</div>
                                        <div style="font-size:11px; font-weight:700; color:var(--text2); overflow:hidden; text-overflow:ellipsis"
                                            id="smEmail">-</div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            PHONE</div>
                                        <div style="font-size:11px; font-weight:700; color:var(--text2)" id="smPhone">-
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                style="background:var(--surface3); border-radius:16px; padding:15px; text-align:center; border:1px solid var(--border)">
                                <div
                                    style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:5px">
                                    ATTENDANCE</div>
                                <div style="font-family:var(--font-display); font-size:24px; font-weight:800; color:var(--accent)"
                                    id="smRate">0%</div>
                                <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted2); margin-top:3px"
                                    id="smJoinedDate">JOINED AT -</div>
                            </div>
                        </div>

                        <div style="height:1px; background:var(--border); margin:20px 0; opacity:0.5"></div>

                        {{-- Recent Attendance --}}
                        <div>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px">
                                <span
                                    style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--text2); letter-spacing:0.05em">RECENT
                                    ATTENDANCE</span>
                                <span style="font-size:9px; color:var(--accent); font-weight:700">LATEST 10</span>
                            </div>
                            <div id="smHistory" style="display:flex; flex-direction:column; gap:10px">
                                {{-- Rows injected --}}
                            </div>
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div style="display:flex; gap:10px; margin-top:10px">
                        <button onclick="closeModal('profileModal')" class="btn-secondary"
                            style="height:44px; flex:1; font-weight:700">CLOSE</button>
                        <button id="profileEditBtn" class="btn-primary"
                            style="height:44px; flex:1; font-weight:700; gap:8px">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            EDIT RECORD
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
    BULK IMPORT MODAL
    ════════════════════════════════════════════ --}}
    <div id="importModal" class="modal-overlay">
        <div class="modal-box" style="max-width:400px">
            <div class="modal-head">
                <span class="modal-title">Bulk Student Import</span>
                <button onclick="closeModal('importModal')" class="modal-close">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div
                    style="background:color-mix(in srgb, var(--accent) 7%, transparent);border:1px dashed color-mix(in srgb, var(--accent) 27%, transparent);border-radius:12px;padding:32px;text-align:center;">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        style="color:var(--accent);margin-bottom:16px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div
                        style="font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px">
                        Upload CSV File</div>
                    <div
                        style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-bottom:20px;letter-spacing:.05em">
                        format: name,student_code,email,phone,group_id,major_id,status</div>
                    <input type="file" id="importFileInput" accept=".csv,.txt" style="display:none"
                        onchange="handleFileSelected(this)">
                    <button type="button" onclick="document.getElementById('importFileInput').click()" class="btn-primary"
                        style="width:100%">SELECT FILE</button>
                </div>
                <div id="importStatus"
                    style="display:none;margin-top:16px;font-family:var(--font-mono);font-size:10px;color:var(--accent);text-align:center;font-weight:700">
                    PROCESSING...</div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
    PAGE CONTENT
    ════════════════════════════════════════════ --}}

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>MANAGEMENT</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">STUDENTS</span>
            </div>
            <h1 class="page-title">Enrollment Registry</h1>
            <p class="page-subtitle">STUDENT LIFECYCLE & ACADEMIC RECORDS</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <button onclick="toggleView('table')" id="viewTableBtn" class="btn-secondary" style="gap:6px;padding:8px 14px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                </svg>
                LIST
            </button>
            <button onclick="toggleView('grid')" id="viewGridBtn" class="btn-secondary" style="gap:6px;padding:8px 14px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                GRID
            </button>
            <div style="width:1px;height:24px;background:var(--border);margin:0 5px"></div>

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdmin())
                <button onclick="window.open('{{ route('admin.export.students') }}', '_blank')" class="btn-secondary"
                    style="gap:7px; background:var(--surface3); border:1px solid var(--border)">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    EXPORT ALL
                </button>
                <button onclick="openModal('importModal')" class="btn-secondary" style="gap:7px;border-color:var(--border)">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    IMPORT BULK
                </button>
            @endif
            @if(Auth::user()->isSuperAdmin())
                <button id="bulkDeleteStudentsBtn" onclick="bulkDeleteStudents()" class="btn-secondary" disabled
                    style="gap:7px;border-color:rgba(239,68,68,.25);color:var(--red);opacity:.45;cursor:not-allowed">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3" />
                    </svg>
                    DELETE SELECTED (<span id="selectedStudentsCount">0</span>)
                </button>
            @endif
            <button onclick="openCreateModal()" class="btn-primary" style="gap:7px">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                ADD NEW STUDENT
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-label">TOTAL STUDENTS</div>
            <div class="stat-value">{{ $students->count() }}</div>
            <span class="stat-pill pill-up">↑ Enrolled</span>
        </div>
        <div class="stat-card green">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">ACTIVE PROFILES</div>
            <div class="stat-value">{{ $students->count() }}</div>
            <span class="stat-pill pill-up">Verified</span>
        </div>
        <div class="stat-card amber">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">AVG ATTENDANCE</div>
            <div class="stat-value">88.4<span style="font-size:12px;opacity:.5">%</span></div>
            <span class="stat-pill pill-amber">Standard</span>
        </div>
        <div class="stat-card red">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">AT RISK</div>
            <div class="stat-value">0</div>
            <span class="stat-pill pill-down">Secure</span>
        </div>
    </div>

    {{-- Toolbar + Table/Grid --}}
    <div class="panel">

        {{-- Toolbar --}}
        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:7px">
                <div
                    style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent);animation:blink 2s infinite">
                </div>
                <span
                    style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2)">ENROLLMENT
                    CATALOG</span>
            </div>

            <div class="search-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
                <input id="searchInput" class="search-input" type="text" placeholder="Search name, code, major…"
                    onkeyup="filterStudents(event)" value="{{ request('search') }}">
            </div>

            <select class="filter-select" id="majorFilter" onchange="filterStudents()">
                <option value="">ALL MAJORS</option>
                @foreach($majors as $m)
                    <option value="{{ $m->id }}" {{ request('major') == $m->id ? 'selected' : '' }}>{{ strtoupper($m->name) }}
                    </option>
                @endforeach
            </select>


            <div class="toolbar-count"><span id="rowCount">{{ $students->count() }}</span> STUDENTS</div>
        </div>

        {{-- ── TABLE VIEW ── --}}
        <div id="tableView">
            <div class="table-responsive">
                <table class="att-table" id="studentTable">
                    <thead>
                        <tr>
                            @if(Auth::user()->isSuperAdmin())
                                <th style="width:42px">
                                    <input type="checkbox" id="selectAllStudents" onchange="toggleAllStudents(this.checked)">
                                </th>
                            @endif
                            <th>STUDENT IDENTITY</th>
                            <th>STUDENT CODE</th>
                            <th>MAJOR / COURSE</th>
                            <th>JOINED</th>
                            <th>STATUS</th>
                            <th style="text-align:right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($students as $student)
                            @php
                                $avatarColors = [
                                    ['#2563EB', '#38BDF8'],
                                    ['#22C55E', '#86EFAC'],
                                    ['#8B5CF6', '#C4B5FD'],
                                    ['#F59E0B', '#FBBF24'],
                                    ['#EF4444', '#F87171'],
                                ];
                                $col = $avatarColors[$loop->index % count($avatarColors)];
                                $init = strtoupper(substr($student->user->name, 0, 2));
                                $displayMajor = $student->major ?? $student->group?->major;
                                $displayMajorId = $student->major_id ?? $student->group?->major_id;
                                $displayMajorName = $displayMajor?->name ?? 'N/A';
                                $className = $student->classRoom->subject->name ?? 'Unassigned';
                            @endphp
                            <tr data-id="{{ $student->id }}" data-name="{{ strtolower($student->user->name) }}"
                                data-email="{{ strtolower($student->user->email) }}"
                                data-phone="{{ $student->user->phone ?? '—' }}"
                                data-code="{{ strtolower($student->student_code) }}" data-major-id="{{ $displayMajorId }}"
                                data-group-id="{{ $student->group_id }}" data-status="{{ $student->status }}"
                                data-rate="{{ 85 + ($loop->index % 15) }}"
                                data-joined="{{ $student->created_at ? $student->created_at->format('M Y') : 'SEP 2025' }}"
                                data-subject="{{ strtolower($className) }}"
                                data-room="{{ strtolower($student->classRoom->room_number ?? 'N/A') }}" class="fade-up">

                                {{-- Student --}}
                                @if(Auth::user()->isSuperAdmin())
                                    <td>
                                        <input type="checkbox" class="student-select" value="{{ $student->id }}"
                                            onchange="updateStudentSelection()" onclick="event.stopPropagation()">
                                    </td>
                                @endif
                                {{-- Student --}}
                                <td>
                                    <div class="subject-cell">
                                        <div class="subject-avatar"
                                            style="background:{{ $col[0] }}22;color:{{ $col[0] }};border:1px solid {{ $col[0] }}33;font-size:10px;width:36px;height:36px;border-radius:50%">
                                            {{ $init }}
                                        </div>
                                        <div>
                                            <div class="subject-name">{{ $student->user->name }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Code --}}
                                <td>
                                    <span
                                        style="font-family:var(--font-mono);font-size:10px;color:var(--accent);letter-spacing:.05em">
                                        {{ $student->student_code }}
                                    </span>
                                </td>

                                {{-- Major & Group --}}
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px">
                                        <div style="font-size:12px;color:var(--text2)">{{ $displayMajorName }}
                                        </div>
                                    </div>
                                    <div
                                        style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px;letter-spacing:.05em">
                                        {{ strtoupper($student->group->name ?? 'NO GROUP') }}
                                        @if($student->group)
                                            •
                                            {{ $student->group->year_level }}{{ in_array($student->group->year_level % 10, [1, 2, 3]) && !in_array($student->group->year_level % 100, [11, 12, 13]) ? ['st', 'nd', 'rd'][$student->group->year_level % 10 - 1] : 'th' }}
                                            YEAR
                                        @endif
                                    </div>
                                </td>

                                {{-- Joined --}}
                                <td>
                                    <span
                                        style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.05em">
                                        {{ $student->created_at ? $student->created_at->format('M Y') : 'SEP 2025' }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($student->status === 'blacklisted')
                                        <span class="status-tag" style="background: rgba(239, 68, 68, 0.12); color: var(--red); border: 1px solid rgba(239, 68, 68, 0.25);">
                                            <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--red); display: inline-block; margin-right: 5px;"></span>
                                            BLACKLISTED
                                        </span>
                                    @else
                                        <span class="status-tag tag-active">ACTIVE</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td style="text-align:right">
                                    <button class="action-btn btn-view" title="View record"
                                        onclick="openProfile(this.closest('tr'))">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn btn-edit" title="Edit student"
                                        onclick="openEditModal(this.closest('tr'))">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @if(Auth::user()->isSuperAdmin())
                                        <button class="action-btn btn-del" title="Remove student"
                                            onclick="openDeleteModal({{ $student->id }}, '{{ addslashes($student->user->name) }}')">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isSuperAdmin() ? 7 : 6 }}">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="empty-title">No students found</div>
                                        <div class="empty-desc">Begin enrolling students to populate the registry</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── GRID VIEW ── --}}
        <div id="gridView" style="display:none;padding:18px">
            <div id="gridBody" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">
                @foreach($students as $student)
                    @php
                        $avatarColors = [
                            ['#2563EB', '#38BDF8'],
                            ['#22C55E', '#86EFAC'],
                            ['#8B5CF6', '#C4B5FD'],
                        ];
                        $col2 = $avatarColors[$loop->index % count($avatarColors)];
                        $init2 = strtoupper(substr($student->user->name, 0, 2));
                        $displayMajor2 = $student->major ?? $student->group?->major;
                        $displayMajorId2 = $student->major_id ?? $student->group?->major_id;
                        $majorDisplay = $displayMajor2?->name ?? 'N/A';
                        $rate2 = 85 + ($loop->index % 15);
                    @endphp
                    <div class="instructor-card fade-up" data-id="{{ $student->id }}"
                        data-name="{{ strtolower($student->user->name) }}" data-email="{{ strtolower($student->user->email) }}"
                        data-phone="{{ $student->user->phone ?? '—' }}" data-code="{{ strtolower($student->student_code) }}"
                        data-major-id="{{ $displayMajorId2 }}" data-group-id="{{ $student->group_id }}"
                        data-status="{{ $student->status }}" data-rate="{{ $rate2 }}"
                        data-joined="{{ $student->created_at ? $student->created_at->format('M Y') : 'SEP 2025' }}"
                        data-subject="{{ strtolower($className2 ?? 'Unassigned') }}"
                        data-room="{{ strtolower($student->classRoom->room_number ?? 'N/A') }}"
                        style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px 18px;display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;transition:all .2s;cursor:pointer;position:relative;overflow:hidden"
                        onmouseenter="this.style.borderColor='var(--border2)';this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-md)'"
                        onmouseleave="this.style.borderColor='var(--border)';this.style.transform='';this.style.boxShadow=''">

                        @if(Auth::user()->isSuperAdmin())
                            <label style="position:absolute;top:10px;right:10px;z-index:2;background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:5px;cursor:pointer"
                                onclick="event.stopPropagation()">
                                <input type="checkbox" class="student-select" value="{{ $student->id }}"
                                    onchange="updateStudentSelection()">
                            </label>
                        @endif

                        <div
                            style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,{{ $col2[0] }},{{ $col2[1] }})">
                        </div>

                        <div
                            style="width:52px;height:52px;border-radius:50%;background:{{ $col2[0] }}22;border:2px solid {{ $col2[0] }}44;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:{{ $col2[0] }};">
                            {{ $init2 }}
                        </div>

                        <div
                            style="font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--text);line-height:1.3">
                            {{ $student->user->name }}
                        </div>

                        <span style="font-family:var(--font-mono);font-size:9px;color:var(--accent);letter-spacing:.1em">
                            {{ $student->student_code }}
                        </span>

                        <div
                            style="display:flex;align-items:center;gap:5px;font-size:9px;color:var(--muted);letter-spacing:.05em;max-width:90%">
                            <span
                                style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ strtoupper($majorDisplay) }}</span>
                        </div>

                        <div style="display:flex;align-items:center;gap:14px;margin-top:4px">
                            <div>
                                <div
                                    style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green);line-height:1">
                                    {{ $rate2 }}%
                                </div>
                                <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em">
                                    ATTENDANCE</div>
                            </div>
                        </div>

                        <div style="display:flex;gap:6px;margin-top:4px">
                            <button class="action-btn btn-view" style="width:28px;height:28px" title="View"
                                onclick="openProfile(this.closest('.instructor-card'))">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" />
                                </svg>
                            </button>
                            <button class="action-btn btn-edit" style="width:28px;height:28px" title="Edit"
                                onclick="openEditModal(this.closest('.instructor-card'))">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" stroke-width="2" />
                                </svg>
                            </button>
                            @if(Auth::user()->isSuperAdmin())
                                <button class="action-btn btn-del" style="width:28px;height:28px" title="Remove"
                                    onclick="openDeleteModal({{ $student->id }}, '{{ addslashes($student->user->name) }}')">
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pagination --}}
        @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div
                style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em">
                    SHOWING {{ $students->firstItem() }}–{{ $students->lastItem() }} OF {{ $students->total() }}
                </span>
                {{ $students->links('vendor.pagination.academy') }}
            </div>
        @endif
    </div>

    <script>
        // ── Modal helpers ──────────────────────────────
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });

        // ── Toast ──────────────────────────────────────
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const ic = document.getElementById('toastIcon');
            t.className = `toast show toast-${type}`;
            ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
            document.getElementById('toastMsg').textContent = msg;
            clearTimeout(t._t);
            t._t = setTimeout(() => t.classList.remove('show'), 3200);
        }

        // ── Avatar preview ─────────────────────────────
        function updateAvatarPreview(val) {
            const words = val.trim().split(/\s+/);
            const init = words.length >= 2
                ? (words[0][0] + words[words.length - 1][0]).toUpperCase()
                : (val.slice(0, 2).toUpperCase() || '?');
            document.getElementById('modalAvatarPreview').textContent = init;
        }

        // ── View toggle ────────────────────────────────
        function toggleView(mode) {
            const isTable = mode === 'table';
            document.getElementById('tableView').style.display = isTable ? '' : 'none';
            document.getElementById('gridView').style.display = isTable ? 'none' : '';
            document.getElementById('viewTableBtn').style.opacity = isTable ? '1' : '.5';
            document.getElementById('viewGridBtn').style.opacity = isTable ? '.5' : '1';
        }
        toggleView('table');

        // ── Filter ─────────────────────────────────────
        // ── Filter (Server-side) ───────────────────────
        let filterTimeout = null;
        function filterStudents(e) {
            if (e && e.type === 'keyup' && e.key !== 'Enter') {
                // Debounce typing to prevent too many reloads
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => filterStudents(), 800);
                return;
            }

            const q = document.getElementById('searchInput').value;
            const major = document.getElementById('majorFilter').value;

            const params = new URLSearchParams(window.location.search);
            if (q) params.set('search', q); else params.delete('search');
            if (major) params.set('major', major); else params.delete('major');
            params.set('page', 1); // Reset to first page on new search

            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }


        // ── Delete ─────────────────────────────────────
        let pendingDeleteId = null;
        function openDeleteModal(id, name) {
            pendingDeleteId = id;
            document.getElementById('deleteSubtitle').innerHTML =
                `<strong style="color:var(--text2)">${name}</strong> will be permanently expelled from the enrollment registry.`;
            openModal('deleteModal');
        }
        document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
            if (!pendingDeleteId) return;
            try {
                const res = await fetch(`/api/admin/students/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    document.querySelectorAll(`tr[data-id="${pendingDeleteId}"], .instructor-card[data-id="${pendingDeleteId}"]`).forEach(el => el.remove());
                    showToast('Student record deleted.', 'success');
                    closeModal('deleteModal');
                }
            } catch (e) { showToast('Error deleting record.', 'error'); }
            pendingDeleteId = null;
        });

        function selectedStudentIds() {
            return [...document.querySelectorAll('.student-select:checked')]
                .map(input => input.value)
                .filter((value, index, values) => values.indexOf(value) === index);
        }

        function updateStudentSelection() {
            const ids = selectedStudentIds();
            const btn = document.getElementById('bulkDeleteStudentsBtn');
            const count = document.getElementById('selectedStudentsCount');
            const selectAll = document.getElementById('selectAllStudents');

            if (count) count.textContent = ids.length;
            if (btn) {
                btn.disabled = ids.length === 0;
                btn.style.opacity = ids.length ? '1' : '.45';
                btn.style.cursor = ids.length ? 'pointer' : 'not-allowed';
            }

            if (selectAll) {
                const tableChecks = [...document.querySelectorAll('#studentTable .student-select')];
                const checked = tableChecks.filter(input => input.checked);
                selectAll.checked = tableChecks.length > 0 && checked.length === tableChecks.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < tableChecks.length;
            }
        }

        function toggleAllStudents(checked) {
            document.querySelectorAll('#studentTable .student-select').forEach(input => {
                input.checked = checked;
            });
            updateStudentSelection();
        }

        let pendingBulkStudentIds = [];

        function bulkDeleteStudents() {
            const ids = selectedStudentIds();
            if (!ids.length) return;

            pendingBulkStudentIds = ids;
            document.getElementById('bulkDeleteSubtitle').innerHTML =
                `<strong style="color:var(--text2)">${ids.length}</strong> selected student(s) will be permanently removed.<br>This action cannot be undone.`;
            openModal('bulkDeleteModal');
        }

        document.getElementById('confirmBulkDeleteBtn').addEventListener('click', async () => {
            const ids = pendingBulkStudentIds;
            if (!ids.length) return;

            const btn = document.getElementById('bulkDeleteStudentsBtn');
            const modalBtn = document.getElementById('confirmBulkDeleteBtn');
            const oldText = btn.innerHTML;
            const oldModalText = modalBtn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'DELETING...';
            modalBtn.disabled = true;
            modalBtn.innerHTML = 'DELETING...';

            try {
                const res = await fetch('/api/admin/students/bulk-delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_ids: ids })
                });
                const data = await res.json().catch(() => ({ success: res.ok }));

                if (!res.ok || !data.success) {
                    showToast(data.error || data.message || 'Failed to delete selected students.', 'error');
                    return;
                }

                ids.forEach(id => {
                    document.querySelectorAll(`tr[data-id="${id}"], .instructor-card[data-id="${id}"]`).forEach(el => el.remove());
                });
                showToast(`${data.deleted_count || ids.length} student(s) deleted.`, 'success');
                closeModal('bulkDeleteModal');
                pendingBulkStudentIds = [];
                updateStudentSelection();
            } catch (e) {
                showToast('Network error deleting selected students.', 'error');
            } finally {
                btn.innerHTML = oldText;
                modalBtn.innerHTML = oldModalText;
                modalBtn.disabled = false;
                updateStudentSelection();
            }
        });

        // ── View Profile ───────────────────────────────
        async function openProfile(el) {
            const hist = document.getElementById('smHistory');
            hist.innerHTML = '<div style="text-align:center; padding:30px; font-size:11px; color:var(--muted)">RETRIEVING PROFILE...</div>';

            // Clear previous view
            document.getElementById('smName').textContent = 'Loading...';
            document.getElementById('smCode').textContent = '--';
            document.getElementById('smInitials').textContent = '-';

            openModal('profileModal');

            const studentId = el.dataset.id;
            try {
                const res = await fetch(`/api/admin/students/${studentId}/attendance`);
                const data = await res.json();
                const s = data.student;

                // Populate Header
                document.getElementById('smName').textContent = s.name.toUpperCase();
                document.getElementById('smCode').textContent = s.student_code.toUpperCase();
                document.getElementById('smInitials').textContent = s.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

                // Populate Analytics
                document.getElementById('smMajor').textContent = s.major || 'N/A';
                document.getElementById('smDept').textContent = s.department || 'N/A';
                const yr = s.year_level || 1;
                const suffix = (['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'][yr % 10] || 'th');
                document.getElementById('smYear').textContent = yr + ((yr % 100 >= 11 && yr % 100 <= 13) ? 'th' : suffix) + ' YEAR';
                document.getElementById('smStatusBadge').textContent = (s.status || 'ACTIVE').toUpperCase() + ' STUDENT';
                document.getElementById('smRate').textContent = s.attendance_rate + '%';
                document.getElementById('smJoinedDate').textContent = 'JOINED AT ' + s.joined_at;
                document.getElementById('smEmail').textContent = s.email || '-';
                document.getElementById('smPhone').textContent = s.phone || '-';

                document.getElementById('profileEditBtn').onclick = () => {
                    closeModal('profileModal');
                    openEditModal(el);
                };

                // Populate History
                if (data.history.length === 0) {
                    hist.innerHTML = '<div style="text-align:center; padding:20px; font-size:10px; color:var(--muted); font-family:var(--font-mono)">NO RECENT RECORDS FOUND</div>';
                    return;
                }

                hist.innerHTML = data.history.map(row => {
                    const isPresent = row.status === 'PRESENT' || row.status === 'LATE';
                    const color = isPresent ? 'var(--green)' : 'var(--red)';

                    return `
                                        <div style="display:flex; align-items:center; justify-content:space-between; background:var(--surface3); padding:10px 14px; border-radius:12px; border:1px solid var(--border)">
                                            <div style="display:flex; align-items:center; gap:10px">
                                                <div style="width:8px; height:8px; border-radius:50%; background:${color}"></div>
                                                <div style="text-align:left">
                                                    <div style="font-size:11px; font-weight:700; color:var(--text2)">${row.subject}</div>
                                                    <div style="font-size:9px; color:var(--muted)">${row.date}</div>
                                                </div>
                                            </div>
                                            <div style="text-align:right">
                                                <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:${color}">${row.status}</div>
                                                <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted2)">${row.time}</div>
                                            </div>
                                        </div>
                                    `;
                }).join('');

            } catch (e) {
                hist.innerHTML = '<div style="text-align:center; padding:30px; color:var(--red)">Failed to load student record.</div>';
            }
        }



        // ── Create/Edit ────────────────────────────────
        function openCreateModal() {
            document.getElementById('studentModalTitle').textContent = 'Add New Student';
            document.getElementById('modalSubmitLabel').textContent = 'ADD NEW STUDENT';
            document.getElementById('modalMode').value = 'create';
            document.getElementById('studentForm').reset();
            document.getElementById('modalAvatarPreview').textContent = '?';
            openModal('studentModal');
        }

        function openEditModal(row) {
            const name = row.dataset.name || row.querySelector('.subject-name').textContent;
            document.getElementById('studentModalTitle').textContent = 'Modify Record';
            document.getElementById('modalSubmitLabel').textContent = 'APPLY CHANGES';
            document.getElementById('modalMode').value = 'edit';
            document.getElementById('modalStudentId').value = row.dataset.id;
            document.getElementById('modalName').value = name;
            document.getElementById('modalEmail').value = row.dataset.email || '';
            document.getElementById('modalPhone').value = row.dataset.phone && row.dataset.phone !== '—' ? row.dataset.phone : '';
            document.getElementById('modalCode').value = row.dataset.code || '';
            document.getElementById('modalMajor').value = row.dataset.majorId || '';
            document.getElementById('modalGroup').value = row.dataset.groupId || '';
            document.getElementById('modalStatus').value = row.dataset.status || 'active';
            updateAvatarPreview(name);
            openModal('studentModal');
        }

        document.getElementById('studentForm').addEventListener('submit', async e => {
            e.preventDefault();
            const mode = document.getElementById('modalMode').value;
            const id = document.getElementById('modalStudentId').value;
            const btn = document.getElementById('modalSubmitBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = 'SAVING...';

            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData.entries());
            payload.name = document.getElementById('modalName').value;
            payload.email = document.getElementById('modalEmail').value;
            payload.phone = document.getElementById('modalPhone').value;
            payload.student_code = document.getElementById('modalCode').value;

            try {
                const url = mode === 'create' ? '/api/admin/students' : `/api/admin/students/${id}`;
                const res = await fetch(url, {
                    method: mode === 'create' ? 'POST' : 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast(mode === 'create' ? 'Student enrolled successfully.' : 'Student record updated.', 'success');
                    closeModal('studentModal');
                    setTimeout(() => location.reload(), 800);
                } else {
                    let errMsg = data.error || data.message || 'Operation failed.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join(' ');
                    }
                    showToast(errMsg, 'error');
                }
            } catch (err) { showToast('Network Error', 'error'); }
            btn.innerHTML = ogHtml;
        });

        async function handleFileSelected(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const status = document.getElementById('importStatus');
            status.style.display = 'block';
            status.textContent = `IMPORTING: ${file.name}...`;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch('/api/admin/students/import', {
                    method: 'POST',
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    const imported = data.imported_count ?? 0;
                    const skipped = data.skipped_count ?? 0;
                    const message = skipped > 0
                        ? `Imported ${imported} student(s). Skipped ${skipped} row(s).`
                        : `Imported ${imported} student(s).`;
                    status.textContent = message;
                    showToast(message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const skipped = Array.isArray(data.skipped) && data.skipped.length
                        ? ` ${data.skipped.slice(0, 3).map(row => `Row ${row.row}: ${row.reason}`).join(' ')}`
                        : '';
                    showToast((data.error || 'Import failed.') + skipped, 'error');
                    status.style.display = 'none';
                }
            } catch (e) {
                showToast('Network error during import.', 'error');
                status.style.display = 'none';
            }
        }
    </script>
@endsection
