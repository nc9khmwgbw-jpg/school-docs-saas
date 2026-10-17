<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Administration') — DocScolaire®</title>
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
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Sidebar Forma Frosted Paper / Obsidian ── */
        aside.admin-sidebar {
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

        .sidebar-brand-box {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ink-primary);
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.035em;
            line-height: 1.2;
        }

        .sidebar-tenant-badge {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--ink-secondary);
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline-strong);
            padding: 3px 8px;
            border-radius: 9999px;
            font-weight: 700;
            letter-spacing: 0.06em;
            flex-shrink: 0;
        }

        /* Nav links */
        nav.sidebar-nav {
            flex: 1;
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }
        .nav-group-title {
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sidebar-title);
            padding: 16px 12px 6px;
        }
        .nav-link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .nav-link-item:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-hover);
        }
        .nav-link-item.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text) !important;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .nav-badge-pill {
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 9999px;
            background: #ef4444;
            color: #ffffff;
        }
        .nav-link-item.active .nav-badge-pill {
            background: rgba(239, 68, 68, 0.9);
        }

        /* Sidebar User Footer */
        .sidebar-footer-box {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: var(--sidebar-footer-bg);
        }
        .user-profile-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar-badge {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            background: var(--ink-primary);
            color: var(--canvas-bg);
            border: 1px solid var(--hairline-strong);
            font-weight: 800;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .user-text-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .user-fullname {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }
        .user-subrole {
            font-family: var(--font-mono);
            font-size: 9.5px;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
        }
        .btn-sidebar-logout {
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
        .btn-sidebar-logout:hover {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        /* ── Zone Centrale de Contenu ── */
        .admin-main-wrap {
            overflow-y: auto;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--canvas-bg);
        }

        .admin-topbar {
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
        [data-theme="dark"] .admin-topbar {
            background: rgba(14, 14, 13, 0.85);
            border-bottom-color: var(--hairline);
        }

        .topbar-left-col {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-heading {
            font-size: 18px;
            font-weight: 800;
            color: var(--ink-primary);
            letter-spacing: -0.035em;
        }
        .topbar-heading em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
        }

        .topbar-right-col {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-school-tag {
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
            box-shadow: var(--shadow-soft);
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
            box-shadow: var(--shadow-soft);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.05);
            border-color: var(--ink-primary);
        }

        main.admin-content {
            padding: clamp(24px, 3vw, 40px);
            flex: 1;
            max-width: 1400px;
            width: 100%;
        }

        /* ── Alertes Forma Éditoriales ── */
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
            box-shadow: var(--shadow-soft);
        }
        .forma-alert-success {
            border-color: rgba(16, 185, 129, 0.35);
        }
        .forma-alert-error {
            border-color: rgba(239, 68, 68, 0.35);
        }

        /* Forcer les boutons primaires à adopter le style Forma Velvet Carbon */
        .btn-modern-primary {
            background: var(--ink-primary) !important;
            color: var(--canvas-bg) !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            letter-spacing: -0.01em !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .btn-modern-primary:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.16) !important;
            opacity: 0.92 !important;
        }
        [data-theme="dark"] .btn-modern-primary {
            background: #f5f4f0 !important;
            color: #141414 !important;
        }

        @media (max-width: 900px) {
            body { grid-template-columns: 1fr; }
            aside.admin-sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Administrateur École -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand-box">
            @if(auth()->user()->ecole && auth()->user()->ecole->logo_url)
                <img src="{{ auth()->user()->ecole->logo_url }}" alt="Logo {{ auth()->user()->ecole->nom }}" style="max-height:36px;max-width:140px;object-fit:contain;margin-bottom:8px;display:block;">
            @endif
            <a href="{{ route('admin_ecole.dashboard') }}" class="sidebar-brand-logo">
                <span>{{ auth()->user()->ecole->nom ?? 'DocScolaire' }}</span>
            </a>
            <span class="sidebar-tenant-badge">{{ auth()->user()->ecole->code_unique ?? 'ÉCOLE' }}</span>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-group-title">Pilotage</span>
            <a href="{{ route('admin_ecole.dashboard') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.dashboard') ? 'active' : '' }}">
                <span>📊 Tableau de bord</span>
            </a>

            <span class="nav-group-title">Scolarité & Documents</span>
            <a href="{{ route('admin_ecole.demandes.index') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.demandes*') ? 'active' : '' }}">
                <span>📋 Demandes Étudiantes</span>
                @php
                    $pendingCount = \App\Models\DemandeDocument::where('ecole_id', auth()->user()->ecole_id ?? 0)->where('statut', 'en_attente')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="nav-badge-pill">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin_ecole.etudiants.index') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.etudiants*') ? 'active' : '' }}">
                <span>👥 Annuaire Étudiants</span>
            </a>
            <a href="{{ route('admin_ecole.modeles.index') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.modeles*') ? 'active' : '' }}">
                <span>📄 Modèles de Pièces</span>
            </a>
            <a href="{{ route('admin_ecole.import.index') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.import*') ? 'active' : '' }}">
                <span>📥 Import &amp; Inscription</span>
            </a>

            <span class="nav-group-title">Gouvernance</span>
            <a href="{{ route('admin_ecole.parametres') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.parametres*') ? 'active' : '' }}">
                <span>⚙️ Identité Établissement</span>
            </a>
            <a href="{{ route('admin_ecole.logs') }}" class="nav-link-item {{ request()->routeIs('admin_ecole.logs*') ? 'active' : '' }}">
                <span>📜 Journal d'Audit</span>
            </a>
        </nav>

        <div class="sidebar-footer-box">
            <div class="user-profile-row">
                @if(auth()->user()->hasPhoto())
                    <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->prenom }}" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;border:1px solid var(--hairline-strong);flex-shrink:0;">
                @else
                    <div class="user-avatar-badge">{{ mb_substr(auth()->user()->prenom ?? 'A', 0, 1) }}</div>
                @endif
                <div class="user-text-details">
                    <span class="user-fullname">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
                    <span class="user-subrole">Gestionnaire Scolarité</span>
                </div>
            </div>
            <form action="{{ route('admin_ecole.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-sidebar-logout">Déconnexion Sécurisée &rarr;</button>
            </form>
        </div>
    </aside>

    <!-- Zone Centrale de Contenu -->
    <div class="admin-main-wrap">
        @if(session()->has('impersonated_by_superadmin'))
            <div style="background:#121210;color:#ffffff;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.15);position:sticky;top:0;z-index:9999;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="background:rgba(255,255,255,0.1);color:#fff;padding:2px 8px;border-radius:9999px;font-size:10.5px;font-family:var(--font-mono);">⚡ ACCÈS MASTER SUPERADMIN</span>
                    <span>Session déléguée active sur l'établissement <strong>{{ auth()->user()->ecole->nom }}</strong>.</span>
                </div>
                <a href="{{ route('impersonate.stop') }}" style="background:#ef4444;color:#fff;padding:6px 14px;border-radius:9999px;text-decoration:none;font-size:12px;font-weight:700;">
                    ✕ Revenir au SuperAdmin &rarr;
                </a>
            </div>
        @endif

        <!-- Topbar Forma Style -->
        <header class="admin-topbar">
            <div class="topbar-left-col">
                <div class="topbar-heading">@yield('page_title', 'Administration')</div>
            </div>

            <div class="topbar-right-col">
                <button class="cmdk-trigger" data-cmdk-trigger title="Ouvrir le lanceur rapide (Cmd+K)">
                    <span>Recherche</span>
                    <kbd class="cmdk-kbd">⌘K</kbd>
                </button>

                <div class="topbar-school-tag">
                    <span class="beacon-dot" style="background:#10b981;"></span>
                    <span>{{ auth()->user()->ecole->nom ?? 'Établissement' }}</span>
                </div>

                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Basculer le thème">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="admin-content">
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
                <span style="color:var(--text-muted);font-size:16px;">⌘</span>
                <input type="text" id="cmdk-input" class="cmdk-input" placeholder="Rechercher une action ou naviguer..." autocomplete="off">
                <kbd class="cmdk-kbd" style="color:var(--text-muted);">ESC</kbd>
            </div>
            <div class="cmdk-list">
                <a href="{{ route('admin_ecole.dashboard') }}" class="cmdk-item">
                    <span>📊 Vue d'ensemble du Tableau de bord</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Aller &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.demandes.index') }}" class="cmdk-item">
                    <span>📋 Demandes d'attestations et certificats</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Valider &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.etudiants.index') }}" class="cmdk-item">
                    <span>👥 Annuaire et fiches des étudiants</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Consulter &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.modeles.index') }}" class="cmdk-item">
                    <span>📄 Gestion des modèles de documents</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Éditer &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.import.index') }}" class="cmdk-item">
                    <span>📥 Import de promotions via CSV</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Importer &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.parametres') }}" class="cmdk-item">
                    <span>⚙️ Paramètres de l'école &amp; cachet</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Configurer &rarr;</span>
                </a>
                <a href="{{ route('admin_ecole.logs') }}" class="cmdk-item">
                    <span>📜 Journal d'audit et traçabilité</span>
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);">Audit &rarr;</span>
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
