@extends('layouts.app')

@section('content')
@php
    $date = $session->attendance_date;
    $requiresLocation = \App\Models\Setting::get('require_location', 'true') === 'true';
@endphp

<div class="teacher-qr-single">
    <header class="teacher-qr-single-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('admin.teacher-attendance', ['date' => $date?->toDateString()]) }}" style="color:var(--muted);cursor:pointer;">TEACHERS</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">QR TOKEN</span>
            </div>
            <h1>Teacher QR Token</h1>
            <p>Display this QR for the assigned teacher. The phone scan page supports check-in, checkout, teacher code verification, and location settings.</p>
        </div>
        <div class="teacher-qr-single-actions">
            <span class="teacher-qr-live"><i></i> LIVE TOKEN</span>
            <a href="{{ route('admin.teacher-attendance.scan-qr', ['date' => $date?->toDateString(), 'session_id' => $session->id]) }}">Full QR Page</a>
            <a class="is-primary" href="{{ route('admin.teacher-attendance.sessions.qr-token', $session) }}">Refresh QR</a>
        </div>
    </header>

    @if($scanUrlNeedsPublicHost)
        <div class="teacher-qr-warning">
            <strong>Phone cannot open localhost.</strong>
            <span>Set <code>TEACHER_QR_PUBLIC_URL</code> in <code>.env</code> to your LAN or public URL, for example <code>http://192.168.1.25:8080</code>, then clear config cache and refresh this QR.</span>
        </div>
    @endif

    <section class="teacher-qr-single-grid">
        <article class="teacher-qr-display">
            <div class="teacher-qr-bar">
                <div>
                    <strong>{{ $session->subject->name ?? 'Subject' }}</strong>
                    <span>{{ $session->teacher->user->name ?? 'Teacher' }} · Session {{ $session->session_number }}</span>
                </div>
                <div class="teacher-qr-clock">
                    <strong id="teacherQrClock">--:--:--</strong>
                    <span>{{ $date?->format('D, M d, Y') }}</span>
                </div>
            </div>

            <div class="teacher-qr-body">
                <div class="teacher-qr-canvas-wrap">
                    <div class="teacher-qr-corners" aria-hidden="true"></div>
                    <div class="teacher-qr-canvas">
                        <canvas id="teacherQrCanvas" width="320" height="320" aria-label="Teacher attendance QR"></canvas>
                    </div>
                </div>

                <div class="teacher-qr-token">
                    <span>GEN {{ now()->format('His') }}</span>
                    <code>{{ substr($qr['token'], -14) }}</code>
                </div>

                <div class="teacher-qr-refresh">
                    <div class="teacher-qr-timer">
                        <svg viewBox="0 0 72 72">
                            <circle cx="36" cy="36" r="28"></circle>
                            <circle id="teacherQrTimerRing" cx="36" cy="36" r="28"></circle>
                        </svg>
                        <strong id="teacherQrSeconds">{{ $qr['ttl_seconds'] }}s</strong>
                    </div>
                    <div>
                        <strong id="teacherQrRefreshLabel">Refreshes in</strong>
                        <span>Token expires every {{ $qr['ttl_seconds'] }} seconds</span>
                    </div>
                    <a href="{{ route('admin.teacher-attendance.sessions.qr-token', $session) }}">Refresh Now</a>
                </div>

                <div class="teacher-qr-progress">
                    <i id="teacherQrProgress"></i>
                </div>
            </div>
        </article>

        <aside class="teacher-qr-side">
            <section class="teacher-qr-detail">
                <h2>Session Details</h2>
                <dl>
                    <div><dt>Teacher</dt><dd>{{ $session->teacher->user->name ?? '-' }}</dd></div>
                    <div><dt>Teacher Code</dt><dd>{{ $session->teacher->teacher_code ?? '-' }}</dd></div>
                    <div><dt>Status</dt><dd>{{ str_replace('_', ' ', $session->attendance_status) }}</dd></div>
                    <div><dt>Department</dt><dd>{{ $session->teacher->department->name ?? '-' }}</dd></div>
                    <div><dt>Schedule</dt><dd>{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</dd></div>
                    <div><dt>Class</dt><dd>{{ $session->classGroup->name ?? $session->classRoom->name ?? '-' }}</dd></div>
                    <div><dt>Room</dt><dd>{{ $session->room_name ?? '-' }}</dd></div>
                    <div><dt>Location</dt><dd>{{ $requiresLocation ? 'Required' : 'Disabled' }}</dd></div>
                </dl>
            </section>

            <section class="teacher-qr-detail">
                <h2>Scan Link</h2>
                <textarea id="teacherScanUrl" readonly>{{ $teacherScanUrl }}</textarea>
                <div class="teacher-qr-copyrow">
                    <button type="button" id="copyQrPayload">Copy Link</button>
                    <a href="{{ $teacherScanUrl }}" target="_blank">Open Scan Page</a>
                </div>
            </section>

            <section class="teacher-qr-note">
                <strong>Teacher Scan Flow</strong>
                <span>Teacher chooses check-in or checkout, enters the generated teacher code, then submits. Session 1 check-in auto checks in later same-subject sessions; checkout closes the latest open session.</span>
            </section>
        </aside>
    </section>
</div>
@endsection

@push('styles')
<style>
    .teacher-qr-single{display:flex;flex-direction:column;gap:20px;padding:24px}
    .teacher-qr-single-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap}
    .teacher-qr-single-head h1{margin:4px 0 0;font-size:32px;line-height:1.08;color:var(--text);font-weight:800}
    .teacher-qr-single-head p{margin:6px 0 0;color:var(--muted);font-size:14px;max-width:780px}
    .teacher-qr-single-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .teacher-qr-single-actions a,.teacher-qr-refresh a,.teacher-qr-copyrow button,.teacher-qr-copyrow a{height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--border);background:var(--surface);padding:0 14px;color:var(--accent);font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;text-decoration:none}
    .teacher-qr-single-actions a.is-primary{background:var(--accent);border-color:var(--accent);color:#fff}
    .teacher-qr-live{height:32px;display:inline-flex;align-items:center;gap:8px;border:1px solid var(--border);border-radius:999px;background:var(--surface);padding:0 12px;color:var(--green);font-size:11px;font-weight:800}
    .teacher-qr-live i{width:8px;height:8px;border-radius:50%;background:var(--green);animation:teacherQrPulse 1.3s ease-in-out infinite}
    .teacher-qr-warning{display:flex;align-items:flex-start;gap:10px;border:1px solid rgba(245,158,11,.35);border-radius:12px;background:rgba(245,158,11,.1);padding:12px 14px;color:#b45309}
    .teacher-qr-warning strong{font-size:13px;white-space:nowrap}
    .teacher-qr-warning span{font-size:12px;line-height:1.5}
    .teacher-qr-warning code{font-family:var(--font-mono);font-size:11px;background:rgba(255,255,255,.5);border:1px solid rgba(245,158,11,.25);border-radius:6px;padding:1px 5px;color:#92400e}
    .teacher-qr-single-grid{display:grid;grid-template-columns:minmax(380px,1.2fr) minmax(320px,.8fr);gap:18px}
    .teacher-qr-display,.teacher-qr-detail,.teacher-qr-note{border:1px solid var(--border);border-radius:12px;background:var(--surface);box-shadow:var(--shadow-sm);overflow:hidden}
    .teacher-qr-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#0f766e,#2563eb);color:#fff}
    .teacher-qr-bar strong{display:block;font-size:18px;line-height:1.2}
    .teacher-qr-bar span{display:block;margin-top:4px;color:#dbeafe;font-size:12px}
    .teacher-qr-clock{text-align:right}
    .teacher-qr-clock strong{font-family:var(--font-mono);font-size:24px}
    .teacher-qr-body{display:flex;flex-direction:column;align-items:center;padding:28px 24px}
    .teacher-qr-canvas-wrap{position:relative}
    .teacher-qr-canvas{border:2px solid #e2e8f0;border-radius:18px;background:#fff;padding:14px;box-shadow:0 18px 34px rgba(15,23,42,.12)}
    .teacher-qr-corners:before,.teacher-qr-corners:after{content:"";position:absolute;inset:-12px;border-color:#2563eb;pointer-events:none}
    .teacher-qr-corners:before{border-top:3px solid #2563eb;border-left:3px solid #2563eb;width:34px;height:34px;border-radius:8px}
    .teacher-qr-corners:after{right:-12px;left:auto;border-top:3px solid #2563eb;border-right:3px solid #2563eb;width:34px;height:34px;border-radius:8px}
    .teacher-qr-token{margin-top:18px;display:flex;align-items:center;gap:8px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);padding:9px 12px;font-family:var(--font-mono);font-size:11px;color:var(--muted)}
    .teacher-qr-token code{color:var(--text);font-weight:800}
    .teacher-qr-refresh{display:flex;align-items:center;gap:16px;margin-top:18px;flex-wrap:wrap;justify-content:center}
    .teacher-qr-refresh>div:nth-child(2){display:grid;gap:3px}
    .teacher-qr-refresh>div:nth-child(2) strong{font-size:14px;color:var(--text)}
    .teacher-qr-refresh>div:nth-child(2) span{font-size:12px;color:var(--muted)}
    .teacher-qr-timer{position:relative;width:68px;height:68px;display:grid;place-items:center}
    .teacher-qr-timer svg{position:absolute;inset:0;transform:rotate(-90deg)}
    .teacher-qr-timer circle{fill:none;stroke:#e2e8f0;stroke-width:4}
    .teacher-qr-timer circle+circle{stroke:#2563eb;stroke-linecap:round;stroke-dasharray:175.93;stroke-dashoffset:0;transition:stroke-dashoffset 1s linear,stroke .2s ease}
    .teacher-qr-timer strong{font-family:var(--font-mono);font-size:13px;color:#2563eb;z-index:1}
    .teacher-qr-progress{width:100%;height:7px;border-radius:999px;background:var(--surface2);overflow:hidden;margin-top:22px}
    .teacher-qr-progress i{display:block;height:100%;width:100%;background:#2563eb;border-radius:999px;transition:width 1s linear,background .2s ease}
    .teacher-qr-side{display:grid;gap:16px}
    .teacher-qr-detail{padding:18px}
    .teacher-qr-detail h2{margin:0 0 14px;color:var(--text);font-size:18px}
    .teacher-qr-detail dl{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:0}
    .teacher-qr-detail dt{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
    .teacher-qr-detail dd{margin:5px 0 0;color:var(--text);font-size:13px;font-weight:700}
    .teacher-qr-detail textarea{width:100%;min-height:110px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);padding:10px;color:var(--text);font-family:var(--font-mono);font-size:11px;resize:vertical}
    .teacher-qr-copyrow{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .teacher-qr-note{display:grid;gap:6px;padding:16px;border-color:rgba(37,99,235,.28);background:rgba(37,99,235,.08);color:#1d4ed8}
    .teacher-qr-note strong{font-size:13px}
    .teacher-qr-note span{font-size:12px;line-height:1.5}
    @keyframes teacherQrPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.55;transform:scale(1.25)}}
    @media (max-width:1100px){.teacher-qr-single-grid{grid-template-columns:1fr}}
    @media (max-width:640px){.teacher-qr-single{padding:16px}.teacher-qr-single-head h1{font-size:26px}.teacher-qr-bar{align-items:flex-start;flex-direction:column}.teacher-qr-clock{text-align:left}.teacher-qr-detail dl{grid-template-columns:1fr}.teacher-qr-canvas canvas{width:240px!important;height:240px!important}}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const total = Number(@json($qr['ttl_seconds'])) || 60;
        let remaining = total;
        const canvas = document.getElementById('teacherQrCanvas');
        const payload = @json($teacherScanUrl);
        const seconds = document.getElementById('teacherQrSeconds');
        const progress = document.getElementById('teacherQrProgress');
        const ring = document.getElementById('teacherQrTimerRing');
        const label = document.getElementById('teacherQrRefreshLabel');
        const clock = document.getElementById('teacherQrClock');
        const circumference = 2 * Math.PI * 28;

        if (window.QRCode && canvas) {
            window.QRCode.toCanvas(canvas, payload, {
                width: 320,
                margin: 2,
                errorCorrectionLevel: 'M',
                color: { dark: '#0f172a', light: '#ffffff' },
            });
        }

        function tickClock() {
            const now = new Date();
            if (clock) clock.textContent = now.toLocaleTimeString([], { hour12:false });
        }

        function tickTimer() {
            remaining -= 1;
            const pct = Math.max(0, remaining / total);
            if (seconds) seconds.textContent = `${remaining}s`;
            if (progress) {
                progress.style.width = `${pct * 100}%`;
                progress.style.background = remaining <= 10 ? '#ef4444' : '#2563eb';
            }
            if (ring) {
                ring.style.strokeDashoffset = String(circumference - (pct * circumference));
                ring.style.stroke = remaining <= 10 ? '#ef4444' : '#2563eb';
            }
            if (label && remaining <= 10) label.textContent = 'Expiring soon';
            if (remaining <= 0) window.location.href = @json(route('admin.teacher-attendance.sessions.qr-token', $session));
        }

        const copy = document.getElementById('copyQrPayload');
        if (copy) {
            copy.addEventListener('click', async function () {
                await navigator.clipboard.writeText(payload);
                copy.textContent = 'Copied';
                setTimeout(() => copy.textContent = 'Copy Link', 1200);
            });
        }

        tickClock();
        setInterval(tickClock, 1000);
        setInterval(tickTimer, 1000);
    });
</script>
@endpush
