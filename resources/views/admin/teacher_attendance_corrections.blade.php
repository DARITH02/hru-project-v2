@extends('layouts.app')

@section('content')

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>TEACHERS</span>
                <span class="breadcrumb-sep">/</span>
                <a href="{{ route('admin.teacher-attendance') }}" style="color:var(--muted);cursor:pointer;">ATTENDANCE</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">CORRECTIONS</span>
            </div>
            <h1 class="page-title">Teacher Front Requests</h1>
            <p class="page-subtitle">PERMISSION · STATUS · MISSING CHECK-INS · CHECK-OUTS</p>
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
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:var(--green);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @php
        $currentStatus = request('status', 'all');
        $showAbsentMenu = request('view') === 'absent';
        $statusButtons = [
            'all' => ['label' => '1 ALL', 'color' => 'var(--accent)'],
            'pending' => ['label' => '2 PENDING', 'color' => 'var(--amber)'],
            'approved' => ['label' => '3 APPROVED', 'color' => 'var(--green)'],
            'rejected' => ['label' => '4 REJECTED', 'color' => 'var(--red)'],
        ];
    @endphp
    <div class="panel" style="padding:12px 14px;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            @foreach($statusButtons as $statusKey => $button)
                @php
                    $active = !$showAbsentMenu && ($currentStatus === $statusKey || ($statusKey === 'all' && !request()->filled('status')));
                    $url = $statusKey === 'all'
                        ? route('admin.teacher-attendance.corrections')
                        : route('admin.teacher-attendance.corrections', ['status' => $statusKey]);
                @endphp
                <a href="{{ $url }}"
                    class="{{ $active ? 'btn-primary' : 'btn-secondary' }}"
                    style="height:38px;padding:0 13px;font-family:var(--font-mono);font-size:10px;font-weight:900;letter-spacing:.08em;gap:8px;{{ $active ? 'background:'.$button['color'].';border-color:'.$button['color'].';color:white;' : 'color:var(--text2);' }}">
                    <span>{{ $button['label'] }}</span>
                    <span style="display:inline-flex;min-width:20px;height:20px;padding:0 6px;align-items:center;justify-content:center;border-radius:99px;background:{{ $active ? 'rgba(255,255,255,.18)' : 'var(--surface3)' }};font-size:9px;">
                        {{ $statusCounts[$statusKey] ?? 0 }}
                    </span>
                </a>
            @endforeach
            <a href="{{ route('admin.teacher-attendance.corrections', ['view' => 'absent']) }}"
                class="{{ $showAbsentMenu ? 'btn-primary' : 'btn-secondary' }}"
                style="height:38px;padding:0 13px;font-family:var(--font-mono);font-size:10px;font-weight:900;letter-spacing:.08em;gap:8px;{{ $showAbsentMenu ? 'background:var(--red);border-color:var(--red);color:white;' : 'color:var(--text2);' }}">
                <span>5 ABSENT</span>
                <span style="display:inline-flex;min-width:20px;height:20px;padding:0 6px;align-items:center;justify-content:center;border-radius:99px;background:{{ $showAbsentMenu ? 'rgba(255,255,255,.18)' : 'rgba(239,68,68,.1)' }};color:{{ $showAbsentMenu ? 'white' : 'var(--red)' }};font-size:9px;">
                    {{ $totalAbsentSessions ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    @if($showAbsentMenu)
        <div class="panel">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:7px;height:7px;border-radius:50%;background:var(--red);box-shadow:0 0 8px var(--red);"></div>
                    <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);font-weight:800;">ABSENT TOTALS BY TEACHER</span>
                </div>
                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    <span style="color:var(--red);font-weight:900;">{{ $totalAbsentSessions ?? 0 }}</span> TOTAL ABSENTS
                </div>
            </div>

            <div class="table-responsive" style="margin-top:12px;">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th>TEACHER INFO</th>
                            <th>DEPARTMENT</th>
                            <th>LATEST ABSENT</th>
                            <th style="text-align:right;">TOTAL ABSENTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absentTeacherStats ?? collect() as $absentStat)
                            @php
                                $teacherName = $absentStat->teacher?->user?->name ?? 'Unknown Teacher';
                                $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                                $clr = $avatarColors[((int) $absentStat->teacher_id) % count($avatarColors)];
                                $latestAbsentDate = $absentStat->latest_absent_date ? \Carbon\Carbon::parse($absentStat->latest_absent_date) : null;
                            @endphp
                            <tr class="fade-up">
                                <td>
                                    <div class="subject-cell">
                                        <div class="subject-avatar"
                                            style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}44;font-size:10px;width:34px;height:34px;border-radius:50%;">
                                            {{ strtoupper(substr($teacherName, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="subject-name">{{ $teacherName }}</div>
                                            <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;">
                                                TEACHER #{{ $absentStat->teacher_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                        {{ $absentStat->teacher?->department?->name ?? 'No Department' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--muted);">
                                        {{ $latestAbsentDate?->format('M d, Y') ?? '-' }}
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:30px;border-radius:99px;background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.25);font-family:var(--font-mono);font-size:13px;font-weight:900;">
                                        {{ $absentStat->absent_total }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-title">No Absent Records</div>
                                        <div class="empty-desc">Teachers with absent attendance sessions will appear here.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(($absentTeacherStats ?? null) instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                        SHOWING {{ $absentTeacherStats->firstItem() ?? 0 }}–{{ $absentTeacherStats->lastItem() ?? 0 }} OF {{ $absentTeacherStats->total() }} TEACHERS
                    </span>
                    @if($absentTeacherStats->hasPages())
                        {{ $absentTeacherStats->links('vendor.pagination.academy') }}
                    @endif
                </div>
            @endif
        </div>
    @else

    {{-- ═══ TABLE PANEL ═══ --}}
    <div class="panel">

        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px var(--accent);animation:blink 2s infinite;"></div>
                <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);">TEACHER FRONT REQUESTS</span>
            </div>
            <div class="toolbar-count">
                <span>{{ $corrections->total() ?? $corrections->count() }}</span> REQUESTS ·
                <span>{{ $absentCounts[$currentStatus] ?? $absentCounts['all'] ?? 0 }}</span> ABSENTS
            </div>
        </div>

        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>TEACHER</th>
                        <th>SESSION</th>
                        <th>REQUEST TYPE</th>
                        <th>REQUESTED VALUES</th>
                        <th>STATUS</th>
                        <th>REVIEW ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($corrections as $correction)
                        @php
                            $reqTypeColors = [
                                'missing_check_in'  => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)',   'border'=>'rgba(245,158,11,.25)',  'label'=>'MISSING CHECK-IN'],
                                'missing_check_out' => ['bg'=>'rgba(251,146,60,.1)', 'color'=>'var(--orange)',  'border'=>'rgba(251,146,60,.25)',  'label'=>'MISSING CHECK-OUT'],
                                'status_dispute'    => ['bg'=>'rgba(139,92,246,.1)', 'color'=>'var(--violet)',  'border'=>'rgba(139,92,246,.25)',  'label'=>'STATUS DISPUTE'],
                                'schedule_issue'    => ['bg'=>'rgba(37,99,235,.1)',  'color'=>'var(--accent)',  'border'=>'rgba(37,99,235,.25)',   'label'=>'SCHEDULE ISSUE'],
                            ];
                            $rtc = $reqTypeColors[$correction->request_type]
                                ?? ['bg'=>'rgba(100,116,139,.1)','color'=>'var(--muted2)','border'=>'rgba(100,116,139,.25)',
                                    'label'=>str_replace('_',' ',strtoupper($correction->request_type))];

                            $statusColors = [
                                'pending'  => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)', 'border'=>'rgba(245,158,11,.25)'],
                                'approved' => ['bg'=>'rgba(34,197,94,.1)',  'color'=>'var(--green)', 'border'=>'rgba(34,197,94,.25)'],
                                'rejected' => ['bg'=>'rgba(239,68,68,.1)',  'color'=>'var(--red)',   'border'=>'rgba(239,68,68,.25)'],
                            ];
                            $stc = $statusColors[$correction->status] ?? $statusColors['pending'];

                            $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                            $clr = $avatarColors[$correction->id % count($avatarColors)];
                            $tName = $correction->teacher->user->name ?? 'Unknown';
                            $session = $correction->attendanceSession;
                            $subject = $session?->subject?->name ?? $correction->schedule?->subject?->name ?? 'Subject';
                            $className = $session?->classRoom?->name ?? $correction->schedule?->classRoom?->name ?? 'Class';
                        @endphp
                        <tr class="fade-up">

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
                                            {{ $correction->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Session --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">{{ $subject }}</div>
                                <div style="font-size:10px;color:var(--muted);margin-top:2px;">{{ $className }}</div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:3px;">
                                    @if($session)
                                        {{ $session->attendance_date?->format('M d') }} · {{ $session->scheduled_start_time?->format('H:i') }} – {{ $session->scheduled_end_time?->format('H:i') }}
                                    @elseif($correction->schedule)
                                        {{ $correction->schedule->schedule_date?->format('M d') }} · {{ $correction->schedule->scheduled_start_time?->format('H:i') }} – {{ $correction->schedule->scheduled_end_time?->format('H:i') }}
                                    @else
                                        No session linked
                                    @endif
                                </div>
                            </td>

                            {{-- Request type + reason --}}
                            <td style="max-width:220px;">
                                <span style="display:inline-flex;padding:3px 9px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $rtc['bg'] }};color:{{ $rtc['color'] }};border:1px solid {{ $rtc['border'] }};margin-bottom:6px;">
                                    {{ $rtc['label'] }}
                                </span>
                                <div style="font-size:11px;color:var(--muted);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                    {{ $correction->reason }}
                                </div>
                            </td>

                            {{-- Requested Values --}}
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;font-family:var(--font-mono);font-size:9px;letter-spacing:.04em;">
                                    @if($correction->requested_check_in_time)
                                        <div style="display:flex;align-items:center;gap:5px;">
                                            <span style="color:var(--muted2);">IN:</span>
                                            <span style="color:var(--green);font-weight:700;">{{ $correction->requested_check_in_time->format('M d, H:i') }}</span>
                                        </div>
                                    @endif
                                    @if($correction->requested_check_out_time)
                                        <div style="display:flex;align-items:center;gap:5px;">
                                            <span style="color:var(--muted2);">OUT:</span>
                                            <span style="color:var(--accent2);font-weight:700;">{{ $correction->requested_check_out_time->format('M d, H:i') }}</span>
                                        </div>
                                    @endif
                                    @if($correction->requested_status)
                                        <div style="display:flex;align-items:center;gap:5px;">
                                            <span style="color:var(--muted2);">STATUS:</span>
                                            <span style="color:var(--amber);font-weight:700;">{{ strtoupper(str_replace('_',' ',$correction->requested_status)) }}</span>
                                        </div>
                                    @endif
                                    @if(!$correction->requested_check_in_time && !$correction->requested_check_out_time && !$correction->requested_status)
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status badge --}}
                            <td>
                                <span style="display:inline-flex;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $stc['bg'] }};color:{{ $stc['color'] }};border:1px solid {{ $stc['border'] }};">
                                    {{ strtoupper($correction->status) }}
                                </span>
                            </td>

                            {{-- Review --}}
                            <td>
                                @if($correction->status === 'pending')
                                    <div style="display:flex;flex-direction:column;gap:7px;">
                                        <form method="POST" action="{{ route('admin.teacher-attendance.corrections.approve', $correction) }}"
                                            style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Approval note…"
                                                style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:130px;">
                                            <button type="submit" class="btn-primary"
                                                style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--green);border-color:var(--green);white-space:nowrap;">
                                                APPROVE
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.teacher-attendance.corrections.reject', $correction) }}"
                                            style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Reject reason…"
                                                style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:130px;">
                                            <button type="submit" class="btn-primary"
                                                style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--red);border-color:var(--red);white-space:nowrap;">
                                                REJECT
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);font-style:italic;">
                                        {{ $correction->review_note ?? 'Reviewed' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                    <div class="empty-title">No Correction Requests</div>
                                    <div class="empty-desc">Teacher correction requests for missing check-ins, check-outs, and status disputes will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($corrections instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    SHOWING {{ $corrections->firstItem() ?? 0 }}–{{ $corrections->lastItem() ?? 0 }} OF {{ $corrections->total() }}
                    · ABSENTS {{ $absentCounts[$currentStatus] ?? $absentCounts['all'] ?? 0 }}
                </span>
                @if($corrections->hasPages())
                    {{ $corrections->links('vendor.pagination.academy') }}
                @endif
            </div>
        @endif
    </div>
    @endif

@endsection
