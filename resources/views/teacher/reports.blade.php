@extends('layouts.app')

@section('content')

{{-- ══ Toast ══ --}}
<div id="toast" class="toast"><div id="toastIcon" class="toast-icon">✓</div><span id="toastMsg">Message</span></div>

{{-- ══ Live Alert Notification Feed ══ --}}
<div id="liveAlertFeed" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;max-width:320px"></div>

{{-- ══ Manual Checkin Modal ══ --}}
<div id="checkinModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-head">
            <span class="modal-title">Manual Override</span>
            <button onclick="closeModal('checkinModal')" class="modal-close">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:24px">
            <div id="checkinStudentInfo" style="display:flex;align-items:center;gap:14px;padding:16px;background:var(--surface3);border-radius:10px;margin-bottom:20px;border:1px solid var(--border)">
                <div id="checkinAvatar" style="width:44px;height:44px;border-radius:50%;background:color-mix(in srgb, var(--accent) 13%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0">?</div>
                <div>
                    <div id="checkinName" style="font-weight:700;font-size:14px;color:var(--text)">Student Name</div>
                    <div id="checkinCode" style="font-family:var(--font-mono);font-size:10px;color:var(--muted);margin-top:2px">CODE</div>
                </div>
            </div>
            <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.1em;margin-bottom:12px">SET ATTENDANCE STATUS</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                <button onclick="setCheckin('present')" class="status-choice-btn" id="btnPresent" style="background:color-mix(in srgb, var(--green) 9%, transparent);border:2px solid color-mix(in srgb, var(--green) 27%, transparent);color:var(--green);border-radius:10px;padding:14px 8px;font-family:var(--font-mono);font-size:10px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.05em">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:block;margin:0 auto 6px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    PRESENT
                </button>
                <button onclick="setCheckin('late')" class="status-choice-btn" id="btnLate" style="background:color-mix(in srgb, var(--amber) 9%, transparent);border:2px solid color-mix(in srgb, var(--amber) 27%, transparent);color:var(--amber);border-radius:10px;padding:14px 8px;font-family:var(--font-mono);font-size:10px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.05em">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:block;margin:0 auto 6px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    LATE
                </button>
                <button onclick="setCheckin('absent')" class="status-choice-btn" id="btnAbsent" style="background:color-mix(in srgb, var(--red) 9%, transparent);border:2px solid color-mix(in srgb, var(--red) 27%, transparent);color:var(--red);border-radius:10px;padding:14px 8px;font-family:var(--font-mono);font-size:10px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.05em">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:block;margin:0 auto 6px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    ABSENT
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('checkinModal')" class="btn-secondary">CANCEL</button>
            <button id="confirmCheckinBtn" onclick="confirmCheckin()" class="btn-primary">CONFIRM</button>
        </div>
    </div>
</div>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="breadcrumb"><span>FACULTY</span><span class="breadcrumb-sep">/</span><span class="breadcrumb-current">ATTENDANCE TRACKER</span></div>
        <h1 class="page-title">Attendance Intelligence</h1>
        <p class="page-subtitle">LIVE MONITORING · REPORTS · ANALYTICS</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <div id="liveSessionBadge" style="display:none;align-items:center;gap:8px;background:color-mix(in srgb, var(--green) 8%, transparent);border:1px solid color-mix(in srgb, var(--green) 25%, transparent);border-radius:99px;padding:8px 14px">
            <div style="width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 1.5s infinite"></div>
            <span style="font-family:var(--font-mono);font-size:10px;color:var(--green);font-weight:700;letter-spacing:.08em">LIVE SESSION</span>
        </div>
        <button onclick="toggleView('monitor')" id="btnMonitor" class="btn-secondary" style="gap:6px;padding:8px 14px">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868V15.13a1 1 0 01-1.447.899L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            LIVE MONITOR
        </button>
        <button onclick="toggleView('reports')" id="btnReports" class="btn-secondary" style="gap:6px;padding:8px 14px">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            HISTORY
        </button>
    </div>
</div>

{{-- SUMMARY STATS --}}
<div class="stats-grid" id="teacherStats">
    <div class="stat-card blue">
        <div class="stat-glow"></div>
        <div class="stat-icon-wrap"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
        <div class="stat-label">MY COURSES</div>
        <div class="stat-value">{{ $classes->count() }}</div>
        <span class="stat-pill pill-up">Assigned</span>
    </div>
    <div class="stat-card green">
        <div class="stat-glow"></div>
        <div class="stat-icon-wrap"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <div class="stat-label">TOTAL STUDENTS</div>
        <div class="stat-value">{{ $totalStudents }}</div>
        <span class="stat-pill pill-up">↑ Enrolled</span>
    </div>
    <div class="stat-card amber">
        <div class="stat-glow"></div>
        <div class="stat-icon-wrap"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
        <div class="stat-label">TOTAL SESSIONS</div>
        <div class="stat-value">{{ $totalSessions }}</div>
        <span class="stat-pill pill-amber">Scheduled</span>
    </div>
    <div class="stat-card red">
        <div class="stat-glow"></div>
        <div class="stat-icon-wrap"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
        <div class="stat-label">ATTENDANCE RATE</div>
        <div class="stat-value">{{ $overallRate }}<span style="font-size:14px;opacity:.5">%</span></div>
        <span class="stat-pill {{ $overallRate >= 75 ? 'pill-up' : 'pill-down' }}">{{ $overallRate >= 75 ? 'Good' : 'Low' }}</span>
    </div>
