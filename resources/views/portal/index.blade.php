<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Portail académique officiel — {{ $ecole->nom }}. Certification et délivrance sécurisée de documents scellés.">
    <title>{{ $ecole->nom }} ({{ $ecole->sigle }}) — Espace Étudiant Officiel | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#edece8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --canvas-bg: #edece8;
            --canvas-card: #ffffff;
            --ink-primary: #141414;
            --ink-secondary: #524f4b;
            --ink-muted: #8a857b;
            --watermark-color: rgba(20, 20, 20, 0.22);
            --watermark-stroke: rgba(20, 20, 20, 0.48);
            --watermark-glow: rgba(255, 255, 255, 0.65);
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.14);
            --btn-bg: #141414;
            --btn-text: #ffffff;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: #171716;
            --ink-primary: #f5f4f0;
            --ink-secondary: #a8a49c;
            --ink-muted: #6e6b63;
            --watermark-color: rgba(255, 255, 255, 0.18);
            --watermark-stroke: rgba(255, 255, 255, 0.42);
            --watermark-glow: rgba(0, 0, 0, 0.60);
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
            --btn-bg: #f5f4f0;
            --btn-text: #141414;
        }

        html, body {
            height: 100vh;
            height: 100dvh;
            max-height: 100vh;
            max-height: 100dvh;
            overflow: hidden;
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        /* ── Header Minimaliste Style "Forma®" ── */
        .forma-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: clamp(14px, 2.2vh, 26px) clamp(24px, 4vw, 56px);
            position: relative;
            z-index: 50;
            user-select: none;
            flex-shrink: 0;
        }

        .forma-brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .forma-brand sup {
            font-size: 11px;
            font-weight: 700;
            position: relative;
            top: -6px;
        }

        .forma-nav-actions {
            display: flex;
            align-items: center;
            gap: clamp(14px, 2.2vw, 26px);
        }

        .forma-nav-link {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--ink-secondary);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .forma-nav-link:hover {
            color: var(--ink-primary);
        }

        .forma-moon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--hairline-strong);
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--ink-secondary);
            transition: all 0.2s ease;
        }
        .forma-moon-btn:hover {
            border-color: var(--ink-primary);
            color: var(--ink-primary);
            transform: scale(1.05);
        }

        .forma-pill-btn {
            padding: 7px 18px;
            border-radius: 9999px;
            border: 1px solid var(--hairline-strong);
            background: transparent;
            color: var(--ink-primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .forma-pill-btn:hover {
            background: var(--ink-primary);
            color: var(--canvas-bg);
            border-color: var(--ink-primary);
        }

        /* ── Stage WebGL2 Plein Écran ── */
        .sh-stage {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            overflow: hidden;
        }

        .sh-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
            opacity: 0;
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sh-fallback {
            position: absolute;
            display: block;
            max-width: none;
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            filter: drop-shadow(0 20px 36px rgba(35, 30, 20, 0.09));
        }

        body[data-gl="on"] .sh-canvas {
            opacity: 1;
        }
        body[data-gl="on"] .sh-fallback {
            opacity: 0;
        }
        body[data-swap="1"] .sh-canvas,
        body[data-swap="1"] .sh-fallback {
            opacity: 0;
            transition-duration: 0.25s;
        }

        /* ── Grille Héro Principale 2 Colonnes ── */
        .forma-hero-container {
            position: relative;
            z-index: 40;
            flex: 1 1 0;
            min-height: 0;
            display: grid;
            grid-template-columns: 55fr 45fr;
            gap: clamp(24px, 3.5vw, 56px);
            padding: 0 clamp(24px, 4vw, 56px) clamp(16px, 2.4vh, 28px);
            width: 100%;
            box-sizing: border-box;
            align-items: stretch;
        }

        /* ── Colonne Gauche (~55%) : Visuel 3D / Sigle École + Typographie Éditoriale ── */
        .hero-col-left {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 0;
            height: 100%;
            z-index: 30;
        }

        /* Scène d'ancrage visuel pour la sculpture 3D et les lettres filigranes */
        .hero-visual-stage {
            position: relative;
            flex: 1 1 0;
            min-height: 180px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            isolation: isolate;
        }

        /* Mot Géant en Filigrane Doux Multi-Calques */
        .forma-giant-watermark {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(140px, 19vw, 290px);
            font-weight: 900;
            letter-spacing: -0.055em;
            text-transform: lowercase;
            pointer-events: none;
            line-height: 0.82;
            white-space: nowrap;
            user-select: none;
            text-align: center;
        }

        /* Calque Arrière : Plus foncé que le fond pour une lisibilité parfaite */
        .forma-giant-watermark.watermark-back {
            z-index: 1;
            color: var(--watermark-color);
            text-shadow: 0 4px 28px var(--watermark-glow);
            transition: color 0.4s ease, opacity 0.4s ease;
        }

        /* Calque Avant : Fin contour de 2px passant par-dessus le sable (la lettre reste 100% visible) */
        .forma-giant-watermark.watermark-front {
            z-index: 3;
            color: transparent;
            -webkit-text-stroke: 2px var(--watermark-stroke);
            opacity: 0.95;
            transition: opacity 0.4s ease, -webkit-text-stroke 0.4s ease;
        }

        body[data-touched="1"] .forma-giant-watermark.watermark-front {
            opacity: 1;
        }

        /* Bloc Éditorial Gauche (sous le visuel) */
        .hero-editorial-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 10px;
            flex-shrink: 0;
            max-width: 620px;
        }

        .forma-kicker {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--ink-muted);
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .forma-kicker::before {
            content: '—';
            display: inline-block;
            color: var(--ink-muted);
        }

        .forma-editorial-title {
            font-size: clamp(26px, 2.5vw, 42px);
            font-weight: 900;
            line-height: 1.12;
            letter-spacing: -0.035em;
            color: var(--ink-primary);
        }
        .forma-editorial-title em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
            letter-spacing: -0.02em;
            display: block;
            font-size: 0.94em;
            margin-top: 3px;
        }

        .forma-editorial-desc {
            font-size: clamp(13.5px, 1.0vw, 15px);
            line-height: 1.55;
            color: var(--ink-secondary);
            max-width: 560px;
        }

        /* ── Colonne Droite (~45%) : Formulaire de Connexion Centré Verticalement ── */
        .hero-col-right {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 0;
            height: 100%;
            z-index: 35;
        }

        /* Section Connexion : Sans cadre blanc (Intégration fluide directe sur le canevas) */
        .forma-login-card {
            background: transparent;
            border-radius: 0;
            padding: 0;
            width: 100%;
            max-width: 500px;
            box-shadow: none;
            border: none;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        [data-theme="dark"] .forma-login-card {
            background: transparent;
            border: none;
            box-shadow: none;
        }

        /* En-tête architectural de connexion */
        .forma-auth-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-bottom: 4px;
        }

        .forma-auth-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-mono);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--ink-muted);
        }

        .auth-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2.5px rgba(16, 185, 129, 0.25);
            display: inline-block;
        }

        /* Pastille Démo en 1-Clic pour CETTE école */
        @php
            $demoEtudiant = \App\Models\Utilisateur::withoutGlobalScopes()
                ->where('ecole_id', $ecole->id)
                ->where('role', 'etudiant')
                ->first();
            if (!$demoEtudiant) {
                $demoEtudiant = \App\Models\Utilisateur::withoutGlobalScopes()
                    ->where('role', 'etudiant')
                    ->first();
            }
        @endphp

        @if($demoEtudiant)
        .forma-demo-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 15px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 500;
            color: var(--ink-secondary);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
        }
        [data-theme="dark"] .forma-demo-pill {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.14);
            color: var(--ink-secondary);
            box-shadow: none;
        }
        .forma-demo-pill:hover {
            background: #ffffff;
            color: var(--ink-primary);
            border-color: var(--ink-primary);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            transform: translateY(-1px);
        }
        [data-theme="dark"] .forma-demo-pill:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: #ffffff;
        }
        .forma-demo-pill .demo-sparkle {
            font-size: 10.5px;
            color: #d97706;
        }
        .forma-demo-pill .demo-badge {
            font-size: 10.5px;
            font-weight: 700;
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            padding: 2px 7px;
            border-radius: 9999px;
            letter-spacing: -0.01em;
        }
        [data-theme="dark"] .forma-demo-pill .demo-badge {
            color: #60a5fa;
            background: rgba(96, 165, 250, 0.15);
        }
        @endif

        /* Champs de saisie verticaux */
        .forma-auth-fields {
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
        }

        .forma-field-wrap {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .field-icon {
            position: absolute;
            left: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-muted);
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .forma-input {
            width: 100%;
            height: 58px;
            padding: 0 48px 0 50px;
            background: #ffffff;
            border: 1.5px solid rgba(0, 0, 0, 0.12);
            border-radius: 14px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 500;
            color: var(--ink-primary);
            outline: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .forma-input.no-toggle {
            padding-right: 20px;
        }
        .forma-input::placeholder {
            color: var(--ink-muted);
            font-weight: 400;
            font-size: 15px;
        }
        [data-theme="dark"] .forma-input {
            background: rgba(255, 255, 255, 0.06);
            color: var(--ink-primary);
            border-color: rgba(255, 255, 255, 0.14);
            box-shadow: none;
        }
        .forma-input:focus {
            background: #ffffff;
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08), 0 6px 20px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }
        .forma-field-wrap:focus-within .field-icon {
            color: var(--ink-primary);
        }
        [data-theme="dark"] .forma-input:focus {
            background: rgba(255, 255, 255, 0.10);
            border-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.16);
        }

        .field-toggle-pwd {
            position: absolute;
            right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            padding: 8px;
            color: var(--ink-muted);
            cursor: pointer;
            border-radius: 8px;
            transition: color 0.2s ease;
        }
        .field-toggle-pwd:hover {
            color: var(--ink-primary);
        }

        .forma-input.autofilled-glow {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
        }

        /* Rangée des Actions Principales */
        .forma-btn-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        /* Bouton Plein Noir Arrondi - Raffiné & Équilibré */
        .forma-solid-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 22px;
            border-radius: 9999px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: -0.01em;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            white-space: nowrap;
        }
        .forma-solid-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        }
        .forma-solid-btn:hover .btn-arrow {
            transform: translateX(3px);
        }
        .forma-solid-btn .btn-arrow {
            font-size: 14px;
            transition: transform 0.2s ease;
        }
        .forma-solid-btn:active {
            transform: translateY(0);
        }

        /* Lien Mot de passe oublié / Vérification */
        .forma-link-subtle {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-secondary);
            text-decoration: none;
            position: relative;
            padding-bottom: 2px;
            transition: color 0.2s ease;
            white-space: nowrap;
        }
        .forma-link-subtle::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: currentColor;
            transform: scaleX(0.7);
            transform-origin: right;
            transition: transform 0.25s ease;
        }
        .forma-link-subtle:hover {
            color: var(--ink-primary);
        }
        .forma-link-subtle:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Barres de partenaires et d'écoles sous le formulaire */
        .forma-partner-tray {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
            padding-top: 18px;
            border-top: 1px solid rgba(0, 0, 0, 0.10);
        }
        [data-theme="dark"] .forma-partner-tray {
            border-top-color: rgba(255, 255, 255, 0.10);
        }
        .forma-schools-label {
            font-size: 11px;
            color: var(--ink-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .forma-school-chip {
            padding: 6px 14px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.12);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
            font-size: 11.5px;
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--ink-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .forma-school-chip.active-school {
            background: var(--ink-primary);
            color: var(--btn-text);
            border-color: var(--ink-primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        [data-theme="dark"] .forma-school-chip {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: none;
        }
        [data-theme="dark"] .forma-school-chip.active-school {
            background: var(--btn-bg);
            color: var(--btn-text);
            border-color: var(--btn-bg);
        }
        .forma-school-chip:hover {
            background: var(--ink-primary);
            color: var(--btn-text);
            border-color: var(--ink-primary);
            transform: translateY(-1px);
        }

        /* Signature fine sous la carte */
        .forma-credit-tag {
            font-size: 11.5px;
            color: var(--ink-muted);
            font-family: var(--font-mono);
            text-align: right;
            margin-top: 2px;
        }

        /* Alertes session */
        .forma-alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.4;
        }
        .forma-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .forma-alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        /* Responsive : 1 colonne sur tablette et mobile */
        @media (max-width: 1024px) {
            html, body {
                height: auto;
                min-height: 100vh;
                max-height: none;
                overflow-y: auto;
            }
            .forma-hero-container {
                grid-template-columns: 1fr;
                gap: 36px;
                height: auto;
                padding-bottom: 40px;
            }
            .hero-col-left {
                height: auto;
                gap: 20px;
            }
            .hero-visual-stage {
                min-height: 240px;
                height: 260px;
            }
            .hero-col-right {
                height: auto;
            }
            .forma-login-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="sh-root">

    <!-- Stage WebGL2 de la Sculpture Liquide (Plein écran sous le contenu) -->
    <div class="sh-stage" id="shStage">
        <canvas id="liquidCanvas" class="sh-canvas"></canvas>
        <img 
            id="fallbackImg" 
            class="sh-fallback" 
            src="/images/sculpture-light.webp" 
            alt="Sculpture 3D {{ $ecole->nom }}"
        >
    </div>

    <!-- Header Supérieur Minimaliste Style Forma -->
    <header class="forma-header">
        <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="forma-brand" title="{{ $ecole->nom }}">
            @if($ecole->logo_url)
                <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="height:28px;width:auto;max-width:130px;object-fit:contain;vertical-align:middle;margin-right:6px;filter:grayscale(100%);">
            @endif
            {{ $ecole->sigle }}<sup>&reg;</sup>
        </a>

        <nav class="forma-nav-actions">
            <button type="button" class="forma-moon-btn" id="themeToggleBtn" title="Basculer Mode Clair / Sombre" aria-label="Mode Clair / Sombre">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </nav>
    </header>

    <!-- ── Section Hero Principale 2 Colonnes (100% Largeur & 100% Hauteur) ── -->
    <main class="forma-hero-container">
        
        <!-- 1. COLONNE GAUCHE (~55% de la largeur) : Visuel 3D / Sigle École + Typographie Éditoriale -->
        <section class="hero-col-left">
            <!-- Scène d'ancrage visuel pour la sculpture 3D et les lettres géantes -->
            <div class="hero-visual-stage" id="heroVisualStage">
                <div class="forma-giant-watermark watermark-back" id="watermarkText">{{ strtolower($ecole->sigle) }}</div>
                <div class="forma-giant-watermark watermark-front" id="watermarkTextFront" aria-hidden="true">{{ strtolower($ecole->sigle) }}</div>
            </div>

            <!-- Bloc Éditorial : Kicker, Titre, Espace Étudiant, Description -->
            <div class="hero-editorial-content" id="heroEditorialContent">
                <div class="forma-kicker" id="kickerText">
                    ÉTABLISSEMENT OFFICIEL — {{ $ecole->code_unique }}
                </div>
                <h1 class="forma-editorial-title" id="editorialTitle">
                    {{ $ecole->nom }}
                    <em>Espace Étudiant.</em>
                </h1>
                <p class="forma-editorial-desc">
                    Coffre-fort académique officiel et infalsifiable. Accédez instantanément à vos attestations de scolarité, relevés de notes et certificats d'études scellés cryptographiquement.
                </p>
            </div>
        </section>

        <!-- 2. COLONNE DROITE (~45% de la largeur) : Formulaire de Connexion Centré Verticalement -->
        <section class="hero-col-right">
            <div class="forma-login-card">
                <!-- Messages de session / Déconnexion réussie / Erreurs -->
                @if(session('success'))
                    <div class="forma-alert forma-alert-success">✓ {{ session('success') }}</div>
                @endif
                @if(session('status'))
                    <div class="forma-alert forma-alert-success">✓ {{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="forma-alert forma-alert-error">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- En-tête architectural : Statut Sécurisé + Pastille Démo -->
                <div class="forma-auth-header">
                    <div class="forma-auth-label">
                        <span class="auth-status-dot"></span>
                        <span>Portail d'Accès Sécurisé</span>
                    </div>

                    @if($demoEtudiant)
                    <button type="button" class="forma-demo-pill" id="demo-autofill-btn" title="Cliquer pour tester avec le compte démo">
                        <span class="demo-sparkle">✦</span>
                        <span>Démo : <strong>{{ $demoEtudiant->prenom }} {{ $demoEtudiant->nom }}</strong></span>
                        <span class="demo-badge">1-Clic &rarr;</span>
                    </button>
                    @endif
                </div>

                <!-- Formulaire de Connexion Agrandie (+25-30%) -->
                <form action="{{ route('tenant.login', ['code_unique' => $ecole->code_unique]) }}" method="POST">
                    @csrf
                    <div class="forma-auth-fields">
                        <div class="forma-field-wrap">
                            <span class="field-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="identifiant" 
                                name="identifiant" 
                                class="forma-input no-toggle" 
                                placeholder="Matricule académique ou email" 
                                required 
                                value="{{ old('identifiant', old('matricule')) }}"
                                autocomplete="username"
                            >
                        </div>

                        <div class="forma-field-wrap">
                            <span class="field-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                id="mot_de_passe" 
                                name="mot_de_passe" 
                                class="forma-input" 
                                placeholder="Mot de passe" 
                                required 
                                autocomplete="current-password"
                            >
                            <button type="button" class="field-toggle-pwd" id="togglePasswordBtn" title="Afficher / Masquer le mot de passe" aria-label="Afficher le mot de passe">
                                <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Rangée des Boutons : Accéder à mon Espace + Mot de passe oublié -->
                    <div class="forma-btn-row">
                        <button type="submit" class="forma-solid-btn" id="btn-login-submit">
                            <span>Accéder à mon Espace</span>
                            <span class="btn-arrow" aria-hidden="true">&rarr;</span>
                        </button>
                        <a href="{{ route('tenant.password.forgot', ['code_unique' => $ecole->code_unique]) }}" class="forma-link-subtle">
                            <span>Mot de passe oublié ?</span>
                            <span class="btn-arrow" aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </form>

                <div class="forma-credit-tag">
                    {{ $ecole->sigle }} &bull; Espace Officiel &bull; by docscolaire.saas
                </div>
            </div>
        </section>
    </main>

    <!-- ── Moteur Particulaire Fluide WebGL2 "Forma® Liquid Sculpture Engine" ── -->
    <script src="/js/liquid-sculpture-engine.js?v={{ time() }}"></script>
    <script>
        // Remplissage automatique démo en 1 clic avec feedback visuel
        const demoBtn = document.getElementById('demo-autofill-btn');
        if (demoBtn) {
            demoBtn.addEventListener('click', () => {
                const idInput = document.getElementById('identifiant');
                const pwInput = document.getElementById('mot_de_passe');
                @if($demoEtudiant)
                    idInput.value = '{{ $demoEtudiant->matricule ?? $demoEtudiant->email }}';
                    pwInput.value = 'Secret2026!';
                    idInput.classList.add('autofilled-glow');
                    pwInput.classList.add('autofilled-glow');
                    setTimeout(() => {
                        idInput.classList.remove('autofilled-glow');
                        pwInput.classList.remove('autofilled-glow');
                    }, 1200);
                    idInput.focus();
                @endif
            });
        }

        // Bascule Afficher / Masquer Mot de passe
        const togglePwBtn = document.getElementById('togglePasswordBtn');
        if (togglePwBtn) {
            togglePwBtn.addEventListener('click', () => {
                const pwInput = document.getElementById('mot_de_passe');
                if (!pwInput) return;
                const isPassword = pwInput.type === 'password';
                pwInput.type = isPassword ? 'text' : 'password';
                togglePwBtn.innerHTML = isPassword 
                    ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
                    : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            });
        }

        // Moteur Fluide WebGL2 Liquid Sculpture Vivant & Interactif
        (function initLiquidSculpturePortal() {
            const body = document.body;
            const canvas = document.getElementById('liquidCanvas');
            const fallback = document.getElementById('fallbackImg');
            const watermark = document.getElementById('watermarkText');
            const watermarkFront = document.getElementById('watermarkTextFront');
            const themeBtn = document.getElementById('themeToggleBtn');

            if (!canvas) return;

            let currentTheme = localStorage.getItem('forma_theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);

            let aspect = 1983 / 793;
            let engine = null;
            let hasTouched = false;

            const bgLight = [0.929, 0.925, 0.910]; // #edece8
            const bgDark = [0.055, 0.055, 0.051];  // #0e0e0d

            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)');

            const opts = {
                interactive: true,
                strength: 1.25,
                radius: 220,
                density: 1.0,
                dotSize: 1.05,
                spread: 1.0,
                turbulence: 1.0,
                liquid: 1.0,
                streaks: true,
                restStyle: 'image',
                returnSpeed: 1.0,
                ambient: true,            // Swirl & breathing autonome perpétuel à 60 FPS
                ambientIntensity: 0.95,
                speed: 1.0,
                intro: true,
                reducedMotion: prefersReduced.matches,
                bg: currentTheme === 'dark' ? bgDark : bgLight,
                imgBg: null
            };

            function onFail() {
                body.dataset.gl = "off";
            }

            try {
                if (window.LiquidEngine) {
                    engine = new LiquidEngine(canvas, opts, onFail);
                } else {
                    onFail();
                }
            } catch (e) {
                console.error("LiquidEngine init error:", e);
                onFail();
            }

            function layout() {
                const wt = window.innerWidth;
                const ht = window.innerHeight;

                const visualStage = document.getElementById('heroVisualStage');
                if (!visualStage) return;

                const vRect = visualStage.getBoundingClientRect();

                let scW, scH, scX, scY;
                if (wt > 1024) {
                    // Desktop 2-colonnes : la sculpture 3D se cale élégamment dans la scène visuelle gauche (~55%)
                    scW = Math.min(vRect.width * 0.94, vRect.height * aspect * 0.90);
                    scH = scW / aspect;
                    scX = vRect.left + (vRect.width - scW) / 2;
                    // Décalée légèrement vers le haut pour laisser respirer le mot filigrane
                    scY = vRect.top + (vRect.height - scH) / 2 - 14;
                } else {
                    // Tablette & mobile : 1 colonne pleine largeur
                    scW = Math.min(wt * 0.92, vRect.height * aspect * 0.92);
                    scH = scW / aspect;
                    scX = (wt - scW) / 2;
                    scY = vRect.top + (vRect.height - scH) / 2 - 8;
                }

                const rect = { x: scX, y: scY, w: scW, h: scH };

                if (fallback) {
                    fallback.style.left = rect.x + 'px';
                    fallback.style.top = rect.y + 'px';
                    fallback.style.width = rect.w + 'px';
                    fallback.style.height = rect.h + 'px';
                }

                if (watermark) {
                    const textLen = Math.max(1, (watermark.textContent || '').trim().length);
                    const safeHeight = Math.max(80, vRect.height);
                    const fSize = Math.min(safeHeight * 0.88, vRect.width * 0.88 / (textLen * 0.56)) + 'px';
                    watermark.style.fontSize = fSize;
                    if (watermarkFront) {
                        watermarkFront.style.fontSize = fSize;
                    }
                }

                if (engine) {
                    engine.resize(wt, ht, rect);
                }
            }

            window.addEventListener('resize', layout);

            let stageReady = false;

            function loadTexture(theme) {
                body.dataset.swap = "1";
                const isDark = theme === 'dark';
                const isMobile = window.innerWidth < 640;
                const src = isMobile
                    ? (isDark ? '/images/sculpture-mobile-dark.webp' : '/images/sculpture-mobile.webp')
                    : (isDark ? '/images/sculpture-dark.webp' : '/images/sculpture-light.webp');

                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = () => {
                    aspect = img.naturalWidth / img.naturalHeight || aspect;
                    layout();
                    if (fallback) {
                        fallback.src = src;
                    }
                    if (engine && body.dataset.gl !== "off") {
                        try {
                            engine.onFirstFrame = () => {
                                body.dataset.gl = "on";
                                delete body.dataset.swap;
                                stageReady = true;
                            };
                            engine.setImage(img);
                            layout();
                        } catch (e) {
                            console.error("setImage error:", e);
                            onFail();
                        }
                    } else {
                        delete body.dataset.swap;
                    }
                };
                img.onerror = () => {
                    delete body.dataset.swap;
                    onFail();
                };
                img.src = src;
            }

            loadTexture(currentTheme);

            function markTouched() {
                if (!hasTouched) {
                    hasTouched = true;
                    body.dataset.touched = "1";
                }
            }

            let lastUserMove = 0;
            let autoAngle = 0;

            function ambientLoop() {
                if (engine && stageReady && (Date.now() - lastUserMove > 400)) {
                    autoAngle += 0.024;
                    const rect = engine.rect;
                    if (rect && rect.w > 10) {
                        const cx = rect.x + rect.w * 0.5;
                        const cy = rect.y + rect.h * 0.5;
                        const rx = rect.w * 0.28;
                        const ry = rect.h * 0.18;
                        const driftX = cx + Math.cos(autoAngle) * rx;
                        const driftY = cy + Math.sin(autoAngle * 2) * ry * 0.5;
                        engine.pointer('move', driftX, driftY, false);
                    }
                }
                requestAnimationFrame(ambientLoop);
            }
            requestAnimationFrame(ambientLoop);

            // Événements pointer
            window.addEventListener('pointermove', (e) => {
                lastUserMove = Date.now();
                if (!engine) return;
                const isTouch = e.pointerType === 'touch';
                if (isTouch && e.buttons === 0 && e.pressure === 0) return;
                engine.pointer('move', e.clientX, e.clientY, isTouch);
                markTouched();
            }, { passive: true });

            window.addEventListener('pointerdown', (e) => {
                lastUserMove = Date.now();
                if (!engine) return;
                engine.pointer('down', e.clientX, e.clientY, e.pointerType === 'touch');
                markTouched();
            }, { passive: true });

            window.addEventListener('pointerup', (e) => {
                if (!engine) return;
                engine.pointer('up', e.clientX, e.clientY, e.pointerType === 'touch');
            }, { passive: true });

            window.addEventListener('pointercancel', (e) => {
                if (!engine) return;
                engine.pointer('up', e.clientX, e.clientY, e.pointerType === 'touch');
            }, { passive: true });

            window.addEventListener('pointerleave', (e) => {
                if (!engine) return;
                engine.pointer('leave', e.clientX, e.clientY, e.pointerType === 'touch');
            }, { passive: true });

            document.addEventListener('visibilitychange', () => {
                if (engine) engine.setVisible(!document.hidden);
            });

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', currentTheme);
                    localStorage.setItem('forma_theme', currentTheme);
                    if (engine) {
                        engine.setOptions({ bg: currentTheme === 'dark' ? bgDark : bgLight });
                    }
                    loadTexture(currentTheme);
                });
            }
        })();
    </script>
</body>
</html>
