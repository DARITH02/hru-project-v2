@extends('layouts.app')

@push('styles')
<style>
    .teacher-attendance-page {
        display: grid;
        gap: 22px;
        padding: 24px;
    }

    .teacher-attendance-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: start;
    }

    .teacher-attendance-title h1 {
        margin: 0;
        color: var(--text);
        font-size: clamp(30px, 3vw, 42px);
        line-height: 1.05;
        font-weight: 800;
        letter-spacing: 0;
    }

    .teacher-attendance-title p {
        margin-top: 8px;
        color: var(--muted);
        font-size: 15px;
    }

    .teacher-attendance-actions {
        display: grid;
        grid-template-columns: repeat(2, max-content) minmax(150px, 180px);
        gap: 10px;
        align-items: stretch;
    }

    .teacher-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        height: 50px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--accent);
        padding: 0 16px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: var(--shadow-sm);
    }

    .teacher-action.is-primary {
        border-color: var(--accent);
        background: var(--accent);
        color: #fff;
        box-shadow: 0 10px 22px color-mix(in srgb, var(--accent) 24%, transparent);
    }

    .teacher-month-card,
    .teacher-metric,
    .teacher-panel,
    .teacher-table-card {
        border: 1px solid var(--border);
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
    }

    .teacher-month-card {
        min-height: 64px;
        padding: 12px 16px;
    }

    .teacher-kicker {
        display: block;
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .teacher-month-card strong,
    .teacher-metric strong {
        display: block;
        margin-top: 6px;
        color: var(--accent);
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
    }

    .teacher-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .teacher-metric {
        min-height: 86px;
        padding: 18px;
    }

    .teacher-metric strong {
        color: var(--text);
    }

    .teacher-section {
        display: grid;
        gap: 14px;
    }

    .teacher-section-title {
        margin: 0;
        color: var(--text);
        font-size: 26px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: 0;
    }

    .teacher-session-grid,
    .teacher-form-grid {
        display: grid;
        gap: 16px;
    }

    .teacher-session-grid,
    .teacher-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .teacher-panel {
        padding: 20px;
    }

    .teacher-panel h2 {
        margin: 0 0 14px;
        color: var(--text);
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
    }

    .teacher-form {
        display: grid;
        gap: 12px;
    }

    .teacher-datetime-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .teacher-control {
        width: 100%;
        min-width: 0;
        height: 50px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--surface2);
        color: var(--text);
        padding: 0 14px;
        font-size: 14px;
        outline: none;
    }

    textarea.teacher-control {
        min-height: 132px;
        height: auto;
        padding: 13px 14px;
        resize: vertical;
    }

    .teacher-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 14%, transparent);
    }

    .teacher-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 50px;
        border: 0;
        border-radius: 12px;
        background: var(--accent);
        color: #fff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        box-shadow: 0 10px 22px color-mix(in srgb, var(--accent) 24%, transparent);
    }

    .teacher-empty {
        grid-column: 1 / -1;
        border: 1px dashed var(--border);
        background: var(--surface);
        border-radius: 16px;
        padding: 28px;
        color: var(--muted);
        text-align: center;
        font-size: 14px;
    }

    .teacher-history-filter {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
    }

    .teacher-table-card {
        overflow: hidden;
    }

    .teacher-table-wrap {
        overflow-x: auto;
    }

    .teacher-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .teacher-table th,
    .teacher-table td {
        border-bottom: 1px solid var(--border);
        padding: 14px 16px;
        text-align: left;
    }

    .teacher-table th {
        background: var(--surface2);
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .teacher-table td {
        color: var(--text2);
        font-size: 14px;
    }

    @media (max-width: 1180px) {
        .teacher-attendance-header {
            grid-template-columns: 1fr;
        }

        .teacher-attendance-actions {
            grid-template-columns: repeat(2, max-content) minmax(150px, 1fr);
        }

        .teacher-metrics,
        .teacher-session-grid,
        .teacher-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .teacher-attendance-page {
            padding: 16px;
        }

        .teacher-attendance-actions,
        .teacher-metrics,
        .teacher-session-grid,
        .teacher-form-grid,
        .teacher-datetime-grid,
        .teacher-history-filter {
            grid-template-columns: 1fr;
        }

        .teacher-action,
        .teacher-month-card {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $statusClasses = [
        'late' => 'bg-amber-100 text-amber-700 border-amber-300/50',
        'very_late' => 'bg-orange-100 text-orange-700 border-orange-300/50',
        'early_leave' => 'bg-amber-100 text-amber-700 border-amber-300/50',
        'missing_check_out' => 'bg-orange-100 text-orange-700 border-orange-300/50',
        'absent' => 'bg-red-100 text-red-700 border-red-300/50',
        'completed' => 'bg-blue-100 text-blue-700 border-blue-300/50',
        'present' => 'bg-green-100 text-green-700 border-green-300/50',
        'on_time' => 'bg-green-100 text-green-700 border-green-300/50',
        'teaching' => 'bg-emerald-100 text-emerald-700 border-emerald-300/50',
        'permission' => 'bg-violet-100 text-violet-700 border-violet-300/50',
        'cancelled' => 'bg-slate-100 text-slate-700 border-slate-300/50',
        'rescheduled' => 'bg-sky-100 text-sky-700 border-sky-300/50',
    ];
@endphp

<div class="teacher-attendance-page">
    <div class="teacher-attendance-header">
        <div class="teacher-attendance-title">
            <h1>My Teaching Attendance</h1>
            <p>Check in and out based on your assigned class schedule.</p>
        </div>
        <div class="teacher-attendance-actions">
            <a href="{{ route('teacher.attendance.scan') }}" class="teacher-action is-primary">Scan QR</a>
            <a href="{{ route('teacher.attendance.checkout') }}" class="teacher-action">Checkout</a>
            <div class="teacher-month-card">
                <span class="teacher-kicker">This Month</span>
                <strong>{{ $percentage }}%</strong>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="teacher-metrics">
        <div class="teacher-metric"><span class="teacher-kicker">Today</span><strong>{{ $todaySessions->count() }}</strong></div>
        <div class="teacher-metric"><span class="teacher-kicker">Upcoming</span><strong>{{ $upcoming->count() }}</strong></div>
        <div class="teacher-metric"><span class="teacher-kicker">Pending Corrections</span><strong>{{ $pendingCorrections }}</strong></div>
        <div class="teacher-metric"><span class="teacher-kicker">Pending Changes</span><strong>{{ $pendingChanges }}</strong></div>
    </div>

    <section class="teacher-section">
        <h2 class="teacher-section-title">Today's Classes</h2>
        <div class="teacher-session-grid">
            @forelse($todaySessions as $session)
                <article class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusClasses[$session->attendance_status] ?? 'border-[var(--border)] bg-[var(--surface2)] text-[var(--text)]' }}">
                                {{ str_replace('_', ' ', strtoupper($session->attendance_status)) }}
                            </span>
                            <h3 class="mt-3 text-lg font-semibold text-[var(--text)]">{{ $session->subject->name ?? 'Subject' }}</h3>
                            <p class="mt-1 text-sm text-[var(--muted)]">{{ $session->classGroup->name ?? ($session->classRoom->name ?? 'Class') }} · {{ $session->room_name ?? 'Room not set' }}</p>
                            <strong class="mt-2 block text-sm font-semibold text-[var(--text2)]">{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</strong>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if (!$session->check_in_time && $session->session_number > 1 && !in_array($session->attendance_status, ['cancelled', 'rescheduled', 'permission']))
                                <form method="POST" action="{{ route('teacher.attendance.check-in', $session) }}">
                                    @csrf
                                    <button class="inline-flex h-10 items-center justify-center rounded-lg bg-green-500 px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-green-600" type="submit">Auto Check In</button>
                                </form>
                            @endif
                            @if ($session->check_in_time && !$session->check_out_time && !in_array($session->attendance_status, ['cancelled', 'rescheduled', 'permission']))
                                <form method="POST" action="{{ route('teacher.attendance.check-out', $session) }}">
                                    @csrf
                                    <button class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-4 text-xs font-bold uppercase tracking-wide text-[var(--text2)] transition hover:border-[var(--accent)] hover:text-[var(--accent)]" type="submit">Check Out</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-[var(--muted)] sm:grid-cols-4">
                        <span>In: {{ $session->check_in_time?->format('H:i') ?? '-' }}</span>
                        <span>Out: {{ $session->check_out_time?->format('H:i') ?? '-' }}</span>
                        <span>Late: {{ $session->late_minutes }}m</span>
                        <span>Hours: {{ $session->actual_teaching_hours }}</span>
                    </div>
                </article>
            @empty
                <div class="teacher-empty">No teaching sessions scheduled for today.</div>
            @endforelse
        </div>
    </section>

    <div class="teacher-form-grid">
        <section class="teacher-panel">
            <h2>Correction Request</h2>
            <form method="POST" action="{{ route('teacher.attendance.corrections.store') }}" class="teacher-form">
                @csrf
                <select name="attendance_session_id" class="teacher-control">
                    <option value="">Select attendance session</option>
                    @foreach ($history as $session)
                        <option value="{{ $session->id }}">{{ $session->attendance_date?->format('M d') }} · {{ $session->subject->name ?? 'Subject' }} · {{ str_replace('_', ' ', $session->attendance_status) }}</option>
                    @endforeach
                </select>
                <select name="request_type" class="teacher-control" required>
                    <option value="missing_check_in">Missing Check-In</option>
                    <option value="missing_check_out">Missing Check-Out</option>
                    <option value="wrong_status">Wrong Attendance Status</option>
                    <option value="internet_problem">Internet Problems</option>
                    <option value="schedule_change">Schedule Changes</option>
                    <option value="other">Other</option>
                </select>
                <div class="teacher-datetime-grid">
                    <input type="datetime-local" name="requested_check_in_time" class="teacher-control">
                    <input type="datetime-local" name="requested_check_out_time" class="teacher-control">
                </div>
                <select name="requested_status" class="teacher-control">
                    <option value="">No status change</option>
                    @foreach (['present', 'on_time', 'late', 'very_late', 'completed', 'early_leave', 'permission', 'absent'] as $status)
                        <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <textarea name="reason" class="teacher-control" required placeholder="Explain the issue"></textarea>
                <button class="teacher-primary">Submit Correction</button>
            </form>
        </section>

        <section class="teacher-panel">
            <h2>Cancel / Reschedule</h2>
            <form method="POST" action="{{ route('teacher.attendance.class-change.store') }}" class="teacher-form">
                @csrf
                <select name="schedule_id" class="teacher-control" required>
                    <option value="">Select schedule</option>
                    @foreach ($todaySessions->concat($upcoming) as $session)
                        <option value="{{ $session->schedule_id }}">{{ $session->attendance_date?->format('M d') }} · {{ $session->subject->name ?? 'Subject' }} · {{ $session->scheduled_start_time?->format('H:i') }}</option>
                    @endforeach
                </select>
                <select name="request_type" class="teacher-control" required>
                    <option value="cancellation">Cancellation</option>
                    <option value="reschedule">Reschedule</option>
                    <option value="replacement">Replacement Session</option>
                </select>
                <div class="teacher-datetime-grid">
                    <input type="datetime-local" name="requested_start_time" class="teacher-control">
                    <input type="datetime-local" name="requested_end_time" class="teacher-control">
                </div>
                <input type="text" name="requested_room_name" class="teacher-control" placeholder="Requested room">
                <textarea name="reason" class="teacher-control" required placeholder="Reason for change"></textarea>
                <button class="teacher-primary">Submit Request</button>
            </form>
        </section>
    </div>

    <section class="teacher-section">
        <h2 class="teacher-section-title">Attendance History</h2>
        <form method="GET" class="teacher-history-filter teacher-table-card">
            <input type="date" name="from" value="{{ request('from') }}" class="teacher-control">
            <input type="date" name="to" value="{{ request('to') }}" class="teacher-control">
            <select name="status" class="teacher-control">
                <option value="">All statuses</option>
                @foreach (['scheduled', 'present', 'on_time', 'late', 'very_late', 'teaching', 'completed', 'early_leave', 'absent', 'permission', 'cancelled', 'rescheduled', 'missing_check_out'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <button class="teacher-action">Filter</button>
        </form>

        <div class="teacher-table-card">
            <div class="teacher-table-wrap">
                <table class="teacher-table">
                    <thead>
                        <tr>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Date</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Subject</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Schedule</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Status</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Metrics</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $session)
                            <tr class="transition hover:bg-[var(--surface2)]/70">
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm text-[var(--text2)]">{{ $session->attendance_date?->format('M d, Y') }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm font-semibold text-[var(--text)]">{{ $session->subject->name ?? '-' }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm text-[var(--text2)]">{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusClasses[$session->attendance_status] ?? 'border-[var(--border)] bg-[var(--surface2)] text-[var(--text)]' }}">{{ str_replace('_', ' ', strtoupper($session->attendance_status)) }}</span>
                                </td>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm text-[var(--muted)]">Late {{ $session->late_minutes }}m · Early {{ $session->early_leave_minutes }}m · {{ $session->actual_teaching_hours }}h</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border-b border-[var(--border)] px-4 py-10 text-center text-sm text-[var(--muted)]">No attendance history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $history->links() }}</div>
    </section>
</div>
@endsection
