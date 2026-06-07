@extends('layouts.app')

@section('content')
@php
    $sessionRate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;
    $absentNow = max(0, $totalCount - $presentCount);
    $criticalStudentCount = $topAbsentStudents->count();
    $criticalClassCount = $topAbsentClasses->count();
    $isDemoUser = Auth::user()?->email === 'demo@example.com';
    $activeSubject = $activeSession?->classRoom?->subject?->name ?? 'No active session';
    $activeGroups = $activeSession?->classRoom?->groups?->pluck('name')->filter()->join(', ') ?: 'No group selected';
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
            <span>STUDENTS</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">OVERVIEW</span>
        </div>
        <div class="dashboard-hero__title-row">
            <div>
                <h1 class="page-title dashboard-hero__title">Student Overview</h1>
                <p class="dashboard-hero__subtitle">
                    Monitor student attendance health, active sessions, QR scans, permissions, groups, and risk signals from one workspace.
                </p>
            </div>
            @if($isDemoUser)
                <div class="demo-mode-badge">
                    <span class="demo-mode-badge__dot"></span>
                    READ-ONLY DEMO
                </div>
            @endif
        </div>

        <div class="dashboard-hero__meta">
            <div class="hero-meta-item"><span>ACTIVE SESSION</span><strong>{{ $activeSubject }}</strong></div>
            <div class="hero-meta-item"><span>ASSIGNED GROUP</span><strong>{{ $activeGroups }}</strong></div>
            <div class="hero-meta-item"><span>SESSION COVERAGE</span><strong>{{ $presentCount }}/{{ $totalCount }} marked</strong></div>
        </div>
    </div>

    <div class="dashboard-hero__status">
        <div class="session-ring" style="--value: {{ $sessionRate }};">
            <div class="session-ring__inner">
                <strong>{{ $sessionRate }}%</strong>
                <span>{{ $hasSessionActivity ? 'SESSION' : 'WAITING DATA' }}</span>
            </div>
        </div>
        <div class="session-status-list">
            <div><span class="status-dot status-dot--green"></span><span>Present or excused</span><strong>{{ $presentCount }}</strong></div>
            <div><span class="status-dot status-dot--red"></span><span>Not marked</span><strong>{{ $absentNow }}</strong></div>
            <div><span class="status-dot status-dot--blue"></span><span>QR scans</span><strong>{{ $sessionScanCount }}</strong></div>
            @unless($hasSessionActivity)
                <p class="session-status-note">Updates after the first attendance mark or QR scan.</p>
            @endunless
        </div>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-glow"></div><div class="stat-label">ACTIVE STUDENTS</div><div class="stat-value">{{ $stats['students'] }}</div><div class="stat-pill pill-blue">ENROLLED</div></div>
    <div class="stat-card green"><div class="stat-glow"></div><div class="stat-label">TOTAL ATTENDANCE</div><div class="stat-value">{{ $stats['attendance_rate'] }}</div><div class="stat-pill pill-up">HEALTH RATE</div></div>
    <div class="stat-card amber"><div class="stat-glow"></div><div class="stat-label">PENDING SESSIONS</div><div class="stat-value">{{ $stats['pending_sessions'] }}</div><div class="stat-pill pill-amber">IN QUEUE</div></div>
    <div class="stat-card red"><div class="stat-glow"></div><div class="stat-label">ABSENCE RATE</div><div class="stat-value">{{ $stats['absence_rate'] }}</div><div class="stat-pill pill-down">{{ $criticalStudentCount }} STUDENTS FLAGGED</div></div>
</div>

