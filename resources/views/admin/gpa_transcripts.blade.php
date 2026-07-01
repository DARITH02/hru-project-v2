@extends('layouts.app')

@php
    $statusMeta = [
        'Good Standing' => ['class' => 'good', 'label' => __('admin.gpa_transcripts.good_standing')],
        'Academic Warning' => ['class' => 'warning', 'label' => __('admin.gpa_transcripts.academic_warning')],
        'Probation' => ['class' => 'danger', 'label' => __('admin.gpa_transcripts.probation')],
    ];

    $gpaTone = function ($gpa) {
        if ($gpa >= 3.5) return 'good';
        if ($gpa >= 2.5) return 'warning';
        return 'danger';
    };

    $programOptions = $students->pluck('program')->filter()->unique()->sort()->values();
    $yearOptions = $students->pluck('year')->filter()->unique()->sort()->values();
@endphp

@section('content')
<div class="gpa-page">
    <header class="gpa-header">
        <div>
            <div class="gpa-kicker">
                <span class="gpa-kicker-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M21.42 10.92 12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0l8.59-3.9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        <path d="M6 12.5V16c0 1.66 2.69 3 6 3s6-1.34 6-3v-3.5M22 10v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </span>
                {{ __('admin.gpa_transcripts.eyebrow') }}
            </div>
            <h1>{{ __('admin.gpa_transcripts.title') }}</h1>
            <p>{{ __('admin.gpa_transcripts.subtitle') }}</p>
        </div>

        <div class="gpa-stats">
            <div class="gpa-stat"><span>{{ __('admin.gpa_transcripts.students') }}</span><strong>{{ $students->count() }}</strong></div>
            <div class="gpa-stat"><span>{{ __('admin.gpa_transcripts.cohort_gpa') }}</span><strong class="tone-{{ $gpaTone($cohortAvg) }}">{{ number_format($cohortAvg, 2) }}</strong></div>
            <div class="gpa-stat"><span>{{ __('admin.gpa_transcripts.warning') }}</span><strong class="tone-warning">{{ $warningCount }}</strong></div>
            <div class="gpa-stat"><span>{{ __('admin.gpa_transcripts.probation') }}</span><strong class="tone-danger">{{ $probationCount }}</strong></div>
        </div>
    </header>

    <section class="gpa-toolbar">
        <div class="gpa-toolbar-main">
            <label class="gpa-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                    <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <input id="gpaSearch" type="search" placeholder="{{ __('admin.gpa_transcripts.search_placeholder') }}">
            </label>

            <button type="button" class="gpa-export" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('admin.gpa_transcripts.print') }}
            </button>
        </div>

        <div class="gpa-filter-bar">
            <label>
                <span>{{ __('admin.gpa_transcripts.standing') }}</span>
                <select id="gpaStatusFilter">
                    <option value="all">{{ __('admin.gpa_transcripts.all') }}</option>
                    <option value="Good Standing">{{ __('admin.gpa_transcripts.good_standing') }}</option>
                    <option value="Academic Warning">{{ __('admin.gpa_transcripts.warning') }}</option>
                    <option value="Probation">{{ __('admin.gpa_transcripts.probation') }}</option>
                </select>
            </label>
            <label>
                <span>{{ __('admin.gpa_transcripts.program') }}</span>
                <select id="gpaProgramFilter">
                    <option value="all">{{ __('admin.gpa_transcripts.all') }}</option>
                    @foreach($programOptions as $program)
                        <option value="{{ strtolower($program) }}">{{ $program }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>{{ __('admin.gpa_transcripts.year') }}</span>
                <select id="gpaYearFilter">
                    <option value="all">{{ __('admin.gpa_transcripts.all') }}</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ strtolower($year) }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Rows</span>
                <select id="gpaPageSize">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="all">All</option>
                </select>
            </label>
            <button type="button" class="gpa-reset" id="gpaResetFilters">Reset</button>
        </div>
    </section>

    <section class="gpa-table-card">
        <div class="gpa-result-bar">
            <span id="gpaResultCount"></span>
            <span id="gpaActiveFilters"></span>
        </div>
        <div class="gpa-table-head">
            <span></span>
            <button type="button" data-sort="name">{{ __('admin.gpa_transcripts.student') }}</button>
            <button type="button" data-sort="program">{{ __('admin.gpa_transcripts.program') }}</button>
            <button type="button" data-sort="year">{{ __('admin.gpa_transcripts.year') }}</button>
            <button type="button" data-sort="credits">{{ __('admin.gpa_transcripts.credits') }}</button>
            <button type="button" data-sort="gpa">{{ __('admin.gpa_transcripts.gpa') }}</button>
            <button type="button" data-sort="status">{{ __('admin.gpa_transcripts.standing') }}</button>
            <span></span>
        </div>

        <div id="gpaRows">
            @forelse ($students as $student)
                @php
                    $standing = $statusMeta[$student['status']] ?? $statusMeta['Probation'];
                    $delta = round($student['gpa'] - $student['prev_gpa'], 2);
                    $transcript = [
                        'name' => $student['name'],
                        'code' => $student['code'],
                        'program' => $student['program'],
                        'group' => $student['group'],
                        'year' => $student['year'],
                        'gpa' => number_format($student['gpa'], 2),
                        'credits' => number_format($student['credits'], 2),
                        'status' => $standing['label'],
                        'download_url' => $student['download_url'],
                        'histories' => $student['histories']->map(fn ($history) => [
                            'term' => $history->academic_year . ' - ' . __('admin.gpa_transcripts.semester') . ' ' . $history->semester,
                            'semester_gpa' => number_format((float) $history->semester_gpa, 2),
                            'cumulative_gpa' => number_format((float) $history->cumulative_gpa, 2),
                            'credits' => number_format((float) $history->total_credits, 2),
                            'courses' => $history->subjectGrades->map(fn ($grade) => [
                                'code' => $grade->subject_code ?: 'SUBJ',
                                'title' => $grade->subject_name,
                                'grade' => $grade->letter_grade,
                                'credits' => number_format((float) $grade->credit, 2),
                                'score' => number_format((float) $grade->total_score, 2),
                            ])->values(),
                        ])->values(),
                    ];
                @endphp

                <article class="gpa-row-wrap"
                    data-name="{{ strtolower($student['name']) }}"
                    data-program="{{ strtolower($student['program']) }}"
                    data-year="{{ strtolower($student['year']) }}"
                    data-credits="{{ $student['credits'] }}"
                    data-gpa="{{ $student['gpa'] }}"
                    data-status="{{ $student['status'] }}"
                    data-search="{{ strtolower($student['name'] . ' ' . $student['code'] . ' ' . $student['program'] . ' ' . $student['group'] . ' ' . $student['year'] . ' ' . $student['status']) }}"
                    data-transcript='@json($transcript)'>
                    <div class="gpa-row" data-expand>
                        <span class="gpa-chevron">&rsaquo;</span>
                        <div class="gpa-student-cell">
                            <span class="gpa-avatar">{{ strtoupper(substr($student['name'], 0, 1)) }}</span>
                            <span>
                                <strong>{{ $student['name'] }}</strong>
                                <small>{{ $student['code'] }}</small>
                            </span>
                        </div>
                        <span>{{ $student['program'] }}</span>
                        <span>{{ $student['year'] }}</span>
                        <span class="mono">{{ number_format($student['credits'], 2) }}</span>
                        <span class="gpa-value tone-{{ $gpaTone($student['gpa']) }}">
                            {{ number_format($student['gpa'], 2) }}
                            <small class="{{ $delta >= 0 ? 'up' : 'down' }}">{{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 2) }}</small>
                        </span>
                        <span><span class="gpa-pill {{ $standing['class'] }}">{{ $standing['label'] }}</span></span>
                        <button type="button" class="gpa-file-btn" data-open-transcript title="{{ __('admin.gpa_transcripts.open_transcript') }}">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M14 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="gpa-expanded">
                        @foreach ($student['histories'] as $history)
                            <div class="gpa-term-preview">
                                <div class="gpa-term-title">
                                    <strong>{{ $history->academic_year }} &middot; {{ __('admin.gpa_transcripts.semester') }} {{ $history->semester }}</strong>
                                    <span>{{ __('admin.gpa_transcripts.gpa') }} {{ number_format((float) $history->semester_gpa, 2) }}</span>
                                </div>
                                @foreach ($history->subjectGrades as $grade)
                                    <div class="gpa-course-line">
                                        <span>{{ $grade->subject_code ?: 'SUBJ' }} &middot; {{ $grade->subject_name }}</span>
                                        <strong>{{ $grade->letter_grade }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="gpa-empty">{{ __('admin.gpa_transcripts.no_records') }}</div>
            @endforelse
        </div>
        <div class="gpa-empty gpa-empty-filter" id="gpaEmptyFilter" hidden>No students match the current filters.</div>
        <div class="gpa-pagination" id="gpaPagination" hidden>
            <button type="button" id="gpaPrevPage">Prev</button>
            <div id="gpaPageButtons"></div>
            <button type="button" id="gpaNextPage">Next</button>
        </div>
    </section>
</div>

<div class="gpa-drawer-backdrop" id="gpaDrawerBackdrop" hidden></div>
<aside class="gpa-drawer" id="gpaDrawer" hidden aria-label="{{ __('admin.gpa_transcripts.open_transcript') }}">
    <button type="button" class="gpa-drawer-close" id="gpaDrawerClose">&times;</button>
    <div id="gpaDrawerBody"></div>
</aside>
@endsection

@push('styles')
<style>
    .gpa-page { display:grid; gap:22px; color:var(--text); }
    .gpa-header { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap; }
    .gpa-kicker { display:flex; align-items:center; gap:10px; margin-bottom:12px; color:var(--muted); font-family:var(--font-mono); font-size:10px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    .gpa-kicker-icon { width:32px; height:32px; border-radius:9px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,var(--accent),#0f766e); }
    .gpa-header h1 { margin:0; color:var(--text); font-family:var(--font-display); font-size:clamp(30px,4vw,46px); font-weight:900; letter-spacing:-.04em; }
    .gpa-header p { max-width:560px; margin:10px 0 0; color:var(--muted); line-height:1.6; }
    .gpa-stats { display:grid; grid-template-columns:repeat(4,minmax(96px,1fr)); gap:12px; }
    .gpa-stat { min-width:96px; border:1px solid var(--border); border-radius:14px; background:var(--surface); padding:14px 16px; box-shadow:0 16px 40px rgba(15,23,42,.06); }
    .gpa-stat span { display:block; margin-bottom:6px; color:var(--muted); font-family:var(--font-mono); font-size:9px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .gpa-stat strong { font-family:var(--font-mono); font-size:24px; color:var(--text); }
    .tone-good { color:#10b981 !important; } .tone-warning { color:#f59e0b !important; } .tone-danger { color:#ef4444 !important; }
    .gpa-toolbar { display:grid; gap:12px; }
    .gpa-toolbar-main { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .gpa-search { flex:1 1 320px; min-height:44px; display:flex; align-items:center; gap:10px; border:1px solid var(--border); border-radius:12px; background:var(--surface); padding:0 14px; color:var(--muted); }
    .gpa-search input { width:100%; border:0; outline:0; background:transparent; color:var(--text); font-size:13px; }
    .gpa-filter-bar { display:grid; grid-template-columns:repeat(4,minmax(130px,1fr)) auto; gap:10px; align-items:end; border:1px solid var(--border); border-radius:14px; background:var(--surface); padding:12px; }
    .gpa-filter-bar label { display:grid; gap:6px; min-width:0; }
    .gpa-filter-bar span { color:var(--muted); font-family:var(--font-mono); font-size:9px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .gpa-filter-bar select { width:100%; height:38px; border:1px solid var(--border); border-radius:10px; background:var(--surface2); color:var(--text); padding:0 10px; font-size:12px; font-weight:800; outline:0; }
    .gpa-filter-bar select:focus, .gpa-search:focus-within { border-color:color-mix(in srgb,var(--accent) 45%,transparent); box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 12%,transparent); }
    .gpa-export, .gpa-reset { min-height:38px; border:1px solid var(--border); border-radius:10px; background:var(--surface); color:var(--text2); padding:0 13px; cursor:pointer; font-size:11px; font-weight:900; }
    .gpa-reset { height:38px; background:var(--surface2); }
    .gpa-export { margin-left:auto; display:flex; align-items:center; gap:8px; }
    .gpa-table-card { border:1px solid var(--border); border-radius:16px; background:var(--surface); overflow:hidden; box-shadow:0 18px 55px rgba(15,23,42,.08); }
    .gpa-result-bar { min-height:44px; display:flex; justify-content:space-between; align-items:center; gap:12px; padding:0 20px; border-bottom:1px solid var(--border); color:var(--muted); font-size:12px; }
    .gpa-result-bar span:first-child { color:var(--text); font-weight:800; }
    .gpa-result-bar span:last-child { font-family:var(--font-mono); font-size:10px; text-transform:uppercase; letter-spacing:.08em; }
    .gpa-table-head, .gpa-row { display:grid; grid-template-columns:28px minmax(220px,1.7fr) minmax(150px,1.25fr) 80px 90px 100px 150px 38px; align-items:center; gap:12px; }
    .gpa-table-head { padding:14px 20px; border-bottom:1px solid var(--border); background:var(--surface2); }
    .gpa-table-head button { border:0; background:transparent; color:var(--muted); cursor:pointer; padding:0; text-align:left; font-family:var(--font-mono); font-size:9px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    .gpa-row-wrap { border-top:1px solid color-mix(in srgb,var(--border) 75%,transparent); }
    .gpa-row-wrap:first-child { border-top:0; }
    .gpa-row { width:100%; padding:16px 20px; color:var(--text2); cursor:pointer; transition:background .16s ease; }
    .gpa-row:hover, .gpa-row-wrap.is-open .gpa-row { background:color-mix(in srgb,var(--accent) 5%,transparent); }
    .gpa-chevron { color:var(--muted); font-size:22px; transform:rotate(0deg); transition:transform .18s ease; }
    .gpa-row-wrap.is-open .gpa-chevron { transform:rotate(90deg); }
    .gpa-student-cell { display:flex; align-items:center; gap:12px; min-width:0; }
    .gpa-avatar { width:38px; height:38px; flex:0 0 38px; border-radius:10px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,var(--accent),#0f766e); font-weight:900; }
    .gpa-student-cell strong { display:block; color:var(--text); font-size:14px; }
    .gpa-student-cell small { display:block; color:var(--muted); font-family:var(--font-mono); font-size:10px; }
    .mono, .gpa-value { font-family:var(--font-mono); font-weight:900; }
    .gpa-value small { margin-left:5px; font-size:10px; color:var(--muted); }
    .gpa-value small.up { color:#10b981; } .gpa-value small.down { color:#ef4444; }
    .gpa-pill { display:inline-flex; align-items:center; min-height:26px; border-radius:999px; padding:0 10px; font-size:10px; font-weight:900; }
    .gpa-pill.good { color:#10b981; background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.24); }
    .gpa-pill.warning { color:#f59e0b; background:rgba(245,158,11,.13); border:1px solid rgba(245,158,11,.24); }
    .gpa-pill.danger { color:#ef4444; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.24); }
    .gpa-file-btn { width:34px; height:34px; display:grid; place-items:center; border:1px solid var(--border); border-radius:10px; background:var(--surface2); color:var(--muted); cursor:pointer; }
    .gpa-expanded { display:none; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; padding:4px 24px 22px 72px; background:color-mix(in srgb,var(--surface2) 68%,transparent); }
    .gpa-row-wrap.is-open .gpa-expanded { display:grid; }
    .gpa-term-preview { min-width:0; }
    .gpa-term-title { display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; color:var(--text); font-size:12px; }
    .gpa-term-title span { color:var(--accent); font-family:var(--font-mono); font-weight:900; }
    .gpa-course-line { display:flex; justify-content:space-between; gap:12px; padding:7px 0; border-bottom:1px solid var(--border); color:var(--text2); font-size:12px; }
    .gpa-course-line strong { font-family:var(--font-mono); color:var(--text); }
    .gpa-empty { padding:54px 20px; text-align:center; color:var(--muted); }
    .gpa-empty-filter { border-top:1px solid var(--border); }
    .gpa-pagination { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; border-top:1px solid var(--border); background:var(--surface2); }
    .gpa-pagination[hidden] { display:none; }
    .gpa-pagination button { min-width:38px; height:34px; border:1px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text2); cursor:pointer; font-size:11px; font-weight:900; }
    .gpa-pagination button:disabled { opacity:.45; cursor:not-allowed; }
    #gpaPageButtons { display:flex; justify-content:center; gap:6px; flex-wrap:wrap; }
    #gpaPageButtons button.active { border-color:color-mix(in srgb,var(--accent) 45%,transparent); background:color-mix(in srgb,var(--accent) 12%,var(--surface)); color:var(--accent); }
    .gpa-drawer-backdrop { position:fixed; inset:0; z-index:2400; background:rgba(15,23,42,.48); backdrop-filter:blur(4px); }
    .gpa-drawer { position:fixed; top:0; right:0; z-index:2450; width:min(520px,94vw); height:100vh; overflow:auto; background:var(--surface); border-left:1px solid var(--border); box-shadow:-24px 0 80px rgba(15,23,42,.28); padding:28px; }
    .gpa-drawer-close { position:sticky; top:0; float:right; width:34px; height:34px; border:1px solid var(--border); border-radius:10px; background:var(--surface2); color:var(--text); cursor:pointer; font-size:24px; line-height:1; }
    .drawer-eyebrow { color:var(--muted); font-family:var(--font-mono); font-size:10px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    .drawer-title { margin:8px 0 2px; font-family:var(--font-display); font-size:26px; font-weight:900; color:var(--text); }
    .drawer-meta { color:var(--muted); font-size:13px; }
    .drawer-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:22px 0; }
    .drawer-summary div { border:1px solid var(--border); border-radius:13px; background:var(--surface2); padding:13px; }
    .drawer-summary span { display:block; margin-bottom:4px; color:var(--muted); font-family:var(--font-mono); font-size:9px; font-weight:900; text-transform:uppercase; }
    .drawer-summary strong { font-family:var(--font-mono); font-size:20px; color:var(--text); }
    .drawer-download { display:flex; align-items:center; justify-content:center; gap:8px; min-height:42px; margin:0 0 22px; border:0; border-radius:12px; background:linear-gradient(135deg,var(--accent),#0f766e); color:#fff; text-decoration:none; font-size:12px; font-weight:900; box-shadow:0 14px 30px color-mix(in srgb,var(--accent) 22%,transparent); }
    .drawer-term { margin-top:22px; }
    .drawer-term-head { display:flex; justify-content:space-between; gap:12px; padding-bottom:8px; border-bottom:1px solid var(--border); color:var(--text); font-weight:900; }
    .drawer-term-head span { color:var(--accent); font-family:var(--font-mono); }
    .drawer-course { display:grid; grid-template-columns:70px 1fr 52px 52px; gap:8px; padding:8px 0; border-bottom:1px solid color-mix(in srgb,var(--border) 65%,transparent); font-size:12px; color:var(--text2); }
    .drawer-course code, .drawer-course strong { font-family:var(--font-mono); color:var(--text); }
    @media (max-width: 980px) {
        .gpa-stats { grid-template-columns:repeat(2,minmax(0,1fr)); width:100%; }
        .gpa-filter-bar { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .gpa-reset { grid-column:1 / -1; }
        .gpa-table-head { display:none; }
        .gpa-row { grid-template-columns:24px 1fr 70px 38px; }
        .gpa-row > span:nth-child(3), .gpa-row > span:nth-child(4), .gpa-row > span:nth-child(5), .gpa-row > span:nth-child(7) { display:none; }
        .gpa-expanded { grid-template-columns:1fr; padding-left:56px; }
        .gpa-result-bar, .gpa-pagination { align-items:flex-start; flex-direction:column; }
    }
</style>
@endpush

@push('scripts')
<script>
    const rows = [...document.querySelectorAll('.gpa-row-wrap')];
    const search = document.getElementById('gpaSearch');
    const statusFilter = document.getElementById('gpaStatusFilter');
    const programFilter = document.getElementById('gpaProgramFilter');
    const yearFilter = document.getElementById('gpaYearFilter');
    const pageSizeSelect = document.getElementById('gpaPageSize');
    const resetFilters = document.getElementById('gpaResetFilters');
    const resultCount = document.getElementById('gpaResultCount');
    const activeFilters = document.getElementById('gpaActiveFilters');
    const emptyFilter = document.getElementById('gpaEmptyFilter');
    const pagination = document.getElementById('gpaPagination');
    const prevPage = document.getElementById('gpaPrevPage');
    const nextPage = document.getElementById('gpaNextPage');
    const pageButtons = document.getElementById('gpaPageButtons');
    let currentPage = 1;
    let sortKey = 'name';
    let sortDir = 1;

    function filteredRows() {
        const q = (search?.value || '').trim().toLowerCase();
        const status = statusFilter?.value || 'all';
        const program = programFilter?.value || 'all';
        const year = yearFilter?.value || 'all';

        return rows.filter(row => {
            const matchesSearch = !q || row.dataset.search.includes(q);
            const matchesStatus = status === 'all' || row.dataset.status === status;
            const matchesProgram = program === 'all' || row.dataset.program === program;
            const matchesYear = year === 'all' || row.dataset.year === year;

            return matchesSearch && matchesStatus && matchesProgram && matchesYear;
        });
    }

    function sortRows(list) {
        return [...list].sort((a, b) => {
            const av = ['gpa', 'credits'].includes(sortKey) ? Number(a.dataset[sortKey] || 0) : (a.dataset[sortKey] || '');
            const bv = ['gpa', 'credits'].includes(sortKey) ? Number(b.dataset[sortKey] || 0) : (b.dataset[sortKey] || '');
            return av > bv ? sortDir : av < bv ? -sortDir : 0;
        });
    }

    function selectedPageSize() {
        const value = pageSizeSelect?.value || '10';
        return value === 'all' ? rows.length || 1 : Number(value);
    }

    function renderPageButtons(totalPages) {
        if (!pageButtons) return;
        pageButtons.innerHTML = '';
        if (totalPages <= 1) return;

        const pages = [];
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);
        for (let page = start; page <= end; page++) pages.push(page);

        pages.forEach(page => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = page;
            button.className = page === currentPage ? 'active' : '';
            button.addEventListener('click', () => {
                currentPage = page;
                renderTable();
            });
            pageButtons.appendChild(button);
        });
    }

    function renderTable() {
        const container = document.getElementById('gpaRows');
        const matches = sortRows(filteredRows());
        const total = matches.length;
        const pageSize = selectedPageSize();
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * pageSize;
        const visibleRows = matches.slice(start, start + pageSize);

        rows.forEach(row => {
            row.style.display = 'none';
            row.classList.remove('is-open');
        });
        matches.forEach(row => container?.appendChild(row));
        visibleRows.forEach(row => row.style.display = '');

        if (resultCount) {
            const first = total ? start + 1 : 0;
            const last = Math.min(start + visibleRows.length, total);
            resultCount.textContent = total
                ? `Showing ${first}-${last} of ${total} students`
                : 'Showing 0 students';
        }

        const filterParts = [];
        if ((search?.value || '').trim()) filterParts.push('search');
        if ((statusFilter?.value || 'all') !== 'all') filterParts.push(statusFilter.value);
        if ((programFilter?.value || 'all') !== 'all') filterParts.push(programFilter.options[programFilter.selectedIndex]?.text || 'program');
        if ((yearFilter?.value || 'all') !== 'all') filterParts.push(yearFilter.options[yearFilter.selectedIndex]?.text || 'year');
        if (activeFilters) activeFilters.textContent = filterParts.length ? filterParts.join(' / ') : 'No filters';

        if (emptyFilter) emptyFilter.hidden = rows.length === 0 || total !== 0;
        if (pagination) pagination.hidden = totalPages <= 1;
        if (prevPage) prevPage.disabled = currentPage <= 1;
        if (nextPage) nextPage.disabled = currentPage >= totalPages;
        renderPageButtons(totalPages);
    }

    [search, statusFilter, programFilter, yearFilter, pageSizeSelect].forEach(control => {
        control?.addEventListener('input', () => {
            currentPage = 1;
            renderTable();
        });
        control?.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });
    });

    resetFilters?.addEventListener('click', () => {
        if (search) search.value = '';
        if (statusFilter) statusFilter.value = 'all';
        if (programFilter) programFilter.value = 'all';
        if (yearFilter) yearFilter.value = 'all';
        if (pageSizeSelect) pageSizeSelect.value = '10';
        currentPage = 1;
        renderTable();
    });

    prevPage?.addEventListener('click', () => {
        currentPage = Math.max(1, currentPage - 1);
        renderTable();
    });

    nextPage?.addEventListener('click', () => {
        currentPage += 1;
        renderTable();
    });

    document.querySelectorAll('[data-expand]').forEach(row => {
        row.addEventListener('click', () => row.closest('.gpa-row-wrap')?.classList.toggle('is-open'));
    });

    document.querySelectorAll('[data-sort]').forEach(button => {
        button.addEventListener('click', () => {
            sortKey = button.dataset.sort;
            sortDir *= -1;
            currentPage = 1;
            renderTable();
        });
    });

    renderTable();

    const drawer = document.getElementById('gpaDrawer');
    const backdrop = document.getElementById('gpaDrawerBackdrop');
    const drawerBody = document.getElementById('gpaDrawerBody');
    const gpaText = {
        officialTranscript: @js(__('admin.gpa_transcripts.official_transcript')),
        cumulativeGpa: @js(__('admin.gpa_transcripts.cumulative_gpa')),
        credits: @js(__('admin.gpa_transcripts.credits')),
        standing: @js(__('admin.gpa_transcripts.standing')),
        downloadPdf: @js(__('admin.gpa_transcripts.download_pdf')),
        gpa: @js(__('admin.gpa_transcripts.gpa')),
        creditShort: @js(__('admin.gpa_transcripts.credit')),
    };

    function closeDrawer() {
        drawer.hidden = true;
        backdrop.hidden = true;
    }

    document.getElementById('gpaDrawerClose')?.addEventListener('click', closeDrawer);
    backdrop?.addEventListener('click', closeDrawer);

    document.querySelectorAll('[data-open-transcript]').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation();
            const data = JSON.parse(button.closest('.gpa-row-wrap').dataset.transcript);
            drawerBody.innerHTML = `
                <div class="drawer-eyebrow">${escapeHtml(gpaText.officialTranscript)}</div>
                <h2 class="drawer-title">${escapeHtml(data.name)}</h2>
                <div class="drawer-meta">${escapeHtml(data.code)} &middot; ${escapeHtml(data.program)} &middot; ${escapeHtml(data.group)}</div>
                <div class="drawer-summary">
                    <div><span>${escapeHtml(gpaText.cumulativeGpa)}</span><strong>${data.gpa}</strong></div>
                    <div><span>${escapeHtml(gpaText.credits)}</span><strong>${data.credits}</strong></div>
                    <div><span>${escapeHtml(gpaText.standing)}</span><strong>${escapeHtml(data.status)}</strong></div>
                </div>
                <a class="drawer-download" href="${escapeAttribute(data.download_url)}">
                    <span>${escapeHtml(gpaText.downloadPdf)}</span>
                    <strong>${escapeHtml(data.name)}</strong>
                </a>
                ${data.histories.map(term => `
                    <section class="drawer-term">
                        <div class="drawer-term-head"><strong>${escapeHtml(term.term)}</strong><span>${escapeHtml(gpaText.gpa)} ${term.semester_gpa}</span></div>
                        ${term.courses.map(course => `
                            <div class="drawer-course">
                                <code>${escapeHtml(course.code)}</code>
                                <span>${escapeHtml(course.title)}</span>
                                <strong>${escapeHtml(course.grade)}</strong>
                                <span>${course.credits} ${escapeHtml(gpaText.creditShort)}</span>
                            </div>
                        `).join('')}
                    </section>
                `).join('')}
            `;
            drawer.hidden = false;
            backdrop.hidden = false;
        });
    });

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }
</script>
@endpush

