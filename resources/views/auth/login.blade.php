<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6F0D0D">
    <title>Masuk | PT. Halus Ciptanadi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- CSS khusus login. Bisa dipindahkan ke app.css setelah desain disetujui. --}}
    <style>
        body.login-page {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            background: #6f0d0d;
            color: #111111;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .hc-auth-main {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 28px 18px;
            box-sizing: border-box;
        }

        .hc-auth-card {
            box-sizing: border-box;
            width: 100%;
            max-width: 506px;
            padding: 60px 76px 80px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 16px 38px rgba(31, 5, 5, .12);
        }

        .hc-auth-heading {
            margin-bottom: 40px;
            text-align: center;
        }

        .hc-auth-mark {
            display: block;
            width: 126px;
            height: 70px;
            margin: 0 auto 12px;
            color: #e60012;
        }

        .hc-auth-title {
            margin: 0;
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 750;
            line-height: 1.2;
            letter-spacing: -.045em;
            color: #0c0c0c;
            white-space: nowrap;
        }

        .hc-auth-subtitle {
            margin: 4px 0 0;
            font-size: 18px;
            line-height: 1.45;
            color: #151515;
        }

        .hc-auth-field + .hc-auth-field { margin-top: 22px; }

        .hc-auth-label {
            display: block;
            margin-bottom: 8px;
            color: #121212;
            font-size: 15px;
            line-height: 1.4;
            font-weight: 650;
        }

        .hc-auth-input-wrap { position: relative; }

        .hc-auth-input {
            box-sizing: border-box;
            display: block;
            width: 100%;
            min-height: 48px;
            padding: 11px 46px 11px 41px;
            border: 1px solid #bababa;
            border-radius: 13px;
            background: #fff;
            color: #151515;
            font: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .hc-auth-input::placeholder { color: #aaa9ad; }
        .hc-auth-input:focus-visible {
            border-color: #7a1111;
            box-shadow: 0 0 0 3px rgba(111, 13, 13, .13);
        }

        .hc-auth-input[aria-invalid="true"] { border-color: #b91c1c; }

        .hc-auth-input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #222733;
            pointer-events: none;
        }

        .hc-auth-password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            display: grid;
            place-items: center;
            width: 33px;
            height: 33px;
            padding: 5px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #737b8e;
            cursor: pointer;
        }

        .hc-auth-password-toggle:hover { background: #f4f4f4; color: #6f0d0d; }
        .hc-auth-password-toggle:focus-visible { outline: 2px solid #6f0d0d; outline-offset: 2px; }
        .hc-auth-password-toggle svg { width: 21px; height: 21px; }
        .hc-auth-password-toggle .hc-icon-hide { display: none; }
        .hc-auth-password-toggle[aria-pressed="true"] .hc-icon-show { display: none; }
        .hc-auth-password-toggle[aria-pressed="true"] .hc-icon-hide { display: block; }

        .hc-auth-error { margin: 6px 0 0; font-size: 12px; color: #b91c1c; }
        .hc-auth-actions { margin-top: 66px; }

        .hc-auth-card .login-submit {
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 45px;
            padding: 10px 16px;
            border: 0;
            border-radius: 12px;
            background: #b1070c;
            color: #fff;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.4;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(71, 0, 0, .10);
            transition: background .15s ease, transform .15s ease;
        }

        .hc-auth-card .login-submit:hover:not(:disabled) { background: #8f080c; }
        .hc-auth-card .login-submit:focus-visible { outline: 3px solid rgba(177, 7, 12, .35); outline-offset: 3px; }
        .hc-auth-card .login-submit:disabled { opacity: .7; cursor: wait; }
        .hc-auth-card .login-submit:active:not(:disabled) { transform: translateY(1px); }

        @media (max-width: 560px) {
            .hc-auth-main { padding: 20px 16px; }
            .hc-auth-card { padding: 44px 26px 54px; }
            .hc-auth-title { font-size: clamp(21px, 6vw, 28px); white-space: normal; }
            .hc-auth-subtitle { font-size: 15px; }
            .hc-auth-heading { margin-bottom: 34px; }
            .hc-auth-actions { margin-top: 45px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hc-auth-input, .hc-auth-card .login-submit { transition: none; }
        }
    </style>
</head>
<body class="login-page">
    <a class="skip-link" href="#login-form">Lewati ke formulir masuk</a>

    <main class="hc-auth-main">
        <section class="hc-auth-card" aria-labelledby="login-title">
            <div class="hc-auth-heading">
                {{-- Emblem dekoratif sementara; ganti dengan aset logo resmi bila tersedia. --}}
                <svg class="hc-auth-mark" viewBox="0 0 130 72" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 53c12-19 17-9 24-16 5-5 6-15 13-18-2 14 5 15 11 9 6-7 10-14 11-26 14 11 8 22 2 29 10 0 17-9 20-20 12 13 2 23-3 27 10 2 19-9 27-15-1 12-6 22-17 26 14 7 20-4 31-4-4 17-17 18-28 14-15-5-22-11-35-12-13-2-20 4-27 11-10 10-22 12-29-5Z" fill="currentColor"/>
                </svg>
                <h1 id="login-title" class="hc-auth-title">PT. Halus Ciptanadi</h1>
                <p class="hc-auth-subtitle">Sistem Informasi Penjualan</p>
            </div>

            @if ($errors->has('login'))
                <x-ui.alert type="error" class="mb-6">{{ $errors->first('login') }}</x-ui.alert>
            @endif

            <form id="login-form" action="{{ route('login.process') }}" method="POST" data-submit-state tabindex="-1">
                @csrf
                <div class="hc-auth-field">
                    <label class="hc-auth-label" for="username">Username</label>
                    <div class="hc-auth-input-wrap">
                        <svg class="hc-auth-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.6"/>
                            <path d="M15.6 8.7v5.2c0 1.2 1.7 1.6 2.5.7M15.6 11.2a3.3 3.3 0 1 0-3.3 3.3 3.3 3.3 0 0 0 3.3-3.3Z" transform="translate(0 2)"/>
                        </svg>
                        <input class="hc-auth-input" id="username" name="username" type="text" value="{{ old('username') }}"
                            placeholder="Masukkan Username..." autocomplete="username" autocapitalize="none" spellcheck="false"
                            @error('username') aria-invalid="true" aria-describedby="username-error" @enderror required>
                    </div>
                    @error('username')
                        <p class="hc-auth-error" id="username-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hc-auth-field">
                    <label class="hc-auth-label" for="password">Kata Sandi</label>
                    <div class="hc-auth-input-wrap">
                        <svg class="hc-auth-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="11" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input class="hc-auth-input" id="password" name="password" type="password"
                            placeholder="Masukkan Kata Sandi..." autocomplete="current-password"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror required>
                        <button class="hc-auth-password-toggle" type="button" data-password-toggle
                            aria-label="Tampilkan kata sandi" aria-controls="password" aria-pressed="false">
                            <svg class="hc-icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="hc-icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3l18 18M10.6 6.1c.5-.1 1-.1 1.4-.1 6.1 0 9.5 6 9.5 6a15 15 0 0 1-3 3.5M6.2 6.3A16 16 0 0 0 2.5 12s3.4 6 9.5 6c1.2 0 2.3-.2 3.3-.6"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="hc-auth-error" id="password-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hc-auth-actions">
                    <x-ui.button type="submit" loading-text="Sedang masuk…" class="login-submit">Masuk</x-ui.button>
                </div>
                <p class="sr-only" role="status" data-submit-status></p>
            </form>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-password-toggle]');
            const password = document.getElementById('password');
            if (!toggle || !password) return;

            toggle.addEventListener('click', () => {
                const visible = password.type === 'password';
                password.type = visible ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(visible));
                toggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        });
    </script>
</body>
</html>
