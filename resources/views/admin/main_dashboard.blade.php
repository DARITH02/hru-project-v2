@extends('layouts.app')

@section('content')
    @php
        $studentCapacity = min(100, max(18, (int) round(($studentCount / max($studentCount + 220, 1)) * 100)));
        $teacherAvailability = min(100, max(12, (int) round(($teacherCount / max($teacherCount + 8, 1)) * 100)));
        $resultHealth = min(100, max(0, (int) round($attendanceRate)));
        $termLabel = 'Term ' . $semester;
        $subjectLabels = $subjectPerformance->pluck('name')->values();
        $subjectScores = $subjectPerformance->pluck('avg_score')->values();
        $subjectPassRates = $subjectPerformance->sortByDesc('pass_rate')->values();
        $subjectColors = $subjectPerformance->pluck('tone')->map(fn($tone) => match ($tone) {
            'emerald' => '#22d3a5',
            'purple' => '#a78bfa',
            'amber' => '#ffb547',
            'rose' => '#ff6b8a',
            'cyan' => '#22c7f0',
            'indigo' => '#818cf8',
            default => '#4f7cff',
        })->values();
        $subjectChartData = $subjectPerformance->values()->map(function ($subject) {
            return [
                'id' => $subject['id'] ?? null,
                'name' => $subject['name'],
                'avg_score' => $subject['avg_score'],
                'pass_rate' => $subject['pass_rate'],
                'tone' => $subject['tone'],
            ];
        })->values();
        $majorLabels = $majorComparison->pluck('name')->values();
        $majorScores = $majorComparison->pluck('avg_score')->values();
        $majorColors = $majorComparison->pluck('tone')->map(fn($tone) => match ($tone) {
            'emerald' => '#22d3a5',
            'purple' => '#a78bfa',
            'amber' => '#ffb547',
            'rose' => '#ff6b8a',
            'cyan' => '#22c7f0',
            'indigo' => '#818cf8',
            default => '#4f7cff',
        })->values();
        $hasMajorComparisonData = $majorComparison->isNotEmpty();
        $attendanceAbsenceRate = max(0, 100 - $attendanceRate);
    @endphp

    <div class="sample-admin-dashboard">
        <div class="sample-topline">
            <div>
                <h1 class="sample-page-title">Dashboard</h1>
                <p class="sample-page-subtitle">{{ $academicYear }} · {{ $termLabel }} · Academic Overview</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="sample-toolbar">
                @if($selectedSubjectId)
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                @endif
                <label class="sample-select-wrap">
                    <select name="academic_year" class="sample-select sample-select--compact" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" @selected($academicYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="sample-select-wrap">
                    <select name="semester" class="sample-select sample-select--compact" onchange="this.form.submit()">
                        <option value="1" @selected($semester === 1)>Term 1</option>
                        <option value="2" @selected($semester === 2)>Term 2</option>
                    </select>
                </label>
                <div class="sample-period-pill">{{ $academicYear }} · {{ $termLabel }}</div>
            </form>
        </div>

        <div class="sample-grid sample-grid--metrics">
            <article class="sample-card sample-metric-card sample-glow-blue">
                <div class="sample-metric-head">
                    <div class="sample-metric-icon tone-blue">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <span class="sample-badge sample-badge--green">{{ $departmentCount }} depts</span>
                </div>
                <div class="sample-metric-value">{{ number_format($studentCount) }}</div>
                <div class="sample-metric-label">Total Students</div>
                <div class="sample-progress"><div class="sample-progress-fill tone-blue" style="width: {{ $studentCapacity }}%"></div></div>
                <div class="sample-metric-meta"><span>{{ $studentCapacity }}% capacity</span><span class="metric-up">this term</span></div>
            </article>

            <article class="sample-card sample-metric-card">
                <div class="sample-metric-head">
                    <div class="sample-metric-icon tone-emerald">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M5 20v-1a7 7 0 0 1 14 0v1"/></svg>
                    </div>
                    <span class="sample-badge sample-badge--green">{{ $subjectCount }} subjects</span>
                </div>
                <div class="sample-metric-value">{{ number_format($teacherCount) }}</div>
                <div class="sample-metric-label">Active Teachers</div>
                <div class="sample-progress"><div class="sample-progress-fill tone-emerald" style="width: {{ $teacherAvailability }}%"></div></div>
                <div class="sample-metric-meta"><span>{{ $teacherAvailability }}% load balance</span><span class="metric-up">{{ $classCount }} classes</span></div>
            </article>

            <article class="sample-card sample-metric-card">
                <div class="sample-metric-head">
                    <div class="sample-metric-icon tone-amber">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <span class="sample-badge sample-badge--amber">{{ $classCount }} sessions</span>
                </div>
                <div class="sample-metric-value">{{ $attendanceRate }}%</div>
                <div class="sample-metric-label">Avg. Attendance</div>
                <div class="sample-progress"><div class="sample-progress-fill tone-amber" style="width: {{ $resultHealth }}%"></div></div>
                <div class="sample-metric-meta"><span>{{ $attendanceAbsenceRate }}% absence</span><span class="metric-down">live attendance data</span></div>
            </article>

            <article class="sample-card sample-metric-card">
                <div class="sample-metric-head">
                    <div class="sample-metric-icon tone-rose">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span class="sample-badge sample-badge--green">{{ number_format($resultCount) }} scores</span>
                </div>
                <div class="sample-metric-value">{{ $passRate }}%</div>
                <div class="sample-metric-label">Pass Rate</div>
                <div class="sample-progress"><div class="sample-progress-fill tone-rose" style="width: {{ $passRate }}%"></div></div>
                <div class="sample-metric-meta"><span>{{ $averageScore }}/100 avg score</span><span class="metric-up">current term</span></div>
            </article>
        </div>

        <div class="sample-grid sample-grid--three">
            <section class="sample-card sample-card--span-2">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Attendance Trend</h3>
                        <p class="sample-card-subtitle">Recent student and teacher attendance rate</p>
                    </div>
                    <div class="sample-legend">
                        <span><i class="tone-blue"></i>Students</span>
                        <span><i class="tone-emerald"></i>Teachers</span>
                    </div>
                </div>
                <div class="sample-chart h-52"><canvas id="attendanceChart"></canvas></div>
            </section>

            <section class="sample-card">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Major Comparison</h3>
                        <p class="sample-card-subtitle">Average student score across all majors</p>
                    </div>
                </div>
                @if($hasMajorComparisonData)
                    <div class="sample-chart sample-chart--major"><canvas id="majorComparisonChart"></canvas></div>
                @else
                    <div class="sample-grade-empty">
                        <div class="sample-grade-empty__icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3v18h18"/>
                                <path d="M7 14l3-3 3 2 4-5"/>
                            </svg>
                        </div>
                        <div class="sample-grade-empty__title">No major comparison yet</div>
                        <div class="sample-grade-empty__desc">No student score data is available for the selected term.</div>
                    </div>
                @endif
                <div class="sample-grade-list">
                    @foreach($majorComparison as $major)
                        <div>
                            <span><i style="background:{{ match ($major['tone']) {
                                'emerald' => '#22d3a5',
                                'purple' => '#a78bfa',
                                'amber' => '#ffb547',
                                'rose' => '#ff6b8a',
                                'cyan' => '#22c7f0',
                                'indigo' => '#818cf8',
                                default => '#4f7cff',
                            } }}"></i>{{ $major['name'] }}</span>
                            <strong>{{ rtrim(rtrim(number_format($major['avg_score'], 1), '0'), '.') }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="sample-grid sample-grid--three">
            <section class="sample-card sample-card--span-2">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Subject Performance</h3>
                        <p class="sample-card-subtitle">Average scores by subject this term</p>
                    </div>
                    <div class="sample-inline-filter">
                        <label class="sample-select-wrap">
                            <select name="subject_id" id="subjectPerformanceSelect" class="sample-select">
                                <option value="">All subjects</option>
                                @foreach($subjectOptions as $subject)
                                    <option value="{{ $subject->id }}" @selected($selectedSubjectId === (int) $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <div class="sample-chart h-52"><canvas id="subjectBar"></canvas></div>
            </section>

            <section class="sample-card">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Pass Rate by Subject</h3>
                        <p class="sample-card-subtitle">Ranked by performance</p>
                    </div>
                </div>
                <div class="sample-rank-list" id="subjectPassRateList">
                    @foreach($subjectPassRates as $item)
                        <div class="sample-rank-item">
                            <div class="sample-rank-item__top"><span>{{ $item['name'] }}</span><strong class="tone-text-{{ $item['tone'] }}">{{ $item['pass_rate'] }}%</strong></div>
                            <div class="sample-progress"><div class="sample-progress-fill tone-{{ $item['tone'] }}" style="width: {{ $item['pass_rate'] }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="sample-grid sample-grid--three">
            <section class="sample-card sample-card--span-2">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Teacher Status</h3>
                        <p class="sample-card-subtitle">Current load and average class results</p>
                    </div>
                    <div class="sample-filter-pill">{{ $teacherRows->count() }} visible</div>
                </div>
                <div class="sample-table-wrap">
                    <table class="sample-table">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Students</th>
                                <th>Avg Score</th>
                                <th>Load</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacherRows as $row)
                                <tr>
                                    <td>
                                        <div class="sample-teacher-cell">
                                            <div class="sample-avatar tone-{{ $row['accent'] }}">{{ $row['initials'] }}</div>
                                            <span>{{ $row['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $row['subject'] }}</td>
                                    <td>{{ $row['students'] }}</td>
                                    <td>
                                        <div class="sample-score-cell">
                                            <div class="sample-progress sample-progress--mini"><div class="sample-progress-fill tone-{{ $row['accent'] }}" style="width: {{ $row['score'] }}%"></div></div>
                                            <span>{{ $row['score'] }}</span>
                                        </div>
                                    </td>
                                    <td><span class="sample-pill sample-pill--{{ $row['load_tone'] }}">{{ $row['load'] }}</span></td>
                                    <td><span class="sample-pill sample-pill--{{ $row['status_tone'] }}">● {{ $row['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="sample-side-stack">
                <section class="sample-card">
                    <div class="sample-card-head">
                        <div>
                            <h3 class="sample-card-title">Student Alerts</h3>
                        </div>
                    </div>
                    <div class="sample-alert-list">
                        @foreach($alerts as $alert)
                            <div class="sample-alert sample-alert--{{ $alert['tone'] }}">
                                <div class="sample-alert__left">
                                    <div class="sample-avatar tone-{{ $alert['accent'] }}">{{ $alert['initials'] }}</div>
                                    <div>
                                        <div class="sample-alert__name">{{ $alert['name'] }}</div>
                                        <div class="sample-alert__sub">{{ $alert['sub'] }}</div>
                                    </div>
                                </div>
                                <strong>{{ $alert['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="sample-card">
                    <div class="sample-card-head">
                        <div>
                            <h3 class="sample-card-title">Activity Feed</h3>
                        </div>
                    </div>
                    <div class="sample-activity-list">
                        @foreach($activityFeed as $activity)
                            <div class="sample-activity-item">
                                <div class="sample-activity-icon tone-{{ $activity['accent'] }}">
                                    @switch($activity['accent'])
                                        @case('emerald')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                            </svg>
                                            @break
                                        @case('rose')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m10.29 3.86-7.08 12.26A2 2 0 0 0 4.94 19h14.12a2 2 0 0 0 1.73-2.88L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                                                <path d="M12 9v4"/>
                                                <path d="M12 17h.01"/>
                                            </svg>
                                            @break
                                        @case('amber')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M8 2v4"/>
                                                <path d="M16 2v4"/>
                                                <rect width="18" height="18" x="3" y="4" rx="2"/>
                                                <path d="M3 10h18"/>
                                            </svg>
                                            @break
                                        @case('purple')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m12 3 2.8 5.68 6.27.91-4.54 4.43 1.07 6.25L12 17.27l-5.6 2.94 1.07-6.25L2.93 9.6l6.27-.91Z"/>
                                            </svg>
                                            @break
                                        @default
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                                <path d="M16 13H8"/>
                                                <path d="M16 17H8"/>
                                                <path d="M10 9H8"/>
                                            </svg>
                                    @endswitch
                                </div>
                                <div class="sample-activity-body">
                                    <div class="sample-activity-item__title">{{ $activity['title'] }}</div>
                                    <div class="sample-activity-item__meta">{{ $activity['meta'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>

        <div class="sample-grid sample-grid--three">
            <section class="sample-card sample-card--span-2">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Monthly Enrollment vs Graduates</h3>
                        <p class="sample-card-subtitle">Last 6 months from the student table</p>
                    </div>
                    <div class="sample-legend">
                        <span><i class="tone-blue"></i>Enrolled</span>
                        <span><i class="tone-emerald"></i>Graduates</span>
                    </div>
                </div>
                <div class="sample-chart h-48"><canvas id="enrollChart"></canvas></div>
            </section>

            <section class="sample-card">
                <div class="sample-card-head">
                    <div>
                        <h3 class="sample-card-title">Operational Radar</h3>
                        <p class="sample-card-subtitle">Live delivery health across core indicators</p>
                    </div>
                </div>
                <div class="sample-chart h-52"><canvas id="radarChart"></canvas></div>
            </section>
        </div>

        <div class="sample-grid sample-module-grid">
            @foreach($modules as $module)
                <a href="{{ $module['route'] }}" class="sample-module-card">
                    <div class="sample-module-card__eyebrow">{{ $module['eyebrow'] }}</div>
                    <div class="sample-module-card__title">{{ $module['name'] }}</div>
                    <p class="sample-module-card__text">{{ $module['description'] }}</p>
                    <div class="sample-module-card__value">{{ $module['metric'] }}</div>
                    <div class="sample-module-card__meta">{{ $module['detail'] }}</div>
                </a>
            @endforeach
        </div>

        <div class="sample-footer">
            {{-- <span>Academic year {{ $academicYear }} </span> --}}
            <span>Developed by <strong>Darith</strong> Team</span>
            <span>© {{ explode('-', $academicYear)[0] ?? now()->year }} HRU Dashboard</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            const charts = [];
            const subjectToneColors = {
                blue: '#4f7cff',
                emerald: '#22d3a5',
                purple: '#a78bfa',
                amber: '#ffb547',
                rose: '#ff6b8a',
                cyan: '#22c7f0',
                indigo: '#818cf8',
                slate: '#94a3b8'
            };
            const subjectPerformanceRows = @json($subjectChartData);
            const chartTheme = () => {
                const styles = getComputedStyle(document.documentElement);
                return {
                    text: styles.getPropertyValue('--muted').trim() || '#64748b',
                    strong: styles.getPropertyValue('--text').trim() || '#111827',
                    surface: styles.getPropertyValue('--surface').trim() || '#ffffff',
                    grid: document.documentElement.getAttribute('data-theme') === 'dark'
                        ? 'rgba(148,163,184,.16)'
                        : 'rgba(15,23,42,.08)',
                    tooltipBg: styles.getPropertyValue('--surface2').trim() || '#f8fafc',
                    tooltipBorder: styles.getPropertyValue('--border').trim() || 'rgba(15,23,42,.1)',
                };
            };

            const commonTooltip = (theme) => ({
                backgroundColor: theme.tooltipBg,
                borderColor: theme.tooltipBorder,
                borderWidth: 1,
                titleColor: theme.strong,
                bodyColor: theme.text,
                padding: 10
            });

            const tickConfig = (theme) => ({ color: theme.text, font: { size: 11, family: 'DM Sans' } });
            let activeTheme = chartTheme();

            Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
            Chart.defaults.color = activeTheme.text;

            const attendanceChart = document.getElementById('attendanceChart');
            if (attendanceChart) {
                charts.push(new Chart(attendanceChart, {
                    type: 'line',
                    data: {
                        labels: @json($attendanceLabels),
                        datasets: [
                            { label:'Students', data:@json($attendanceStudentSeries), borderColor:'#4f7cff', backgroundColor:'rgba(79,124,255,0.08)', borderWidth:2.5, pointRadius:4, pointBackgroundColor:'#4f7cff', pointBorderColor:activeTheme.surface, pointBorderWidth:2, fill:true, tension:0.4 },
                            { label:'Teachers', data:@json($attendanceTeacherSeries), borderColor:'#22d3a5', backgroundColor:'rgba(34,211,165,0.06)', borderWidth:2.5, pointRadius:4, pointBackgroundColor:'#22d3a5', pointBorderColor:activeTheme.surface, pointBorderWidth:2, fill:true, tension:0.4, borderDash:[5,3] }
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{display:false}, tooltip: commonTooltip(activeTheme) },
                        scales: {
                            y:{ min:0, max:100, ticks:{ ...tickConfig(activeTheme), callback: v=>v+'%' }, grid:{ color:activeTheme.grid }, border:{display:false} },
                            x:{ ticks:tickConfig(activeTheme), grid:{display:false}, border:{display:false} }
                        }
                    }
                }));
            }

            const majorComparisonChart = document.getElementById('majorComparisonChart');
            if (majorComparisonChart) {
                charts.push(new Chart(majorComparisonChart, {
                    type:'bar',
                    data: {
                        labels:@json($majorLabels),
                        datasets:[{
                            label:'Average Score',
                            data:@json($majorScores),
                            backgroundColor:@json($majorColors),
                            borderRadius:8,
                            borderSkipped:false
                        }]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{display:false}, tooltip:{ ...commonTooltip(activeTheme), callbacks:{ label: ctx => ` ${ctx.label}: ${ctx.parsed.y}/100` } } },
                        scales: {
                            y:{ min:0, max:100, ticks:tickConfig(activeTheme), grid:{ color:activeTheme.grid }, border:{display:false} },
                            x:{ ticks:tickConfig(activeTheme), grid:{display:false}, border:{display:false} }
                        }
                    }
                }));
            }

            const subjectBar = document.getElementById('subjectBar');
            let subjectBarChart = null;
            if (subjectBar) {
                subjectBarChart = new Chart(subjectBar, {
                    type:'bar',
                    data: {
                        labels:@json($subjectLabels),
                        datasets:[{ label:'Avg Score', data:@json($subjectScores), backgroundColor:@json($subjectColors), borderRadius:6, borderSkipped:false }]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{display:false}, tooltip: commonTooltip(activeTheme) },
                        scales: {
                            y:{ min:0, max:100, ticks:tickConfig(activeTheme), grid:{ color:activeTheme.grid }, border:{display:false} },
                            x:{ ticks:tickConfig(activeTheme), grid:{display:false}, border:{display:false} }
                        }
                    }
                });
                charts.push(subjectBarChart);
            }

            const enrollChart = document.getElementById('enrollChart');
            if (enrollChart) {
                charts.push(new Chart(enrollChart, {
                    type:'bar',
                    data: {
                        labels:@json($enrollmentSeries['labels']),
                        datasets:[
                            { label:'Enrolled', data:@json($enrollmentSeries['enrolled']), backgroundColor:'rgba(79,124,255,0.75)', borderRadius:5, borderSkipped:false },
                            { label:'Graduates', data:@json($enrollmentSeries['graduated']), backgroundColor:'rgba(34,211,165,0.75)', borderRadius:5, borderSkipped:false }
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{display:false}, tooltip: commonTooltip(activeTheme) },
                        scales: {
                            y:{ ticks:tickConfig(activeTheme), grid:{ color:activeTheme.grid }, border:{display:false} },
                            x:{ ticks:tickConfig(activeTheme), grid:{display:false}, border:{display:false} }
                        }
                    }
                }));
            }

            const radarChart = document.getElementById('radarChart');
            if (radarChart) {
                charts.push(new Chart(radarChart, {
                    type:'radar',
                    data: {
                        labels:@json($radarMetrics['labels']),
                        datasets:[
                            { label:'Current', data:@json($radarMetrics['series']), backgroundColor:'rgba(79,124,255,0.15)', borderColor:'#4f7cff', borderWidth:2, pointBackgroundColor:'#4f7cff', pointRadius:4 },
                            { label:'Target', data:@json($radarMetrics['target']), backgroundColor:'rgba(34,211,165,0.06)', borderColor:'rgba(34,211,165,0.4)', borderWidth:1.5, borderDash:[4,3], pointRadius:0 }
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{display:false}, tooltip: commonTooltip(activeTheme) },
                        scales: {
                            r: {
                                min:0, max:100,
                                ticks:{ display:false },
                                grid:{ color:activeTheme.grid },
                                pointLabels:{ color:activeTheme.text, font:{ size:10, family:'DM Sans' } },
                                angleLines:{ color:activeTheme.grid }
                            }
                        }
                    }
                }));
            }

            const subjectSelect = document.getElementById('subjectPerformanceSelect');
            const subjectPassRateList = document.getElementById('subjectPassRateList');

            const renderSubjectPassRateList = (rows) => {
                if (!subjectPassRateList) return;

                subjectPassRateList.innerHTML = rows.map((item) => `
                    <div class="sample-rank-item">
                        <div class="sample-rank-item__top">
                            <span>${item.name}</span>
                            <strong class="tone-text-${item.tone}">${item.pass_rate}%</strong>
                        </div>
                        <div class="sample-progress">
                            <div class="sample-progress-fill tone-${item.tone}" style="width: ${item.pass_rate}%"></div>
                        </div>
                    </div>
                `).join('');
            };

            const applySubjectPerformanceFilter = () => {
                if (!subjectSelect || !subjectBarChart) return;

                const selectedId = subjectSelect.value;
                const filteredRows = selectedId
                    ? subjectPerformanceRows.filter((row) => String(row.id) === selectedId)
                    : subjectPerformanceRows.slice();

                const sortedRows = filteredRows.slice().sort((a, b) => b.pass_rate - a.pass_rate);

                subjectBarChart.data.labels = filteredRows.map((row) => row.name);
                subjectBarChart.data.datasets[0].data = filteredRows.map((row) => row.avg_score);
                subjectBarChart.data.datasets[0].backgroundColor = filteredRows.map((row) => subjectToneColors[row.tone] || subjectToneColors.blue);
                subjectBarChart.update();

                renderSubjectPassRateList(sortedRows);
                const url = new URL(window.location.href);
                if (selectedId) {
                    url.searchParams.set('subject_id', selectedId);
                } else {
                    url.searchParams.delete('subject_id');
                }
                window.history.replaceState({}, '', url);
            };

            if (subjectSelect) {
                subjectSelect.addEventListener('change', applySubjectPerformanceFilter);
                applySubjectPerformanceFilter();
            }

            const updateChartTheme = () => {
                activeTheme = chartTheme();
                Chart.defaults.color = activeTheme.text;
                charts.forEach((chart) => {
                    if (chart.config.type === 'doughnut') {
                        chart.data.datasets.forEach(dataset => dataset.borderColor = activeTheme.surface);
                    }

                    if (chart.config.type === 'line') {
                        chart.data.datasets.forEach(dataset => dataset.pointBorderColor = activeTheme.surface);
                    }

                    if (chart.options.plugins?.tooltip) {
                        Object.assign(chart.options.plugins.tooltip, commonTooltip(activeTheme));
                    }

                    Object.values(chart.options.scales || {}).forEach((scale) => {
                        if (scale.ticks) scale.ticks.color = activeTheme.text;
                        if (scale.grid && scale.grid.display !== false) scale.grid.color = activeTheme.grid;
                    });

                    if (chart.options.scales?.r) {
                        chart.options.scales.r.grid.color = activeTheme.grid;
                        chart.options.scales.r.angleLines.color = activeTheme.grid;
                        chart.options.scales.r.pointLabels.color = activeTheme.text;
                    }

                    chart.update('none');
                });
            };

            new MutationObserver(updateChartTheme).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });
        })();
    </script>
@endpush
