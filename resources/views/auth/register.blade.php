<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    @vite(['resources/css/app.css'])
    <link rel="icon" href="https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png" type="image/png" sizes="32x32"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"> -->

    <style>
        /* ─── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ─── Dark theme (default) ───────────────────────────── */
        :root {
            --bg:              #0D0D0F;
            --surface:         #141418;
            --surface2:        #0D0D0F;
            --border:          rgba(255,255,255,0.07);
            --border-hover:    rgba(255,255,255,0.13);
            --border-focus:    rgba(99,59,246,0.65);
            --text:            #ffffff;
            --text-sub:        rgba(255,255,255,0.38);
            --text-muted:      rgba(255,255,255,0.22);
            --label:           rgba(255,255,255,0.30);
            --accent:          #633BF6;
            --accent2:         #4A2ECE;
            --teal:            #16C4A7;
            --icon:            rgba(255,255,255,0.30);
            --placeholder:     rgba(255,255,255,0.20);
            --error:           #F09595;
            --error-bg:        rgba(226,75,74,0.10);
            --error-border:    rgba(226,75,74,0.28);
            --success:         #16C4A7;
            --success-bg:      rgba(22,196,167,0.10);
            --success-border:  rgba(22,196,167,0.28);
            --chip-bg:         rgba(255,255,255,0.04);
            --chip-border:     rgba(255,255,255,0.07);
            --chip-text:       rgba(255,255,255,0.38);
            --strength-track:  rgba(255,255,255,0.07);
            --top-line:        linear-gradient(90deg, transparent, rgba(99,59,246,0.65) 40%, rgba(22,196,167,0.45) 70%, transparent);
            --radial1:         rgba(99,59,246,0.17);
            --radial2:         rgba(22,196,167,0.09);
            --toggle-bg:       rgba(255,255,255,0.06);
            --toggle-border:   rgba(255,255,255,0.10);
            --toggle-color:    rgba(255,255,255,0.45);
        }

        /* ─── Light theme ────────────────────────────────────── */
        html.light {
            --bg:              #F0EEF8;
            --surface:         #ffffff;
            --surface2:        #F7F5FF;
            --border:          rgba(99,59,246,0.11);
            --border-hover:    rgba(99,59,246,0.22);
            --border-focus:    #633BF6;
            --text:            #1a1528;
            --text-sub:        #6b6385;
            --text-muted:      #a49dbf;
            --label:           #8e87ad;
            --accent:          #633BF6;
            --accent2:         #4A2ECE;
            --teal:            #0FA88F;
            --icon:            #9f99c0;
            --placeholder:     #c7c2dc;
            --error:           #b03030;
            --error-bg:        rgba(176,48,48,0.06);
            --error-border:    rgba(176,48,48,0.22);
            --success:         #0FA88F;
            --success-bg:      rgba(15,168,143,0.07);
            --success-border:  rgba(15,168,143,0.25);
            --chip-bg:         rgba(99,59,246,0.05);
            --chip-border:     rgba(99,59,246,0.10);
            --chip-text:       #9991bf;
            --strength-track:  rgba(99,59,246,0.07);
            --top-line:        linear-gradient(90deg, transparent, rgba(99,59,246,0.45) 40%, rgba(15,168,143,0.35) 70%, transparent);
            --radial1:         rgba(99,59,246,0.08);
            --radial2:         rgba(15,168,143,0.06);
            --toggle-bg:       rgba(99,59,246,0.06);
            --toggle-border:   rgba(99,59,246,0.14);
            --toggle-color:    #7a71c0;
        }

        /* ─── Base ───────────────────────────────────────────── */
        html, body { min-height: 100vh; }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 65% 55% at 15% 8%,  var(--radial1) 0%, transparent 60%),
                radial-gradient(ellipse 45% 55% at 85% 92%, var(--radial2) 0%, transparent 55%);
            padding: 2rem 1rem;
            font-family: 'DM Sans', sans-serif;
            transition: background-color .35s, color .35s;
        }

        /* ─── Card ───────────────────────────────────────────── */
        .card {
            width: 100%;
            max-width: 448px;
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
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* shimmer top border */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
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
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #633BF6, #16C4A7);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 25px;
            letter-spacing: .20em;
            color: blue;
            transition: color .35s;
        }

        .brand-text span { color: var(--teal); }

        .theme-btn {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-border);
            color: var(--toggle-color);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s;
            flex-shrink: 0;
        }

        .theme-btn:hover { opacity: .75; }

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
            display: flex; align-items: center; gap: 6px;
            transition: border-color .2s, background .2s;
        }

        .chip:hover {
            background: var(--chip-bg);
            border-color: var(--border-hover);
        }

        .chip-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .chip span {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .04em;
            color: var(--chip-text);
        }

        /* ─── Error / success banner ──────────────────────────── */
        .alert {
            border-radius: 10px;
            padding: 10px 14px;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: .02em;
            margin-bottom: 18px;
            display: none;
        }

        .alert.show { display: block; }
        .alert.error { background: var(--error-bg); border: 1px solid var(--error-border); color: var(--error); }
        .alert.success { background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success); }
        .alert .a-item { margin-bottom: 2px; }
        .alert .a-item:last-child { margin-bottom: 0; }

        /* ─── Form layout ─────────────────────────────────────── */
        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field { margin-bottom: 16px; }

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
        .inp-wrap { position: relative; }

        .inp-wrap .ico {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--icon);
            display: flex; align-items: center;
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

        .inp-wrap input::placeholder { color: var(--placeholder); }

        .inp-wrap input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(99,59,246,.11);
        }

        .inp-wrap input.has-toggle { padding-right: 38px; }

        /* validation states */
        .inp-wrap input.invalid { border-color: var(--error) !important; box-shadow: 0 0 0 3px rgba(226,75,74,.09) !important; }
        .inp-wrap input.valid   { border-color: var(--success) !important; box-shadow: 0 0 0 3px rgba(22,196,167,.08) !important; }

        /* ─── Password eye toggle ─────────────────────────────── */
        .eye-btn {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            color: var(--icon);
            display: flex; align-items: center;
            padding: 3px;
            transition: color .2s;
        }

        .eye-btn:hover { color: var(--text); }

        /* ─── Field hint / error text ─────────────────────────── */
        .hint {
            font-size: 10.5px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .03em;
            margin-top: 5px;
            color: var(--error);
            display: none;
        }

        .hint.show { display: block; }
        .hint.ok   { display: block; color: var(--success); }

        /* ─── Password strength ───────────────────────────────── */
        .strength { margin-top: 7px; display: none; }
        .strength.show { display: block; }

        .str-bars { display: flex; gap: 4px; margin-bottom: 4px; }

        .str-bar {
            flex: 1; height: 3px;
            border-radius: 2px;
            background: var(--strength-track);
            transition: background .3s;
        }

        .str-bar.lv1 { background: #E24B4A; }
        .str-bar.lv2 { background: #EF9F27; }
        .str-bar.lv3 { background: #16C4A7; }
        .str-bar.lv4 { background: #633BF6; }

        .str-label {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .07em;
            color: var(--label);
        }

        /* ─── Match indicator ─────────────────────────────────── */
        .match-row {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
        }

        .match-row.show { display: flex; }

        .match-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--strength-track);
            transition: background .3s;
            flex-shrink: 0;
        }

        .match-dot.yes { background: var(--success); }
        .match-dot.no  { background: var(--error); }

        .match-label {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: .05em;
            color: var(--label);
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
            margin-top: 6px;
            position: relative;
            overflow: hidden;
            letter-spacing: .02em;
            transition: opacity .2s, transform .15s;
        }

        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-submit:hover { opacity: .91; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* ─── Footer link ─────────────────────────────────────── */
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

        .card-footer a:hover { opacity: .72; }

        /* ─── Responsive ──────────────────────────────────────── */
        @media (max-width: 480px) {
            .card { padding: 28px 22px 26px; }
            .grid2 { grid-template-columns: 1fr; gap: 0; }
            .chips { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<div class="card">

    {{-- ── Top row: brand + theme toggle ──────────────────────── --}}
    <div class="top-row">
        <div class="brand">
     <div class="brand-mark">
                <img src="https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png" alt="Logo">
            </div>
            <div class="brand-text">HRU <span>ATS</span></div>
        
    </div>

        <button class="theme-btn" id="themeBtn" type="button" title="Toggle light / dark">
            {{-- Moon icon (shown in dark mode) --}}
            <svg id="ico-moon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
            {{-- Sun icon (shown in light mode) --}}
            <svg id="ico-sun" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="display:none;">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1"     x2="12" y2="3"/>
                <line x1="12" y1="21"    x2="12" y2="23"/>
                <line x1="4.22" y1="4.22"   x2="5.64"  y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1"  y1="12" x2="3"  y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
                <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
            </svg>
        </button>
    </div>

    {{-- ── Heading ──────────────────────────────────────────────── --}}
    <p class="headline">Create your account</p>
    <p class="subline">Join AttendAI and start tracking smarter</p>

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
    @if($errors->any())
        <div class="alert error show">
            @foreach($errors->all() as $err)
                <div class="a-item">• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    {{-- Client-side validation banner (hidden by default) --}}
    <div class="alert error" id="jsAlert"></div>

    {{-- ── Form ─────────────────────────────────────────────────── --}}
    <form action="{{ route('register.post') }}" method="POST" id="regForm" novalidate>
        @csrf
        <input type="hidden" name="role" value="admin">

        {{-- Full name --}}
        <div class="field">
            <label for="name">Full name</label>
            <div class="inp-wrap">
                <span class="ico">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <input type="text" id="name" name="name"
                       placeholder="John Doe"
                       value="{{ old('name') }}"
                       autocomplete="name"
                       required>
            </div>
            <div class="hint" id="hint-name">Full name is required</div>
        </div>

        {{-- Email --}}
        <div class="field">
            <label for="email">Email address</label>
            <div class="inp-wrap">
                <span class="ico">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M2 7l10 7 10-7"/>
                    </svg>
                </span>
                <input type="email" id="email" name="email"
                       placeholder="you@company.com"
                       value="{{ old('email') }}"
                       autocomplete="email"
                       required>
            </div>
            <div class="hint" id="hint-email">Enter a valid email address</div>
        </div>

        {{-- Admin Key (New) --}}
        <div class="field">
            <label for="admin_key">Admin Secret Key (Optional)</label>
            <div class="inp-wrap">
                <span class="ico">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                        <line x1="8" y1="16" x2="8" y2="16"/>
                    </svg>
                </span>
                <input type="password" id="admin_key" name="admin_key"
                       placeholder="Enter key for Super Admin status (optional)"
                       autocomplete="off">
            </div>
            <div class="hint ok" id="hint-admin-key" style="display:block; color:var(--text-sub);">Leave blank for regular Admin account</div>
        </div>

        {{-- Password + Confirm (2-column grid) --}}
        <div class="grid2">

            {{-- Password --}}
            <div class="field">
                <label for="password">Password</label>
                <div class="inp-wrap">
                    <span class="ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           class="has-toggle"
                           required>
                    <button type="button" class="eye-btn" data-target="password" aria-label="Show password">
                        <svg class="eye-open" width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>

                {{-- Strength meter --}}
                <div class="strength" id="strengthWrap">
                    <div class="str-bars">
                        <div class="str-bar" id="sb1"></div>
                        <div class="str-bar" id="sb2"></div>
                        <div class="str-bar" id="sb3"></div>
                        <div class="str-bar" id="sb4"></div>
                    </div>
                    <div class="str-label" id="strengthLabel">—</div>
                </div>
                <div class="hint" id="hint-password">Minimum 8 characters</div>
            </div>

            {{-- Confirm password --}}
            <div class="field">
                <label for="password_confirmation">Confirm</label>
                <div class="inp-wrap">
                    <span class="ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           class="has-toggle"
                           required>
                    <button type="button" class="eye-btn" data-target="password_confirmation" aria-label="Show confirm password">
                        <svg class="eye-open" width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>

                {{-- Match indicator --}}
                <div class="match-row" id="matchRow">
                    <div class="match-dot" id="matchDot"></div>
                    <span class="match-label" id="matchLabel">—</span>
                </div>
                <div class="hint" id="hint-confirm">Passwords must match</div>
            </div>

        </div>{{-- /.grid2 --}}

        <button type="submit" class="btn-submit">Create account</button>
    </form>

    <div class="card-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{--  JavaScript                                                     --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<script>
/* ── Theme toggle ───────────────────────────────────────────────── */
(function () {
    const html   = document.documentElement;
    const btn    = document.getElementById('themeBtn');
    const moon   = document.getElementById('ico-moon');
    const sun    = document.getElementById('ico-sun');
    let   isLight = localStorage.getItem('ai-theme') === 'light';

    function applyTheme() {
        if (isLight) {
            html.classList.add('light');
            moon.style.display = 'none';
            sun.style.display  = 'block';
        } else {
            html.classList.remove('light');
            moon.style.display = 'block';
            sun.style.display  = 'none';
        }
    }

    applyTheme();

    btn.addEventListener('click', function () {
        isLight = !isLight;
        localStorage.setItem('ai-theme', isLight ? 'light' : 'dark');
        applyTheme();
    });
})();

/* ── Show / hide password ────────────────────────────────────────── */
document.querySelectorAll('.eye-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input  = document.getElementById(btn.dataset.target);
        var isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.querySelector('.eye-open').style.display  = isText ? 'block' : 'none';
        btn.querySelector('.eye-closed').style.display = isText ? 'none'  : 'block';
    });
});

/* ── Password strength ──────────────────────────────────────────── */
function calcStrength(pw) {
    var score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    if (score <= 1) return { level: 1, label: 'WEAK'   };
    if (score === 2) return { level: 2, label: 'FAIR'   };
    if (score === 3) return { level: 3, label: 'GOOD'   };
                     return { level: 4, label: 'STRONG' };
}

var pwInput   = document.getElementById('password');
var confInput = document.getElementById('password_confirmation');
var strWrap   = document.getElementById('strengthWrap');
var strLabel  = document.getElementById('strengthLabel');
var bars      = [
    document.getElementById('sb1'),
    document.getElementById('sb2'),
    document.getElementById('sb3'),
    document.getElementById('sb4')
];

pwInput.addEventListener('input', function () {
    var val = pwInput.value;

    if (val.length > 0) {
        strWrap.classList.add('show');
        var s = calcStrength(val);
        bars.forEach(function (bar, i) {
            bar.className = 'str-bar';
            if (i < s.level) bar.classList.add('lv' + s.level);
        });
        strLabel.textContent = s.label;
    } else {
        strWrap.classList.remove('show');
    }

    if (confInput.value.length > 0) checkMatch();
});

/* ── Confirm / match ────────────────────────────────────────────── */
function checkMatch() {
    var row  = document.getElementById('matchRow');
    var dot  = document.getElementById('matchDot');
    var lbl  = document.getElementById('matchLabel');

    if (confInput.value.length > 0) {
        row.classList.add('show');
        if (pwInput.value === confInput.value) {
            dot.className = 'match-dot yes';
            lbl.textContent = 'MATCH';
            setFieldState(confInput, 'valid');
            clearHint('hint-confirm');
        } else {
            dot.className = 'match-dot no';
            lbl.textContent = 'NO MATCH';
            setFieldState(confInput, 'invalid');
        }
    } else {
        row.classList.remove('show');
    }
}

confInput.addEventListener('input', checkMatch);

/* ── Field state helpers ─────────────────────────────────────────── */
function setFieldState(input, state) {
    input.classList.remove('valid', 'invalid');
    if (state) input.classList.add(state);
}

function showHint(id, msg, isOk) {
    var el = document.getElementById(id);
    el.textContent = msg;
    el.className   = 'hint ' + (isOk ? 'ok' : 'show');
}

function clearHint(id) {
    var el = document.getElementById(id);
    el.className = 'hint';
}

/* ── Blur-time validation ────────────────────────────────────────── */
document.getElementById('name').addEventListener('blur', function () {
    var v = this.value.trim();
    if (!v) {
        setFieldState(this, 'invalid');
        showHint('hint-name', 'Full name is required', false);
    } else {
        setFieldState(this, 'valid');
        clearHint('hint-name');
    }
});

document.getElementById('email').addEventListener('blur', function () {
    var v  = this.value.trim();
    var ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    if (!v) {
        setFieldState(this, 'invalid');
        showHint('hint-email', 'Email is required', false);
    } else if (!ok) {
        setFieldState(this, 'invalid');
        showHint('hint-email', 'Enter a valid email address', false);
    } else {
        setFieldState(this, 'valid');
        clearHint('hint-email');
    }
});

document.getElementById('admin_key').addEventListener('input', function() {
    var hint = document.getElementById('hint-admin-key');
    if (this.value.length > 0) {
        hint.textContent = 'Key entered — checking status on server...';
        hint.style.color = 'var(--accent)';
    } else {
        hint.textContent = 'Leave blank for regular Admin account';
        hint.style.color = 'var(--text-sub)';
        setFieldState(this, null);
    }
});

pwInput.addEventListener('blur', function () {
    if (this.value.length > 0 && this.value.length < 8) {
        setFieldState(this, 'invalid');
        showHint('hint-password', 'Minimum 8 characters required', false);
    } else if (this.value.length >= 8) {
        setFieldState(this, 'valid');
        clearHint('hint-password');
    }
});

/* ── Submit-time validation ──────────────────────────────────────── */
document.getElementById('regForm').addEventListener('submit', function (e) {
    var errors = [];

    var nameVal  = document.getElementById('name').value.trim();
    var emailVal = document.getElementById('email').value.trim();
    var pwVal    = pwInput.value;
    var confVal  = confInput.value;

    /* name */
    if (!nameVal) {
        setFieldState(document.getElementById('name'), 'invalid');
        showHint('hint-name', 'Full name is required', false);
        errors.push('Full name is required');
    } else {
        setFieldState(document.getElementById('name'), 'valid');
        clearHint('hint-name');
    }

    /* email */
    var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
    if (!emailVal) {
        setFieldState(document.getElementById('email'), 'invalid');
        showHint('hint-email', 'Email is required', false);
        errors.push('Email is required');
    } else if (!emailOk) {
        setFieldState(document.getElementById('email'), 'invalid');
        showHint('hint-email', 'Enter a valid email address', false);
        errors.push('Invalid email address');
    } else {
        setFieldState(document.getElementById('email'), 'valid');
        clearHint('hint-email');
    }

    /* password */
    if (!pwVal || pwVal.length < 8) {
        setFieldState(pwInput, 'invalid');
        showHint('hint-password', 'Minimum 8 characters required', false);
        errors.push('Password must be at least 8 characters');
    } else {
        setFieldState(pwInput, 'valid');
        clearHint('hint-password');
    }

    /* confirm */
    if (!confVal || pwVal !== confVal) {
        setFieldState(confInput, 'invalid');
        document.getElementById('hint-confirm').className = 'hint show';
        errors.push('Passwords do not match');
    } else {
        setFieldState(confInput, 'valid');
        clearHint('hint-confirm');
    }

    var alert = document.getElementById('jsAlert');

    if (errors.length > 0) {
        e.preventDefault(); /* stop native submission */
        alert.className = 'alert error show';
        alert.style.background  = '';
        alert.style.borderColor = '';
        alert.style.color       = '';
        alert.innerHTML = errors.map(function (msg) {
            return '<div class="a-item">• ' + msg + '</div>';
        }).join('');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    /* if no JS errors → form submits normally to Laravel */
});
</script>

@include('partials.legacy-translator')
</body>
</html>
