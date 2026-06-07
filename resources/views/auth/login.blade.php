<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login </title>
    @vite(['resources/css/app.css'])
    <link rel="icon"
        href="https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png"
        type="image/png" sizes="32x32" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <style>
        /* ─── Reset ──────────────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ─── Dark theme (default) ───────────────────────────── */
        :root {
            --bg: #0D0D0F;
            --surface: #141418;
            --surface2: #0D0D0F;
            --border: rgba(255, 255, 255, 0.07);
            --border-hover: rgba(255, 255, 255, 0.13);
            --border-focus: rgba(99, 59, 246, 0.65);
            --text: #ffffff;
            --text-sub: rgba(255, 255, 255, 0.38);
            --text-muted: rgba(255, 255, 255, 0.22);
            --label: rgba(255, 255, 255, 0.30);
            --accent: #633BF6;
            --teal: #16C4A7;
            --icon: rgba(255, 255, 255, 0.30);
            --placeholder: rgba(255, 255, 255, 0.20);
            --error: #F09595;
            --error-bg: rgba(226, 75, 74, 0.10);
            --error-border: rgba(226, 75, 74, 0.28);
            --chip-bg: rgba(255, 255, 255, 0.04);
            --chip-border: rgba(255, 255, 255, 0.07);
            --chip-text: rgba(255, 255, 255, 0.38);
            --top-line: linear-gradient(90deg, transparent, rgba(99, 59, 246, 0.65) 40%, rgba(22, 196, 167, 0.45) 70%, transparent);
            --radial1: rgba(99, 59, 246, 0.17);
            --radial2: rgba(22, 196, 167, 0.09);
            --toggle-bg: rgba(255, 255, 255, 0.06);
            --toggle-border: rgba(255, 255, 255, 0.10);
            --toggle-color: rgba(255, 255, 255, 0.45);
            --divider: rgba(255, 255, 255, 0.07);
            --divider-text: rgba(255, 255, 255, 0.20);
        }

        /* ─── Light theme ────────────────────────────────────── */
        html.light {
            --bg: #F0EEF8;
            --surface: #ffffff;
            --surface2: #F7F5FF;
            --border: rgba(99, 59, 246, 0.11);
            --border-hover: rgba(99, 59, 246, 0.22);
            --border-focus: #633BF6;
            --text: #1a1528;
            --text-sub: #6b6385;
            --text-muted: #a49dbf;
            --label: #8e87ad;
            --accent: #633BF6;
            --teal: #0FA88F;
            --icon: #9f99c0;
            --placeholder: #c7c2dc;
            --error: #b03030;
            --error-bg: rgba(176, 48, 48, 0.06);
            --error-border: rgba(176, 48, 48, 0.22);
            --chip-bg: rgba(99, 59, 246, 0.05);
            --chip-border: rgba(99, 59, 246, 0.10);
            --chip-text: #9991bf;
            --top-line: linear-gradient(90deg, transparent, rgba(99, 59, 246, 0.45) 40%, rgba(15, 168, 143, 0.35) 70%, transparent);
            --radial1: rgba(99, 59, 246, 0.08);
            --radial2: rgba(15, 168, 143, 0.06);
            --toggle-bg: rgba(99, 59, 246, 0.06);
            --toggle-border: rgba(99, 59, 246, 0.14);
            --toggle-color: #7a71c0;
            --divider: rgba(99, 59, 246, 0.10);
            --divider-text: #b0abcc;
        }

        /* ─── Base ───────────────────────────────────────────── */
        html,
        body {
            min-height: 100vh;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 65% 55% at 15% 8%, var(--radial1) 0%, transparent 60%),
                radial-gradient(ellipse 45% 55% at 85% 92%, var(--radial2) 0%, transparent 55%);
            padding: 2rem 1rem;
            font-family: 'DM Sans', sans-serif;
            transition: background-color .35s;
        }

        /* ─── Card ───────────────────────────────────────────── */
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 36px 34px 32px;
            position: relative;
            overflow: hidden;
            transition: background .35s, border-color .35s;
            animation: fadeUp .45s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* shimmer top border */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--top-line);
        }

        /* ─── Top row ─────────────────────────────────────────── */
        .top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 25px;
            letter-spacing: .20em;
            color: blue;
            transition: color .35s;
        }

        .brand-text span {
            color: var(--teal);
        }

        .theme-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-border);
            color: var(--toggle-color);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
            flex-shrink: 0;
        }

        .theme-btn:hover {
            opacity: .75;
        }

        /* ─── Heading ─────────────────────────────────────────── */
        .headline {
            font-family: 'Syne', sans-serif;
            font-size: 21px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
            margin-bottom: 5px;
            transition: color .35s;
        }

        .subline {
            font-size: 13px;
            font-weight: 300;
            color: var(--text-sub);
            margin-bottom: 22px;
            transition: color .35s;
        }

        /* ─── Feature chips ───────────────────────────────────── */
        .chips {
            display: flex;
            gap: 8px;
            margin-bottom: 22px;
        }

        .chip {
            flex: 1;
            background: var(--chip-bg);
            border: 1px solid var(--chip-border);
            border-radius: 8px;
            padding: 7px 9px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: border-color .2s;
        }

        .chip:hover {
            border-color: var(--border-hover);
        }

        .chip-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .chip span {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .04em;
            color: var(--chip-text);
        }

        /* ─── Error banner ────────────────────────────────────── */
        .alert {
            border-radius: 10px;
            padding: 10px 14px;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: .02em;
            margin-bottom: 18px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert.error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error);
        }

        .alert .a-item {
            margin-bottom: 2px;
        }

        .alert .a-item:last-child {
            margin-bottom: 0;
        }

        /* ─── Field ───────────────────────────────────────────── */
        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: .10em;
            text-transform: uppercase;
            color: var(--label);
            margin-bottom: 7px;
            transition: color .35s;
        }

        /* ─── Input wrapper ───────────────────────────────────── */
        .inp-wrap {
            position: relative;
        }

        .inp-wrap .ico {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--icon);
            display: flex;
            align-items: center;
            transition: color .35s;
        }

        .inp-wrap input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 11px;
            color: var(--text);
            font-size: 13.5px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .35s, color .35s;
        }

        .inp-wrap input::placeholder {
            color: var(--placeholder);
        }

        .inp-wrap input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(99, 59, 246, .11);
        }

        .inp-wrap input.has-toggle {
            padding-right: 38px;
        }

        /* validation states */
        .inp-wrap input.invalid {
            border-color: var(--error) !important;
            box-shadow: 0 0 0 3px rgba(226, 75, 74, .09) !important;
        }

        .inp-wrap input.valid {
            border-color: var(--teal) !important;
            box-shadow: 0 0 0 3px rgba(22, 196, 167, .08) !important;
        }

        /* ─── Eye toggle ──────────────────────────────────────── */
        .eye-btn {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--icon);
            display: flex;
            align-items: center;
            padding: 3px;
            transition: color .2s;
        }

        .eye-btn:hover {
            color: var(--text);
        }

        /* ─── Field hint ──────────────────────────────────────── */
        .hint {
            font-size: 10.5px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .03em;
            margin-top: 5px;
            color: var(--error);
            display: none;
        }

        .hint.show {
            display: block;
        }

        /* ─── Forgot password row ─────────────────────────────── */
        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -6px 0 18px;
        }

        .forgot-row a {
            font-size: 11.5px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .04em;
            color: var(--label);
            text-decoration: none;
            transition: color .2s;
        }

        .forgot-row a:hover {
            color: var(--teal);
        }

        /* ─── Demo credentials ───────────────────────────────── */
        .demo-account {
            display: grid;
            gap: 10px;
            margin: -2px 0 18px;
            padding: 12px;
            background: var(--chip-bg);
            border: 1px solid var(--chip-border);
            border-radius: 12px;
        }

        .demo-account__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .demo-account__label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--label);
        }

        .demo-account__button {
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface2);
            color: var(--teal);
            cursor: pointer;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: .04em;
            padding: 7px 9px;
            transition: border-color .2s, color .2s, background .2s;
            width: 100%;
        }

        .demo-account__button:hover {
            border-color: var(--border-hover);
            color: var(--text);
        }

        .demo-account__credentials {
            display: grid;
            gap: 5px;
            color: var(--text-sub);
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            line-height: 1.5;
            min-width: 0;
        }

        .demo-account__credentials span {
            color: var(--text);
            word-break: break-word;
        }

        /* ─── Submit button ───────────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            background: linear-gradient(135deg, #633BF6 0%, #4A2ECE 50%, #16C4A7 100%);
            border: none;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            letter-spacing: .02em;
            transition: opacity .2s, transform .15s;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, .08) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-submit:hover {
            opacity: .91;
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ─── Divider ─────────────────────────────────────────── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
        }

        .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--divider);
        }

        .divider span {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .06em;
            color: var(--divider-text);
            white-space: nowrap;
        }

        /* ─── SSO buttons ─────────────────────────────────────── */
        .sso-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 4px;
        }

        .sso-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 11px;
            background: var(--chip-bg);
            border: 1px solid var(--chip-border);
            color: var(--text-sub);
            font-size: 12.5px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
            cursor: pointer;
            text-decoration: none;
            transition: border-color .2s, background .2s, color .2s;
        }

        .sso-btn:hover {
            border-color: var(--border-hover);
            color: var(--text);
            background: var(--chip-bg);
        }

        /* ─── Footer ──────────────────────────────────────────── */
        .card-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
            color: var(--text-sub);
            transition: color .35s;
        }

        .card-footer a {
            color: var(--teal);
            text-decoration: none;
            font-weight: 500;
            transition: opacity .15s;
        }

        .card-footer a:hover {
            opacity: .72;
        }

        /* ─── Responsive ──────────────────────────────────────── */
        @media (max-width: 480px) {
            .card {
                padding: 28px 22px 26px;
            }

            .chips {
                flex-wrap: wrap;
            }

            .sso-row {
                grid-template-columns: 1fr;
            }
        }

        body {
            /* background: url("https://www.hru.edu.kh/wp-content/uploads/2023/08/350773696_210061804722310_6900832573841793976_n-745x400.jpg"); */
            background: url("https://image.freshnewsasia.com/2020/id-025/fn-2020-12-26-11-31-31-0.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

        }
    </style>

</head>

<body>

    <div class="card">

        {{-- ── Top row: brand + theme toggle ──────────────────────── --}}
        <div class="top-row">
            <div class="brand">
                <div class="brand-mark">
                    <img src="https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png"
                        alt="Logo">
                </div>
                <div class="brand-text">HRU <span>ATS</span></div>
            </div>

            <button class="theme-btn" id="themeBtn" type="button" title="Toggle light / dark">
                {{-- Moon icon (dark mode) --}}
                <svg id="ico-moon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                {{-- Sun icon (light mode) --}}
                <svg id="ico-sun" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    style="display:none;">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" />
                    <line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>
            </button>
        </div>

        {{-- ── Heading ──────────────────────────────────────────────── --}}
        <p class="headline">Welcome back</p>
        <p class="subline">Sign in to your workspace</p>

        {{-- ── Feature chips ────────────────────────────────────────── --}}
        <div class="chips">
            <div class="chip">
                <div class="chip-dot" style="background:#16C4A7;"></div>
                <span>Live tracking</span>
            </div>
            <div class="chip">
                <div class="chip-dot" style="background:#633BF6;"></div>
                <span>AI insights</span>
            </div>
            <div class="chip">
                <div class="chip-dot" style="background:#F09595;"></div>
                <span>Smart alerts</span>
            </div>
        </div>

        {{-- ── Laravel server-side error banner ───────────────────── --}}
        @if ($errors->any())
            <div class="alert error show">
                @foreach ($errors->all() as $err)
                    <div class="a-item">• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- Client-side validation banner (hidden by default) --}}
        <div class="alert error" id="jsAlert"></div>

        {{-- ── Form ─────────────────────────────────────────────────── --}}
        <form action="{{ route('login.post') }}" method="POST" id="loginForm" novalidate>
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email">Email address</label>
                <div class="inp-wrap">
                    <span class="ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="M2 7l10 7 10-7" />
                        </svg>
                    </span>
                    <input type="email" id="email" name="email" placeholder="you@company.com"
                        value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div class="hint" id="hint-email">Enter a valid email address</div>
            </div>

            {{-- Password --}}
            <div class="field">
                <label for="password">Password</label>
                <div class="inp-wrap">
                    <span class="ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0110 0v4" />
                        </svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="••••••••"
                        autocomplete="current-password" class="has-toggle" required>
                    <button type="button" class="eye-btn" data-target="password" aria-label="Show password">
                        <svg class="eye-open" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg class="eye-closed" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
                <div class="hint" id="hint-password">Password is required</div>
            </div>

            {{-- Forgot password --}}
            <div class="forgot-row">
                <a href="">forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">Sign in</button>
        </form>

        <form action="{{ route('demo.login') }}" method="POST" class="demo-account" aria-label="Demo system access">
            @csrf
            <div class="demo-account__top">
                <div class="demo-account__label">Demo system</div>
            </div>
            <div class="demo-account__credentials">
                <div>Open a ready admin demo workspace to review dashboard, attendance, students, instructors, courses, and reports.</div>
            </div>
            <button type="submit" class="demo-account__button">Try demo system</button>
        </form>

        {{-- ── Divider ───────────────────────────────────────────────── --}}
        <div class="divider">
            <hr><span>or continue with</span>
            <hr>
        </div>

        {{-- ── SSO buttons ──────────────────────────────────────────── --}}
        <!-- <div class="sso-row">
        <a href="#" class="sso-btn">
            {{-- Google --}}
            <svg width="15" height="15" viewBox="0 0 48 48" fill="none">
                <path d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3 12.9 3 4 11.9 4 23s8.9 20 20 20c11 0 19.7-8 19.7-20 0-1.3-.1-2.7-.2-3z" fill="#4285F4"/>
                <path d="M6.3 14.7l7 5.1C15.1 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3c-7.6 0-14.2 4.4-17.7 10.7z" fill="#EA4335"/>
                <path d="M24 43c5.5 0 10.4-1.9 14.2-5l-6.6-5.4C29.6 34.5 27 35.5 24 35.5c-6 0-10.6-3.9-11.9-9.1l-7 5.4C8 38.4 15.4 43 24 43z" fill="#34A853"/>
                <path d="M43.6 20H24v8h11.8c-.7 2.6-2.3 4.8-4.4 6.3l6.6 5.4C41.4 36.1 44 30 44 23c0-1-.1-2-.4-3z" fill="#FBBC05"/>
            </svg>
            Google
        </a>
        <a href="#" class="sso-btn">
            {{-- Microsoft --}}
            <svg width="14" height="14" viewBox="0 0 21 21" fill="none">
                <rect x="1"  y="1"  width="9" height="9" fill="#F25022"/>
                <rect x="11" y="1"  width="9" height="9" fill="#7FBA00"/>
                <rect x="1"  y="11" width="9" height="9" fill="#00A4EF"/>
                <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
            </svg>
            Microsoft
        </a>
    </div> -->

        {{-- ── Footer ────────────────────────────────────────────────── --}}
        <div class="card-footer">
            Don't have an account? <a href="{{ route('register') }}">Join now</a>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  JavaScript                                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <script>
        /* ── Theme toggle ────────────────────────────────────────────────── */
        (function() {
            var html = document.documentElement;
            var btn = document.getElementById('themeBtn');
            var moon = document.getElementById('ico-moon');
            var sun = document.getElementById('ico-sun');
            var isLight = localStorage.getItem('ai-theme') === 'light';

            function applyTheme() {
                if (isLight) {
                    html.classList.add('light');
                    moon.style.display = 'none';
                    sun.style.display = 'block';
                } else {
                    html.classList.remove('light');
                    moon.style.display = 'block';
                    sun.style.display = 'none';
                }
            }

            applyTheme();

            btn.addEventListener('click', function() {
                isLight = !isLight;
                localStorage.setItem('ai-theme', isLight ? 'light' : 'dark');
                applyTheme();
            });
        })();

        /* ── Show / hide password ────────────────────────────────────────── */
        document.querySelectorAll('.eye-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.dataset.target);
                var isText = input.type === 'text';
                input.type = isText ? 'password' : 'text';
                btn.querySelector('.eye-open').style.display = isText ? 'block' : 'none';
                btn.querySelector('.eye-closed').style.display = isText ? 'none' : 'block';
            });
        });

        /* ── Helpers ─────────────────────────────────────────────────────── */
        function setFieldState(input, state) {
            input.classList.remove('valid', 'invalid');
            if (state) input.classList.add(state);
        }

        function showHint(id, msg) {
            var el = document.getElementById(id);
            el.textContent = msg;
            el.className = 'hint show';
        }

        function clearHint(id) {
            document.getElementById(id).className = 'hint';
        }

        /* ── Blur validation ─────────────────────────────────────────────── */
        document.getElementById('email').addEventListener('blur', function() {
            var v = this.value.trim();
            var ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
            if (!v) {
                setFieldState(this, 'invalid');
                showHint('hint-email', 'Email is required');
            } else if (!ok) {
                setFieldState(this, 'invalid');
                showHint('hint-email', 'Enter a valid email address');
            } else {
                setFieldState(this, 'valid');
                clearHint('hint-email');
            }
        });

        document.getElementById('password').addEventListener('blur', function() {
            if (!this.value) {
                setFieldState(this, 'invalid');
                showHint('hint-password', 'Password is required');
            } else {
                setFieldState(this, 'valid');
                clearHint('hint-password');
            }
        });

        /* ── Submit validation ───────────────────────────────────────────── */
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            var errors = [];
            var emailEl = document.getElementById('email');
            var pwEl = document.getElementById('password');
            var emailVal = emailEl.value.trim();
            var pwVal = pwEl.value;

            /* email */
            var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
            if (!emailVal) {
                setFieldState(emailEl, 'invalid');
                showHint('hint-email', 'Email is required');
                errors.push('Email is required');
            } else if (!emailOk) {
                setFieldState(emailEl, 'invalid');
                showHint('hint-email', 'Enter a valid email address');
                errors.push('Invalid email address');
            } else {
                setFieldState(emailEl, 'valid');
                clearHint('hint-email');
            }

            /* password */
            if (!pwVal) {
                setFieldState(pwEl, 'invalid');
                showHint('hint-password', 'Password is required');
                errors.push('Password is required');
            } else {
                setFieldState(pwEl, 'valid');
                clearHint('hint-password');
            }

            var alert = document.getElementById('jsAlert');
            if (errors.length > 0) {
                e.preventDefault();
                alert.className = 'alert error show';
                alert.innerHTML = errors.map(function(m) {
                    return '<div class="a-item">• ' + m + '</div>';
                }).join('');
            } else {
                alert.className = 'alert error';
            }
        });
    </script>

</body>

</html>
