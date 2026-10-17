<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Super Administration') — DocScolaire® SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <meta name="theme-color" content="#edece8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/modern-ui.css?v={{ time() }}">
    <script>
        const savedTheme = localStorage.getItem('forma_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        :root {
            /* Palette Signature Forma (Mode Clair par défaut) */
            --canvas-bg: #edece8;
            --canvas-card: rgba(255, 255, 255, 0.75);
            --canvas-card-solid: #ffffff;
            --canvas-subtle: rgba(0, 0, 0, 0.035);
            
            --ink-primary: #141414;
            --ink-secondary: #524f4b;
            --ink-muted: #8a857b;
            
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.14);
            
            --sidebar-bg: rgba(247, 246, 242, 0.92);
            --sidebar-border: var(--hairline);
            --sidebar-hover: rgba(0, 0, 0, 0.04);
            --sidebar-active-bg: #141414;
            --sidebar-active-text: #ffffff;
            --sidebar-text: var(--ink-secondary);
            --sidebar-text-hover: var(--ink-primary);
            --sidebar-title: var(--ink-muted);
            --sidebar-footer-bg: rgba(238, 237, 232, 0.75);
            
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: rgba(22, 22, 21, 0.82);
            --canvas-card-solid: #171716;
            --canvas-subtle: rgba(255, 255, 255, 0.04);
            
            --ink-primary: #f5f4f0;
            --ink-secondary: #a8a49c;
            --ink-muted: #6e6b63;
            
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
            
            --sidebar-bg: rgba(18, 18, 17, 0.92);
            --sidebar-border: var(--hairline);
            --sidebar-hover: rgba(255, 255, 255, 0.06);
            --sidebar-active-bg: #f5f4f0;
            --sidebar-active-text: #141414;
            --sidebar-text: var(--ink-secondary);
            --sidebar-text-hover: #ffffff;
            --sidebar-title: var(--ink-muted);
            --sidebar-footer-bg: rgba(12, 12, 11, 0.7);
        }

        body {
            font-family: var(--font-sans);
            background: var(--canvas-bg);
            color: var(--ink-primary);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── Sidebar Forma SuperAdmin ── */
        aside.super-sidebar {
            background: var(--sidebar-bg);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 40;
            color: var(--ink-primary);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .super-brand-header {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .super-brand-title {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ink-primary);
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.035em;
        }
        .super-brand-title span { color: var(--ink-muted); font-size: 13px; font-weight: 600; }
        .super-brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background: var(--ink-primary);
            color: var(--canvas-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
        }

        .super-badge-chip {
            font-family: var(--font-mono);
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--ink-secondary);
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline-strong);
            padding: 3px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        nav.super-nav {
            flex: 1;
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }
        .super-nav-section {
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sidebar-title);
            padding: 16px 12px 6px;
        }
        .super-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .super-nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-hover);
        }
        .super-nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text) !important;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .super-sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
            background: var(--sidebar-footer-bg);
        }
        .btn-super-logout {
            width: 100%;
            padding: 8px 14px;
            background: transparent;
            border: 1px solid var(--hairline-strong);
            border-radius: 9999px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-secondary);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: center;
        }
        .btn-super-logout:hover {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        /* ── Zone Centrale SuperAdmin ── */
        .super-main-wrap {
            overflow-y: auto;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--canvas-bg);
        }

        .super-topbar {
            background: rgba(237, 236, 232, 0.85);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-bottom: 1px solid var(--hairline);
            padding: 0 clamp(20px, 3vw, 40px);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        [data-theme="dark"] .super-topbar {
            background: rgba(14, 14, 13, 0.85);
            border-bottom-color: var(--hairline);
        }

        .topbar-status-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-primary);
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            padding: 6px 14px;
            border-radius: 9999px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .theme-toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.05);
            border-color: var(--ink-primary);
        }

        main.super-content {
            padding: clamp(24px, 3vw, 40px);
            flex: 1;
            max-width: 1400px;
            width: 100%;
        }

        .forma-alert {
            background: var(--canvas-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--hairline-strong);
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--ink-primary);
            margin-bottom: 24px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.03);
        }
        .forma-alert-success {
            border-color: rgba(16, 185, 129, 0.35);
        }
        .forma-alert-error {
            border-color: rgba(239, 68, 68, 0.35);
        }

        @media (max-width: 900px) {
            body { grid-template-columns: 1fr; }
            aside.super-sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar SuperAdmin -->
    <aside class="super-sidebar">
        <div class="super-brand-header">
            <a href="{{ route('superadmin.dashboard') }}" class="super-brand-title">
                <div class="super-brand-icon">⚡</div>
                <div>DocScolaire<span>.Master</span></div>
            </a>
            <span class="super-badge-chip">Owner</span>
        </div>

        <nav class="super-nav">
            <span class="super-nav-section">Cockpit Multi-Tenants</span>
            <a href="{{ route('superadmin.dashboard') }}" class="super-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <span>📊 Dashboard Global</span>
            </a>
            <a href="{{ route('superadmin.statistiques') }}" class="super-nav-link {{ request()->routeIs('superadmin.statistiques') ? 'active' : '' }}">
                <span>📈 Statistiques &amp; Métriques</span>
            </a>

            <span class="super-nav-section">Réseau Établissements</span>
            <a href="{{ route('superadmin.ecoles.index') }}" class="super-nav-link {{ request()->routeIs('superadmin.ecoles.*') ? 'active' : '' }}">
                <span>🏫 Établissements Partenaires</span>
            </a>
            <a href="{{ route('superadmin.etudiants.index') }}" class="super-nav-link {{ request()->routeIs('superadmin.etudiants.*') ? 'active' : '' }}">
                <span>🎓 Annuaire Global Étudiants</span>
            </a>
            <a href="{{ route('superadmin.ecoles.create') }}" class="super-nav-link {{ request()->routeIs('superadmin.ecoles.create') ? 'active' : '' }}">
                <span>➕ Onboarder une École</span>
            </a>

            <span class="super-nav-section">Gouvernance Plateforme</span>
            <a href="{{ route('superadmin.logs') }}" class="super-nav-link {{ request()->routeIs('superadmin.logs') ? 'active' : '' }}">
                <span>📜 Audit Global &amp; Sécurité</span>
            </a>
        </nav>

        <div class="super-sidebar-footer">
            <form action="{{ route('superadmin.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-super-logout">Déconnexion Master &rarr;</button>
            </form>
        </div>
    </aside>

    <!-- Zone Centrale SuperAdmin -->
    <div class="super-main-wrap">
        <header class="super-topbar">
            <div>
                <h2 style="font-size:18px;font-weight:900;color:var(--ink-primary);letter-spacing:-0.035em;">@yield('page_title', 'Super Administration')</h2>
            </div>

            <div style="display:flex;align-items:center;gap:12px;">
                <button class="cmdk-trigger" data-cmdk-trigger title="Ouvrir le lanceur rapide (Cmd+K)">
                    <span>Recherche</span>
                    <kbd class="cmdk-kbd">⌘K</kbd>
                </button>

                <div class="topbar-status-pulse">
                    <span class="beacon-dot" style="background:#10b981;"></span>
                    <span>Plateforme Opérationnelle</span>
                </div>

                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Basculer le thème">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <main class="super-content">
            @if(session('success'))
                <div class="forma-alert forma-alert-success">
                    <span class="beacon-dot" style="background:#10b981;"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="forma-alert forma-alert-error">
                    <span class="beacon-dot" style="background:#ef4444;"></span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Palette de Commande Universelle (Cmd+K / Ctrl+K) -->
    <div id="cmdk-backdrop" class="cmdk-backdrop">
        <div class="cmdk-modal">
            <div class="cmdk-input-row">
                <span style="color:#c084fc;font-size:18px;">⌘</span>
                <input type="text" id="cmdk-input" class="cmdk-input" placeholder="Action rapide SuperAdmin..." autocomplete="off">
                <kbd class="cmdk-kbd" style="color:#94a3b8;">ESC</kbd>
            </div>
            <div class="cmdk-list">
                <a href="{{ route('superadmin.dashboard') }}" class="cmdk-item">
                    <span>📊 Dashboard Global SaaS</span>
                    <span style="font-size:11px;color:#c084fc;">Cockpit</span>
                </a>
                <a href="{{ route('superadmin.ecoles.index') }}" class="cmdk-item">
                    <span>🏫 Gestion des Établissements Partenaires</span>
                    <span style="font-size:11px;color:#c084fc;">Tenants</span>
                </a>
                <a href="{{ route('superadmin.etudiants.index') }}" class="cmdk-item">
                    <span>🎓 Annuaire Global Étudiants</span>
                    <span style="font-size:11px;color:#c084fc;">Utilisateurs</span>
                </a>
                <a href="{{ route('superadmin.ecoles.create') }}" class="cmdk-item">
                    <span>➕ Créer et Onboarder une Nouvelle École</span>
                    <span style="font-size:11px;color:#c084fc;">Nouveau</span>
                </a>
                <a href="{{ route('superadmin.logs') }}" class="cmdk-item">
                    <span>📜 Journal d'Audit Sécurité Plateforme</span>
                    <span style="font-size:11px;color:#c084fc;">Audit</span>
                </a>
            </div>
        </div>
    </div>

    <script src="/js/modern-ui.js"></script>
    <script>
        const themeBtn = document.getElementById('themeToggleBtn');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme') || 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('forma_theme', next);
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
