@extends('layouts.app')

@section('content')
<div class="teacher-qr-workspace"
    data-teacher-scan-url="{{ $teacherScanUrl ?? '' }}"
    data-qr-ttl="{{ $qr['ttl_seconds'] ?? '' }}"
    data-next-qr-open-at="{{ !empty($nextQrOpenAt) ? \Carbon\Carbon::parse($nextQrOpenAt)->toIso8601String() : '' }}">
    <header class="teacher-qr-header">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('admin.teacher-attendance') }}" style="color:var(--muted);cursor:pointer;">TEACHERS</a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">QR SCAN</span>
            </div>
            <h1>Teacher QR Scan</h1>
            <p>Display the QR for the selected teacher session. Session 1 supports check-in; later sessions can use QR for check-out.</p>
        </div>
        <div class="teacher-qr-actions">
            <span class="teacher-qr-live"><i></i> SYSTEM LIVE</span>
            <a href="{{ route('admin.teacher-attendance', ['date' => $date->toDateString()]) }}">Live Dashboard</a>
            <a href="{{ route('admin.teacher-attendance.scan-monitor', ['date' => $date->toDateString()]) }}">Scan Monitor</a>
            @if($selectedSession)
                <button type="button" id="refreshQrButton" data-url="{{ route('admin.teacher-attendance.sessions.qr-token', $selectedSession) }}">Refresh QR</button>
            @endif
        </div>
    </header>

    <section class="teacher-qr-filter">
        <form method="GET" action="{{ route('admin.teacher-attendance.scan-qr') }}" data-teacher-qr-filter>
            <label>
                <span>Date</span>
                <input type="date" name="date" value="{{ $date->toDateString() }}">
            </label>
            <label>
                <span>Teacher</span>
                <select name="teacher_id">
                    <option value="">All teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($selectedTeacherId === $teacher->id)>
                            {{ $teacher->user->name ?? 'Teacher' }}
                            @if($teacher->department)
                                · {{ $teacher->department->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Teacher Session</span>
                <select name="session_id">
                    <option value="" @selected(!$selectedSession)>Auto select QR session</option>
                    @forelse($sessions as $session)
                        <option value="{{ $session->id }}" @selected($selectedSession?->id === $session->id)>
                            {{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}
                            · {{ $session->teacher->user->name ?? 'Teacher' }}
                            · {{ $session->subject->name ?? 'Subject' }}
                        </option>
                    @empty
                        <option value="">No teacher sessions for this date</option>
                    @endforelse
                </select>
            </label>
            <button type="submit">Load QR</button>
        </form>
    </section>

    @if($selectedSession && $qr)
        @if($scanUrlNeedsPublicHost)
            <div class="teacher-qr-warning">
                <strong>Phone cannot open localhost.</strong>
                <span>Set <code>TEACHER_QR_PUBLIC_URL</code> in <code>.env</code> to your LAN or public URL, for example <code>http://192.168.1.25:8000</code>, then clear config cache and refresh this QR.</span>
            </div>
        @endif

        <section class="teacher-qr-grid">
            <article class="teacher-qr-panel">
                <div class="teacher-qr-sessionbar">
                    <div>
                        <strong>{{ $selectedSession->subject->name ?? 'Subject' }}</strong>
                        <span>{{ $selectedSession->teacher->user->name ?? 'Teacher' }} · Session {{ $selectedSession->session_number }}</span>
                    </div>
                    <div class="teacher-qr-clock">
                        <strong id="teacherQrClock">--:--:--</strong>
                        <span>{{ $date->format('D, M d, Y') }}</span>
                    </div>
                </div>

                <div class="teacher-qr-body">
                    <div class="teacher-qr-canvas-wrap">
                        <div class="teacher-qr-corners" aria-hidden="true"></div>
                        <div class="teacher-qr-canvas">
                            <canvas id="teacherQrCanvas" width="300" height="300" aria-label="Teacher attendance QR"></canvas>
                        </div>
                    </div>

                    <div class="teacher-qr-token">
                        <span>GEN {{ now()->format('His') }}</span>
                        <code id="teacherQrTokenCode">{{ substr($qr['token'], -14) }}</code>
                    </div>

                    <div class="teacher-qr-refresh">
                        <div class="teacher-qr-timer">
                            <svg viewBox="0 0 72 72">
                                <circle cx="36" cy="36" r="28"></circle>
                                <circle id="teacherQrTimerRing" cx="36" cy="36" r="28"></circle>
                            </svg>
                            <strong id="teacherQrSeconds">60s</strong>
                        </div>
                        <div>
                            <strong id="teacherQrRefreshLabel">Valid for check-in and check-out</strong>
                            <span>QR stays active until this class checkout window closes</span>
                        </div>
                        <button type="button" id="refreshQrInline" data-url="{{ route('admin.teacher-attendance.sessions.qr-token', $selectedSession) }}">Refresh Now</button>
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
                        <div><dt>Teacher</dt><dd>{{ $selectedSession->teacher->user->name ?? '-' }}</dd></div>
                        <div><dt>Teacher Code</dt><dd>{{ $selectedSession->teacher->teacher_code ?? '-' }}</dd></div>
                        <div><dt>Department</dt><dd>{{ $selectedSession->teacher->department->name ?? '-' }}</dd></div>
                        <div><dt>Schedule</dt><dd>{{ $selectedSession->scheduled_start_time?->format('H:i') }} - {{ $selectedSession->scheduled_end_time?->format('H:i') }}</dd></div>
                        <div><dt>Class</dt><dd>{{ $selectedSession->classGroup->name ?? $selectedSession->classRoom->name ?? '-' }}</dd></div>
                        <div><dt>Room</dt><dd>{{ $selectedSession->room_name ?? '-' }}</dd></div>
                        <div><dt>Status</dt><dd>{{ str_replace('_', ' ', $selectedSession->attendance_status) }}</dd></div>
                    </dl>
                </section>

                <section class="teacher-qr-detail">
                    <h2>Scan Link</h2>
                    <textarea id="teacherScanUrl" readonly>{{ $teacherScanUrl }}</textarea>
                    <div class="teacher-qr-copyrow">
                        <button type="button" id="copyQrPayload">Copy Link</button>
                        <a id="openScanPage" href="{{ $teacherScanUrl }}" target="_blank">Open Scan Page</a>
                    </div>
                </section>

                <section class="teacher-qr-note">
                    <strong>Teacher Code Form</strong>
                    <span>The QR opens 30 minutes before class. Teachers check in with session 1, then use the selected session QR for check-out when class ends.</span>
                </section>
            </aside>
        </section>
    @else
        <div class="teacher-qr-empty">
            {{ $selectedUnavailableReason ?? ('No QR is live right now for ' . $date->format('M d, Y') . '.') }}
            @if(!empty($nextQrOpenAt))
                Next QR opens at {{ \Carbon\Carbon::parse($nextQrOpenAt)->format('H:i') }}.
            @endif
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .teacher-qr-workspace{display:flex;flex-direction:column;gap:20px;padding:24px}
    .teacher-qr-workspace.is-loading{opacity:.58;pointer-events:none}
    .teacher-qr-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap}
    .teacher-qr-header h1{margin:4px 0 0;font-size:32px;line-height:1.08;color:var(--text);font-weight:800}
    .teacher-qr-header p{margin:6px 0 0;color:var(--muted);font-size:14px;max-width:740px}
    .teacher-qr-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .teacher-qr-actions a,.teacher-qr-actions button,.teacher-qr-filter button,.teacher-qr-refresh button,.teacher-qr-copyrow button,.teacher-qr-copyrow a{height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--border);background:var(--surface);padding:0 14px;color:var(--accent);font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;text-decoration:none;cursor:pointer}
    .teacher-qr-actions button,.teacher-qr-filter button{background:var(--accent);border-color:var(--accent);color:#fff}
    .teacher-qr-live{height:32px;display:inline-flex;align-items:center;gap:8px;border:1px solid var(--border);border-radius:999px;background:var(--surface);padding:0 12px;color:var(--green);font-size:11px;font-weight:800}
    .teacher-qr-live i{width:8px;height:8px;border-radius:50%;background:var(--green);animation:teacherQrPulse 1.3s ease-in-out infinite}
    .teacher-qr-filter{border:1px solid var(--border);border-radius:12px;background:var(--surface);padding:14px;box-shadow:var(--shadow-sm)}
    .teacher-qr-filter form{display:grid;grid-template-columns:180px minmax(220px,.75fr) minmax(260px,1fr) auto;gap:12px;align-items:end}
    .teacher-qr-filter label{display:grid;gap:6px}
    .teacher-qr-filter span,.teacher-qr-detail dt{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
    .teacher-qr-filter input,.teacher-qr-filter select{height:40px;border:1px solid var(--border);border-radius:8px;background:var(--surface2);padding:0 12px;color:var(--text);font-size:13px;outline:none}
    .teacher-qr-grid{display:grid;grid-template-columns:minmax(360px,1.45fr) minmax(280px,.75fr);gap:18px}
    .teacher-qr-panel,.teacher-qr-detail,.teacher-qr-note,.teacher-qr-empty{border:1px solid var(--border);border-radius:12px;background:var(--surface);box-shadow:var(--shadow-sm);overflow:hidden}
    .teacher-qr-warning{display:flex;align-items:flex-start;gap:10px;border:1px solid rgba(245,158,11,.35);border-radius:12px;background:rgba(245,158,11,.1);padding:12px 14px;color:#b45309}
    .teacher-qr-warning strong{font-size:13px;white-space:nowrap}
    .teacher-qr-warning span{font-size:12px;line-height:1.5}
    .teacher-qr-warning code{font-family:var(--font-mono);font-size:11px;background:rgba(255,255,255,.5);border:1px solid rgba(245,158,11,.25);border-radius:6px;padding:1px 5px;color:#92400e}
    .teacher-qr-sessionbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff}
    .teacher-qr-sessionbar strong{display:block;font-size:18px;line-height:1.2}
    .teacher-qr-sessionbar span{display:block;margin-top:4px;color:#bfdbfe;font-size:12px}
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
    .teacher-qr-detail dd{margin:5px 0 0;color:var(--text);font-size:13px;font-weight:700}
    .teacher-qr-detail textarea{width:100%;min-height:110px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);padding:10px;color:var(--text);font-family:var(--font-mono);font-size:11px;resize:vertical}
    .teacher-qr-copyrow{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .teacher-qr-note{display:grid;gap:6px;padding:16px;border-color:rgba(37,99,235,.28);background:rgba(37,99,235,.08);color:#1d4ed8}
    .teacher-qr-note strong{font-size:13px}
    .teacher-qr-note span{font-size:12px;line-height:1.5}
    .teacher-qr-empty{padding:36px;text-align:center;color:var(--muted);font-size:14px}
    @keyframes teacherQrPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.55;transform:scale(1.25)}}
    @media (max-width:1100px){.teacher-qr-grid{grid-template-columns:1fr}.teacher-qr-filter form{grid-template-columns:1fr}}
    @media (max-width:640px){.teacher-qr-workspace{padding:16px}.teacher-qr-header h1{font-size:26px}.teacher-qr-sessionbar{align-items:flex-start;flex-direction:column}.teacher-qr-clock{text-align:left}.teacher-qr-detail dl{grid-template-columns:1fr}.teacher-qr-canvas canvas{width:240px!important;height:240px!important}}
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function clearTeacherQrTimers() {
            if (!window.teacherQrTimers) return;
            window.teacherQrTimers.forEach(clearInterval);
            window.teacherQrTimers = [];
        }

        function formatDuration(value) {
            value = Math.max(0, Number(value) || 0);
            const hours = Math.floor(value / 3600);
            const minutes = Math.floor((value % 3600) / 60);
            const secondsValue = value % 60;

            if (hours > 0) return `${hours}h ${String(minutes).padStart(2, '0')}m`;
            if (minutes > 0) return `${minutes}m ${String(secondsValue).padStart(2, '0')}s`;
            return `${secondsValue}s`;
        }

        async function loadTeacherQrUrl(url, replaceHistory = true) {
            const workspace = document.querySelector('.teacher-qr-workspace');
            if (workspace) workspace.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const html = await response.text();
                if (!response.ok) throw new Error('Unable to load QR data.');

                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextWorkspace = doc.querySelector('.teacher-qr-workspace');
                if (!nextWorkspace || !workspace) throw new Error('QR content was not found.');

                workspace.replaceWith(nextWorkspace);
                if (replaceHistory) window.history.replaceState({}, '', url);
                window.initTeacherQrWorkspace(nextWorkspace);
            } catch (error) {
                if (workspace) workspace.classList.remove('is-loading');
                alert(error.message || 'Unable to load QR data.');
            }
        }

        window.initTeacherQrWorkspace = function (workspace = document.querySelector('.teacher-qr-workspace')) {
            clearTeacherQrTimers();
            if (!workspace) return;

            const form = workspace.querySelector('[data-teacher-qr-filter]');
            if (form) {
                let resetSessionOnSubmit = false;

                const requestForm = function (event) {
                    event.preventDefault();
                    const submitter = event.submitter;
                    const url = new URL(form.action, window.location.origin);
                    const formData = new FormData(form);
                    for (const [key, value] of formData.entries()) {
                        if (resetSessionOnSubmit && key === 'session_id') continue;
                        if (value !== '') url.searchParams.set(key, value);
                    }
                    resetSessionOnSubmit = false;
                    if (submitter?.name && submitter.value) {
                        url.searchParams.set(submitter.name, submitter.value);
                    }
                    loadTeacherQrUrl(url.toString());
                };

                form.addEventListener('submit', requestForm);
                form.querySelectorAll('select,input[type="date"]').forEach(control => {
                    control.addEventListener('change', function (event) {
                        if (['teacher_id', 'date'].includes(event.currentTarget.name)) {
                            resetSessionOnSubmit = true;
                            const sessionSelect = form.querySelector('[name="session_id"]');
                            if (sessionSelect) sessionSelect.value = '';
                        }
                        form.requestSubmit();
                    });
                });
            }

            const nextQrOpenAt = workspace.dataset.nextQrOpenAt;
            const canvas = workspace.querySelector('#teacherQrCanvas');
            if (!canvas) {
                if (nextQrOpenAt) {
                    const delay = Math.max(1000, new Date(nextQrOpenAt).getTime() - Date.now() + 1000);
                    window.teacherQrTimers = [setTimeout(() => loadTeacherQrUrl(window.location.href, false), Math.min(delay, 2147483647))];
                }
                return;
            }

            let total = Math.max(1, Number(workspace.dataset.qrTtl) || 60);
            let remaining = total;
            let payload = workspace.dataset.teacherScanUrl || '';
            const seconds = workspace.querySelector('#teacherQrSeconds');
            const progress = workspace.querySelector('#teacherQrProgress');
            const ring = workspace.querySelector('#teacherQrTimerRing');
            const label = workspace.querySelector('#teacherQrRefreshLabel');
            const clock = workspace.querySelector('#teacherQrClock');
            const tokenCode = workspace.querySelector('#teacherQrTokenCode');
            const scanUrl = workspace.querySelector('#teacherScanUrl');
            const openScanPage = workspace.querySelector('#openScanPage');
            const refreshButtons = [workspace.querySelector('#refreshQrButton'), workspace.querySelector('#refreshQrInline')].filter(Boolean);
            const circumference = 2 * Math.PI * 28;

            function drawQr() {
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (!window.QRCode) {
                    if (ctx) {
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.fillStyle = '#ef4444';
                        ctx.font = '700 15px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('QR library not loaded', canvas.width / 2, canvas.height / 2 - 8);
                        ctx.fillStyle = '#64748b';
                        ctx.font = '12px sans-serif';
                        ctx.fillText('Refresh the page after rebuilding assets.', canvas.width / 2, canvas.height / 2 + 16);
                    }
                    return;
                }

                Promise.resolve(window.QRCode.toCanvas(canvas, payload, {
                    width: 300,
                    margin: 2,
                    errorCorrectionLevel: 'M',
                    color: { dark: '#0f172a', light: '#ffffff' },
                })).catch(function () {
                    if (!ctx) return;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#ef4444';
                    ctx.font = '700 15px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('Unable to draw QR', canvas.width / 2, canvas.height / 2);
                });
            }

            drawQr();

            function tickClock() {
                const now = new Date();
                if (clock) clock.textContent = now.toLocaleTimeString([], { hour12:false });
            }

            function tickTimer() {
                remaining -= 1;
                const pct = Math.max(0, remaining / total);
                if (seconds) seconds.textContent = formatDuration(remaining);
                if (progress) {
                    progress.style.width = `${pct * 100}%`;
                    progress.style.background = remaining <= 10 ? '#ef4444' : '#2563eb';
                }
                if (ring) {
                    ring.style.strokeDashoffset = String(circumference - (pct * circumference));
                    ring.style.stroke = remaining <= 10 ? '#ef4444' : '#2563eb';
                }
                if (label && remaining <= 10) label.textContent = 'Expiring soon';
                if (remaining <= 0) {
                    if (label) label.textContent = 'QR window closed';
                    refreshButtons.forEach(btn => btn.disabled = true);
                }
            }

            async function refreshQr() {
                const button = refreshButtons[0];
                const url = button?.dataset.url;
                if (!url || refreshQr.busy) return;

                refreshQr.busy = true;
                refreshButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.dataset.originalText ??= btn.textContent;
                    btn.textContent = 'Refreshing...';
                });

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Unable to refresh QR.');

                    payload = data.teacherScanUrl;
                    remaining = Math.max(1, Number(data.qr.ttl_seconds) || total);
                    total = remaining;
                    if (tokenCode) tokenCode.textContent = String(data.qr.token).slice(-14);
                    if (scanUrl) scanUrl.value = payload;
                    if (openScanPage) openScanPage.href = payload;
                    if (label) label.textContent = 'Valid for check-in and check-out';
                    if (seconds) seconds.textContent = formatDuration(remaining);
                    if (progress) {
                        progress.style.width = '100%';
                        progress.style.background = '#2563eb';
                    }
                    if (ring) {
                        ring.style.strokeDashoffset = '0';
                        ring.style.stroke = '#2563eb';
                    }
                    drawQr();
                } catch (error) {
                    if (label) label.textContent = error.message || 'Refresh failed';
                } finally {
                    refreshButtons.forEach(btn => {
                        btn.disabled = false;
                        btn.textContent = btn.dataset.originalText || 'Refresh QR';
                    });
                    refreshQr.busy = false;
                }
            }

            const copy = workspace.querySelector('#copyQrPayload');
            if (copy) {
                copy.addEventListener('click', async function () {
                    await navigator.clipboard.writeText(payload);
                    copy.textContent = 'Copied';
                    setTimeout(() => copy.textContent = 'Copy Link', 1200);
                });
            }

            refreshButtons.forEach(btn => btn.addEventListener('click', refreshQr));

            tickClock();
            if (seconds) seconds.textContent = formatDuration(remaining);
            window.teacherQrTimers = [
                setInterval(tickClock, 1000),
                setInterval(tickTimer, 1000),
            ];
        };

        document.addEventListener('DOMContentLoaded', function () {
            window.initTeacherQrWorkspace();
        });
    })();
</script>
@endpush
