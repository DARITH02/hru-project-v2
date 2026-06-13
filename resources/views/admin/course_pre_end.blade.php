@extends('layouts.app')

@section('title', 'Pre-End Academic Review')

@section('content')
<div class="page-container" style="padding: 30px; max-width: 1200px; margin: 0 auto;">
    {{-- Header Section --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:40px;">
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <a href="{{ route('admin.courses') }}" style="color:var(--muted); hover:color:var(--text); transition:0.2s;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 style="font-size:28px; font-weight:900; color:var(--text); margin:0; font-family:var(--font-display);">Pre-End Academic Review</h1>
            </div>
            <p style="color:var(--muted); font-size:14px;">Finalizing <span style="color:var(--accent); font-weight:700;">{{ $class->subject->name ?? 'Unknown Class' }}</span> • {{ $assignment->academic_year ?? 'N/A' }} Semester {{ $assignment->semester ?? 'N/A' }}</p>
        </div>
        
        @if($assignment)
        <div style="display:flex; gap:12px;">
            <button onclick="openFinalModal()" id="headerFinalBtn" class="btn-primary" style="height:48px; padding:0 30px; border-radius:14px; background:var(--red); border:none; font-weight:800; display:flex; align-items:center; gap:10px; box-shadow:0 10px 20px color-mix(in srgb, var(--red) 20%, transparent);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                FINALIZE & END CLASS
            </button>
        </div>
        @endif
    </div>

    @if(isset($error))
    <div style="padding:24px; background:color-mix(in srgb, var(--red) 3%, transparent); border:1px solid color-mix(in srgb, var(--red) 20%, transparent); border-radius:20px; margin-bottom:40px; display:flex; align-items:center; gap:20px;">
        <div style="width:48px; height:48px; background:var(--red); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <div style="font-weight:900; color:var(--text); font-size:16px; margin-bottom:4px;">Incomplete Configuration</div>
            <p style="color:var(--muted); font-size:13px; margin:0;">{{ $error }}</p>
        </div>
    </div>
    @endif

    {{-- Stats Row --}}
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:40px;">
        <div class="stat-card" style="background:var(--surface); padding:24px; border-radius:20px; border:1px solid var(--border); position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">Total Students</div>
            <div style="font-size:32px; font-weight:900; color:var(--text); font-family:var(--font-display);">{{ $stats['total_students'] }}</div>
            <div style="position:absolute; right:-10px; bottom:-10px; opacity:0.05; transform:rotate(-15deg);">
                <svg width="100" height="100" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
        <div class="stat-card" style="background:var(--surface); padding:24px; border-radius:20px; border:1px solid var(--border); position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">Total Sessions</div>
            <div style="font-size:32px; font-weight:900; color:var(--text); font-family:var(--font-display);">{{ $stats['total_sessions'] }}</div>
            <div style="position:absolute; right:-10px; bottom:-10px; opacity:0.05; transform:rotate(-15deg);">
                <svg width="100" height="100" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-card" style="background:var(--surface); padding:24px; border-radius:20px; border:1px solid var(--border); position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">Avg Attendance</div>
            <div style="font-size:32px; font-weight:900; color:var(--accent); font-family:var(--font-display);">{{ $stats['avg_attendance'] }}%</div>
            <div style="position:absolute; right:-10px; bottom:-10px; opacity:0.05; transform:rotate(-15deg);">
                <svg width="100" height="100" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="stat-card" style="background:{{ $stats['scheduled_count'] > 0 ? 'color-mix(in srgb, var(--red) 3%, transparent)' : 'color-mix(in srgb, var(--green) 3%, transparent)' }}; padding:24px; border-radius:20px; border:1px solid {{ $stats['scheduled_count'] > 0 ? 'color-mix(in srgb, var(--red) 20%, transparent)' : 'color-mix(in srgb, var(--green) 20%, transparent)' }}; position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:800; color:{{ $stats['scheduled_count'] > 0 ? 'var(--red)' : 'var(--green)' }}; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">Status</div>
            <div style="font-size:20px; font-weight:900; color:{{ $stats['scheduled_count'] > 0 ? 'var(--red)' : 'var(--green)' }}; font-family:var(--font-display);">
                {{ $stats['scheduled_count'] > 0 ? $stats['scheduled_count'] . ' PENDING SESSIONS' : 'READY TO END' }}
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
        {{-- Left: Student List --}}
        <div style="display:flex; flex-direction:column; gap:30px; min-width:0;">
            <div style="background:var(--surface); border-radius:24px; border:1px solid var(--border); overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.05);">
                <div style="padding:24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <h2 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">Student Performance Records</h2>
                    <button onclick="saveStudentScoresOnly()" id="saveScoresBtn" class="btn-secondary" style="height:36px; padding:0 20px; border-radius:10px; font-size:11px; font-weight:700;">SAVE SCORES ONLY</button>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:600px;">
                        <thead style="background:var(--surface2);">
                            <tr>
                                <th style="padding:15px 24px; text-align:left; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase;">Student Info</th>
                                <th style="padding:15px 24px; text-align:center; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase;">Att (20)</th>
                                <th style="padding:15px 24px; text-align:center; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase;">Mid (15)</th>
                                <th style="padding:15px 24px; text-align:center; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase;">Asgn (15)</th>
                                <th style="padding:15px 24px; text-align:center; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase;">Final (50)</th>
                                <th style="padding:15px 24px; text-align:right; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase; width:100px;">Total (100)</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            @forelse($students as $s)
                            <tr style="border-bottom:1px solid var(--border); transition:0.2s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                <td style="padding:18px 24px;">
                                    <div style="font-weight:800; color:var(--text); font-size:14px; margin-bottom:2px;">{{ $s['name'] }}</div>
                                    <div style="font-size:10px; color:var(--muted); font-family:var(--font-mono);">{{ $s['code'] }}</div>
                                </td>
                                <td style="padding:18px 24px; text-align:center;">
                                    <div style="font-weight:900; color:var(--accent); font-family:var(--font-mono);">{{ $s['att_score'] }}</div>
                                    <div style="font-size:9px; color:var(--muted);">{{ $s['rate'] }}%</div>
                                    @if(($s['permission_sessions'] ?? 0) > 0)
                                        <div style="font-size:8px; color:var(--accent); font-family:var(--font-mono); margin-top:2px;">{{ $s['permission_sessions'] }} PERMISSION</div>
                                    @endif
                                </td>
                                <td style="padding:18px 24px; text-align:center;">
                                    <input type="number" class="score-input mid-input" data-student-id="{{ $s['id'] }}" value="{{ $s['midterm'] }}" min="0" max="15" step="0.5" style="width:60px; height:34px; background:var(--surface3); border:1px solid var(--border); border-radius:8px; text-align:center; color:var(--text); font-weight:800; font-family:var(--font-mono);">
                                </td>
                                <td style="padding:18px 24px; text-align:center;">
                                    <input type="number" class="score-input asgn-input" data-student-id="{{ $s['id'] }}" value="{{ $s['assignment'] }}" min="0" max="15" step="0.5" style="width:60px; height:34px; background:var(--surface3); border:1px solid var(--border); border-radius:8px; text-align:center; color:var(--text); font-weight:800; font-family:var(--font-mono);">
                                </td>
                                <td style="padding:18px 24px; text-align:center;">
                                    <input type="number" class="score-input final-input" data-student-id="{{ $s['id'] }}" value="{{ $s['final'] }}" min="0" max="50" step="0.5" style="width:60px; height:34px; background:var(--surface3); border:1px solid var(--border); border-radius:8px; text-align:center; color:var(--text); font-weight:800; font-family:var(--font-mono);">
                                </td>
                                <td style="padding:18px 24px; text-align:right;">
                                    <div style="font-weight:900; color:var(--text); font-size:16px; font-family:var(--font-display);">{{ $s['total'] }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="padding:40px; text-align:center; color:var(--muted); font-style:italic;">No students enrolled in this class.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 📅 Detailed Session Ledger --}}
            <div style="background:var(--surface); border-radius:24px; border:1px solid var(--border); overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.05); display:flex; flex-direction:column;">
                <div style="padding:24px; border-bottom:1px solid var(--border); background:var(--surface2);">
                    <h2 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">Detailed Session Ledger</h2>
                    <p style="font-size:12px; color:var(--muted); margin:4px 0 0 0;">Date-by-date attendance breakdown for all generated sessions.</p>
                </div>
                <div style="overflow-x:auto; width:100%; background:var(--surface2);">
                    <table style="border-collapse:collapse; table-layout:fixed; width: max-content; min-width: 100%;">
                        <thead>
                            <tr style="background:var(--surface3);">
                                <th style="padding:15px 24px; text-align:left; font-size:10px; color:var(--muted); font-weight:800; text-transform:uppercase; position:sticky; left:0; background:var(--surface3); z-index:3; width:220px; border-right:1px solid var(--border);">Student Name</th>
                                @foreach($sessions as $session)
                                <th style="padding:12px 10px; text-align:center; font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; width:85px; border-left:1px solid var(--border);">
                                    <div style="color:var(--text); margin-bottom:2px;">{{ \Carbon\Carbon::parse($session->start_time)->format('M d') }}</div>
                                    <div style="font-size:8px; opacity:0.6; font-weight:500;">#{{ $loop->iteration }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $s)
                            <tr style="border-bottom:1px solid var(--border); background:var(--surface);">
                                <td style="padding:12px 24px; font-weight:700; color:var(--text); position:sticky; left:0; background:var(--surface); z-index:2; border-right:1px solid var(--border); font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $s['name'] }}
                                </td>
                                @foreach($sessions as $session)
                                <td style="padding:12px 10px; text-align:center; border-left:1px solid var(--border);">
                                    @php
                                        $status = $attendanceGrid[$s['id']][$session->id] ?? 'absent';
                                        $color = $status == 'present' ? 'var(--green)' : ($status == 'late' ? 'var(--amber)' : 'var(--red)');
                                        $opacity = $status == 'absent' ? '0.2' : '1';
                                    @endphp
                                    <div style="width:12px; height:12px; border-radius:50%; background:{{ $color }}; margin:0 auto; opacity:{{ $opacity }}; box-shadow:0 0 8px {{ $status != 'absent' ? $color : 'transparent' }};" title="{{ ucfirst($status) }}"></div>
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($sessions) + 1 }}" style="padding:40px; text-align:center; color:var(--muted); font-style:italic;">No attendance records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="padding:15px 24px; border-top:1px solid var(--border); display:flex; gap:20px; font-size:10px; font-weight:700; color:var(--muted);">
                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px; height:8px; border-radius:50%; background:var(--green);"></div> PRESENT</div>
                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px; height:8px; border-radius:50%; background:var(--amber);"></div> LATE</div>
                    <div style="display:flex; align-items:center; gap:6px;"><div style="width:8px; height:8px; border-radius:50%; background:var(--red);"></div> ABSENT</div>
                </div>
            </div>
        </div>

        {{-- Right: Final Review & Notes --}}
        <div>
            <div style="background:var(--surface); border-radius:24px; border:1px solid var(--border); padding:30px; position:sticky; top:30px; box-shadow:0 20px 40px rgba(0,0,0,0.05);">
                <h2 style="font-size:18px; font-weight:800; color:var(--text); margin-bottom:24px;">Administrative Summary</h2>
                
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label" style="font-size:10px; color:var(--muted); letter-spacing:0.05em">TEACHER'S INPUT SCORE</label>
                    <div style="height:50px; background:var(--surface2); border-radius:12px; border:1px solid var(--border); display:flex; align-items:center; padding:0 18px; font-weight:900; color:var(--violet); font-size:18px; font-family:var(--font-mono);">
                        {{ $assignment->teacher_score ?? '—' }}
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label" style="font-size:10px; color:var(--text); letter-spacing:0.05em">FINAL CLASS SCORE</label>
                    <input id="adminScoreInput" type="number" step="0.1" min="0" max="100" value="{{ $assignment->admin_score ?? 0 }}" style="width:100%; height:50px; background:var(--surface2); border:1px solid color-mix(in srgb, var(--accent) 20%, transparent); border-radius:12px; padding:0 18px; font-weight:900; color:var(--text); font-size:18px; font-family:var(--font-mono);">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label" style="font-size:10px; color:var(--muted); letter-spacing:0.05em">REVIEWER NOTES</label>
                    <textarea id="gradingNotesInput" style="width:100%; height:100px; background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:15px; font-size:13px; color:var(--text); resize:none;">{{ $assignment->grading_notes ?? '' }}</textarea>
                </div>

                <div class="form-group" style="margin-bottom:30px;">
                    <label class="form-label" style="font-size:10px; color:var(--muted); letter-spacing:0.05em">GRADUATION STATUS</label>
                    <select id="gradingStatusSelect" style="width:100%; height:50px; background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:0 15px; font-weight:800; color:var(--text);">
                        <option value="pending" {{ ($assignment->grading_status ?? '') == 'pending' ? 'selected' : '' }}>PENDING REVIEW</option>
                        <option value="reviewed" {{ ($assignment->grading_status ?? '') == 'reviewed' ? 'selected' : '' }}>REVIEWED & LOCKED</option>
                        <option value="finalized" {{ ($assignment->grading_status ?? '') == 'finalized' ? 'selected' : '' }}>FINALIZED (READY TO END)</option>
                    </select>
                </div>

                @if($assignment && $stats['scheduled_count'] > 0)
                <div style="padding:15px; background:color-mix(in srgb, var(--red) 3%, transparent); border:1px dashed color-mix(in srgb, var(--red) 20%, transparent); border-radius:12px; margin-bottom:20px;">
                    <div style="color:var(--red); font-size:11px; font-weight:800; margin-bottom:5px;">CANNOT FINALIZE YET</div>
                    <p style="font-size:10px; color:var(--muted); line-height:1.4; margin:0;">There are still <strong>{{ $stats['scheduled_count'] }}</strong> active sessions. All sessions must be completed or skipped before closing the class.</p>
                </div>
                @endif

                <button onclick="downloadReport()" class="btn-secondary" style="width:100%; height:46px; border-radius:12px; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:12px;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    DOWNLOAD PDF REPORT
                </button>

                <button onclick="downloadExcel()" class="btn-secondary" style="width:100%; height:46px; border-radius:12px; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:12px; border:1px solid #107c10; color:#107c10; background:rgba(16, 124, 16, 0.05);">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6-9l3-3m0 0l3 3m-3-3v12"/></svg>
                    DOWNLOAD EXCEL REPORT
                </button>

                <button onclick="openFinalModal()" id="finalEndBtn" class="btn-primary" style="width:100%; height:54px; background:var(--red); border-radius:16px; font-weight:900; border:none; box-shadow:0 12px 24px color-mix(in srgb, var(--red) 20%, transparent); color:white; cursor:pointer;">
                    FINALIZE & END CLASS
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 🎓 Finalization Modal --}}
<div id="finalModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:10000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface); width:100%; max-width:450px; border-radius:32px; border:1px solid var(--border); overflow:hidden; box-shadow:0 40px 100px rgba(0,0,0,0.4); animation:modalSlide 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <div id="modalContentWarning" style="padding:40px; text-align:center;">
            <div style="width:80px; height:80px; background:color-mix(in srgb, var(--amber) 13%, transparent); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                <span style="font-size:32px;">⚠️</span>
            </div>
            <h3 style="font-size:22px; font-weight:900; color:var(--text); margin:0 0 12px 0;">Finalization Required</h3>
            <p style="font-size:14px; color:var(--muted); line-height:1.6; margin:0 0 32px 0;">To end this class schedule, you must set the Graduation Status to <b style="color:var(--text);">FINALIZED</b> in the administrative summary.</p>
            <button onclick="closeFinalModal()" class="btn-secondary" style="width:100%; height:54px; border-radius:16px; font-weight:800;">UNDERSTOOD</button>
        </div>
        
        <div id="modalContentConfirm" style="display:none; padding:40px; text-align:center;">
            <div style="width:80px; height:80px; background:color-mix(in srgb, var(--green) 13%, transparent); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                <span style="font-size:32px;">🚀</span>
            </div>
            <h3 style="font-size:22px; font-weight:900; color:var(--text); margin:0 0 12px 0;">Purge & Finalize?</h3>
            <p style="font-size:14px; color:var(--muted); line-height:1.6; margin:0 0 32px 0;">This will <b>permanently delete</b> all remaining sessions from the database, send the Telegram report, and generate the final PDF. This action is final.</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button onclick="closeFinalModal()" class="btn-secondary" style="height:54px; border-radius:16px; font-weight:800;">CANCEL</button>
                <button onclick="executeFinalEnd()" id="confirmFinalBtn" class="btn-primary" style="height:54px; background:var(--red); border-radius:16px; font-weight:800; border:none;">YES, END NOW</button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modalSlide {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
const assignmentId = {{ $assignment->id ?? 'null' }};
const classId = {{ $class->id }};

function openFinalModal() {
    const status = document.getElementById('gradingStatusSelect').value;
    const modal = document.getElementById('finalModal');
    const warning = document.getElementById('modalContentWarning');
    const confirm = document.getElementById('modalContentConfirm');
    
    modal.style.display = 'flex';
    if (status !== 'finalized') {
        warning.style.display = 'block';
        confirm.style.display = 'none';
    } else {
        warning.style.display = 'none';
        confirm.style.display = 'block';
    }
}

function closeFinalModal() {
    document.getElementById('finalModal').style.display = 'none';
}

async function saveStudentScoresOnly() {
    const btn = document.getElementById('saveScoresBtn');
    if (!btn) return;
    const ogHtml = btn.innerHTML;
    const scores = Array.from(document.querySelectorAll('.score-input.mid-input')).map(input => {
        const row = input.closest('tr');
        const studentId = input.dataset.studentId;
        return {
            student_id: studentId,
            attendance_score: parseFloat(row.querySelector('td:nth-child(2) div:first-child').textContent),
            midterm_score: parseFloat(row.querySelector('.mid-input').value),
            assignment_score: parseFloat(row.querySelector('.asgn-input').value),
            final_score: parseFloat(row.querySelector('.final-input').value),
            notes: ''
        };
    });

    btn.innerHTML = 'SAVING...';
    btn.disabled = true;

    try {
        if (!assignmentId) throw new Error('No assignment ID');
        const res = await fetch(`/api/admin/semesters/${assignmentId}/student-scores`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ scores })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Scores saved. Reloading to update totals...', 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (err) {
        showToast('Error saving scores.', 'error');
    } finally {
        btn.innerHTML = ogHtml;
        btn.disabled = false;
    }
}

