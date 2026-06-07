@extends('layouts.app')

@section('content')

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>TEACHERS</span>
                <span class="breadcrumb-sep">/</span>
                <a href="{{ route('admin.teacher-attendance') }}" class="breadcrumb-current" style="cursor:pointer;">ATTENDANCE</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">CLASS CHANGES</span>
            </div>
            <h1 class="page-title">Teacher Requests</h1>
            <p class="page-subtitle">PERMISSION · CORRECTIONS · CANCELLATIONS · RESCHEDULES</p>
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
    @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:var(--red);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:var(--red);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif
    @if(auth()->user()?->email === 'demo@example.com')
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);color:var(--amber);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v4m0-8h.01M5 21h14a2 2 0 0 0 2-2V7l-6-6H5a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2z"/>
            </svg>
            DEMO MODE IS READ-ONLY. APPROVE / REJECT REQUIRES A REAL ADMIN ACCOUNT.
        </div>
    @endif

    {{-- ═══ PERMISSION / CORRECTION REQUESTS ═══ --}}
    <div class="panel">
        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:var(--violet);box-shadow:0 0 8px var(--violet);animation:blink 2s infinite;"></div>
                <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);">TEACHER FRONT REQUESTS</span>
            </div>
            <div class="toolbar-count">
                <span>{{ $permissionRequests->total() ?? $permissionRequests->count() }}</span> REQUESTS
            </div>
        </div>

        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>TEACHER</th>
                        <th>SESSION</th>
                        <th>REQUEST DETAILS</th>
                        <th>STATUS</th>
                        <th>REVIEW ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissionRequests as $permissionItem)
                        @php
                            $requestTypeColors = [
                                'wrong_status' => ['bg'=>'rgba(139,92,246,.1)', 'color'=>'var(--violet)', 'border'=>'rgba(139,92,246,.25)'],
                                'missing_check_in' => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)', 'border'=>'rgba(245,158,11,.25)'],
                                'missing_check_out' => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)', 'border'=>'rgba(245,158,11,.25)'],
                                'internet_problem' => ['bg'=>'rgba(37,99,235,.1)', 'color'=>'var(--accent)', 'border'=>'rgba(37,99,235,.25)'],
                                'schedule_change' => ['bg'=>'rgba(16,185,129,.1)', 'color'=>'var(--emerald)', 'border'=>'rgba(16,185,129,.25)'],
                            ];
                            $rtc = $requestTypeColors[$permissionItem->request_type] ?? ['bg'=>'rgba(100,116,139,.1)','color'=>'var(--muted2)','border'=>'rgba(100,116,139,.25)'];
                            $statusColors = [
                                'pending'  => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)',  'border'=>'rgba(245,158,11,.25)'],
                                'approved' => ['bg'=>'rgba(34,197,94,.1)',  'color'=>'var(--green)',  'border'=>'rgba(34,197,94,.25)'],
                                'rejected' => ['bg'=>'rgba(239,68,68,.1)',  'color'=>'var(--red)',    'border'=>'rgba(239,68,68,.25)'],
                            ];
                            $stc = $statusColors[$permissionItem->status] ?? $statusColors['pending'];
                            $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                            $clr = $avatarColors[$permissionItem->id % count($avatarColors)];
                            $tName = $permissionItem->teacher->user->name ?? 'Unknown';
                            $session = $permissionItem->attendanceSession;
                            $subject = $session?->subject?->name ?? $permissionItem->schedule?->subject?->name ?? 'Subject';
                        @endphp
                        <tr class="fade-up">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-avatar" style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}44;font-size:10px;width:34px;height:34px;border-radius:50%;">
                                        {{ strtoupper(substr($tName, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="subject-name">{{ $tName }}</div>
                                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;">
                                            {{ $permissionItem->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">{{ $subject }}</div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:3px;">
                                    @if($session)
                                        {{ $session->attendance_date?->format('M d') }} · {{ $session->scheduled_start_time?->format('H:i') }} – {{ $session->scheduled_end_time?->format('H:i') }}
                                    @elseif($permissionItem->schedule)
                                        {{ $permissionItem->schedule->schedule_date?->format('M d') }} · {{ $permissionItem->schedule->scheduled_start_time?->format('H:i') }} – {{ $permissionItem->schedule->scheduled_end_time?->format('H:i') }}
                                    @else
                                        No session linked
                                    @endif
                                </div>
                            </td>
                            <td style="max-width:300px;">
                                <div style="display:flex;align-items:center;gap:7px;margin-bottom:6px;flex-wrap:wrap;">
                                    <span style="display:inline-flex;padding:3px 9px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $rtc['bg'] }};color:{{ $rtc['color'] }};border:1px solid {{ $rtc['border'] }};">
                                        {{ strtoupper(str_replace('_', ' ', $permissionItem->request_type)) }}
                                    </span>
                                    @if($permissionItem->requested_status)
                                        <span style="display:inline-flex;padding:3px 9px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:rgba(139,92,246,.1);color:var(--violet);border:1px solid rgba(139,92,246,.25);">
                                            STATUS: {{ strtoupper(str_replace('_', ' ', $permissionItem->requested_status)) }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:11px;color:var(--text2);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                    {{ $permissionItem->reason }}
                                </div>
                                @if($permissionItem->requested_check_in_time || $permissionItem->requested_check_out_time)
                                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);margin-top:4px;">
                                        IN: {{ $permissionItem->requested_check_in_time?->format('M d H:i') ?? '-' }}
                                        · OUT: {{ $permissionItem->requested_check_out_time?->format('M d H:i') ?? '-' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="display:inline-flex;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $stc['bg'] }};color:{{ $stc['color'] }};border:1px solid {{ $stc['border'] }};">
                                    {{ strtoupper($permissionItem->status) }}
                                </span>
                            </td>
                            <td>
                                @if($permissionItem->status === 'pending')
                                    <div style="display:flex;flex-direction:column;gap:7px;">
                                        <form method="POST" action="{{ route('admin.teacher-attendance.corrections.approve', $permissionItem) }}" style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Approval note…" style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:140px;">
                                            <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--green);border-color:var(--green);">APPROVE</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.teacher-attendance.corrections.reject', $permissionItem) }}" style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Reject reason…" style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:140px;">
                                            <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--red);border-color:var(--red);">REJECT</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;font-style:italic;">
                                        {{ $permissionItem->review_note ?? 'Reviewed' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                        </svg>
                                    </div>
                                    <div class="empty-title">No Teacher Front Requests</div>
                                    <div class="empty-desc">Permission, status, missing check-in, and missing checkout requests from teachers will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permissionRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $permissionRequests->hasPages())
            <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    SHOWING {{ $permissionRequests->firstItem() }}–{{ $permissionRequests->lastItem() }} OF {{ $permissionRequests->total() }}
                </span>
                {{ $permissionRequests->links('vendor.pagination.academy') }}
            </div>
        @endif
    </div>

    {{-- ═══ CLASS CHANGE TABLE ═══ --}}
    <div class="panel">
        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:var(--violet);box-shadow:0 0 8px var(--violet);animation:blink 2s infinite;"></div>
                <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);">PENDING REVIEW QUEUE</span>
            </div>
            <div class="toolbar-count">
                <span>{{ $changeRequests->total() ?? $changeRequests->count() }}</span> REQUESTS
            </div>
        </div>

        <div class="table-responsive">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>TEACHER</th>
                        <th>CURRENT SCHEDULE</th>
                        <th>REQUEST DETAILS</th>
                        <th>STATUS</th>
                        <th>REVIEW ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($changeRequests as $requestItem)
                        @php
                            $reqTypeColors = [
                                'cancellation' => ['bg'=>'rgba(239,68,68,.1)',   'color'=>'var(--red)',    'border'=>'rgba(239,68,68,.25)'],
                                'reschedule'   => ['bg'=>'rgba(37,99,235,.1)',   'color'=>'var(--accent)', 'border'=>'rgba(37,99,235,.25)'],
                                'replacement'  => ['bg'=>'rgba(16,185,129,.1)', 'color'=>'var(--emerald)','border'=>'rgba(16,185,129,.25)'],
                            ];
                            $rtc = $reqTypeColors[$requestItem->request_type] ?? ['bg'=>'rgba(139,92,246,.1)','color'=>'var(--violet)','border'=>'rgba(139,92,246,.25)'];
                            $statusColors = [
                                'pending'  => ['bg'=>'rgba(245,158,11,.1)', 'color'=>'var(--amber)',  'border'=>'rgba(245,158,11,.25)'],
                                'approved' => ['bg'=>'rgba(34,197,94,.1)',  'color'=>'var(--green)',  'border'=>'rgba(34,197,94,.25)'],
                                'rejected' => ['bg'=>'rgba(239,68,68,.1)',  'color'=>'var(--red)',    'border'=>'rgba(239,68,68,.25)'],
                            ];
                            $stc = $statusColors[$requestItem->status] ?? $statusColors['pending'];
                            $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                            $clr = $avatarColors[$requestItem->id % count($avatarColors)];
                            $tName = $requestItem->teacher->user->name ?? 'Unknown';
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
                                            {{ $requestItem->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Current Schedule --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $requestItem->schedule->subject->name ?? 'Subject' }}
                                </div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:3px;">
                                    {{ $requestItem->schedule->scheduled_start_time?->format('M d, H:i') }}
                                    – {{ $requestItem->schedule->scheduled_end_time?->format('H:i') }}
                                </div>
                            </td>

                            {{-- Request --}}
                            <td style="max-width:260px;">
                                <div style="display:flex;align-items:center;gap:7px;margin-bottom:6px;">
                                    <span style="display:inline-flex;padding:3px 9px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $rtc['bg'] }};color:{{ $rtc['color'] }};border:1px solid {{ $rtc['border'] }};">
                                        {{ strtoupper($requestItem->request_type) }}
                                    </span>
                                </div>
                                <div style="font-size:11px;color:var(--text2);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                    {{ $requestItem->reason }}
                                </div>
                                @if($requestItem->requested_start_time)
                                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--accent);margin-top:4px;">
                                        NEW: {{ $requestItem->requested_start_time->format('M d H:i') }}
                                        – {{ $requestItem->requested_end_time?->format('H:i') ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span style="display:inline-flex;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $stc['bg'] }};color:{{ $stc['color'] }};border:1px solid {{ $stc['border'] }};">
                                    {{ strtoupper($requestItem->status) }}
                                </span>
                            </td>

                            {{-- Review --}}
                            <td>
                                @if($requestItem->status === 'pending')
                                    <div style="display:flex;flex-direction:column;gap:7px;">
                                        <form method="POST" action="{{ route('admin.teacher-attendance.class-change.approve', $requestItem) }}"
                                            style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Approval note…"
                                                style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:140px;">
                                            <button type="submit" class="btn-primary"
                                                style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--green);border-color:var(--green);">
                                                APPROVE
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.teacher-attendance.class-change.reject', $requestItem) }}"
                                            style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <input class="form-input" name="review_note" placeholder="Reject reason…"
                                                style="height:32px;padding:0 10px;font-size:11px;flex:1;min-width:140px;">
                                            <button type="submit" class="btn-primary"
                                                style="height:32px;padding:0 12px;font-size:9px;letter-spacing:.08em;background:var(--red);border-color:var(--red);">
                                                REJECT
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.04em;font-style:italic;">
                                        {{ $requestItem->review_note ?? 'Reviewed' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div class="empty-title">No Class Change Requests</div>
                                    <div class="empty-desc">Teacher requests for cancellations, reschedules, and replacements will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($changeRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $changeRequests->hasPages())
            <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    SHOWING {{ $changeRequests->firstItem() }}–{{ $changeRequests->lastItem() }} OF {{ $changeRequests->total() }}
                </span>
                {{ $changeRequests->links('vendor.pagination.academy') }}
            </div>
        @endif
    </div>

@endsection
