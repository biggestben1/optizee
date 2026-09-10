<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $brandLogoPath = public_path('logo.jpg');
        $brandLogoVer = is_file($brandLogoPath) ? (string) filemtime($brandLogoPath) : '1';
        $isKitchen = auth()->check() && auth()->user()->isKitchen();
        $canPos = auth()->check() && (auth()->user()->canAccessPOS() || auth()->user()->is_admin);
        $canKitchen = auth()->check() && auth()->user()->canAccessKitchen();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Optizee Staff">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}">
    <title>@yield('title', 'Optizee Staff')</title>
    <style>
        :root {
            --bg: #0f172a;
            --bg-2: #1e293b;
            --bg-3: #334155;
            --card: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --accent-2: #0ea5e9;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: rgba(148, 163, 184, 0.2);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --nav-h: 64px;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .app-shell {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding-bottom: calc(var(--nav-h) + var(--safe-bottom));
        }
        .app-shell.no-nav { padding-bottom: 0; }
        .app-top {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }
        .app-top h1 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .app-top .sub {
            color: var(--muted);
            font-size: 0.78rem;
        }
        .app-content {
            flex: 1;
            padding: 12px 14px 20px;
        }
        .app-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 50;
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: 1fr;
            height: calc(var(--nav-h) + var(--safe-bottom));
            padding-bottom: var(--safe-bottom);
            background: rgba(15, 23, 42, 0.96);
            border-top: 1px solid var(--border);
            backdrop-filter: blur(12px);
        }
        .app-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 600;
        }
        .app-nav a.active { color: var(--accent); }
        .app-nav svg { width: 22px; height: 22px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 700;
            cursor: pointer;
            background: var(--bg-3);
            color: var(--text);
        }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }
        .btn-primary { background: linear-gradient(135deg, var(--accent-2), #0284c7); color: #fff; }
        .btn-success { background: var(--success); color: #052e16; }
        .btn-warning { background: var(--warning); color: #422006; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-block { width: 100%; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px;
        }
        .muted { color: var(--muted); }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            background: var(--bg-3);
        }
        .badge-success { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .badge-warning { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .badge-info { background: rgba(56, 189, 248, 0.2); color: #7dd3fc; }
        .toast {
            position: fixed;
            left: 50%;
            bottom: calc(var(--nav-h) + var(--safe-bottom) + 16px);
            transform: translateX(-50%) translateY(120%);
            z-index: 80;
            background: #022c22;
            color: #ecfdf5;
            border: 1px solid rgba(16, 185, 129, 0.4);
            padding: 12px 16px;
            border-radius: 12px;
            opacity: 0;
            transition: .25s ease;
            max-width: calc(100% - 32px);
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.error { background: #450a0a; border-color: rgba(239, 68, 68, 0.4); color: #fee2e2; }
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-2);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        @yield('styles')
    </style>
</head>
<body>
<div class="app-shell @yield('shell_class')">
    @hasSection('top')
        <header class="app-top">
            @yield('top')
        </header>
    @endif

    <main class="app-content">
        @yield('content')
    </main>

    @auth
        @unless($isKitchen && !$canPos)
            <nav class="app-nav">
                @if($canPos)
                    <a href="{{ route('app.pos') }}" class="{{ request()->routeIs('app.pos') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                        POS
                    </a>
                @endif
                @if($canKitchen)
                    <a href="{{ route('app.kitchen') }}" class="{{ request()->routeIs('app.kitchen') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h16v10H4zM8 10V6a4 4 0 018 0v4"/></svg>
                        Kitchen
                    </a>
                @endif
                <form method="POST" action="{{ route('app.logout') }}">
                    @csrf
                    <button type="submit" class="btn" style="background:transparent;color:var(--muted);width:100%;height:100%;border-radius:0;flex-direction:column;gap:4px;font-size:0.72rem;">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Logout
                    </button>
                </form>
            </nav>
        @else
            <nav class="app-nav">
                <a href="{{ route('app.kitchen') }}" class="active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h16v10H4zM8 10V6a4 4 0 018 0v4"/></svg>
                    Orders
                </a>
                <form method="POST" action="{{ route('app.logout') }}">
                    @csrf
                    <button type="submit" class="btn" style="background:transparent;color:var(--muted);width:100%;height:100%;border-radius:0;flex-direction:column;gap:4px;font-size:0.72rem;">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Logout
                    </button>
                </form>
            </nav>
        @endunless
    @endauth
</div>

<div id="app-toast" class="toast"></div>

<script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    function showToast(message, isError = false) {
        const el = document.getElementById('app-toast');
        el.textContent = message;
        el.classList.toggle('error', !!isError);
        el.classList.add('show');
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
    }
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    }
</script>
@yield('scripts')
</body>
</html>