<section class="summary-workbench">
    <div class="summary-panel summary-panel--wide">
        <div class="summary-panel__head">
            <div><span class="summary-eyebrow">Operational Snapshot</span><h2>Today’s student attendance posture</h2></div>
            <span class="summary-chip">{{ $classes->count() }} sessions visible</span>
        </div>
        <div class="summary-metrics">
            <div class="summary-metric"><span>Live or upcoming sessions</span><strong>{{ $stats['pending_sessions'] }}</strong></div>
            <div class="summary-metric"><span>Students in selected session</span><strong>{{ $totalCount }}</strong></div>
            <div class="summary-metric"><span>Critical students</span><strong>{{ $criticalStudentCount }}</strong></div>
            <div class="summary-metric"><span>Critical classes</span><strong>{{ $criticalClassCount }}</strong></div>
        </div>
    </div>

    <div class="summary-panel">
        <div class="summary-panel__head">
            <div><span class="summary-eyebrow">Modules</span><h2>Quick access</h2></div>
        </div>
        <div class="summary-actions">
            <a href="{{ route('admin.students') }}">Students</a>
            <a href="{{ route('admin.permissions') }}">Permissions</a>
            <a href="{{ route('admin.classes') }}">Student Groups</a>
            <a href="{{ route('admin.attendance-issues') }}">Attendance Issues</a>
        </div>
    </div>
</section>