</div>

{{-- ══════════════════════════════════════
     LIVE MONITOR VIEW
══════════════════════════════════════ --}}
<div id="viewMonitor">
    <div class="main-grid" style="grid-template-columns: 300px 1fr; gap: 20px;">

        {{-- SESSION LIST --}}
        <div class="panel">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border)">
                <div style="font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.1em;color:var(--text2)">SESSION LIST</div>
                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:3px">SELECT TO MONITOR</div>
            </div>
            <div style="padding:10px;max-height:600px;overflow-y:auto" id="sessionList">
                @forelse($allSessions as $sess)
                @php
                    $isActive = $sess->status === 'active';
                    $isDone = $sess->status === 'completed';
                    $dot = $isActive ? 'var(--green)' : ($isDone ? 'var(--muted)' : 'var(--amber)');
                    $label = $isActive ? 'LIVE' : ($isDone ? 'DONE' : 'SOON');
                @endphp
                <button onclick="loadSession({{ $sess->id }})" 
                    data-sid="{{ $sess->id }}"
                    class="session-btn {{ $sess->id == ($selectedSession?->id) ? 'active' : '' }}"
                    style="width:100%;text-align:left;background:{{ $sess->id == ($selectedSession?->id) ? 'color-mix(in srgb, var(--accent) 6%, transparent)' : 'transparent' }};border:1px solid {{ $sess->id == ($selectedSession?->id) ? 'color-mix(in srgb, var(--accent) 25%, transparent)' : 'var(--border)' }};border-radius:10px;padding:12px 14px;margin-bottom:8px;cursor:pointer;transition:all .2s">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted)">#{{ str_pad($sess->id,4,'0',STR_PAD_LEFT) }}</div>
                        <div style="display:flex;align-items:center;gap:5px">
                            <div style="width:6px;height:6px;border-radius:50%;background:{{ $dot }};{{ $isActive ? 'animation:blink 1.5s infinite' : '' }}"></div>
                            <span style="font-family:var(--font-mono);font-size:9px;color:{{ $dot }};font-weight:700">{{ $label }}</span>
                        </div>
                    </div>
                    <div style="font-weight:700;font-size:12px;color:var(--text);margin-bottom:3px">{{ $sess->classRoom->subject->name ?? 'Unknown' }}</div>
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted2)">{{ \Carbon\Carbon::parse($sess->start_time)->format('D, M d') }} · {{ \Carbon\Carbon::parse($sess->start_time)->format('H:i') }}</div>
                </button>
                @empty
                <div style="padding:30px;text-align:center">
                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted)">NO SESSIONS FOUND</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:6px">Generate academic calendar first</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- LIVE MONITOR PANEL --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            @if($selectedSession)
            <script>const selectedSessionId = {{ $selectedSession->id }};</script>
            @php
                $liveSession = $selectedSession;
                $isLive = $liveSession->status === 'active';
                $liveStudents = $liveSession->classRoom?->all_students ?? collect();
                $totalInClass = $liveStudents->count();
                $presentCount = $liveSession->attendanceRecords->whereIn('status', ['present','late'])->count();
                
                $sessionDate = \Carbon\Carbon::parse($liveSession->start_time)->toDateString();
                $excusedCount = \App\Models\StudentPermission::where('start_date', '<=', $sessionDate)
                    ->where('end_date', '>=', $sessionDate)
                    ->whereIn('student_id', $liveStudents->pluck('id'))
                    ->count();
                $absentCount = max(0, $totalInClass - $presentCount - $excusedCount);
                $rate = $totalInClass > 0 ? round(($presentCount / $totalInClass) * 100) : 0;
            @endphp

            {{-- LIVE HEADER CARD --}}
            <div class="panel" style="background: linear-gradient(135deg, var(--surface2) 0%, var(--surface3) 100%); border:1px solid var(--border2); overflow:hidden; position:relative;">
                <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:{{ $isLive ? 'var(--green)' : 'var(--muted)' }}08;pointer-events:none"></div>
                <div style="padding:22px 24px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px">
                        <div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                @if($isLive)
                                <div style="display:flex;align-items:center;gap:7px;background:color-mix(in srgb, var(--green) 9%, transparent);border:1px solid color-mix(in srgb, var(--green) 25%, transparent);border-radius:99px;padding:5px 12px">
                                    <div style="width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 1.5s infinite"></div>
                                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--green);font-weight:700">LIVE</span>
                                </div>
                                @else
                                <span style="font-family:var(--font-mono);font-size:9px;font-weight:700;padding:5px 12px;border-radius:99px;background:var(--surface3);border:1px solid var(--border);color:var(--muted2)">{{ strtoupper($liveSession->status) }}</span>
                                @endif
                                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em">SESSION #{{ str_pad($liveSession->id,4,'0',STR_PAD_LEFT) }}</span>
                            </div>
                            <div style="font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--text);margin-bottom:5px">
                                {{ $liveSession->classRoom->subject->name ?? 'Unknown Subject' }}
                            </div>
                            <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted2)">
                                {{ \Carbon\Carbon::parse($liveSession->start_time)->format('l, M d Y') }} · {{ \Carbon\Carbon::parse($liveSession->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($liveSession->end_time)->format('H:i') }}
                            </div>
                        </div>
                        {{-- Status controls --}}
                        <div style="display:flex;gap:8px;flex-shrink:0">
                            @if(!$isLive)
                            <button onclick="updateSessionStatus({{ $liveSession->id }}, 'active')" class="btn-primary" style="font-size:10px;padding:8px 14px;gap:6px;background:var(--green);border-color:var(--green)">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                ACTIVATE
                            </button>
                            @else
                            <button onclick="updateSessionStatus({{ $liveSession->id }}, 'completed')" class="btn-secondary" style="font-size:10px;padding:8px 14px;gap:6px;border-color:color-mix(in srgb, var(--amber) 27%, transparent);color:var(--amber)">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                                END SESSION
                            </button>
                            @endif
                            <button onclick="refreshMonitor()" class="btn-secondary" style="width:36px;height:36px;padding:0;display:flex;align-items:center;justify-content:center" title="Refresh">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="refreshIcon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-top:22px">
                        <div style="background:color-mix(in srgb, var(--surface) 31%, transparent);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:10px;padding:14px">
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em;margin-bottom:4px">PRESENT</div>
                            <div id="quickPresent" style="font-weight:800;font-size:22px;color:var(--green)">{{ $presentCount }}</div>
                        </div>
                        <div style="background:color-mix(in srgb, var(--surface) 31%, transparent);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:10px;padding:14px">
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em;margin-bottom:4px">EXCUSED</div>
                            <div id="quickExcused" style="font-weight:800;font-size:22px;color:var(--accent)">{{ $excusedCount }}</div>
                        </div>
                        <div style="background:color-mix(in srgb, var(--surface) 31%, transparent);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:10px;padding:14px">
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em;margin-bottom:4px">ABSENT</div>
                            <div id="quickAbsent" style="font-weight:800;font-size:22px;color:var(--red)">{{ $absentCount }}</div>
                        </div>
                        <div style="background:color-mix(in srgb, var(--surface) 31%, transparent);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:10px;padding:14px">
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em;margin-bottom:4px">TOTAL</div>
                            <div style="font-weight:800;font-size:22px;color:var(--text)">{{ $totalInClass }}</div>
                        </div>
                        <div style="background:color-mix(in srgb, var(--surface) 31%, transparent);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:10px;padding:14px">
                            <div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);letter-spacing:.1em;margin-bottom:4px">RATE</div>
                            <div id="quickRate" style="font-weight:800;font-size:22px;color:{{ $rate >= 75 ? 'var(--green)' : 'var(--amber)' }}">{{ $rate }}%</div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div style="margin-top:16px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                            <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted)">ATTENDANCE PROGRESS</span>
                            <span id="progressLabel" style="font-family:var(--font-mono);font-size:9px;color:var(--accent)">{{ $presentCount }}/{{ $totalInClass }}</span>
                        </div>
                        <div style="height:6px;background:var(--surface3);border-radius:99px;overflow:hidden">
                            <div id="progressFill" style="height:100%;width:{{ $rate }}%;background:linear-gradient(90deg,var(--accent),var(--green));border-radius:99px;transition:width .6s ease"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STUDENT ROSTER --}}
            <div class="panel">
                <div class="catalog-toolbar" style="padding:15px 20px">
                    <div style="display:flex;align-items:center;gap:10px;flex:1">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $isLive ? 'var(--green)' : 'var(--muted)' }};{{ $isLive ? 'animation:blink 1.5s infinite' : '' }}"></div>
                        <span style="font-family:var(--font-mono);font-size:10px;font-weight:700;color:var(--text2)">STUDENT ROSTER</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="height:34px;background:var(--surface3);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;padding:0 14px">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                            <input id="rosterSearch" type="text" placeholder="Search student..." oninput="filterRoster()" style="background:transparent;border:none;color:var(--text);font-size:11px;padding-left:8px;outline:none;width:170px">
                        </div>
                        <select id="rosterFilter" onchange="filterRoster()" style="height:34px;background:var(--surface3);border:1px solid var(--border);border-radius:10px;color:var(--text2);font-family:var(--font-mono);font-size:9px;padding:0 30px 0 12px;cursor:pointer">
                            <option value="">ALL STATUS</option>
                            <option value="present">PRESENT</option>
                            <option value="late">LATE</option>
                            <option value="excused">EXCUSED</option>
                            <option value="absent">ABSENT</option>
                        </select>
                    </div>
                </div>

                <table class="att-table" id="rosterTable">
                    <thead>
                        <tr>
                            <th style="padding-left:24px">STUDENT</th>
                            <th>CODE</th>
                            <th>CHECK-IN TIME</th>
                            <th>METHOD</th>
                            <th>STATUS</th>
                            <th style="text-align:right;padding-right:24px">OVERRIDE</th>
                        </tr>
                    </thead>
                    <tbody id="rosterBody">
                    @php
                        $sessionAttendanceMap = $liveSession->attendanceRecords->keyBy('student_id');
                        $sessionDate = \Carbon\Carbon::parse($liveSession->start_time)->toDateString();
                        $sessionPermissions = \App\Models\StudentPermission::where('start_date', '<=', $sessionDate)
                            ->where('end_date', '>=', $sessionDate)
                            ->whereIn('student_id', $liveStudents->pluck('id'))
                            ->get()
                            ->keyBy('student_id');
                    @endphp
                    @foreach($liveStudents as $student)
                    @php
                        $att = $sessionAttendanceMap->get($student->id);
                        $perm = $sessionPermissions->get($student->id);
                        $status = $att ? $att->status : ($perm ? 'excused' : 'absent');
                        $statusColor = match($status) {
                            'present' => 'var(--green)',
                            'late' => 'var(--amber)',
                            'excused' => 'var(--accent)',
                            default => 'var(--red)',
                        };
                        $initials = collect(explode(' ', $student->user->name ?? ''))->map(fn($n) => substr($n,0,1))->join('');
                    @endphp
                    <tr data-student-id="{{ $student->id }}" data-status="{{ $status }}" data-name="{{ strtolower($student->user->name ?? '') }}">
                        <td style="padding-left:24px">
                            <div style="display:flex;align-items:center;gap:12px">
                                <div style="width:34px;height:34px;border-radius:50%;background:{{ $statusColor }}18;border:2px solid {{ $statusColor }}44;color:{{ $statusColor }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex-shrink:0">
                                    {{ strtoupper(substr($initials, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:13px;color:var(--text)">{{ $student->user->name ?? 'Unknown' }}</div>
                                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:1px">{{ $student->major ?? 'N/A' }}</div>
                                    @if($perm)
                                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);margin-top:2.5px;display:flex;align-items:center;gap:4px">
                                            <span>📋</span>
                                            <span>Permission: {{ $perm->reason }} ({{ strtoupper($perm->type) }})</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span style="font-family:var(--font-mono);font-size:10px;color:var(--text2)">{{ $student->student_code }}</span></td>
                        <td>
                            <span style="font-family:var(--font-mono);font-size:11px;color:{{ $att ? 'var(--text2)' : 'var(--muted)' }}">
                                {{ $att && $att->scan_time ? \Carbon\Carbon::parse($att->scan_time)->format('H:i') : '—' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-family:var(--font-mono);font-size:9px;text-transform:uppercase;color:var(--muted2)">
                                {{ $att ? ($att->method === 'qr' ? '📱 QR' : '✏️ Manual') : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="status-tag" style="background:{{ $statusColor }}15;color:{{ $statusColor }};border:1px solid {{ $statusColor }}30">
                                {{ strtoupper($status) }}
                            </span>
                        </td>
                        <td style="text-align:right;padding-right:24px">
                            <button onclick="openCheckin({{ $student->id }}, '{{ addslashes($student->user->name ?? '') }}', '{{ $student->student_code }}', {{ $liveSession->id }})"
                                class="action-btn btn-edit" title="Override attendance"
                                style="background:color-mix(in srgb, var(--accent) 9%, transparent);border-color:color-mix(in srgb, var(--accent) 27%, transparent)">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @else
            {{-- NO SESSION SELECTED --}}
            <div class="panel" style="padding:70px;text-align:center">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--surface3);display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="var(--muted2)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.868V15.13a1 1 0 01-1.447.899L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div style="font-family:var(--font-display);font-weight:700;font-size:16px;color:var(--text);margin-bottom:8px">Select a Session</div>
                <p style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.05em">PICK A SESSION FROM THE LEFT PANEL TO BEGIN MONITORING</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════
     HISTORY / REPORTS VIEW
══════════════════════════════════════ --}}
<div id="viewReports" style="display:none">
    <div class="main-grid" style="grid-template-columns: 280px 1fr; gap: 20px;">

        {{-- CLASS DIRECTORY --}}
        <div class="panel">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border)">
                <div style="font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.1em;color:var(--text2)">MY COURSES</div>
            </div>
            <div style="padding:10px">
                @foreach($classes as $class)
                @php
                    $classRate = $class->sessions->count() > 0
                        ? round(($class->sessions->sum('attendance_records_count') / ($class->sessions->count() * max(1, $class->students->count()))) * 100)
                        : 0;
                @endphp
                <a href="{{ route('teacher.reports') }}?class_id={{ $class->id }}" 
                    class="class-row {{ ($selectedClass && $selectedClass->id == $class->id) ? 'active' : '' }}"
                    style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;margin-bottom:8px;text-decoration:none;border:1px solid {{ ($selectedClass && $selectedClass->id == $class->id) ? 'color-mix(in srgb, var(--accent) 25%, transparent)' : 'var(--border)' }};background:{{ ($selectedClass && $selectedClass->id == $class->id) ? 'color-mix(in srgb, var(--accent) 6%, transparent)' : 'transparent' }};transition:all .2s;">
                    <div style="width:36px;height:36px;border-radius:8px;background:color-mix(in srgb, var(--accent) 9%, transparent);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0">
                        {{ strtoupper(substr($class->subject->name ?? 'X', 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;font-size:12px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $class->subject->name ?? 'Untitled' }}</div>
                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px">{{ $class->sessions->count() }} sessions</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-weight:700;font-size:12px;color:{{ $classRate >= 75 ? 'var(--green)' : 'var(--amber)' }}">{{ $classRate }}%</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- SESSION HISTORY TABLE --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            @if($selectedClass)
            <div class="panel">
                <div class="catalog-toolbar" style="padding:15px 20px">
                    <div style="flex:1;display:flex;align-items:center;gap:10px">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--accent)"></div>
                        <span style="font-family:var(--font-mono);font-size:10px;font-weight:700;color:var(--text2)">SESSION ARCHIVES · {{ strtoupper($selectedClass->subject->name ?? '') }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="height:34px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;padding:0 14px">
                            <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted2)">
                                <span style="color:var(--accent);font-weight:700">{{ $selectedClass->sessions->count() }}</span> RECORDS
                            </span>
                        </div>
                    </div>
                </div>

                <div style="overflow-x:auto">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th style="padding-left:24px">SESSION</th>
                                <th>DATE & TIME</th>
                                <th>ATTENDANCE</th>
                                <th>RATE</th>
                                <th>STATUS</th>
                                <th style="text-align:right;padding-right:24px">DETAILS</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($selectedClass->sessions as $sess)
                        @php
                            $isDone = now() > $sess->end_time;
                            $isLiveNow = $sess->status === 'active';
                            $scans = $sess->attendance_records_count ?? 0;
                            $classSize = $selectedClass->students->count();
                            $sessRate = $classSize > 0 ? round(($scans/$classSize)*100) : 0;
                            $statusCol = $isLiveNow ? 'var(--green)' : ($isDone ? 'var(--muted2)' : 'var(--amber)');
                            $statusLabel = $isLiveNow ? 'LIVE' : ($isDone ? 'COMPLETED' : 'UPCOMING');
                        @endphp
                        <tr class="fade-up">
                            <td style="padding-left:24px">
                                <div style="font-family:var(--font-mono);font-weight:700;font-size:11px;color:var(--text)">#{{ str_pad($sess->id,4,'0',STR_PAD_LEFT) }}</div>
                                @if($sess->semester)<div style="font-family:var(--font-mono);font-size:8px;color:var(--muted);margin-top:2px">SEM {{ $sess->semester }}</div>@endif
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:700;color:var(--text2)">{{ \Carbon\Carbon::parse($sess->start_time)->format('M d, Y') }}</div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);margin-top:2px">{{ \Carbon\Carbon::parse($sess->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($sess->end_time)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="font-weight:800;font-size:16px;color:var(--accent)">{{ $scans }}</div>
                                    <span style="font-family:var(--font-mono);font-size:8px;color:var(--muted)">/ {{ $classSize }}</span>
                                </div>
                                <div style="margin-top:4px;width:60px;height:3px;background:var(--border2);border-radius:99px;overflow:hidden">
                                    <div style="height:100%;width:{{ $sessRate }}%;background:{{ $sessRate >= 75 ? 'var(--green)' : 'var(--amber)' }};border-radius:99px"></div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:700;font-size:14px;color:{{ $sessRate >= 75 ? 'var(--green)' : 'var(--amber)' }}">{{ $sessRate }}%</span>
                            </td>
                            <td>
                                <span class="status-tag" style="background:{{ $statusCol }}15;color:{{ $statusCol }};border:1px solid {{ $statusCol }}30">{{ $statusLabel }}</span>
                            </td>
                            <td style="text-align:right;padding-right:24px">
                                <a href="{{ route('teacher.reports') }}?class_id={{ $selectedClass->id }}&session_id={{ $sess->id }}" 
                                    class="action-btn btn-view" title="View Details">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-title">No sessions yet</div><div class="empty-desc">Generate academic calendar to create sessions</div></div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SESSION DRILL-DOWN --}}
            @if($selectedSession)
            @php
                $drillSession = $selectedSession;
                $drillStudents = $drillSession->classRoom?->all_students ?? collect();
                $drillTotal = $drillStudents->count();
                $drillPresent = $drillSession->attendanceRecords->whereIn('status',['present','late'])->count();
                $drillDate = \Carbon\Carbon::parse($drillSession->start_time)->toDateString();
                $drillExcused = \App\Models\StudentPermission::where('start_date', '<=', $drillDate)
                    ->where('end_date', '>=', $drillDate)
                    ->whereIn('student_id', $drillStudents->pluck('id'))
                    ->count();
                $drillAbsent = max(0, $drillTotal - $drillPresent - $drillExcused);
                $drillRate = $drillTotal > 0 ? round(($drillPresent/$drillTotal)*100) : 0;
                $drillAttMap = $drillSession->attendanceRecords->keyBy('student_id');
            @endphp
            <div class="panel fade-up">
                <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--surface2)">
                    <div>
                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);letter-spacing:.1em;margin-bottom:4px">SESSION DRILL-DOWN · #{{ str_pad($drillSession->id,4,'0',STR_PAD_LEFT) }}</div>
                        <div style="font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--text)">{{ $drillSession->classRoom->subject->name ?? 'Unknown' }}</div>
                        <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted);margin-top:3px">{{ \Carbon\Carbon::parse($drillSession->start_time)->format('l, M d Y · H:i') }} – {{ \Carbon\Carbon::parse($drillSession->end_time)->format('H:i') }}</div>
                    </div>
                </div>

                {{-- Metrics --}}
                <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0;border-bottom:1px solid var(--border)">
                    @foreach([
                        ['PRESENT RATE', $drillRate.'%', 'var(--green)'], 
                        ['PRESENT', $drillPresent, 'var(--green)'], 
                        ['EXCUSED', $drillExcused, 'var(--accent)'],
                        ['ABSENT', $drillAbsent, 'var(--red)'], 
                        ['TOTAL', $drillTotal, 'var(--muted2)']
                    ] as [$label, $val, $col])
                    <div style="padding:20px 24px;border-right:1px solid var(--border);last-child:border-right:none">
                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.1em;margin-bottom:6px">{{ $label }}</div>
                        <div style="font-weight:800;font-size:24px;color:{{ $col }}">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Student detail table --}}
                <table class="att-table">
                    <thead><tr>
                        <th style="padding-left:24px">STUDENT</th>
                        <th>CODE</th>
                        <th>AUTH METHOD</th>
                        <th>SCAN TIME</th>
                        <th>STATUS</th>
                    </tr></thead>
                    <tbody>
                    @php
                        $drillPermissions = \App\Models\StudentPermission::where('start_date', '<=', $drillDate)
                            ->where('end_date', '>=', $drillDate)
                            ->whereIn('student_id', $drillStudents->pluck('id'))
                            ->get()
                            ->keyBy('student_id');
                    @endphp
                    @foreach($drillStudents as $stu)
                    @php
                        $a = $drillAttMap->get($stu->id);
                        $perm = $drillPermissions->get($stu->id);
                        $st = $a ? $a->status : ($perm ? 'excused' : 'absent');
                        $sc = match($st){ 'present'=>'var(--green)', 'late'=>'var(--amber)', 'excused'=>'var(--accent)', default=>'var(--red)' };
                        $init = collect(explode(' ', $stu->user->name ?? ''))->map(fn($n)=>substr($n,0,1))->join('');
                    @endphp
                    <tr>
                        <td style="padding-left:24px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:30px;height:30px;border-radius:50%;background:{{ $sc }}18;color:{{ $sc }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;flex-shrink:0">{{ strtoupper(substr($init,0,2)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:12px;color:var(--text)">{{ $stu->user->name ?? 'Unknown' }}</div>
                                    @if($perm)
                                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);margin-top:2px">📋 Permission: {{ $perm->reason }} ({{ strtoupper($perm->type) }})</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span style="font-family:var(--font-mono);font-size:10px;color:var(--muted2)">{{ $stu->student_code }}</span></td>
                        <td><span style="font-family:var(--font-mono);font-size:10px;color:var(--muted2)">{{ $a ? strtoupper($a->method).' AUTH' : '—' }}</span></td>
                        <td><span style="font-family:var(--font-mono);font-size:11px;font-weight:700;color:var(--accent)">{{ $a && $a->scan_time ? \Carbon\Carbon::parse($a->scan_time)->format('H:i') : '—' }}</span></td>
                        <td><span class="status-tag" style="background:{{ $sc }}15;color:{{ $sc }};border:1px solid {{ $sc }}30">{{ strtoupper($st) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @else
            <div class="panel" style="padding:60px;text-align:center">
                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--muted2)" style="margin:0 auto 18px;display:block"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div style="font-family:var(--font-display);font-weight:700;font-size:16px;color:var(--text)">Select a Course</div>
                <p style="font-family:var(--font-mono);font-size:10px;color:var(--muted);margin-top:8px">CHOOSE A COURSE FROM THE LEFT TO VIEW SESSION HISTORY</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// const csrf is already declared in layouts.app

// ── Toast ──────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.className = `toast show toast-${type}`;
    document.getElementById('toastIcon').textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
    document.getElementById('toastMsg').textContent = msg;
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Modal ──────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));

// ── View Toggle ───────────────────────────────
function toggleView(mode) {
    document.getElementById('viewMonitor').style.display = mode === 'monitor' ? '' : 'none';
    document.getElementById('viewReports').style.display = mode === 'reports' ? '' : 'none';
    document.getElementById('btnMonitor').style.opacity = mode === 'monitor' ? '1' : '.55';
    document.getElementById('btnReports').style.opacity = mode === 'reports' ? '1' : '.55';
    localStorage.setItem('teacherView', mode);
}

// Restore last view
const savedView = localStorage.getItem('teacherView') || 'monitor';
toggleView(savedView);

// ── Load Session ──────────────────────────────
function loadSession(sessionId) {
    const url = new URL(window.location.href);
    url.searchParams.set('session_id', sessionId);
    url.searchParams.delete('class_id');
    window.location.href = url.toString();
}

// ── Live Badge ──────────────────────────────
@if(isset($selectedSession) && $selectedSession && $selectedSession->status === 'active')
    document.getElementById('liveSessionBadge').style.display = 'flex';
@endif

// ── Session Status Update ──────────────────────
async function updateSessionStatus(sessionId, status) {
    try {
        const res = await fetch(`/api/teacher/session/${sessionId}/status-update`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ status })
        });
        const data = await res.json();
        if (data.success) {
            const label = status === 'active' ? 'ACTIVATED' : 'COMPLETED';
            showToast(`Session ${label} successfully.`, 'success');
            // Show summary achievement for completion
            if (status === 'completed') {
                showSessionSummary(sessionId);
            } else {
                setTimeout(() => location.reload(), 900);
            }
        } else {
            showToast(data.message || 'Failed to update session.', 'error');
        }
    } catch(e) { showToast('Network error.', 'error'); }
}

