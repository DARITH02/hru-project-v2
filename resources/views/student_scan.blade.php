<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Check-in</title>

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Kantumruy+Pro:wght@400;500;600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #020617;
    --surface: rgba(15, 23, 42, 0.78);
    --surface-soft: rgba(30, 41, 59, 0.68);
    --border: rgba(148, 163, 184, 0.18);
    --accent: #2563eb;
    --success: #22c55e;
    --warning: #f59e0b;
    --error: #ef4444;
    --text: #f8fafc;
    --text-secondary: #cbd5e1;
    --muted: #64748b;
    --font-display: 'Syne', 'Kantumruy Pro', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
}

[data-theme="light"] {
    --bg: #f8fafc;
    --surface: rgba(255, 255, 255, 0.94);
    --surface-soft: rgba(241, 245, 249, 0.86);
    --border: rgba(15, 23, 42, 0.1);
    --text: #0f172a;
    --text-secondary: #334155;
    --muted: #64748b;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-display);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}

.card-wrapper {
    width: 100%;
    max-width: 520px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    padding: 1px;
}

.card {
    background: var(--surface);
    backdrop-filter: blur(18px);
    border-radius: 18px;
    padding: 28px;
}

.brand {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.brand-left { display: flex; align-items: center; gap: 10px; }
.brand-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); }
.brand-name { font-size: 14px; font-weight: 800; letter-spacing: .12em; }
.theme-toggle { cursor: pointer; font-size: 12px; color: var(--muted); border: 0; background: transparent; }

.subject-name-kh { font-size: 13px; color: var(--accent); margin-bottom: 4px; }
.subject-name { font-size: 24px; font-weight: 800; margin-bottom: 18px; line-height: 1.15; }

.session-meta {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-secondary);
    margin-bottom: 20px;
    background: var(--surface-soft);
    padding: 9px 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
}

.alert { padding: 12px; border-radius: 12px; margin-bottom: 16px; font-size: 13px; }
.alert-success { background: rgba(34,197,94,0.1); color: var(--success); }
.alert-error { background: rgba(239,68,68,0.1); color: var(--error); }
.alert-warning { background: rgba(245,158,11,0.12); color: var(--warning); }

.section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.section-title h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .12em; }
.section-title span { font-size: 11px; color: var(--muted); font-family: var(--font-mono); }

.student-list {
    display: grid;
    gap: 10px;
    max-height: 52vh;
    overflow-y: auto;
    padding-right: 4px;
}

.student-button {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: var(--surface-soft);
    color: var(--text);
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    text-align: left;
    transition: transform .18s ease, border-color .18s ease, background .18s ease;
}

.student-button:hover:not(:disabled) {
    transform: translateY(-1px);
    border-color: rgba(37, 99, 235, .55);
    background: rgba(37, 99, 235, .08);
}

.student-button:disabled { cursor: not-allowed; opacity: .76; }
.student-info { min-width: 0; }
.student-name { font-size: 15px; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.student-meta { margin-top: 4px; font-size: 11px; color: var(--text-secondary); font-family: var(--font-mono); }

.student-status {
    border-radius: 999px;
    padding: 6px 9px;
    font-size: 10px;
    font-family: var(--font-mono);
    font-weight: 800;
    text-transform: uppercase;
    color: var(--accent);
    background: rgba(37, 99, 235, .12);
    white-space: nowrap;
}

.student-status.done { color: var(--success); background: rgba(34, 197, 94, .12); }
.student-status.closed { color: var(--warning); background: rgba(245, 158, 11, .12); }

.footer {
    margin-top: 24px;
    text-align: center;
    border-top: 1px solid var(--border);
    padding-top: 16px;
}

.footer-text { font-size: 12px; color: var(--muted); }
.footer-text-en { font-size: 9px; letter-spacing: .1em; color: var(--muted); }
</style>

<script>
function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme");
    const next = current === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("theme", next);
}

document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme) document.documentElement.setAttribute("data-theme", savedTheme);

    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition((position) => {
        document.querySelectorAll("input[name='latitude']").forEach((input) => input.value = position.coords.latitude);
        document.querySelectorAll("input[name='longitude']").forEach((input) => input.value = position.coords.longitude);
        document.querySelectorAll("input[name='accuracy']").forEach((input) => input.value = position.coords.accuracy);
    }, () => {}, { enableHighAccuracy: true, timeout: 8000 });
});
</script>
</head>
<body>

<div class="card-wrapper">
<div class="card">

<div class="brand">
    <div class="brand-left">
        <div class="brand-dot"></div>
        <div class="brand-name">{{ config('app.name', 'HRU-ATMS') }}</div>
    </div>
    <button class="theme-toggle" type="button" onclick="toggleTheme()">Theme</button>
</div>

<div class="subject-name-kh">Subject</div>
<div class="subject-name">{{ $session->classRoom->subject->name ?? 'Attendance Session' }}</div>

<div class="session-meta">
    Room {{ $session->classRoom->room_number ?? (100 + ($session->id % 400)) }} /
    {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} -
    {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

@unless($qrToken)
<div class="alert alert-warning">QR token is missing. Please scan the latest QR code from the teacher screen.</div>
@endunless

<div class="section-title">
    <h2>Select Your Name</h2>
    <span>{{ $students->count() }} students</span>
</div>

@if($students->isEmpty())
    <div class="alert alert-warning">No students found for this class group.</div>
@else
    <div class="student-list">
        @foreach($students as $student)
            @php
                $attendance = $attendanceByStudent->get($student->id);
                $done = $attendance && in_array($attendance->status, ['present', 'late'], true);
            @endphp
            <form action="{{ route('student.verify') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <input type="hidden" name="qr_token" value="{{ $qrToken }}">
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="latitude">
                <input type="hidden" name="longitude">
                <input type="hidden" name="accuracy">

                <button class="student-button" type="submit" @disabled($done || !$qrToken)>
                    <span class="student-info">
                        <span class="student-name">{{ $student->user->name ?? 'Unknown Student' }}</span>
                        <span class="student-meta">{{ $student->student_code }}{{ $student->group?->name ? ' - '.$student->group->name : '' }}</span>
                    </span>
                    <span class="student-status {{ $done ? 'done' : (!$qrToken ? 'closed' : '') }}">
                        {{ $done ? $attendance->status : ($qrToken ? 'Check in' : 'Closed') }}
                    </span>
                </button>
            </form>
        @endforeach
    </div>
@endif

<div class="footer">
    <div class="footer-text">Digital Attendance System</div>
    <div class="footer-text-en">SMART ATTENDANCE SYSTEM</div>
</div>

</div>
</div>

@include('partials.legacy-translator')
</body>
</html>
