<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $branding = $branding ?? [];
        $appName = $branding['app_name'] ?? config('app.name', 'HRU ATS');
        $appSub = $branding['app_sub'] ?? __('auth.login_subtitle', ['app' => $appName]);
        $appLogo = $branding['app_logo'] ?? 'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.forgot_password_title', ['app' => $appName]) }}</title>
    @vite(['resources/css/app.css'])
    <link rel="icon" href="{{ $appLogo }}" type="image/png" sizes="32x32" />
    <style>
        :root{--panel:#fff;--soft:#f6f8fb;--border:#dbe3ee;--text:#142033;--muted:#66758a;--primary:#1e3a8a;--primary2:#2563eb;--danger:#c2413d;--danger-bg:#fff1f1;--success:#0f9f8f}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(110deg,rgba(238,243,248,.95),rgba(238,243,248,.78)),url("https://image.freshnewsasia.com/2020/id-025/fn-2020-12-26-11-31-31-0.jpg") center/cover fixed}
        .card{width:min(460px,100%);border:1px solid rgba(255,255,255,.68);border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 28px 70px rgba(15,23,42,.16);overflow:hidden}
        .brand{display:flex;align-items:center;gap:12px;padding:22px;border-bottom:1px solid var(--border);background:var(--soft)}
        .brand img{width:46px;height:46px;object-fit:contain;border-radius:10px;background:#fff}.brand strong{display:block;font-size:20px}.brand span{display:block;color:var(--muted);font-size:12px}
        .body{padding:26px}.kicker{color:var(--primary2);font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.title{margin:8px 0;color:var(--text);font-size:28px;line-height:1.15}.subtitle{margin:0 0 22px;color:var(--muted);font-size:14px;line-height:1.55}
        .alert{margin-bottom:16px;padding:12px 14px;border:1px solid #f0b8b5;border-radius:8px;color:var(--danger);background:var(--danger-bg);font-size:13px;line-height:1.45}.alert.ok{border-color:rgba(15,159,143,.3);background:rgba(15,159,143,.08);color:var(--success)}.alert[hidden]{display:none}
        label{display:block;margin-bottom:7px;color:#314058;font-size:13px;font-weight:700}.input{width:100%;height:48px;padding:0 14px;border:1px solid var(--border);border-radius:8px;color:var(--text);background:var(--soft);outline:none}.input:focus{border-color:var(--primary2);background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,.13)}
        .button{width:100%;height:48px;margin-top:16px;border:0;border-radius:8px;color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary2));font-weight:800;cursor:pointer}.button:disabled{cursor:not-allowed;opacity:.65}
        .links{margin-top:18px;text-align:center;color:var(--muted);font-size:13px}.links a{color:var(--primary2);font-weight:800;text-decoration:none}
    </style>
</head>

<body>
    <main class="card">
        <div class="brand">
            <img src="{{ $appLogo }}" alt="{{ $appName }}">
            <div><strong>{{ $appName }}</strong><span>{{ $appSub }}</span></div>
        </div>
        <section class="body">
            <div class="kicker">{{ __('auth.password_reset') }}</div>
            <h1 class="title">{{ __('auth.forgot_password_heading') }}</h1>
            <p class="subtitle">{{ __('auth.forgot_password_desc') }}</p>

            <div class="alert" id="resetAlert" hidden></div>
            <div class="alert ok" id="resetSuccess" hidden></div>

            <form id="forgotPasswordForm" novalidate>
                @csrf
                <label for="email">{{ __('auth.email_address') }}</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('auth.email_placeholder') }}" autocomplete="email" required>
                <button class="button" id="sendResetCodeButton" type="submit" data-url="{{ route('password.email-otp') }}">{{ __('auth.send_reset_code') }}</button>
            </form>

            <div class="links">
                <a href="{{ route('login') }}">{{ __('auth.back_to_login') }}</a>
            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const email = document.getElementById('email');
        const button = document.getElementById('sendResetCodeButton');
        const alertBox = document.getElementById('resetAlert');
        const successBox = document.getElementById('resetSuccess');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            alertBox.hidden = true;
            successBox.hidden = true;
            button.disabled = true;
            button.textContent = @json(__('auth.email_otp_sending'));

            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ email: email.value.trim() }),
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    const errors = data.errors ? Object.values(data.errors).flat() : [data.message || @json(__('auth.reset_code_failed'))];
                    throw new Error(errors.join('<br>'));
                }

                successBox.innerHTML = '<div>' + data.message + '</div>';
                successBox.hidden = false;
                window.location.href = data.reset_url;
            } catch (error) {
                alertBox.innerHTML = '<div>' + (error.message || @json(__('auth.reset_code_failed'))) + '</div>';
                alertBox.hidden = false;
                button.disabled = false;
                button.textContent = @json(__('auth.send_reset_code'));
            }
        });
    </script>
</body>

</html>