// ── Refresh Monitor ─────────────────────────
function refreshMonitor() {
    const icon = document.getElementById('refreshIcon');
    icon.style.animation = 'spin 0.8s linear infinite';
    setTimeout(() => { icon.style.animation=''; location.reload(); }, 300);
}

// ── Roster Filter ────────────────────────────
function filterRoster() {
    const q = (document.getElementById('rosterSearch')?.value || '').toLowerCase();
    const f = document.getElementById('rosterFilter')?.value || '';
    document.querySelectorAll('#rosterBody tr').forEach(row => {
        const nameMatch = !q || row.dataset.name.includes(q);
        const statusMatch = !f || row.dataset.status === f;
        row.style.display = nameMatch && statusMatch ? '' : 'none';
    });
}

// ── Manual Checkin Modal ──────────────────────
let _checkinStudentId = null, _checkinSessionId = null, _checkinStatus = 'present';

function openCheckin(studentId, name, code, sessionId) {
    _checkinStudentId = studentId;
    _checkinSessionId = sessionId;
    _checkinStatus = 'present';
    const init = name.trim().split(/\s+/).filter(Boolean).map(w=>w[0]).slice(0,2).join('').toUpperCase();
    document.getElementById('checkinAvatar').textContent = init || '?';
    document.getElementById('checkinName').textContent = name;
    document.getElementById('checkinCode').textContent = code;
    updateStatusButtons('present');
    openModal('checkinModal');
}

