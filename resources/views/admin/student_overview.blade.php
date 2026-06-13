@extends('layouts.app')

@section('content')
@php
    $sessionRate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;
    $absentNow = max(0, $totalCount - $presentCount);
    $criticalStudentCount = $topAbsentStudents->count();
    $criticalClassCount = $topAbsentClasses->count();
    $isDemoUser = Auth::user()?->email === 'demo@example.com';
    $activeSubject = $activeSession?->classRoom?->subject?->name ?? __('admin_student_overview.no_active_session');
    $activeGroups = $activeSession?->classRoom?->groups?->pluck('name')->filter()->join(', ') ?: __('admin_student_overview.no_group_selected');
    $hasSessionActivity = $presentCount > 0 || $sessionScanCount > 0;
    $monitorClasses = collect($monitorSubjects);
    $monitorAvg = $monitorClasses->count() ? round($monitorClasses->avg('progress')) : 0;
    $monitorStrongest = $monitorClasses->sortByDesc('progress')->first();
    $monitorNeedsAttention = $monitorClasses->filter(fn($class) => $class['progress'] < 60)->count();
    $selectedMajor = $selectedMajorId ? $majorOptions->firstWhere('id', $selectedMajorId) : null;
    $yearCollection = collect($yearStats);
    $yearAverage = $yearCollection->count() ? round($yearCollection->avg()) : 0;
    $topYear = $yearCollection->sortDesc()->keys()->first();
    $topYearScore = $topYear ? $yearCollection->get($topYear) : 0;
@endphp

<section class="dashboard-hero">
    <div class="dashboard-hero__content">
        <div class="breadcrumb">
            <span>{{ __('admin_student_overview.breadcrumb_students') }}</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">{{ __('admin_student_overview.breadcrumb_overview') }}</span>
        </div>
        <div class="dashboard-hero__title-row">
            <div>
                <h1 class="page-title dashboard-hero__title">{{ __('admin_student_overview.title') }}</h1>
                <p class="dashboard-hero__subtitle">
                    {{ __('admin_student_overview.subtitle') }}
                </p>
            </div>
            @if($isDemoUser)
                <div class="demo-mode-badge">
                    <span class="demo-mode-badge__dot"></span>
                    {{ __('admin_student_overview.read_only_demo') }}
                </div>
            @endif
        </div>

        <div class="dashboard-hero__meta">
            <div class="hero-meta-item"><span>{{ __('admin_student_overview.active_session') }}</span><strong>{{ $activeSubject }}</strong></div>
            <div class="hero-meta-item"><span>{{ __('admin_student_overview.assigned_group') }}</span><strong>{{ $activeGroups }}</strong></div>
            <div class="hero-meta-item"><span>{{ __('admin_student_overview.session_coverage') }}</span><strong>{{ $presentCount }}/{{ $totalCount }} {{ __('admin_student_overview.marked') }}</strong></div>
        </div>
    </div>

    <div class="dashboard-hero__status">
        <div class="session-ring" style="--value: {{ $sessionRate }};">
            <div class="session-ring__inner">
                <strong>{{ $sessionRate }}%</strong>
                <span>{{ $hasSessionActivity ? __('admin_student_overview.session') : __('admin_student_overview.waiting_data') }}</span>
            </div>
        </div>
        <div class="session-status-list">
            <div><span class="status-dot status-dot--green"></span><span>{{ __('admin_student_overview.present_or_excused') }}</span><strong>{{ $presentCount }}</strong></div>
            <div><span class="status-dot status-dot--red"></span><span>{{ __('admin_student_overview.not_marked') }}</span><strong>{{ $absentNow }}</strong></div>
            <div><span class="status-dot status-dot--blue"></span><span>{{ __('admin_student_overview.qr_scans') }}</span><strong>{{ $sessionScanCount }}</strong></div>
            @unless($hasSessionActivity)
                <p class="session-status-note">{{ __('admin_student_overview.waiting_note') }}</p>
            @endunless
        </div>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-glow"></div><div class="stat-label">{{ __('admin_student_overview.active_students') }}</div><div class="stat-value">{{ $stats['students'] }}</div><div class="stat-pill pill-blue">{{ __('admin_student_overview.enrolled') }}</div></div>
    <div class="stat-card green"><div class="stat-glow"></div><div class="stat-label">{{ __('admin_student_overview.total_attendance') }}</div><div class="stat-value">{{ $stats['attendance_rate'] }}</div><div class="stat-pill pill-up">{{ __('admin_student_overview.health_rate') }}</div></div>
    <div class="stat-card amber"><div class="stat-glow"></div><div class="stat-label">{{ __('admin_student_overview.pending_sessions') }}</div><div class="stat-value">{{ $stats['pending_sessions'] }}</div><div class="stat-pill pill-amber">{{ __('admin_student_overview.in_queue') }}</div></div>
    <div class="stat-card red"><div class="stat-glow"></div><div class="stat-label">{{ __('admin_student_overview.absence_rate') }}</div><div class="stat-value">{{ $stats['absence_rate'] }}</div><div class="stat-pill pill-down">{{ __('admin_student_overview.students_flagged', ['count' => $criticalStudentCount]) }}</div></div>
