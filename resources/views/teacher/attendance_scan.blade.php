@extends('layouts.app')

@push('styles')
<style>
    .teacher-attendance-page{display:grid;gap:22px;padding:24px}.teacher-attendance-header{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:start}.teacher-attendance-title h1{margin:0;color:var(--text);font-size:clamp(30px,3vw,42px);line-height:1.05;font-weight:800;letter-spacing:0}.teacher-attendance-title p{margin-top:8px;color:var(--muted);font-size:15px;max-width:760px}.teacher-action{display:inline-flex;align-items:center;justify-content:center;min-width:92px;height:50px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--accent);padding:0 16px;font-size:12px;font-weight:800;text-transform:uppercase;white-space:nowrap;box-shadow:var(--shadow-sm)}.teacher-action.is-primary,.teacher-primary{border-color:var(--accent);background:var(--accent);color:#fff;box-shadow:0 10px 22px color-mix(in srgb,var(--accent) 24%,transparent)}.teacher-scan-grid{display:grid;grid-template-columns:minmax(320px,420px) minmax(0,1fr);gap:18px}.teacher-panel,.teacher-table-card{border:1px solid var(--border);background:var(--surface);border-radius:16px;box-shadow:var(--shadow-sm)}.teacher-panel{padding:20px}.teacher-panel h2{margin:0 0 14px;color:var(--text);font-size:24px;line-height:1.2;font-weight:800}.teacher-form{display:grid;gap:12px}.teacher-kicker{display:block;color:var(--muted);font-family:var(--font-mono);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.teacher-control{width:100%;min-width:0;height:50px;border:1px solid var(--border);border-radius:12px;background:var(--surface2);color:var(--text);padding:0 14px;font-size:14px;outline:none}textarea.teacher-control{min-height:190px;height:auto;padding:13px 14px;resize:vertical;font-family:var(--font-mono);font-size:12px}.teacher-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 14%,transparent)}.teacher-primary{display:inline-flex;align-items:center;justify-content:center;width:100%;height:50px;border:0;border-radius:12px;font-size:12px;font-weight:900;letter-spacing:.04em;text-transform:uppercase}.teacher-table-card{overflow:hidden}.teacher-table-wrap{overflow-x:auto}.teacher-table{width:100%;min-width:640px;border-collapse:collapse}.teacher-table th,.teacher-table td{border-bottom:1px solid var(--border);padding:14px 16px;text-align:left}.teacher-table th{background:var(--surface2);color:var(--muted);font-family:var(--font-mono);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.teacher-table td{color:var(--text2);font-size:14px}.teacher-empty{padding:34px;color:var(--muted);text-align:center;font-size:14px}@media(max-width:980px){.teacher-attendance-header,.teacher-scan-grid{grid-template-columns:1fr}}@media(max-width:720px){.teacher-attendance-page{padding:16px}.teacher-action{width:100%}}
</style>
@endpush

@section('content')
<div class="teacher-attendance-page">
    <div class="teacher-attendance-header">
        <div class="teacher-attendance-title">
            <h1>Teacher QR Check-In</h1>
            <p>Scan or paste the session 1 QR token. Session 2 uses session 1 check-in for the same subject and date.</p>
        </div>
        <a href="{{ route('teacher.attendance.checkout') }}" class="teacher-action">Checkout</a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="teacher-scan-grid">
        <form method="POST" action="{{ route('teacher.attendance.qr-check-in') }}" class="teacher-panel teacher-form">
            @csrf
            <label class="teacher-kicker" for="token">QR Token</label>
            <textarea id="token" name="token" required autofocus class="teacher-control" placeholder="Paste scanned QR token here">{{ old('token', $prefilledToken) }}</textarea>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <button class="teacher-primary" type="submit">Validate QR & Check In</button>
        </form>

        <div class="teacher-table-card">
            <div class="teacher-panel" style="border:0;border-radius:0;box-shadow:none;padding-bottom:0;">
                <h2>Today</h2>
            </div>
            <div class="teacher-table-wrap">
                <table class="teacher-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Session</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todaySessions as $session)
                            <tr>
                                <td style="font-weight:700;color:var(--text)">{{ $session->subject->name ?? 'Subject' }}</td>
                                <td>Session {{ $session->session_number }}</td>
                                <td style="font-family:var(--font-mono);font-size:12px;color:var(--muted)">{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</td>
                                <td style="font-family:var(--font-mono);font-size:11px;font-weight:800;text-transform:uppercase;color:var(--accent)">{{ str_replace('_', ' ', $session->attendance_status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="teacher-empty">No sessions scheduled today.</td></tr>
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
