@extends('layouts.app')

@section('content')
@php
    $statusConfig = [
        'late'             => ['bg'=>'rgba(245,158,11,.1)',  'color'=>'var(--amber)',   'border'=>'rgba(245,158,11,.25)',  'label'=>'LATE'],
        'very_late'        => ['bg'=>'rgba(251,146,60,.1)',  'color'=>'var(--orange)',  'border'=>'rgba(251,146,60,.25)',  'label'=>'VERY LATE'],
        'early_leave'      => ['bg'=>'rgba(245,158,11,.1)',  'color'=>'var(--amber)',   'border'=>'rgba(245,158,11,.25)',  'label'=>'EARLY LEAVE'],
        'missing_check_out'=> ['bg'=>'rgba(245,158,11,.1)',  'color'=>'var(--amber)',   'border'=>'rgba(245,158,11,.25)',  'label'=>'MISSING CHECK-OUT'],
        'absent'           => ['bg'=>'rgba(239,68,68,.1)',   'color'=>'var(--red)',     'border'=>'rgba(239,68,68,.25)',   'label'=>'ABSENT'],
        'completed'        => ['bg'=>'rgba(37,99,235,.1)',   'color'=>'var(--accent)',  'border'=>'rgba(37,99,235,.25)',   'label'=>'COMPLETED'],
        'present'          => ['bg'=>'rgba(34,197,94,.1)',   'color'=>'var(--green)',   'border'=>'rgba(34,197,94,.25)',   'label'=>'PRESENT'],
        'on_time'          => ['bg'=>'rgba(34,197,94,.1)',   'color'=>'var(--green)',   'border'=>'rgba(34,197,94,.25)',   'label'=>'ON TIME'],
        'teaching'         => ['bg'=>'rgba(16,185,129,.1)',  'color'=>'var(--emerald)', 'border'=>'rgba(16,185,129,.25)',  'label'=>'TEACHING'],
        'permission'       => ['bg'=>'rgba(139,92,246,.1)', 'color'=>'var(--violet)',  'border'=>'rgba(139,92,246,.25)',  'label'=>'PERMISSION'],
        'cancelled'        => ['bg'=>'rgba(100,116,139,.1)','color'=>'var(--muted2)',  'border'=>'rgba(100,116,139,.25)', 'label'=>'CANCELLED'],
        'rescheduled'      => ['bg'=>'rgba(56,189,248,.1)', 'color'=>'var(--accent2)', 'border'=>'rgba(56,189,248,.25)',  'label'=>'RESCHEDULED'],
    ];
