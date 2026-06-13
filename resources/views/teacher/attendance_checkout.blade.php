@extends('layouts.app')

@push('styles')
<style>
    .teacher-attendance-page{display:grid;gap:22px;padding:24px}.teacher-attendance-header{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:start}.teacher-attendance-title h1{margin:0;color:var(--text);font-size:clamp(30px,3vw,42px);line-height:1.05;font-weight:800;letter-spacing:0}.teacher-attendance-title p{margin-top:8px;color:var(--muted);font-size:15px;max-width:760px}.teacher-action,.teacher-primary{display:inline-flex;align-items:center;justify-content:center;min-width:92px;height:50px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--accent);padding:0 16px;font-size:12px;font-weight:800;text-transform:uppercase;white-space:nowrap;box-shadow:var(--shadow-sm)}.teacher-primary{border:0;background:var(--accent);color:#fff;box-shadow:0 10px 22px color-mix(in srgb,var(--accent) 24%,transparent)}.teacher-session-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.teacher-session-card{border:1px solid var(--border);background:var(--surface);border-radius:16px;box-shadow:var(--shadow-sm);padding:20px}.teacher-session-card__top{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:start}.teacher-badge{display:inline-flex;width:max-content;border-radius:999px;border:1px solid color-mix(in srgb,var(--green) 28%,transparent);background:color-mix(in srgb,var(--green) 10%,transparent);color:var(--green);padding:5px 10px;font-family:var(--font-mono);font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.teacher-session-card h2{margin:12px 0 0;color:var(--text);font-size:19px;line-height:1.25;font-weight:800}.teacher-session-card p{margin:6px 0 0;color:var(--muted);font-size:14px}.teacher-session-card strong{display:block;margin-top:10px;color:var(--text2);font-size:14px}.teacher-session-meta{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:18px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted);font-size:12px}.teacher-empty{grid-column:1/-1;border:1px dashed var(--border);background:var(--surface);border-radius:16px;padding:34px;color:var(--muted);text-align:center;font-size:14px;box-shadow:var(--shadow-sm)}@media(max-width:980px){.teacher-attendance-header,.teacher-session-grid{grid-template-columns:1fr}.teacher-session-card__top{grid-template-columns:1fr}.teacher-session-meta{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.teacher-attendance-page{padding:16px}.teacher-action,.teacher-primary{width:100%}.teacher-session-meta{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="teacher-attendance-page">
    <div class="teacher-attendance-header">
        <div class="teacher-attendance-title">
            <h1>Teacher Checkout</h1>
            <p>Every session needs its own check-out, including session 2 after auto check-in.</p>
        </div>
        <a href="{{ route('teacher.attendance.scan') }}" class="teacher-action">Scan QR</a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="teacher-session-grid">
        @forelse ($sessions as $session)
            <article class="teacher-session-card">
                <div class="teacher-session-card__top">
                    <div>
                        <span class="teacher-badge">
                            {{ $session->check_in_method === 'auto_session' ? 'AUTO CHECK-IN' : 'CHECKED IN' }}
                        </span>
                        <h2>{{ $session->subject->name ?? 'Subject' }} · Session {{ $session->session_number }}</h2>
                        <p>{{ $session->classGroup->name ?? ($session->classRoom->name ?? 'Class') }} · {{ $session->room_name ?? 'Room not set' }}</p>
                        <strong>{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</strong>
                    </div>
                    <form method="POST" action="{{ route('teacher.attendance.check-out', $session) }}">
                        @csrf
                        <button class="teacher-primary" type="submit">Check Out</button>
                    </form>
                </div>
                <div class="teacher-session-meta">
                    <span>In: {{ $session->check_in_time?->format('H:i') ?? '-' }}</span>
                    <span>Method: {{ str_replace('_', ' ', $session->check_in_method ?? '-') }}</span>
                    <span>Status: {{ str_replace('_', ' ', $session->attendance_status) }}</span>
                    <span>Source: {{ $session->auto_check_in_source_session_id ? 'Session 1' : '-' }}</span>
                </div>
            </article>
        @empty
            <div class="teacher-empty">No sessions currently require check-out.</div>
        @endforelse
    </div>
</div>
@endsection
