@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="m-0 text-[32px] font-semibold leading-tight text-[var(--text)]">Teacher QR Check-In</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">Scan or paste the session 1 QR token. Session 2 uses session 1 check-in for the same subject and date.</p>
        </div>
        <a href="{{ route('teacher.attendance.checkout') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-xs font-bold uppercase tracking-wide text-[var(--accent)]">Checkout</a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(320px,420px)_1fr]">
        <form method="POST" action="{{ route('teacher.attendance.qr-check-in') }}" class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
            @csrf
            <label class="block text-xs font-bold uppercase tracking-wide text-[var(--muted)]" for="token">QR Token</label>
            <textarea id="token" name="token" required autofocus class="mt-2 min-h-36 w-full rounded-lg border border-[var(--border)] bg-[var(--surface2)] px-3 py-2 font-mono text-xs text-[var(--text)] outline-none focus:border-[var(--accent)]" placeholder="Paste scanned QR token here">{{ old('token', $prefilledToken) }}</textarea>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <button class="mt-3 inline-flex h-11 w-full items-center justify-center rounded-lg bg-[var(--accent)] px-4 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-blue-700" type="submit">Validate QR & Check In</button>
        </form>

        <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
            <h2 class="mb-3 text-xl font-semibold text-[var(--text)]">Today</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse">
                    <thead class="bg-[var(--surface2)]">
                        <tr>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Subject</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Session</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Time</th>
                            <th class="border-b border-[var(--border)] px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todaySessions as $session)
                            <tr>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm font-semibold text-[var(--text)]">{{ $session->subject->name ?? 'Subject' }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-sm text-[var(--text2)]">Session {{ $session->session_number }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3 font-mono text-xs text-[var(--muted)]">{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</td>
                                <td class="border-b border-[var(--border)] px-4 py-3 text-xs font-bold uppercase tracking-wide text-[var(--accent)]">{{ str_replace('_', ' ', $session->attendance_status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-[var(--muted)]">No sessions scheduled today.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        });
    }
</script>
@endsection
