<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi kendala — Etalase')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formal.css') }}">
    <style>
        .error-shell {
            width: min(680px, calc(100% - 40px));
            margin: 70px auto 80px;
        }
        .error-card {
            background: var(--white);
            border: 1px solid #c8ced3;
            box-shadow: 0 14px 38px rgba(28, 41, 52, .07);
            overflow: hidden;
        }
        .error-card .section-code {
            display: block;
            padding: 15px 26px;
            color: #e8edf0;
            background: #13293d;
            letter-spacing: .11em;
        }
        .error-body {
            padding: clamp(32px, 6vw, 58px);
        }
        .error-code {
            margin: 0;
            color: var(--blue);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(72px, 12vw, 128px);
            font-weight: 400;
            line-height: 1;
            letter-spacing: -.045em;
        }
        .error-body h1 {
            max-width: 520px;
            margin: 18px 0 14px;
            color: var(--blue);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(24px, 3.4vw, 34px);
            font-weight: 400;
            line-height: 1.15;
            letter-spacing: -.02em;
        }
        .error-body > p {
            max-width: 480px;
            margin: 0 0 32px;
            color: var(--gray);
            font-size: 14px;
            line-height: 1.7;
        }
        .error-actions {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-top: 32px;
        }
        .error-hint {
            margin: 44px 0 0;
            padding-top: 20px;
            color: var(--gray);
            border-top: 1px solid var(--line);
            font-family: "Courier New", monospace;
            font-size: 10px;
            line-height: 1.7;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        @media (max-width: 520px) {
            .error-shell { width: calc(100% - 24px); margin-top: 36px; }
            .error-body { padding: 30px 22px; }
            .error-actions { align-items: stretch; flex-direction: column; gap: 14px; }
            .error-actions .fit { width: 100%; }
        }
    </style>
</head>
<body class="error-page">
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Etalase, halaman katalog">
            <span class="brand-mark" aria-hidden="true">E</span>
            <span class="brand-name">ETALASE</span>
        </a>
    </header>

    <main>
        <section class="error-shell">
            <div class="error-card">
                <span class="section-code">ETALASE / {{ strtoupper($__env->yieldContent('code', 'ERROR')) }}</span>
                <div class="error-body">
                    <h2 class="error-code">@yield('code', 'Error')</h2>
                    <h1>@yield('message', 'Terjadi kendala')</h1>
                    <p>@yield('description', 'Halaman tidak dapat dimuat. Silakan coba lagi atau kembali ke halaman utama.')</p>
                    <div class="error-actions">
                        <a class="primary-button button-link fit" href="{{ route('home') }}">Kembali ke katalog</a>
                        <a class="text-link" href="{{ route('home') }}">Halaman utama</a>
                    </div>
                    @hasSection('hint')
                        <p class="error-hint">@yield('hint')</p>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <span>ETALASE / TOKO ONLINE</span>
        <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
    </footer>
</body>
</html>