async function executeFinalEnd() {
    const scheduledCount = {{ $stats['scheduled_count'] }};
    
    if (scheduledCount > 0) {
        showToast('All sessions must be completed before ending the class.', 'error');
        closeFinalModal();
        return;
    }

    const btn = document.getElementById('confirmFinalBtn');
    btn.innerHTML = 'PROCESSING...';
    btn.disabled = true;

    try {
        if (!assignmentId) throw new Error('No assignment ID');
        
        // 1. Save individual scores
        const scores = Array.from(document.querySelectorAll('.score-input.mid-input')).map(input => {
            const row = input.closest('tr');
            return {
                student_id: input.dataset.studentId,
                attendance_score: parseFloat(row.querySelector('td:nth-child(2) div:first-child').textContent),
                midterm_score: parseFloat(row.querySelector('.mid-input').value),
                assignment_score: parseFloat(row.querySelector('.asgn-input').value),
                final_score: parseFloat(row.querySelector('.final-input').value),
                notes: ''
            };
        });

        await fetch(`/api/admin/semesters/${assignmentId}/student-scores`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ scores })
        });

        // 2. Save admin overall score and status
        await fetch(`/api/admin/semesters/${assignmentId}/score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                admin_score: document.getElementById('adminScoreInput').value,
                grading_notes: document.getElementById('gradingNotesInput').value,
                grading_status: 'finalized'
            })
        });

        // 3. End class schedule
        const res = await fetch(`/api/admin/terminate-class/${classId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        
        if (data.success) {
            showToast('Class archived! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = `/api/admin/semesters/${assignmentId}/report`;
            }, 1000);
            setTimeout(() => window.location.href = "{{ route('admin.courses') }}", 4000);
        }
    } catch (err) {
        showToast('An error occurred during finalization.', 'error');
    } finally {
        btn.innerHTML = 'YES, END NOW';
        btn.disabled = false;
        closeFinalModal();
    }
}

function downloadReport() {
    if (!assignmentId) {
        showToast('No assignment found to generate report.', 'error');
        return;
    }
    window.location.href = `/api/admin/semesters/${assignmentId}/report`;
}

function downloadExcel() {
    window.location.href = "{{ route('admin.courses.pre-end.export', $class->id) }}";
}
</script>

<style>
.stat-card:hover { transform: translateY(-5px); transition: 0.3s; box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
.btn-primary:active { transform: scale(0.98); }
</style>
@endsection
