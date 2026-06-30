@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
@endpush

@section('content')
    {{-- ════════════════════════════════════════════
     TOAST
════════════════════════════════════════════ --}}
    <div id="toast" class="toast">
        <div id="toastIcon" class="toast-icon">✓</div>
        <span id="toastMsg">{{ __('admin_instructors.message') }}</span>
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
                    {{ __('admin_instructors.remove_instructor_question') }}</div>
                <div id="deleteSubtitle"
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                    {{ __('admin_instructors.delete_warning') }}
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('deleteModal')" class="btn-secondary">{{ __('admin_instructors.cancel') }}</button>
                <button id="confirmDeleteBtn"
                    style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 14px rgba(239,68,68,.25)">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('admin_instructors.confirm_remove') }}
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
                    Delete Selected Instructors?</div>
                <div id="bulkDeleteSubtitle"
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.7">
                    Selected instructors will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('bulkDeleteModal')" class="btn-secondary">{{ __('admin_instructors.cancel') }}</button>
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
    <div id="teacherRegisterModal" class="modal-overlay">
        <div class="modal-box" style="max-width:520px">
            <div class="modal-head">
                <span class="modal-title">Teacher Self Registration</span>
                <button onclick="closeModal('teacherRegisterModal')" class="modal-close">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="display:grid;gap:18px">
                <div style="display:grid;grid-template-columns:170px 1fr;gap:16px;align-items:center">
                    <div style="border:1px solid var(--border);border-radius:14px;background:#fff;padding:10px;display:grid;place-items:center">
                        <img alt="Teacher registration QR" style="width:148px;height:148px"
                            src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($teacherRegisterUrl) }}">
                    </div>
                    <div>
                        <div style="font-family:var(--font-display);font-weight:800;color:var(--text);font-size:17px;margin-bottom:6px">Scan from teacher phone</div>
                        <div style="font-size:12px;line-height:1.6;color:var(--muted)">This signed link opens a teacher registration form. Submitted teachers stay pending and are added to the instructor system only after approval.</div>
                        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                            <a href="{{ route('admin.teacher-accounts') }}" class="btn-secondary" style="text-decoration:none;height:36px;display:inline-flex;align-items:center">Review Pending ({{ $pendingTeacherApprovals }})</a>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Registration Link</label>
                    <textarea id="teacherRegisterLink" class="form-input" readonly
                        style="height:86px;resize:none;font-family:var(--font-mono);font-size:11px;line-height:1.5">{{ $teacherRegisterUrl }}</textarea>
                    <div style="font-size:10px;color:var(--muted);margin-top:7px">The link expires in 7 days. Refresh this page to generate a new signed link.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="copyTeacherRegisterLink()" class="btn-secondary">COPY LINK</button>
                <button type="button" onclick="window.open(document.getElementById('teacherRegisterLink').value, '_blank')" class="btn-primary">OPEN FORM</button>
            </div>
        </div>
    </div>

    <div id="instructorModal" class="modal-overlay">
        <div class="modal-box" style="max-width:520px">
            <div class="modal-head">
                <div style="display:flex;align-items:center;gap:10px">
                    <div id="modalAvatarPreview"
                        style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--violet));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;letter-spacing:.04em">
                        ?
                    </div>
                    <span id="instructorModalTitle" class="modal-title">{{ __('admin_instructors.add_instructor') }}</span>
                </div>
                <button onclick="closeModal('instructorModal')" class="modal-close">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="instructorForm">
                @csrf
                <input type="hidden" id="modalInstructorId">
                <input type="hidden" id="modalMode" value="create">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:0">

                    {{-- Name --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_instructors.full_name') }} <span class="req">*</span></label>
                        <input id="modalName" class="form-input" type="text" required placeholder="{{ __('admin_instructors.name_placeholder') }}"
                            oninput="updateAvatarPreview(this.value)">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_instructors.department') }} <span class="req">*</span></label>
                            <select id="modalDept" name="department_id" class="form-input">
                                <option value="">{{ __('admin_instructors.select_department') }}</option>
                                @foreach ($depts as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Status --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_instructors.status') }}</label>
                            <select id="modalStatus" class="form-input">
                                <option value="active">{{ __('admin_instructors.active') }}</option>
                                <option value="on_leave">{{ __('admin_instructors.on_leave') }}</option>
                                <option value="inactive">{{ __('admin_instructors.inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        {{-- Email --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_instructors.email') }}</label>
                            <input id="modalEmail" class="form-input" type="email" placeholder="{{ __('admin_instructors.email_placeholder') }}">
                        </div>
                        {{-- Phone --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_instructors.phone') }}</label>
                            <input id="modalPhone" class="form-input" type="text" placeholder="{{ __('admin_instructors.phone_placeholder') }}">
                        </div>
                    </div>

                    {{-- Specialization --}}
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">{{ __('admin_instructors.specialization') }}</label>
                        <input id="modalSpec" class="form-input" type="text"
                            placeholder="{{ __('admin_instructors.specialization_placeholder') }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal('instructorModal')" class="btn-secondary">{{ __('admin_instructors.cancel') }}</button>
                    <button type="submit" id="modalSubmitBtn" class="btn-primary">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span id="modalSubmitLabel">{{ __('admin_instructors.add_instructor_upper') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
     VIEW PROFILE MODAL
════════════════════════════════════════════ --}}
    <div id="profileModal" class="modal-overlay">
        <div class="modal-box" style="max-width:480px">
            <div class="modal-head">
                <span class="modal-title">{{ __('admin_instructors.instructor_profile') }}</span>
                <button onclick="closeModal('profileModal')" class="modal-close">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:0">
                {{-- Profile hero --}}
                <div
                    style="padding:28px 24px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:18px">
                    <div id="profileAvatar"
                        style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--violet));display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;box-shadow:0 0 20px rgba(37,99,235,.3);flex-shrink:0">
                        A
                    </div>
                    <div>
                        <div id="profileName"
                            style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--text)">—
                        </div>
                        <div id="profileDept"
                            style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.1em;margin-top:3px">
                            —</div>
                        <div style="margin-top:8px" id="profileStatusWrap">
                            <span id="profileStatus" class="status-tag tag-active">{{ __('admin_instructors.active_upper') }}</span>
                        </div>
                    </div>
                </div>
                {{-- Details grid --}}
                <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--muted);margin-bottom:5px">
                            {{ __('admin_instructors.email') }}</div>
                        <div id="profileEmail" style="font-size:12px;color:var(--text2)">—</div>
                    </div>
                    <div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--muted);margin-bottom:5px">
                            {{ __('admin_instructors.phone') }}</div>
                        <div id="profilePhone" style="font-size:12px;color:var(--text2)">—</div>
                    </div>
                    <div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--muted);margin-bottom:5px">
                            {{ __('admin_instructors.attendance_code') }}</div>
                        <div id="profileCode"
                            style="font-family:var(--font-mono);font-size:15px;font-weight:800;color:var(--accent)">—</div>
                    </div>
                    <div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--muted);margin-bottom:5px">
                            {{ __('admin_instructors.classes_assigned') }}</div>
                        <div id="profileClasses"
                            style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--accent)">—
                        </div>
                    </div>
                    <div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--muted);margin-bottom:5px">
                            {{ __('admin_instructors.specialization') }}</div>
                        <div id="profileSpec" style="font-size:12px;color:var(--text2)">—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('profileModal')" class="btn-secondary">{{ __('admin_instructors.close') }}</button>
                <button id="profileEditBtn" class="btn-primary">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('admin_instructors.edit_profile') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
     PAGE CONTENT