function setCheckin(status) {
    _checkinStatus = status;
    updateStatusButtons(status);
}

function updateStatusButtons(active) {
    const map = { present:'btnPresent', late:'btnLate', absent:'btnAbsent' };
    Object.entries(map).forEach(([s, id]) => {
        const btn = document.getElementById(id);
        btn.style.opacity = s === active ? '1' : '.5';
                        btn.style.transform = s === active ? 'scale(1.04)' : 'scale(1)';
    });
}

// ── Live Feed Polling ─────────────────────────
let lastAttendanceId = 0;
@if($selectedSession)
    @php
        $latest = $selectedSession->attendanceRecords->max('id') ?? 0;
    @endphp
    lastAttendanceId = {{ $latest }};
    
    // Auto-poll if session is active
    if ("{{ $selectedSession->status }}" === 'active') {
        setInterval(pollAttendance, 3500);
    }
@endif

async function pollAttendance() {
    if (typeof selectedSessionId === 'undefined') return;
    try {
        const res = await fetch(`/api/teacher/session/${selectedSessionId}/live-feed?last_id=${lastAttendanceId}`);
        const data = await res.json();
        if (data.new_records && data.new_records.length > 0) {
            data.new_records.forEach(rec => {
                showLiveNotification(rec);
                if (rec.id > lastAttendanceId) lastAttendanceId = rec.id;
                updateRosterRow(rec);
            });
            updateMonitorUI(data.stats);
        }
    } catch(e) {}
}

