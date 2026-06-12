@extends('layouts.app')

@section('content')
    @php
        $statusConfig = [
            'late' => [
                'bg' => 'rgba(245,158,11,.1)',
                'color' => 'var(--amber)',
                'border' => 'rgba(245,158,11,.25)',
                'label' => __('admin_teacher_attendance.status.late'),
            ],
            'very_late' => [
                'bg' => 'rgba(251,146,60,.1)',
                'color' => 'var(--orange)',
                'border' => 'rgba(251,146,60,.25)',
                'label' => __('admin_teacher_attendance.status.very_late'),
            ],
            'early_leave' => [
                'bg' => 'rgba(245,158,11,.1)',
                'color' => 'var(--amber)',
                'border' => 'rgba(245,158,11,.25)',
                'label' => __('admin_teacher_attendance.status.early_leave'),
            ],
            'missing_check_out' => [
                'bg' => 'rgba(245,158,11,.1)',
                'color' => 'var(--amber)',
                'border' => 'rgba(245,158,11,.25)',
                'label' => __('admin_teacher_attendance.status.missing_check_out'),
            ],
            'absent' => [
                'bg' => 'rgba(239,68,68,.1)',
                'color' => 'var(--red)',
                'border' => 'rgba(239,68,68,.25)',
                'label' => __('admin_teacher_attendance.status.absent'),
            ],
            'completed' => [
                'bg' => 'rgba(37,99,235,.1)',
                'color' => 'var(--accent)',
                'border' => 'rgba(37,99,235,.25)',
                'label' => __('admin_teacher_attendance.status.completed'),
            ],
            'present' => [
                'bg' => 'rgba(34,197,94,.1)',
                'color' => 'var(--green)',
                'border' => 'rgba(34,197,94,.25)',
                'label' => __('admin_teacher_attendance.status.present'),
            ],
            'on_time' => [
                'bg' => 'rgba(34,197,94,.1)',
                'color' => 'var(--green)',
                'border' => 'rgba(34,197,94,.25)',
                'label' => __('admin_teacher_attendance.status.on_time'),
            ],
            'teaching' => [
                'bg' => 'rgba(16,185,129,.1)',
                'color' => 'var(--emerald)',
                'border' => 'rgba(16,185,129,.25)',
                'label' => __('admin_teacher_attendance.status.teaching'),
            ],
            'permission' => [
                'bg' => 'rgba(139,92,246,.1)',
                'color' => 'var(--violet)',
                'border' => 'rgba(139,92,246,.25)',
                'label' => __('admin_teacher_attendance.status.permission'),
            ],
            'cancelled' => [
                'bg' => 'rgba(100,116,139,.1)',
                'color' => 'var(--muted2)',
                'border' => 'rgba(100,116,139,.25)',
                'label' => __('admin_teacher_attendance.status.cancelled'),
            ],
            'rescheduled' => [
                'bg' => 'rgba(56,189,248,.1)',
                'color' => 'var(--accent2)',
                'border' => 'rgba(56,189,248,.25)',
                'label' => __('admin_teacher_attendance.status.rescheduled'),
            ],
        ];
    @endphp

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>{{ __('admin_teacher_attendance.breadcrumb_teachers') }}</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">{{ __('admin_teacher_attendance.breadcrumb_attendance') }}</span>
            </div>
            <h1 class="page-title">{{ __('admin_teacher_attendance.title') }}</h1>
            <p class="page-subtitle">{{ __('admin_teacher_attendance.subtitle') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.teacher-attendance.sync') }}">
            @csrf
            <button type="submit" class="btn-primary" style="gap:8px;height:40px;padding:0 20px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ __('admin_teacher_attendance.sync_sessions') }}
            </button>
        </form>
    </div>

    {{-- ═══ FLASH ═══ --}}
    @if (session('success'))
        <div
            style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:var(--green);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ═══ STAT CARDS ═══ --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card blue">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="stat-label">{{ __('admin_teacher_attendance.scheduled') }}</div>
            <div class="stat-value">{{ $stats['scheduled'] }}</div>
            <span class="stat-pill pill-blue">{{ __('admin_teacher_attendance.sessions_today') }}</span>
        </div>
        <div class="stat-card green">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">{{ __('admin_teacher_attendance.present') }}</div>
            <div class="stat-value">{{ $stats['present'] }}</div>
            <span class="stat-pill pill-up">{{ __('admin_teacher_attendance.on_time') }}</span>
        </div>
        <div class="stat-card amber">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">{{ __('admin_teacher_attendance.late') }}</div>
            <div class="stat-value">{{ $stats['late'] }}</div>
            <span class="stat-pill pill-amber">{{ __('admin_teacher_attendance.checked_in_late') }}</span>
        </div>
        <div class="stat-card red">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-label">{{ __('admin_teacher_attendance.absent') }}</div>
            <div class="stat-value">{{ $stats['absent'] }}</div>
            <span class="stat-pill pill-down">{{ __('admin_teacher_attendance.no_check_in') }}</span>
        </div>
    </div>

    {{-- ═══ SECONDARY STATS ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @php
            $miniStats = [
                [
                    'label' => __('admin_teacher_attendance.teaching_now'),
                    'value' => $stats['teaching'],
                    'color' => 'var(--emerald)',
                    'hint' => __('admin_teacher_attendance.currently_in_session'),
                ],
                [
                    'label' => __('admin_teacher_attendance.completed'),
                    'value' => $stats['completed'],
                    'color' => 'var(--accent)',
                    'hint' => __('admin_teacher_attendance.sessions_finished'),
                ],
                [
                    'label' => __('admin_teacher_attendance.missing_check_out'),
                    'value' => $stats['missing_checkout'],
                    'color' => 'var(--amber)',
                    'hint' => __('admin_teacher_attendance.did_not_check_out'),
                ],
                [
                    'label' => __('admin_teacher_attendance.pending_requests'),
                    'value' => $stats['pending_corrections'] + $stats['pending_changes'],
                    'color' => 'var(--violet)',
                    'hint' => __('admin_teacher_attendance.awaiting_approval'),
                ],
            ];
        @endphp
        @foreach ($miniStats as $ms)
            <div class="stat-card" style="border-top:2px solid {{ $ms['color'] }};">
                <div class="stat-label">{{ $ms['label'] }}</div>
                <div
                    style="font-family:var(--font-display);font-size:28px;font-weight:800;color:{{ $ms['color'] }};line-height:1;margin:8px 0;">
                    {{ $ms['value'] }}</div>
                <span
                    style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">{{ $ms['hint'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- ═══ FILTERS + NAV ═══ --}}
    <div class="panel" style="padding:0;">
        <div
            style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);">

            {{-- Date / Teacher / Status filters --}}
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-input"
                    style="width:160px;height:36px;padding:0 12px;font-family:var(--font-mono);font-size:12px;color-scheme:var(--data-theme,light);">

                <select name="teacher_id" class="form-input"
                    style="width:180px;height:36px;padding:0 12px;font-size:12px;">
                    <option value="">{{ __('admin_teacher_attendance.all_teachers') }}</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>
                            {{ $teacher->user->name ?? __('admin_teacher_attendance.teacher_number', ['id' => $teacher->id]) }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-input" style="width:180px;height:36px;padding:0 12px;font-size:12px;">
                    <option value="">{{ __('admin_teacher_attendance.all_statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ __('admin_teacher_attendance.status.' . $status) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary"
                    style="height:36px;padding:0 16px;font-size:10px;">{{ __('admin_teacher_attendance.filter') }}</button>
                @if (request()->hasAny(['date', 'teacher_id', 'status']))
                    <a href="{{ route('admin.teacher-attendance') }}" class="btn-secondary"
                        style="height:36px;padding:0 14px;font-size:10px;">{{ __('admin_teacher_attendance.clear') }}</a>
                @endif
            </form>

            {{-- Quick nav links --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.teacher-attendance.corrections') }}" class="btn-secondary"
                    style="height:36px;padding:0 14px;font-size:10px;gap:6px;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('admin_teacher_attendance.corrections') }}
                    @if ($stats['pending_corrections'] > 0)
                        <span
                            style="background:var(--red);color:#fff;border-radius:99px;font-size:8px;padding:1px 6px;font-weight:800;">
                            {{ $stats['pending_corrections'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.teacher-attendance.scan-qr', ['date' => $date->toDateString()]) }}"
                    class="btn-secondary" style="height:36px;padding:0 14px;font-size:10px;gap:6px;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 16 16" stroke="currentColor">
                        <path d="M2 2h4v4H2V2ZM10 2h4v4h-4V2ZM2 10h4v4H2v-4Z" stroke-width="1.5" />
                        <path d="M10 10h4v4h-4v-1.5h2.5V10" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                    {{ __('admin_teacher_attendance.teacher_qr') }}
                </a>
                <a href="{{ route('admin.teacher-attendance.scan-monitor', ['date' => $date->toDateString()]) }}"
                    class="btn-secondary" style="height:36px;padding:0 14px;font-size:10px;gap:6px;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 16 16" stroke="currentColor">
                        <path d="M2.5 3h11v8h-11V3Z" stroke-width="1.5" />
                        <path d="M5 13h6M8 11v2" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                    {{ __('admin_teacher_attendance.scan_monitor') }}
                </a>
                <a href="{{ route('admin.teacher-attendance.class-change') }}" class="btn-secondary"
                    style="height:36px;padding:0 14px;font-size:10px;gap:6px;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    {{ __('admin_teacher_attendance.class_changes') }}
                    @if ($stats['pending_changes'] > 0)
                        <span
                            style="background:var(--red);color:#fff;border-radius:99px;font-size:8px;padding:1px 6px;font-weight:800;">
                            {{ $stats['pending_changes'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.teacher-attendance.reports') }}" class="btn-secondary"
                    style="height:36px;padding:0 14px;font-size:10px;gap:6px;">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ __('admin_teacher_attendance.reports') }}
                </a>
            </div>
        </div>

        {{-- ═══ TABLE ═══ --}}
        <div class="table-responsive">
            <table class="att-table" id="teacherAttTable">
                <thead>
                    <tr>
                        <th>{{ __('admin_teacher_attendance.table_teacher') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_class_subject') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_schedule') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_status') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_check_in') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_check_out') }}</th>
                        <th>{{ __('admin_teacher_attendance.table_metrics') }}</th>
                        <th style="text-align:right;">{{ __('admin_teacher_attendance.table_admin_control') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        @php
                            $sc = $statusConfig[$session->attendance_status] ?? [
                                'bg' => 'rgba(100,116,139,.1)',
                                'color' => 'var(--muted2)',
                                'border' => 'rgba(100,116,139,.25)',
                                'label' => __('admin_teacher_attendance.status.' . $session->attendance_status),
                            ];
                            $avatarColors = ['#2563EB', '#22C55E', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444'];
                            $clr = $avatarColors[$session->id % count($avatarColors)];
                            $tName = $session->teacher->user->name ?? __('admin_teacher_attendance.unknown_teacher');
                        @endphp
                        <tr class="fade-up" data-attendance-session="{{ $session->id }}">
                            {{-- Teacher --}}
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-avatar"
                                        style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}44;font-size:10px;width:34px;height:34px;border-radius:50%;">
                                        {{ strtoupper(substr($tName, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="subject-name">{{ $tName }}</div>
                                        <div
                                            style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;">
                                            {{ $session->teacher->department->name ?? __('admin_teacher_attendance.no_department') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Class --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $session->subject->name ?? __('admin_teacher_attendance.no_subject') }}
                                </div>
                                <div
                                    style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px;letter-spacing:.04em;">
                                    {{ $session->classGroup->name ?? ($session->classRoom->name ?? __('admin_teacher_attendance.no_group')) }}
                                    @if ($session->room_name)
                                        · {{ $session->room_name }}
                                    @endif
                                </div>
                            </td>

                            {{-- Schedule --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $session->scheduled_start_time?->format('M d, Y') }}
                                </div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px;">
                                    {{ $session->scheduled_start_time?->format('H:i') }} –
                                    {{ $session->scheduled_end_time?->format('H:i') }}
                                </div>
                            </td>

                            {{-- Status badge --}}
                            <td>
                                <span data-attendance-field="status"
                                    style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['border'] }};">
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            {{-- Check-in --}}
                            <td>
                                <span data-attendance-field="check_in"
                                    style="font-family:var(--font-mono);font-size:12px;color:{{ $session->check_in_time ? 'var(--green)' : 'var(--muted)' }};font-weight:600;">
                                    {{ $session->check_in_time?->format('H:i') ?? '—' }}
                                </span>
                            </td>

                            {{-- Check-out --}}
                            <td>
                                <span data-attendance-field="check_out"
                                    style="font-family:var(--font-mono);font-size:12px;color:{{ $session->check_out_time ? 'var(--accent2)' : 'var(--muted)' }};font-weight:600;">
                                    {{ $session->check_out_time?->format('H:i') ?? '—' }}
                                </span>
                            </td>

                            {{-- Metrics --}}
                            <td>
                                <div
                                    style="display:flex;flex-direction:column;gap:3px;font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;">
                                    @if ($session->late_minutes > 0)
                                        <span style="color:var(--amber);">↓ {{ __('admin_teacher_attendance.minutes_late', ['minutes' => $session->late_minutes]) }}</span>
                                    @endif
                                    @if ($session->early_leave_minutes > 0)
                                        <span style="color:var(--orange);">↑ {{ __('admin_teacher_attendance.minutes_early', ['minutes' => $session->early_leave_minutes]) }}</span>
                                    @endif
                                    @if ($session->actual_teaching_hours)
                                        <span style="color:var(--muted2);">{{ __('admin_teacher_attendance.hours_taught', ['hours' => $session->actual_teaching_hours]) }}</span>
                                    @endif
                                    @if (!$session->late_minutes && !$session->early_leave_minutes && !$session->actual_teaching_hours)
                                        <span>—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Admin control --}}
                            <td style="text-align:right;">
                                <form method="POST"
                                    action="{{ route('admin.teacher-attendance.sessions.update', $session) }}"
                                    style="display:flex;gap:6px;align-items:center;justify-content:flex-end;flex-wrap:wrap;">
                                    @csrf
                                    @method('PUT')
                                    <select name="attendance_status" class="form-input"
                                        style="height:32px;padding:0 8px;font-size:10px;min-width:130px;font-family:var(--font-mono);">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($session->attendance_status === $status)>
                                                {{ __('admin_teacher_attendance.status.' . $status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="datetime-local" name="check_in_time"
                                        value="{{ $session->check_in_time?->format('Y-m-d\TH:i') }}" class="form-input"
                                        style="height:32px;padding:0 8px;font-size:10px;width:152px;color-scheme:light dark;">
                                    <input type="datetime-local" name="check_out_time"
                                        value="{{ $session->check_out_time?->format('Y-m-d\TH:i') }}" class="form-input"
                                        style="height:32px;padding:0 8px;font-size:10px;width:152px;color-scheme:light dark;">
                                    <button type="submit" class="btn-primary"
                                        style="height:32px;padding:0 14px;font-size:9px;letter-spacing:.08em;">{{ __('admin_teacher_attendance.save') }}</button>
                                    @if ($session->session_number === 1)
                                        <a href="{{ route('admin.teacher-attendance.sessions.qr-token', $session) }}"
                                            class="btn-secondary"
                                            style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;">{{ __('admin_teacher_attendance.qr') }}</a>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="empty-title">{{ __('admin_teacher_attendance.empty_title') }}</div>
                                    <div class="empty-desc">{{ __('admin_teacher_attendance.empty_desc') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($sessions->hasPages())
            <div
                style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    {{ __('admin_teacher_attendance.showing_range', ['first' => $sessions->firstItem(), 'last' => $sessions->lastItem(), 'total' => $sessions->total()]) }}
                </span>
                {{ $sessions->links('vendor.pagination.academy') }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            (function() {
                const date = @json($date->toDateString());
                const attendanceStatusLabels = @json(trans('admin_teacher_attendance.status'));
                const formatTime = (value) => value ? new Date(value).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }) : '—';
                const paint = (payload) => {
                    const session = payload.session || payload;
                    const row = document.querySelector(`[data-attendance-session="${session.id}"]`);
                    if (!row) return;
                    const status = row.querySelector('[data-attendance-field="status"]');
                    const checkIn = row.querySelector('[data-attendance-field="check_in"]');
                    const checkOut = row.querySelector('[data-attendance-field="check_out"]');
                    if (status) status.textContent = attendanceStatusLabels[session.attendance_status] || String(session.attendance_status || '').replaceAll('_', ' ')
                        .toUpperCase();
                    if (checkIn) checkIn.textContent = formatTime(session.check_in_time);
                    if (checkOut) checkOut.textContent = formatTime(session.check_out_time);
                    row.style.transition = 'background .25s ease';
                    row.style.background = 'rgba(37,99,235,.08)';
                    setTimeout(() => row.style.background = '', 800);
                };

                if (window.Echo) {
                    window.Echo.channel(`teacher-attendance.${date}`)
                        .listen('.teacher.attendance.updated', paint);
                    return;
                }

                let snapshot = {};
                setInterval(async function() {
                    try {
                        const response = await fetch(`/api/admin/teacher-attendance/dashboard?date=${date}`, {
                            headers: {
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        (data.sessions || []).forEach(function(session) {
                            const signature = [session.attendance_status, session.check_in_time, session
                                .check_out_time
                            ].join('|');
                            if (snapshot[session.id] && snapshot[session.id] !== signature) paint({
                                session
                            });
                            snapshot[session.id] = signature;
                        });
                    } catch (error) {}
                }, 10000);
            })();
        </script>
    @endpush
@endsection
