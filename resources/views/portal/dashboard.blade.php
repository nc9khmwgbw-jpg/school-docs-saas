<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Coffre-Fort Numérique Officiel — {{ $ecole->nom }}.">
    <title>Mon Coffre-Fort Académique | {{ $ecole->nom }} ({{ $ecole->sigle }})</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <meta name="theme-color" content="#edece8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --canvas-bg: #edece8;
            --canvas-card: rgba(255, 255, 255, 0.72);
            --canvas-card-solid: #ffffff;
            --canvas-subtle: rgba(0, 0, 0, 0.03);
            --ink-primary: #121210;
            --ink-secondary: #5a5953;
            --ink-muted: #8c8a82;
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.14);
            --btn-bg: #121210;
            --btn-text: #f5f4f0;
            --btn-hover: #262624;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', SFMono-Regular, Menlo, monospace;
            --accent-emerald: #10b981;
            --accent-amber: #d97706;
            --accent-red: #ef4444;
            --accent-blue: #2563eb;
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: rgba(255, 255, 255, 0.05);
            --canvas-card-solid: #171716;
            --canvas-subtle: rgba(255, 255, 255, 0.04);
            --ink-primary: #f5f4f0;
            --ink-secondary: #a8a69d;
            --ink-muted: #6b6962;
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
            --btn-bg: #f5f4f0;
            --btn-text: #121210;
            --btn-hover: #ffffff;
        }

        html, body {
            min-height: 100vh;
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Header Navigation Minimaliste Style Forma ── */
        .forma-nav-header {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            background: rgba(237, 236, 232, 0.88);
            border-bottom: 1px solid var(--hairline);
            padding: 14px clamp(20px, 4vw, 56px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        [data-theme="dark"] .forma-nav-header {
            background: rgba(14, 14, 13, 0.88);
            border-bottom-color: var(--hairline);
        }

        .forma-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--ink-primary);
        }
        .forma-brand-sigle {
            font-size: 19px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
        }
        .forma-brand-sigle sup {
            font-size: 10px;
            font-weight: 700;
            margin-left: 1px;
            color: var(--ink-muted);
        }
        .forma-brand-sep {
            color: var(--hairline-strong);
            font-weight: 300;
        }
        .forma-brand-sub {
            font-family: var(--font-mono);
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: var(--ink-muted);
            text-transform: uppercase;
        }

        .forma-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .forma-user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 14px 4px 6px;
            background: var(--canvas-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--hairline-strong);
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink-primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .forma-user-photo-thumb {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--hairline-strong);
            background: var(--canvas-subtle);
            flex-shrink: 0;
            display: block;
        }
        .forma-user-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-emerald);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }

        .forma-profile-main-flex {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }
        .forma-profile-avatar-wrap {
            position: relative;
            width: 82px;
            height: 82px;
            flex-shrink: 0;
        }
        .forma-profile-photo-hero {
            width: 82px;
            height: 82px;
            border-radius: 20px;
            object-fit: cover;
            border: 2px solid var(--hairline-strong);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            background: var(--canvas-subtle);
            display: block;
        }
        .forma-profile-photo-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--accent-emerald);
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--canvas-card);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .forma-theme-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .forma-theme-btn:hover {
            transform: scale(1.05);
            border-color: var(--ink-primary);
        }

        .forma-btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 16px;
            border-radius: 9999px;
            background: transparent;
            border: 1px solid var(--hairline-strong);
            color: var(--ink-secondary);
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .forma-btn-logout:hover {
            background: var(--ink-primary);
            color: var(--btn-text);
            border-color: var(--ink-primary);
            transform: translateY(-1px);
        }

        /* ── Container Principal Éditorial ── */
        .forma-dashboard-container {
            max-width: 1240px;
            margin: 32px auto 80px;
            padding: 0 clamp(20px, 4vw, 56px);
            display: flex;
            flex-direction: column;
            gap: 36px;
        }

        /* Bannière SuperAdmin Impersonation */
        .forma-impersonation-bar {
            background: #121210;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.15);
            gap: 12px;
            flex-wrap: wrap;
        }
        .forma-impersonation-btn {
            background: #ef4444;
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* ── Hero Profil Étudiant Éditorial ── */
        .forma-hero-profile {
            background: var(--canvas-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--hairline-strong);
            border-radius: 24px;
            padding: clamp(26px, 4vw, 40px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
            position: relative;
        }

        .forma-profile-kicker {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-muted);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .forma-profile-kicker::before {
            content: '—';
            color: var(--ink-muted);
        }

        .forma-profile-name {
            font-size: clamp(26px, 2.4vw + 0.5rem, 38px);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.035em;
            color: var(--ink-primary);
        }
        .forma-profile-name em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
            letter-spacing: -0.02em;
            display: block;
            font-size: 0.85em;
            color: var(--ink-secondary);
            margin-top: 4px;
        }

        .forma-profile-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .forma-mono-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 4px 12px;
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline-strong);
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .forma-mono-chip:hover {
            border-color: var(--ink-primary);
            background: var(--canvas-card-solid);
        }

        .forma-security-seal-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 4px 12px;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.22);
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 11.5px;
            font-weight: 700;
            color: #059669;
            letter-spacing: -0.01em;
        }
        [data-theme="dark"] .forma-security-seal-chip {
            color: #34d399;
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(52, 211, 153, 0.25);
        }
        .forma-seal-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
        }

        .forma-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .forma-solid-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 22px;
            border-radius: 9999px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        .forma-solid-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
        }
        .forma-solid-btn:active {
            transform: translateY(0);
        }

        .forma-outline-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 44px;
            padding: 0 20px;
            border-radius: 9999px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .forma-outline-btn:hover {
            border-color: var(--ink-primary);
            transform: translateY(-1px);
        }

        /* ── Grille des Statistiques Clés Minimalistes ── */
        .forma-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .forma-stat-tile {
            background: var(--canvas-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--hairline-strong);
            border-radius: 18px;
            padding: 22px 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .forma-stat-tile:hover {
            transform: translateY(-2px);
            border-color: var(--ink-primary);
        }
        .forma-stat-label {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-muted);
        }
        .forma-stat-val-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
        .forma-stat-number {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
            line-height: 1;
        }
        .forma-stat-tag {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
            color: var(--ink-muted);
        }

        /* ── Alertes Session ── */
        .forma-alert {
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .forma-alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        [data-theme="dark"] .forma-alert-success {
            color: #34d399;
            background: rgba(16, 185, 129, 0.12);
        }
        .forma-alert-error {
            background: rgba(239, 68, 68, 0.08);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.22);
        }
        [data-theme="dark"] .forma-alert-error {
            color: #f87171;
            background: rgba(239, 68, 68, 0.12);
        }

        /* ── Section Titres Éditoriaux ── */
        .forma-section-head {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }
        .forma-section-title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.03em;
            color: var(--ink-primary);
        }
        .forma-section-desc {
            font-size: 13.5px;
            color: var(--ink-secondary);
            margin-top: 4px;
            max-width: 600px;
            line-height: 1.5;
        }

        /* ── Catalogue des Modèles Disponibles ── */
        .forma-catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }
        .forma-catalog-card {
            background: var(--canvas-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--hairline-strong);
            border-radius: 20px;
            padding: 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 20px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .forma-catalog-card:hover {
            transform: translateY(-3px);
            border-color: var(--ink-primary);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.04);
        }
        .forma-doc-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline-strong);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-primary);
            font-size: 18px;
            flex-shrink: 0;
        }
        .forma-doc-title {
            font-size: 16.5px;
            font-weight: 800;
            color: var(--ink-primary);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }
        .forma-doc-desc {
            font-size: 13px;
            color: var(--ink-secondary);
            line-height: 1.5;
        }
        .forma-doc-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .forma-doc-badge {
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline);
            color: var(--ink-secondary);
        }

        /* ── Tableau Coffre-Fort Numérique ── */
        .forma-table-card {
            background: var(--canvas-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--hairline-strong);
            border-radius: 20px;
            overflow-x: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02);
        }
        .forma-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .forma-table th {
            padding: 16px 22px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-muted);
            border-bottom: 1px solid var(--hairline);
            background: var(--canvas-subtle);
        }
        .forma-table td {
            padding: 18px 22px;
            border-bottom: 1px solid var(--hairline);
            font-size: 13.5px;
            color: var(--ink-primary);
            vertical-align: middle;
        }
        .forma-table tr:last-child td {
            border-bottom: none;
        }
        .forma-table tr:hover td {
            background: var(--canvas-subtle);
        }

        .forma-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            font-family: var(--font-mono);
            letter-spacing: -0.01em;
        }
        .forma-status-success {
            background: rgba(16, 185, 129, 0.09);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.22);
        }
        [data-theme="dark"] .forma-status-success {
            color: #34d399;
            background: rgba(16, 185, 129, 0.14);
        }
        .forma-status-warning {
            background: rgba(217, 119, 6, 0.09);
            color: #d97706;
            border: 1px solid rgba(217, 119, 6, 0.22);
        }
        .forma-status-danger {
            background: rgba(239, 68, 68, 0.09);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.22);
        }

        .forma-action-btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 34px;
            padding: 0 14px;
            border-radius: 9999px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .forma-action-btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        .forma-action-ghost-sm {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 34px;
            padding: 0 12px;
            border-radius: 9999px;
            background: transparent;
            border: 1px solid var(--hairline-strong);
            color: var(--ink-secondary);
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .forma-action-ghost-sm:hover {
            border-color: var(--ink-primary);
            color: var(--ink-primary);
        }

        /* Toast copie */
        .forma-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--ink-primary);
            color: var(--btn-text);
            padding: 10px 18px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            font-family: var(--font-mono);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 9999;
        }
    </style>
