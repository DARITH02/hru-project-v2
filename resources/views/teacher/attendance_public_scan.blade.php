<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teacher Attendance Check-In</title>
    <style>
        :root{--accent:#2563eb;--green:#16a34a;--red:#dc2626;--amber:#d97706;--bg:#f8fafc;--surface:#fff;--border:#e5e7eb;--text:#111827;--muted:#64748b}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;padding:18px}
        .scan{width:min(440px,100%);display:grid;gap:14px}.brand{text-align:center}.brand h1{margin:0;font-size:24px;letter-spacing:-.02em}.brand p{margin:6px 0 0;color:var(--muted);font-size:13px}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:0 16px 40px rgba(15,23,42,.08);padding:18px}
        .session{display:grid;gap:10px}.badge{display:inline-flex;width:max-content;border-radius:999px;border:1px solid rgba(37,99,235,.25);background:rgba(37,99,235,.08);color:var(--accent);padding:4px 10px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
        .session h2{margin:0;font-size:20px}.session dl{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:0}.session dt{color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:800}.session dd{margin:3px 0 0;font-size:13px;font-weight:700}
        form{display:grid;gap:12px}.field{display:grid;gap:6px}.field label{font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}.field input{height:46px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;color:var(--text);padding:0 13px;font-size:16px;outline:none}.field input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(37,99,235,.12)}
        .segmented{display:grid;grid-template-columns:1fr 1fr;gap:8px}.segmented label{height:46px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}.segmented input{position:absolute;opacity:0;pointer-events:none}.segmented label:has(input:checked){border-color:var(--accent);background:rgba(37,99,235,.1);color:var(--accent);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
        .action-pill{height:46px;border:1px solid rgba(37,99,235,.25);border-radius:10px;background:rgba(37,99,235,.1);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.action-pill.is-check-out{border-color:rgba(22,163,74,.28);background:rgba(22,163,74,.1);color:var(--green)}
        button{height:46px;border:0;border-radius:10px;background:var(--accent);color:white;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}button:disabled{opacity:.55;cursor:not-allowed}.location-btn{background:#111827}
        .error{border:1px solid rgba(220,38,38,.25);background:rgba(220,38,38,.08);color:var(--red);border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700}
        .warning{border:1px solid rgba(217,119,6,.28);background:rgba(217,119,6,.08);color:var(--amber);border-radius:10px;padding:10px 12px;font-size:12px;line-height:1.45}
        .location{border:1px solid rgba(37,99,235,.2);background:rgba(37,99,235,.06);color:var(--accent);border-radius:10px;padding:9px 11px;font-size:12px;font-weight:700;line-height:1.4}
        .location.error{border-color:rgba(220,38,38,.25);background:rgba(220,38,38,.08);color:var(--red)}
        .help{color:var(--muted);font-size:12px;line-height:1.5;text-align:center}.expired{opacity:.62}
    </style>
</head>
<body>
    @php
        $session = $qr->attendanceSession;
        $isExpired = $qr->expires_at->isPast();
        $isSessionOne = (int) $session->session_number === 1;
        $attendanceAction = $isSessionOne ? 'check_in' : 'check_out';
    @endphp
    <main class="scan">
        <header class="brand">
            <h1>Teacher Attendance</h1>
            <p>Tap present to submit this QR {{ $isSessionOne ? 'check-in' : 'check-out' }}.</p>
        </header>

        <section class="card session {{ $isExpired ? 'expired' : '' }}">
            <span class="badge">Session {{ $session->session_number }}</span>
            <h2>{{ $session->subject->name ?? 'Subject' }}</h2>
            <dl>
                <div><dt>Teacher</dt><dd>{{ $session->teacher->user->name ?? 'Assigned teacher' }}</dd></div>
                <div><dt>Schedule</dt><dd>{{ $session->scheduled_start_time?->format('H:i') }} - {{ $session->scheduled_end_time?->format('H:i') }}</dd></div>
                <div><dt>Class</dt><dd>{{ $session->classGroup->name ?? $session->classRoom->name ?? '-' }}</dd></div>
                <div><dt>Room</dt><dd>{{ $session->room_name ?? '-' }}</dd></div>
            </dl>
        </section>

        @if(isset($errors) && $errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if($isExpired)
            <div class="warning">This QR token is expired. Ask the admin to refresh the QR code.</div>
        @else
            <form method="POST" action="{{ route('teacher.attendance.public-qr-check-in') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="attendance_action" value="{{ $attendanceAction }}">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="accuracy" id="accuracy">
                <div class="field">
                    <label>Attendance Status</label>
                    <div class="action-pill {{ $isSessionOne ? 'is-check-in' : 'is-check-out' }}">
                        {{ $isSessionOne ? 'Present' : 'Check Out' }}
                    </div>
                </div>
                @if($requireLocation)
                    <div id="locationStatus" class="location">Tap Enable Location to verify this phone.</div>
                    <button id="enableLocation" class="location-btn" type="button">Enable Location</button>
                @else
                    <div class="location">Location tracking is disabled in system settings.</div>
                @endif
                <button id="submitAttendance" type="submit" @disabled($requireLocation)>{{ $isSessionOne ? 'Mark Present' : 'Submit Check Out' }}</button>
            </form>
        @endif

        <p class="help">
            @if($isSessionOne)
                Session 1 QR is for check-in. Later same-subject sessions are checked in automatically.
            @else
                Session {{ $session->session_number }} QR is for check-out of this session.
            @endif
            @if($requireLocation) Check-out must use the same location as check-in. @endif
        </p>
    </main>
    <script>
        const requireLocation = @json($requireLocation);
        const locationStatus = document.getElementById('locationStatus');
        const submitAttendance = document.getElementById('submitAttendance');
        const enableLocation = document.getElementById('enableLocation');

        function requestLocation() {
            if (!navigator.geolocation) {
                if (locationStatus) {
                    locationStatus.textContent = 'This device does not support location verification.';
                    locationStatus.classList.add('error');
                }
                return;
            }

            if (locationStatus) {
                locationStatus.textContent = 'Requesting phone location...';
                locationStatus.classList.remove('error');
            }

            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('accuracy').value = position.coords.accuracy;
                if (locationStatus) {
                    locationStatus.textContent = 'Phone location verified.';
                    locationStatus.classList.remove('error');
                }
                if (submitAttendance) submitAttendance.disabled = false;
                if (enableLocation) enableLocation.style.display = 'none';
            }, function () {
                if (locationStatus) {
                    locationStatus.textContent = 'Location permission is required. Allow location access and try again.';
                    locationStatus.classList.add('error');
                }
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        }

        if (!requireLocation && submitAttendance) submitAttendance.disabled = false;
        if (enableLocation) enableLocation.addEventListener('click', requestLocation);
    </script>
@include('partials.legacy-translator')
</body>
</html>