</div>

<section class="summary-workbench">
    <div class="summary-panel summary-panel--wide">
        <div class="summary-panel__head">
            <div><span class="summary-eyebrow">{{ __('admin_student_overview.operational_snapshot') }}</span><h2>{{ __('admin_student_overview.posture_title') }}</h2></div>
            <span class="summary-chip">{{ __('admin_student_overview.sessions_visible', ['count' => $classes->count()]) }}</span>
        </div>
        <div class="summary-metrics">
            <div class="summary-metric"><span>{{ __('admin_student_overview.live_upcoming_sessions') }}</span><strong>{{ $stats['pending_sessions'] }}</strong></div>
            <div class="summary-metric"><span>{{ __('admin_student_overview.students_in_selected_session') }}</span><strong>{{ $totalCount }}</strong></div>
            <div class="summary-metric"><span>{{ __('admin_student_overview.critical_students') }}</span><strong>{{ $criticalStudentCount }}</strong></div>
            <div class="summary-metric"><span>{{ __('admin_student_overview.critical_classes') }}</span><strong>{{ $criticalClassCount }}</strong></div>
        </div>
    </div>

    <div class="summary-panel">
        <div class="summary-panel__head">
            <div><span class="summary-eyebrow">{{ __('admin_student_overview.modules') }}</span><h2>{{ __('admin_student_overview.quick_access') }}</h2></div>
        </div>
        <div class="summary-actions">
            <a href="{{ route('admin.students') }}">{{ __('admin_student_overview.students') }}</a>
            <a href="{{ route('admin.permissions') }}">{{ __('admin_student_overview.permissions') }}</a>
            <a href="{{ route('admin.classes') }}">{{ __('admin_student_overview.student_groups') }}</a>
            <a href="{{ route('admin.attendance-issues') }}">{{ __('admin_student_overview.attendance_issues') }}</a>
        </div>
    </div>
</section>

