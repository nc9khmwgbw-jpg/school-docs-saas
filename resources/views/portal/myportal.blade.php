<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="MyPortal — Passerelle Universelle Étudiante. Accédez au portail officiel sécurisé et cloisonné de votre établissement d'enseignement supérieur.">
    <title>MyPortal — Portail Étudiant Universel | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#edece8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.16);
            --btn-bg: #141414;
            --btn-text: #ffffff;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
            --radius-card: 16px;
            --radius-pill: 9999px;
            --shadow-subtle: 0 4px 20px -2px rgba(20, 20, 20, 0.06);
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: #171716;
            --ink-primary: #f5f4f0;
            --ink-secondary: #a8a49c;
            --ink-muted: #6e6b63;
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.18);
            --btn-bg: #f5f4f0;
            --btn-text: #141414;
            --shadow-subtle: 0 4px 24px -2px rgba(0, 0, 0, 0.4);
        }

        html, body {
            min-height: 100vh;
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
            line-height: 1.5;
        }

        /* ── Header Officiel ── */
        .portal-header {
            width: 100%;
            padding: 20px clamp(20px, 4vw, 48px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--hairline);
            background: var(--canvas-bg);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(8px);
        }

        .portal-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--ink-primary);
        }

        .portal-logo-mark {
            width: 36px;
            height: 36px;
            border: 1.5px solid var(--ink-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-serif);
            font-size: 18px;
            font-weight: 700;
            border-radius: 8px;
        }

        .portal-logo-text {
            font-family: var(--font-serif);
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .portal-logo-text span {
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-muted);
            margin-left: 6px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .theme-toggle-btn {
            background: transparent;
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            width: 38px;
            height: 38px;
            border-radius: var(--radius-pill);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .theme-toggle-btn:hover {
            background: var(--hairline);
        }

        .btn-link-subtle {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-secondary);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--hairline);
            transition: all 0.2s ease;
        }
        .btn-link-subtle:hover {
            color: var(--ink-primary);
            border-color: var(--hairline-strong);
        }

        /* ── Conteneur Principal ── */
        .gateway-container {
            max-width: 1100px;
            margin: 48px auto 90px;
            padding: 0 clamp(16px, 4vw, 36px);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .editorial-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border: 1px solid var(--hairline-strong);
            border-radius: var(--radius-pill);
            font-family: var(--font-mono);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-secondary);
            margin-bottom: 24px;
        }
        .editorial-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--ink-primary);
        }

        .gateway-title {
            font-family: var(--font-serif);
            font-size: clamp(32px, 3.5vw + 1rem, 54px);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -0.025em;
            color: var(--ink-primary);
            max-width: 820px;
            margin-bottom: 18px;
        }

        .gateway-subtitle {
            font-size: clamp(15px, 0.8vw + 0.6rem, 17px);
            line-height: 1.65;
            color: var(--ink-secondary);
            max-width: 680px;
            margin-bottom: 40px;
        }

        /* ── Boîte de Recherche / Détection Directe ── */
        .matricule-search-box {
            width: 100%;
            max-width: 660px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: 18px;
            padding: 20px 24px;
            box-shadow: var(--shadow-subtle);
            margin-bottom: 56px;
            text-align: left;
        }

        .matricule-search-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-muted);
            margin-bottom: 10px;
        }

        .matricule-search-row {
            display: flex;
            gap: 10px;
        }

        .matricule-input {
            flex: 1;
            height: 50px;
            padding: 0 18px;
            font-family: var(--font-mono);
            font-size: 14.5px;
            color: var(--ink-primary);
            background: transparent;
            border: 1px solid var(--hairline-strong);
            border-radius: 10px;
            outline: none;
            transition: all 0.2s ease;
        }
        .matricule-input:focus {
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(20, 20, 20, 0.1);
        }

        .matricule-btn {
            height: 50px;
            padding: 0 24px;
            border-radius: 10px;
            background: var(--btn-bg);
            color: var(--btn-text);
            border: 0;
            font-family: var(--font-sans);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .matricule-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* ── Grille des Établissements ── */
        .section-separator {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
        }
        .separator-line {
            flex: 1;
            height: 1px;
            background: var(--hairline-strong);
        }
        .separator-text {
            font-family: var(--font-mono);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-muted);
        }

        .schools-gateway-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 22px;
            margin-bottom: 50px;
        }

        .school-dedicated-card {
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: var(--radius-card);
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: left;
            position: relative;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: var(--shadow-subtle);
        }
        .school-dedicated-card:hover {
            transform: translateY(-4px);
            border-color: var(--ink-primary);
        }

        .card-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-sigle-badge {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 700;
            color: var(--ink-primary);
            border: 1px solid var(--hairline-strong);
            padding: 4px 10px;
            border-radius: 6px;
        }

        .card-code-meta {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-muted);
        }

        .card-school-name {
            font-family: var(--font-serif);
            font-size: 20px;
            font-weight: 700;
            color: var(--ink-primary);
            letter-spacing: -0.015em;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .card-school-sub {
            font-size: 13px;
            color: var(--ink-secondary);
            line-height: 1.55;
            margin-bottom: 24px;
        }

        .btn-access-school {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 44px;
            padding: 0 16px;
            border-radius: 10px;
            background: transparent;
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .school-dedicated-card:hover .btn-access-school {
            background: var(--btn-bg);
            color: var(--btn-text);
            border-color: var(--btn-bg);
        }

        /* ── Sécurité Footer Note ── */
        .gateway-security-notice {
            margin-top: 10px;
            padding: 18px 24px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: 14px;
            font-size: 12.5px;
            color: var(--ink-secondary);
            display: flex;
            align-items: center;
            gap: 14px;
            max-width: 760px;
            text-align: left;
            line-height: 1.5;
        }
        .security-seal-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .matricule-search-row {
                flex-direction: column;
            }
            .matricule-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- En-tête Officiel -->
    <header class="portal-header">
        <a href="{{ route('home') }}" class="portal-logo-wrap" title="Retour à l'accueil officiel">
            <div class="portal-logo-mark">§</div>
            <div class="portal-logo-text">MyPortal<span>/ Espace Étudiant</span></div>
        </a>

        <div class="header-actions">
            <a href="{{ route('public.verifier', ['uuid' => '00000000-0000-0000-0000-000000000000']) }}" class="btn-link-subtle">
                Vérifier un document &rarr;
            </a>
            <button type="button" class="theme-toggle-btn" id="theme-btn" title="Basculer thème sombre / clair" aria-label="Basculer thème">
                ◐
            </button>
        </div>
    </header>

    <!-- Conteneur Principal -->
    <main class="gateway-container">

        <div class="editorial-badge">
            Espace Étudiant &bull; Architecture Multi-Tenant &bull; Cloisonnement Cryptographique
        </div>

        <h1 class="gateway-title">
            Passerelle Universelle d'Orientation Étudiante
        </h1>

        <p class="gateway-subtitle">
            Chaque établissement d'enseignement supérieur dispose d'un espace souverain et isolé. Renseignez votre matricule institutionnel ou sélectionnez votre école pour accéder à vos documents officiels.
        </p>

        <!-- Recherche / Détection en Direct par Matricule ou Email -->
        <div class="matricule-search-box">
            <div class="matricule-search-label">
                <span>Accès Direct par Matricule ou Email</span>
                <span style="font-weight:600;color:var(--ink-primary);">Orientation instantanée</span>
            </div>
            <form action="{{ route('ecole.recherche') }}" method="POST" id="gateway-search-form">
                @csrf
                <div class="matricule-search-row">
                    <input
                        type="text"
                        name="code_unique"
                        id="matricule-input"
                        class="matricule-input"
                        placeholder="Ex : E2026-9901, sara.alami@ens.ma ou ENS-2026"
                        required
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <button type="submit" class="matricule-btn" id="gateway-btn">
                        <span>Ouvrir mon portail &rarr;</span>
                    </button>
                </div>
            </form>
        </div>



        <!-- Note de Sécurité Officielle -->
        <div class="gateway-security-notice">
            <span class="security-seal-icon">⚖️</span>
            <div>
                <strong>Étanchéité Multi-Tenants &amp; Souveraineté Numérique :</strong> Aucun compte étudiant ne peut interagir avec les registres d'un établissement tiers. Chaque document généré est scellé par l'empreinte SHA-256 de son école d'appartenance.
            </div>
        </div>

    </main>

    <script>
        // Gestionnaire de thème sombre / clair
        const themeBtn = document.getElementById('theme-btn');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        if (themeBtn) {
            themeBtn.textContent = savedTheme === 'dark' ? '☼' : '◐';
            themeBtn.addEventListener('click', () => {
                const current = html.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                themeBtn.textContent = next === 'dark' ? '☼' : '◐';
            });
        }

        // Détection automatique en direct : si l'étudiant tape un matricule ou email connu, redirection directe
        const input = document.getElementById('matricule-input');
        let timer = null;

        if (input) {
            input.addEventListener('input', function() {
                const val = this.value.trim();
                if (!val) return;

                clearTimeout(timer);
                timer = setTimeout(() => {
                    const upper = val.toUpperCase();
                    const lower = val.toLowerCase();

                    if (upper.startsWith('E2026') || lower.includes('@ens.ma') || upper === 'ENS-2026') {
                        window.location.href = '/portal/ENS-2026';
                    } else if (upper.startsWith('P2026') || lower.includes('@poly.ma') || upper === 'POLY-2026') {
                        window.location.href = '/portal/POLY-2026';
                    } else if (upper.startsWith('F2026') || lower.includes('@fst.ma') || upper === 'FST-2026') {
                        window.location.href = '/portal/FST-2026';
                    }
                }, 350);
            });
        }
    </script>
</body>
</html>