<div class="main-grid">
    <div class="left-col">
        <div class="panel">
            <div class="panel-head">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:blink 2s infinite;"></div>
                    <span class="panel-title">LIVE STUDENT MONITORING</span>
                </div>
                <div style="display:flex;align-items:center;gap:15px;">
                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted2);">{{ strtoupper($activeSession?->classRoom?->subject?->name ?? 'NO SESSION') }}</span>
                    <div style="display:flex;align-items:center;gap:5px;background:var(--surface3);padding:2px 8px;border-radius:4px;border:1px solid var(--border);">
                        <span style="font-family:var(--font-mono);font-size:9px;color:var(--green);font-weight:800;">{{ $presentCount }}/{{ $totalCount }}</span>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height:450px;overflow-y:auto;">
                <table class="att-table">
                    <thead><tr><th>STUDENT IDENTITY</th><th>CODE</th><th>TIME</th><th>STATUS</th><th style="text-align:right">METHOD</th></tr></thead>
                    <tbody>
                        @forelse($activeStudents as $student)
                            <tr class="fade-up">
                                <td>
                                    <div class="subject-cell">
                                        <div class="subject-avatar" style="background:{{ $student['avatar_color'] }}22;color:{{ $student['avatar_color'] }};width:32px;height:32px;font-size:10px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;border:1px solid {{ $student['avatar_color'] }}44;">{{ $student['initials'] }}</div>
                                        <div><div class="subject-name" style="font-size:13px;font-weight:700;">{{ $student['name'] }}</div><div class="subject-id" style="font-size:9px;color:var(--muted2);">YEAR {{ $student['year'] }} · {{ $student['major'] }}</div></div>
                                    </div>
                                </td>
                                <td style="font-family:var(--font-mono);font-size:10px;color:var(--accent);font-weight:700;">{{ $student['code'] }}</td>
                                <td style="font-family:var(--font-mono);font-size:10px;color:var(--text2);">{{ $student['time'] }}</td>
                                <td>
                                    @if(strtolower($student['status']) === 'present')
                                        <span class="status-tag tag-active">PRESENT</span>
                                    @elseif(strtolower($student['status']) === 'late')
                                        <span class="status-tag" style="background:var(--amber)22;color:var(--amber);border:1px solid var(--amber)44">LATE</span>
                                    @elseif(strtolower($student['status']) === 'excused')
                                        <span class="status-tag" style="background:var(--accent)22;color:var(--accent);border:1px solid var(--accent)44;cursor:help;" title="REASON: {{ $student['permission'] ?? 'Excused' }}">EXCUSED</span>
                                    @else
                                        <span class="status-tag" style="background:var(--red)22;color:var(--red);border:1px solid var(--red)44">ABSENT</span>
                                    @endif
                                </td>
                                <td style="text-align:right;font-family:var(--font-mono);font-size:9px;color:var(--muted2);font-weight:700;">{{ strtoupper($student['method']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;padding:50px 0;color:var(--muted2);font-size:11px;font-family:var(--font-mono);letter-spacing:.05em;">NO STUDENTS REGISTERED IN THIS SESSION</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel monitor-panel" style="margin-top:20px;">
            <div class="monitor-head">
                <div><div class="monitor-kicker"><span class="monitor-pulse"></span>MONITOR DATA</div><h2>Overall class progress</h2></div>
                <div class="monitor-score"><span>Average</span><strong>{{ $monitorAvg }}%</strong></div>
            </div>
            <form method="GET" action="{{ route('admin.students.overview') }}" class="monitor-filter">
                <label for="major_id">Progress by major</label>
                <select id="major_id" name="major_id" onchange="this.form.submit()">
                    <option value="">All majors</option>
                    @foreach($majorOptions as $major)
                        <option value="{{ $major->id }}" @selected($selectedMajorId === $major->id)>{{ $major->name }}{{ $major->code ? ' · ' . $major->code : '' }}</option>
                    @endforeach
                </select>
                @if($selectedMajor)<a href="{{ route('admin.students.overview') }}">Clear</a>@endif
            </form>
            <div class="monitor-insights">
                <div class="monitor-insight"><span>Subjects tracked</span><strong>{{ $monitorClasses->count() }}</strong></div>
                <div class="monitor-insight"><span>Strongest subject</span><strong>{{ $monitorStrongest['name'] ?? 'None' }}</strong></div>
                <div class="monitor-insight monitor-insight--warn"><span>Needs attention</span><strong>{{ $monitorNeedsAttention }}</strong></div>
            </div>
            <div class="monitor-chart-wrap"><canvas id="monitor-chart"></canvas></div>
        </div>
    </div>

    <div class="right-col">
        <div class="panel year-panel" style="margin-bottom:20px;">
            <div class="year-head"><div><div class="year-kicker"><span class="year-pulse"></span>YEAR LEVEL PERFORMANCE</div><h2>Academic year comparison</h2></div><div class="year-score"><span>Average</span><strong>{{ $yearAverage }}%</strong></div></div>
            <div class="year-rank"><span>Top cohort</span><strong>Year {{ $topYear ?? '-' }}</strong><em>{{ $topYearScore }}%</em></div>
            <div class="year-cards">
                @foreach($yearStats as $year => $rate)
                    <div class="year-card {{ $rate >= 80 ? 'year-card--strong' : ($rate >= 60 ? 'year-card--steady' : 'year-card--risk') }}">
                        <div><span>Year {{ $year }}</span><strong>{{ $rate }}%</strong></div>
                        <div class="year-card__bar"><i style="width:{{ min(100, max(0, $rate)) }}%;"></i></div>
                    </div>
                @endforeach
            </div>
            <div class="year-chart-wrap"><canvas id="year-chart"></canvas></div>
        </div>

        <div class="panel" style="margin-bottom:20px;">
            <div class="panel-head"><div style="display:flex;align-items:center;gap:8px;"><div class="db-dot" style="background:var(--red);box-shadow:0 0 8px var(--red);"></div><span class="panel-title">HIGH ABSENCE STUDENTS</span></div><span style="font-family:var(--font-mono);font-size:9px;color:var(--muted2);">TOP 5 CRITICAL</span></div>
            <div style="padding:10px 0;">
                @forelse($topAbsentStudents as $student)
                    <div class="class-row" style="cursor:default;border-bottom:1px solid rgba(255,255,255,.03);">
                        <div class="row-icon" style="background:rgba(239,68,68,.1);color:#ef4444;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $student['initials'] }}</div>
                        <div class="row-info"><div class="row-name">{{ $student['name'] }}</div><div class="row-meta">{{ $student['absent_count'] }} SESSIONS MISSED</div></div>
                        <div style="text-align:right;padding-right:15px;"><div style="font-size:11px;font-weight:900;color:#ef4444;">{{ $student['absence_rate'] }}%</div><div style="font-size:8px;color:var(--muted2);font-family:var(--font-mono);">ABSENCE RATE</div></div>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--muted2);font-size:11px;">NO CRITICAL ABSENCES DETECTED</div>
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
    const yearLabels = @json($yearCollection->keys()->map(fn($year) => 'Year ' . $year)->values());
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
