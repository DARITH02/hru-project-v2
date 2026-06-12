@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="m-0 text-[32px] font-semibold leading-tight text-[var(--text)]">Teacher Checkout</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">Every session needs its own check-out, including session 2 after auto check-in.</p>
        </div>
        <a href="{{ route('teacher.attendance.scan') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-xs font-bold uppercase tracking-wide text-[var(--accent)]">Scan QR</a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        @forelse ($sessions as $session)
            <article class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="inline-flex rounded-full border border-emerald-300/50 bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">
                            {{ $session->check_in_method === 'auto_session' ? 'AUTO CHECK-IN' : 'CHECKED IN' }}
                        </span>
                        <h2 class="mt-3 text-lg font-semibold text-[var(--text)]">{{ $session->subject->name ?? 'Subject' }} · Session {{ $session->session_number }}</h2>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ $session->classGroup->name ?? ($session->classRoom->name ?? 'Class') }} · {{ $session->room_name ?? 'Room not set' }}</p>
                        <strong class="mt-2 block text-sm font-semibold text-[var(--text2)]">{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</strong>
                    </div>
                    <form method="POST" action="{{ route('teacher.attendance.check-out', $session) }}">
                        @csrf
                        <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[var(--accent)] px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-blue-700" type="submit">Check Out</button>
                    </form>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-[var(--muted)] sm:grid-cols-4">
                    <span>In: {{ $session->check_in_time?->format('H:i') ?? '-' }}</span>
                    <span>Method: {{ str_replace('_', ' ', $session->check_in_method ?? '-') }}</span>
                    <span>Status: {{ str_replace('_', ' ', $session->attendance_status) }}</span>
                    <span>Source: {{ $session->auto_check_in_source_session_id ? 'Session 1' : '-' }}</span>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6 text-center text-sm text-[var(--muted)] shadow-sm xl:col-span-2">No sessions currently require check-out.</div>
        @endforelse
    </div>
</div>
@endsection
