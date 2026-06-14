<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $branding = $branding ?? [];
        $appName = $branding['app_name'] ?? config('app.name', 'HRU ATS');
        $appSub = $branding['app_sub'] ?? __('auth.login_subtitle', ['app' => $appName]);
        $institutionName = $branding['institution_name'] ?? 'HRU';
        $appLogo = $branding['app_logo'] ?? 'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.register_title', ['app' => $appName]) }}</title>
    @vite(['resources/css/app.css'])
    <link rel="icon" href="{{ $appLogo }}" type="image/png" sizes="32x32" />

    <style>
        :root {
            --register-panel: #ffffff;
            --register-panel-soft: #f6f8fb;
            --register-border: #dbe3ee;
            --register-text: #142033;
            --register-muted: #66758a;
            --register-primary: #1e3a8a;
            --register-primary-2: #2563eb;
            --register-success: #0f9f8f;
            --register-danger: #c2413d;
            --register-danger-bg: #fff1f1;
            --register-shadow: 0 28px 70px rgba(15, 23, 42, .16);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--register-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(110deg, rgba(238, 243, 248, .95), rgba(238, 243, 248, .78)),
                url("https://image.freshnewsasia.com/2020/id-025/fn-2020-12-26-11-31-31-0.jpg") center / cover fixed;
        }

        button,
        input {
            font: inherit;
        }

        .register-shell {
            width: min(1120px, calc(100% - 32px));
            min-height: 100vh;
            margin: 0 auto;
            display: grid;
            align-items: center;
            padding: 32px 0;
        }

        .register-frame {
            display: grid;
            grid-template-columns: minmax(0, .95fr) 500px;
            min-height: 720px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .68);
            border-radius: 18px;
            background: rgba(255, 255, 255, .76);
            box-shadow: var(--register-shadow);
            backdrop-filter: blur(16px);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100%;
            padding: 36px;
            color: #fff;
            background:
                linear-gradient(150deg, rgba(9, 25, 54, .88), rgba(30, 58, 138, .68)),
                url("https://www.hru.edu.kh/wp-content/uploads/2023/08/350773696_210061804722310_6900832573841793976_n-745x400.jpg") center / cover;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, transparent, rgba(5, 12, 24, .5));
        }

        .brand-top,
        .brand-copy,
        .status-row {
            position: relative;
            z-index: 1;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 12px;
            background: rgba(255, 255, 255, .92);
        }

        .brand-logo img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .brand-name {
            display: grid;
            gap: 2px;
        }

        .brand-name strong {
            font-size: 22px;
            line-height: 1;
            letter-spacing: .08em;
        }

        .brand-name span {
            font-size: 13px;
            color: rgba(255, 255, 255, .78);
        }

        .brand-copy h1 {
            margin: 0;
            max-width: 620px;
            font-size: clamp(34px, 5vw, 60px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .brand-copy p {
            max-width: 520px;
            margin: 18px 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: 16px;
            line-height: 1.7;
        }

        .status-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .status-item {
            min-height: 76px;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
        }

        .status-item span {
            display: block;
            color: rgba(255, 255, 255, .72);
            font-size: 12px;
        }

        .status-item strong {
            display: block;
            margin-top: 7px;
            font-size: 15px;
            line-height: 1.25;
        }

        .form-panel {
            display: flex;
            align-items: center;
            background: var(--register-panel);
            padding: 36px 42px;
        }

        .form-card {
            width: 100%;
        }

        .form-kicker {
            color: var(--register-primary-2);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .form-title {
            margin: 10px 0 8px;
            color: var(--register-text);
            font-size: 30px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .form-subtitle {
            margin: 0 0 24px;
            color: var(--register-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #f0b8b5;
            border-radius: 8px;
            color: var(--register-danger);
            background: var(--register-danger-bg);
            font-size: 13px;
            line-height: 1.45;
        }

        .alert[hidden] {
            display: none;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #314058;
            font-size: 13px;
            font-weight: 700;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon,
        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #8695a8;
        }

        .input-icon {
            left: 14px;
            display: flex;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 14px 0 42px;
            border: 1px solid var(--register-border);
            border-radius: 8px;
            color: var(--register-text);
            background: var(--register-panel-soft);
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .input-wrap input::placeholder {
            color: #9aa8ba;
        }

        .input-wrap input:focus {
            border-color: var(--register-primary-2);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .13);
        }

        .input-wrap input.invalid {
            border-color: var(--register-danger);
            box-shadow: 0 0 0 4px rgba(194, 65, 61, .1);
        }

        .input-wrap input.valid {
            border-color: var(--register-success);
            box-shadow: 0 0 0 4px rgba(15, 159, 143, .1);
        }

        .input-wrap input.has-toggle {
            padding-right: 46px;
        }

        .password-toggle {
            right: 10px;
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
        }

        .password-toggle:hover {
            color: var(--register-primary);
            background: rgba(30, 58, 138, .08);
        }

        .hint {
            display: none;
            margin-top: 6px;
            color: var(--register-danger);
            font-size: 12px;
        }

        .hint.show,
        .hint.ok {
            display: block;
        }

        .hint.ok {
            color: var(--register-muted);
        }

        .strength {
            display: none;
            margin-top: 8px;
        }

        .strength.show {
            display: block;
        }

        .strength-bars {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 4px;
            margin-bottom: 5px;
        }

        .strength-bar {
            height: 4px;
            border-radius: 999px;
            background: #dbe3ee;
        }

        .strength-bar.active-1 {
            background: #c2413d;
        }

        .strength-bar.active-2 {
            background: #d97706;
        }

        .strength-bar.active-3 {
            background: #0f9f8f;
        }

        .strength-bar.active-4 {
            background: #2563eb;
        }

        .strength-label,
        .match-label {
            color: var(--register-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .match-row {
            display: none;
            align-items: center;
            gap: 7px;
            margin-top: 8px;
        }

        .match-row.show {
            display: flex;
        }

        .match-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #dbe3ee;
        }

        .match-dot.yes {
            background: var(--register-success);
        }

        .match-dot.no {
            background: var(--register-danger);
        }

        .submit-btn {
            width: 100%;
            height: 48px;
            margin-top: 4px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--register-primary), var(--register-primary-2));
            box-shadow: 0 14px 24px rgba(37, 99, 235, .22);
            transition: transform .16s ease, box-shadow .16s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 30px rgba(37, 99, 235, .26);
        }

        .form-footer {
            margin-top: 20px;
            color: var(--register-muted);
            font-size: 13px;
            text-align: center;
        }

        .form-footer a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            margin-left: 6px;
            padding: 0 12px;
            border: 1px solid rgba(37, 99, 235, .22);
            border-radius: 8px;
            color: var(--register-primary-2);
            font-weight: 800;
            text-decoration: none;
            background: rgba(37, 99, 235, .06);
        }

        .form-footer a:hover {
            border-color: rgba(37, 99, 235, .42);
            background: rgba(37, 99, 235, .1);
        }

        @media (max-width: 960px) {
            .register-shell {
                width: min(560px, calc(100% - 24px));
            }

            .register-frame {
                display: block;
                min-height: 0;
            }

            .brand-panel {
                min-height: 230px;
                padding: 24px;
            }

            .brand-copy {
                margin-top: 48px;
            }

            .brand-copy h1 {
                font-size: 32px;
            }

            .brand-copy p,
            .status-row {
                display: none;
            }

            .form-panel {
                padding: 28px 22px 30px;
            }
        }

        @media (max-width: 520px) {
            .register-shell {
                width: 100%;
                padding: 0;
            }

            .register-frame {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
            }

            .brand-panel {
                min-height: 190px;
            }

            .field-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <main class="register-shell">
        <section class="register-frame" aria-label="{{ __('auth.register_aria', ['app' => $appName]) }}">
            <aside class="brand-panel">
                <div class="brand-top">
                    <div class="brand-logo">
                        <img src="{{ $appLogo }}" alt="{{ $appName }}">
                    </div>
                    <div class="brand-name">
                        <strong>{{ $appName }}</strong>
                        <span>{{ $appSub ?: $institutionName }}</span>
                    </div>
                </div>

                <div class="brand-copy">
                    <h1>{{ __('auth.register_hero_title') }}</h1>
                    <p>{{ __('auth.register_hero_description') }}</p>
                </div>

                <div class="status-row" aria-label="{{ __('auth.system_highlights') }}">
                    <div class="status-item">
                        <span>{{ __('auth.approval') }}</span>
                        <strong>{{ __('auth.pending_review') }}</strong>
                    </div>
                    <div class="status-item">
                        <span>{{ __('auth.secure') }}</span>
                        <strong>{{ __('auth.password_rules') }}</strong>
                    </div>
                    <div class="status-item">
                        <span>{{ __('auth.identity') }}</span>
                        <strong>{{ __('auth.verified_email') }}</strong>
                    </div>
                </div>
            </aside>

            <div class="form-panel">
                <div class="form-card">
                    <div class="form-kicker">{{ __('auth.secure_sign_in') }}</div>
                    <h1 class="form-title">{{ __('auth.create_account_title') }}</h1>
                    <p class="form-subtitle">{{ __('auth.register_subtitle', ['app' => $appName]) }}</p>

                    @if ($errors->any())
                        <div class="alert">
                            @foreach ($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="alert" id="jsAlert" hidden></div>

                    <form action="{{ route('register.post') }}" method="POST" id="registerForm" novalidate>
                        @csrf

                        <div class="field">
                            <label for="name">{{ __('auth.full_name') }}</label>
                            <div class="input-wrap">
                                <span class="input-icon" aria-hidden="true">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </span>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    placeholder="{{ __('auth.full_name_placeholder') }}" autocomplete="name" required>
                            </div>
                            <div class="hint" id="hint-name">{{ __('auth.full_name_required') }}</div>
                        </div>

                        <div class="field">
                            <label for="email">{{ __('auth.email_address') }}</label>
                            <div class="input-wrap">
                                <span class="input-icon" aria-hidden="true">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                        <path d="m22 7-10 7L2 7" />
                                    </svg>
                                </span>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    placeholder="{{ __('auth.email_placeholder') }}" autocomplete="email" required>
                            </div>
                            <div class="hint" id="hint-email">{{ __('auth.email_invalid') }}</div>
                        </div>

                        <div class="field">
                            <label for="admin_key">{{ __('auth.admin_secret_key_optional') }}</label>
                            <div class="input-wrap">
                                <span class="input-icon" aria-hidden="true">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.78 7.78 5.5 5.5 0 0 1 7.78-7.78Zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4" />
                                    </svg>
                                </span>
                                <input type="password" id="admin_key" name="admin_key"
                                    placeholder="{{ __('auth.admin_secret_key_placeholder') }}" autocomplete="off">
                            </div>
                            <div class="hint ok" id="hint-admin-key">{{ __('auth.admin_key_help') }}</div>
                        </div>

                        <div class="field-grid">
                            <div class="field">
                                <label for="password">{{ __('auth.password') }}</label>
                                <div class="input-wrap">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <input type="password" id="password" name="password"
                                        placeholder="{{ __('auth.password_placeholder') }}" autocomplete="new-password"
                                        class="has-toggle" required>
                                    <button type="button" class="password-toggle" data-target="password"
                                        aria-label="{{ __('auth.show_password') }}">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" hidden>
                                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                            <path d="M6.61 6.61C3.54 8.33 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61" />
                                            <path d="m2 2 20 20" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="strength" id="strengthWrap">
                                    <div class="strength-bars">
                                        <div class="strength-bar" id="strengthBar1"></div>
                                        <div class="strength-bar" id="strengthBar2"></div>
                                        <div class="strength-bar" id="strengthBar3"></div>
                                        <div class="strength-bar" id="strengthBar4"></div>
                                    </div>
                                    <div class="strength-label" id="strengthLabel">{{ __('auth.password_min') }}</div>
                                </div>
                                <div class="hint" id="hint-password">{{ __('auth.password_min_required') }}</div>
                            </div>

                            <div class="field">
                                <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                                <div class="input-wrap">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        placeholder="{{ __('auth.confirm_password_placeholder') }}"
                                        autocomplete="new-password" class="has-toggle" required>
                                    <button type="button" class="password-toggle" data-target="password_confirmation"
                                        aria-label="{{ __('auth.show_password') }}">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" hidden>
                                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                            <path d="M6.61 6.61C3.54 8.33 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61" />
                                            <path d="m2 2 20 20" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="match-row" id="matchRow">
                                    <div class="match-dot" id="matchDot"></div>
                                    <span class="match-label" id="matchLabel">{{ __('auth.passwords_must_match') }}</span>
                                </div>
                                <div class="hint" id="hint-confirm">{{ __('auth.passwords_must_match') }}</div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">{{ __('auth.create_account_button') }}</button>
                    </form>

                    <div class="form-footer">
                        {{ __('auth.already_have_account') }} <a href="{{ route('login') }}">{{ __('auth.sign_in') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @php
        $registerText = [
            'showPassword' => __('auth.show_password'),
            'hidePassword' => __('auth.hide_password'),
            'nameRequired' => __('auth.full_name_required'),
            'emailRequired' => __('auth.email_required'),
            'emailInvalid' => __('auth.email_invalid'),
            'adminKeyHelp' => __('auth.admin_key_help'),
            'adminKeyEntered' => __('auth.admin_key_entered'),
            'passwordMin' => __('auth.password_min_required'),
            'passwordsMustMatch' => __('auth.passwords_must_match'),
            'passwordsDoNotMatch' => __('auth.passwords_do_not_match'),
            'match' => __('auth.match'),
            'noMatch' => __('auth.no_match'),
            'weak' => __('auth.weak'),
            'fair' => __('auth.fair'),
            'good' => __('auth.good'),
            'strong' => __('auth.strong'),
        ];
    @endphp

    <script>
        const registerText = @json($registerText);

        document.querySelectorAll('.password-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.dataset.target);
                var isText = input.type === 'text';
                input.type = isText ? 'password' : 'text';
                btn.querySelector('.eye-open').hidden = !isText;
                btn.querySelector('.eye-closed').hidden = isText;
                btn.setAttribute('aria-label', isText ? registerText.showPassword : registerText.hidePassword);
            });
        });

        function setFieldState(input, state) {
            input.classList.remove('valid', 'invalid');
            if (state) {
                input.classList.add(state);
            }
        }

        function showHint(id, msg, ok) {
            var el = document.getElementById(id);
            el.textContent = msg;
            el.className = ok ? 'hint ok' : 'hint show';
        }

        function clearHint(id) {
            document.getElementById(id).className = 'hint';
        }

        function passwordStrength(password) {
            var score = 0;
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            if (score <= 1) return { level: 1, label: registerText.weak };
            if (score === 2) return { level: 2, label: registerText.fair };
            if (score === 3) return { level: 3, label: registerText.good };
            return { level: 4, label: registerText.strong };
        }

        var nameInput = document.getElementById('name');
        var emailInput = document.getElementById('email');
        var adminKeyInput = document.getElementById('admin_key');
        var passwordInput = document.getElementById('password');
        var confirmInput = document.getElementById('password_confirmation');
        var strengthWrap = document.getElementById('strengthWrap');
        var strengthLabel = document.getElementById('strengthLabel');
        var strengthBars = [
            document.getElementById('strengthBar1'),
            document.getElementById('strengthBar2'),
            document.getElementById('strengthBar3'),
            document.getElementById('strengthBar4')
        ];

        nameInput.addEventListener('blur', function() {
            if (!nameInput.value.trim()) {
                setFieldState(nameInput, 'invalid');
                showHint('hint-name', registerText.nameRequired);
            } else {
                setFieldState(nameInput, 'valid');
                clearHint('hint-name');
            }
        });

        emailInput.addEventListener('blur', function() {
            var value = emailInput.value.trim();
            var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            if (!value) {
                setFieldState(emailInput, 'invalid');
                showHint('hint-email', registerText.emailRequired);
            } else if (!valid) {
                setFieldState(emailInput, 'invalid');
                showHint('hint-email', registerText.emailInvalid);
            } else {
                setFieldState(emailInput, 'valid');
                clearHint('hint-email');
            }
        });

        adminKeyInput.addEventListener('input', function() {
            showHint('hint-admin-key', adminKeyInput.value ? registerText.adminKeyEntered : registerText.adminKeyHelp, true);
        });

        passwordInput.addEventListener('input', function() {
            var value = passwordInput.value;
            if (value) {
                var result = passwordStrength(value);
                strengthWrap.classList.add('show');
                strengthLabel.textContent = result.label;
                strengthBars.forEach(function(bar, index) {
                    bar.className = 'strength-bar';
                    if (index < result.level) {
                        bar.classList.add('active-' + result.level);
                    }
                });
            } else {
                strengthWrap.classList.remove('show');
            }

            if (confirmInput.value) {
                updateMatchState();
            }
        });

        passwordInput.addEventListener('blur', function() {
            if (passwordInput.value && passwordInput.value.length < 8) {
                setFieldState(passwordInput, 'invalid');
                showHint('hint-password', registerText.passwordMin);
            } else if (passwordInput.value.length >= 8) {
                setFieldState(passwordInput, 'valid');
                clearHint('hint-password');
            }
        });

        function updateMatchState() {
            var row = document.getElementById('matchRow');
            var dot = document.getElementById('matchDot');
            var label = document.getElementById('matchLabel');

            if (!confirmInput.value) {
                row.classList.remove('show');
                return;
            }

            row.classList.add('show');
            if (passwordInput.value === confirmInput.value) {
                dot.className = 'match-dot yes';
                label.textContent = registerText.match;
                setFieldState(confirmInput, 'valid');
                clearHint('hint-confirm');
            } else {
                dot.className = 'match-dot no';
                label.textContent = registerText.noMatch;
                setFieldState(confirmInput, 'invalid');
                showHint('hint-confirm', registerText.passwordsMustMatch);
            }
        }

        confirmInput.addEventListener('input', updateMatchState);

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            var errors = [];
            var nameValue = nameInput.value.trim();
            var emailValue = emailInput.value.trim();
            var emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);
            var passwordValue = passwordInput.value;
            var confirmValue = confirmInput.value;

            if (!nameValue) {
                setFieldState(nameInput, 'invalid');
                showHint('hint-name', registerText.nameRequired);
                errors.push(registerText.nameRequired);
            }

            if (!emailValue) {
                setFieldState(emailInput, 'invalid');
                showHint('hint-email', registerText.emailRequired);
                errors.push(registerText.emailRequired);
            } else if (!emailValid) {
                setFieldState(emailInput, 'invalid');
                showHint('hint-email', registerText.emailInvalid);
                errors.push(registerText.emailInvalid);
            }

            if (!passwordValue || passwordValue.length < 8) {
                setFieldState(passwordInput, 'invalid');
                showHint('hint-password', registerText.passwordMin);
                errors.push(registerText.passwordMin);
            }

            if (!confirmValue || passwordValue !== confirmValue) {
                setFieldState(confirmInput, 'invalid');
                showHint('hint-confirm', registerText.passwordsMustMatch);
                errors.push(registerText.passwordsDoNotMatch);
            }

            var alert = document.getElementById('jsAlert');
            if (errors.length) {
                e.preventDefault();
                alert.hidden = false;
                alert.innerHTML = errors.map(function(error) {
                    return '<div>' + error + '</div>';
                }).join('');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert.hidden = true;
                alert.innerHTML = '';
            }
        });
    </script>

    @include('partials.legacy-translator')
</body>

</html>
