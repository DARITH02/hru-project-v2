<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Check-in</title>

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Kantumruy+Pro:wght@400;500;600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #020617;
    --surface: rgba(15, 23, 42, 0.75);
    --surface-soft: rgba(30, 41, 59, 0.6);
    --border: rgba(148, 163, 184, 0.15);

    --accent: #6366f1;
    --accent-soft: rgba(99, 102, 241, 0.15);

    --success: #22c55e;
    --error: #ef4444;

    --text: #f8fafc;
    --text-secondary: #cbd5e1;
    --muted: #64748b;

    --font-display: 'Syne', 'Kantumruy Pro', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
}

[data-theme="light"] {
    --bg: #f8fafc;
    --surface: rgba(255,255,255,0.9);
    --surface-soft: rgba(241,245,249,0.8);
    --border: rgba(15,23,42,0.08);

    --text: #0f172a;
    --text-secondary: #334155;
    --muted: #64748b;
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
    background: var(--bg);
    background-image:
        radial-gradient(circle at 10% 10%, rgba(99,102,241,0.08), transparent 40%);
    color: var(--text);
    font-family: var(--font-display);
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    padding:20px;
}

/* CARD */
.card-wrapper {
    width:100%;
    max-width:420px;
    border-radius:20px;
    background: rgba(255,255,255,0.02);
    border:1px solid var(--border);
    padding:1px;
}

.card {
    background: var(--surface);
    backdrop-filter: blur(18px);
    border-radius:18px;
    padding:32px;
}

/* BRAND */
.brand {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
}

.brand-left {
    display:flex;
    align-items:center;
    gap:10px;
}

.brand-dot {
    width:8px;
    height:8px;
    border-radius:50%;
    background: var(--accent);
}

.brand-name {
    font-size:14px;
    font-weight:800;
    letter-spacing:.12em;
}

/* THEME TOGGLE */
.theme-toggle {
    cursor:pointer;
    font-size:12px;
    color: var(--muted);
}

/* SUBJECT */
.subject-name-kh {
    font-size:14px;
    color: var(--accent);
    margin-bottom:4px;
}

.subject-name {
    font-size:24px;
    font-weight:800;
    margin-bottom:20px;
}

/* META */
.session-meta {
    font-family: var(--font-mono);
    font-size:11px;
    color: var(--text-secondary);
    margin-bottom:28px;
    background: var(--surface-soft);
    padding:8px 12px;
    border-radius:10px;
    border:1px solid var(--border);
}

/* FORM */
.form-group { margin-bottom:18px; }

.label {
    display:flex;
    justify-content:space-between;
    font-size:13px;
    margin-bottom:8px;
}

.label .en {
    font-family: var(--font-mono);
    font-size:10px;
    color: var(--muted);
}

/* INPUT */
.input {
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid var(--border);
    background: var(--surface-soft);
    color: var(--text);
    font-family: var(--font-mono);
}

.input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px var(--accent-soft);
}

/* BUTTON */
.btn {
    width:100%;
    padding:14px;
    border-radius:12px;
    border:none;
    background: var(--accent);
    color:white;
    cursor:pointer;
    display:flex;
    flex-direction:column;
    align-items:center;
    transition:0.2s;
}

.btn:hover { background:#4f46e5; transform:translateY(-1px); }
.btn:active { transform:scale(0.98); }

/* ALERT */
.alert {
    padding:12px;
    border-radius:12px;
    margin-bottom:16px;
    font-size:13px;
}

.alert-success { background: rgba(34,197,94,0.1); color: var(--success); }
.alert-error { background: rgba(239,68,68,0.1); color: var(--error); }

/* SUCCESS */
.success-box { text-align:center; padding:20px 0; }
.verify-text { color: var(--success); font-weight:700; }
.verify-text-en { font-size:12px; color: var(--muted); }

/* FOOTER */
.footer {
    margin-top:30px;
    text-align:center;
    border-top:1px solid var(--border);
    padding-top:16px;
}

.footer-text { font-size:12px; color: var(--muted); }
.footer-text-en { font-size:9px; letter-spacing:.1em; color: var(--muted); }
</style>

<script>
function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme");
    const next = current === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("theme", next);
}
</script>

</head>
<body>

<div class="card-wrapper">
<div class="card">

<!-- BRAND -->
<div class="brand">
    <div class="brand-left">
        <div class="brand-dot"></div>
        <div class="brand-name">ATTEND<span style="color:var(--accent)">AI</span></div>
    </div>
    <div class="theme-toggle" onclick="toggleTheme()">☀️ / 🌙</div>
</div>

<!-- SUBJECT -->
<div class="subject-name-kh">មុខវិជ្ជា</div>
<div class="subject-name">{{ $session->classRoom->subject->name }}</div>

<!-- META -->
<div class="session-meta">
    Room {{ 100 + ($session->id % 400) }} · 
    {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>

<div class="success-box">
    <div class="verify-text">វត្តមានបានបញ្ជាក់</div>
    <div class="verify-text-en">ATTENDANCE VERIFIED</div>
</div>

@else

@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<form action="{{ route('student.verify') }}" method="POST">
@csrf
<input type="hidden" name="session_id" value="{{ $session->id }}">

<div class="form-group">
    <label class="label">
        <span>លេខសម្គាល់និស្សិត</span>
        <span class="en">Student Code</span>
    </label>
    <input type="text" name="student_code" class="input" placeholder="STD-12345" required autofocus>
</div>

<button class="btn">
    <span>បញ្ជាក់វត្តមាន</span>
    <small>VERIFY ATTENDANCE</small>
</button>
</form>

@endif

<!-- FOOTER -->
<div class="footer">
    <div class="footer-text">ប្រព័ន្ធវត្តមានឌីជីថល</div>
    <div class="footer-text-en">SMART ATTENDANCE SYSTEM</div>
</div>

</div>
</div>

</body>
</html>