function showLiveNotification(rec) {
    const feed = document.getElementById('liveAlertFeed');
    if (!feed) return;
    const note = document.createElement('div');
    note.className = 'live-alert-note';
    note.style = `
        background: var(--surface2);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border);
        border-left: 4px solid var(--green);
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 12px;
        pointer-events: auto;
        animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    `;
    
    const init = rec.student_name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
    
    note.innerHTML = `
        <div style="width:36px;height:36px;border-radius:50%;background:color-mix(in srgb, var(--green) 8%, transparent);color:var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex-shrink:0">${init}</div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:12px;color:var(--text)">${escapeHTML(rec.student_name)}</div>
            <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:2px">Verified via ${escapeHTML(rec.method)} · ${escapeHTML(rec.time)}</div>
        </div>
    `;
    
    feed.prepend(note);
    setTimeout(() => {
        note.style.animation = 'fadeOutUp 0.5s ease forwards';
        setTimeout(() => note.remove(), 500);
    }, 5000);
}

function updateRosterRow(rec) {
    const row = document.querySelector(`#rosterBody tr[data-student-code="${rec.student_code}"]`) || 
                document.querySelector(`#rosterBody tr[data-name="${rec.student_name.toLowerCase()}"]`);
    if (row) {
        row.dataset.status = rec.status.toLowerCase();
        const statusTag = row.querySelector('.status-tag');
        if (statusTag) {
            statusTag.textContent = rec.status;
            const statusUpper = rec.status.toUpperCase();
            const color = statusUpper === 'PRESENT' ? 'var(--green)' : (statusUpper === 'LATE' ? 'var(--amber)' : (statusUpper === 'EXCUSED' ? 'var(--accent)' : 'var(--red)'));
            statusTag.style.background = `${color}15`;
            statusTag.style.color = color;
            statusTag.style.borderColor = `${color}30`;
        }
        const timeCell = row.cells[2];
        if (timeCell) {
            timeCell.innerHTML = `<span style="font-family:var(--font-mono);font-size:11px;color:var(--text2)">${rec.time.substring(0,5)}</span>`;
        }
        const methodCell = row.cells[3];
        if (methodCell) {
            methodCell.innerHTML = `<span style="font-family:var(--font-mono);font-size:9px;text-transform:uppercase;color:var(--muted2)">${rec.method === 'QR' ? '📱 QR' : '✏️ Manual'}</span>`;
        }
        // Flash row
        row.style.background = 'color-mix(in srgb, var(--accent) 3%, transparent)';
        setTimeout(() => row.style.background = '', 2000);
    }
}

