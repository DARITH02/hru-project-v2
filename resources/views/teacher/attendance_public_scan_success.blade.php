<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Submitted</title>
    <style>
        :root{--green:#16a34a;--bg:#f8fafc;--surface:#fff;--border:#e5e7eb;--text:#111827;--muted:#64748b}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;padding:18px}
        .card{width:min(420px,100%);background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:0 16px 40px rgba(15,23,42,.08);padding:22px;text-align:center}
        .icon{width:58px;height:58px;border-radius:50%;display:grid;place-items:center;background:rgba(22,163,74,.12);color:var(--green);font-size:28px;margin:0 auto 14px}
        h1{margin:0;font-size:24px}p{margin:8px 0 0;color:var(--muted);font-size:14px;line-height:1.5}.meta{margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:grid;gap:7px;text-align:left}.meta div{display:flex;justify-content:space-between;gap:12px;font-size:13px}.meta span{color:var(--muted)}.meta strong{text-align:right}
    </style>
</head>
<body>
    <main class="card">
        @php
            $isCheckout = ($action ?? 'check_in') === 'check_out';
        @endphp
        <div class="icon">✓</div>
        <h1>{{ $isCheckout ? 'Checkout Submitted' : 'Check-In Submitted' }}</h1>
        <p>{{ $isCheckout ? 'Your QR checkout has been recorded.' : 'Your QR check-in has been recorded.' }}</p>
        <section class="meta">
            <div><span>Subject</span><strong>{{ $session->subject->name ?? 'Subject' }}</strong></div>
            <div><span>Session</span><strong>{{ $session->session_number }}</strong></div>
            <div><span>Status</span><strong>{{ str_replace('_', ' ', $session->attendance_status) }}</strong></div>
            <div><span>Check-in</span><strong>{{ $session->check_in_time?->format('H:i') }}</strong></div>
            <div><span>Checkout</span><strong>{{ $session->check_out_time?->format('H:i') ?? '-' }}</strong></div>
        </section>
    </main>
</body>
</html>
