@extends('layouts.app')

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

<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="m-0 text-[32px] font-semibold leading-tight text-[var(--text)]">My Teaching Attendance</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">Check in and out based on your assigned class schedule.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('teacher.attendance.scan') }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-[var(--accent)] px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-blue-700">Scan QR</a>
            <a href="{{ route('teacher.attendance.checkout') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-xs font-bold uppercase tracking-wide text-[var(--accent)]">Checkout</a>
            <div class="min-w-36 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
                <span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">This Month</span>
                <strong class="mt-2 block text-2xl font-semibold text-[var(--accent)]">{{ $percentage }}%</strong>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm"><span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Today</span><strong class="mt-2 block text-2xl font-semibold text-[var(--text)]">{{ $todaySessions->count() }}</strong></div>
        <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm"><span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Upcoming</span><strong class="mt-2 block text-2xl font-semibold text-[var(--text)]">{{ $upcoming->count() }}</strong></div>
        <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm"><span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Pending Corrections</span><strong class="mt-2 block text-2xl font-semibold text-[var(--text)]">{{ $pendingCorrections }}</strong></div>
        <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm"><span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Pending Changes</span><strong class="mt-2 block text-2xl font-semibold text-[var(--text)]">{{ $pendingChanges }}</strong></div>
    </div>

    <section class="grid gap-3">
        <h2 class="text-2xl font-semibold text-[var(--text)]">Today's Classes</h2>
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
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
                <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6 text-center text-sm text-[var(--muted)] shadow-sm">No teaching sessions scheduled for today.</div>
            @endforelse
        </div>
    </section>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
            <h2 class="mb-3 text-2xl font-semibold text-[var(--text)]">Correction Request</h2>
            <form method="POST" action="{{ route('teacher.attendance.corrections.store') }}" class="grid gap-3">
                @csrf
                <select name="attendance_session_id" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                    <option value="">Select attendance session</option>
                    @foreach ($history as $session)
                        <option value="{{ $session->id }}">{{ $session->attendance_date?->format('M d') }} · {{ $session->subject->name ?? 'Subject' }} · {{ str_replace('_', ' ', $session->attendance_status) }}</option>
                    @endforeach
                </select>
                <select name="request_type" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" required>
                    <option value="missing_check_in">Missing Check-In</option>
                    <option value="missing_check_out">Missing Check-Out</option>
                    <option value="wrong_status">Wrong Attendance Status</option>
                    <option value="internet_problem">Internet Problems</option>
                    <option value="schedule_change">Schedule Changes</option>
                    <option value="other">Other</option>
                </select>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input type="datetime-local" name="requested_check_in_time" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                    <input type="datetime-local" name="requested_check_out_time" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                </div>
                <select name="requested_status" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                    <option value="">No status change</option>
                    @foreach (['present', 'on_time', 'late', 'very_late', 'completed', 'early_leave', 'permission', 'absent'] as $status)
                        <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <textarea name="reason" class="min-h-28 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 py-2 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" required placeholder="Explain the issue"></textarea>
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[var(--accent)] px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-blue-700">Submit Correction</button>
            </form>
        </section>

        <section class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
            <h2 class="mb-3 text-2xl font-semibold text-[var(--text)]">Cancel / Reschedule</h2>
            <form method="POST" action="{{ route('teacher.attendance.class-change.store') }}" class="grid gap-3">
                @csrf
                <select name="schedule_id" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" required>
                    <option value="">Select schedule</option>
                    @foreach ($todaySessions->concat($upcoming) as $session)
                        <option value="{{ $session->schedule_id }}">{{ $session->attendance_date?->format('M d') }} · {{ $session->subject->name ?? 'Subject' }} · {{ $session->scheduled_start_time?->format('H:i') }}</option>
                    @endforeach
                </select>
                <select name="request_type" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" required>
                    <option value="cancellation">Cancellation</option>
                    <option value="reschedule">Reschedule</option>
                    <option value="replacement">Replacement Session</option>
                </select>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input type="datetime-local" name="requested_start_time" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                    <input type="datetime-local" name="requested_end_time" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                </div>
                <input type="text" name="requested_room_name" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" placeholder="Requested room">
                <textarea name="reason" class="min-h-28 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 py-2 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]" required placeholder="Reason for change"></textarea>
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[var(--accent)] px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-blue-700">Submit Request</button>
            </form>
        </section>
    </div>

    <section class="grid gap-3">
        <h2 class="text-2xl font-semibold text-[var(--text)]">Attendance History</h2>
        <form method="GET" class="grid grid-cols-1 gap-2 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-3 shadow-sm sm:grid-cols-4">
            <input type="date" name="from" value="{{ request('from') }}" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
            <input type="date" name="to" value="{{ request('to') }}" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
            <select name="status" class="h-10 rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
                <option value="">All statuses</option>
                @foreach (['scheduled', 'present', 'on_time', 'late', 'very_late', 'teaching', 'completed', 'early_leave', 'absent', 'permission', 'cancelled', 'rescheduled', 'missing_check_out'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <button class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-xs font-bold uppercase tracking-wide text-[var(--accent)] transition hover:border-[var(--accent)] hover:bg-[var(--surface2)]">Filter</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)] shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse">
                    <thead class="bg-[var(--surface2)]">
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
