@extends('layouts.app')

@section('content')
    <div class="analytics-container" style="padding: 30px; animation: fadeIn 0.5s ease-out;">
        {{-- Header Section --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
            <div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                    <div
                        style="width:32px; height:32px; border-radius:10px; background:color-mix(in srgb, var(--accent) 13%, transparent); color:var(--accent); display:flex; align-items:center; justify-content:center;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div
                        style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--accent); letter-spacing:0.1em; text-transform:uppercase;">
                        {{ __('admin_results.authority') }}</div>
                </div>
                <h1
                    style="font-family:var(--font-display); font-size:36px; font-weight:900; color:var(--text); letter-spacing:-0.02em; margin:0;">
                    {{ __('admin_results.title') }} <span style="color:var(--accent);">{{ __('admin_results.title_suffix') }}</span></h1>
                <p style="font-size:14px; color:var(--muted); margin-top:8px; font-weight:500;">{{ __('admin_results.subtitle') }}</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                {{-- Year Filter --}}
                <div
                    style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:4px 12px; display:flex; align-items:center; gap:8px;">
                    <span style="font-family:var(--font-mono); font-size:8px; font-weight:800; color:var(--muted);">{{ __('admin_results.academic_year') }}</span>
                    <select id="filterYear" onchange="applyFilters()"
                        style="background:transparent; border:none; color:var(--text); font-family:var(--font-mono); font-size:11px; font-weight:700; outline:none; cursor:pointer;">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ $academicYear == $year ? 'selected' : '' }}
                                style="background:var(--surface2);">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester Filter --}}
                <div
                    style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:4px 12px; display:flex; align-items:center; gap:8px;">
                    <span
                        style="font-family:var(--font-mono); font-size:8px; font-weight:800; color:var(--muted);">{{ __('admin_results.semester') }}</span>
                    <select id="filterSemester" onchange="applyFilters()"
                        style="background:transparent; border:none; color:var(--text); font-family:var(--font-mono); font-size:11px; font-weight:700; outline:none; cursor:pointer;">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }} style="background:var(--surface2);">{{ __('admin_results.term_1') }}
                        </option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }} style="background:var(--surface2);">{{ __('admin_results.term_2') }}
                        </option>
                    </select>
                </div>

                {{-- Year Level Filter --}}
                <div
                    style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:4px 12px; display:flex; align-items:center; gap:8px;">
                    <span style="font-family:var(--font-mono); font-size:8px; font-weight:800; color:var(--muted);">{{ __('admin_results.year_level') }}</span>
                    <select id="filterYearLevel" onchange="applyFilters()"
                        style="background:transparent; border:none; color:var(--text); font-family:var(--font-mono); font-size:11px; font-weight:700; outline:none; cursor:pointer;">
                        <option value="" style="background:var(--surface2);">{{ __('admin_results.all_years') }}</option>
                        @foreach([1, 2, 3, 4] as $lvl)
                            <option value="{{ $lvl }}" {{ $yearLevel == $lvl ? 'selected' : '' }}
                                style="background:var(--surface2);">{{ __('admin_results.year_number', ['year' => $lvl]) }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="width:1px; height:24px; background:var(--border); margin:0 8px;"></div>

                {{-- Export Group --}}
                <div style="display:flex; gap:6px;">
                    <a href="{{ route('admin.results.export.excel', request()->all()) }}" class="btn-primary"
                        style="width:36px; height:36px; padding:0; border-radius:10px; background:var(--surface2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text2);"
                        title="{{ __('admin_results.export_excel') }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.results.export.pdf', request()->all()) }}" class="btn-primary"
                        style="width:36px; height:36px; padding:0; border-radius:10px; background:var(--surface2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text2);"
                        title="{{ __('admin_results.download_pdf') }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </a>
                    <form action="{{ route('admin.results.send-telegram', request()->all()) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-primary"
                            style="width:36px; height:36px; padding:0; border-radius:10px; background:var(--accent); border:none; display:flex; align-items:center; justify-content:center; color:white;"
                            title="{{ __('admin_results.send_telegram') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- 📊 STAT CARDS --}}
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:24px; margin-bottom:40px;">
            {{-- Avg Score --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--accent) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_results.average_performance') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--text); line-height:1;">{{ $avgScore }}<span
                        style="font-size:18px; color:var(--muted); font-weight:600;">/100</span></div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--green); font-weight:800; font-size:12px;">+2.4%</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_results.from_last_period') }}</span>
                </div>
            </div>

            {{-- Pass Rate --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--green) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_results.student_pass_rate') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--green); line-height:1;">{{ $passRate }}%</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--green); font-weight:800; font-size:12px;">{{ __('admin_results.optimal') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_results.above_target') }}</span>
                </div>
            </div>

            {{-- Enrollment --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--violet) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_results.active_enrollment') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--text); line-height:1;">{{ $totalStudents }}</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--accent); font-weight:800; font-size:12px;">{{ __('admin_results.secure') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_results.across_classes', ['count' => $totalClasses]) }}</span>
                </div>
            </div>

            {{-- Hub Status --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--amber) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_results.system_analytics') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--text); line-height:1;">98.2<span
                        style="font-size:18px; color:var(--muted); font-weight:600;">%</span></div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--green); font-weight:800; font-size:12px;">{{ __('admin_results.active') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_results.last_sync') }}</span>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
            {{-- Departmental Performance --}}
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; overflow:hidden;">
                <div
                    style="padding:25px 30px; background:var(--surface3); display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border);">
                    <div>
                        <div
                            style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--accent); letter-spacing:0.1em; margin-bottom:4px;">
                            {{ __('admin_results.distribution') }}</div>
                        <div style="font-size:16px; font-weight:800; color:var(--text);">{{ __('admin_results.departmental_performance') }}</div>
                    </div>
                    <div style="font-size:10px; font-weight:700; color:var(--muted); font-family:var(--font-mono);">{{ __('admin_results.updated_live') }}</div>
                </div>
                <div style="padding:30px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left;">
                                <th
                                    style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase;">
                                    {{ __('admin_results.faculty_department') }}</th>
                                <th
                                    style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:center;">
                                    {{ __('admin_results.records') }}</th>
                                <th
                                    style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:center;">
                                    {{ __('admin_results.avg_score') }}</th>
                                <th
                                    style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:right;">
                                    {{ __('admin_results.pass_rate') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deptStats as $dept)
                                <tr style="border-top:1px solid var(--border); transition:background 0.2s;"
                                    onmouseover="this.style.background='var(--surface3)'"
                                    onmouseout="this.style.background='transparent'">
                                    <td style="padding:20px 10px;">
                                        <div style="font-weight:800; color:var(--text); font-size:14px;">{{ $dept['name'] }}
                                        </div>
                                        <div style="font-size:10px; color:var(--muted); margin-top:2px;">{{ __('admin_results.academic_sector', ['number' => '0' . $loop->iteration]) }}</div>
                                    </td>
                                    <td style="padding:20px 10px; text-align:center;">
                                        <div
                                            style="font-family:var(--font-mono); font-size:12px; font-weight:700; color:var(--text2);">
                                            {{ $dept['count'] }}</div>
                                    </td>
                                    <td style="padding:20px 10px; text-align:center;">
                                        <div
                                            style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--text);">
                                            {{ $dept['avg'] }}</div>
                                    </td>
                                    <td style="padding:20px 10px; text-align:right;">
                                        <div style="display:inline-flex; align-items:center; gap:10px;">
                                            <div
                                                style="width:80px; height:6px; background:var(--surface3); border-radius:3px; overflow:hidden;">
                                                <div
                                                    style="width:{{ $dept['pass_rate'] }}%; height:100%; background:{{ $dept['pass_rate'] > 75 ? 'var(--green)' : 'var(--amber)' }};">
                                                </div>
                                            </div>
                                            <span
                                                style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:{{ $dept['pass_rate'] > 75 ? 'var(--green)' : 'var(--amber)' }};">{{ $dept['pass_rate'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top Performers --}}
            <div
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; overflow:hidden; height: fit-content;">
                <div style="padding:25px 30px; background:var(--surface3); border-bottom:1px solid var(--border);">
                    <div
                        style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--accent); letter-spacing:0.1em; margin-bottom:4px;">
                        {{ __('admin_results.leaderboard') }}</div>
                    <div style="font-size:16px; font-weight:800; color:var(--text);">{{ __('admin_results.top_performers') }}</div>
                </div>
                <div style="padding:20px;">
                    @foreach($topStudents as $student)
                        <div
                            style="padding:15px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid color-mix(in srgb, var(--border) 27%, transparent); @if($loop->last) border-bottom:none; @endif">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div
                                    style="width:36px; height:36px; border-radius:50%; background:var(--surface3); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--accent);">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <div style="font-size:13px; font-weight:700; color:var(--text);">
                                        {{ $student['student_name'] }}</div>
                                    <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted);">
                                        {{ $student['student_code'] }}</div>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div
                                    style="font-family:var(--font-display); font-size:16px; font-weight:900; color:var(--green);">
                                    {{ round($student['avg_score'], 1) }}</div>
                                <div style="font-size:8px; font-weight:800; color:var(--muted); text-transform:uppercase;">{{ __('admin_results.avg_score') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 📜 DETAILED STUDENT RESULTS (TRANSCRIPT VIEW) --}}
        <div
            style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; overflow:hidden; margin-top:40px;">
            <div
                style="padding:25px 30px; background:var(--surface3); display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border);">
                <div>
                    <div
                        style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--accent); letter-spacing:0.1em; margin-bottom:4px;">
                        {{ __('admin_results.graduation_transcript') }}</div>
                    <div style="font-size:16px; font-weight:800; color:var(--text);">{{ __('admin_results.semester_summary') }}</div>
                </div>
                <div style="display:flex; gap:10px;">
                    <input type="text" placeholder="{{ __('admin_results.search_transcripts') }}"
                        style="height:36px; background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:0 15px; color:var(--text); font-size:11px; font-family:var(--font-mono); width:250px;">
                </div>
            </div>
            <div style="padding:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left; background:color-mix(in srgb, var(--surface3) 27%, transparent);">
                            <th
                                style="padding:15px 10px; text-align:center; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; width:60px;">
                                {{ __('admin_results.no') }}</th>
                            <th
                                style="padding:15px 30px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase;">
                                {{ __('admin_results.student_code') }}</th>
                            <th
                                style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:center;">
                                {{ __('admin_results.subjects') }}</th>
                            <th
                                style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:center;">
                                {{ __('admin_results.gpa_avg') }}</th>
                            <th
                                style="padding:15px 10px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:center;">
                                {{ __('admin_results.grade') }}</th>
                            <th
                                style="padding:15px 30px; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); text-transform:uppercase; text-align:right;">
                                {{ __('admin_results.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedResults as $groupName => $students)
                            {{-- Group Header Row --}}
                            <tr style="background:color-mix(in srgb, var(--surface3) 53%, transparent); border-top:2px solid var(--border);">
                                <td colspan="5" style="padding:12px 30px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:8px; height:8px; border-radius:2px; background:var(--accent);"></div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text); letter-spacing:0.05em;">
                                            {{ __('admin_results.major_group', ['group' => $groupName ?? __('admin_results.unassigned')]) }}</div>
                                        <div
                                            style="font-size:10px; font-weight:700; color:var(--muted); padding:2px 8px; border-radius:4px; background:var(--surface2); border:1px solid var(--border);">
                                            {{ __('admin_results.students_count', ['count' => $students->count()]) }}</div>
                                    </div>
                                </td>
                            </tr>

                            @foreach($students as $res)
                                <tr style="border-top:1px solid var(--border); transition:background 0.2s;"
                                    onmouseover="this.style.background='color-mix(in srgb, var(--surface3) 27%, transparent)'"
                                    onmouseout="this.style.background='transparent'">
                                    <td style="padding:18px 10px; text-align:center;">
                                        <div style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--muted);">{{ $loop->iteration }}</div>
                                    </td>
                                    <td style="padding:18px 30px;">
                                        <div style="font-weight:700; color:var(--text); font-size:13px;">{{ $res['student_name'] }}
                                        </div>
                                        <div
                                            style="font-family:var(--font-mono); font-size:9px; color:var(--muted); margin-top:2px;">
                                            {{ $res['student_code'] }}</div>
                                    </td>
                                    <td style="padding:18px 10px; text-align:center;">
                                        <div
                                            style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text2);">
                                            {{ __('admin_results.units', ['count' => $res['total_subjects']]) }}</div>
                                    </td>
                                    <td style="padding:18px 10px; text-align:center;">
                                        <div
                                            style="font-family:var(--font-display); font-size:18px; font-weight:900; color:var(--text);">
                                            {{ round($res['avg_score'], 1) }}</div>
                                    </td>
                                    <td style="padding:18px 10px; text-align:center;">
                                        <div
                                            style="width:32px; height:32px; margin:0 auto; border-radius:8px; background:{{ $res['status'] === 'PASSED' ? 'color-mix(in srgb, var(--green) 7%, transparent)' : 'color-mix(in srgb, var(--red) 7%, transparent)' }}; color:{{ $res['status'] === 'PASSED' ? 'var(--green)' : 'var(--red)' }}; display:flex; align-items:center; justify-content:center; font-weight:900; font-family:var(--font-display); border:1px solid {{ $res['status'] === 'PASSED' ? 'color-mix(in srgb, var(--green) 20%, transparent)' : 'color-mix(in srgb, var(--red) 20%, transparent)' }};">
                                            {{ $res['grade'] }}
                                        </div>
                                    </td>
                                    <td style="padding:18px 30px; text-align:right;">
                                        <span
                                            style="font-family:var(--font-mono); font-size:9px; font-weight:800; padding:4px 10px; border-radius:20px; background:{{ $res['status'] === 'PASSED' ? 'var(--green)' : 'var(--red)' }}; color:white; letter-spacing:0.05em;">
                                            {{ $res['status'] === 'PASSED' ? __('admin_results.passed') : __('admin_results.failed') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function applyFilters() {
                const year = document.getElementById('filterYear').value;
                const semester = document.getElementById('filterSemester').value;
                const yearLevel = document.getElementById('filterYearLevel').value;
                const url = new URL(window.location.href);
                url.searchParams.set('academic_year', year);
                url.searchParams.set('semester', semester);
                if (yearLevel) url.searchParams.set('year_level', yearLevel);
                else url.searchParams.delete('year_level');
                window.location.href = url.toString();
            }
        </script>
    @endpush
@endsection