function updateMonitorUI(stats) {
    const rate = Math.round((stats.present_count / (stats.total_students || 1)) * 100);
    const bar = document.getElementById('progressFill');
    if (bar) bar.style.width = rate + '%';
    
    const label = document.getElementById('progressLabel');
    if (label) label.textContent = `${stats.present_count}/${stats.total_students}`;

    const quickPresent = document.getElementById('quickPresent');
    if (quickPresent) quickPresent.textContent = stats.present_count;

    const quickExcused = document.getElementById('quickExcused');
    if (quickExcused) quickExcused.textContent = stats.excused_count || 0;

    const quickAbsent = document.getElementById('quickAbsent');
    if (quickAbsent) quickAbsent.textContent = Math.max(0, stats.total_students - stats.present_count - (stats.excused_count || 0));

    const quickRate = document.getElementById('quickRate');
    if (quickRate) quickRate.textContent = rate + '%';
}

async function showSessionSummary(sid) {
    try {
        const res = await fetch(`/api/teacher/session/${sid}/monitor`);
        const data = await res.json();
        const rate = Math.round((data.present_count / (data.total_count || 1)) * 100);
        
        // Show specialized toast
        const s = document.createElement('div');
        s.style = `position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:320px; background:var(--surface2); backdrop-filter:blur(25px); border:1px solid color-mix(in srgb, var(--accent) 25%, transparent); border-radius:24px; padding:32px; z-index:10000; box-shadow:0 30px 60px rgba(0,0,0,0.5); text-align:center; animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);`;
        s.innerHTML = `
            <div style="width:60px; height:60px; border-radius:50%; background:color-mix(in srgb, var(--accent) 9%, transparent); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; color:var(--accent);">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div style="font-family:var(--font-mono); font-size:10px; color:var(--accent); font-weight:700; letter-spacing:.12em; margin-bottom:8px;">SESSION ARCHIVED</div>
            <div style="font-family:var(--font-display); font-size:20px; font-weight:800; color:var(--text); margin-bottom:24px;">${rate}% Attendance</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px;">
                <div style="background:var(--surface3); padding:12px; border-radius:12px; border:1px solid var(--border);">
                    <div style="font-size:8px; color:var(--muted); margin-bottom:4px;">PRESENT</div>
                    <div style="font-weight:700; color:var(--text);">${data.present_count}</div>
                </div>
                <div style="background:var(--surface3); padding:12px; border-radius:12px; border:1px solid var(--border);">
                    <div style="font-size:8px; color:var(--muted); margin-bottom:4px;">EXPECTED</div>
                    <div style="font-weight:700; color:var(--text);">${data.total_count}</div>
                </div>
            </div>
            <button onclick="location.reload()" style="width:100%; background:var(--accent); color:white; border:none; border-radius:12px; padding:12px; font-family:var(--font-mono); font-size:10px; font-weight:700; cursor:pointer;">DONE</button>
        `;
        document.body.appendChild(s);
    } catch(e) { location.reload(); }
}

