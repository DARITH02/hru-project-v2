<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $branding = $branding ?? [];
        $appName = $branding['app_name'] ?? config('app.name', 'HRU ATS');
        $appSub = $branding['app_sub'] ?? __('auth.login_subtitle', ['app' => $appName]);
        $institutionName = $branding['institution_name'] ?? 'HRU';
        $appLogo =
            $branding['app_logo'] ??
            'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login_title', ['app' => $appName]) }}</title>
    @vite(['resources/css/app.css'])
    <link rel="icon" href="{{ $appLogo }}" type="image/png" sizes="32x32" />

    <style>
        :root {
            --login-bg: #eef3f8;
            --login-panel: #ffffff;
            --login-panel-soft: #f6f8fb;
            --login-border: #dbe3ee;
            --login-text: #142033;
            --login-muted: #66758a;
            --login-primary: #1e3a8a;
            --login-primary-2: #2563eb;
            --login-accent: #0f9f8f;
            --login-danger: #c2413d;
            --login-danger-bg: #fff1f1;
            --login-shadow: 0 28px 70px rgba(15, 23, 42, .16);
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
            color: var(--login-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(110deg, rgba(238, 243, 248, .95), rgba(238, 243, 248, .78)),
                url("https://image.freshnewsasia.com/2020/id-025/fn-2020-12-26-11-31-31-0.jpg") center / cover fixed;
        }

        button,
        input {
            font: inherit;
        }

        .login-shell {
            width: min(1120px, calc(100% - 32px));
            min-height: 100vh;
            margin: 0 auto;
            display: grid;
            align-items: center;
            padding: 32px 0;
        }

        .login-frame {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) 440px;
            min-height: 680px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .68);
            border-radius: 18px;
            background: rgba(255, 255, 255, .76);
            box-shadow: var(--login-shadow);
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
                url("{{ asset('images/bg-banner.jpg') }}") center / cover;
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

        .brand-copy {
            max-width: 560px;
        }

        .brand-copy h1 {
            margin: 0;
            max-width: 620px;
            font-size: clamp(34px, 5vw, 62px);
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
            background: var(--login-panel);
            padding: 42px;
        }

        .form-card {
            width: 100%;
        }

        .form-kicker {
            color: var(--login-primary-2);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .form-title {
            margin: 10px 0 8px;
            color: var(--login-text);
            font-size: 30px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .form-subtitle {
            margin: 0 0 26px;
            color: var(--login-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #f0b8b5;
            border-radius: 8px;
            color: var(--login-danger);
            background: var(--login-danger-bg);
            font-size: 13px;
            line-height: 1.45;
        }

        .alert[hidden] {
            display: none;
        }

        .field {
            margin-bottom: 17px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #314058;
            font-size: 13px;
            font-weight: 700;
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
            border: 1px solid var(--login-border);
            border-radius: 8px;
            color: var(--login-text);
            background: var(--login-panel-soft);
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .input-wrap input::placeholder {
            color: #9aa8ba;
        }

        .input-wrap input:focus {
            border-color: var(--login-primary-2);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .13);
        }

        .input-wrap input.invalid {
            border-color: var(--login-danger);
            box-shadow: 0 0 0 4px rgba(194, 65, 61, .1);
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
            color: var(--login-primary);
            background: rgba(30, 58, 138, .08);
        }

        .hint {
            display: none;
            margin-top: 6px;
            color: var(--login-danger);
            font-size: 12px;
        }

        .hint.show {
            display: block;
        }

        .submit-btn,
        .demo-btn {
            width: 100%;
            height: 48px;
            border-radius: 8px;
            font-weight: 800;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .submit-btn {
            border: 0;
            color: #fff;
            background: linear-gradient(135deg, var(--login-primary), var(--login-primary-2));
            box-shadow: 0 14px 24px rgba(37, 99, 235, .22);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 30px rgba(37, 99, 235, .26);
        }

        .demo-box {
            margin-top: 14px;
            padding: 14px;
            border: 1px solid var(--login-border);
            border-radius: 8px;
            background: #f8fafc;
        }

        .demo-box p {
            margin: 0 0 10px;
            color: var(--login-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .demo-btn {
            border: 1px solid #b7c6d9;
            color: var(--login-primary);
            background: #fff;
        }

        .demo-btn:hover {
            border-color: var(--login-primary-2);
            background: #eef5ff;
        }

        .form-footer {
            margin-top: 22px;
            color: var(--login-muted);
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
            color: var(--login-primary-2);
            font-weight: 800;
            text-decoration: none;
            background: rgba(37, 99, 235, .06);
        }

        .form-footer a:hover {
            border-color: rgba(37, 99, 235, .42);
            background: rgba(37, 99, 235, .1);
        }

        @media (max-width: 900px) {
            body {
                background:
                    linear-gradient(120deg, rgba(238, 243, 248, .94), rgba(238, 243, 248, .86)),
                    url("http://www.hru.edu.kh/wp-content/uploads/2023/08/350773696_210061804722310_6900832573841793976_n-745x400.jpg") center / cover fixed;
            }

            .login-shell {
                width: min(520px, calc(100% - 24px));
            }

            .login-frame {
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

        @media (max-width: 420px) {
            .login-shell {
                width: 100%;
                padding: 0;
            }

            .login-frame {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
            }

            .brand-panel {
                min-height: 190px;
            }

            .brand-logo {
                width: 48px;
                height: 48px;
            }

            .brand-logo img {
                width: 42px;
                height: 42px;
            }

            .brand-name strong {
                font-size: 19px;
            }

            .form-title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <section class="login-frame" aria-label="{{ __('auth.login_aria', ['app' => $appName]) }}">
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
                    <h1>{{ __('auth.hero_title') }}</h1>
                    <p>{{ __('auth.hero_description') }}</p>
                </div>

                <div class="status-row" aria-label="{{ __('auth.system_highlights') }}">
                    <div class="status-item">
                        <span>{{ __('auth.access') }}</span>
                        <strong>{{ __('auth.role_protected') }}</strong>
                    </div>
                    <div class="status-item">
                        <span>{{ __('auth.records') }}</span>
                        <strong>{{ __('auth.audit_logged') }}</strong>
                    </div>
                    <div class="status-item">
                        <span>{{ __('auth.backups') }}</span>
                        <strong>{{ __('auth.automated') }}</strong>
                    </div>
                </div>
            </aside>

            <div class="form-panel">
                <div class="form-card">
                    <div class="form-kicker">{{ __('auth.secure_sign_in') }}</div>
                    <h2 class="form-title">{{ __('auth.welcome_back') }}</h2>
                    <p class="form-subtitle">{{ __('auth.login_subtitle', ['app' => $appName]) }}</p>

                    @if ($errors->any())
                        <div class="alert">
                            @foreach ($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="alert" id="jsAlert" hidden></div>

                    <form action="{{ route('login.post') }}" method="POST" id="loginForm" novalidate>
                        @csrf

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
                                <input type="email" id="email" name="email"
                                    placeholder="{{ __('auth.email_placeholder') }}" value="{{ old('email') }}"
                                    autocomplete="email" required>
                            </div>
                            <div class="hint" id="hint-email">{{ __('auth.email_invalid') }}</div>
                        </div>

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
                                    placeholder="{{ __('auth.password_placeholder') }}" autocomplete="current-password"
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
                                        <path
                                            d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                        <path d="M6.61 6.61C3.54 8.33 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61" />
                                        <path d="m2 2 20 20" />
                                    </svg>
                                </button>
                            </div>
                            <div class="hint" id="hint-password">{{ __('auth.password_required') }}</div>
                        </div>

                        <button type="submit" class="submit-btn">{{ __('auth.sign_in') }}</button>
                    </form>

                    @if (config('auth.demo_login_enabled'))
                        <form action="{{ route('demo.login') }}" method="POST" class="demo-box">
                            @csrf
                            <p>{{ __('auth.demo_description') }}</p>
                            <button type="submit" class="demo-btn">{{ __('auth.try_demo') }}</button>
                        </form>
                    @endif

                    @if (config('auth.public_registration_enabled'))
                        <div class="form-footer">
                            {{ __('auth.need_account') }} <a
                                href="{{ route('register') }}">{{ __('auth.create_account') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>

    @php
        $authText = [
            'showPassword' => __('auth.show_password'),
            'hidePassword' => __('auth.hide_password'),
            'emailRequired' => __('auth.email_required'),
            'emailInvalid' => __('auth.email_invalid'),
            'passwordRequired' => __('auth.password_required'),
        ];
    @endphp

    <script>
        const authText = @json($authText);

        document.querySelectorAll('.password-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.dataset.target);
                var isText = input.type === 'text';
                input.type = isText ? 'password' : 'text';
                btn.querySelector('.eye-open').hidden = !isText;
                btn.querySelector('.eye-closed').hidden = isText;
                btn.setAttribute('aria-label', isText ? authText.showPassword : authText.hidePassword);
            });
        });

        function setFieldState(input, state) {
            input.classList.remove('invalid');
            if (state === 'invalid') {
                input.classList.add('invalid');
            }
        }

        function showHint(id, msg) {
            var el = document.getElementById(id);
            el.textContent = msg;
            el.classList.add('show');
        }

        function clearHint(id) {
            document.getElementById(id).classList.remove('show');
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            var errors = [];
            var emailEl = document.getElementById('email');
            var pwEl = document.getElementById('password');
            var emailVal = emailEl.value.trim();
            var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);

            if (!emailVal) {
                setFieldState(emailEl, 'invalid');
                showHint('hint-email', authText.emailRequired);
                errors.push(authText.emailRequired);
            } else if (!emailOk) {
                setFieldState(emailEl, 'invalid');
                showHint('hint-email', authText.emailInvalid);
                errors.push(authText.emailInvalid);
            } else {
                setFieldState(emailEl);
                clearHint('hint-email');
            }

            if (!pwEl.value) {
                setFieldState(pwEl, 'invalid');
                showHint('hint-password', authText.passwordRequired);
                errors.push(authText.passwordRequired);
            } else {
                setFieldState(pwEl);
                clearHint('hint-password');
            }

            var alert = document.getElementById('jsAlert');
            if (errors.length) {
                e.preventDefault();
                alert.hidden = false;
                alert.innerHTML = errors.map(function(error) {
                    return '<div>' + error + '</div>';
                }).join('');
            } else {
                alert.hidden = true;
                alert.innerHTML = '';
            }
        });
    </script>

    @include('partials.legacy-translator')
</body>

</html>