</head>
<body>

    @if(session()->has('impersonated_by_superadmin'))
        <div class="forma-impersonation-bar">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-family:var(--font-mono);background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:6px;font-size:10.5px;letter-spacing:0.05em;">SUPERVISION SUPERADMIN</span>
                <span>Session active sur le dossier de <strong>{{ $etudiant->prenom }} {{ $etudiant->nom }}</strong> ({{ $ecole->nom }}).</span>
            </div>
            <a href="{{ route('impersonate.stop') }}" class="forma-impersonation-btn">
                ✕ Quitter &amp; Revenir
            </a>
        </div>
    @endif

    <!-- ── Barre de Navigation Haute Couture ── -->
    <header class="forma-nav-header">
        <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="forma-brand-wrap" title="Retour à la présentation {{ $ecole->nom }}">
            @if($ecole->logo_url)
                <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="height:28px;width:auto;max-width:110px;object-fit:contain;vertical-align:middle;margin-right:8px;filter:grayscale(100%);">
            @endif
            <span class="forma-brand-sigle">{{ $ecole->sigle }}<sup>&reg;</sup></span>
            <span class="forma-brand-sep">/</span>
            <span class="forma-brand-sub">Espace Étudiant</span>
        </a>

        <div class="forma-header-actions">
            <div class="forma-user-pill" title="Compte étudiant actif : {{ $etudiant->prenom }} {{ $etudiant->nom }}">
                <img src="{{ $etudiant->photo_url }}" alt="{{ $etudiant->prenom }} {{ $etudiant->nom }}" class="forma-user-photo-thumb">
                <span class="forma-user-dot"></span>
                <span>{{ $etudiant->prenom }} {{ $etudiant->nom }}</span>
            </div>

            <button type="button" class="forma-theme-btn" id="themeToggleBtn" title="Basculer Mode Clair / Sombre" aria-label="Mode Clair / Sombre">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

            <form action="{{ route('tenant.logout', ['code_unique' => $ecole->code_unique]) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="forma-btn-logout">
                    <span>Déconnexion</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>
        </div>
    </header>

    <!-- ── Corps du Tableau de Bord ── -->
    <main class="forma-dashboard-container">

        <!-- 1. Hero Profil Étudiant Éditorial -->
        <div class="forma-hero-profile">
            <div class="forma-profile-main-flex">
                <div class="forma-profile-avatar-wrap">
                    <img src="{{ $etudiant->photo_url }}" alt="Photo officielle de {{ $etudiant->prenom }} {{ $etudiant->nom }}" class="forma-profile-photo-hero">
                    <span class="forma-profile-photo-badge" title="Profil certifié conforme">✓</span>
                </div>
                <div>
                    <div class="forma-profile-kicker">
                        DOSSIER ACADÉMIQUE OFFICIEL &bull; {{ $ecole->code_unique }}
                    </div>
                    <h1 class="forma-profile-name">
                        Bonjour, {{ $etudiant->prenom }} {{ $etudiant->nom }}
                        <em>{{ $ecole->nom }} &bull; Espace Étudiant.</em>
                    </h1>

                <div class="forma-profile-meta">
                    <span class="forma-mono-chip" id="matriculeChip" data-copy="{{ $etudiant->matricule }}" title="Cliquer pour copier le matricule">
                        <span>Matricule : <strong>{{ $etudiant->matricule }}</strong></span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </span>

                    <span class="forma-security-seal-chip">
                        <span class="forma-seal-dot"></span>
                        <span>Dossier Académique Scellé SHA-256</span>
                    </span>
                </div>
            </div>

            <div class="forma-hero-actions">
                <a href="{{ route('tenant.historique', ['code_unique' => $ecole->code_unique]) }}" class="forma-outline-btn">
                    <span>Historique complet</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Alertes Session -->
        @if(session('success'))
            <div class="forma-alert forma-alert-success">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="forma-alert forma-alert-error">
                <span>⚠️</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- 2. Grille des 3 Indicateurs Clés -->
        <div class="forma-stats-grid">
            <div class="forma-stat-tile">
                <span class="forma-stat-label">Documents Scellés Prêts</span>
                <div class="forma-stat-val-row">
                    <span class="forma-stat-number" style="color:var(--accent-emerald);">{{ $demandes->where('statut', 'genere')->count() }}</span>
                    <span class="forma-stat-tag">Certifiés</span>
                </div>
            </div>
            <div class="forma-stat-tile">
                <span class="forma-stat-label">Demandes en Cours</span>
                <div class="forma-stat-val-row">
                    <span class="forma-stat-number" style="color:var(--accent-amber);">{{ $demandes->where('statut', 'en_attente')->count() }}</span>
                    <span class="forma-stat-tag">Traitement</span>
                </div>
            </div>
            <div class="forma-stat-tile">
                <span class="forma-stat-label">Modèles Éligibles</span>
                <div class="forma-stat-val-row">
                    <span class="forma-stat-number">{{ $modeles->count() }}</span>
                    <span class="forma-stat-tag">Disponibles</span>
                </div>
            </div>
        </div>

        <!-- 3. Catalogue des Pièces Officielles Disponibles -->
        <div>
            <div class="forma-section-head">
                <div>
                    <h2 class="forma-section-title">Commander un Document Officiel</h2>
                    <p class="forma-section-desc">Sélectionnez la pièce académique souhaitée. Chaque document est instantanément scellé par signature numérique et QR code cryptographique de contrôle.</p>
                </div>
            </div>

            <div class="forma-catalog-grid">
                @forelse($modeles as $modele)
                    <div class="forma-catalog-card">
                        <div>
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                                <div class="forma-doc-icon-box">📄</div>
                                <div>
                                    <h3 class="forma-doc-title">{{ $modele->titre }}</h3>
                                    <div class="forma-doc-desc">{{ $modele->description ?? 'Document officiel certifié avec contrôle d\'authenticité par QR code.' }}</div>
                                </div>
                            </div>

                            <div class="forma-doc-badges">
                                <span class="forma-doc-badge">Scellé SHA-256</span>
                                <span class="forma-doc-badge">Signature Électronique</span>
                                <span class="forma-doc-badge">QR Code Conforme</span>
                            </div>
                        </div>

                        <form action="{{ route('tenant.demandes.store', ['code_unique' => $ecole->code_unique]) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="modele_id" value="{{ $modele->id }}">
                            <button type="submit" class="forma-solid-btn" style="width:100%;height:44px;">
                                <span>Commander &amp; Émettre</span>
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="forma-catalog-card" style="grid-column:1 / -1;text-align:center;padding:48px 24px;">
                        <p style="color:var(--ink-muted);">Aucun modèle de document disponible pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. Tableau des Documents Scellés Récents -->
        <div>
            <div class="forma-section-head">
                <div>
                    <h2 class="forma-section-title">Mes Documents Scellés Récents</h2>
                    <p class="forma-section-desc">Copies certifiées prêtes pour vos démarches administratives, bancaires, consulaires et académiques.</p>
                </div>
                <a href="{{ route('tenant.historique', ['code_unique' => $ecole->code_unique]) }}" class="forma-outline-btn" style="height:38px;padding:0 16px;font-size:12.5px;">
                    <span>Voir tout l'historique</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="forma-table-card">
                <table class="forma-table">
                    <thead>
                        <tr>
                            <th>Date d'Émission</th>
                            <th>Document &amp; Empreinte</th>
                            <th>Statut d'Authenticité</th>
                            <th>Sceau Numérique</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandes as $demande)
                            <tr>
                                <td style="font-family:var(--font-mono);font-size:12px;color:var(--ink-secondary);">
                                    {{ $demande->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div style="font-weight:700;color:var(--ink-primary);font-size:14px;">
                                        {{ $demande->modele->titre ?? 'Document Officiel' }}
                                    </div>
                                    @if($demande->documentGenere)
                                        <div style="font-size:11px;color:var(--ink-muted);font-family:var(--font-mono);margin-top:2px;">
                                            UUID : {{ substr($demande->documentGenere->uuid, 0, 18) }}...
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($demande->statut === 'genere')
                                        <span class="forma-status-chip forma-status-success">
                                            <span class="forma-seal-dot"></span>
                                            Certifié Conforme
                                        </span>
                                    @elseif($demande->statut === 'en_attente')
                                        <span class="forma-status-chip forma-status-warning">
                                            <span style="width:6px;height:6px;border-radius:50%;background:#d97706;"></span>
                                            En validation
                                        </span>
                                    @elseif($demande->statut === 'refuse')
                                        <span class="forma-status-chip forma-status-danger">
                                            <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;"></span>
                                            Refusé
                                        </span>
                                    @else
                                        <span class="forma-status-chip">{{ $demande->statut }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($demande->documentGenere)
                                        <span style="font-family:var(--font-mono);font-size:12px;color:#059669;font-weight:700;">✓ QR Scellé</span>
                                    @elseif($demande->statut === 'refuse')
                                        <span style="font-size:12px;color:var(--ink-muted);">Non scellé</span>
                                    @else
                                        <span style="font-size:12px;color:var(--accent-amber);">En attente de signature</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if($demande->documentGenere)
                                        <div style="display:inline-flex;gap:8px;justify-content:flex-end;">
                                            <a href="{{ route('tenant.documents.download', ['code_unique' => $ecole->code_unique, 'uuid' => $demande->documentGenere->uuid]) }}" class="forma-action-btn-sm">
                                                <span>Télécharger PDF</span>
                                                <span aria-hidden="true">&darr;</span>
                                            </a>
                                            <a href="{{ route('public.verifier', ['uuid' => $demande->documentGenere->uuid]) }}" class="forma-action-ghost-sm" target="_blank" title="Vérifier l'authenticité par QR code">
                                                <span>Vérifier</span>
                                                <span aria-hidden="true">&nearr;</span>
                                            </a>
                                        </div>
                                    @elseif($demande->statut === 'refuse')
                                        <div style="text-align:right;">
                                            <span style="font-size:12px;font-weight:600;color:#dc2626;">✕ Refusé</span>
                                            @if($demande->motif_refus)
                                                <div style="font-size:11px;color:var(--ink-muted);margin-top:2px;">Motif : {{ $demande->motif_refus }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span style="font-size:12px;color:var(--ink-muted);">Traitement en cours</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:48px 24px;color:var(--ink-muted);">
                                    Aucun document généré pour le moment. Choisissez un modèle ci-dessus pour émettre votre première pièce officielle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Toast Notification Copie -->
    <div class="forma-toast" id="copyToast">✓ Matricule copié dans le presse-papier</div>

    <script>
        // Gestion du Thème Clair / Sombre Forma
        (function initTheme() {
            const savedTheme = localStorage.getItem('forma_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            const themeBtn = document.getElementById('themeToggleBtn');
            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                    const next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('forma_theme', next);
                });
            }
        })();

        // Copier le matricule en 1-clic
        const chip = document.getElementById('matriculeChip');
        const toast = document.getElementById('copyToast');
        if (chip && toast) {
            chip.addEventListener('click', () => {
                const text = chip.getAttribute('data-copy');
                if (text) {
                    navigator.clipboard.writeText(text).then(() => {
                        toast.style.display = 'block';
                        setTimeout(() => { toast.style.display = 'none'; }, 2200);
                    });
                }
            });
        }
    </script>
</body>
</html>