async function confirmCheckin() {
    if (!_checkinStudentId || !_checkinSessionId) return;
    const btn = document.getElementById('confirmCheckinBtn');
    btn.textContent = 'SAVING…';
    btn.disabled = true;
    try {
        const res = await fetch(`/api/teacher/session/${_checkinSessionId}/checkin`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ student_id: _checkinStudentId, status: _checkinStatus })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Marked as ${_checkinStatus.toUpperCase()} successfully.`, 'success');
            // 🎉 Trigger premium alert for manual track too
            showLiveNotification({
                student_name: name,
                student_code: code,
                status: _checkinStatus.toUpperCase(),
                time: new Date().toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' }),
                method: 'MANUAL'
            });
            closeModal('checkinModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to update.', 'error');
        }
    } catch(e) { showToast('Network error.', 'error'); }
    btn.textContent = 'CONFIRM';
    btn.disabled = false;
}
</script>

<style>
.session-btn:hover { background:color-mix(in srgb, var(--accent) 3%, transparent) !important; border-color:color-mix(in srgb, var(--accent) 19%, transparent) !important; }
.session-btn.active { background:color-mix(in srgb, var(--accent) 7%, transparent) !important; border-color:color-mix(in srgb, var(--accent) 27%, transparent) !important; }
#refreshIcon:not([style*="animation"]) { transition: transform .3s; }
#refreshIcon:not([style*="animation"]):hover { transform: rotate(90deg); }
@keyframes spin { to { transform: rotate(360deg); } }

@keyframes slideInRight {
    from { transform: translateX(100%) scale(0.9); opacity: 0; }
    to { transform: translateX(0) scale(1); opacity: 1; }
}
@keyframes fadeOutUp {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-20px); opacity: 0; }
}
@keyframes bounceIn {
    from { transform: translate(-50%,-50%) scale(0.6); opacity: 0; }
    to { transform: translate(-50%,-50%) scale(1); opacity: 1; }
}
.live-alert-note { transition: all 0.3s ease; }
.live-alert-note:hover { transform: scale(1.02); background: var(--surface3) !important; }
</style>
@endsection