@endphp

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>TEACHERS</span>
                <span class="breadcrumb-sep">/</span>
                <a href="{{ route('admin.teacher-attendance') }}" style="color:var(--muted);cursor:pointer;">ATTENDANCE</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">REPORTS</span>
            </div>
            <h1 class="page-title">Attendance Reports</h1>
            <p class="page-subtitle">DAILY · MONTHLY · SEMESTER · TEACHING HOURS REPORTING BASE</p>
        </div>
        <a href="{{ route('admin.teacher-attendance') }}" class="btn-secondary" style="gap:7px;">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            BACK TO MONITORING
        </a>
    </div>

    {{-- ═══ FLASH ═══ --}}
    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:var(--green);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;margin-bottom:20px;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:var(--red);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;margin-bottom:20px;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ═══ FILTER FORM PANEL ═══ --}}
    <div class="panel" style="padding:16px 20px;margin-bottom:20px;">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;width:100%;">
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-family:var(--font-mono);font-size:10px;color:var(--muted2);letter-spacing:.08em;font-weight:700;">FROM:</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-input" style="height:36px;padding:0 12px;font-family:var(--font-mono);font-size:12px;width:150px;color-scheme:var(--data-theme,light);">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-family:var(--font-mono);font-size:10px;color:var(--muted2);letter-spacing:.08em;font-weight:700;">TO:</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-input" style="height:36px;padding:0 12px;font-family:var(--font-mono);font-size:12px;width:150px;color-scheme:var(--data-theme,light);">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-family:var(--font-mono);font-size:10px;color:var(--muted2);letter-spacing:.08em;font-weight:700;">TEACHER:</label>
                <select name="teacher_id" class="form-input" style="height:36px;padding:0 12px;font-size:12px;width:200px;">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>
                            {{ $teacher->user->name ?? 'Teacher #'.$teacher->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary" style="height:36px;padding:0 18px;font-size:10px;">GENERATE REPORT</button>
            @if(request()->hasAny(['from','to','teacher_id']))
                <a href="{{ route('admin.teacher-attendance.reports') }}" class="btn-secondary" style="height:36px;padding:0 14px;font-size:10px;">RESET</a>
            @endif

            <div style="width:1px; height:24px; background:var(--border); margin:0 4px;"></div>

            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.teacher-attendance.reports.export.pdf', request()->all()) }}" class="btn-secondary"
                    style="height:36px; width:36px; padding:0; border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--text2);"
                    title="Download PDF Report">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                </a>
                <button type="submit" formaction="{{ route('admin.teacher-attendance.reports.send-telegram') }}" formmethod="POST" class="btn-primary"
                    style="height:36px; width:36px; padding:0; border-radius:10px; background:var(--accent); border:none; display:flex; align-items:center; justify-content:center; color:white; cursor:pointer;"
                    title="Send PDF Report to Telegram">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

    {{-- ═══ SUMMARY STATS GRID ═══ --}}
    <div class="stats-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:20px;">
        @php
            $summaryColorConfig = [
                'total_sessions'    => ['color'=>'var(--accent)',  'glow'=>'blue'],
                'present_sessions'  => ['color'=>'var(--green)',   'glow'=>'green'],
                'absent_sessions'   => ['color'=>'var(--red)',     'glow'=>'red'],
                'late_sessions'     => ['color'=>'var(--amber)',   'glow'=>'amber'],
                'teaching_hours'    => ['color'=>'var(--violet)',  'glow'=>'violet']
            ];
        @endphp
        @foreach($summary as $label => $value)
            @php
                $cfg = $summaryColorConfig[$label] ?? ['color'=>'var(--text)', 'glow'=>'blue'];
                $labelFormatted = strtoupper(str_replace('_', ' ', $label));
            @endphp
            <div class="stat-card {{ $cfg['glow'] }}">
                <div class="stat-glow"></div>
                <div class="stat-label">{{ $labelFormatted }}</div>
                <div class="stat-value" style="color:{{ $cfg['color'] }};margin-top:10px;">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- ═══ TABLE PANEL ═══ --}}
    <div class="panel">
        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent);animation:blink 2s infinite;"></div>
                <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);">REPORT ENTRIES</span>
            </div>
            <div class="toolbar-count">
                <span>{{ $sessions->count() }}</span> SESSIONS
            </div>
        </div>

        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>TEACHER</th>
                        <th>SUBJECT / CLASS</th>
                        <th>STATUS</th>
                        <th>TIMING DETAILS</th>
                        <th style="text-align:right;">TEACHING HOURS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        @php
                            $sc = $statusConfig[$session->attendance_status] ?? ['bg'=>'rgba(100,116,139,.1)','color'=>'var(--muted2)','border'=>'rgba(100,116,139,.25)','label'=>strtoupper(str_replace('_',' ',$session->attendance_status))];
                            $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                            $clr = $avatarColors[$session->id % count($avatarColors)];
                            $tName = $session->teacher->user->name ?? 'Unknown';
                        @endphp
                        <tr class="fade-up">
                            {{-- Date --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $session->attendance_date?->format('M d, Y') }}
                                </div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px;">
                                    {{ $session->attendance_date?->format('l') }}
                                </div>
                            </td>

                            {{-- Teacher --}}
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-avatar"
                                        style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}44;font-size:10px;width:34px;height:34px;border-radius:50%;">
                                        {{ strtoupper(substr($tName, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="subject-name">{{ $tName }}</div>
                                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;">
                                            {{ $session->teacher->department->name ?? 'No dept.' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Subject --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $session->subject->name ?? '-' }}
                                </div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px;letter-spacing:.04em;">
                                    {{ $session->classGroup->name ?? $session->classRoom->name ?? 'No group' }}
                                </div>
                            </td>

                            {{-- Status badge --}}
                            <td>
                                <span style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border:1px solid {{ $sc['border'] }};">
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            {{-- Timing Details --}}
                            <td>
                                <div style="font-family:var(--font-mono);font-size:10px;color:var(--text2);display:flex;flex-direction:column;gap:3px;">
                                    <div>
                                        <span style="color:var(--muted2);">SCHED:</span> {{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}
                                    </div>
                                    <div>
                                        <span style="color:var(--muted2);">ACTUAL:</span> {{ $session->check_in_time?->format('H:i') ?? '-' }} - {{ $session->check_out_time?->format('H:i') ?? '-' }}
                                    </div>
                                </div>
                            </td>

                            {{-- Hours --}}
                            <td style="text-align:right;">
                                <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;color:var(--accent);">
                                    {{ number_format($session->actual_teaching_hours, 1) }}
                                </span>
                                <span style="font-size:10px;color:var(--muted);margin-left:2px;">hrs</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 17v-2a2 2 0 00-2-2H5a2 2 0 00-2 2v2m6-12h10m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="empty-title">No Report Entries</div>
                                    <div class="empty-desc">No sessions matching the selected filter criteria could be found.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