<div class="main-grid">
    <div class="left-col">
        <div class="panel">
            <div class="panel-head">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:blink 2s infinite;"></div>
                    <span class="panel-title">{{ __('admin_student_overview.live_monitoring') }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:15px;">
                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted2);">{{ $activeSession?->classRoom?->subject?->name ? strtoupper($activeSession->classRoom->subject->name) : __('admin_student_overview.no_session') }}</span>
                    <div style="display:flex;align-items:center;gap:5px;background:var(--surface3);padding:2px 8px;border-radius:4px;border:1px solid var(--border);">
                        <span style="font-family:var(--font-mono);font-size:9px;color:var(--green);font-weight:800;">{{ $presentCount }}/{{ $totalCount }}</span>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height:450px;overflow-y:auto;">
                <table class="att-table">
                    <thead><tr><th>{{ __('admin_student_overview.student_identity') }}</th><th>{{ __('admin_student_overview.code') }}</th><th>{{ __('admin_student_overview.time') }}</th><th>{{ __('admin_student_overview.status') }}</th><th style="text-align:right">{{ __('admin_student_overview.method') }}</th></tr></thead>
                    <tbody>
                        @forelse($activeStudents as $student)
                            <tr class="fade-up">
                                <td>
                                    <div class="subject-cell">
                                        <div class="subject-avatar" style="background:{{ $student['avatar_color'] }}22;color:{{ $student['avatar_color'] }};width:32px;height:32px;font-size:10px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;border:1px solid {{ $student['avatar_color'] }}44;">{{ $student['initials'] }}</div>
                                        <div><div class="subject-name" style="font-size:13px;font-weight:700;">{{ $student['name'] }}</div><div class="subject-id" style="font-size:9px;color:var(--muted2);">{{ __('admin_student_overview.year', ['year' => $student['year']]) }} · {{ $student['major'] }}</div></div>
                                    </div>
                                </td>
                                <td style="font-family:var(--font-mono);font-size:10px;color:var(--accent);font-weight:700;">{{ $student['code'] }}</td>
                                <td style="font-family:var(--font-mono);font-size:10px;color:var(--text2);">{{ $student['time'] }}</td>
                                <td>
                                    @if(strtolower($student['status']) === 'present')
                                        <span class="status-tag tag-active">{{ __('admin_student_overview.present') }}</span>
                                    @elseif(strtolower($student['status']) === 'late')
                                        <span class="status-tag" style="background:color-mix(in srgb, var(--amber) 13%, transparent);color:var(--amber);border:1px solid color-mix(in srgb, var(--amber) 27%, transparent)">{{ __('admin_student_overview.late') }}</span>
                                    @elseif(strtolower($student['status']) === 'excused')
                                        <span class="status-tag" style="background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);border:1px solid color-mix(in srgb, var(--accent) 27%, transparent);cursor:help;" title="{{ __('admin_student_overview.reason') }}: {{ $student['permission'] ?? __('admin_student_overview.default_excused_reason') }}">{{ __('admin_student_overview.excused') }}</span>
                                    @else
                                        <span class="status-tag" style="background:color-mix(in srgb, var(--red) 13%, transparent);color:var(--red);border:1px solid color-mix(in srgb, var(--red) 27%, transparent)">{{ __('admin_student_overview.absent') }}</span>
                                    @endif
                                </td>
                                <td style="text-align:right;font-family:var(--font-mono);font-size:9px;color:var(--muted2);font-weight:700;">{{ strtoupper($student['method']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;padding:50px 0;color:var(--muted2);font-size:11px;font-family:var(--font-mono);letter-spacing:.05em;">{{ __('admin_student_overview.empty_session') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel monitor-panel" style="margin-top:20px;">
            <div class="monitor-head">
                <div><div class="monitor-kicker"><span class="monitor-pulse"></span>{{ __('admin_student_overview.monitor_data') }}</div><h2>{{ __('admin_student_overview.overall_progress') }}</h2></div>
                <div class="monitor-score"><span>{{ __('admin_student_overview.average') }}</span><strong>{{ $monitorAvg }}%</strong></div>
            </div>
            <form method="GET" action="{{ route('admin.students.overview') }}" class="monitor-filter">
                <label for="major_id">{{ __('admin_student_overview.progress_by_major') }}</label>
                <select id="major_id" name="major_id" onchange="this.form.submit()">
                    <option value="">{{ __('admin_student_overview.all_majors') }}</option>
                    @foreach($majorOptions as $major)
                        <option value="{{ $major->id }}" @selected($selectedMajorId === $major->id)>{{ $major->name }}{{ $major->code ? ' · ' . $major->code : '' }}</option>
                    @endforeach
                </select>
                @if($selectedMajor)<a href="{{ route('admin.students.overview') }}">{{ __('admin_student_overview.clear') }}</a>@endif
            </form>
            <div class="monitor-insights">
                <div class="monitor-insight"><span>{{ __('admin_student_overview.subjects_tracked') }}</span><strong>{{ $monitorClasses->count() }}</strong></div>
                <div class="monitor-insight"><span>{{ __('admin_student_overview.strongest_subject') }}</span><strong>{{ $monitorStrongest['name'] ?? __('admin_student_overview.none') }}</strong></div>
                <div class="monitor-insight monitor-insight--warn"><span>{{ __('admin_student_overview.needs_attention') }}</span><strong>{{ $monitorNeedsAttention }}</strong></div>
            </div>
            <div class="monitor-chart-wrap"><canvas id="monitor-chart"></canvas></div>
        </div>
    </div>

    <div class="right-col">
        <div class="panel year-panel" style="margin-bottom:20px;">
            <div class="year-head"><div><div class="year-kicker"><span class="year-pulse"></span>{{ __('admin_student_overview.year_performance') }}</div><h2>{{ __('admin_student_overview.year_comparison') }}</h2></div><div class="year-score"><span>{{ __('admin_student_overview.average') }}</span><strong>{{ $yearAverage }}%</strong></div></div>
            <div class="year-rank"><span>{{ __('admin_student_overview.top_cohort') }}</span><strong>{{ __('admin_student_overview.year_value', ['year' => $topYear ?? '-']) }}</strong><em>{{ $topYearScore }}%</em></div>
            <div class="year-cards">
                @foreach($yearStats as $year => $rate)
                    <div class="year-card {{ $rate >= 80 ? 'year-card--strong' : ($rate >= 60 ? 'year-card--steady' : 'year-card--risk') }}">
                        <div><span>{{ __('admin_student_overview.year_value', ['year' => $year]) }}</span><strong>{{ $rate }}%</strong></div>
                        <div class="year-card__bar"><i style="width:{{ min(100, max(0, $rate)) }}%;"></i></div>
                    </div>
                @endforeach
            </div>
            <div class="year-chart-wrap"><canvas id="year-chart"></canvas></div>
        </div>

        <div class="panel" style="margin-bottom:20px;">
            <div class="panel-head"><div style="display:flex;align-items:center;gap:8px;"><div class="db-dot" style="background:var(--red);box-shadow:0 0 8px var(--red);"></div><span class="panel-title">{{ __('admin_student_overview.high_absence_students') }}</span></div><span style="font-family:var(--font-mono);font-size:9px;color:var(--muted2);">{{ __('admin_student_overview.top_critical') }}</span></div>
            <div style="padding:10px 0;">
                @forelse($topAbsentStudents as $student)
                    <div class="class-row" style="cursor:default;border-bottom:1px solid rgba(255,255,255,.03);">
                        <div class="row-icon" style="background:rgba(239,68,68,.1);color:#ef4444;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $student['initials'] }}</div>
                        <div class="row-info"><div class="row-name">{{ $student['name'] }}</div><div class="row-meta">{{ __('admin_student_overview.sessions_missed', ['count' => $student['absent_count']]) }}</div></div>
                        <div style="text-align:right;padding-right:15px;"><div style="font-size:11px;font-weight:900;color:#ef4444;">{{ $student['absence_rate'] }}%</div><div style="font-size:8px;color:var(--muted2);font-family:var(--font-mono);">{{ __('admin_student_overview.absence_rate') }}</div></div>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--muted2);font-size:11px;">{{ __('admin_student_overview.no_critical_absences') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartGrid = 'rgba(148,163,184,.16)';
    const chartText = '#94a3b8';
    const subjectLabels = @json($monitorClasses->take(8)->pluck('name')->values());
    const subjectRates = @json($monitorClasses->take(8)->pluck('progress')->values());
    const yearLabels = @json($yearCollection->keys()->map(fn($year) => __('admin_student_overview.year_value', ['year' => $year]))->values());
    const yearRates = @json($yearCollection->values());
    function makeChart(id, config) {
        const el = document.getElementById(id);
        if (!el || !window.Chart) return;
        new Chart(el, config);
    }
    makeChart('monitor-chart', {
        type: 'bar',
        data: { labels: subjectLabels, datasets: [{ data: subjectRates, backgroundColor: '#2563eb', borderRadius: 8 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100, ticks: { color: chartText }, grid: { color: chartGrid } }, x: { ticks: { color: chartText }, grid: { display: false } } } }
    });
    makeChart('year-chart', {
        type: 'line',
        data: { labels: yearLabels, datasets: [{ data: yearRates, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.12)', fill: true, tension: .4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100, ticks: { color: chartText }, grid: { color: chartGrid } }, x: { ticks: { color: chartText }, grid: { display: false } } } }
    });
</script>
@endpush
