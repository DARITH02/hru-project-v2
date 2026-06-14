@extends('layouts.app')

@section('content')

    {{-- ═══ PREMIUM DESIGN SYSTEM ═══ --}}
    <style>
        :root {
            --glass-bg: rgba(var(--bg-rgb), 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            --accent-glow: 0 0 20px color-mix(in srgb, var(--accent) 20%, transparent);
        }

        .premium-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            border-color: color-mix(in srgb, var(--accent) 27%, transparent);
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        }

        .att-table tr {
            transition: all 0.2s ease;
        }

        .att-table tbody tr:hover {
            background: var(--surface2);
            box-shadow: inset 4px 0 0 var(--accent);
        }

        .stat-value-new {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
            margin: 10px 0;
            background: linear-gradient(135deg, var(--text), var(--text2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label-new {
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .glow-icon {
            padding: 12px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.05);
        }

        .table-header-premium {
            background: var(--surface3);
            border-bottom: 2px solid var(--border);
        }

        .table-header-premium th {
            padding: 18px 20px;
            font-family: var(--font-mono);
            font-size: 9px;
            font-weight: 900;
            color: var(--muted2);
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .page-title-new {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--text);
            margin-bottom: 5px;
        }

        .page-subtitle-new {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        /* Smooth scroll for modals */
        .modal-body {
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        /* Action Dropdown Menu */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-dropdown-menu {
            position: fixed;
            right: auto;
            top: auto;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.22);
            z-index: 2200;
            width: 225px;
            display: none;
            margin-top: 0;
            padding: 6px;
            backdrop-filter: blur(10px);
            transform-origin: top right;
        }

        .action-dropdown-menu.show {
            display: block;
            animation: dropdownFadeIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: var(--text2);
            font-size: 10px;
            font-family: var(--font-mono);
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            white-space: nowrap;
        }

        .dropdown-item:hover {
            background: var(--surface3);
            color: var(--accent);
            transform: translateX(4px);
        }

        .dropdown-item svg {
            width: 14px;
            height: 14px;
            opacity: 0.7;
        }

        .dropdown-item:hover svg {
            opacity: 1;
        }

        .dropdown-item.text-red:hover {
            color: var(--red);
            background: color-mix(in srgb, var(--red) 3%, transparent);
        }

        .more-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .more-btn:hover,
        .more-btn.active {
            background: color-mix(in srgb, var(--accent) 7%, transparent);
            color: var(--accent);
            border-color: color-mix(in srgb, var(--accent) 27%, transparent);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

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
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div
                    style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">
                    {{ __('admin_courses.delete_class_question') }}</div>
                <div
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.06em;line-height:1.6">
                    {{ __('admin_courses.delete_warning') }}
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal('deleteModal')" class="btn-secondary">{{ __('admin_courses.cancel') }}</button>
                <button id="confirmDeleteBtn"
                    style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;font-weight:600;cursor:pointer;transition:all .2s;">
                    {{ __('admin_courses.delete_entry') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ BULK DELETE MODAL ═══ --}}
    <div id="bulkDeleteModal" class="modal-overlay">
        <div class="modal-box danger-modal-box">
            <div class="danger-modal-body">
                <div class="danger-modal-icon">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div class="danger-modal-title">{{ __('admin_courses.delete_selected_classes') }}</div>
                <div class="danger-modal-text">
                    {{ __('admin_courses.bulk_delete_warning') }}
                </div>
                <div id="bulkDeleteModalCount" class="danger-modal-count">
                    {{ __('admin_courses.selected_classes', ['count' => 1]) }}
                </div>
            </div>
            <div class="danger-modal-footer">
                <button type="button" onclick="closeModal('bulkDeleteModal')" class="btn-secondary">{{ __('admin_courses.cancel') }}</button>
                <button type="button" id="confirmBulkDeleteBtn" class="btn-danger">
                    {{ __('admin_courses.delete_selected') }}
                </button>
            </div>
        </div>
    </div>


    {{-- ═══ VIEW / EDIT MODAL ═══ --}}
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-head">
                <span id="editModalTitle" class="modal-title">{{ __('admin_courses.edit_class') }}</span>
                <button onclick="closeModal('editModal')" class="modal-close">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editForm">
                @csrf
                <input type="hidden" id="editClassId">
                <div class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.subject') }}</label>
                            <select id="editSubjectName" name="subject_id" class="form-input">
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.class_groups') }}</label>
                            <select id="editClassGroup" name="group_ids[]" class="form-input" multiple
                                style="height: auto; min-height: 100px;">
                                @foreach($classGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.instructor') }}</label>
                            <select id="editInstructor" name="teacher_id" class="form-input">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? __('admin_courses.unknown') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.location_room') }}</label>
                            <input id="editRoom" name="room_number" class="form-input" type="text" placeholder="{{ __('admin_courses.example_room') }}">
                        </div>
                    </div>

                    <div class="form-group"
                        style="padding:15px; background:var(--surface3); border:1px solid var(--border); border-radius:12px; margin: 5px 0 15px;">
                        <label class="form-label" style="margin-top:0">{{ __('admin_courses.schedule_timing') }}</label>
                        <div class="form-grid-2">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:9px">{{ __('admin_courses.preferred_days') }}</label>
                                <div class="day-selector-grid" id="editDaySelector">
                                    <label class="day-chip"><input type="checkbox" value="Mon"><span>M</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Tue"><span>T</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Wed"><span>W</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Thu"><span>T</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Fri"><span>F</span></label>
                                    <label class="day-chip weekend"><input type="checkbox"
                                            value="Sat"><span>S</span></label>
                                    <label class="day-chip weekend"><input type="checkbox"
                                            value="Sun"><span>S</span></label>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                <div class="form-group" style="margin-bottom:0">
                                    <label class="form-label" style="font-size:9px">{{ __('admin_courses.start') }}</label>
                                    <input id="editTimeStart" name="time_start" class="form-input" type="time">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label class="form-label" style="font-size:9px">{{ __('admin_courses.end') }}</label>
                                    <input id="editTimeEnd" name="time_end" class="form-input" type="time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">{{ __('admin_courses.operational_status') }}</label>
                        <select id="editStatus" name="status" class="form-input">
                            <option value="active">{{ __('admin_courses.active') }}</option>
                            <option value="waiting">{{ __('admin_courses.waiting') }}</option>
                            <option value="ready">{{ __('admin_courses.ready') }}</option>
                        </select>
                    </div>
                </div>
                <div id="editModalFooter" class="modal-footer">
                    <button type="button" onclick="closeModal('editModal')" class="btn-secondary">{{ __('admin_courses.cancel') }}</button>
                    <button type="submit" class="btn-primary">{{ __('admin_courses.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ CREATE MODAL ═══ --}}
    <div id="createModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-head">
                <span class="modal-title">{{ __('admin_courses.new_entry_title') }}</span>
                <button onclick="closeModal('createModal')" class="modal-close">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.subject') }} <span class="req">*</span></label>
                            <select name="subject_id" class="form-input" required>
                                <option value="" disabled selected>{{ __('admin_courses.select_subject') }}</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.class_groups') }} <span class="req">*</span></label>
                            <select name="group_ids[]" class="form-input" required multiple
                                style="height: auto; min-height: 100px;">
                                @foreach($classGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.lead_instructor') }} <span class="req">*</span></label>
                            <select name="teacher_id" class="form-input" required>
                                <option value="" disabled selected>{{ __('admin_courses.select_instructor') }}</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? __('admin_courses.unknown_instructor') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_courses.location_room') }} <span class="req">*</span></label>
                            <input name="room_number" class="form-input" type="text" required placeholder="{{ __('admin_courses.example_room') }}">
                        </div>
                    </div>

                    <div class="form-group"
                        style="padding:15px; background:var(--surface3); border:1px solid var(--border); border-radius:12px; margin: 10px 0 15px;">
                        <label class="form-label" style="margin-top:0">{{ __('admin_courses.weekly_schedule') }}</label>
                        <div class="form-grid-2">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:9px">{{ __('admin_courses.preferred_days') }}</label>
                                <div class="day-selector-grid" id="createDaySelector">
                                    <label class="day-chip"><input type="checkbox" value="Mon"><span>M</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Tue"><span>T</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Wed"><span>W</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Thu"><span>T</span></label>
                                    <label class="day-chip"><input type="checkbox" value="Fri"><span>F</span></label>
                                    <label class="day-chip weekend"><input type="checkbox"
                                            value="Sat"><span>S</span></label>
                                    <label class="day-chip weekend"><input type="checkbox"
                                            value="Sun"><span>S</span></label>
                                </div>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                <div class="form-group" style="margin-bottom:0">
                                    <label class="form-label" style="font-size:9px">{{ __('admin_courses.start_time') }}</label>
                                    <input name="time_start" class="form-input" type="time" value="08:00">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label class="form-label" style="font-size:9px">{{ __('admin_courses.end_time') }}</label>
                                    <input name="time_end" class="form-input" type="time" value="09:30">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">{{ __('admin_courses.initial_operational_status') }}</label>
                        <select name="status" class="form-input">
                            <option value="active">{{ __('admin_courses.active') }}</option>
                            <option value="waiting">{{ __('admin_courses.waiting') }}</option>
                            <option value="ready">{{ __('admin_courses.ready') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('createModal')" class="btn-secondary">{{ __('admin_courses.cancel') }}</button>
                    <button type="submit" class="btn-primary">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin_courses.create_entry') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ GENERATE CALENDAR MODAL ═══ --}}
    {{-- ═══ ENROLL MODAL ═══ --}}
    <div id="enrollModal" class="modal-overlay">
        <div class="modal-box" style="max-width:600px; border-radius:24px; overflow:hidden;">
            <div class="modal-head"
                style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:42px;height:42px;border-radius:14px;background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;box-shadow: 0 4px 15px color-mix(in srgb, var(--accent) 13%, transparent)">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <div id="enrollModalTitle" class="modal-title"
                            style="font-weight: 800; font-size: 17px; letter-spacing: -0.02em;">{{ __('admin_courses.manage_enrollment') }}</div>
                        <div id="enrollModalSubtitle"
                            style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:0.02em">
                            {{ __('admin_courses.batch_transfer_students') }}</div>
                    </div>
                </div>
                <button onclick="closeModal('enrollModal')" class="modal-close"
                    style="background:var(--surface3); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:0">
                <div
                    style="padding:18px 28px; background:var(--surface3); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:10; display:flex; gap:12px; align-items:center;">
                    <div style="position:relative; flex:1">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--muted)"
                            style="position:absolute; left:12px; top:50%; transform:translateY(-50%)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                        </svg>
                        <input id="studentSearch" type="text" class="search-input"
                            placeholder="{{ __('admin_courses.find_student') }}" onkeyup="filterEnrollList()"
                            style="padding-left:36px; height:42px; background:var(--surface2); border:1px solid var(--border); border-radius:12px; font-size:12px;">
                    </div>
                    <div id="enrollCount"
                        style="font-family:var(--font-mono); font-size:10px; color:var(--text2); background:var(--surface2); padding:11px 16px; border-radius:12px; border:1px solid var(--border); font-weight:800">
                        {{ count($students) }} {{ __('admin_courses.total') }}
                    </div>
                </div>
                <div id="studentListContainer" class="enroll-list" style="max-height:480px; padding: 20px 28px;">
                    @php
                        $sortedStudents = $students->sortBy(function ($s) {
                            return $s->user->name ?? '';
                        });
                    @endphp
                    @foreach($sortedStudents as $s)
                        <div class="enroll-row" data-id="{{ $s->id }}" data-name="{{ strtolower($s->user->name) }}"
                            data-code="{{ strtolower($s->student_code) }}" data-class="{{ $s->class_id ?? 0 }}"
                            data-group="{{ $s->group_id ?? 0 }}" @php
                                $m = ($s->major instanceof \App\Models\Major) ? $s->major : ($s->group->major ?? null);
                                $d = ($m instanceof \App\Models\Major) ? ($m->department ?? null) : null;
                                $yr = $s->group->year_level ?? 1;
                            @endphp data-major="{{ strtolower($m->name ?? "") }}"
                            data-dept="{{ strtolower($d->name ?? "") }}" data-year="{{ $yr }}">
                            <div class="enroll-info" onclick="openStudentRecordModal({{ $s->id }})" style="cursor:pointer"
                                onmouseover="this.querySelector('.enroll-name').style.color='var(--accent)'"
                                onmouseout="this.querySelector('.enroll-name').style.color='var(--text)'">
                                @php
                                    $allClr = ['#2563EB', '#22C55E', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444'];
                                    $clr = $allClr[$s->id % count($allClr)];
                                @endphp
                                <div class="enroll-avatar" style="background:{{ $clr }}">
                                    {{ strtoupper(substr($s->user->name, 0, 1)) }}
                                </div>
                                <div class="enroll-details">
                                    <div class="enroll-name">{{ $s->user->name }}</div>
                                    <div class="enroll-meta" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap">
                                        <span class="enroll-code"
                                            style="color:var(--text2); font-weight:700">{{ $s->student_code }}</span>
                                        <span style="opacity:0.3">/</span>
                                        @php
                                            // Prioritize the Major relationship object over the legacy 'major' string column
                                            $major = ($s->major instanceof \App\Models\Major) ? $s->major : ($s->group->major ?? null);
                                            $dept = ($major instanceof \App\Models\Major) ? ($major->department ?? null) : null;
                                            $yr = $s->group->year_level ?? 1;
                                            $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'][$yr % 10];
                                            if ($yr % 100 >= 11 && $yr % 100 <= 13)
                                                $suffix = 'th';
                                        @endphp
                                        <span style="color:var(--muted); font-size:9px; letter-spacing:0.05em">DEPT:</span>
                                        <span
                                            style="color:var(--text); font-weight:600; font-size:10px">{{ strtoupper($dept->name ?? 'N/A') }}</span>
                                        <span style="opacity:0.3">•</span>
                                        <span style="color:var(--muted); font-size:9px; letter-spacing:0.05em">MAJOR:</span>
                                        <span class="enroll-major"
                                            style="color:var(--accent); font-weight:700; font-size:10px">{{ strtoupper($major->name ?? 'N/A') }}</span>
                                        <span style="opacity:0.3">•</span>
                                        <span
                                            style="font-weight:800; color:var(--text2); font-size:10px">{{ $yr }}{{ strtoupper($suffix) }}
                                            YEAR</span>
                                    </div>
                                </div>
                            </div>
                            <button class="enroll-btn" onclick="toggleEnroll({{ $s->id }})">
                                <span class="lbl-add">ENROLL</span>
                                <span class="lbl-rem">REMOVE</span>
                                <span class="lbl-oth">ENROLL</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer"
                style="background:var(--surface2); padding: 18px 28px; justify-content: space-between;">
                <div style="display:flex; align-items:center; gap:8px">
                    <div
                        style="width:6px; height:6px; border-radius:50%; background:var(--green); box-shadow:0 0 8px var(--green)">
                    </div>
                    <span
                        style="font-family:var(--font-mono); font-size:10px; color:var(--muted); font-weight:600; letter-spacing:0.02em">ATOMIC
                        AUTO-SYNC ACTIVE</span>
                </div>
                <button onclick="closeModal('enrollModal')" class="btn-primary"
                    style="height:40px; padding:0 24px; font-weight:800; border-radius:12px; font-size:11px; letter-spacing:0.02em">EXIT
                    MANAGEMENT</button>
            </div>
        </div>
    </div>

    <style>
        /* Enrollment Management Premium Styles */
        .enroll-container {
            display: flex;
            flex-direction: column;
            background: var(--surface2);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .enroll-list {
            max-height: 450px;
            overflow-y: auto;
            padding: 12px;
        }

        .enroll-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 8px;
            background: var(--surface3);
            border: 1px solid var(--border);
            transition: all .2s;
        }

        .enroll-row:hover {
            transform: translateX(4px);
            border-color: color-mix(in srgb, var(--accent) 27%, transparent);
            background: var(--surface2);
        }

        .enroll-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .enroll-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .enroll-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .enroll-name {
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .enroll-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-mono);
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 0.02em;
        }

        .enroll-major {
            color: var(--accent);
            font-weight: 700;
            text-transform: uppercase;
        }

        .enroll-btn {
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .05em;
            cursor: pointer;
            transition: all .2s;
            min-width: 100px;
        }

        /* Status-specific button colors */
        .enroll-btn {
            background: var(--surface2);
            color: var(--text2);
            border: 1px solid var(--border);
        }

        .enroll-btn:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .enroll-btn.active {
            background: color-mix(in srgb, var(--red) 8%, transparent);
            color: var(--red);
            border: 1px solid color-mix(in srgb, var(--red) 20%, transparent);
        }

        .enroll-btn.active:hover {
            background: var(--red);
            color: #fff;
            border-color: var(--red);
        }

        .enroll-btn.other {
            background: color-mix(in srgb, var(--amber) 8%, transparent);
            color: var(--amber);
            border: 1px solid color-mix(in srgb, var(--amber) 20%, transparent);
        }

        .enroll-btn.other:hover {
            background: var(--amber);
            color: #fff;
            border-color: var(--amber);
        }

        .enroll-btn .lbl-rem,
        .enroll-btn .lbl-oth {
            display: none
        }

        .enroll-btn.active .lbl-add,
        .enroll-btn.active .lbl-oth {
            display: none
        }

        .enroll-btn.active .lbl-rem {
            display: inline
        }

        .enroll-btn.other .lbl-add,
        .enroll-btn.other .lbl-rem {
            display: none
        }

        .enroll-btn.other .lbl-oth {
            display: inline
        }

        .enroll-btn.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .enroll-btn.loading .lbl-add,
        .enroll-btn.loading .lbl-rem,
        .enroll-btn.loading .lbl-oth {
            display: none !important
        }

        .enroll-btn.loading::after {
            content: "";
            width: 10px;
            height: 10px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: inline-block;
            vertical-align: middle;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(360deg)
            }
        }

        @media (max-width: 640px) {
            .enroll-row {
                padding: 12px;
            }

            .enroll-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .enroll-meta {
                flex-wrap: wrap;
            }
        }

        .day-selector-grid {
            display: flex;
            gap: 4px;
            margin-top: 4px;
        }

        .day-chip {
            flex: 1;
            cursor: pointer;
        }

        .day-chip input {
            display: none;
        }

        .day-chip span {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 32px;
            border-radius: 10px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 800;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .day-chip input:checked+span {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 27%, transparent);
        }

        .day-chip.weekend span {
            color: color-mix(in srgb, var(--red) 53%, transparent);
        }

        .day-chip.weekend input:checked+span {
            background: var(--red);
            color: #fff;
            border-color: var(--red);
        }
    </style>

    {{-- Page Header --}}
    <div class="page-header"
        style="margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <div class="breadcrumb" style="margin-bottom: 15px;">
                <span
                    style="font-family: var(--font-mono); font-size: 10px; font-weight: 800; color: var(--muted); letter-spacing: 0.1em;">{{ __('admin_courses.breadcrumb_section') }}</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current" style="color: var(--accent); font-weight: 800;">{{ __('admin_courses.breadcrumb_current') }}</span>
            </div>
            <h1 class="page-title-new">{{ __('admin_courses.title') }}</h1>
            <p class="page-subtitle-new">{{ __('admin_courses.subtitle') }}</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button class="btn-secondary" onclick="window.location.href='{{ route('admin.export.courses') }}'"
                style="border-radius: 14px; height: 46px; padding: 0 20px; font-weight: 700; font-size: 11px; gap: 10px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ __('admin_courses.export_system_data') }}
            </button>
            <button class="btn-primary" onclick="openModal('createModal')"
                style="border-radius: 14px; height: 46px; padding: 0 24px; font-weight: 800; font-size: 11px; background: #364ed9 ; box-shadow: 0 8px 20px color-mix(in srgb, var(--accent) 20%, transparent); gap: 10px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('admin_courses.new_catalog_entry') }}
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px;">
        <div class="premium-card" style="padding: 24px; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: color-mix(in srgb, var(--accent) 3%, transparent); border-radius: 50%; blur: 40px;">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="stat-label-new">{{ __('admin_courses.total_catalogue') }}</div>
                    <div class="stat-value-new">{{ $classes->count() }}</div>
                </div>
                <div class="glow-icon" style="background: color-mix(in srgb, var(--accent) 8%, transparent); color: var(--accent);">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 800; color: var(--green);">↑
                    2.4%</span>
                <span style="font-size: 9px; color: var(--muted2); font-weight: 600;">{{ __('admin_courses.from_last_period') }}</span>
            </div>
        </div>

        <div class="premium-card" style="padding: 24px; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: color-mix(in srgb, var(--green) 3%, transparent); border-radius: 50%; blur: 40px;">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="stat-label-new">{{ __('admin_courses.active_roster') }}</div>
                    <div class="stat-value-new">{{ $classes->pluck('teacher_id')->unique()->count() }}</div>
                </div>
                <div class="glow-icon" style="background: color-mix(in srgb, var(--green) 8%, transparent); color: var(--green);">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                <div
                    style="width: 6px; height: 6px; border-radius: 50%; background: var(--green); box-shadow: 0 0 8px var(--green);">
                </div>
                <span
                    style="font-family: var(--font-mono); font-size: 10px; font-weight: 800; color: var(--green); letter-spacing: 0.05em;">{{ __('admin_courses.system_healthy') }}</span>
            </div>
        </div>

        <div class="premium-card" style="padding: 24px; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: color-mix(in srgb, var(--amber) 3%, transparent); border-radius: 50%; blur: 40px;">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="stat-label-new">{{ __('admin_courses.resource_load') }}</div>
                    <div class="stat-value-new">84<span style="font-size: 18px; opacity: 0.5">%</span></div>
                </div>
                <div class="glow-icon" style="background: color-mix(in srgb, var(--amber) 8%, transparent); color: var(--amber);">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                <div
                    style="width: 6px; height: 6px; border-radius: 50%; background: var(--amber); box-shadow: 0 0 8px var(--amber);">
                </div>
                <span
                    style="font-family: var(--font-mono); font-size: 10px; font-weight: 800; color: var(--amber); letter-spacing: 0.05em;">{{ __('admin_courses.near_capacity') }}</span>
            </div>
        </div>

        <div class="premium-card" style="padding: 24px; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: color-mix(in srgb, var(--violet) 3%, transparent); border-radius: 50%; blur: 40px;">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="stat-label-new">{{ __('admin_courses.hub_uptime') }}</div>
                    <div class="stat-value-new">99.9<span style="font-size: 18px; opacity: 0.5">%</span></div>
                </div>
                <div class="glow-icon" style="background: color-mix(in srgb, var(--violet) 8%, transparent); color: var(--violet);">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-7.618 3.04L4 7.424l1.393 11.14c.245 1.956 1.832 3.436 3.805 3.436h5.604c1.973 0 3.56-1.48 3.805-3.436L20 7.424l-1.382-3.44z" />
                    </svg>
                </div>
            </div>
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                <div
                    style="width: 6px; height: 6px; border-radius: 50%; background: var(--green); box-shadow: 0 0 8px var(--green);">
                </div>
                <span
                    style="font-family: var(--font-mono); font-size: 10px; font-weight: 800; color: var(--green); letter-spacing: 0.05em;">{{ __('admin_courses.operational') }}</span>
            </div>
        </div>
    </div>

    {{-- Main two-column grid --}}
    <div class="main-grid">

        {{-- LEFT: Catalog Table --}}
        <div class="panel">

            {{-- Bulk Actions Toolbar --}}
            <div id="bulkActionsToolbar" style="display:none; align-items:center; justify-content:space-between; background:var(--surface3); padding:12px 20px; border-radius:12px; margin: 15px; border:1px solid color-mix(in srgb, var(--accent) 20%, transparent); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="display:flex; align-items:center; gap:12px">
                    <div style="width:12px; height:12px; border-radius:50%; background:var(--red); box-shadow:0 0 10px var(--red)"></div>
                    <span id="selectedClassesCount" style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--text); letter-spacing:0.05em">{{ __('admin_courses.classes_selected', ['count' => 0]) }}</span>
                </div>
                <button onclick="confirmBulkDeleteClasses()" class="btn-primary" style="background:var(--red); border:none; height:34px; font-size:10px; padding:0 20px; font-weight:900; box-shadow:0 4px 12px color-mix(in srgb, var(--red) 27%, transparent)">
                    {{ __('admin_courses.confirm_bulk_delete') }}
                </button>
            </div>

            {{-- Toolbar --}}
            <div class="catalog-toolbar" style="padding: 16px 20px; gap: 15px;">
                <div style="display:flex;align-items:center;gap:10px; flex: 1;">
                    <div
                        style="width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 10px var(--accent)">
                    </div>
                    <span
                        style="font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.12em;color:var(--text2)">{{ __('admin_courses.academic_catalog') }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="search-wrap"
                        style="width: 250px; height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; padding: 0 12px;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--muted2)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                        </svg>
                        <input id="searchInput" name="search" value="{{ request('search') }}" class="search-input"
                            type="text" placeholder="{{ __('admin_courses.search_catalog') }}" onkeyup="filterTable(event)"
                            style="border: none; background: transparent; color: var(--text); font-size: 11px; padding-left: 10px; width: 100%; outline: none;">
                    </div>

                    <select class="filter-select" onchange="filterByDept(this.value)"
                        style="height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; color: var(--text2); font-family: var(--font-mono); font-size: 9px; padding: 0 35px 0 15px; cursor: pointer;">
                        <option value="">{{ __('admin_courses.all_departments') }}</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('dept') == $d->id ? 'selected' : '' }}>
                                {{ strtoupper($d->name) }}
                            </option>
                        @endforeach
                    </select>

                    <select class="filter-select" onchange="filterByStatus(this.value)"
                        style="height: 36px; background: var(--surface3); border: 1px solid var(--border); border-radius: 10px; color: var(--text2); font-family: var(--font-mono); font-size: 9px; padding: 0 35px 0 15px; cursor: pointer;">
                        <option value="">{{ __('admin_courses.all_status') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin_courses.active') }}</option>
                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>{{ __('admin_courses.waiting') }}</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>{{ __('admin_courses.ready') }}</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ __('admin_courses.archived') }}</option>
                    </select>

                    <div
                        style="height: 36px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; padding: 0 15px;">
                        <span
                            style="font-family: var(--font-mono); font-size: 9px; color: var(--muted2); letter-spacing: .05em;">
                            <span id="rowCount"
                                style="color: var(--accent); font-weight: 700;">{{ $classes->count() }}</span> {{ __('admin_courses.entries') }}
                        </span>
                    </div>


                    <button class="btn-primary" onclick="openModal('createModal')" title="{{ __('admin_courses.add_entry') }}"
                        style="width: 36px; height: 36px; border-radius: 10px; padding: 0; display: flex; align-items: center; justify-content: center; transform: scale(1); transition: transform .2s;"
                        onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <table class="att-table" id="classTable" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead class="table-header-premium">
                    <tr>
                        <th style="padding-left:25px; width:45px; border-top-left-radius: 12px;">
                            <input type="checkbox" id="selectAllClasses" onchange="toggleSelectAllClasses(this.checked)" style="accent-color:var(--accent); width:16px; height:16px; cursor:pointer">
                        </th>
                        <th>{{ __('admin_courses.table_course_identity') }}</th>
                        <th>{{ __('admin_courses.table_faculty') }}</th>
                        <th>{{ __('admin_courses.table_logistics') }}</th>
                        <th style="width:80px">{{ __('admin_courses.table_students') }}</th>
                        <th style="width:160px">{{ __('admin_courses.table_sessions') }}</th>
                        <th style="width:100px">{{ __('admin_courses.table_status') }}</th>
                        <th style="text-align:right; padding-right:25px; border-top-right-radius: 12px;">{{ __('admin_courses.table_control') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($groupedClasses as $dept => $majors)
                        {{-- Department Header --}}
                        <tr style="background:var(--surface3);">
                            <td colspan="8" style="padding:15px 25px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div
                                        style="width:10px; height:10px; border-radius:50%; background:var(--accent); box-shadow:0 0 10px var(--accent)">
                                    </div>
                                    <span
                                        style="font-family:var(--font-display); font-size:16px; font-weight:900; color:var(--text); text-transform:uppercase; letter-spacing:0.02em;">{{ $dept }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach($majors as $major => $years)
                            {{-- Major Header --}}
                            <tr style="background:var(--surface2);">
                                <td colspan="8" style="padding:10px 40px; border-bottom:1px solid var(--border);">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span
                                            style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--accent); text-transform:uppercase;">{{ $major }}</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach($years as $year => $groups)
                                @foreach($groups as $groupName => $classes)
                                    {{-- Group Subheader --}}
                                    <tr style="background:var(--surface1);">
                                        <td colspan="8" style="padding:8px 55px; border-bottom:1px solid var(--border);">
                                            <div style="display:flex; align-items:center; gap:15px; opacity:0.8;">
                                                <span
                                                    style="font-family:var(--font-mono); font-size:10px; font-weight:700; color:var(--muted); background:var(--surface3); padding:2px 8px; border-radius:4px;">{{ $year }}</span>
                                                <span
                                                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--text2);">{{ $groupName }}</span>
                                                <div
                                                    style="flex:1; height:1px; background:linear-gradient(to right, var(--border), transparent);">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($classes as $class)
                                        <tr data-id="{{ $class->id }}" data-status="{{ $class->status ?? 'active' }}" class="fade-up">
                                            <td style="padding-left:25px; width:45px">
                                                @php $isReady = strtolower($class->status ?? '') === 'ready'; @endphp
                                                @if($isReady)
                                                    <input type="checkbox" class="class-checkbox" data-id="{{ $class->id }}" onchange="updateBulkDeleteUI()" style="accent-color:var(--accent); width:16px; height:16px; cursor:pointer">
                                                @else
                                                    <div style="width:16px; height:16px; border:1px solid var(--border); border-radius:4px; opacity:0.2" title="{{ __('admin_courses.only_ready_selectable') }}"></div>
                                                @endif
                                            </td>
                                            {{-- Subject --}}
                                            <td style="padding-left:65px; width:250px">
                                                <div class="subject-cell">
                                                    @php
                                                        $subName = $class->subject->name ?? __('admin_courses.unknown');
                                                        $initials = strtoupper(substr($subName, 0, 1));
                                                        $allClr = ['#2563EB', '#22C55E', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444'];
                                                        $clr = $allClr[$class->subject_id % count($allClr)];
                                                    @endphp
                                                    <div class="subject-avatar"
                                                        style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}33">
                                                        {{ $initials }}
                                                    </div>
                                                    <div>
                                                        <div class="subject-name">{{ $subName }}</div>
                                                        <div class="subject-id">#{{ str_pad($class->id, 4, '0', STR_PAD_LEFT) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Instructor --}}
                                            <td>
                                                @if($class->teacher && $class->teacher->user)
                                                    <div class="instructor-cell">
                                                        <div class="instructor-dot">{{ strtoupper(substr($class->teacher->user->name, 0, 1)) }}
                                                        </div>
                                                        <span class="instructor-name">{{ $class->teacher->user->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="instructor-empty">— {{ __('admin_courses.unassigned') }} —</span>
                                                @endif
                                            </td>
                                            {{-- Room & Schedule --}}
                                            <td>
                                                <div style="display:flex; flex-direction:column; gap:5px">
                                                    <span class="room-badge">{{ __('admin_courses.room_short') }} {{ $class->room_number ?? __('admin_courses.tbd') }}</span>
                                                    @if($class->schedule)
                                                        @php
                                                            $sched = explode(' ', $class->schedule);
                                                            $days = $sched[0] ?? 'TBD';
                                                            $times = $sched[1] ?? '';
                                                        @endphp
                                                        <div style="display:flex; align-items:center; gap:6px">
                                                            <span
                                                                style="font-family:var(--font-mono); font-size:9px; background:color-mix(in srgb, var(--accent) 7%, transparent); color:var(--accent); padding:1px 5px; border-radius:4px; font-weight:800">{{ strtoupper($days) }}</span>
                                                            <span
                                                                style="font-family:var(--font-mono); font-size:9px; color:var(--muted); font-weight:600">{{ str_replace(['(', ')'], '', $times) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            {{-- Enrolled --}}
                                            <td><span
                                                    style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text2)">{{ $class->all_students->count() }}</span>
                                            </td>
                                            {{-- Workload --}}
                                            <td>
                                                <div style="display:flex; flex-direction:column; gap:5px">
                                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                                        <span
                                                            style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted)">{{ __('admin_courses.progress') }}</span>
                                                        <span
                                                            style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--accent)">{{ $class->sessions->whereIn('status', ['completed'])->count() }}/{{ $class->sessions->count() }}</span>
                                                    </div>
                                                    <div
                                                        style="width:100%; height:4px; background:var(--surface3); border-radius:2px; overflow:hidden;">
                                                        <div
                                                            style="width:{{ $class->sessions->count() > 0 ? min(100, round(($class->sessions->whereIn('status', ['completed'])->count() / $class->sessions->count()) * 100)) : 0 }}%; height:100%; background:var(--accent); box-shadow:0 0 10px color-mix(in srgb, var(--accent) 40%, transparent);">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Status --}}
                                            <td>
                                                @php $st = strtolower($class->status ?? 'active'); @endphp
                                                @if($st === 'active')
                                                    <span class="status-tag tag-active">{{ __('admin_courses.active') }}</span>
                                                @elseif($st === 'archived')
                                                    <span class="status-tag"
                                                        style="background:var(--surface3); color:var(--muted); border:1px solid var(--border);">{{ __('admin_courses.archived') }}</span>
                                                @else
                                                    <span class="status-tag tag-ready">{{ strtoupper($st) }}</span>
                                                @endif
                                            </td>
                                            {{-- Actions --}}
                                            <td style="text-align:right; padding-right:25px">
                                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                                                    <button class="action-btn btn-view" title="{{ __('admin_courses.view_detail') }}"
                                                        onclick="openViewModal({{ $class->id }}, '{{ $class->subject_id }}', '{{ $class->teacher_id }}', '{{ addslashes($class->room_number ?? '') }}', '{{ addslashes($class->schedule ?? '') }}', '{{ $class->status ?? 'active' }}', '{{ $class->groups->pluck('id')->join(',') }}')">
                                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>

                                                    <button class="action-btn btn-edit" title="{{ __('admin_courses.edit_metadata') }}"
                                                        onclick="openEditModal({{ $class->id }}, '{{ $class->subject_id }}', '{{ $class->teacher_id }}', '{{ addslashes($class->room_number ?? '') }}', '{{ addslashes($class->schedule ?? '') }}', '{{ $class->status ?? 'active' }}', '{{ $class->groups->pluck('id')->join(',') }}')">
                                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>

                                                    <div class="action-dropdown">
                                                        <button class="more-btn" onclick="toggleActionMenu(event, this)" title="{{ __('admin_courses.more_options') }}">
                                                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                                    d="M5 12h.01M12 12h.01M19 12h.01" />
                                                            </svg>
                                                        </button>
                                                        <div class="action-dropdown-menu">
                                                            <button class="dropdown-item"
                                                                onclick="openEnrollModal({{ $class->id }}, '{{ addslashes($subName) }}', '{{ $class->groups->pluck('id')->join(',') }}')">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                                </svg>
                                                                {{ __('admin_courses.enroll_students') }}
                                                            </button>
                                                            <button class="dropdown-item"
                                                                onclick="openSessionsModal({{ $class->id }}, '{{ addslashes($class->subject->name ?? __('admin_courses.unknown')) }}')">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {{ __('admin_courses.view_sessions') }}
                                                            </button>
                                                            <button class="dropdown-item"
                                                                onclick="openCourseSemesterModal({{ $class->id }}, '{{ addslashes($class->subject->name ?? __('admin_courses.unknown')) }}', '{{ addslashes($class->schedule ?? '') }}')">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ __('admin_courses.semesters') }}
                                                            </button>
                                                            <div style="height:1px; background:var(--border); margin:4px 8px;"></div>
                                                            <a href="{{ route('admin.courses.pre-end', $class->id) }}" target="_blank"
                                                                class="dropdown-item"
                                                                style="color:var(--amber); text-decoration:none; display:flex; align-items:center; gap:10px;">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                                </svg>
                                                                {{ __('admin_courses.pre_end_schedule') }}
                                                            </a>
                                                            <button class="dropdown-item text-red" onclick="openDeleteModal({{ $class->id }})">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                                {{ __('admin_courses.delete_class') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state" style="padding: 60px 0; text-align:center;">
                                    <div class="empty-title"
                                        style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text)">
                                        {{ __('admin_courses.catalog_empty') }}</div>
                                    <div class="empty-desc"
                                        style="font-family:var(--font-mono); font-size:10px; color:var(--muted); max-width:260px; margin:0 auto">
                                        {{ __('admin_courses.no_classes_found') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- RIGHT: Sidebar panel --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- 🟢 GLOBAL SESSION SKIP (New Feature) --}}
            <div class="side-panel"
                style="border: 1px solid color-mix(in srgb, var(--red) 20%, transparent); background: linear-gradient(180deg, var(--surface2), var(--surface3));">
                <div class="side-panel-head" style="color: var(--red); font-weight: 900;">
                    <span
                        style="width:8px;height:8px;border-radius:2px;background:var(--red);display:inline-block;box-shadow:0 0 10px var(--red)"></span>
                    {{ __('admin_courses.global_session_skip') }}
                </div>
                <div style="padding: 20px;">
                    <p
                        style="font-size:10px; color:var(--muted); line-height:1.5; margin-bottom:18px; font-family:var(--font-mono)">
                        {{ __('admin_courses.global_skip_desc') }}
                    </p>

                    <button onclick="openModal('globalSkipModal')" class="btn-primary"
                        style="width:100%; height:44px; background:linear-gradient(135deg, var(--red), #f43f5e); border:none; box-shadow:0 8px 20px rgba(244, 63, 94, 0.2); font-weight:800; letter-spacing:0.05em;">
                        {{ __('admin_courses.configure_global_skip') }}
                    </button>

                    <div
                        style="margin-top:15px; display:flex; align-items:center; gap:8px; padding:10px; background:color-mix(in srgb, var(--red) 3%, transparent); border-radius:10px; border:1px dashed color-mix(in srgb, var(--red) 20%, transparent)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2">
                            <path
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span
                            style="font-size:8px; font-family:var(--font-mono); color:var(--red); font-weight:700; text-transform:uppercase">{{ __('admin_courses.irreversible_action') }}</span>
                    </div>
                </div>
            </div>

            {{-- QUICK OPERATIONS (Standardized) --}}
            <div class="side-panel">
                <div class="side-panel-head">
                    <span
                        style="width:6px;height:6px;border-radius:50%;background:var(--accent);display:inline-block"></span>
                    {{ __('admin_courses.quick_operations') }}
                </div>
                <div style="padding:16px; display:flex; flex-direction:column; gap:10px">
                    <button onclick="window.location.href='/api/admin/classes/export'" class="btn-secondary"
                        style="width:100%; display:flex; justify-content:space-between; align-items:center; height:42px; padding:0 16px; font-size:10px; font-weight:700; background:var(--surface3)">
                        <span>{{ __('admin_courses.export_csv') }}</span>
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2" />
                        </svg>
                    </button>

                    <button id="syncCacheBtn" onclick="runCacheClear()" class="btn-secondary"
                        style="width:100%; display:flex; justify-content:space-between; align-items:center; height:42px; padding:0 16px; font-size:10px; font-weight:700; background:var(--surface3)">
                        <span id="syncCacheBtnLabel">{{ __('admin_courses.sync_cache') }}</span>
                        <svg id="syncCacheIcon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                stroke-width="2" />
                        </svg>
                    </button>

                    {{-- Hub Integrity Integrated --}}
                    <div
                        style="margin-top:10px; padding:15px; border-radius:15px; background:color-mix(in srgb, var(--accent) 3%, transparent); border:1px solid color-mix(in srgb, var(--accent) 8%, transparent); display:flex; align-items:center; justify-content:space-between">
                        <div>
                            <div
                                style="font-family:var(--font-mono); font-size:8px; color:var(--muted2); letter-spacing:.05em; margin-bottom:4px">
                                {{ __('admin_courses.hub_integrity') }}</div>
                            <div
                                style="font-size:18px; font-weight:900; color:var(--accent); font-family:var(--font-display)">
                                A+</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-family:var(--font-mono); font-size:8px; color:var(--green); font-weight:800">
                                {{ __('admin_courses.optimized') }}</div>
                            <div style="font-size:8px; color:var(--muted); line-height:1.2">{{ __('admin_courses.registry_data') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 📊 DATABASE ACTIVITY LOG (Replaces Schedule Set) --}}
            <div class="side-panel">
                <div class="side-panel-head" style="display:flex; justify-content:space-between; align-items:center">
                    <div style="display:flex; align-items:center; gap:8px">
                        <span
                            style="width:8px;height:8px;border-radius:50%;background:var(--green);animation:blink 1.5s infinite;display:inline-block;box-shadow:0 0 10px var(--green)"></span>
                        {{ __('admin_courses.database_activity') }}
                    </div>
                    <span
                        style="font-family:var(--font-mono); font-size:8px; color:var(--muted); letter-spacing:0.1em">{{ __('admin_courses.live') }}</span>
                </div>
                <div id="dbActivityLogs" class="db-entries" style="padding:10px 16px">
                    @forelse($recentActivities ?? [] as $act)
                        <div class="db-entry"
                            style="padding:12px 0; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; gap:12px">
                            @php
                                $color = $act['action'] === 'DELETE' ? 'var(--red)' :
                                    ($act['action'] === 'UPDATE' ? 'var(--amber)' :
                                        ($act['action'] === 'INSERT' ? 'var(--green)' : 'var(--accent)'));
                            @endphp
                            <div
                                style="margin-top:4px; width:6px; height:6px; border-radius:50%; background:{{ $color }}; box-shadow:0 0 10px {{ $color }}">
                            </div>
                            <div style="flex:1">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                                    <span
                                        style="font-family:var(--font-mono); font-size:10px; font-weight:900; color:{{ $color }}; letter-spacing:0.05em">{{ $act['action'] }}</span>
                                    <span
                                        style="font-family:var(--font-mono); font-size:9px; color:var(--muted)">{{ $act['time'] }}</span>
                                </div>
                                <div style="font-family:var(--font-mono); font-size:10px; color:var(--text2)">
                                    {!! $act['target'] !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="dbActivityPlaceholder"
                            style="padding:20px; text-align:center; color:var(--muted); font-size:9px; font-family:var(--font-mono)">
                            {{ __('admin_courses.no_recent_actions') }}
                        </div>
                    @endforelse
                </div>
            </div>

        </div>{{-- end right column --}}
    </div>{{-- end main-grid --}}

    {{-- ═══ SEMESTER ASSIGNMENT MODAL ═══ --}}
    <div id="courseSemesterModal" class="modal-overlay">
        <div class="modal-box" style="max-width:640px; border-radius: 20px; overflow: hidden;">
            <div class="modal-head"
                style="padding: 24px 24px 16px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg, color-mix(in srgb, var(--violet) 13%, transparent), color-mix(in srgb, var(--violet) 3%, transparent));color:var(--violet);display:flex;align-items:center;justify-content:center;box-shadow: 0 4px 12px rgba(0,0,0,0.1)">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="modal-title" style="font-size: 16px; font-weight: 800; letter-spacing: -0.01em;">
                            {{ __('admin_courses.semester_management') }}</div>
                        <div id="csmSubtitle"
                            style="font-family:var(--font-mono);font-size:10px;color:var(--accent);font-weight:700;letter-spacing:0.05em">
                            {{ __('admin_courses.loading_course') }}</div>
                    </div>
                </div>
                <button onclick="closeModal('courseSemesterModal')" class="modal-close"
                    style="background: var(--surface3); border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body" style="padding:24px; max-height: 70vh; overflow-y: auto;">
                {{-- Preview Timeline Section --}}
                <div id="csmPreview" class="csm-timeline-preview" style="display:none">
                    {{-- Preview content injected via JS --}}
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <div style="height: 1px; flex: 1; background: linear-gradient(90deg, var(--accent), transparent);">
                    </div>
                    <div
                        style="font-family:var(--font-mono);font-size:9px;letter-spacing:.12em;color:var(--accent);font-weight:800">
                        {{ __('admin_courses.assign_new_semester') }}</div>
                    <div style="height: 1px; flex: 1; background: linear-gradient(270deg, var(--accent), transparent);">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_courses.academic_year') }} <span class="req">*</span></label>
                        <input id="csmYear" class="form-input" type="text" placeholder="2025-2026"
                            value="{{ now()->year }}-{{ now()->year + 1 }}" style="background:var(--surface3)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_courses.semester') }} <span class="req">*</span></label>
                        <select id="csmSemester" class="form-input" style="background:var(--surface3)">
                            <option value="1">{{ __('admin_courses.semester_1') }}</option>
                            <option value="2">{{ __('admin_courses.semester_2') }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px">
                    <label class="form-label" style="font-size:9px">{{ __('admin_courses.preferred_days') }} <span class="req">*</span></label>
                    <div class="day-selector-grid" id="csmDaySelector">
                        <label class="day-chip"><input type="checkbox" value="Mon"><span>M</span></label>
                        <label class="day-chip"><input type="checkbox" value="Tue"><span>T</span></label>
                        <label class="day-chip"><input type="checkbox" value="Wed"><span>W</span></label>
                        <label class="day-chip"><input type="checkbox" value="Thu"><span>T</span></label>
                        <label class="day-chip"><input type="checkbox" value="Fri"><span>F</span></label>
                        <label class="day-chip weekend"><input type="checkbox" value="Sat"><span>S</span></label>
                        <label class="day-chip weekend"><input type="checkbox" value="Sun"><span>S</span></label>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_courses.academic_start') }} <span class="req">*</span></label>
                        <div style="position:relative">
                            <input id="csmStart" class="form-input" type="date" oninput="csmPreview()"
                                style="background:var(--surface3); padding-right: 12px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('admin_courses.break_holiday') }} <span
                                style="color:var(--muted); font-size: 8px;">({{ __('admin_courses.optional') }})</span></label>
                        <input id="csmHoliday" class="form-input" type="date" oninput="csmPreview()"
                            style="background:var(--surface3)">
                    </div>
                </div>

                <div class="form-grid-2" style="margin-top: -5px">
                    <div class="form-group">
                        <label class="form-label" style="display:flex; justify-content:space-between">
                            {{ __('admin_courses.session_1') }} <span style="color:var(--muted); font-size: 8px;">({{ __('admin_courses.start_end') }})</span>
                        </label>
                        <div style="display:flex; align-items:center; gap:8px">
                            <input id="csmTimeStart" class="form-input" type="time" value="08:00"
                                style="background:var(--surface3); font-size: 11px;">
                            <span style="font-size:10px; color:var(--muted); font-weight: 700;">{{ __('admin_courses.to') }}</span>
                            <input id="csmTimeEnd" class="form-input" type="time" value="09:30"
                                style="background:var(--surface3); font-size: 11px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display:flex; justify-content:space-between">
                            {{ __('admin_courses.session_2') }} <span style="color:var(--muted); font-size: 8px;">({{ __('admin_courses.optional') }})</span>
                        </label>
                        <div style="display:flex; align-items:center; gap:8px">
                            <input id="csmTimeStart2" class="form-input" type="time"
                                style="background:var(--surface3); font-size: 11px;">
                            <span style="font-size:10px; color:var(--muted); font-weight: 700;">{{ __('admin_courses.to') }}</span>
                            <input id="csmTimeEnd2" class="form-input" type="time"
                                style="background:var(--surface3); font-size: 11px;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('admin_courses.target_capacity') }} <span
                            style="color:var(--muted); font-size: 8px;">({{ __('admin_courses.sessions') }})</span></label>
                    <div style="position:relative">
                        <input id="csmCount" class="form-input" type="number" value="30" min="1" max="100"
                            style="background:var(--surface3); font-weight: 700; color: var(--accent); padding-right: 40px">
                        <div
                            style="position:absolute; right:15px; top:50%; transform:translateY(-50%); font-family:var(--font-mono); font-size:9px; color:var(--muted); pointer-events:none">
                            {{ __('admin_courses.sessions') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('admin_courses.internal_notes') }}</label>
                    <textarea id="csmNotes" class="form-input" rows="2"
                        placeholder="{{ __('admin_courses.notes_placeholder') }}"
                        style="background:var(--surface3); height: 60px; padding-top: 10px; resize: none;"></textarea>
                </div>

                {{-- Dynamic Assignments List Container --}}
                <div style="margin-top: 30px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px">
                        <div style="display:flex; align-items:center; gap:10px">
                            <div style="width:14px; height:2px; background:var(--accent); border-radius:2px"></div>
                            <span
                                style="font-family:var(--font-mono); font-size:10px; font-weight:800; letter-spacing:.12em; color:var(--text2)">{{ __('admin_courses.active_assignments') }}</span>
                        </div>
                        <span id="csmCountBadge"
                            style="font-family:var(--font-mono); font-size:9px; color:var(--accent); background:color-mix(in srgb, var(--accent) 9%, transparent); padding:4px 12px; border-radius:20px; font-weight:800; border:1px solid color-mix(in srgb, var(--accent) 13%, transparent)">{{ __('admin_courses.active_count', ['count' => 0]) }}</span>
                    </div>
                    <div id="csmItems">
                        {{-- Cards injected via csmLoad() --}}
                    </div>
                </div>

            </div>

            <style>
                /* Premium Semester Assignment Styles */
                .csm-card {
                    background: var(--surface2);
                    border: 1px solid var(--border);
                    border-radius: 18px;
                    padding: 22px 26px;
                    margin-bottom: 18px;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                }

                .csm-card:hover {
                    border-color: var(--border2);
                    transform: translateY(-2px);
                    box-shadow: var(--shadow-lg);
                }

                .csm-card-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 22px;
                }

                .csm-title-group {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .csm-accent-bar {
                    width: 3.5px;
                    height: 22px;
                    background: var(--violet);
                    border-radius: 4px;
                }

                .csm-title {
                    font-family: var(--font-mono);
                    font-size: 13px;
                    font-weight: 700;
                    color: var(--text);
                    letter-spacing: 0.02em;
                }

                .csm-badge {
                    font-family: var(--font-mono);
                    font-size: 9px;
                    font-weight: 800;
                    padding: 4px 10px;
                    border-radius: 20px;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                }

                .csm-badge.upcoming {
                    background: #EEEDFE;
                    color: #534AB7;
                }

                .csm-badge.active {
                    background: rgba(34, 197, 94, 0.15);
                    color: var(--green);
                }

                .csm-badge.completed {
                    background: var(--surface3);
                    color: var(--muted);
                }

                .csm-remove-btn {
                    font-family: var(--font-mono);
                    font-size: 10px;
                    font-weight: 800;
                    padding: 10px 22px;
                    border-radius: 12px;
                    border: 1.5px solid var(--border2);
                    background: transparent;
                    color: var(--text);
                    cursor: pointer;
                    transition: all 0.2s ease;
                    text-transform: uppercase;
                    letter-spacing: 0.1em;
                }

                .csm-remove-btn:hover {
                    background: var(--red);
                    color: #fff;
                    border-color: var(--red);
                }

                .csm-divider {
                    height: 1px;
                    background: var(--border);
                    margin: 0 -26px 22px;
                    opacity: 0.6;
                }

                .csm-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 24px;
                    margin-bottom: 24px;
                }

                .csm-label {
                    font-family: var(--font-mono);
                    font-size: 9px;
                    font-weight: 700;
                    color: var(--muted2);
                    text-transform: uppercase;
                    margin-bottom: 9px;
                    letter-spacing: 0.1em;
                }

                .csm-value {
                    font-family: var(--font-mono);
                    font-size: 14px;
                    font-weight: 800;
                    color: var(--text);
                    letter-spacing: 0.02em;
                }

                .csm-value.green {
                    color: var(--green);
                }

                .csm-value.muted {
                    font-size: 11px;
                    color: var(--muted);
                    font-weight: 500;
                }

                .csm-progress-section {
                    background: var(--bg);
                    border-radius: 14px;
                    padding: 18px 22px;
                    border: 1px solid var(--border);
                }

                .csm-progress-head {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 14px;
                }

                .csm-progress-track {
                    height: 6px;
                    background: var(--surface3);
                    border-radius: 10px;
                    overflow: visible;
                    position: relative;
                }

                .csm-progress-fill {
                    height: 100%;
                    background: var(--violet);
                    border-radius: 10px;
                    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                    box-shadow: 0 0 15px rgba(139, 92, 246, 0.3);
                }

                .csm-progress-fill::after {
                    content: '';
                    position: absolute;
                    right: -4px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 10px;
                    height: 10px;
                    background: #fff;
                    border: 2px solid var(--violet);
                    border-radius: 50%;
                    box-shadow: 0 0 10px var(--violet);
                    z-index: 2;
                }
            </style>
            <div class="modal-footer"
                style="padding: 16px 24px; background: var(--surface2); border-top: 1px solid var(--border);">
                <button type="button" onclick="closeModal('courseSemesterModal')" class="btn-secondary"
                    style="height: 42px; font-weight: 700;">CANCEL</button>
                <button type="button" onclick="csmSave()" class="btn-primary" id="csmSaveBtn"
                    style="height: 42px; flex: 1; font-weight: 700; gap: 8px; box-shadow: 0 4px 15px color-mix(in srgb, var(--accent) 27%, transparent);">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    INITIATE ASSIGNMENT
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ STUDENT RECORD MODAL ═══ --}}
    <div id="studentDetailModal" class="modal-overlay" style="z-index: 1100;">
        <div class="modal-box" style="max-width:500px; border-radius:28px; overflow-y:auto; max-height:90vh;">
            <div class="modal-body" style="padding:0; position:relative">
                {{-- Profile Header --}}
                <div style="background:#2c3faf; padding:40px 30px 60px; color:white; position:relative">
                    <button onclick="closeModal('studentDetailModal')"
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
                                <div style="display:inline-block; font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--green); background:color-mix(in srgb, var(--green) 9%, transparent); padding:3px 12px; border-radius:20px; letter-spacing:0.05em; text-transform:uppercase; margin-bottom:12px"
                                    id="smStatusBadge">ACTIVE STUDENT</div>
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px">
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            DEPARTMENT</div>
                                        <div style="font-size:12px; font-weight:700; color:var(--text)" id="smDept">N/A
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                            MAJOR</div>
                                        <div style="font-size:12px; font-weight:700; color:var(--accent)" id="smMajor">N/A
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top:12px">
                                    <div
                                        style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:4px">
                                        YEAR LEVEL</div>
                                    <div style="font-size:13px; font-weight:800; color:var(--text2)" id="smYear">1st Year
                                    </div>
                                </div>
                            </div>
                            <div
                                style="background:var(--surface3); border-radius:16px; padding:15px; text-align:center; border:1px solid var(--border)">
                                <div
                                    style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:5px">
                                    ATTENDANCE RATE</div>
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

                        {{-- Action Footer --}}
                        <div
                            style="display:flex; gap:10px; margin-top:20px; padding-top:15px; border-top:1px solid var(--border)">
                            <button type="button" onclick="closeModal('studentDetailModal')" class="btn-secondary"
                                style="height:44px; flex:1; font-weight:700">CLOSE PROFILE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- ═══ SESSION LIST MODAL ═══ --}}
    <div id="sessionsModal" class="modal-overlay">
        <div class="modal-box" style="max-width:700px; border-radius:24px;">
            <div class="modal-head"
                style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:42px;height:42px;border-radius:14px;background:color-mix(in srgb, var(--amber) 13%, transparent);color:var(--amber);display:flex;align-items:center;justify-content:center;box-shadow: 0 4px 15px color-mix(in srgb, var(--amber) 13%, transparent)">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="modal-title" style="font-weight: 800; font-size: 17px; letter-spacing: -0.02em;">Session
                            History</div>
                        <div id="sessionsModalSubtitle"
                            style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:0.02em;text-transform:uppercase">
                            TIMELINE ANALYTICS</div>
                    </div>
                </div>
                <button onclick="closeModal('sessionsModal')" class="modal-close"
                    style="background:var(--surface3); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:0; max-height: 500px; overflow-y: auto;">
                <div id="sessionsListContainer" style="padding: 20px 28px;">
                    {{-- Dynamic content --}}
                </div>
            </div>
            <div class="modal-footer" style="background:var(--surface2); padding: 18px 28px;">
                <button onclick="closeModal('sessionsModal')" class="btn-secondary"
                    style="width:100%; height:42px; font-weight:800; border-radius:12px; font-size:11px; letter-spacing:0.02em">CLOSE TIMELINE</button>
            </div>
        </div>
    </div>

    {{-- ═══ SESSION DETAIL MODAL ═══ --}}
    <div id="sessionDetailModal" class="modal-overlay" style="z-index: 1000;">
        <div class="modal-box" style="max-width:600px; border-radius:24px;">
            <div class="modal-head"
                style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:42px;height:42px;border-radius:14px;background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;box-shadow: 0 4px 15px color-mix(in srgb, var(--accent) 13%, transparent)">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <div id="sdmTitle" class="modal-title"
                            style="font-weight: 800; font-size: 17px; letter-spacing: -0.02em;">Attendance Detail</div>
                        <div id="sdmSubtitle"
                            style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:0.02em;text-transform:uppercase">
                            SESSION RECORD</div>
                    </div>
                </div>
                <button onclick="closeModal('sessionDetailModal')" class="modal-close"
                    style="background:var(--surface3); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:0; max-height: 400px; overflow-y: auto;">
                <div id="sdmStats"
                    style="padding: 15px 28px; background: var(--surface3); border-bottom: 1px solid var(--border); display: flex; gap: 20px;">
                    {{-- Stats injected --}}
                </div>
                <div id="sdmList" style="padding: 20px 28px;">
                    {{-- Student list injected --}}
                </div>
            </div>
            <div class="modal-footer" style="background:var(--surface2); padding: 18px 28px;">
                <button onclick="closeModal('sessionDetailModal')" class="btn-secondary"
                    style="width:100%; height:42px; font-weight:800; border-radius:12px; font-size:11px; letter-spacing:0.02em">RETURN
                    TO TIMELINE</button>
            </div>
        </div>
    </div>


    {{-- ═══ SKIP CONFIRMATION MODAL ═══ --}}
    <div id="skipConfirmModal" class="modal-overlay" style="z-index: 1200;">
        <div class="modal-box" style="max-width:450px; border-radius:24px; overflow:hidden;">
            <div class="modal-body" style="padding:40px 32px; text-align:center;">
                <div
                    style="width:64px; height:64px; border-radius:20px; background:color-mix(in srgb, var(--red) 8%, transparent); color:var(--red); display:flex; align-items:center; justify-content:center; margin:0 auto 24px; box-shadow: 0 8px 20px color-mix(in srgb, var(--red) 8%, transparent);">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div
                    style="font-weight:900; font-size:20px; color:var(--text); letter-spacing:-0.02em; margin-bottom:12px;">
                    Skip this session?</div>
                <p
                    style="font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:32px; font-family:var(--font-mono)">
                    You are marking this academic slot as <span style="color:var(--red); font-weight:700">SKIPPED</span>.
                    Choose if you want to recover this lost time at the end of the semester.</p>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button id="skipRescheduleBtn" class="btn-primary"
                        style="width:100%; height:50px; background:linear-gradient(135deg, var(--green), #10b981); box-shadow:0 10px 25px rgba(16, 185, 129, 0.3); border:none; font-weight:800; font-size:11px; letter-spacing:0.05em;">
                        SKIP & RESCHEDULE TO END
                    </button>
                    <button id="skipOnlyBtn" class="btn-secondary"
                        style="width:100%; height:50px; border:1.5px solid color-mix(in srgb, var(--red) 20%, transparent); color:var(--red); font-weight:800; font-size:11px; letter-spacing:0.05em; background:transparent;">
                        JUST SKIP (LOST TIME)
                    </button>
                    <button onclick="closeModal('skipConfirmModal')" class="btn-secondary"
                        style="width:100%; height:46px; border:none; background:transparent; color:var(--muted2); font-weight:700; font-size:10px; margin-top:8px;">
                        CANCEL ACTION
                    </button>
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- ═══ GRADING & REVIEW MODAL ═══ --}}
    <div id="gradingModal" class="modal-overlay" style="z-index: 1250;">
        <div class="modal-box" style="max-width:500px; border-radius:24px; overflow:hidden;">
            <div class="modal-head"
                style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:40px;height:40px;border-radius:12px;background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <div class="modal-title" style="font-weight: 800; font-size: 16px;">Grading & Review</div>
                        <div id="gradingModalSubtitle"
                            style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:0.02em;text-transform:uppercase">
                            CLASS PERFORMANCE REVIEW</div>
                    </div>
                </div>
                <button onclick="closeModal('gradingModal')" class="modal-close"
                    style="background:var(--surface3); width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:28px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="gradingAssignmentId">
                <input type="hidden" id="gradingClassId">

                {{-- Stats Cards --}}
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:24px;">
                    <div
                        style="background:var(--surface3); padding:12px; border-radius:14px; border:1px solid var(--border); text-align:center;">
                        <div
                            style="font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; margin-bottom:4px;">
                            Students</div>
                        <div id="gradeStatStudents"
                            style="font-size:16px; font-weight:900; color:var(--text); font-family:var(--font-display);">0
                        </div>
                    </div>
                    <div
                        style="background:var(--surface3); padding:12px; border-radius:14px; border:1px solid var(--border); text-align:center;">
                        <div
                            style="font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; margin-bottom:4px;">
                            Sessions</div>
                        <div id="gradeStatSessions"
                            style="font-size:16px; font-weight:900; color:var(--text); font-family:var(--font-display);">0
                        </div>
                    </div>
                    <div
                        style="background:var(--surface3); padding:12px; border-radius:14px; border:1px solid var(--border); text-align:center;">
                        <div
                            style="font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; margin-bottom:4px;">
                            Avg Att.</div>
                        <div id="gradeStatRate"
                            style="font-size:16px; font-weight:900; color:var(--accent); font-family:var(--font-display);">
                            0%</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                    <div class="form-group">
                        <label class="form-label"
                            style="font-size:10px; color:var(--muted); letter-spacing:0.05em">TEACHER'S INPUT SCORE</label>
                        <div id="teacherScoreDisplay"
                            style="height:44px; background:var(--surface3); border-radius:10px; border:1px solid var(--border); display:flex; align-items:center; padding:0 12px; font-family:var(--font-mono); font-weight:800; color:var(--violet)">
                            —</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size:10px; color:var(--text); letter-spacing:0.05em">FINAL
                            ADMIN SCORE</label>
                        <input id="adminScoreInput" class="form-input" type="number" step="0.1" min="0" max="100"
                            placeholder="0.0"
                            style="background:var(--surface3); width:100%; height:44px; border-radius:10px; border:1px solid var(--border); padding:0 12px; color:var(--text); font-family:var(--font-mono); font-weight:800; border-color:color-mix(in srgb, var(--accent) 20%, transparent)">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:24px">
                    <label class="form-label" style="font-size:10px; color:var(--muted)">REVIEWER NOTES</label>
                    <textarea id="gradingNotesInput" class="form-input"
                        placeholder="Enter class performance observations..."
                        style="background:var(--surface3); width:100%; height:70px; border-radius:12px; border:1px solid var(--border); padding:12px; color:var(--text); resize:none; font-size:11px; line-height:1.4"></textarea>
                </div>

                {{-- Student Performance Preview --}}
                <div style="margin-bottom:24px">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <label class="form-label" style="margin:0; font-size:10px; color:var(--muted)">STUDENT PERFORMANCE
                            PREVIEW</label>
                        <span style="font-size:9px; font-family:var(--font-mono); color:var(--muted2)">TOP 15 RECORDS</span>
                    </div>
                    <div
                        style="border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--surface2)">
                        <div style="max-height:200px; overflow-y:auto;">
                            <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                <thead style="background:var(--surface3); position:sticky; top:0; z-index:10">
                                    <tr>
                                        <th
                                            style="padding:8px 12px; text-align:left; color:var(--muted2); font-weight:800;">
                                            STUDENT</th>
                                        <th
                                            style="padding:8px 12px; text-align:center; color:var(--muted2); font-weight:800;">
                                            RATE</th>
                                        <th
                                            style="padding:8px 12px; text-align:right; color:var(--muted2); font-weight:800; width:80px;">
                                            SCORE</th>
                                    </tr>
                                </thead>
                                <tbody id="gradingStudentPreviewBody">
                                    {{-- Injected --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div style="margin-top:10px; display:flex; justify-content:flex-end;">
                        <button id="saveStudentScoresBtn" onclick="saveStudentScores()" class="btn-primary"
                            style="height:32px; padding:0 15px; font-size:9px; background:var(--surface3); color:var(--text); border:1px solid var(--border); font-weight:700;">SAVE
                            STUDENT SCORES</button>
                    </div>
                </div>

                <div id="gradingStatusSection">
                    <label class="form-label" style="font-size:10px; color:var(--muted)">SELECTION STATUS</label>
                    <select id="gradingStatusSelect" class="form-input"
                        style="background:var(--surface3); width:100%; height:44px; border-radius:12px; border:1px solid var(--border); padding:0 12px; color:var(--text); font-weight:700">
                        <option value="pending">PENDING (STILL REVIEWING)</option>
                        <option value="reviewed">REVIEWED (LOCKED FOR TEACHER)</option>
                        <option value="finalized">FINALIZED (READY TO TERMINATE)</option>
                    </select>
                </div>

                <div id="gradingIncompleteWarning"
                    style="display:none; margin-top:20px; padding:15px; background:color-mix(in srgb, var(--red) 3%, transparent); border:1px dashed color-mix(in srgb, var(--red) 20%, transparent); border-radius:12px;">
                    <div
                        style="display:flex; align-items:center; gap:10px; color:var(--red); font-weight:800; font-size:11px; margin-bottom:5px;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        INCOMPLETE SESSIONS DETECTED
                    </div>
                    <p style="font-size:10px; color:var(--red); line-height:1.4; opacity:0.8;">
                        This class still has <span id="incompleteSessionCount" style="font-weight:900">0</span> scheduled
                        sessions remaining. You cannot finalize or end this schedule until all sessions are either completed
                        or marked as skipped.
                    </p>
                </div>

                <div id="gradingReportSection" style="margin-top:24px; display:none;">
                    <button onclick="downloadSemesterReport()" class="btn-secondary"
                        style="width:100%; height:42px; border:1px solid var(--border); background:var(--surface3); color:var(--text2); font-weight:700; display:flex; align-items:center; justify-content:center; gap:8px; font-size:11px;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        GENERATE PERFORMANCE REPORT
                    </button>
                </div>
            </div>
            <div class="modal-footer" style="background:var(--surface2); padding: 18px 28px; display:flex; gap:10px;">
                <button id="saveGradingBtn" class="btn-primary"
                    style="flex:1; height:46px; background:var(--accent); border:none; font-weight:800; border-radius:12px; font-size:11px;"
                    onclick="saveGrading()">SAVE REVIEW</button>
                <button id="proceedToEndBtn" class="btn-primary"
                    style="flex:1; height:46px; background:var(--red); border:none; font-weight:800; border-radius:12px; font-size:11px; display:none;"
                    onclick="triggerEndFromGrading()">END SCHEDULE</button>
            </div>
        </div>
    </div>

    {{-- ═══ NO SEMESTER WARNING MODAL ═══ --}}
    <div id="noSemesterModal" class="modal-overlay" style="z-index: 1500;">
        <div class="modal-box" style="max-width:400px; border-radius:24px; overflow:hidden;">
            <div class="modal-body" style="padding:40px 30px; text-align:center;">
                <div
                    style="width:60px; height:60px; border-radius:20px; background:color-mix(in srgb, var(--amber) 8%, transparent); color:var(--amber); display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 style="font-size:18px; font-weight:800; color:var(--text); margin-bottom:12px;">No Active Semester</h3>
                <p style="font-size:11px; color:var(--muted); line-height:1.6; margin-bottom:24px;">
                    This class hasn't been assigned to a semester yet. You can either end it directly without grading or
                    close this to assign a semester first.
                </p>
                <input type="hidden" id="noSemClassId">
                <input type="hidden" id="noSemClassName">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <button onclick="triggerEndNoSem()" class="btn-primary"
                        style="width:100%; height:46px; background:var(--red); border:none; font-weight:800; border-radius:12px; font-size:11px;">END
                        SCHEDULE DIRECTLY</button>
                    <button onclick="closeModal('noSemesterModal')" class="btn-secondary"
                        style="width:100%; height:46px; border:1px solid var(--border); background:var(--surface3); color:var(--text2); font-weight:700; border-radius:12px; font-size:11px;">CANCEL
                        & REVIEW</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ END SCHEDULE CONFIRMATION MODAL ═══ --}}
    <div id="endScheduleModal" class="modal-overlay" style="z-index: 1200;">
        <div class="modal-box" style="max-width:450px; border-radius:24px; overflow:hidden;">
            <div class="modal-body" style="padding:40px 32px; text-align:center;">
                <div
                    style="width:64px; height:64px; border-radius:20px; background:color-mix(in srgb, var(--red) 8%, transparent); color:var(--red); display:flex; align-items:center; justify-content:center; margin:0 auto 24px; box-shadow: 0 8px 20px color-mix(in srgb, var(--red) 8%, transparent);">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div id="endScheduleTitle"
                    style="font-weight:900; font-size:20px; color:var(--text); letter-spacing:-0.02em; margin-bottom:12px;">
                    End Course Schedule?</div>
                <p id="endScheduleDesc"
                    style="font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:32px; font-family:var(--font-mono)">
                    This will mark all <span style="color:var(--accent); font-weight:700">future sessions</span> for this
                    course as skipped and finalize the current semester assignment.</p>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button id="confirmEndScheduleBtn" class="btn-primary"
                        style="width:100%; height:50px; background:linear-gradient(135deg, var(--red), #f43f5e); box-shadow:0 10px 25px rgba(244, 63, 94, 0.3); border:none; font-weight:800; font-size:11px; letter-spacing:0.05em;">
                        CONFIRM & END SCHEDULE
                    </button>
                    <button onclick="closeModal('endScheduleModal')" class="btn-secondary"
                        style="width:100%; height:46px; border:none; background:transparent; color:var(--muted2); font-weight:700; font-size:10px; margin-top:8px;">
                        CANCEL ACTION
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══ SESSION RESCHEDULE MODAL ═══ --}}
    <div id="rescheduleModal" class="modal-overlay" style="z-index: 1200;">
        <div class="modal-box" style="max-width:450px; border-radius:24px; overflow:hidden;">
            <div class="modal-head"
                style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:15px">
                    <div
                        style="width:40px;height:40px;border-radius:12px;background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="modal-title" style="font-weight: 800; font-size: 16px;">Manual Reschedule</div>
                        <div
                            style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:0.02em;text-transform:uppercase">
                            ADJUST SESSION TIMING</div>
                    </div>
                </div>
                <button onclick="closeModal('rescheduleModal')" class="modal-close"
                    style="background:var(--surface3); width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding:28px;">
                <input type="hidden" id="reschSessionId">
                <input type="hidden" id="reschClassId">

                <div class="form-group" style="margin-bottom:20px">
                    <label class="form-label">New Start Date & Time <span class="req">*</span></label>
                    <input id="reschStart" class="form-input" type="datetime-local"
                        style="background:var(--surface3); width:100%; height:42px; border-radius:10px; border:1px solid var(--border); padding:0 12px; color:var(--text)">
                </div>

                <div class="form-group">
                    <label class="form-label">New End Time <span class="req">*</span></label>
                    <input id="reschEnd" class="form-input" type="datetime-local"
                        style="background:var(--surface3); width:100%; height:42px; border-radius:10px; border:1px solid var(--border); padding:0 12px; color:var(--text)">
                </div>

                <div
                    style="margin-top:24px; padding:15px; border-radius:12px; background:color-mix(in srgb, var(--accent) 3%, transparent); border:1px dashed color-mix(in srgb, var(--accent) 20%, transparent);">
                    <p style="font-size:10px; color:var(--text2); line-height:1.4; font-family:var(--font-mono)">
                        <span style="color:var(--accent); font-weight:800">NOTE:</span> QR check-in windows will
                        automatically adjust to ±20 minutes from the new start time.
                    </p>
                </div>
            </div>
            <div class="modal-footer" style="background:var(--surface2); padding: 188px 28px;">
                <button id="executeReschBtn" class="btn-primary"
                    style="width:100%; height:46px; font-weight:800; border-radius:12px; font-size:11px; letter-spacing:0.02em"
                    onclick="executeReschedule()">UPDATE ACADEMIC SLOT</button>
            </div>
        </div>
    </div>
    {{-- ═══ SKIP & MOVE TO END CONFIRMATION MODAL ═══ --}}
    <div id="skipMoveConfirmModal" class="modal-overlay" style="z-index: 1300;">
        <div class="modal-box" style="max-width:450px; border-radius:24px; overflow:hidden;">
            <div class="modal-body" style="padding:40px 32px; text-align:center;">
                <div
                    style="width:64px; height:64px; border-radius:20px; background:color-mix(in srgb, var(--accent) 8%, transparent); color:var(--accent); display:flex; align-items:center; justify-content:center; margin:0 auto 24px; box-shadow: 0 8px 20px color-mix(in srgb, var(--accent) 8%, transparent);">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div style="font-weight:900; font-size:20px; color:var(--text); letter-spacing:-0.02em; margin-bottom:8px;">
                    Move to End?</div>
                <p
                    style="font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:24px; font-family:var(--font-mono)">
                    This session will be moved to the next available slot at the end of the semester:</p>

                <div
                    style="background:var(--surface3); padding:15px; border-radius:15px; border:1px solid var(--border); margin-bottom:32px;">
                    <div id="moveTargetDate"
                        style="font-family:var(--font-mono); font-size:18px; font-weight:800; color:var(--accent)">
                        CALCULATING...</div>
                    <div id="moveTargetTime"
                        style="font-family:var(--font-mono); font-size:11px; color:var(--muted2); margin-top:4px">...</div>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button id="confirmMoveBtn" class="btn-primary"
                        style="width:100%; height:50px; background:var(--accent); box-shadow:0 10px 25px color-mix(in srgb, var(--accent) 20%, transparent); border:none; font-weight:800; font-size:11px; letter-spacing:0.05em;"
                        onclick="finalExecuteMove()">
                        CONFIRM & MOVE SESSION
                    </button>
                    <button onclick="closeModal('skipMoveConfirmModal')" class="btn-secondary"
                        style="width:100%; height:46px; border:none; background:transparent; color:var(--muted2); font-weight:700; font-size:10px;">
                        CANCEL
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        const courseT = @json(trans('admin_courses'));
        const courseText = (key, fallback = key) => courseT[key] || fallback;
        const courseCountText = (key, count, fallback) => (courseT[key] || fallback).replace(':count', count);

        // ─── Modals ────────────────────────────────────
        // ═════════════════════════════════════════════════════════════════════
        // ACTION DROPDOWN LOGIC
        // ═════════════════════════════════════════════════════════════════════
        let activeActionMenu = null;
        let activeActionButton = null;

        function closeActionMenus() {
            if (activeActionMenu && activeActionMenu._origin) {
                activeActionMenu.classList.remove('show');
                activeActionMenu.removeAttribute('style');
                activeActionMenu._origin.appendChild(activeActionMenu);
            }

            document.querySelectorAll('.action-dropdown-menu').forEach(m => {
                m.classList.remove('show');
                if (m._origin && m.parentElement !== m._origin) {
                    m.removeAttribute('style');
                    m._origin.appendChild(m);
                }
            });
            document.querySelectorAll('.more-btn').forEach(b => b.classList.remove('active'));
            activeActionMenu = null;
            activeActionButton = null;
        }

        function toggleActionMenu(e, btn) {
            e.stopPropagation();

            const menu = btn.nextElementSibling;
            const isOpen = activeActionMenu === menu && menu.classList.contains('show');
            closeActionMenus();

            if (isOpen) return;

            menu._origin = menu.parentElement;
            document.body.appendChild(menu);
            menu.classList.add('show');
            btn.classList.add('active');

            const buttonRect = btn.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const gap = 8;
            const margin = 12;

            let left = buttonRect.right - menuRect.width;
            let top = buttonRect.bottom + gap;

            if (left < margin) left = margin;
            if (left + menuRect.width > window.innerWidth - margin) {
                left = window.innerWidth - menuRect.width - margin;
            }
            if (top + menuRect.height > window.innerHeight - margin) {
                top = Math.max(margin, buttonRect.top - menuRect.height - gap);
            }

            menu.style.left = `${left}px`;
            menu.style.top = `${top}px`;
            activeActionMenu = menu;
            activeActionButton = btn;
        }

        // Close menu when clicking anywhere else
        window.addEventListener('click', closeActionMenus);
        window.addEventListener('scroll', closeActionMenus, true);
        window.addEventListener('resize', closeActionMenus);
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeActionMenus();
        });

        // Prevent menu from closing when clicking inside it
        document.addEventListener('click', function (e) {
            if (e.target.closest('.action-dropdown-menu')) {
                if (e.target.closest('.dropdown-item')) {
                    setTimeout(closeActionMenus, 0);
                }
                e.stopPropagation();
            }
        });

        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }

        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });

        // ESC to close all modals
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.open').forEach(modal => {
                    modal.classList.remove('open');
                });
            }
        });

        // ─── Toast ─────────────────────────────────────
        // ─── Cache Clear ─────────────────────────────────────────────
        async function runCacheClear() {
            const btn   = document.getElementById('syncCacheBtn');
            const label = document.getElementById('syncCacheBtnLabel');
            const icon  = document.getElementById('syncCacheIcon');

            btn.disabled = true;
            label.textContent = courseText('syncing', 'SYNCING...');
            icon.style.animation = 'spin 0.9s linear infinite';

            // Inline spin keyframe if not already defined
            if (!document.getElementById('spinStyle')) {
                const style = document.createElement('style');
                style.id = 'spinStyle';
                style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                          || document.querySelector('input[name="_token"]')?.value || '';

                const res  = await fetch('{{ route("admin.cache.clear") }}', {
                    method : 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });

                const data = await res.json();
                if (data.success) {
                    showToast(courseText('cache_purged', 'Cache purged. App, config, route, and view caches were cleared.'), 'success');
                } else {
                    showToast(data.message || courseText('cache_clear_failed', 'Cache clear failed.'), 'error');
                }
            } catch (err) {
                showToast(courseText('cache_connection_error', 'Connection error during cache sync.'), 'error');
            }

            btn.disabled = false;
            label.textContent = courseText('sync_cache', 'SYNC CACHE');
            icon.style.animation = '';
        }

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const ic = document.getElementById('toastIcon');
            const tx = document.getElementById('toastMsg');
            t.className = `toast show toast-${type}`;
            ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
            tx.textContent = window.__t ? window.__t(msg) : msg;
            clearTimeout(t._t);
            t._t = setTimeout(() => t.classList.remove('show'), 3200);
        }

        // ── Search + Filter (Server-side) ─────────────
        let filterTimeout = null;
        function filterTable(e) {
            if (e && e.type === 'keyup' && e.key !== 'Enter') {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => filterTable(), 800);
                return;
            }
            const q = document.getElementById('searchInput').value;
            const params = new URLSearchParams(window.location.search);
            if (q) params.set('search', q); else params.delete('search');
            params.set('page', 1);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }

        function filterByStatus(status) {
            const url = new URL(window.location.href);
            if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
            window.location.href = url.toString();
        }

        function filterByDept(deptId) {
            const url = new URL(window.location.href);
            if (deptId) url.searchParams.set('dept', deptId); else url.searchParams.delete('dept');
            window.location.href = url.toString();
        }

        // ─── Delete ────────────────────────────────────
        function logActivity(action, target) {
            const container = document.getElementById('dbActivityLogs');

            // 🟢 CRITICAL: Remove placeholder if it exists before adding real data
            const placeholder = document.getElementById('dbActivityPlaceholder');
            if (placeholder) placeholder.remove();

            const now = new Date();
            const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

            const color = action === 'DELETE' ? 'var(--red)' :
                action === 'UPDATE' ? 'var(--amber)' :
                    action === 'INSERT' ? 'var(--green)' : 'var(--accent)';

            const html = `
                                    <div class="db-entry" style="padding:12px 0; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; gap:12px; animation: slideIn 0.4s ease-out">
                                        <div style="margin-top:4px; width:6px; height:6px; border-radius:50%; background:${color}; box-shadow:0 0 10px ${color}"></div>
                                        <div style="flex:1">
                                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                                                <span style="font-family:var(--font-mono); font-size:10px; font-weight:900; color:${color}; letter-spacing:0.05em">${action}</span>
                                                <span style="font-family:var(--font-mono); font-size:9px; color:var(--muted)">${time}</span>
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:10px; color:var(--text2)">
                                                ${target}
                                            </div>
                                        </div>
                                    </div>
                                `;
            container.insertAdjacentHTML('afterbegin', html);

            // Keep only last 5
            if (container.children.length > 5) {
                container.lastElementChild.remove();
            }
        }

        async function fetchInitialActivity() {
            try {
                const res = await fetch('/api/admin/global-activity?limit=5');
                const data = await res.json();
                if (data.success && data.activity.length > 0) {
                    const placeholder = document.getElementById('dbActivityPlaceholder');
                    if (placeholder) placeholder.remove();

                    // Clear existing if any
                    const container = document.getElementById('dbActivityLogs');

                    data.activity.reverse().forEach(act => {
                        logActivity(act.action, act.target);
                    });
                } else {
                    document.getElementById('dbActivityPlaceholder').innerHTML = courseText('no_recent_actions_found', 'NO RECENT ACTIONS FOUND');
                }
            } catch (e) {
                console.error('Activity fetch failed', e);
                document.getElementById('dbActivityPlaceholder').innerHTML = courseText('stream_offline', 'STREAM OFFLINE');
            }
        }

        // Run on load
        document.addEventListener('DOMContentLoaded', () => {
            fetchInitialActivity();
        });

        // Add slideIn animation
        const style = document.createElement('style');
        style.textContent = `
                                @keyframes slideIn {
                                    from { opacity: 0; transform: translateX(20px); }
                                    to { opacity: 1; transform: translateX(0); }
                                }
                            `;
        document.head.appendChild(style);
        function openDeleteModal(id) { pendingDeleteId = id; openModal('deleteModal'); }

        document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
            if (!pendingDeleteId) return;
            const btn = document.getElementById('confirmDeleteBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = courseText('deleting', 'DELETING...');
            try {
                const res = await fetch(`/api/admin/classes/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${pendingDeleteId}"]`);
                    if (row) { row.style.opacity = 0; setTimeout(() => row.remove(), 300); }
                    showToast(courseText('class_deleted', 'Class entry deleted permanently.'), 'success');
                    closeModal('deleteModal');
                    // Update counts if necessary
                    const cntEl = document.getElementById('rowCount');
                    if (cntEl) cntEl.textContent = parseInt(cntEl.textContent) - 1;
                } else {
                    showToast(data.error || courseText('delete_failed', 'Failed to delete entry.'), 'error');
                }
            } catch (e) {
                showToast(courseText('delete_network_error', 'Network error on delete.'), 'error');
            }
            btn.innerHTML = ogHtml;
            pendingDeleteId = null;
        });

        // ─── View (read-only) ──────────────────────────
        function openViewModal(id, subject, instructor, room, schedule, status, groupId) {
            document.getElementById('editModalTitle').textContent = courseText('class_details', 'Class Details');
            document.getElementById('editClassId').value = id;
            document.getElementById('editSubjectName').value = subject;
            document.getElementById('editInstructor').value = instructor;
            document.getElementById('editRoom').value = room;
            const gEl = document.getElementById('editClassGroup');
            if (gEl) {
                const ids = groupId ? groupId.toString().split(',') : [];
                Array.from(gEl.options).forEach(opt => opt.selected = ids.includes(opt.value));
            }

            // Sync Day Chips
            const syncDayChips = (selectorId, sched) => {
                document.querySelectorAll(`#${selectorId} input`).forEach(inp => {
                    inp.checked = false;
                    if (sched.includes('mon-fri') || sched.includes('weekday')) {
                        if (['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(inp.value)) inp.checked = true;
                    } else if (sched.includes(inp.value.toLowerCase()) || sched.includes(inp.value)) {
                        inp.checked = true;
                    }
                });
            };

            if (schedule) {
                syncDayChips('editDaySelector', schedule.toLowerCase());
                const parts = schedule.split(' ');
                const timePart = parts.length > 1 ? parts[1] : '';
                if (timePart.includes('(')) {
                    const inner = timePart.replace('(', '').replace(')', '');
                    const times = inner.includes('–') ? inner.split('–') : inner.split('-');
                    if (times.length >= 2) {
                        document.getElementById('editTimeStart').value = times[0];
                        document.getElementById('editTimeEnd').value = times[1];
                    }
                }
            }

            let stEl = document.getElementById('editStatus');
            if (stEl) stEl.value = status;

            ['editSubjectName', 'editInstructor', 'editRoom', 'editTimeStart', 'editTimeEnd', 'editStatus', 'editClassGroup'].forEach(f => {
                const el = document.getElementById(f);
                if (!el) return;
                el.disabled = true;
                el.readOnly = true;
            });
            document.querySelectorAll('#editDaySelector input').forEach(inp => inp.disabled = true);
            document.getElementById('editModalFooter').style.display = 'none';
            openModal('editModal');
        }

        // ─── Edit ──────────────────────────────────────
        function openEditModal(id, subject, instructor, room, schedule, status, groupId) {
            document.getElementById('editModalTitle').textContent = courseText('edit_class', 'Edit Class');
            document.getElementById('editClassId').value = id;
            document.getElementById('editSubjectName').value = subject;
            document.getElementById('editInstructor').value = instructor;
            document.getElementById('editRoom').value = room;
            const editGEl = document.getElementById('editClassGroup');
            if (editGEl) {
                const ids = groupId ? groupId.toString().split(',') : [];
                Array.from(editGEl.options).forEach(opt => opt.selected = ids.includes(opt.value));
            }

            // Sync Day Chips
            const syncDayChips = (selectorId, sched) => {
                document.querySelectorAll(`#${selectorId} input`).forEach(inp => {
                    inp.checked = false;
                    // Disable only for "View" mode? No, this function is shared.
                    // Actually I should pass a "disabled" flag.
                });

                const lowerSched = sched.toLowerCase();
                document.querySelectorAll(`#${selectorId} input`).forEach(inp => {
                    if (lowerSched.includes('mon-fri') || lowerSched.includes('weekday')) {
                        if (['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(inp.value)) inp.checked = true;
                    } else if (lowerSched.includes(inp.value.toLowerCase())) {
                        inp.checked = true;
                    }
                });
            };

            if (schedule) {
                syncDayChips('editDaySelector', schedule);
                const parts = schedule.split(' ');
                const timePart = parts.length > 1 ? parts[parts.length - 1] : '';
                if (timePart.includes('(')) {
                    const inner = timePart.replace('(', '').replace(')', '');
                    const times = inner.includes('–') ? inner.split('–') : inner.split('-');
                    if (times.length >= 2) {
                        document.getElementById('editTimeStart').value = times[0];
                        document.getElementById('editTimeEnd').value = times[1];
                    }
                }
            }

            let stEl = document.getElementById('editStatus');
            if (stEl) stEl.value = status;

            ['editSubjectName', 'editInstructor', 'editRoom', 'editTimeStart', 'editTimeEnd', 'editStatus', 'editClassGroup'].forEach(f => {
                const el = document.getElementById(f);
                if (!el) return;
                el.disabled = false;
                el.readOnly = false;
            });
            document.querySelectorAll('#editDaySelector input').forEach(inp => inp.disabled = false);
            document.getElementById('editModalFooter').style.display = 'flex';
            openModal('editModal');
        }
        document.getElementById('editForm').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = courseText('saving', 'SAVING...');

            try {
                const id = document.getElementById('editClassId').value;
                const formData = new FormData(e.target);

                // Format schedule from chips
                const getSelectedDays = (selectorId) => {
                    const checked = Array.from(document.querySelectorAll(`#${selectorId} input:checked`));
                    if (checked.length === 5 && checked.every(c => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(c.value))) return 'Mon-Fri';
                    return checked.length > 0 ? checked.map(c => c.value).join('/') : 'TBD';
                };

                const days = getSelectedDays('editDaySelector');
                const tStart = formData.get('time_start') || '00:00';
                const tEnd = formData.get('time_end') || '00:00';
                const scheduleStr = `${days} (${tStart}-${tEnd})`;

                const payload = {
                    subject_id: formData.get('subject_id'),
                    teacher_id: formData.get('teacher_id'),
                    group_ids: formData.getAll('group_ids[]'),
                    room_number: formData.get('room_number'),
                    schedule: scheduleStr,
                    status: formData.get('status')
                };

                const res = await fetch(`/api/admin/classes/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    logActivity('UPDATE', `catalog.classes#${id}`);
                    showToast(courseText('class_updated', 'Class updated successfully.'), 'success');
                    closeModal('editModal');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(data.error || courseText('update_failed', 'Failed to update entry.'), 'error');
                }
            } catch (err) {
                showToast(courseText('network_error', 'Network error occurred.'), 'error');
            }
            btn.innerHTML = ogHtml;
        });

        // ─── Create ────────────────────────────────────
        document.getElementById('createForm').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = courseText('saving', 'SAVING...');

            try {
                const formData = new FormData(e.target);

                // Find selected subject text to generate DB title
                let subName = 'New Module';
                const subSelect = e.target.querySelector('select[name="subject_id"]');
                if (subSelect && subSelect.selectedIndex > 0) {
                    subName = subSelect.options[subSelect.selectedIndex].text;
                }

                // Format schedule from chips
                const getSelectedDays = (selectorId) => {
                    const checked = Array.from(document.querySelectorAll(`#${selectorId} input:checked`));
                    if (checked.length === 5 && checked.every(c => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(c.value))) return 'Mon-Fri';
                    return checked.length > 0 ? checked.map(c => c.value).join('/') : 'TBD';
                };

                const days = getSelectedDays('createDaySelector');
                const tStart = formData.get('time_start') || '00:00';
                const tEnd = formData.get('time_end') || '00:00';
                const scheduleStr = `${days} (${tStart}-${tEnd})`;

                const payload = {
                    name: subName + ' Class', // "name" goes directly to DB classes.name
                    subject_id: formData.get('subject_id'),
                    teacher_id: formData.get('teacher_id'),
                    group_ids: formData.getAll('group_ids[]'),
                    room_number: formData.get('room_number'),
                    schedule: scheduleStr,
                    status: formData.get('status')
                };

                const res = await fetch('/api/admin/classes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    showToast(courseText('new_entry_created', 'New catalog entry created.'), 'success');
                    closeModal('createModal');
                    e.target.reset();
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(data.error || courseText('create_failed', 'Failed to create entry.'), 'error');
                }
            } catch (err) {
                showToast(courseText('network_error', 'Network error occurred.'), 'error');
            }
            btn.innerHTML = ogHtml;
        });

        // ─── Enrollment Management ──────────────────────
        let enrollingClassId = null;
        let enrollingGroupIds = [];
        function openEnrollModal(classId, className, groupIds) {
            enrollingClassId = classId;
            enrollingGroupIds = groupIds ? groupIds.toString().split(',').map(id => parseInt(id)) : [];
            document.getElementById('enrollModalTitle').textContent = `Manage Enrollment: ${className}`;
            refreshEnrollList();
            openModal('enrollModal');
        }

        function filterEnrollList() {
            const q = document.getElementById('studentSearch').value.toLowerCase();
            let count = 0;
            document.querySelectorAll('.enroll-row').forEach(row => {
                const nameMatch = row.dataset.name.includes(q);
                const codeMatch = row.dataset.code.includes(q);
                const majorMatch = (row.dataset.major || "").includes(q);
                const deptMatch = (row.dataset.dept || "").includes(q);
                const yr = row.dataset.year || "";
                const yearMatch = yr.includes(q) || (yr + "st").includes(q) || (yr + "nd").includes(q) || (yr + "rd").includes(q) || (yr + "th").includes(q) || (q.includes("year") && q.includes(yr));

                const show = nameMatch || codeMatch || majorMatch || deptMatch || yearMatch;
                row.style.display = show ? 'flex' : 'none';
                if (show) count++;
            });
            const countBadge = document.getElementById('enrollCount');
            if (countBadge) countBadge.textContent = `${count} RESULT${count === 1 ? '' : 'S'}`;
        }

        function refreshEnrollList() {
            document.querySelectorAll('.enroll-row').forEach(row => {
                const rowClassId = parseInt(row.dataset.class || 0);
                const rowGroupId = parseInt(row.dataset.group || 0);
                const btn = row.querySelector('.enroll-btn');
                btn.className = 'enroll-btn';

                const isCurrentClass = (rowClassId === enrollingClassId);
                const isCurrentGroup = (enrollingGroupIds.length > 0 && enrollingGroupIds.includes(rowGroupId));

                if (isCurrentClass || isCurrentGroup) {
                    btn.classList.add('active');
                } else if (rowClassId > 0 || rowGroupId > 0) {
                    btn.classList.add('other');
                }
            });
        }

        async function toggleEnroll(studentId) {
            const row = document.querySelector(`.enroll-row[data-id="${studentId}"]`);
            const btn = row.querySelector('.enroll-btn');

            const rowGroupId = parseInt(row.dataset.group || 0);
            const isEnrolledByGroup = enrollingGroupIds.includes(rowGroupId);
            const isEnrolledDirectly = parseInt(row.dataset.class) === enrollingClassId;
            const isEnrolled = isEnrolledByGroup || isEnrolledDirectly;

            const newClassId = isEnrolled ? null : enrollingClassId;

            btn.classList.add('loading');

            try {
                const res = await fetch(`/api/admin/students/${studentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ class_id: newClassId })
                });
                const data = await res.json();
                if (data.success) {
                    row.dataset.class = data.student.class_id || 0;
                    row.dataset.group = data.student.group_id || 0;
                    refreshEnrollList();
                    showToast(newClassId ? 'Student cohort updated.' : 'Removed from manual enrollment.', 'success');
                } else {
                    showToast(data.error || 'Server error', 'error');
                }
            } catch (e) {
                showToast(courseText('sync_failure', 'Sync failure'), 'error');
            }
            btn.classList.remove('loading');
        }



        // ── COURSE PAGE: SEMESTER ASSIGNMENT ──────────────────────────────
        const csmCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let _csmClassId = null;

        function csmPreview() {
            const sv = document.getElementById('csmStart').value;
            const hv = document.getElementById('csmHoliday').value;
            const previewDiv = document.getElementById('csmPreview');

            if (!sv) {
                previewDiv.innerHTML = `
                                    <div style="padding:12px; text-align:center; background:color-mix(in srgb, var(--surface3) 27%, transparent); border-radius:12px; border:1px dashed var(--border)">
                                        <div style="font-family:var(--font-mono); font-size:9px; font-weight:700; color:var(--muted); letter-spacing:.05em">TIMELINE ARCHITECTURE INACTIVE</div>
                                        <div style="font-size:8px; color:var(--muted); font-family:var(--font-mono); margin-top:2px">Awaiting start date for temporal visualization</div>
                                    </div>
                                `;
                previewDiv.style.display = 'block';
                return;
            }

            previewDiv.innerHTML = `
                                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:15px">
                                    <div>
                                        <div class="csm-label">ESTIMATED COMPLETION</div>
                                        <div id="csmPrevEnd" style="font-weight:800;font-size:14px;color:var(--text); line-height:1">-</div>
                                    </div>
                                    <div style="text-align:right">
                                        <div class="csm-label">ACADEMIC SPAN</div>
                                        <div id="csmPrevDays" style="font-weight:800;font-size:14px;color:var(--green); line-height:1">-</div>
                                    </div>
                                </div>
                                <div style="position:relative;height:24px;background:var(--surface2);border-radius:8px;overflow:hidden;border:1px solid var(--border); box-shadow: inset 0 2px 4px rgba(0,0,0,0.1)">
                                    <div id="csmPrevBar" style="position:absolute;left:0;top:0;height:100%;background:linear-gradient(90deg, color-mix(in srgb, var(--violet) 27%, transparent), color-mix(in srgb, var(--accent) 27%, transparent));border-radius:8px"></div>
                                    <div id="csmPrevHolBar" style="position:absolute;top:0;height:100%;background:color-mix(in srgb, var(--amber) 20%, transparent); border-left:1px solid color-mix(in srgb, var(--amber) 27%, transparent); border-right:1px solid color-mix(in srgb, var(--amber) 27%, transparent);"></div>
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:8px;color:var(--text);font-weight:700;letter-spacing:0.1em;text-shadow: 0 1px 2px rgba(0,0,0,0.5)">TEMPORAL SEMESTER PROJECTION</div>
                                </div>
                                <div id="csmPrevHol" style="margin-top:10px; font-family:var(--font-mono); font-size:9px; color:var(--amber); font-weight:600; text-align:center"></div>
                            `;
            previewDiv.style.display = 'block';
            const start = new Date(sv);
            const end = new Date(sv); end.setMonth(end.getMonth() + 4);
            const total = Math.round((end - start) / 86400000);
            let hDays = 0, hStart = null, hEnd = null;
            if (hv) { hStart = new Date(hv); hEnd = new Date(hv); hEnd.setDate(hEnd.getDate() + 21); hDays = 21; }
            const active = total - hDays;
            const fmt = d => d ? d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
            document.getElementById('csmPrevEnd').textContent = fmt(end);
            document.getElementById('csmPrevDays').textContent = active + ' DAYS';
            const holEl = document.getElementById('csmPrevHol');
            if (hStart) {
                holEl.innerHTML = `<span style="opacity:0.6">HOLIDAY BREAK:</span> ${fmt(hStart)} — ${fmt(hEnd)}`;
            } else {
                holEl.textContent = '';
            }

            document.getElementById('csmPrevBar').style.width = '100%';
            if (hStart && total > 0) {
                const off = Math.round(((hStart - start) / (end - start)) * 100);
                const wid = Math.min(Math.round((21 / total) * 100), 100 - off);
                const holBar = document.getElementById('csmPrevHolBar');
                holBar.style.left = off + '%';
                holBar.style.width = wid + '%';
            } else {
                document.getElementById('csmPrevHolBar').style.width = '0';
            }
        }

        async function openCourseSemesterModal(classId, className, schedule) {
            if (!classId) { showToast(courseText('invalid_class_id', 'Invalid class ID.'), 'error'); return; }
            _csmClassId = classId;

            // Initial UI state
            document.getElementById('csmSubtitle').textContent = className.toUpperCase();
            document.getElementById('csmStart').value = '';
            document.getElementById('csmHoliday').value = '';
            document.getElementById('csmNotes').value = '';

            // Refresh existing assignments list immediately
            csmLoad(classId);

            // Default times from class schedule if possible
            document.getElementById('csmTimeStart2').value = '';
            document.getElementById('csmTimeEnd2').value = '';

            // Reset Day Chips in CSM
            document.querySelectorAll('#csmDaySelector input').forEach(i => i.checked = false);

            if (schedule && schedule.includes('(')) {
                const daysPart = schedule.split('(')[0].trim();
                const lowerDays = daysPart.toLowerCase();
                document.querySelectorAll('#csmDaySelector input').forEach(inp => {
                    if (lowerDays.includes('mon-fri') || lowerDays.includes('weekday')) {
                        if (['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(inp.value)) inp.checked = true;
                    } else if (lowerDays.includes(inp.value.toLowerCase())) {
                        inp.checked = true;
                    }
                });

                const inner = schedule.split('(')[1].replace(')', '');
                const slots = inner.split(',').map(s => s.trim());

                if (slots[0]) {
                    const times = slots[0].includes('–') ? slots[0].split('–') : slots[0].split('-');
                    if (times.length >= 2) {
                        document.getElementById('csmTimeStart').value = times[0].trim();
                        document.getElementById('csmTimeEnd').value = times[1].trim();
                    }
                }
                if (slots[1]) {
                    const times = slots[1].includes('–') ? slots[1].split('–') : slots[1].split('-');
                    if (times.length >= 2) {
                        document.getElementById('csmTimeStart2').value = times[0].trim();
                        document.getElementById('csmTimeEnd2').value = times[1].trim();
                    }
                }
            } else if (schedule) {
                const lowerSched = schedule.toLowerCase();
                document.querySelectorAll('#csmDaySelector input').forEach(inp => {
                    if (lowerSched.includes(inp.value.toLowerCase())) inp.checked = true;
                });
            }

            csmPreview();
            openModal('courseSemesterModal');
        }

        async function csmLoad(classId) {
            const c = document.getElementById('csmItems');
            const badge = document.getElementById('csmCountBadge');
            c.innerHTML = `
                                <div style="padding:40px; text-align:center;">
                                    <div class="loading-spinner" style="margin: 0 auto 12px; border-top-color: var(--accent);"></div>
                                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted)">RETRIEVING ACADEMIC RECORDS...</div>
                                </div>
                            `;
            try {
                const res = await fetch('/api/admin/classes/' + classId + '/semesters');
                const json = await res.json();
                const data = json.data || json; // Handle wrapped or unwrapped

                if (!data || !data.length) {
                    badge.textContent = courseText('zero_found', '0 FOUND');
                    c.innerHTML = `
                                        <div style="padding:40px 20px; text-align:center; background:color-mix(in srgb, var(--surface3) 27%, transparent); border-radius:16px; border:1px dashed var(--border); margin:0 4px">
                                            <div style="width:48px; height:48px; border-radius:50%; background:color-mix(in srgb, var(--violet) 6%, transparent); color:var(--violet); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; opacity:0.8">
                                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--text); letter-spacing:.05em; margin-bottom:6px">O RECORDS ASSIGNED</div>
                                            <div style="font-size:10px; color:var(--muted); font-family:var(--font-mono); max-width: 280px; margin: 0 auto; line-height: 1.5">No semester periods are currently linked to this course catalog entry.</div>
                                        </div>
                                    `;
                    return;
                }

                const activeAssignment = data.find(a => a.status === 'active');
                if (activeAssignment) {
                    const subTitle = document.getElementById('csmSubtitle');
                    const originalName = subTitle.textContent.split(' • ')[0];
                    subTitle.innerHTML = `${originalName} • <span style="color:var(--green)">ACTIVE PERIOD FOUND</span>`;
                }

                badge.textContent = `${data.length} ACTIVE`;
                c.innerHTML = data.map(a => {
                    const isCompleted = a.status === 'completed';
                    const isActive = a.status === 'active';
                    const sc = isActive ? 'var(--green)' : isCompleted ? 'var(--muted)' : 'var(--accent)';
                    const bg = isActive ? 'color-mix(in srgb, var(--green) 8%, transparent)' : isCompleted ? 'var(--surface3)' : 'color-mix(in srgb, var(--accent) 8%, transparent)';

                    const holDisplay = a.holiday_start
                        ? `<span style="color:var(--amber)">${a.holiday_start}</span> <span style="opacity:0.5">TO</span> <span style="color:var(--amber)">${a.holiday_end}</span>`
                        : '<span style="opacity:0.5">NOT DEFINED</span>';

                    const notesHtml = a.notes
                        ? `<div style="background:var(--surface3); border-radius:10px; padding:10px 14px; margin-bottom:15px; display:flex; align-items:flex-start; gap:10px; border-left: 3px solid color-mix(in srgb, var(--accent) 40%, transparent)">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--accent)" style="margin-top:2px; flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <div style="font-size:10px; color:var(--text2); font-family:var(--font-mono); line-height:1.5">${a.notes}</div>
                                           </div>` : '';

                    return `
                                        <div class="csm-card">
                                            ${notesHtml}
                                            <div class="csm-card-header">
                                                <div class="csm-title-group">
                                                    <div class="csm-accent-bar"></div>
                                                    <span class="csm-title">${a.academic_year} · SEMESTER ${a.semester}</span>
                                                    <span class="csm-badge ${a.status === 'waiting' ? 'upcoming' : a.status}">${a.status === 'waiting' ? 'UPCOMING' : a.status.toUpperCase()}</span>
                                                </div>
                                                <button class="csm-remove-btn" onclick="csmDelete(${a.id})">REMOVE</button>
                                            </div>

                                            <div class="csm-divider"></div>

                                            <div class="csm-grid">
                                                <div>
                                                    <div class="csm-label">TERMINATION DATE</div>
                                                    <div class="csm-value">${a.end_date}</div>
                                                </div>
                                                <div>
                                                    <div class="csm-label">NET SESSIONS</div>
                                                    <div class="csm-value green">${a.active_days} DAYS</div>
                                                </div>
                                                <div>
                                                    <div class="csm-label">ACADEMIC BREAK</div>
                                                    <div class="csm-value ${a.holiday_start ? '' : 'muted'}">${holDisplay}</div>
                                                </div>
                                            </div>

                                            <div class="csm-progress-section">
                                                <div class="csm-progress-head">
                                                    <span class="csm-label" style="margin-bottom:0">${courseText('course_progression', 'COURSE PROGRESSION')}</span>
                                                    <span class="csm-value" style="font-size:13px">${a.progress}%</span>
                                                </div>
                                                <div class="csm-progress-track">
                                                    <div class="csm-progress-fill" style="width:${a.progress}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                }).join('');
            } catch (e) {
                console.error(e);
                c.innerHTML = `
                                    <div style="padding:24px; text-align:center; color:var(--red); background:color-mix(in srgb, var(--red) 3%, transparent); border-radius:12px; border:1px solid color-mix(in srgb, var(--red) 13%, transparent)">
                                        <div style="font-family:var(--font-mono); font-size:10px; font-weight:800">DATA SYNCHRONIZATION ERROR</div>
                                        <div style="font-size:9px; margin-top:4px">Failed to resolve academic assignments.</div>
                                    </div>
                                `;
            }
        }

        async function csmSave() {
            if (!_csmClassId) return;
            const btn = document.getElementById('csmSaveBtn');
            const og = btn.innerHTML; btn.textContent = courseText('saving', 'SAVING...'); btn.disabled = true;
            const getSelectedDays = (selectorId) => {
                const checked = Array.from(document.querySelectorAll(`#${selectorId} input:checked`));
                if (checked.length === 5 && checked.every(c => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(c.value))) return 'Mon-Fri';
                return checked.length > 0 ? checked.map(c => c.value).join('/') : 'TBD';
            };

            const payload = {
                academic_year: document.getElementById('csmYear').value.trim(),
                semester: document.getElementById('csmSemester').value,
                start_date: document.getElementById('csmStart').value,
                schedule_days: getSelectedDays('csmDaySelector'),
                holiday_start: document.getElementById('csmHoliday').value || null,
                notes: document.getElementById('csmNotes').value.trim() || null,
                time_start: document.getElementById('csmTimeStart').value,
                time_end: document.getElementById('csmTimeEnd').value,
                time_start2: document.getElementById('csmTimeStart2').value || null,
                time_end2: document.getElementById('csmTimeEnd2').value || null,
                sessions_count: document.getElementById('csmCount').value,
            };
            if (!payload.academic_year || !payload.start_date) {
                showToast(courseText('academic_year_start_required', 'Academic year and start date are required.'), 'error');
                btn.innerHTML = og; btn.disabled = false; return;
            }
            try {
                const res = await fetch('/api/admin/classes/' + _csmClassId + '/assign-semester', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csmCsrf
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast(courseText('semester_saved', 'Semester assignment saved.'), 'success');
                    document.getElementById('csmStart').value = '';
                    document.getElementById('csmHoliday').value = '';
                    document.getElementById('csmNotes').value = '';
                    document.getElementById('csmPreview').style.display = 'none';
                    await csmLoad(_csmClassId);
                } else { showToast(data.error || data.message || courseText('failed', 'Failed.'), 'error'); }
            } catch (e) { showToast(courseText('network_error_short', 'Network error.'), 'error'); }
            btn.innerHTML = og; btn.disabled = false;
        }

        async function csmDelete(id) {
            if (!await confirmAction(courseText('remove_semester_question', 'Remove this semester assignment?'))) return;
            try {
                const res = await fetch('/api/admin/semesters/' + id, {
                    method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csmCsrf }
                });
                const data = await res.json();
                if (data.success) { showToast(courseText('removed', 'Removed.'), 'success'); await csmLoad(_csmClassId); }
                else showToast(courseText('failed', 'Failed.'), 'error');
            } catch (e) { showToast(courseText('network_error_short', 'Network error.'), 'error'); }
        }

        // ── SESSION HISTORY FEATURE ─────────────────────────
        async function openSessionsModal(classId, className) {
            const container = document.getElementById('sessionsListContainer');
            document.getElementById('sessionsModalSubtitle').textContent = className;
            container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--muted)">Retrieving timeline records...</div>';
            openModal('sessionsModal');

            try {
                const res = await fetch(`/api/admin/classes/${classId}/sessions`);
                if (!res.ok) throw new Error('API request failed');
                const sessions = await res.json();

                if (sessions.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:60px; color:var(--muted); font-family:var(--font-mono); font-size:11px">NO RECORDED SESSIONS FOUND</div>';
                    return;
                }

                container.innerHTML = sessions.map(s => {
                    const startStr = (s.start_time || '').replace(' ', 'T');
                    const d = new Date(startStr);

                    const dateStr = isNaN(d.getTime()) ? 'Invalid Date' : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    const timeStr = isNaN(d.getTime()) ? '--:--' : d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });

                    const isSkipped = s.status === 'skipped';
                    const isCompleted = s.status === 'completed';
                    const isActive = s.status === 'active';
                    const statusClr = isSkipped ? 'var(--red)' : isCompleted ? 'var(--muted)' : isActive ? 'var(--green)' : 'var(--amber)';
                    const statusBg = isSkipped ? 'color-mix(in srgb, var(--red) 8%, transparent)' : isCompleted ? 'var(--surface3)' : isActive ? 'color-mix(in srgb, var(--green) 8%, transparent)' : 'color-mix(in srgb, var(--amber) 8%, transparent)';

                    const pct = s.total_students_count > 0 ? Math.round((s.presence_count / s.total_students_count) * 100) : 0;

                    return `
                                        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px; border:1px solid var(--border); border-radius:16px; margin-bottom:12px; background:var(--surface2); transition:all 0.2s" onmouseover="this.style.borderColor='color-mix(in srgb, var(--amber) 27%, transparent)'; this.style.transform='translateX(4px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none'">
                                            <div style="display:flex; align-items:center; gap:16px">
                                                <div style="width:48px; height:48px; border-radius:12px; background:var(--surface3); display:flex; flex-direction:column; align-items:center; justify-content:center; border:1px solid var(--border)">
                                                    <div style="font-size:9px; font-weight:800; color:var(--muted); font-family:var(--font-mono)">${isNaN(d.getTime()) ? '???' : d.toLocaleDateString('en-US', { month: 'short' }).toUpperCase()}</div>
                                                    <div style="font-size:16px; font-weight:800; color:var(--text); line-height:1">${isNaN(d.getTime()) ? '--' : d.getDate()}</div>
                                                </div>
                                                <div>
                                                    <div style="font-family:var(--font-mono); font-size:12px; font-weight:700; color:var(--text)">${dateStr} @ ${timeStr}</div>
                                                    <div style="display:flex; align-items:center; gap:8px; margin-top:4px">
                                                        <span style="font-family:var(--font-mono); font-size:8px; padding:2px 8px; border-radius:10px; background:${statusBg}; color:${statusClr}; font-weight:800; text-transform:uppercase">${s.status}</span>
                                                        <span style="font-size:10px; color:var(--muted)">•</span>
                                                        <span style="font-family:var(--font-mono); font-size:10px; color:var(--text2); font-weight:700">${s.presence_count} / ${s.total_students_count} <span style="font-weight:400; font-size:9px; color:var(--muted)">ARRIVED</span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:8px">
                                                ${isSkipped ? `
                                                <button class="action-btn" onclick="autoMoveToEnd(${s.id}, ${classId})" style="background:color-mix(in srgb, var(--green) 6%, transparent); border:1px solid color-mix(in srgb, var(--green) 13%, transparent); border-radius:10px; padding:8px 12px; font-family:var(--font-mono); font-size:9px; font-weight:800; cursor:pointer; color:var(--green)">
                                                    AUTO-MOVE
                                                </button>
                                                <button class="action-btn" onclick="openRescheduleModal(${s.id}, ${classId}, '${s.start_time}', '${s.end_time}')" style="background:color-mix(in srgb, var(--accent) 6%, transparent); border:1px solid color-mix(in srgb, var(--accent) 13%, transparent); border-radius:10px; padding:8px 12px; font-family:var(--font-mono); font-size:9px; font-weight:800; cursor:pointer; color:var(--accent)">
                                                    MANUAL
                                                </button>
                                                ` : `
                                                <button class="action-btn" onclick="skipSession(${s.id}, ${classId})" style="background:color-mix(in srgb, var(--red) 6%, transparent); border:1px solid color-mix(in srgb, var(--red) 13%, transparent); border-radius:10px; padding:8px 12px; font-family:var(--font-mono); font-size:9px; font-weight:800; cursor:pointer; color:var(--red)">
                                                    SKIP
                                                </button>
                                                `}
                                                <button class="action-btn" onclick="openSessionDetail(${s.id})" style="background:var(--surface3); border:1px solid var(--border); border-radius:10px; padding:8px 16px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:pointer; color:var(--text2)">
                                                    DETAILS
                                                </button>
                                            </div>
                                        </div>
                                    `;
                }).join('');
            } catch (e) {
                container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--red)">Failed to load session timeline.</div>';
            }
        }

        let _pendingSkip = null;
        function skipSession(sessionId, classId) {
            _pendingSkip = { sessionId, classId };
            openModal('skipConfirmModal');
        }

        document.getElementById('skipOnlyBtn').onclick = () => executeSkip(false);
        document.getElementById('skipRescheduleBtn').onclick = () => executeSkip(true);

        async function executeSkip(reschedule) {
            if (!_pendingSkip) return;
            const { sessionId, classId } = _pendingSkip;
            const btn = reschedule ? document.getElementById('skipRescheduleBtn') : document.getElementById('skipOnlyBtn');
            const ogHtml = btn.innerHTML;

            if (reschedule) {
                btn.innerHTML = 'CALCULATING...';
                btn.disabled = true;
                try {
                    const res = await fetch(`/api/admin/session/${sessionId}/next-available-slot`);
                    const data = await res.json();
                    if (data.success) {
                        document.getElementById('moveTargetDate').textContent = data.date;
                        document.getElementById('moveTargetTime').textContent = data.time;
                        closeModal('skipConfirmModal');
                        openModal('skipMoveConfirmModal');
                    } else {
                        showToast(data.error || 'No future slots available.', 'error');
                    }
                } catch (e) {
                    showToast('Network error.', 'error');
                } finally {
                    btn.innerHTML = ogHtml;
                    btn.disabled = false;
                }
                return;
            }

            btn.innerHTML = 'PROCESSING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/session/${sessionId}/status-update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'skipped', reschedule: false })
                });
                const data = await res.json();
                if (data.success) {
                    logActivity('UPDATE', `session#${sessionId}.status_skip`);
                    showToast('Session marked as skipped.', 'success');
                    closeModal('skipConfirmModal');
                    openSessionsModal(classId, document.getElementById('sessionsModalSubtitle').textContent);
                } else {
                    showToast(data.error || 'Failed to update session.', 'error');
                }
            } catch (err) {
                showToast('Network error.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
                _pendingSkip = null;
            }
        }

        async function finalExecuteMove() {
            if (!_pendingSkip) return;
            const { sessionId, classId } = _pendingSkip;
            const btn = document.getElementById('confirmMoveBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = 'MOVING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/session/${sessionId}/status-update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'skipped', reschedule: true })
                });
                const data = await res.json();
                if (data.success) {
                    logActivity('UPDATE', `session#${sessionId}.move_to_end`);
                    showToast('Session moved to end of semester.', 'success');
                    closeModal('skipMoveConfirmModal');
                    openSessionsModal(classId, document.getElementById('sessionsModalSubtitle').textContent);
                } else {
                    showToast(data.error || 'Failed to move session.', 'error');
                }
            } catch (err) {
                showToast('Network error.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
                _pendingSkip = null;
            }
        }

        let _pendingEndScheduleId = null;
        function openEndScheduleModal(classId, className) {
            _pendingEndScheduleId = classId;
            document.getElementById('endScheduleTitle').textContent = `End Schedule: ${className}`;
            openModal('endScheduleModal');
        }

        document.getElementById('confirmEndScheduleBtn').onclick = async () => {
            if (!_pendingEndScheduleId) return;
            const btn = document.getElementById('confirmEndScheduleBtn');
            const ogHtml = btn.innerHTML;
            btn.innerHTML = 'PROCESSING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/terminate-class/${_pendingEndScheduleId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    closeModal('endScheduleModal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.error || 'Failed to end schedule.', 'error');
                }
            } catch (err) {
                showToast('Network error occurred.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
                _pendingEndScheduleId = null;
            }
        };

        async function openGradingModal(classId, className) {
            document.getElementById('gradingClassId').value = classId;
            document.getElementById('gradingModalSubtitle').textContent = className;

            // Fetch semester assignments to find the active one
            try {
                const res = await fetch(`/api/admin/classes/${classId}/semesters`);
                const data = await res.json();
                if (data.success && data.data.length > 0) {
                    const active = data.data.find(a => a.status === 'active' || a.status === 'completed');
                    if (!active) {
                        document.getElementById('noSemClassId').value = classId;
                        document.getElementById('noSemClassName').value = className;
                        openModal('noSemesterModal');
                        return;
                    }

                    document.getElementById('gradingAssignmentId').value = active.id;
                    document.getElementById('teacherScoreDisplay').textContent = active.teacher_score || 'PENDING';
                    document.getElementById('adminScoreInput').value = active.admin_score || '';
                    document.getElementById('gradingNotesInput').value = active.notes || '';
                    document.getElementById('gradingStatusSelect').value = active.grading_status || 'pending';

                    // Toggle visibility based on status
                    const isFinalized = active.grading_status === 'finalized';
                    document.getElementById('proceedToEndBtn').style.display = isFinalized ? 'block' : 'none';
                    document.getElementById('gradingReportSection').style.display = (isFinalized || active.status === 'completed') ? 'block' : 'none';

                    // Fetch detailed preview
                    fetchGradingPreview(active.id);

                    openModal('gradingModal');
                }
            } catch (err) {
                showToast('Failed to load grading details.', 'error');
            }
        }

        async function fetchGradingPreview(assignmentId) {
            const body = document.getElementById('gradingStudentPreviewBody');
            body.innerHTML = '<tr><td colspan="3" style="padding:20px; text-align:center; color:var(--muted)">Loading performance data...</td></tr>';

            try {
                const res = await fetch(`/api/admin/semesters/${assignmentId}/preview`);
                const data = await res.json();
                if (data.success) {
                    document.getElementById('gradeStatStudents').textContent = data.stats.total_students;
                    document.getElementById('gradeStatSessions').textContent = data.stats.total_sessions;
                    document.getElementById('gradeStatRate').textContent = data.stats.avg_attendance + '%';

                    // Incomplete session validation
                    const scheduled = data.stats.scheduled_count || 0;
                    const warning = document.getElementById('gradingIncompleteWarning');
                    const statusSelect = document.getElementById('gradingStatusSelect');
                    const saveBtn = document.getElementById('saveGradingBtn');

                    if (scheduled > 0) {
                        warning.style.display = 'block';
                        document.getElementById('incompleteSessionCount').textContent = scheduled;
                        statusSelect.disabled = true;
                        statusSelect.value = 'pending';
                        saveBtn.disabled = true;
                        saveBtn.style.opacity = '0.5';
                    } else {
                        warning.style.display = 'none';
                        statusSelect.disabled = false;
                        saveBtn.disabled = false;
                        saveBtn.style.opacity = '1';
                    }

                    body.innerHTML = '';
                    data.students.slice(0, 15).forEach(s => {
                        const row = `
                                    <tr style="border-top:1px solid var(--border)">
                                        <td style="padding:10px 12px;">
                                            <div style="font-weight:700; color:var(--text2)">${s.name}</div>
                                            <div style="font-size:8px; color:var(--muted); font-family:var(--font-mono)">${s.code}</div>
                                        </td>
                                        <td style="padding:10px 12px; text-align:center;">
                                            <span style="font-family:var(--font-mono); font-weight:800; color:${s.rate > 80 ? 'var(--green)' : (s.rate > 50 ? 'var(--amber)' : 'var(--red)')}">${s.rate}%</span>
                                            <div style="font-size:7px; color:var(--muted)">${s.attended} SESS</div>
                                        </td>
                                        <td style="padding:10px 12px; text-align:right;">
                                            <input type="number" class="student-score-input" data-student-id="${s.id}" value="${s.score || ''}" step="0.1" min="0" max="100" style="width:60px; height:28px; background:var(--surface3); border:1px solid var(--border); border-radius:6px; color:var(--text); text-align:center; font-family:var(--font-mono); font-weight:700;">
                                        </td>
                                    </tr>
                                `;
                        body.innerHTML += row;
                    });
                }
            } catch (err) {
                body.innerHTML = '<tr><td colspan="3" style="padding:20px; text-align:center; color:var(--red)">Error loading preview.</td></tr>';
            }
        }

        async function saveStudentScores() {
            const assignmentId = document.getElementById('gradingAssignmentId').value;
            const btn = document.getElementById('saveStudentScoresBtn');
            const ogHtml = btn.innerHTML;

            const scores = Array.from(document.querySelectorAll('.student-score-input')).map(input => ({
                student_id: input.dataset.studentId,
                score: input.value,
                notes: ''
            }));

            btn.innerHTML = 'SAVING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/semesters/${assignmentId}/student-scores`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ scores })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Student scores updated.', 'success');
                } else {
                    showToast('Failed to save scores.', 'error');
                }
            } catch (err) {
                showToast('Network error.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
            }
        }

        async function saveGrading() {
            const assignmentId = document.getElementById('gradingAssignmentId').value;
            const btn = document.getElementById('saveGradingBtn');
            const ogHtml = btn.innerHTML;

            const payload = {
                admin_score: document.getElementById('adminScoreInput').value,
                grading_notes: document.getElementById('gradingNotesInput').value,
                grading_status: document.getElementById('gradingStatusSelect').value
            };

            btn.innerHTML = 'SAVING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/semesters/${assignmentId}/score`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Grading updated successfully.', 'success');
                    const isFinalized = payload.grading_status === 'finalized';
                    document.getElementById('proceedToEndBtn').style.display = isFinalized ? 'block' : 'none';
                    document.getElementById('gradingReportSection').style.display = isFinalized ? 'block' : 'none';
                } else {
                    showToast(data.error || 'Failed to save.', 'error');
                }
            } catch (err) {
                showToast('Network error.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
            }
        }

        function triggerEndFromGrading() {
            const classId = document.getElementById('gradingClassId').value;
            const className = document.getElementById('gradingModalSubtitle').textContent;
            closeModal('gradingModal');
            openEndScheduleModal(classId, className);
        }

        function triggerEndNoSem() {
            const classId = document.getElementById('noSemClassId').value;
            const className = document.getElementById('noSemClassName').value;
            closeModal('noSemesterModal');
            openEndScheduleModal(classId, className);
        }

        function downloadSemesterReport() {
            const assignmentId = document.getElementById('gradingAssignmentId').value;
            window.location.href = `/api/admin/semesters/${assignmentId}/report`;
        }

        function openRescheduleModal(sessionId, classId, currentStart, currentEnd) {
            document.getElementById('reschSessionId').value = sessionId;
            document.getElementById('reschClassId').value = classId;

            const fmt = (d) => {
                if (!d) return '';
                const date = new Date(d.replace(' ', 'T'));
                return date.getFullYear() + '-' +
                    String(date.getMonth() + 1).padStart(2, '0') + '-' +
                    String(date.getDate()).padStart(2, '0') + 'T' +
                    String(date.getHours()).padStart(2, '0') + ':' +
                    String(date.getMinutes()).padStart(2, '0');
            };

            document.getElementById('reschStart').value = fmt(currentStart);
            document.getElementById('reschEnd').value = fmt(currentEnd);
            openModal('rescheduleModal');
        }

        async function executeReschedule() {
            const sessionId = document.getElementById('reschSessionId').value;
            const classId = document.getElementById('reschClassId').value;
            const start = document.getElementById('reschStart').value;
            const end = document.getElementById('reschEnd').value;
            const btn = document.getElementById('executeReschBtn');
            const ogHtml = btn.innerHTML;

            if (!start || !end) { showToast('Please select both start and end times.', 'error'); return; }

            btn.innerHTML = 'UPDATING...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/session/${sessionId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ start_time: start, end_time: end, status: 'scheduled' })
                });
                const data = await res.json();
                if (data.success) {
                    logActivity('UPDATE', `session#${sessionId}.reschedule_man`);
                    showToast('Session rescheduled successfully.', 'success');
                    closeModal('rescheduleModal');
                    openSessionsModal(classId, document.getElementById('sessionsModalSubtitle').textContent);
                } else {
                    showToast(data.error || 'Failed to reschedule.', 'error');
                }
            } catch (err) {
                showToast('Network error.', 'error');
            } finally {
                btn.innerHTML = ogHtml;
                btn.disabled = false;
            }
        }

        async function openSessionDetail(sessionId) {
            const list = document.getElementById('sdmList');
            const stats = document.getElementById('sdmStats');
            list.innerHTML = '<div style="text-align:center; padding:40px; color:var(--muted)">Fetching arrival logs...</div>';
            stats.innerHTML = '';
            openModal('sessionDetailModal');

            try {
                const res = await fetch(`/api/admin/session/${sessionId}/attendance`);
                const data = await res.json();

                document.getElementById('sdmTitle').textContent = data.session_name;

                stats.innerHTML = `
                                    <div>
                                        <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:2px">PRESENCE RATIO</div>
                                        <div style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--accent)">${data.present_count} / ${data.total_count}</div>
                                    </div>
                                    <div style="width:1px; background:var(--border)"></div>
                                    <div>
                                        <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:2px">EXCUSED</div>
                                        <div style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--accent)">${data.excused_count || 0}</div>
                                    </div>
                                    <div style="width:1px; background:var(--border)"></div>
                                    <div>
                                        <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase; margin-bottom:2px">EFFICIENCY</div>
                                        <div style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--green)">${data.total_count > 0 ? Math.round((data.present_count / data.total_count) * 100) : 0}%</div>
                                    </div>
                                `;

                list.innerHTML = data.data.map(row => {
                    const isPresent = row.status === 'PRESENT' || row.status === 'LATE';
                    const isExcused = row.status === 'EXCUSED';
                    const statusClr = isPresent ? (row.status === 'LATE' ? 'var(--amber)' : 'var(--green)') : (isExcused ? 'var(--accent)' : 'var(--red)');

                    return `
                                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid color-mix(in srgb, var(--border) 27%, transparent); cursor:pointer" onmouseover="this.style.background='var(--surface3)'; this.querySelector('.s-name').style.color='var(--accent)'" onmouseout="this.style.background='transparent'; this.querySelector('.s-name').style.color='var(--text)'" onclick="openStudentRecordModal(${row.id})">
                                            <div style="display:flex; align-items:center; gap:12px">
                                                <div style="width:32px; height:32px; border-radius:50%; background:${statusClr}15; color:${statusClr}; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px">
                                                    ${row.name.charAt(0)}
                                                </div>
                                                <div>
                                                    <div class="s-name" style="font-size:12px; font-weight:700; color:var(--text); transition:color 0.2s">${row.name}</div>
                                                    <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted)">${row.student_code}</div>
                                                    ${isExcused && row.permission_reason ? `<div style="font-family:var(--font-mono); font-size:8px; color:var(--accent); margin-top:2px">📋 ${row.permission_reason}${row.permission_type ? ' (' + row.permission_type.toUpperCase() + ')' : ''}</div>` : ''}
                                                </div>
                                            </div>
                                            <div style="text-align:right">
                                                <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:${statusClr}">${row.status}</div>
                                                <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted)">${row.check_in_time}</div>
                                            </div>
                                        </div>
                                    `;
                }).join('');
            } catch (e) {
                list.innerHTML = '<div style="text-align:center; padding:40px; color:var(--red)">Error retrieving session details.</div>';
            }
        }

        async function openStudentRecordModal(studentId) {
            const hist = document.getElementById('smHistory');
            hist.innerHTML = '<div style="text-align:center; padding:30px; font-size:11px; color:var(--muted)">RETRIEVING PROFILE...</div>';
            openModal('studentDetailModal');

            try {
                const res = await fetch(`/api/admin/students/${studentId}/attendance`);
                const data = await res.json();
                const s = data.student;

                // Populate Header
                document.getElementById('smName').textContent = s.name;
                document.getElementById('smCode').textContent = s.student_code;
                document.getElementById('smInitials').textContent = s.name.split(' ').map(n => n[0]).join('').substring(0, 2);

                // Populate Analytics
                document.getElementById('smMajor').textContent = s.major || 'N/A';
                document.getElementById('smDept').textContent = s.department || 'N/A';
                const yrVal = s.year_level || 1;
                const suf = (['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'][yrVal % 10] || 'th');
                document.getElementById('smYear').textContent = yrVal + ((yrVal % 100 >= 11 && yrVal % 100 <= 13) ? 'th' : suf) + ' YEAR';
                document.getElementById('smStatusBadge').textContent = s.status.toUpperCase() + ' STUDENT';
                document.getElementById('smRate').textContent = s.attendance_rate + '%';
                document.getElementById('smJoinedDate').textContent = 'JOINED AT ' + s.joined_at;

                // Populate History
                if (data.history.length === 0) {
                    hist.innerHTML = '<div style="text-align:center; padding:20px; font-size:10px; color:var(--muted); font-family:var(--font-mono)">NO RECENT RECORDS FOUND</div>';
                    return;
                }

                hist.innerHTML = data.history.map(row => {
                    const isPresent = row.status === 'PRESENT' || row.status === 'LATE';
                    const isExcused = row.status === 'EXCUSED';
                    const color = isPresent ? 'var(--green)' : (isExcused ? 'var(--accent)' : 'var(--red)');

                    return `
                                        <div style="display:flex; align-items:center; justify-content:space-between; background:var(--surface); padding:10px 14px; border-radius:12px; border:1px solid var(--border)">
                                            <div style="display:flex; align-items:center; gap:10px">
                                                <div style="width:8px; height:8px; border-radius:50%; background:${color}"></div>
                                                <div>
                                                    <div style="font-size:11px; font-weight:700; color:var(--text)">${row.subject}</div>
                                                    <div style="font-size:9px; color:var(--muted)">${row.date}</div>
                                                    ${isExcused && row.permission_reason ? `<div style="font-size:8px; color:var(--accent); font-family:var(--font-mono); margin-top:2px">📋 ${row.permission_reason}</div>` : ''}
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


        async function autoMoveToEnd(sessionId, classId) {
            const btn = event.currentTarget;
            const ogHtml = btn.innerHTML;
            btn.innerHTML = '...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/admin/session/${sessionId}/status-update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ status: 'skipped', reschedule: true })
    });
                    const data = await res.json();
                    if (data.success) {
                        logActivity('UPDATE', `session#${sessionId}.auto_move`);
                        showToast('Session moved to end of semester.', 'success');
                        openSessionsModal(classId, document.getElementById('sessionsModalSubtitle').textContent);
                    } else {
                        showToast(data.error || 'Failed to move.', 'error');
                    }
                } catch (err) {
                    showToast('Network error.', 'error');
                } finally {
                    btn.innerHTML = ogHtml;
                    btn.disabled = false;
                }
            }
        </script>
        {{-- ═══ GLOBAL SKIP MODAL ═══ --}}
        <div id="globalSkipModal" class="modal-overlay" style="z-index: 1400;">
            <div class="modal-box" style="max-width:450px; border-radius:24px; overflow:hidden;">
                <div class="modal-head"
                    style="padding: 24px 28px; background: var(--surface2); border-bottom: 1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:15px">
                        <div
                            style="width:40px;height:40px;border-radius:12px;background:color-mix(in srgb, var(--red) 13%, transparent);color:var(--red);display:flex;align-items:center;justify-content:center;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="modal-title" style="font-weight: 800; font-size: 16px;">Global Range Skip</div>
                            <div
                                style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:0.02em;text-transform:uppercase">
                                BATCH CANCEL SESSIONS</div>
                        </div>
                    </div>
                    <button onclick="closeModal('globalSkipModal')" class="modal-close"
                        style="background:var(--surface3); width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer;">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body" style="padding:28px;">
                    <div class="form-group" style="margin-bottom:20px">
                        <label class="form-label">Range Start <span class="req">*</span></label>
                        <input id="gsStart" class="form-input" type="datetime-local"
                            style="background:var(--surface3); width:100%; height:42px; border-radius:10px; border:1px solid var(--border); padding:0 12px; color:var(--text)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Range End <span class="req">*</span></label>
                        <input id="gsEnd" class="form-input" type="datetime-local"
                            style="background:var(--surface3); width:100%; height:42px; border-radius:10px; border:1px solid var(--border); padding:0 12px; color:var(--text)">
                    </div>

                    <div
                        style="margin-top:24px; padding:15px; border-radius:12px; background:color-mix(in srgb, var(--red) 3%, transparent); border:1px dashed color-mix(in srgb, var(--red) 20%, transparent);">
                        <p style="font-size:10px; color:var(--red); line-height:1.4; font-family:var(--font-mono)">
                            <span style="font-weight:800">WARNING:</span> This will mark every session from all subjects within
                            this range as "SKIPPED".
                        </p>
                    </div>

                    <div
                        style="margin-top:20px; display:flex; align-items:center; gap:12px; padding:12px; background:var(--surface3); border-radius:12px; border:1px solid var(--border)">
                        <input type="checkbox" id="gsReschedule"
                            style="width:18px; height:18px; cursor:pointer; accent-color:var(--accent)">
                        <label for="gsReschedule"
                            style="font-size:11px; font-weight:700; color:var(--text2); cursor:pointer">Automatically move
                            skipped sessions to end of semester?</label>
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--surface2); padding: 18px 28px;">
                    <button id="executeGlobalSkipBtn" class="btn-primary"
                        style="width:100%; height:46px; background:var(--red); border:none; font-weight:800; border-radius:12px; font-size:11px; letter-spacing:0.02em"
                        onclick="handleGlobalSkip()">EXECUTE BATCH SKIP</button>
                </div>
            </div>
        </div>

        <script>
        function toggleSelectAllClasses(checked) {
            document.querySelectorAll('.class-checkbox').forEach(cb => {
                if (!cb.disabled) cb.checked = checked;
            });
            updateBulkDeleteUI();
        }

        function updateBulkDeleteUI() {
            const checked = document.querySelectorAll('.class-checkbox:checked');
            const toolbar = document.getElementById('bulkActionsToolbar');
            const countLabel = document.getElementById('selectedClassesCount');
            
            if (checked.length > 0) {
                toolbar.style.display = 'flex';
                countLabel.textContent = courseCountText('classes_selected', checked.length, ':count CLASSES SELECTED');
            } else {
                toolbar.style.display = 'none';
            }
        }

        function confirmBulkDeleteClasses() {
            const checked = [...document.querySelectorAll('.class-checkbox:checked')];
            if (checked.length === 0) return;
            
            const count = checked.length;
            document.getElementById('bulkDeleteModalCount').textContent = courseCountText(count === 1 ? 'selected_class' : 'selected_classes', count, ':count Selected Classes');
            openModal('bulkDeleteModal');
        }

        async function executeBulkDeleteClasses() {
            const checked = [...document.querySelectorAll('.class-checkbox:checked')];
            if (checked.length === 0) return;
            
            const ids = checked.map(cb => parseInt(cb.dataset.id));
            
            const btn = document.getElementById('confirmBulkDeleteBtn');
            const ogText = btn.textContent;
            btn.textContent = courseText('deleting', 'DELETING...');
            btn.disabled = true;

            try {
                const res = await fetch('/api/admin/classes/bulk-delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ class_ids: ids })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    closeModal('bulkDeleteModal');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.error || 'Bulk delete failed', 'error');
                    btn.textContent = ogText;
                    btn.disabled = false;
                }
            } catch (e) {
                showToast(courseText('network_error_plain', 'Network error'), 'error');
                btn.textContent = ogText;
                btn.disabled = false;
            }
        }

        document.getElementById('confirmBulkDeleteBtn').addEventListener('click', executeBulkDeleteClasses);

            async function handleGlobalSkip() {
                const start = document.getElementById('gsStart').value;
                const end = document.getElementById('gsEnd').value;
                const reschedule = document.getElementById('gsReschedule').checked;
                const btn = document.getElementById('executeGlobalSkipBtn');
                const ogHtml = btn.innerHTML;

                if (!start || !end) { showToast('Please select both start and end range.', 'error'); return; }

                const confirmMsg = reschedule
                    ? 'Are you absolutely sure? This will MOVE multiple sessions to the end of the semester across all subjects.'
                    : 'Are you absolutely sure? This will SKIP multiple sessions across the entire system.';

                if (!await confirmAction(confirmMsg)) return;

                btn.innerHTML = 'PROCESSING...';
                btn.disabled = true;

                try {
                    const res = await fetch('/api/admin/sessions/global-skip', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ start_time: start, end_time: end, reschedule: reschedule })
                    });
                    const data = await res.json();
                    if (data.success) {
                        logActivity(reschedule ? 'UPDATE' : 'UPDATE', `global.batch_skip#${data.affected_count}`);
                        showToast(data.message, 'success');
                        closeModal('globalSkipModal');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(data.error || 'Failed to execute global skip.', 'error');
                    }
                } catch (err) {
                    showToast('Network error.', 'error');
                } finally {
                    btn.innerHTML = ogHtml;
                    btn.disabled = false;
                }
            }
        </script>
@endsection
