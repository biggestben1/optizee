<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $brandLogoPath = public_path('logo.jpg');
        $brandLogoVer = is_file($brandLogoPath) ? (string) filemtime($brandLogoPath) : '1';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Optizee Staff">
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}">
    <title>Staff Login - Optizee</title>
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --accent: #0ea5e9;
            --border: rgba(148, 163, 184, 0.2);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100dvh;
            background:
                radial-gradient(circle at top, rgba(14, 165, 233, 0.25), transparent 40%),
                linear-gradient(180deg, #0f172a 0%, #020617 100%);
            color: var(--text);
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .wrap {
            width: 100%;
            max-width: 420px;
        }
        .brand {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand img {
            width: 96px;
            height: 96px;
            object-fit: contain;
            border-radius: 20px;
            background: rgba(255,255,255,0.06);
            padding: 8px;
        }
        .brand h1 {
            margin: 14px 0 4px;
            font-size: 1.45rem;
        }
        .brand p { margin: 0; color: var(--muted); }
        .card {
            background: rgba(30, 41, 59, 0.92);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }
        .tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 18px;
        }
        .tab {
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            border-radius: 12px;
            padding: 10px;
            font-weight: 700;
            cursor: pointer;
        }
        .tab.active {
            background: rgba(14, 165, 233, 0.15);
            color: #7dd3fc;
            border-color: rgba(14, 165, 233, 0.4);
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }
        input {
            width: 100%;
            border: 1px solid var(--border);
            background: #0f172a;
            color: var(--text);
            border-radius: 12px;
            padding: 14px 12px;
            margin-bottom: 14px;
        }
        .btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px;
            font-weight: 800;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
            cursor: pointer;
        }
        .errors {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fecaca;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }
        .panel { display: none; }
        .panel.active { display: block; }
        .hint {
            text-align: center;
            margin-top: 16px;
            color: var(--muted);
            font-size: 0.82rem;
        }
        .code-row {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }
        .code-row input {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 0.35em;
            font-weight: 800;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img src="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}" alt="Optizee">
        <h1>Optizee Staff</h1>
        <p>Cashier & Kitchen mobile app</p>
    </div>

    <div class="card">
        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="tabs">
            <button type="button" class="tab active" data-tab="code">Quick Code</button>
            <button type="button" class="tab" data-tab="email">Email Login</button>
        </div>

        <form id="panel-code" class="panel active" method="POST" action="{{ route('app.login.quick') }}">
            @csrf
            <label>4-digit staff code</label>
            <div class="code-row">
                <input type="text" name="login_code" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" value="{{ old('login_code') }}" placeholder="••••" required autofocus>
            </div>
            <button class="btn" type="submit">Sign in</button>
        </form>

        <form id="panel-email" class="panel" method="POST" action="{{ route('app.login') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <label>Password</label>
            <input type="password" name="password" required autocomplete="current-password">
            <button class="btn" type="submit">Sign in</button>
        </form>
    </div>

    <p class="hint">Cashiers open POS. Kitchen staff open live orders.</p>
</div>

<script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
        });
    });
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    }
</script>
</body>
</html>