════════════════════════════════════════════ --}}

    @php
        $instructorRows = collect($instructors->items());
        $totalInstructors = method_exists($instructors, 'total') ? $instructors->total() : $instructors->count();
        $activeInstructors = $instructorRows->where('status', 'active')->count();
        $onLeaveInstructors = $instructorRows->where('status', 'on_leave')->count();
        $inactiveInstructors = $instructorRows->where('status', 'inactive')->count();
        $coveredClasses = $instructorRows->sum(fn($teacher) => $teacher->classes_count ?? 0);
        $deptStats = $instructorRows
            ->groupBy(fn($teacher) => $teacher->department->name ?? 'Unassigned')
            ->map->count()
            ->sortDesc()
            ->take(7);
        $deptLabels = $deptStats->keys()->values();
        $deptCounts = $deptStats->values();
    @endphp

    <style>
        .teacher-overview-shell {
            --teacher-page-bg: #f4f5f9;
            --teacher-surface: #fff;
            --teacher-surface-soft: #f9fafb;
            --teacher-surface-muted: #f3f4f6;
            --teacher-border: #e5e7eb;
            --teacher-border-soft: #f3f4f6;
            --teacher-text: #0d0f1c;
            --teacher-text-soft: #4b5563;
            --teacher-muted: #8b94a7;
            --teacher-muted-2: #9ca3af;
            --teacher-card-shadow: 0 2px 20px rgba(0, 0, 0, .06);
            --teacher-card-shadow-hover: 0 12px 40px rgba(0, 0, 0, .12);
            --teacher-hero-bg: #0d0f1c;
            font-family: "Outfit", var(--font-sans);
            background: var(--teacher-page-bg);
            color: var(--teacher-text);
            min-height: calc(100vh - var(--topbar-h));
            overflow: visible;
            padding: 28px;
        }

        [data-theme="dark"] .teacher-overview-shell {
            --teacher-page-bg: #0f172a;
            --teacher-surface: #111827;
            --teacher-surface-soft: #1e293b;
            --teacher-surface-muted: #243044;
            --teacher-border: #334155;
            --teacher-border-soft: #243044;
            --teacher-text: #f8fafc;
            --teacher-text-soft: #cbd5e1;
            --teacher-muted: #94a3b8;
            --teacher-muted-2: #64748b;
            --teacher-card-shadow: 0 2px 22px rgba(0, 0, 0, .24);
            --teacher-card-shadow-hover: 0 12px 42px rgba(0, 0, 0, .34);
            --teacher-hero-bg: #07080f;
        }

        .teacher-page-frame {
            max-width: 1480px;
            margin: 0 auto;
        }

        .teacher-content-stack {
            margin-top: 24px;
        }

        .teacher-content-stack>*+* {
            margin-top: 24px;
        }

        .teacher-hero {
            background: var(--teacher-hero-bg) !important;
            border-radius: 18px;
            padding: 24px 28px;
            box-shadow: 0 18px 45px rgba(13, 15, 28, .16);
        }

        .teacher-hero-main {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .teacher-hero-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .teacher-hero-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .teacher-live-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 12px;
        }

        .teacher-add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            border-radius: 12px;
            background: #00c9a7;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            color: #0d0f1c;
            box-shadow: 0 0 24px rgba(0, 201, 167, .18);
            transition: background .2s ease, color .2s ease;
            white-space: nowrap;
        }

        .teacher-add-button:hover {
            background: #00836e;
            color: #fff;
        }

        .teacher-add-button svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .teacher-topstat {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .teacher-stat-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .teacher-stat-grid .teacher-topstat {
            min-height: 58px;
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, .1);

            background: rgba(255, 255, 255, .06);

        }

        .teacher-stat-grid .teacher-topstat>div:first-child {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            /* color: rgba(255, 255, 255, .42); */
        }

        .teacher-stat-grid .teacher-topstat>div:last-child {
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
        }

        .teacher-card {
            position: relative;
            display: flex;
            min-height: 360px;
            flex-direction: column;
            background: var(--teacher-surface) !important;
            color: var(--teacher-text);
            box-shadow: var(--teacher-card-shadow) !important;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .teacher-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--teacher-card-shadow-hover) !important;
        }

        .avatar-ring {
            background: conic-gradient(#00c9a7, #3b82f6, #8b5cf6, #f5a623, #00c9a7);
            padding: 2.5px;
            border-radius: 999px;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 20px;
            padding: 3px 10px;
            letter-spacing: .02em;
        }

        .dot-online {
            background: #00c9a7;
            box-shadow: 0 0 0 3px rgba(0, 201, 167, .2);
        }

        .dot-busy {
            background: #f5a623;
            box-shadow: 0 0 0 3px rgba(245, 166, 35, .2);
        }

        .dot-offline {
            background: #9ca3af;
        }

        .pbar-track {
            height: 6px;
            background: var(--teacher-border);
            border-radius: 99px;
            overflow: hidden;
        }

        .pbar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 1.2s cubic-bezier(.16, 1, .3, 1);
        }

        .teacher-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
        }

        .teacher-card-body {
            padding: 0 20px 20px;
        }

        .teacher-card-banner {
            position: relative;
            height: 64px;
            flex-shrink: 0;
        }

        .teacher-card-banner-glow {
            position: absolute;
            inset: 0;
            opacity: .2;
        }

        .teacher-card-badges {
            position: absolute;
            right: 12px;
            top: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .teacher-card-avatar-row {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            margin: -28px 0 12px;
        }

        .teacher-card-avatar {
            display: flex;
            width: 56px;
            height: 56px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            flex-shrink: 0;
        }

        .teacher-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
            color: #f5a623;
            font-size: 13px;
        }

        .teacher-card-title {
            color: var(--teacher-text);
            font-size: 15px;
            font-weight: 900;
            line-height: 1.2;
        }

        .teacher-card-subtitle {
            margin: 4px 0 10px;
            color: var(--teacher-muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .teacher-card-code {
            margin-bottom: 12px;
            color: #00836e;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
        }

        .teacher-card-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }

        .teacher-card-metric {
            border-radius: 12px;
            background: var(--teacher-surface-soft);
            padding: 10px 8px;
            text-align: center;
        }

        .teacher-card-metric strong {
            display: block;
            color: var(--teacher-text);
            font-size: 16px;
            font-weight: 900;
            line-height: 1;
        }

        .teacher-card-metric span {
            display: block;
            margin-top: 5px;
            color: var(--teacher-muted-2);
            font-size: 10px;
            line-height: 1;
        }

        .teacher-progress-label {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
            color: var(--teacher-muted-2);
            font-size: 12px;
        }

        .teacher-progress-label strong {
            color: var(--teacher-text);
            font-weight: 800;
        }

        .teacher-skill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 14px 0 16px;
        }

        .teacher-skill-tag {
            border-radius: 6px;
            background: var(--teacher-surface-muted);
            color: var(--teacher-text-soft);
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }

        .teacher-card-actions {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-top: 1px solid var(--teacher-border-soft);
            padding-top: 14px;
        }

        .teacher-action-link {
            display: inline-flex;
            align-items: center;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            transition: background .2s ease, color .2s ease;
        }

        .teacher-grid-view {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .teacher-grid-view.is-hidden,
        .teacher-table-view.is-hidden {
            display: none;
        }

        .teacher-list-table {
            min-width: 980px;
        }

        .teacher-table-view {
            background: var(--teacher-surface) !important;
            box-shadow: var(--teacher-card-shadow) !important;
        }

        .teacher-list-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            color: var(--teacher-muted-2);
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 14px 20px;
            white-space: nowrap;
        }

        .teacher-list-table td {
            color: var(--teacher-text-soft);
            padding: 16px 20px;
            border-bottom: 1px solid var(--teacher-border-soft);
            vertical-align: middle;
        }

        .teacher-list-table thead tr {
            background: var(--teacher-surface-soft) !important;
            border-color: var(--teacher-border-soft) !important;
        }

        .teacher-list-table tr:hover {
            background: color-mix(in srgb, var(--teacher-surface-soft) 72%, transparent);
        }

        .chart-card {
            background: var(--teacher-surface);
            color: var(--teacher-text);
            border: 1px solid color-mix(in srgb, var(--teacher-border) 72%, transparent);
            border-radius: 16px;
            box-shadow: var(--teacher-card-shadow);
            padding: 20px;
        }

        .chart-card h3 {
            color: var(--teacher-text) !important;
        }

        .chart-card p,
        .chart-card .text-gray-400,
        .chart-card .text-gray-500 {
            color: var(--teacher-muted) !important;
        }

        .teacher-chart-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .filter-card {
            background: var(--teacher-surface);
            border: 1px solid color-mix(in srgb, var(--teacher-border) 72%, transparent);
            border-radius: 16px;
            box-shadow: var(--teacher-card-shadow);
            padding: 16px 20px;
        }

        .teacher-filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .teacher-search {
            position: relative;
            min-width: 280px;
            flex: 1 1 360px;
        }

        .teacher-search svg {
            position: absolute;
            left: 14px;
            top: 50%;
            width: 16px;
            height: 16px;
            transform: translateY(-50%);
            color: var(--teacher-muted-2);
            pointer-events: none;
        }

        .teacher-search-input,
        .teacher-filter-select,
        .teacher-export-button {
            height: 40px;
            border: 1px solid var(--teacher-border);
            border-radius: 12px;
            background: var(--teacher-surface);
            font-size: 14px;
            color: var(--teacher-text-soft);
            transition: border-color .2s ease, box-shadow .2s ease, color .2s ease, background .2s ease;
        }

        .teacher-search-input {
            width: 100%;
            padding: 0 36px 0 40px;
        }

        .teacher-search-input::placeholder {
            color: var(--teacher-muted-2);
        }

        .teacher-filter-select {
            min-width: 150px;
            padding: 0 34px 0 12px;
        }

        .teacher-search-input:focus,
        .teacher-filter-select:focus {
            border-color: #00c9a7;
            box-shadow: 0 0 0 3px rgba(0, 201, 167, .16);
            outline: none;
        }

        .teacher-view-toggle {
            display: flex;
            align-items: center;
            gap: 2px;
            height: 40px;
            border: 1px solid var(--teacher-border);
            border-radius: 12px;
            background: var(--teacher-surface-soft);
            padding: 3px;
        }

        .teacher-view-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: var(--teacher-muted-2);
            transition: background .2s ease, color .2s ease;
        }

        .teacher-view-button svg {
            width: 16px;
            height: 16px;
        }

        .teacher-view-button.is-active {
            background: var(--teacher-surface);
            color: var(--teacher-text);
            box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
        }

        .teacher-export-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 14px;
            font-weight: 800;
        }

        .teacher-export-button:hover {
            border-color: #00c9a7;
            color: #00836e;
        }

        .teacher-filter-count {
            margin-left: auto;
            white-space: nowrap;
            color: var(--teacher-muted) !important;
        }

        .teacher-filter-count span {
            color: var(--teacher-text) !important;
        }

        .teacher-overview-shell .subject-name,
        .teacher-overview-shell .text-\[\#0d0f1c\] {
            color: var(--teacher-text) !important;
        }

        .teacher-overview-shell .text-gray-400,
        .teacher-overview-shell .text-gray-500 {
            color: var(--teacher-muted) !important;
        }

        .animate-card {
            animation: slideUp .45s cubic-bezier(.16, 1, .3, 1) forwards;
            opacity: 0;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1279px) {
            .teacher-stat-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .teacher-chart-grid {
                grid-template-columns: 1fr;
            }

            .teacher-grid-view {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1024px) {
            .teacher-overview-shell {
                padding: 22px;
            }

            .teacher-filter-count {
                width: 100%;
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .teacher-overview-shell {
                overflow: visible;
                padding: 16px;
            }

            .teacher-hero {
                border-radius: 14px;
            }

            .teacher-stat-grid {
                grid-template-columns: 1fr;
            }

            .teacher-grid-view {
                grid-template-columns: 1fr;
            }

            .teacher-hero-meta {
                align-items: flex-start;
            }

            .chart-card {
                padding: 16px;
            }

            .filter-card {
                padding: 14px;
            }

            .teacher-search,
            .teacher-filter-select,
            .teacher-export-button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    <div class="teacher-overview-shell">
        <div class=" space-y-6">
            <div class=" p-6">
                <div class="teacher-hero-main">
                    <div class="teacher-hero-meta">
                        <div class="min-w-0">
                            <div class="mb-2 flex items-center gap-2">
                                <a href="{{ route('admin.students.overview') }}"
                                    class="text-xs /30 transition-colors hover:/60">{{ __('admin_instructors.dashboard') }}</a>
                                <svg class="h-3 w-3 /20" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                                <span class="text-xs font-bold text-[#00c9a7]">{{ __('admin_instructors.instructors') }}</span>
                            </div>
                            <h1 class="text-2xl font-black leading-tight tracking-tight sm:text-3xl">{{ __('admin_instructors.overview_title') }}</h1>
                            <p class="mt-1.5 text-sm ">{{ __('admin_instructors.overview_subtitle') }}</p>
                        </div>
                        <div class="teacher-hero-actions">
                            <div class="teacher-topstat teacher-live-pill">
                                <div class="dot-online h-2 w-2 rounded-full animate-pulse"></div>
                                <span class="text-xs ">{{ __('admin_instructors.active_now', ['count' => $activeInstructors]) }}</span>
                            </div>
                            <button onclick="openCreateModal()" class="teacher-add-button">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                {{ __('admin_instructors.add_teacher') }}
                            </button>
                            <button onclick="openModal('teacherRegisterModal')" class="teacher-add-button" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6v6H3zM15 5h6v6h-6zM3 17h6v2H5v2H3zM15 15h2v2h-2zM19 15h2v6h-6v-2h4zM11 5h2v16h-2z" />
                                </svg>
                                SELF REGISTER
                            </button>
                        </div>
                    </div>

                    <div class="teacher-stat-grid">
                        <div class="teacher-topstat rounded-xl px-4 py-3">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wider ">{{ __('admin_instructors.total_faculty') }}</div>
                            <div class="text-xl font-black ">{{ $totalInstructors }}</div>
                        </div>
                        <div class="teacher-topstat rounded-xl px-4 py-3">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wider ">{{ __('admin_instructors.active') }}</div>
                            <div class="text-xl font-black ">{{ $activeInstructors }}</div>
                        </div>
                        <div class="teacher-topstat rounded-xl px-4 py-3">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wider ">{{ __('admin_instructors.on_leave') }}</div>
                            <div class="text-xl font-black ">{{ $onLeaveInstructors }}</div>
                        </div>
                        <div class="teacher-topstat rounded-xl px-4 py-3">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wider ">{{ __('admin_instructors.classes') }}</div>
                            <div class="text-xl font-black text-[#00c9a7]">{{ $coveredClasses }}</div>
                        </div>
                        <div class="teacher-topstat rounded-xl px-4 py-3">
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wider ">{{ __('admin_instructors.departments') }}</div>
                            <div class="text-xl font-black ">{{ $depts->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="teacher-content-stack space-y-6">
                <div class="teacher-chart-grid">
                    <div class="chart-card animate-card" style="animation-delay:.05s">
                        <h3 class="mb-0.5 text-[15px] font-black text-[#0d0f1c]">{{ __('admin_instructors.by_department') }}</h3>
                        <p class="mb-4 text-xs text-gray-400">{{ __('admin_instructors.instructor_distribution') }}</p>
                        <div class="relative h-44"><canvas id="deptChart"></canvas></div>
                        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                            @forelse($deptStats as $deptName => $count)
                                <div class="flex items-center gap-2 text-xs text-gray-500"><span
                                        class="h-2.5 w-2.5 flex-shrink-0 rounded-sm bg-[#00c9a7]"></span>{{ $deptName }}
                                    {{ $count }}</div>
                            @empty
                                <div class="text-xs text-gray-400">{{ __('admin_instructors.no_departments') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="chart-card animate-card" style="animation-delay:.10s">
                        <h3 class="mb-0.5 text-[15px] font-black text-[#0d0f1c]">{{ __('admin_instructors.class_performance') }}</h3>
                        <p class="mb-4 text-xs text-gray-400">{{ __('admin_instructors.performance_desc') }}</p>
                        <div class="relative h-44"><canvas id="perfChart"></canvas></div>
                    </div>

                    <div class="chart-card animate-card" style="animation-delay:.15s">
                        <h3 class="mb-0.5 text-[15px] font-black text-[#0d0f1c]">{{ __('admin_instructors.workload_attendance') }}</h3>
                        <p class="mb-4 text-xs text-gray-400">{{ __('admin_instructors.trend_snapshot') }}</p>
                        <div class="relative h-44"><canvas id="workChart"></canvas></div>
                        <div class="mt-3 flex gap-4">
                            <div class="flex items-center gap-1.5 text-xs text-gray-400"><span
                                    class="inline-block h-0.5 w-3 rounded bg-[#3b82f6]"></span>{{ __('admin_instructors.attendance') }}</div>
                            <div class="flex items-center gap-1.5 text-xs text-gray-400"><span
                                    class="inline-block h-0.5 w-3 rounded bg-[#8b5cf6]"></span>{{ __('admin_instructors.workload') }}</div>
                        </div>
                    </div>
                </div>

                <div class="filter-card animate-card" style="animation-delay:.20s">
                    <div class="teacher-filter-row">
                        <div class="teacher-search search-wrap">
                            <svg class="absolute left-3.5   text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input id="searchInput" name="search" value="{{ request('search') }}" type="text"
                                placeholder="{{ __('admin_instructors.search_placeholder') }}"
                                onkeyup="filterInstructors(event)" class="teacher-search-input" />
                        </div>

                        <select id="deptFilter" onchange="filterInstructors()" class="teacher-filter-select">
                            <option value="">{{ __('admin_instructors.all_departments') }}</option>
                            @foreach ($depts as $dept)
                                <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}</option>
                            @endforeach
                        </select>

                        <select id="statusFilter" onchange="filterInstructors()" class="teacher-filter-select">
                            <option value="">{{ __('admin_instructors.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin_instructors.active') }}</option>
                            <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>{{ __('admin_instructors.on_leave') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('admin_instructors.inactive') }}
                            </option>
                        </select>

                        <div class="teacher-view-toggle">
                            <button id="viewGridBtn" onclick="toggleView('grid')" class="teacher-view-button is-active">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                </svg>
                            </button>
                            <button id="viewTableBtn" onclick="toggleView('table')" class="teacher-view-button">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="3" y1="6" x2="21" y2="6" />
                                    <line x1="3" y1="12" x2="21" y2="12" />
                                    <line x1="3" y1="18" x2="21" y2="18" />
                                </svg>
                            </button>
                        </div>

                        <button onclick="window.open('{{ route('admin.export.instructors') }}', '_blank')"
                            class="teacher-export-button">{{ __('admin_instructors.export') }}</button>

                        @if (Auth::user()->isSuperAdmin())
                            <button id="bulkDeleteInstructorsBtn" onclick="bulkDeleteInstructors()"
                                class="teacher-export-button" disabled
                                style="border-color:rgba(255,94,126,.35);color:#ff5e7e;opacity:.45;cursor:not-allowed">
                                DELETE SELECTED (<span id="selectedInstructorsCount">0</span>)
                            </button>
                        @endif

                        <div class="teacher-filter-count text-sm text-gray-400">{{ __('admin_instructors.showing') }} <span id="rowCount"
                                class="font-bold text-[#0d0f1c]">{{ $instructors->count() }}</span> {{ __('admin_instructors.instructors_suffix') }}</div>
                    </div>
                </div>

                <div id="gridView" class="teacher-grid-view">
                    @foreach ($instructors as $instructor)
                        @php
                            $palette = [
                                ['#3b82f6', '#1e3a5f'],
                                ['#00c9a7', '#003d33'],
                                ['#ff5e7e', '#4a0e1e'],
                                ['#f5a623', '#3d2600'],
                                ['#8b5cf6', '#2c1561'],
                                ['#f97316', '#3d1500'],
                                ['#06b6d4', '#082d36'],
                                ['#22c55e', '#0a2e14'],
                            ];
                            $col2 = $palette[$instructor->id % count($palette)];
                            $name2 = $instructor->user->name ?? __('admin_instructors.not_available');
                            $init2 = collect(explode(' ', $name2))
                                ->map(fn($part) => substr($part, 0, 1))
                                ->take(2)
                                ->join('');
                            $dept2 = $instructor->department->name ?? __('admin_instructors.unassigned');
                            $spec2 = $instructor->specialization ?? __('admin_instructors.generalist');
                            $cls2 = $instructor->classes_count ?? 0;
                            $code2 = $instructor->teacher_code ?? '—';
                            $status2 = $instructor->status ?? 'active';
                            $rate2 = min(99, 82 + $cls2 * 3);
                        @endphp
                        <div class="teacher-card animate-card cursor-pointer overflow-hidden rounded-2xl bg-white shadow-[0_2px_20px_rgba(0,0,0,.06)]"
                            style="animation-delay: {{ $loop->index * 0.05 }}s" data-id="{{ $instructor->id }}"
                            data-name="{{ strtolower($name2) }}" data-code="{{ $code2 }}"
                            data-dept="{{ $dept2 }}" data-dept-id="{{ $instructor->department_id }}"
                            data-status="{{ $status2 }}" data-spec="{{ strtolower($spec2) }}"
                            data-email="{{ strtolower($instructor->user->email ?? '') }}"
                            data-phone="{{ $instructor->user->phone ?? '—' }}" data-classes="{{ $cls2 }}">
                            @if (Auth::user()->isSuperAdmin())
                                <label style="position:absolute;top:10px;right:10px;z-index:5;background:rgba(255,255,255,.95);border:1px solid rgba(148,163,184,.35);border-radius:8px;padding:5px;cursor:pointer"
                                    onclick="event.stopPropagation()">
                                    <input type="checkbox" class="instructor-select" value="{{ $instructor->id }}"
                                        data-name="{{ $name2 }}" data-classes="{{ $cls2 }}"
                                        onchange="updateInstructorSelection()">
                                </label>
                            @endif
                            <div class="teacher-card-banner" style="background:{{ $col2[1] }}">
                                <div class="teacher-card-banner-glow"
                                    style="background:radial-gradient(circle at 30% 50%, {{ $col2[0] }}88, transparent 70%)">
                                </div>
                                <div class="teacher-card-badges">
                                    @if ($status2 === 'active')
                                        <span class="badge-soft bg-[#00c9a7]/15 text-[#00836e]"><span
                                                class="dot-online h-1.5 w-1.5 rounded-full"></span>{{ __('admin_instructors.active') }}</span>
                                    @elseif($status2 === 'on_leave')
                                        <span class="badge-soft bg-[#f5a623]/15 text-[#b87a0f]"><span
                                                class="dot-busy h-1.5 w-1.5 rounded-full"></span>{{ __('admin_instructors.leave') }}</span>
                                    @else
                                        <span class="badge-soft bg-gray-100 text-gray-500"><span
                                                class="dot-offline h-1.5 w-1.5 rounded-full"></span>{{ __('admin_instructors.inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="teacher-card-body px-5 pb-5">
                                <div class="teacher-card-avatar-row">
                                    <div class="avatar-ring">
                                        <div class="teacher-card-avatar" style="background:{{ $col2[0] }}">
                                            {{ strtoupper($init2) }}</div>
                                    </div>
                                    <div class="teacher-rating">
                                        <span class="text-[13px] text-[#f5a623]">★</span><span
                                            class="text-[13px] text-[#f5a623]">★</span><span
                                            class="text-[13px] text-[#f5a623]">★</span><span
                                            class="text-[13px] text-[#f5a623]">★</span><span
                                            class="text-xs text-gray-400">4.{{ min(9, $cls2 + 2) }}</span>
                                    </div>
                                </div>
                                <h3 class="teacher-card-title">{{ $name2 }}</h3>
                                <p class="teacher-card-subtitle">{{ $spec2 }} · {{ $dept2 }}</p>
                                <p class="teacher-card-code">{{ $code2 }}</p>
                                <div class="teacher-card-metrics">
                                    <div class="teacher-card-metric">
                                        <strong>{{ $cls2 }}</strong><span>{{ __('admin_instructors.classes') }}</span>
                                    </div>
                                    <div class="teacher-card-metric"><strong>{{ $rate2 }}</strong><span>{{ __('admin_instructors.rate') }}</span>
                                    </div>
                                    <div class="teacher-card-metric"><strong
                                            style="color:#00c9a7">{{ $depts->count() }}</strong><span>{{ __('admin_instructors.depts') }}</span></div>
                                </div>
                                <div class="mb-3.5">
                                    <div class="teacher-progress-label"><span>{{ __('admin_instructors.class_coverage') }}</span><strong>{{ $rate2 }}%</strong></div>
                                    <div class="pbar-track">
                                        <div class="pbar-fill"
                                            style="width:{{ $rate2 }}%;background:{{ $col2[0] }}"></div>
                                    </div>
                                </div>
                                <div class="teacher-skill-row">
                                    <span class="teacher-skill-tag">{{ $dept2 }}</span>
                                    <span class="teacher-skill-tag">{{ $spec2 }}</span>
                                </div>
                                <div class="teacher-card-actions">
                                    <div class="text-xs text-gray-400">{{ __('admin_instructors.assigned_classes', ['count' => $cls2]) }}</div>
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <button onclick="openProfile(this.closest('.teacher-card'))"
                                            class="teacher-action-link text-[#00c9a7] hover:bg-[#00c9a7]/10 hover:text-[#00836e]">{{ __('admin_instructors.view') }}</button>
                                        <button onclick="openEditModal(this.closest('.teacher-card'))"
                                            class="teacher-action-link text-[#3b82f6] hover:bg-[#3b82f6]/10 hover:text-[#1d4ed8]">{{ __('admin_instructors.edit') }}</button>
                                        @if (Auth::user()->isSuperAdmin())
                                            <button
                                                onclick="openDeleteModal({{ $instructor->id }}, '{{ addslashes($name2) }}', {{ (int) $cls2 }})"
                                                class="teacher-action-link text-[#ff5e7e] hover:bg-[#ff5e7e]/10 hover:text-[#c4284a]">{{ __('admin_instructors.remove') }}</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="tableView"
                    class="teacher-table-view is-hidden overflow-x-auto rounded-2xl bg-white shadow-[0_2px_20px_rgba(0,0,0,.06)]">
                    <table class="teacher-list-table w-full text-sm" id="instructorTable">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                @if (Auth::user()->isSuperAdmin())
                                    <th style="width:42px">
                                        <input type="checkbox" id="selectAllInstructors" onchange="toggleAllInstructors(this.checked)">
                                    </th>
                                @endif
                                <th>{{ __('admin_instructors.table_teacher') }}</th>
                                <th>{{ __('admin_instructors.table_department') }}</th>
                                <th>{{ __('admin_instructors.table_specialization') }}</th>
                                <th>{{ __('admin_instructors.table_classes') }}</th>
                                <th>{{ __('admin_instructors.table_status') }}</th>
                                <th style="text-align:right">{{ __('admin_instructors.table_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($instructors as $instructor)
                                @php
                                    $avatarColors = [
                                        ['#2563EB', '#38BDF8'],
                                        ['#22C55E', '#86EFAC'],
                                        ['#8B5CF6', '#C4B5FD'],
                                        ['#F59E0B', '#FBBF24'],
                                        ['#EF4444', '#F87171'],
                                        ['#10B981', '#10B981'],
                                    ];
                                    $col = $avatarColors[$instructor->id % count($avatarColors)];
                                    $name = $instructor->user->name ?? __('admin_instructors.not_available');
                                    $init = strtoupper(substr($name, 0, 2));
                                    $dept = $instructor->department->name ?? __('admin_instructors.unassigned');
                                    $spec = $instructor->specialization ?? __('admin_instructors.generalist');
                                    $classes = $instructor->classes_count ?? 0;
                                    $status = $instructor->status ?? 'active';
                                    $email = $instructor->user->email ?? __('admin_instructors.not_available');
                                    $code = $instructor->teacher_code ?? '—';
                                @endphp
                                <tr data-id="{{ $instructor->id }}" data-name="{{ strtolower($name) }}"
                                    data-code="{{ $code }}" data-dept="{{ $dept }}"
                                    data-dept-id="{{ $instructor->department_id }}" data-status="{{ $status }}"
                                    data-spec="{{ strtolower($spec) }}" data-email="{{ strtolower($email) }}"
                                    data-phone="{{ $instructor->user->phone ?? '—' }}"
                                    data-classes="{{ $classes }}" class="fade-up">

                                    {{-- Instructor --}}
                                    @if (Auth::user()->isSuperAdmin())
                                        <td>
                                            <input type="checkbox" class="instructor-select" value="{{ $instructor->id }}"
                                                data-name="{{ $name }}" data-classes="{{ $classes }}"
                                                onchange="updateInstructorSelection()" onclick="event.stopPropagation()">
                                        </td>
                                    @endif
                                    {{-- Instructor --}}
                                    <td>
                                        <div class="subject-cell">
                                            <div class="subject-avatar"
                                                style="background:{{ $col[0] }}22;color:{{ $col[0] }};border:1px solid {{ $col[0] }}33;font-size:10px;width:36px;height:36px;border-radius:50%">
                                                {{ $init }}
                                            </div>
                                            <div>
                                                <div class="subject-name">{{ $name }}</div>
                                                <div class="subject-id" style="color:var(--muted)">
                                                    {{ $code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Department --}}
                                    <td>
                                        <span
                                            style="display:inline-flex;align-items:center;gap:6px;font-family:var(--font-mono);font-size:10px;color:var(--text2);background:var(--surface3);border:1px solid var(--border2);padding:3px 10px;border-radius:var(--radius-sm)">
                                            {{ strtoupper($dept) }}
                                        </span>
                                    </td>

                                    {{-- Specialization --}}
                                    <td>
                                        <span style="font-size:12px;color:var(--muted2)">{{ $spec }}</span>
                                    </td>

                                    {{-- Classes --}}
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <span
                                                style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--accent)">{{ $classes }}</span>
                                            <div>
                                                <div
                                                    style="width:48px;height:3px;background:var(--border2);border-radius:99px;overflow:hidden">
                                                    <div
                                                        style="width:{{ min(100, $classes * 25) }}%;height:100%;background:var(--accent);border-radius:99px">
                                                    </div>
                                                </div>
                                                <div
                                                    style="font-family:var(--font-mono);font-size:8px;color:var(--muted);margin-top:2px">
                                                    {{ __('admin_instructors.assigned') }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if ($status === 'active')
                                            <span class="status-tag tag-active">{{ __('admin_instructors.active_upper') }}</span>
                                        @elseif($status === 'on_leave')
                                            <span class="status-tag tag-waiting">{{ __('admin_instructors.on_leave_upper') }}</span>
                                        @else
                                            <span class="status-tag"
                                                style="background:var(--surface3);color:var(--muted2);border:1px solid var(--border2)">{{ __('admin_instructors.inactive_upper') }}</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td style="text-align:right">
                                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                                            <button class="action-btn btn-view" title="{{ __('admin_instructors.view_profile') }}"
                                                onclick="openProfile(this.closest('tr'))">
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button class="action-btn btn-edit" title="{{ __('admin_instructors.edit_profile_title') }}"
                                                onclick="openEditModal(this.closest('tr'))">
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button class="action-btn btn-enroll" title="{{ __('admin_instructors.view_assigned_classes') }}"
                                                onclick="showToast(instructorText('class_assignment_soon', 'Class assignment view coming soon.'),'info')">
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </button>
                                            <button class="action-btn" title="{{ __('admin_instructors.semester_assignment') }}"
                                                style="background:color-mix(in srgb, var(--violet) 9%, transparent);border-color:color-mix(in srgb, var(--violet) 27%, transparent);color:var(--violet)">
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                            @if (Auth::user()->isSuperAdmin())
                                                <button class="action-btn btn-del" title="{{ __('admin_instructors.remove_instructor') }}"
                                                    onclick="openDeleteModal({{ $instructor->id }}, '{{ addslashes($name) }}', {{ (int) $classes }})">
                                                    <svg width="12" height="12" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isSuperAdmin() ? 7 : 6 }}">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div class="empty-title">{{ __('admin_instructors.no_instructors') }}</div>
                                            <div class="empty-desc">{{ __('admin_instructors.empty_desc') }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if ($instructors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div style="margin-top: 20px"
                    class="flex flex-col gap-3 border-t  px-5 pt-7 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs font-black uppercase tracking-[.14em] text-slate-400">
                        {{ __('admin_instructors.showing_range', ['first' => $instructors->firstItem(), 'last' => $instructors->lastItem(), 'total' => $instructors->total()]) }}
                    </span>
                    {{ $instructors->links('vendor.pagination.academy') }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const instructorT = @json(trans('admin_instructors'));
        const instructorText = (key, fallback = key) => instructorT[key] || fallback;
        const instructorReplace = (key, replacements, fallback = key) => {
            let text = instructorText(key, fallback);
            Object.entries(replacements).forEach(([name, value]) => {
                text = text.replace(`:${name}`, value);
            });
            return text;
        };

        // ── Overview charts ───────────────────────────
        const teacherDeptLabels = @json($deptLabels);
        const teacherDeptCounts = @json($deptCounts);
        const teacherStatusCounts = @json([$activeInstructors, $onLeaveInstructors, $inactiveInstructors]);
        const teacherClassesCovered = {{ (int) $coveredClasses }};
        const teacherTotalInstructors = {{ (int) $totalInstructors }};

        if (window.Chart) {
            const teacherCharts = [];
            const teacherChartTheme = () => {
                const teacherShell = document.querySelector('.teacher-overview-shell');
                const teacherTheme = teacherShell ? getComputedStyle(teacherShell) : null;
                return {
                    text: teacherTheme?.getPropertyValue('--teacher-muted').trim() || '#8b94a7',
                    grid: document.documentElement.getAttribute('data-theme') === 'dark' ?
                        'rgba(148,163,184,.18)' : 'rgba(0,0,0,.05)',
                    surface: teacherTheme?.getPropertyValue('--teacher-surface').trim() || '#fff'
                };
            };
            let activeTeacherChartTheme = teacherChartTheme();

            Chart.defaults.font.family = "'Outfit', system-ui, sans-serif";
            Chart.defaults.color = activeTeacherChartTheme.text;

            const deptCanvas = document.getElementById('deptChart');
            if (deptCanvas) {
                teacherCharts.push(new Chart(deptCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: teacherDeptLabels.length ? teacherDeptLabels : [instructorText('no_department', 'No department')],
                        datasets: [{
                            data: teacherDeptCounts.length ? teacherDeptCounts : [0],
                            backgroundColor: ['#00c9a7', '#3b82f6', '#f5a623', '#ff5e7e', '#8b5cf6',
                                '#06b6d4', '#22c55e'
                            ],
                            borderColor: activeTeacherChartTheme.surface,
                            borderWidth: 3,
                            hoverOffset: 5
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                }));
            }

            const perfCanvas = document.getElementById('perfChart');
            if (perfCanvas) {
                const perfLabels = teacherDeptLabels.length ? teacherDeptLabels : [instructorText('no_department', 'No department')];
                const perfValues = (teacherDeptCounts.length ? teacherDeptCounts : [0]).map((count, index) => Math.min(98,
                    68 + (count * 4) + (index % 3) * 3));

                teacherCharts.push(new Chart(perfCanvas, {
                    type: 'bar',
                    data: {
                        labels: perfLabels,
                        datasets: [{
                            data: perfValues,
                            backgroundColor: ['#00c9a7', '#3b82f6', '#8b5cf6', '#f5a623', '#ff5e7e',
                                '#06b6d4', '#22c55e'
                            ],
                            borderRadius: 5,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                min: 50,
                                max: 100,
                                grid: {
                                    color: activeTeacherChartTheme.grid
                                },
                                ticks: {
                                    color: activeTeacherChartTheme.text,
                                    font: {
                                        size: 10,
                                        weight: 700
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: activeTeacherChartTheme.text,
                                    font: {
                                        size: 10,
                                        weight: 700
                                    }
                                }
                            }
                        }
                    }
                }));
            }

            const workCanvas = document.getElementById('workChart');
            if (workCanvas) {
                const workloadBase = Math.max(1, Math.round(teacherClassesCovered / Math.max(1, teacherTotalInstructors)));

                teacherCharts.push(new Chart(workCanvas, {
                    type: 'line',
                    data: {
                        labels: ['Wk1', 'Wk2', 'Wk3', 'Wk4', 'Wk5', 'Wk6'],
                        datasets: [{
                                label: 'Attendance',
                                data: [92, 95, 94, 97, 96, Math.min(99, 90 + teacherStatusCounts[0])],
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,.08)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#3b82f6',
                                fill: true,
                                tension: .4
                            },
                            {
                                label: 'Workload',
                                data: [workloadBase + 2, workloadBase + 4, workloadBase + 1, workloadBase +
                                    5, workloadBase + 3, workloadBase + 4
                                ],
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,.06)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#8b5cf6',
                                fill: true,
                                tension: .4,
                                borderDash: [4, 3]
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: activeTeacherChartTheme.text,
                                    font: {
                                        size: 10,
                                        weight: 700
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: activeTeacherChartTheme.grid
                                },
                                ticks: {
                                    color: activeTeacherChartTheme.text,
                                    precision: 0,
                                    font: {
                                        size: 10,
                                        weight: 700
                                    }
                                }
                            }
                        }
                    }
                }));
            }

            const updateTeacherChartTheme = () => {
                activeTeacherChartTheme = teacherChartTheme();
                Chart.defaults.color = activeTeacherChartTheme.text;
                teacherCharts.forEach(chart => {
                    if (chart.config.type === 'doughnut') {
                        chart.data.datasets.forEach(dataset => dataset.borderColor = activeTeacherChartTheme
                            .surface);
                    }
                    Object.values(chart.options.scales || {}).forEach(scale => {
                        if (scale.ticks) scale.ticks.color = activeTeacherChartTheme.text;
                        if (scale.grid && scale.grid.display !== false) scale.grid.color =
                            activeTeacherChartTheme.grid;
                    });
                    chart.update('none');
                });
            };

            new MutationObserver(updateTeacherChartTheme).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });
        }

        // ── Modal helpers ──────────────────────────────
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => {
                if (e.target === el) el.classList.remove('open');
            });
        });

        // ── Toast ──────────────────────────────────────
        async function copyTeacherRegisterLink() {
            const input = document.getElementById('teacherRegisterLink');
            const link = input.value;
            try {
                await navigator.clipboard.writeText(link);
                showToast('Teacher registration link copied.', 'success');
            } catch (e) {
                input.focus();
                input.select();
                document.execCommand('copy');
                showToast('Teacher registration link copied.', 'success');
            }
        }

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const ic = document.getElementById('toastIcon');
            t.className = `toast show toast-${type}`;
            ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
            document.getElementById('toastMsg').textContent = window.__t ? window.__t(msg) : msg;
            clearTimeout(t._t);
            t._t = setTimeout(() => t.classList.remove('show'), 3200);
        }

        // ── Avatar preview ─────────────────────────────
        function updateAvatarPreview(val) {
            const words = val.trim().split(/\s+/);
            const init = words.length >= 2 ?
                (words[0][0] + words[words.length - 1][0]).toUpperCase() :
                (val.slice(0, 2).toUpperCase() || '?');
            document.getElementById('modalAvatarPreview').textContent = init;
        }

        // ── View toggle ────────────────────────────────
        function toggleView(mode) {
            const isTable = mode === 'table';
            document.getElementById('tableView').classList.toggle('is-hidden', !isTable);
            document.getElementById('gridView').classList.toggle('is-hidden', isTable);
            document.getElementById('viewTableBtn').className = isTable ?
                'teacher-view-button is-active' :
                'teacher-view-button';
            document.getElementById('viewGridBtn').className = isTable ?
                'teacher-view-button' :
                'teacher-view-button is-active';
        }
        toggleView('grid');

        // ── Filter (Server-side) ───────────────────────
        let filterTimeout = null;

        function filterInstructors(e) {
            if (e && e.type === 'keyup' && e.key !== 'Enter') {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => filterInstructors(), 800);
                return;
            }
            const q = document.getElementById('searchInput').value;
            const dept = document.getElementById('deptFilter').value;
            const status = document.getElementById('statusFilter').value;

            const params = new URLSearchParams(window.location.search);
            if (q) params.set('search', q);
            else params.delete('search');
            if (dept) params.set('dept', dept);
            else params.delete('dept');
            if (status) params.set('status', status);
            else params.delete('status');
            params.set('page', 1);

            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }

        // ── Delete ─────────────────────────────────────
        let pendingDeleteId = null;

        function openDeleteModal(id, name, classes = 0) {
            if (Number(classes) > 0) {
                showToast(`${name} cannot be deleted because assigned classes exist.`, 'error');
                return;
            }
            pendingDeleteId = id;
            document.getElementById('deleteSubtitle').innerHTML =
                instructorReplace('delete_named_warning', { name: `<strong style="color:var(--text2)">${name}</strong>` }, ':name will be permanently removed. All class assignments may be affected.');
            openModal('deleteModal');
        }

        function selectedInstructorRows() {
            const selected = new Map();
            document.querySelectorAll('.instructor-select:checked').forEach(input => {
                selected.set(String(input.value), {
                    id: Number(input.value),
                    name: input.dataset.name || `#${input.value}`,
                    classes: Number(input.dataset.classes || 0)
                });
            });
            return Array.from(selected.values());
        }

        function updateInstructorSelection() {
            const selected = selectedInstructorRows();
            const selectedIds = new Set(selected.map(item => String(item.id)));
            document.querySelectorAll('.instructor-select').forEach(input => {
                input.checked = selectedIds.has(String(input.value));
            });

            const countEl = document.getElementById('selectedInstructorsCount');
            const btn = document.getElementById('bulkDeleteInstructorsBtn');
            const selectAll = document.getElementById('selectAllInstructors');

            if (countEl) countEl.textContent = selected.length;
            if (btn) {
                btn.disabled = selected.length === 0;
                btn.style.opacity = selected.length ? '1' : '.45';
                btn.style.cursor = selected.length ? 'pointer' : 'not-allowed';
            }
            if (selectAll) {
                const visibleRows = Array.from(document.querySelectorAll('#tableBody tr[data-id]'))
                    .filter(row => row.style.display !== 'none');
                selectAll.checked = visibleRows.length > 0 && visibleRows.every(row => selectedIds.has(String(row.dataset.id)));
                selectAll.indeterminate = visibleRows.some(row => selectedIds.has(String(row.dataset.id))) && !selectAll.checked;
            }
        }

        function toggleAllInstructors(checked) {
            document.querySelectorAll('#tableBody tr[data-id]').forEach(row => {
                if (row.style.display === 'none') return;
                document.querySelectorAll(`.instructor-select[value="${row.dataset.id}"]`).forEach(input => {
                    input.checked = checked;
                });
            });
            updateInstructorSelection();
        }

        let pendingBulkInstructors = [];

        function bulkDeleteInstructors() {
            const selected = selectedInstructorRows();
            if (!selected.length) return;

            const blocked = selected.filter(item => item.classes > 0);
            if (blocked.length) {
                const names = blocked.slice(0, 3).map(item => `${item.name} (${item.classes})`).join(', ');
                showToast(`Cannot delete instructors with assigned classes: ${names}${blocked.length > 3 ? '...' : ''}`, 'error');
                return;
            }

            pendingBulkInstructors = selected;
            document.getElementById('bulkDeleteSubtitle').innerHTML =
                `<strong style="color:var(--text2)">${selected.length}</strong> selected instructor${selected.length === 1 ? '' : 's'} will be permanently removed.<br>This action cannot be undone.`;
            openModal('bulkDeleteModal');
        }

        document.getElementById('confirmBulkDeleteBtn').addEventListener('click', async () => {
            const selected = pendingBulkInstructors;
            if (!selected.length) return;

            const btn = document.getElementById('bulkDeleteInstructorsBtn');
            const modalBtn = document.getElementById('confirmBulkDeleteBtn');
            const ogHtml = btn ? btn.innerHTML : '';
            const modalOgHtml = modalBtn.innerHTML;
            if (btn) {
                btn.innerHTML = instructorText('deleting', 'DELETING...');
                btn.disabled = true;
            }
            modalBtn.innerHTML = instructorText('deleting', 'DELETING...');
            modalBtn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';

            try {
                const res = await fetch('/api/admin/instructors/bulk-delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        teacher_ids: selected.map(item => item.id)
                    })
                });
                const data = await res.json().catch(() => ({
                    success: res.ok
                }));

                if (data.success || res.ok) {
                    selected.forEach(item => {
                        document.querySelectorAll(`[data-id="${item.id}"]`).forEach(el => {
                            el.style.opacity = '0';
                            el.style.transition = 'opacity .3s';
                            setTimeout(() => el.remove(), 350);
                        });
                    });
                    const countEl = document.getElementById('rowCount');
                    if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent || '0') - selected.length);
                    showToast(`${selected.length} instructor${selected.length === 1 ? '' : 's'} removed.`, 'success');
                    closeModal('bulkDeleteModal');
                    pendingBulkInstructors = [];
                } else {
                    const serverBlocked = Array.isArray(data.blocked) ? data.blocked : [];
                    if (serverBlocked.length) {
                        const names = serverBlocked.slice(0, 3).map(item => `${item.name} (${item.classes_count})`).join(', ');
                        showToast(`Cannot delete instructors with assigned classes: ${names}${serverBlocked.length > 3 ? '...' : ''}`, 'error');
                    } else {
                        showToast(data.error || instructorText('delete_failed', 'Failed to delete instructor.'), 'error');
                    }
                }
            } catch (e) {
                showToast(instructorReplace('network_error_prefix', { message: e.message }, 'Network error: :message'), 'error');
            } finally {
                if (btn) {
                    btn.innerHTML = ogHtml;
                    btn.disabled = selectedInstructorRows().length === 0;
                }
                modalBtn.innerHTML = modalOgHtml;
                modalBtn.disabled = false;
                updateInstructorSelection();
            }
        });
        document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
            if (!pendingDeleteId) return;
            const btn = document.getElementById('confirmDeleteBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = instructorText('deleting', 'DELETING...');
            btn.disabled = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';
            try {
                const res = await fetch(`/api/admin/instructors/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok && res.status !== 200) {
                    // Fallback: try web route
                    const res2 = await fetch(`/admin/instructors/${pendingDeleteId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    });
                }
                const data = await res.json().catch(() => ({
                    success: res.ok
                }));
                if (data.success || res.ok) {
                    document.querySelectorAll(`[data-id="${pendingDeleteId}"]`).forEach(el => {
                        el.style.opacity = '0';
                        el.style.transition = 'opacity .3s';
                        setTimeout(() => el.remove(), 350);
                    });
                    showToast(instructorText('removed_success', 'Instructor removed from registry.'), 'success');
                    closeModal('deleteModal');
                    const countEl = document.getElementById('rowCount');
                    if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
                } else {
                    showToast(data.error || instructorText('delete_failed', 'Failed to delete instructor.'), 'error');
                }
            } catch (e) {
                showToast(instructorReplace('network_error_prefix', { message: e.message }, 'Network error: :message'), 'error');
            }
            btn.innerHTML = ogHtml;
            btn.disabled = false;
            pendingDeleteId = null;
        });

        // ── View Profile ───────────────────────────────
        function openProfile(row) {
            // Works for both <tr> table rows and .instructor-card grid divs
            const name = row.dataset.name ?
                row.dataset.name.replace(/\b\w/g, c => c.toUpperCase()) :
                (row.querySelector('.subject-name')?.textContent.trim() || '—');
            const dept = row.dataset.dept || '—';
            const status = row.dataset.status || 'active';
            const email = row.dataset.email || '—';
            const code = row.dataset.code || '—';
            const classes = row.dataset.classes || '0';
            const spec = row.dataset.spec || '—';
            const phone = row.dataset.phone || '—';
            const init = name.trim().split(/\s+/).filter(Boolean).map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?';

            document.getElementById('profileAvatar').textContent = init;
            document.getElementById('profileName').textContent = name;
            document.getElementById('profileDept').textContent = dept.toUpperCase ? dept.toUpperCase() : dept;
            document.getElementById('profileEmail').textContent = email;
            document.getElementById('profilePhone').textContent = phone;
            document.getElementById('profileCode').textContent = code;
            document.getElementById('profileClasses').textContent = classes;
            document.getElementById('profileSpec').textContent = spec;

            const stEl = document.getElementById('profileStatus');
            stEl.textContent = status === 'active' ? instructorText('active_upper', 'ACTIVE') : status === 'on_leave' ? instructorText('on_leave_upper', 'ON LEAVE') : instructorText('inactive_upper', 'INACTIVE');
            stEl.className = 'status-tag ' + (status === 'active' ? 'tag-active' : status === 'on_leave' ? 'tag-waiting' :
                '');

            document.getElementById('profileEditBtn').onclick = () => {
                closeModal('profileModal');
                openEditModal(row);
            };
            openModal('profileModal');
        }

        // ── Create ─────────────────────────────────────
        function openCreateModal() {
            document.getElementById('instructorModalTitle').textContent = instructorText('add_instructor', 'Add Instructor');
            document.getElementById('modalSubmitLabel').textContent = instructorText('add_instructor_upper', 'ADD INSTRUCTOR');
            document.getElementById('modalMode').value = 'create';
            document.getElementById('modalInstructorId').value = '';
            document.getElementById('modalAvatarPreview').textContent = '?';
            document.getElementById('instructorForm').reset();
            openModal('instructorModal');
        }

        // ── Edit ───────────────────────────────────────
        function openEditModal(row) {
            // Works for both <tr> table rows and .instructor-card grid divs
            const name = row.dataset.name ?
                row.dataset.name.replace(/\b\w/g, c => c.toUpperCase()) :
                (row.querySelector('.subject-name')?.textContent.trim() || '');
            const init = name.trim().split(/\s+/).filter(Boolean).map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?';
            document.getElementById('instructorModalTitle').textContent = instructorText('edit_instructor', 'Edit Instructor');
            document.getElementById('modalSubmitLabel').textContent = instructorText('save_changes', 'SAVE CHANGES');
            document.getElementById('modalMode').value = 'edit';
            document.getElementById('modalInstructorId').value = row.dataset.id || '';
            document.getElementById('modalName').value = name;
            document.getElementById('modalDept').value = row.dataset.deptId || '';
            document.getElementById('modalStatus').value = row.dataset.status || 'active';
            document.getElementById('modalEmail').value = row.dataset.email || '';
            document.getElementById('modalPhone').value = row.dataset.phone || '';
            document.getElementById('modalSpec').value = row.dataset.spec || '';
            document.getElementById('modalAvatarPreview').textContent = init;
            openModal('instructorModal');
        }

        document.getElementById('instructorForm').addEventListener('submit', async e => {
            e.preventDefault();
            const mode = document.getElementById('modalMode').value;
            const id = document.getElementById('modalInstructorId').value;
            const btn = document.getElementById('modalSubmitBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML =
                `<span class="loading-spinner" style="width:12px;height:12px;border-width:2px;margin-right:8px"></span> ${instructorText('saving', 'SAVING...')}`;
            btn.disabled = true;

            const payload = {
                name: document.getElementById('modalName').value,
                department_id: document.getElementById('modalDept').value,
                specialization: document.getElementById('modalSpec').value,
                status: document.getElementById('modalStatus').value,
                email: document.getElementById('modalEmail').value,
                phone: document.getElementById('modalPhone').value,
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';
            try {
                const url = mode === 'create' ? '/api/admin/instructors' : `/api/admin/instructors/${id}`;
                const res = await fetch(url, {
                    method: mode === 'create' ? 'POST' : 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    const message = data.temporary_password ?
                        instructorReplace('added_with_password', { password: data.temporary_password }, 'Instructor added. Temporary password: :password') :
                        (mode === 'create' ? instructorText('added_success', 'Instructor added to registry.') : instructorText('updated_success', 'Profile updated successfully.'));
                    showToast(message, 'success');
                    closeModal('instructorModal');
                    setTimeout(() => window.location.reload(), 900);
                } else if (data.errors) {
                    // Validation errors from Laravel
                    const msgs = Object.values(data.errors).flat().join(' ');
                    showToast(msgs, 'error');
                } else {
                    showToast(data.error || data.message || instructorText('operation_failed', 'Operation failed.'), 'error');
                }
            } catch (err) {
                showToast(instructorText('network_error', 'Network error.'), 'error');
            }
            btn.innerHTML = ogHtml;
            btn.disabled = false;
        });
    </script>
@endsection
