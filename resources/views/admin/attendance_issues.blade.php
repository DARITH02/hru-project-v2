@extends('layouts.app')

@section('content')
    <div class="analytics-container" style="padding: 30px; animation: fadeIn 0.5s ease-out;">
        {{-- Custom Flash Alert System --}}
        @if(session('success'))
            <div style="background:rgba(34, 197, 94, 0.12); border:1px solid rgba(34, 197, 94, 0.3); border-radius:16px; padding:15px 25px; margin-bottom:25px; display:flex; align-items:center; gap:12px; animation: slideIn 0.3s ease-out;">
                <div style="width:24px; height:24px; border-radius:50%; background:var(--green); color:white; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:11px;">✓</div>
                <div style="font-size:13.5px; font-weight:700; color:var(--green);">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="background:rgba(239, 68, 68, 0.12); border:1px solid rgba(239, 68, 68, 0.3); border-radius:16px; padding:15px 25px; margin-bottom:25px; display:flex; align-items:center; gap:12px; animation: slideIn 0.3s ease-out;">
                <div style="width:24px; height:24px; border-radius:50%; background:var(--red); color:white; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:11px;">!</div>
                <div style="font-size:13.5px; font-weight:700; color:var(--red);">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div style="background:rgba(245, 158, 11, 0.12); border:1px solid rgba(245, 158, 11, 0.3); border-radius:16px; padding:15px 25px; margin-bottom:25px; display:flex; align-items:center; gap:12px; animation: slideIn 0.3s ease-out;">
                <div style="width:24px; height:24px; border-radius:50%; background:var(--amber); color:white; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:11px;">!</div>
                <div style="font-size:13.5px; font-weight:700; color:var(--amber);">
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        {{-- Header Section --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
            <div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                    <div
                        style="width:32px; height:32px; border-radius:10px; background:color-mix(in srgb, var(--red) 13%, transparent); color:var(--red); display:flex; align-items:center; justify-content:center;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div
                        style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--red); letter-spacing:0.1em; text-transform:uppercase;">
                        {{ __('admin_attendance_issues.monitor') }}</div>
                </div>
                <h1
                    style="font-family:var(--font-display); font-size:36px; font-weight:900; color:var(--text); letter-spacing:-0.02em; margin:0;">
                    {{ __('admin_attendance_issues.title') }} <span style="color:var(--red);">{{ __('admin_attendance_issues.title_suffix') }}</span></h1>
                <p style="font-size:14px; color:var(--muted); margin-top:8px; font-weight:500;">
                    {{ __('admin_attendance_issues.subtitle') }}
                </p>
            </div>
            
            <div style="display:flex; gap:10px; align-items:center;">
                {{-- Year Filter --}}
                <div
                    style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:4px 12px; display:flex; align-items:center; gap:8px;">
                    <span style="font-family:var(--font-mono); font-size:8px; font-weight:800; color:var(--muted);">{{ __('admin_attendance_issues.academic_year') }}</span>
                    <select id="filterYear" onchange="applyFilters()"
                        style="background:transparent; border:none; color:var(--text); font-family:var(--font-mono); font-size:11px; font-weight:700; outline:none; cursor:pointer;">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ $academicYear == $year ? 'selected' : '' }}
                                style="background:var(--surface2); color:var(--text);">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester Filter --}}
                <div
                    style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:4px 12px; display:flex; align-items:center; gap:8px;">
                    <span style="font-family:var(--font-mono); font-size:8px; font-weight:800; color:var(--muted);">{{ __('admin_attendance_issues.semester') }}</span>
                    <select id="filterSemester" onchange="applyFilters()"
                        style="background:transparent; border:none; color:var(--text); font-family:var(--font-mono); font-size:11px; font-weight:700; outline:none; cursor:pointer;">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }} style="background:var(--surface2); color:var(--text);">{{ __('admin_attendance_issues.term_1') }}</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }} style="background:var(--surface2); color:var(--text);">{{ __('admin_attendance_issues.term_2') }}</option>
                    </select>
                </div>

                <div style="width:1px; height:24px; background:var(--border); margin:0 8px;"></div>

                {{-- Export Group --}}
                <div style="display:flex; gap:6px;">
                    <a href="{{ route('admin.attendance-issues.export.pdf', request()->all()) }}" class="btn-primary"
                        style="width:36px; height:36px; padding:0; border-radius:10px; background:var(--surface2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text2);"
                        title="{{ __('admin_attendance_issues.download_pdf') }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </a>
                    <form action="{{ route('admin.attendance-issues.send-telegram', request()->all()) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-primary"
                            style="width:36px; height:36px; padding:0; border-radius:10px; background:var(--red); border:none; display:flex; align-items:center; justify-content:center; color:white; cursor:pointer;"
                            title="{{ __('admin_attendance_issues.send_telegram') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div style="width:1px; height:24px; background:var(--border); margin:0 8px;"></div>

                
            </div>
            
            
        </div>  
        <div class="search-wrap" style="max-width:30%;margin-bottom: 25px;">
            <form method="GET" action="{{ route('admin.attendance-issues') }}" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                    <input name="search" class="search-input" type="text"
                        placeholder="{{ __('admin_attendance_issues.search_placeholder') }}"
                        value="{{ request('search') }}"
                        style="padding-left:34px; width:100%; box-sizing:border-box;">
                <button type="submit" class="btn-primary"
                    title="{{ __('admin_attendance_issues.search') }}"
                    style="background:var(--surface3); border:1px solid var(--border); color:var(--text2); box-shadow:none; padding:8px 14px; display:flex; align-items:center; gap:5px; white-space:nowrap;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    {{ __('admin_attendance_issues.search') }}
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.attendance-issues') }}?academic_year={{ $academicYear }}&semester={{ $semester }}"
                        class="btn-secondary"
                        title="{{ __('admin_attendance_issues.clear') }}"
                        style="padding:8px 14px; display:flex; align-items:center; gap:5px; white-space:nowrap;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        {{ __('admin_attendance_issues.clear') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- 📊 STAT CARDS --}}
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:24px; margin-bottom:40px;">
            {{-- Blacklisted --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--red) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_attendance_issues.blacklisted_card') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--red); line-height:1;">{{ $totalBlacklisted }}</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--red); font-weight:800; font-size:12px;">⚠️ {{ __('admin_attendance_issues.restricted') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_attendance_issues.excluded_exams') }}</span>
                </div>
            </div>

            {{-- At Risk --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--amber) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_attendance_issues.at_risk_card') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--amber); line-height:1;">{{ $totalAtRisk }}</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--amber); font-weight:800; font-size:12px;">{{ __('admin_attendance_issues.warning') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_attendance_issues.approaching_limit') }}</span>
                </div>
            </div>

            {{-- Normal --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--green) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_attendance_issues.secure_good') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--green); line-height:1;">{{ $totalNormal }}</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--green); font-weight:800; font-size:12px;">🟢 {{ __('admin_attendance_issues.exempt') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_attendance_issues.attendance_stable') }}</span>
                </div>
            </div>

            {{-- Avg Absence Rate --}}
            <div class="stat-card"
                style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; padding:30px; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; top:-20px; right:-20px; width:120px; height:120px; background:color-mix(in srgb, var(--accent) 3%, transparent); border-radius:50%; blur:40px;">
                </div>
                <div
                    style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); margin-bottom:12px; letter-spacing:0.05em;">
                    {{ __('admin_attendance_issues.avg_absence_rate') }}</div>
                <div style="font-size:42px; font-weight:900; color:var(--text); line-height:1;">{{ $avgAbsenceRate }}%</div>
                <div style="margin-top:15px; display:flex; align-items:center; gap:6px;">
                    <span style="color:var(--accent); font-weight:800; font-size:12px;">{{ __('admin_attendance_issues.systemic') }}</span>
                    <span style="color:var(--muted); font-size:11px; font-weight:600;">{{ __('admin_attendance_issues.across_groups') }}</span>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT TABS --}}
        <div style="background:var(--surface2); border:1px solid var(--border); border-radius:24px; overflow:hidden; margin-top:20px;">
            <div style="padding:20px 30px; background:var(--surface3); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; gap:15px;">
                    <button class="btn-tab active" onclick="switchTab('blacklist-tab')" id="btn-blacklist-tab"
                        style="background:transparent; border:none; padding:10px 18px; font-family:var(--font-display); font-size:14px; font-weight:800; cursor:pointer; color:var(--text); border-bottom:3px solid var(--red); transition:all 0.2s;">
                        🚫 {{ __('admin_attendance_issues.tab_blacklisted') }} ({{ $totalBlacklisted }})
                    </button>
                    <button class="btn-tab" onclick="switchTab('atrisk-tab')" id="btn-atrisk-tab"
                        style="background:transparent; border:none; padding:10px 18px; font-family:var(--font-display); font-size:14px; font-weight:700; cursor:pointer; color:var(--muted); border-bottom:3px solid transparent; transition:all 0.2s;">
                        ⚠️ {{ __('admin_attendance_issues.tab_at_risk') }} ({{ $totalAtRisk }})
                    </button>
                    <button class="btn-tab" onclick="switchTab('all-tab')" id="btn-all-tab"
                        style="background:transparent; border:none; padding:10px 18px; font-family:var(--font-display); font-size:14px; font-weight:700; cursor:pointer; color:var(--muted); border-bottom:3px solid transparent; transition:all 0.2s;">
                        👥 {{ __('admin_attendance_issues.tab_all_registry') }} ({{ $processedStudents->count() }})
                    </button>
                    <button class="btn-tab" onclick="switchTab('history-tab')" id="btn-history-tab"
                        style="background:transparent; border:none; padding:10px 18px; font-family:var(--font-display); font-size:14px; font-weight:700; cursor:pointer; color:var(--muted); border-bottom:3px solid transparent; transition:all 0.2s;">
                        📋 {{ __('admin_attendance_issues.tab_event_registry') }} ({{ $restoreHistories->count() }})
                    </button>
                </div>
                <div style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted2);">
                    {{ __('admin_attendance_issues.critical_limit') }}
                </div>
            </div>

            {{-- ──────── TAB: BLACKLIST ──────── --}}
            <div id="blacklist-tab" class="tab-pane" style="display:block;">
                <div class="table-responsive">
                    <table class="att-table" style="width:100%;">
                        <thead>
                            <tr style="text-align:left; background:color-mix(in srgb, var(--surface3) 27%, transparent);">
                                <th style="padding:15px 25px; width:60px;">{{ __('admin_attendance_issues.identity') }}</th>
                                <th>{{ __('admin_attendance_issues.student_code') }}</th>
                                <th>{{ __('admin_attendance_issues.major_group') }}</th>
                                <th style="text-align:center;">{{ __('admin_attendance_issues.cumulative_absences') }}</th>
                                <th>{{ __('admin_attendance_issues.status') }}</th>
                                <th style="text-align:right; padding-right:25px;">{{ __('admin_attendance_issues.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blacklistedGrouped as $groupName => $items)
                                {{-- Group Header Row --}}
                                <tr style="background:color-mix(in srgb, var(--surface3) 67%, transparent); border-top:1px solid var(--border);">
                                    <td colspan="6" style="padding:12px 25px;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width:8px; height:8px; border-radius:2px; background:var(--red);"></div>
                                            <div style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text); letter-spacing:0.05em;">
                                                {{ __('admin_attendance_issues.class_group', ['group' => strtoupper($groupName)]) }}
                                            </div>
                                            <div style="font-size:10px; font-weight:700; color:var(--muted); padding:2px 8px; border-radius:4px; background:var(--surface2); border:1px solid var(--border);">
                                                {{ __('admin_attendance_issues.blacklisted_count', ['count' => count($items)]) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($items as $item)
                                    <tr style="border-top:1px solid var(--border);">
                                        <td style="padding:15px 25px;">
                                            <div style="display:flex; align-items:center; gap:12px;">
                                                <div style="width:36px; height:36px; border-radius:50%; background:color-mix(in srgb, var(--red) 8%, transparent); color:var(--red); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px;">
                                                    {{ strtoupper(substr($item['student']->user->name, 0, 2)) }}
                                                </div>
                                                <div style="font-weight:700; color:var(--text); font-size:13px;">
                                                    {{ $item['student']->user->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-family:var(--font-mono); font-size:11px; font-weight:700; color:var(--accent);">
                                                {{ $item['student']->student_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size:12.5px; color:var(--text2);">
                                                {{ $item['student']->major->name ?? $item['student']->group->major->name ?? __('admin_attendance_issues.not_available') }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted); margin-top:2px;">
                                                {{ strtoupper($item['student']->group->name ?? __('admin_attendance_issues.no_group')) }}
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <div style="font-family:var(--font-display); font-size:18px; font-weight:900; color:var(--red);">
                                                {{ $item['absences'] }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase;">
                                                {{ __('admin_attendance_issues.sessions_absent') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-direction:column; gap:4px;">
                                                <span class="status-tag" style="background: rgba(239, 68, 68, 0.12); color: var(--red); border: 1px solid rgba(239, 68, 68, 0.25);">
                                                    <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--red); display: inline-block; margin-right: 5px;"></span>
                                                    {{ __('admin_attendance_issues.blacklisted') }}
                                                </span>
                                                @if($item['restore_count'] > 0)
                                                    <span style="font-family:var(--font-mono); font-size:9px; color:var(--muted); font-weight:700;">
                                                        🔄 {{ __('admin_attendance_issues.restored_count', ['count' => $item['restore_count']]) }}
                                                    </span>
                                                    @if($item['latest_restore'])
                                                        <div style="font-size:9px; color:var(--muted); margin-top:2px; font-weight:500; font-style:italic; max-width:180px;" title="{{ $item['latest_restore']->reason }}">
                                                            {{ $item['latest_restore']->reason }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td style="text-align:right; padding-right:25px;">
                                            @if($item['restore_count'] >= 2)
                                                <span class="status-tag" style="background:rgba(239, 68, 68, 0.08); color:var(--muted); border:1px solid var(--border); padding:6px 12px; font-size:9px; font-weight:800; font-family:var(--font-mono);">
                                                    🔒 {{ __('admin_attendance_issues.restore_limit') }}
                                                </span>
                                            @else
                                                <button type="button" class="btn-primary" 
                                                    onclick="openActionModal({{ $item['student']->id }}, '{{ addslashes($item['student']->user->name) }}', false, {{ $item['restore_count'] + 1 }})"
                                                    style="background:linear-gradient(135deg, var(--green), #22C55E); box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25); border:none; padding:6px 12px; font-size:9px;">
                                                    🟢 {{ __('admin_attendance_issues.restore_student', ['count' => $item['restore_count'] + 1]) }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:40px; text-align:center; color:var(--muted); font-family:var(--font-mono); font-size:12px;">
                                        🎉 {{ __('admin_attendance_issues.empty_blacklisted', ['year' => $academicYear, 'semester' => $semester]) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ──────── TAB: AT RISK ──────── --}}
            <div id="atrisk-tab" class="tab-pane" style="display:none;">
                <div class="table-responsive">
                    <table class="att-table" style="width:100%;">
                        <thead>
                            <tr style="text-align:left; background:color-mix(in srgb, var(--surface3) 27%, transparent);">
                                <th style="padding:15px 25px; width:60px;">{{ __('admin_attendance_issues.identity') }}</th>
                                <th>{{ __('admin_attendance_issues.student_code') }}</th>
                                <th>{{ __('admin_attendance_issues.major_group') }}</th>
                                <th style="text-align:center; width:220px;">{{ __('admin_attendance_issues.progress_to_blacklist') }}</th>
                                <th>{{ __('admin_attendance_issues.status') }}</th>
                                <th style="text-align:right; padding-right:25px;">{{ __('admin_attendance_issues.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($atRiskGrouped as $groupName => $items)
                                {{-- Group Header Row --}}
                                <tr style="background:color-mix(in srgb, var(--surface3) 67%, transparent); border-top:1px solid var(--border);">
                                    <td colspan="6" style="padding:12px 25px;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width:8px; height:8px; border-radius:2px; background:var(--amber);"></div>
                                            <div style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text); letter-spacing:0.05em;">
                                                {{ __('admin_attendance_issues.class_group', ['group' => strtoupper($groupName)]) }}
                                            </div>
                                            <div style="font-size:10px; font-weight:700; color:var(--muted); padding:2px 8px; border-radius:4px; background:var(--surface2); border:1px solid var(--border);">
                                                {{ __('admin_attendance_issues.at_risk_count', ['count' => count($items)]) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($items as $item)
                                    <tr style="border-top:1px solid var(--border);">
                                        <td style="padding:15px 25px;">
                                            <div style="display:flex; align-items:center; gap:12px;">
                                                <div style="width:36px; height:36px; border-radius:50%; background:color-mix(in srgb, var(--amber) 8%, transparent); color:var(--amber); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px;">
                                                    {{ strtoupper(substr($item['student']->user->name, 0, 2)) }}
                                                </div>
                                                <div style="font-weight:700; color:var(--text); font-size:13px;">
                                                    {{ $item['student']->user->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-family:var(--font-mono); font-size:11px; font-weight:700; color:var(--accent);">
                                                {{ $item['student']->student_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size:12.5px; color:var(--text2);">
                                                {{ $item['student']->major->name ?? $item['student']->group->major->name ?? __('admin_attendance_issues.not_available') }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted); margin-top:2px;">
                                                {{ strtoupper($item['student']->group->name ?? __('admin_attendance_issues.no_group')) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                                                <div style="width:100%; height:6px; background:var(--surface3); border-radius:3px; overflow:hidden;">
                                                    <div style="width:{{ ($item['absences'] / 30) * 100 }}%; height:100%; background:var(--amber); border-radius:3px;"></div>
                                                </div>
                                                <div style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--amber);">
                                                    {{ __('admin_attendance_issues.absences_count', ['count' => $item['absences']]) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-direction:column; gap:4px;">
                                                <span class="status-tag" style="background: rgba(245, 158, 11, 0.12); color: var(--amber); border: 1px solid rgba(245, 158, 11, 0.25);">
                                                    <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--amber); display: inline-block; margin-right: 5px;"></span>
                                                    {{ __('admin_attendance_issues.warning_at_risk') }}
                                                </span>
                                                @if($item['restore_count'] > 0)
                                                    <span style="font-family:var(--font-mono); font-size:9px; color:var(--muted); font-weight:700;">
                                                        🔄 {{ __('admin_attendance_issues.restored_count', ['count' => $item['restore_count']]) }}
                                                    </span>
                                                    @if($item['latest_restore'])
                                                        <div style="font-size:9px; color:var(--muted); margin-top:2px; font-weight:500; font-style:italic; max-width:180px;" title="{{ $item['latest_restore']->reason }}">
                                                            {{ $item['latest_restore']->reason }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td style="text-align:right; padding-right:25px;">
                                            <button type="button" class="btn-primary" 
                                                onclick="openActionModal({{ $item['student']->id }}, '{{ addslashes($item['student']->user->name) }}', true, 0)"
                                                style="background:linear-gradient(135deg, var(--red), #F87171); box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25); border:none; padding:6px 12px; font-size:9px;">
                                                🚫 {{ __('admin_attendance_issues.force_blacklist') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:40px; text-align:center; color:var(--muted); font-family:var(--font-mono); font-size:12px;">
                                        🟢 {{ __('admin_attendance_issues.empty_at_risk', ['year' => $academicYear, 'semester' => $semester]) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ──────── TAB: ALL REGISTRY ──────── --}}
            <div id="all-tab" class="tab-pane" style="display:none;">
                <div class="table-responsive">
                    <table class="att-table" style="width:100%;">
                        <thead>
                            <tr style="text-align:left; background:color-mix(in srgb, var(--surface3) 27%, transparent);">
                                <th style="padding:15px 25px; width:60px;">{{ __('admin_attendance_issues.identity') }}</th>
                                <th>{{ __('admin_attendance_issues.student_code') }}</th>
                                <th>{{ __('admin_attendance_issues.major_group') }}</th>
                                <th style="text-align:center;">{{ __('admin_attendance_issues.total_absences') }}</th>
                                <th>{{ __('admin_attendance_issues.status') }}</th>
                                <th style="text-align:right; padding-right:25px;">{{ __('admin_attendance_issues.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processedStudentsGrouped as $groupName => $items)
                                {{-- Group Header Row --}}
                                <tr style="background:color-mix(in srgb, var(--surface3) 67%, transparent); border-top:1px solid var(--border);">
                                    <td colspan="6" style="padding:12px 25px;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width:8px; height:8px; border-radius:2px; background:var(--accent);"></div>
                                            <div style="font-family:var(--font-mono); font-size:12px; font-weight:800; color:var(--text); letter-spacing:0.05em;">
                                                {{ __('admin_attendance_issues.class_group', ['group' => strtoupper($groupName)]) }}
                                            </div>
                                            <div style="font-size:10px; font-weight:700; color:var(--muted); padding:2px 8px; border-radius:4px; background:var(--surface2); border:1px solid var(--border);">
                                                {{ __('admin_attendance_issues.students_total', ['count' => count($items)]) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($items as $item)
                                    @php
                                        $isBlacklisted = $item['is_blacklisted_by_absences'];
                                        $isAtRisk = (!$isBlacklisted && $item['absences'] >= 10);
                                    @endphp
                                    <tr style="border-top:1px solid var(--border);">
                                        <td style="padding:15px 25px;">
                                            <div style="display:flex; align-items:center; gap:12px;">
                                                <div style="width:36px; height:36px; border-radius:50%; background:var(--surface3); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px;">
                                                    {{ strtoupper(substr(e($item['student']->user->name ?? 'UN'), 0, 2)) }}
                                                </div>
                                                <div style="font-weight:700; color:var(--text); font-size:13px;">
                                                    {{ $item['student']->user->name ?? __('admin_attendance_issues.unknown_student') }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-family:var(--font-mono); font-size:11px; font-weight:700; color:var(--accent);">
                                                {{ $item['student']->student_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size:12.5px; color:var(--text2);">
                                                {{ $item['student']->major->name ?? $item['student']->group->major->name ?? __('admin_attendance_issues.not_available') }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted); margin-top:2px;">
                                                {{ strtoupper($item['student']->group->name ?? __('admin_attendance_issues.no_group')) }}
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <div style="font-family:var(--font-display); font-size:18px; font-weight:900; color:{{ $isBlacklisted ? 'var(--red)' : ($isAtRisk ? 'var(--amber)' : 'var(--green)') }};">
                                                {{ $item['absences'] }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:8px; color:var(--muted); text-transform:uppercase;">
                                                {{ __('admin_attendance_issues.sessions_absent') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-direction:column; gap:4px;">
                                                @if($isBlacklisted)
                                                    <span class="status-tag" style="background: rgba(239, 68, 68, 0.12); color: var(--red); border: 1px solid rgba(239, 68, 68, 0.25);">
                                                        <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--red); display: inline-block; margin-right: 5px;"></span>
                                                        {{ __('admin_attendance_issues.blacklisted') }}
                                                    </span>
                                                @elseif($isAtRisk)
                                                    <span class="status-tag" style="background: rgba(245, 158, 11, 0.12); color: var(--amber); border: 1px solid rgba(245, 158, 11, 0.25);">
                                                        <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--amber); display: inline-block; margin-right: 5px;"></span>
                                                        {{ __('admin_attendance_issues.at_risk') }}
                                                    </span>
                                                @else
                                                    <span class="status-tag" style="background: rgba(34, 197, 94, 0.12); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.25);">
                                                        <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--green); display: inline-block; margin-right: 5px;"></span>
                                                        {{ __('admin_attendance_issues.secure') }}
                                                    </span>
                                                @endif
                                                @if($item['restore_count'] > 0)
                                                    <span style="font-family:var(--font-mono); font-size:9px; color:var(--muted); font-weight:700;">
                                                        🔄 {{ __('admin_attendance_issues.restored_count', ['count' => $item['restore_count']]) }}
                                                    </span>
                                                    @if($item['latest_restore'])
                                                        <div style="font-size:9px; color:var(--muted); margin-top:2px; font-weight:500; font-style:italic; max-width:180px;" title="{{ $item['latest_restore']->reason }}">
                                                            {{ $item['latest_restore']->reason }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td style="text-align:right; padding-right:25px;">
                                            @if($isBlacklisted)
                                                @if($item['restore_count'] >= 2)
                                                    <span class="status-tag" style="background:rgba(239, 68, 68, 0.08); color:var(--muted); border:1px solid var(--border); padding:6px 12px; font-size:9px; font-weight:800; font-family:var(--font-mono);">
                                                        🔒 {{ __('admin_attendance_issues.restore_limit') }}
                                                    </span>
                                                @else
                                                    <button type="button" class="btn-primary" 
                                                        onclick="openActionModal({{ $item['student']->id }}, '{{ addslashes($item['student']->user->name) }}', false, {{ $item['restore_count'] + 1 }})"
                                                        style="background:linear-gradient(135deg, var(--green), #22C55E); box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25); border:none; padding:6px 12px; font-size:9px;">
                                                        🟢 {{ __('admin_attendance_issues.restore_student', ['count' => $item['restore_count'] + 1]) }}
                                                    </button>
                                                @endif
                                            @else
                                                <button type="button" class="btn-primary" 
                                                    onclick="openActionModal({{ $item['student']->id }}, '{{ addslashes($item['student']->user->name) }}', true, 0)"
                                                    style="background:linear-gradient(135deg, var(--red), #F87171); box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25); border:none; padding:6px 12px; font-size:9px;">
                                                    🚫 {{ __('admin_attendance_issues.blacklist_student') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:40px; text-align:center; color:var(--muted); font-family:var(--font-mono); font-size:12px;">
                                        {{ __('admin_attendance_issues.empty_registered', ['year' => $academicYear, 'semester' => $semester]) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ──────── TAB: HISTORY REGISTRY ──────── --}}
    <div id="history-tab" class="tab-pane" style="display:none;">
        <div style="padding:30px;">

            {{-- Header + Action Toolbar --}}
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:22px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="width:10px; height:10px; border-radius:3px; background:#8B5CF6;"></div>
                    <h2 style="font-family:var(--font-display); font-size:18px; font-weight:900; color:var(--text); margin:0;">
                        {{ __('admin_attendance_issues.event_log_title') }}
                    </h2>
                    <span style="font-family:var(--font-mono); font-size:10px; color:var(--muted); font-weight:700; background:var(--surface3); border:1px solid var(--border); padding:3px 10px; border-radius:6px;">
                        {{ $academicYear }} · {{ __('admin_attendance_issues.sem_short', ['semester' => $semester]) }}
                    </span>
                    <span style="font-family:var(--font-mono); font-size:10px; color:var(--accent); font-weight:800; background:rgba(99,179,237,0.08); border:1px solid rgba(99,179,237,0.2); padding:3px 10px; border-radius:6px;">
                        {{ __('admin_attendance_issues.records_count', ['count' => $restoreHistories->count()]) }}
                    </span>
                    <span style="font-family:var(--font-mono); font-size:9px; color:var(--green); font-weight:700; background:rgba(34,197,94,0.07); border:1px solid rgba(34,197,94,0.2); padding:3px 10px; border-radius:6px;">
                        🟢 {{ __('admin_attendance_issues.restored_history_count', ['count' => $restoredHistoryCount]) }}
                    </span>
                </div>

                {{-- Action buttons --}}
                @if(!$restoreHistories->isEmpty())
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    {{-- Select All checkbox --}}
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); user-select:none;">
                        <input type="checkbox" id="selectAllHistory" onchange="toggleSelectAll(this)"
                            style="width:15px; height:15px; accent-color:#8B5CF6; cursor:pointer;">
                        {{ __('admin_attendance_issues.select_all') }}
                    </label>

                    {{-- Bulk Drop button --}}
                    @if(auth()->user()->role === 'super_admin')
                        <button type="button" id="bulkDropBtn" onclick="submitBulkDrop()" disabled
                            style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:var(--red); padding:7px 14px; border-radius:10px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; opacity:0.4; transition:opacity 0.2s;">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                            {{ __('admin_attendance_issues.drop_selected') }} (<span id="selectedCount">0</span>)
                        </button>
                    @else
                        <button type="button" id="bulkDropBtn" onclick="openUnauthorizedModal()" disabled
                            style="background:rgba(107,114,128,0.08); border:1px solid rgba(107,114,128,0.25); color:var(--muted); padding:7px 14px; border-radius:10px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:not-allowed; display:flex; align-items:center; gap:6px; opacity:0.55; transition:opacity 0.2s;">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            {{ __('admin_attendance_issues.drop_selected') }} (<span id="selectedCount">0</span>)
                        </button>
                    @endif

                    <div style="width:1px; height:20px; background:var(--border);"></div>

                    {{-- Drop All button --}}
                    @if(auth()->user()->role === 'super_admin')
                        <button type="button" onclick="confirmDropAll()"
                            style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.35); color:var(--red); padding:7px 14px; border-radius:10px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; transition:background 0.2s;"
                            onmouseenter="this.style.background='rgba(239,68,68,0.22)'" onmouseleave="this.style.background='rgba(239,68,68,0.12)'">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M9 6V4h6v2"/>
                            </svg>
                            {{ __('admin_attendance_issues.drop_all') }}
                        </button>
                    @else
                        <button type="button" onclick="openUnauthorizedModal()"
                            style="background:rgba(107,114,128,0.08); border:1px solid rgba(107,114,128,0.25); color:var(--muted); padding:7px 14px; border-radius:10px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s;"
                            onmouseenter="this.style.background='rgba(107,114,128,0.16)'" onmouseleave="this.style.background='rgba(107,114,128,0.08)'">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            {{ __('admin_attendance_issues.drop_all') }}
                        </button>
                    @endif
                </div>
                @endif
            </div>

            {{-- Hidden forms for delete actions --}}
            <form id="dropAllForm" method="POST" action="{{ route('admin.attendance-issues.history.drop-all') }}" style="display:none;">
                @csrf @method('DELETE')
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
            </form>
            <form id="bulkDropForm" method="POST" action="{{ route('admin.attendance-issues.history.bulk-drop') }}" style="display:none;">
                @csrf @method('DELETE')
                <div id="bulkDropIds"></div>
            </form>

            @if($restoreHistories->isEmpty())
                <div style="padding:60px; text-align:center; color:var(--muted); font-family:var(--font-mono); font-size:12px;">
                    <div style="font-size:48px; margin-bottom:16px;">📋</div>
                    {{ __('admin_attendance_issues.empty_history', ['year' => $academicYear, 'semester' => $semester]) }}
                </div>
            @else
                {{-- Timeline Feed --}}
                <div style="display:flex; flex-direction:column; gap:14px;">
                    @foreach($restoreHistories as $idx => $record)
                        @php
                            $isRestore    = str_contains(strtolower($record->reason ?? ''), 'authorized by');
                            $eventColor   = $isRestore ? 'var(--green)' : 'var(--red)';
                            $eventBg      = $isRestore ? 'rgba(34,197,94,0.07)' : 'rgba(239,68,68,0.07)';
                            $eventBorder  = $isRestore ? 'rgba(34,197,94,0.22)' : 'rgba(239,68,68,0.22)';
                            $eventIcon    = $isRestore ? '🟢' : '🚫';
                            $eventLabel   = $isRestore ? __('admin_attendance_issues.restored') : __('admin_attendance_issues.blacklisted');

                            // Parse "Authorized by: X | Details: Y" format
                            $authorizerName = null;
                            $detailReason   = $record->reason;
                            if ($record->reason && str_contains($record->reason, 'Authorized by:')) {
                                preg_match('/Authorized by:\s*(.+?)\s*\|\s*Details:\s*(.+)/s', $record->reason, $m);
                                $authorizerName = trim($m[1] ?? '');
                                $detailReason   = trim($m[2] ?? $record->reason);
                            }
                            $studentRestoreCount = $record->display_sequence ?? 1;
                        @endphp

                        <div class="history-card" data-id="{{ $record->id }}"
                             style="background:{{ $eventBg }}; border:1px solid {{ $eventBorder }}; border-radius:18px; padding:22px 26px; position:relative; overflow:hidden; transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.2s;"
                             onmouseenter="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(0,0,0,0.18)';"
                             onmouseleave="this.style.transform='translateY(0)'; this.style.boxShadow='none';">

                            {{-- Per-record checkbox --}}
                            <label style="position:absolute; top:14px; right:16px; z-index:10; cursor:pointer; display:flex; align-items:center; gap:5px;">
                                <input type="checkbox" class="history-checkbox" value="{{ $record->id }}"
                                    onchange="onCheckboxChange()"
                                    style="width:16px; height:16px; accent-color:#8B5CF6; cursor:pointer;">
                            </label>

                            {{-- Accent stripe --}}
                            <div style="position:absolute; left:0; top:0; bottom:0; width:4px; background:{{ $eventColor }}; border-radius:18px 0 0 18px;"></div>

                            <div style="display:grid; grid-template-columns:56px 1fr auto; gap:20px; align-items:start;">

                                {{-- Event icon + label --}}
                                <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                                    <div style="width:48px; height:48px; border-radius:14px; background:{{ $eventBg }}; border:1px solid {{ $eventBorder }}; display:flex; align-items:center; justify-content:center; font-size:22px;">
                                        {{ $eventIcon }}
                                    </div>
                                    <span style="font-family:var(--font-mono); font-size:8px; font-weight:900; color:{{ $eventColor }}; text-align:center; letter-spacing:0.03em;">
                                        {{ $eventLabel }}
                                    </span>
                                </div>

                                {{-- Main detail block --}}
                                <div style="display:flex; flex-direction:column; gap:12px;">

                                    {{-- Student identity --}}
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <div style="width:34px; height:34px; border-radius:50%; background:var(--surface3); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-weight:900; font-size:11px; color:var(--text2); flex-shrink:0;">
                                            {{ strtoupper(substr($record->student->user->name ?? 'UN', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:800; font-size:14px; color:var(--text); line-height:1.2;">
                                                {{ $record->student->user->name ?? __('admin_attendance_issues.unknown_student') }}
                                            </div>
                                            <div style="font-family:var(--font-mono); font-size:10px; color:var(--accent); font-weight:700; margin-top:1px;">
                                                {{ $record->student->student_code ?? __('admin_attendance_issues.not_available') }}
                                            </div>
                                        </div>
                                        <span style="font-size:11px; color:var(--muted); font-weight:600;">
                                            · {{ $record->student->major->name ?? ($record->student->group->major->name ?? __('admin_attendance_issues.not_available')) }}
                                        </span>
                                        <span style="font-family:var(--font-mono); font-size:9px; font-weight:700; color:var(--muted); background:var(--surface3); border:1px solid var(--border); padding:2px 8px; border-radius:6px;">
                                            {{ strtoupper($record->student->group->name ?? __('admin_attendance_issues.no_group')) }}
                                        </span>
                                    </div>

                                    {{-- Metadata chips --}}
                                    <div style="display:flex; gap:18px; flex-wrap:wrap; align-items:flex-start;">

                                        @if($authorizerName)
                                            <div style="display:flex; align-items:flex-start; gap:8px;">
                                                <span style="font-size:16px; margin-top:1px;">✍️</span>
                                                <div>
                                                    <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted); text-transform:uppercase; margin-bottom:2px;">{{ __('admin_attendance_issues.authorized_by') }}</div>
                                                    <div style="font-size:12.5px; font-weight:700; color:var(--text);">{{ $authorizerName }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($record->restoredBy)
                                            <div style="display:flex; align-items:flex-start; gap:8px;">
                                                <span style="font-size:16px; margin-top:1px;">👤</span>
                                                <div>
                                                    <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted); text-transform:uppercase; margin-bottom:2px;">{{ __('admin_attendance_issues.system_account') }}</div>
                                                    <div style="font-size:12.5px; font-weight:700; color:var(--text);">{{ $record->restoredBy->name }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        <div style="display:flex; align-items:flex-start; gap:8px;">
                                            <span style="font-size:16px; margin-top:1px;">📅</span>
                                            <div>
                                                <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted); text-transform:uppercase; margin-bottom:2px;">{{ __('admin_attendance_issues.timestamp') }}</div>
                                                <div style="font-size:12.5px; font-weight:700; color:var(--text);">
                                                    {{ \Carbon\Carbon::parse($record->created_at)->format('d M Y, h:i A') }}
                                                </div>
                                                <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted); margin-top:1px;">
                                                    {{ \Carbon\Carbon::parse($record->created_at)->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display:flex; align-items:flex-start; gap:8px;">
                                            <span style="font-size:16px; margin-top:1px;">📖</span>
                                            <div>
                                                <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted); text-transform:uppercase; margin-bottom:2px;">{{ __('admin_attendance_issues.academic_period') }}</div>
                                                <div style="font-size:12.5px; font-weight:700; color:var(--text);">
                                                    {{ $record->academic_year }} · {{ __('admin_attendance_issues.sem_short', ['semester' => $record->semester]) }}
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Reason / Notes box --}}
                                    @if($detailReason)
                                        <div style="background:var(--surface3); border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-top:2px;">
                                            <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--muted); text-transform:uppercase; margin-bottom:6px;">📝 {{ __('admin_attendance_issues.reason_notes') }}</div>
                                            <div style="font-size:12.5px; color:var(--text2); font-weight:500; line-height:1.7;">{{ $detailReason }}</div>
                                        </div>
                                    @endif

                                </div>

                                {{-- Right badge column --}}
                                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px; min-width:80px;">
                                    <span style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted);">
                                        #{{ $restoreHistories->count() - $idx }}
                                    </span>
                                    <span style="font-size:10px; font-weight:800; font-family:var(--font-mono); color:{{ $eventColor }}; background:{{ $eventBg }}; border:1px solid {{ $eventBorder }}; padding:4px 10px; border-radius:8px; white-space:nowrap;">
                                        {{ $eventLabel }}
                                    </span>
                                    @if($isRestore)
                                        <span style="font-family:var(--font-mono); font-size:9px; color:var(--muted); font-weight:700; white-space:nowrap;">
                                            {{ __('admin_attendance_issues.restore_number', ['count' => $studentRestoreCount]) }}
                                        </span>
                                    @endif
                                </div>

                            </div>{{-- /grid --}}
                        </div>{{-- /record card --}}

                    @endforeach
                </div>{{-- /timeline --}}
            @endif

        </div>
    </div>{{-- /history-tab --}}

    <script>
        const attendanceIssueT = @json(trans('admin_attendance_issues'));
        const issueText = (key, fallback = key) => attendanceIssueT[key] || fallback;
        const issueReplace = (key, replacements, fallback = key) => {
            let text = issueText(key, fallback);
            Object.entries(replacements).forEach(([name, value]) => {
                text = text.replace(`:${name}`, value);
            });
            return text;
        };

        function applyFilters() {
            const year = document.getElementById('filterYear').value;
            const semester = document.getElementById('filterSemester').value;
            const url = new URL(window.location.href);
            url.searchParams.set('academic_year', year);
            url.searchParams.set('semester', semester);
            window.location.href = url.toString();
        }

        function switchTab(tabId) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
            
            // Show requested tab pane
            document.getElementById(tabId).style.display = 'block';

            // Remove active styles from all buttons
            document.querySelectorAll('.btn-tab').forEach(btn => {
                btn.style.color = 'var(--muted)';
                btn.style.borderBottomColor = 'transparent';
                btn.classList.remove('active');
            });

            // Add active style to selected button
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--text)';
            
            if (tabId === 'blacklist-tab') {
                activeBtn.style.borderBottomColor = 'var(--red)';
            } else if (tabId === 'atrisk-tab') {
                activeBtn.style.borderBottomColor = 'var(--amber)';
            } else if (tabId === 'history-tab') {
                activeBtn.style.borderBottomColor = '#8B5CF6';
            } else {
                activeBtn.style.borderBottomColor = 'var(--accent)';
            }
        }

        function openActionModal(studentId, studentName, isBlacklist, restoreNumber) {
            const modal = document.getElementById('actionModal');
            const form = document.getElementById('actionForm');
            const title = document.getElementById('actionModalTitle');
            const subtitle = document.getElementById('actionModalSubtitle');
            const authorizerLabel = document.getElementById('authorizerLabel');
            const reasonLabel = document.getElementById('reasonLabel');
            const confirmBtn = document.getElementById('actionConfirmBtn');
            const authorizerInput = document.getElementById('authorizer_name');
            const reasonInput = document.getElementById('action_reason');
            
            // Set form action URL
            form.action = `/admin/attendance-issues/${studentId}/toggle-blacklist`;
            
            // Customize inputs/labels depending on action
            if (isBlacklist) {
                title.textContent = issueReplace('force_blacklist_title', { name: studentName }, 'Force Blacklist: :name');
                subtitle.textContent = issueText('force_blacklist_subtitle', 'Please authorize manual student blacklisting.');
                authorizerLabel.innerHTML = `${issueText('blacklisted_by', 'Blacklisted By / Name')} <span style="color:var(--red);">*</span>`;
                authorizerInput.placeholder = issueText('generic_authorizer_placeholder', 'e.g. Admin, Dean, Registrar');
                reasonLabel.innerHTML = `${issueText('reason_blacklisting', 'Reason for Blacklisting')} <span style="color:var(--red);">*</span>`;
                reasonInput.placeholder = issueText('blacklist_placeholder', 'Explain why this student is being forced into the blacklist...');
                
                // Red theme for blacklist
                confirmBtn.style.background = 'linear-gradient(135deg, var(--red), #F87171)';
                confirmBtn.style.boxShadow = '0 4px 14px rgba(239, 68, 68, 0.25)';
                confirmBtn.textContent = issueText('confirm_blacklist', 'CONFIRM BLACKLIST');
            } else {
                title.textContent = issueReplace('restore_title', { name: studentName }, 'Restore Student: :name');
                subtitle.textContent = issueReplace('restore_subtitle', { count: restoreNumber }, 'Authorizing restoration attempt #:count of 2.');
                authorizerLabel.innerHTML = `${issueText('authorized_by_name', 'Authorized By / Name')} <span style="color:var(--red);">*</span>`;
                authorizerInput.placeholder = issueText('authorizer_placeholder', 'e.g. Dr. John Doe (Dean)');
                reasonLabel.innerHTML = `${issueText('reason_restoration', 'Reason for Restoration')} <span style="color:var(--red);">*</span>`;
                reasonInput.placeholder = issueText('restore_placeholder', 'Describe the justification or reason for restoring this student...');
                
                // Green theme for restore
                confirmBtn.style.background = 'linear-gradient(135deg, var(--green), #22C55E)';
                confirmBtn.style.boxShadow = '0 4px 14px rgba(34, 197, 94, 0.25)';
                confirmBtn.textContent = issueText('confirm_restore', 'CONFIRM RESTORE');
            }
            
            // Clear previous inputs
            authorizerInput.value = '';
            reasonInput.value = '';
            
            // Open modal
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.firstElementChild.style.transform = 'scale(1)';
            }, 10);
        }

        function closeActionModal() {
            const modal = document.getElementById('actionModal');
            modal.style.opacity = '0';
            modal.firstElementChild.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }

        // ─── EVENT REGISTRY: Selection & Deletion ───────────────────────

        function onCheckboxChange() {
            const checkboxes = document.querySelectorAll('.history-checkbox');
            const checked    = document.querySelectorAll('.history-checkbox:checked');
            const bulkBtn    = document.getElementById('bulkDropBtn');
            const countSpan  = document.getElementById('selectedCount');
            const selectAll  = document.getElementById('selectAllHistory');

            // Update counter
            countSpan.textContent = checked.length;

            // Enable / disable bulk-drop button
            if (checked.length > 0) {
                bulkBtn.disabled = false;
                bulkBtn.style.opacity = '1';
                bulkBtn.style.cursor  = 'pointer';
            } else {
                bulkBtn.disabled = true;
                bulkBtn.style.opacity = '0.4';
                bulkBtn.style.cursor  = 'not-allowed';
            }

            // Sync select-all state
            selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
            selectAll.checked       = checked.length === checkboxes.length && checkboxes.length > 0;

            // Highlight selected cards
            document.querySelectorAll('.history-card').forEach(card => {
                const cb = card.querySelector('.history-checkbox');
                if (cb && cb.checked) {
                    card.style.outline      = '2px solid #8B5CF6';
                    card.style.outlineOffset = '0px';
                } else {
                    card.style.outline = 'none';
                }
            });
        }

        function toggleSelectAll(masterCb) {
            document.querySelectorAll('.history-checkbox').forEach(cb => {
                cb.checked = masterCb.checked;
            });
            onCheckboxChange();
        }

        function submitBulkDrop() {
            const checked = document.querySelectorAll('.history-checkbox:checked');
            if (checked.length === 0) return;

            openConfirmModal(
                `🗑️ ${issueText('drop_selected_title', 'Drop Selected Records')}`,
                issueReplace('drop_selected_body', { count: checked.length }, 'You are about to permanently delete <strong>:count</strong> selected event record(s). This action cannot be undone.'),
                'rgba(139,92,246,0.15)',
                'rgba(139,92,246,0.3)',
                '#8B5CF6',
                issueText('drop_selected', 'DROP SELECTED'),
                () => {
                    const container = document.getElementById('bulkDropIds');
                    container.innerHTML = '';
                    checked.forEach(cb => {
                        const inp = document.createElement('input');
                        inp.type  = 'hidden';
                        inp.name  = 'ids[]';
                        inp.value = cb.value;
                        container.appendChild(inp);
                    });
                    document.getElementById('bulkDropForm').submit();
                }
            );
        }

        function confirmDropAll() {
            const total = document.querySelectorAll('.history-checkbox').length;
            openConfirmModal(
                `🚨 ${issueText('drop_all_title', 'Drop ALL Records')}`,
                issueReplace('drop_all_body', { count: total }, 'You are about to permanently delete <strong>all :count</strong> event record(s) for this semester. This action cannot be undone.'),
                'rgba(239,68,68,0.12)',
                'rgba(239,68,68,0.35)',
                'var(--red)',
                issueText('drop_all', 'DROP ALL'),
                () => document.getElementById('dropAllForm').submit()
            );
        }

        // Generic confirmation mini-modal
        function openConfirmModal(title, bodyHtml, bgColor, borderColor, accentColor, confirmLabel, onConfirm) {
            // Remove existing if any
            const existing = document.getElementById('confirmDeleteModal');
            if (existing) existing.remove();

            const overlay = document.createElement('div');
            overlay.id = 'confirmDeleteModal';
            overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(10,10,12,0.75);backdrop-filter:blur(6px);display:flex;justify-content:center;align-items:center;opacity:0;transition:opacity 0.2s;';

            overlay.innerHTML = `
                <div style="background:var(--surface);border:1px solid ${borderColor};border-radius:20px;width:100%;max-width:440px;overflow:hidden;transform:scale(0.95);transition:transform 0.2s;box-shadow:0 20px 50px rgba(0,0,0,0.6);">
                    <div style="padding:24px 28px;border-bottom:1px solid var(--border);background:${bgColor};">
                        <h3 style="font-family:var(--font-display);font-size:17px;font-weight:900;color:var(--text);margin:0;">${title}</h3>
                    </div>
                    <div style="padding:22px 28px;">
                        <p style="font-size:13px;color:var(--text2);line-height:1.7;margin:0;">${bodyHtml}</p>
                    </div>
                    <div style="padding:18px 28px;background:rgba(0,0,0,0.12);border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;">
                        <button onclick="document.getElementById('confirmDeleteModal').remove()"
                            style="background:var(--surface3);border:1px solid var(--border);color:var(--text2);padding:9px 20px;border-radius:10px;font-family:var(--font-mono);font-size:10px;font-weight:800;cursor:pointer;">
                            ${issueText('cancel', 'CANCEL')}
                        </button>
                        <button id="confirmDeleteOk"
                            style="background:${accentColor};border:none;color:#fff;padding:9px 20px;border-radius:10px;font-family:var(--font-mono);font-size:10px;font-weight:800;cursor:pointer;letter-spacing:0.04em;">
                            ${confirmLabel}
                        </button>
                    </div>
                </div>`;

            document.body.appendChild(overlay);
            setTimeout(() => {
                overlay.style.opacity = '1';
                overlay.firstElementChild.style.transform = 'scale(1)';
            }, 10);

            document.getElementById('confirmDeleteOk').onclick = () => {
                onConfirm();
            };
        }

        // ─── UNAUTHORIZED MODAL ───────────────────────────────────────────
        function openUnauthorizedModal() {
            const modal = document.getElementById('unauthorizedModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.firstElementChild.style.transform = 'scale(1)';
            }, 10);
        }

        function closeUnauthorizedModal() {
            const modal = document.getElementById('unauthorizedModal');
            modal.style.opacity = '0';
            modal.firstElementChild.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
    </script>

    <!-- Beautiful Custom Glassmorphic Action Modal -->
    <div id="actionModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(10, 10, 12, 0.7); backdrop-filter:blur(8px); justify-content:center; align-items:center; opacity:0; transition:opacity 0.25s ease-in-out;">
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:24px; width:100%; max-width:480px; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.6); transform:scale(0.95); transition:transform 0.25s ease-in-out; overflow:hidden;">
            <!-- Modal Header -->
            <div style="padding:25px 30px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.02);">
                <div>
                    <h3 style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--text); margin:0;" id="actionModalTitle">
                        {{ __('admin_attendance_issues.authorize_action') }}
                    </h3>
                    <p style="font-size:11px; color:var(--muted); margin:4px 0 0 0;" id="actionModalSubtitle">
                        {{ __('admin_attendance_issues.authorization_subtitle') }}
                    </p>
                </div>
                <button type="button" onclick="closeActionModal()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px; border-radius:50%; display:flex; align-items:center; justify-content:center; hover:color:var(--text); transition:color 0.2s;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Modal Form -->
            <form id="actionForm" method="POST" action="" style="margin:0;">
                @csrf
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                
                <div style="padding:30px; display:flex; flex-direction:column; gap:20px;">
                    <!-- Input: Name -->
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label for="authorizer_name" id="authorizerLabel" style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); letter-spacing:0.05em; text-transform:uppercase;">
                            {{ __('admin_attendance_issues.authorized_by_name') }} <span style="color:var(--red);">*</span>
                        </label>
                        <input type="text" id="authorizer_name" name="authorizer_name" required placeholder="{{ __('admin_attendance_issues.authorizer_placeholder') }}" 
                            style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px 16px; color:var(--text); font-size:13px; font-weight:600; outline:none; transition:border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    
                    <!-- Input: Reason -->
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label for="action_reason" id="reasonLabel" style="font-family:var(--font-mono); font-size:10px; font-weight:800; color:var(--muted); letter-spacing:0.05em; text-transform:uppercase;">
                            {{ __('admin_attendance_issues.reason_description') }} <span style="color:var(--red);">*</span>
                        </label>
                        <textarea id="action_reason" name="reason" required placeholder="{{ __('admin_attendance_issues.reason_placeholder') }}" rows="4"
                            style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px 16px; color:var(--text); font-size:13px; font-weight:600; outline:none; transition:border-color 0.2s; resize:none; line-height:1.5;"
                            onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'"></textarea>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div style="padding:20px 30px; background:rgba(0,0,0,0.15); border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:12px;">
                    <button type="button" onclick="closeActionModal()" class="btn-primary" style="background:var(--surface3); border:1px solid var(--border); color:var(--text2); padding:10px 20px; font-size:11px; cursor:pointer;">
                        {{ __('admin_attendance_issues.cancel') }}
                    </button>
                    <button type="submit" id="actionConfirmBtn" class="btn-primary" style="border:none; padding:10px 20px; font-size:11px; cursor:pointer; font-weight:800; font-family:var(--font-mono);">
                        {{ __('admin_attendance_issues.confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- ──────── UNAUTHORIZED MODAL ──────── --}}
    <div id="unauthorizedModal"
         style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(10,10,12,0.78); backdrop-filter:blur(10px); justify-content:center; align-items:center; opacity:0; transition:opacity 0.25s ease-in-out;"
         onclick="if(event.target===this) closeUnauthorizedModal()">
        <div style="background:var(--surface); border:1px solid rgba(239,68,68,0.35); border-radius:24px; width:100%; max-width:460px; box-shadow:0 25px 60px -10px rgba(0,0,0,0.7); transform:scale(0.95); transition:transform 0.25s ease-in-out; overflow:hidden;">

            {{-- Header --}}
            <div style="padding:28px 30px 22px; background:rgba(239,68,68,0.06); border-bottom:1px solid rgba(239,68,68,0.2); display:flex; align-items:flex-start; gap:16px;">
                <div style="width:46px; height:46px; border-radius:14px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2.2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--red); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:4px;">{{ __('admin_attendance_issues.access_denied') }}</div>
                    <h3 style="font-family:var(--font-display); font-size:19px; font-weight:900; color:var(--text); margin:0 0 4px;">{{ __('admin_attendance_issues.unauthorized_action') }}</h3>
                    <p style="font-size:12px; color:var(--muted); margin:0;">{{ __('admin_attendance_issues.unauthorized_desc') }}</p>
                </div>
                <button type="button" onclick="closeUnauthorizedModal()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px; border-radius:50%; display:flex; align-items:center; flex-shrink:0;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding:26px 30px;">
                <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.18); border-radius:14px; padding:18px 20px; margin-bottom:18px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <span style="font-size:18px;">🚫</span>
                        <span style="font-family:var(--font-mono); font-size:11px; font-weight:800; color:var(--red);">{{ __('admin_attendance_issues.deletion_restricted') }}</span>
                    </div>
                    <p style="font-size:13px; color:var(--text2); line-height:1.75; margin:0;">
                        {{ __('admin_attendance_issues.restricted_body') }}
                    </p>
                </div>
                <div style="display:flex; align-items:center; gap:10px; background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:14px 16px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--muted)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p style="font-size:12px; color:var(--muted); margin:0; line-height:1.6;">
                        {{ __('admin_attendance_issues.contact_super_admin') }}
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:18px 30px; background:rgba(0,0,0,0.12); border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
                <button type="button" onclick="closeUnauthorizedModal()"
                    style="background:linear-gradient(135deg, rgba(239,68,68,0.15), rgba(239,68,68,0.08)); border:1px solid rgba(239,68,68,0.35); color:var(--red); padding:10px 28px; border-radius:12px; font-family:var(--font-mono); font-size:10px; font-weight:800; cursor:pointer; letter-spacing:0.06em; transition:all 0.2s;"
                    onmouseenter="this.style.background='rgba(239,68,68,0.22)'" onmouseleave="this.style.background='linear-gradient(135deg, rgba(239,68,68,0.15), rgba(239,68,68,0.08))'">
                    {{ __('admin_attendance_issues.understood') }}
                </button>
            </div>
        </div>
    </div>

@endsection
