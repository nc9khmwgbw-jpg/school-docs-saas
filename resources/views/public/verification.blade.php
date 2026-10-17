<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Vérification Officielle de Document | DocScolaire®</title>
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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --canvas-bg: #edece8;
            --canvas-card: rgba(255, 255, 255, 0.85);
            --canvas-card-solid: #ffffff;
            --canvas-subtle: rgba(0, 0, 0, 0.035);
            --ink-primary: #141414;
            --ink-secondary: #524f4b;
            --ink-muted: #8a857b;
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.14);
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: rgba(22, 22, 21, 0.85);
            --canvas-card-solid: #171716;
            --canvas-subtle: rgba(255, 255, 255, 0.04);
            --ink-primary: #f5f4f0;
            --ink-secondary: #a8a49c;
            --ink-muted: #6e6b63;
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
        }

        body {
            font-family: var(--font-sans);
            background: var(--canvas-bg);
            color: var(--ink-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 4vw, 48px) 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .verify-wrap {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Header Forma ── */
        .verify-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .brand-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--ink-primary);
            font-size: 19px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }
        .brand-link sup {
            font-size: 10px;
            color: var(--ink-muted);
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

        /* ── Carte Principale ── */
        .forma-verify-card {
            background: var(--canvas-card);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border: 1px solid var(--hairline-strong);
            border-radius: 26px;
            box-shadow: 0 20px 48px -10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card-hero {
            padding: 34px 32px 24px;
            text-align: center;
            border-bottom: 1px solid var(--hairline);
            position: relative;
        }

        .status-badge-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        .status-badge-valid {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        [data-theme="dark"] .status-badge-valid {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .status-badge-invalid {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .status-badge-expired {
            background: rgba(217, 119, 6, 0.1);
            color: #d97706;
            border: 1px solid rgba(217, 119, 6, 0.3);
        }

        .hero-title {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
            line-height: 1.25;
            margin-bottom: 8px;
        }
        .hero-title em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
        }
        .hero-sub {
            font-size: 13.5px;
            color: var(--ink-secondary);
            max-width: 440px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* ── Profil Certifié de l'Élève ── */
        .student-certified-row {
            padding: 20px 32px;
            display: flex;
            align-items: center;
            gap: 18px;
            background: var(--canvas-subtle);
            border-bottom: 1px solid var(--hairline);
        }
        .student-photo-box {
            width: 56px;
            height: 56px;
            border-radius: 9999px;
            object-fit: cover;
            border: 2px solid var(--canvas-card-solid);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            background: var(--ink-primary);
            color: var(--canvas-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
        }
        .student-info-meta {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .student-name-big {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--ink-primary);
        }
        .student-details-sub {
            font-size: 12.5px;
            color: var(--ink-secondary);
            margin-top: 2px;
        }
        .student-matricule-tag {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-primary);
            font-weight: 700;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            padding: 2px 8px;
            border-radius: 9999px;
            display: inline-block;
            margin-top: 4px;
            align-self: flex-start;
        }

        /* ── Table de Métadonnées ── */
        .cert-details-grid {
            padding: 12px 32px;
        }
        .cert-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--hairline);
            font-size: 13px;
        }
        .cert-row:last-child {
            border-bottom: none;
        }
        .cert-label {
            font-family: var(--font-mono);
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-muted);
        }
        .cert-value {
            font-weight: 600;
            color: var(--ink-primary);
            text-align: right;
            max-width: 320px;
        }
        .cert-value.mono {
            font-family: var(--font-mono);
            font-size: 11.5px;
        }

        /* ── Action Téléchargement Original ── */
        .original-action-box {
            padding: 24px 32px;
            background: var(--canvas-subtle);
            border-top: 1px solid var(--hairline);
            text-align: center;
        }
        .btn-forma-carbon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 13px 24px;
            background: var(--ink-primary);
            color: var(--canvas-bg);
            border: none;
            border-radius: 9999px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-forma-carbon:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.16);
            opacity: 0.92;
        }
        [data-theme="dark"] .btn-forma-carbon {
            background: #f5f4f0;
            color: #141414;
        }

        /* ── Blocs Cryptographiques ── */
        .crypto-block {
            padding: 18px 32px;
            border-top: 1px solid var(--hairline);
        }
        .crypto-label {
            font-family: var(--font-mono);
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-muted);
            margin-bottom: 4px;
        }
        .crypto-hash {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-secondary);
            word-break: break-all;
            background: var(--canvas-subtle);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--hairline);
        }

        /* ── Note de Confidentialité ── */
        .security-badge-note {
            margin-top: 20px;
            padding: 14px 18px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: var(--ink-secondary);
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }
        .security-badge-note strong {
            color: var(--ink-primary);
        }

        footer {
            margin-top: 24px;
            font-size: 12px;
            color: var(--ink-muted);
            text-align: center;
        }
        footer a {
            color: var(--ink-primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 6px;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="verify-wrap">
        <!-- Header -->
        <header class="verify-header">
            <a href="{{ route('home') }}" class="brand-link">
                <span>DocScolaire</span><sup>®</sup>
            </a>
            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Basculer le thème">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </header>

        <!-- Carte Principale -->
        <div class="forma-verify-card">
            @if($estAuthentique)
                @if($estExpire)
                    <div class="card-hero">
                        <div class="status-badge-wrap status-badge-expired">
                            <span class="beacon-dot" style="background:#d97706;"></span>
                            <span>Document Expiré</span>
                        </div>
                        <h1 class="hero-title">Validité <em>Échue.</em></h1>
                        <p class="hero-sub">Ce document est authentique mais sa date limite d'utilisation a expiré le <strong>{{ $infos['date_expiration'] }}</strong>.</p>
                    </div>
                @else
                    <div class="card-hero">
                        <div class="status-badge-wrap status-badge-valid">
                            <span class="beacon-dot" style="background:#10b981;"></span>
                            <span>Authentique &amp; Scellé</span>
                        </div>
                        <h1 class="hero-title">Document Officiel <em>Certifié.</em></h1>
                        <p class="hero-sub">L'authenticité et l'intégrité cryptographique de ce document officiel émis par l'établissement sont certifiées conformes.</p>
                    </div>
                @endif

                <!-- Profil Étudiant Certifié avec Photo -->
                <div class="student-certified-row">
                    @if(!empty($infos['photo_url']))
                        <img src="{{ $infos['photo_url'] }}" alt="Photo {{ $infos['prenom_etudiant'] }} {{ $infos['nom_etudiant'] }}" class="student-photo-box">
                    @else
                        <div class="student-photo-box">{{ mb_substr($infos['prenom_etudiant'] ?? 'E', 0, 1) }}</div>
                    @endif
                    <div class="student-info-meta">
                        <div class="student-name-big">{{ $infos['prenom_etudiant'] }} {{ $infos['nom_etudiant'] }}</div>
                        <div class="student-details-sub">{{ $infos['filiere'] }} &bull; {{ $infos['niveau'] }}</div>
                        <span class="student-matricule-tag">Matricule : {{ $infos['matricule'] }}</span>
                    </div>
                </div>

                <!-- Métadonnées Administratives -->
                <div class="cert-details-grid">
                    <div class="cert-row">
                        <span class="cert-label">Nature de la Pièce</span>
                        <span class="cert-value">{{ $infos['type_document'] }}</span>
                    </div>
                    <div class="cert-row">
                        <span class="cert-label">Établissement Émetteur</span>
                        <span class="cert-value">
                            @if(!empty($infos['logo_url']))
                                <img src="{{ $infos['logo_url'] }}" alt="Logo" style="max-height:18px;max-width:80px;object-fit:contain;vertical-align:middle;margin-right:6px;">
                            @endif
                            {{ $infos['ecole'] }}
                        </span>
                    </div>
                    <div class="cert-row">
                        <span class="cert-label">Année Universitaire</span>
                        <span class="cert-value">{{ $infos['annee_universitaire'] }}</span>
                    </div>
                    <div class="cert-row">
                        <span class="cert-label">Date d'Émission</span>
                        <span class="cert-value">{{ $infos['date_delivrance'] }}</span>
                    </div>
                    @if(!empty($infos['date_expiration']))
                        <div class="cert-row">
                            <span class="cert-label">Validité</span>
                            <span class="cert-value" style="{{ $estExpire ? 'color:#dc2626;' : 'color:#059669;' }}">
                                {{ $estExpire ? 'Expiré le ' : 'Valide jusqu\'au ' }}{{ $infos['date_expiration'] }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Bouton Copie Conforme Scellée -->
                <div class="original-action-box">
                    <a href="{{ route('public.verifier.original', ['uuid' => $uuid]) }}" target="_blank" class="btn-forma-carbon">
                        <span>🛡️</span>
                        <span>Consulter la Copie Conforme Scellée (PDF)</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Empreintes Cryptographiques -->
                <div class="crypto-block">
                    <div class="crypto-label">Empreinte SHA-256 (Inaltérable)</div>
                    <div class="crypto-hash">{{ $infos['hash_sha256'] }}</div>
                </div>
                <div class="crypto-block" style="padding-top:0;border-top:none;">
                    <div class="crypto-label">Identifiant Unique UUID</div>
                    <div class="crypto-hash">{{ $uuid }}</div>
                </div>

            @else
                <div class="card-hero">
                    <div class="status-badge-wrap status-badge-invalid">
                        <span class="beacon-dot" style="background:#ef4444;"></span>
                        <span>Document Non Reconnu</span>
                    </div>
                    <h1 class="hero-title">Document Non Trouvé</h1>
                    <p class="hero-sub">Aucune archive officielle ne correspond à cet identifiant dans le registre. Ce document n'a pas été émis ou son code est invalide.</p>
                </div>
                <div class="crypto-block">
                    <div class="crypto-label">Identifiant Scanné</div>
                    <div class="crypto-hash">{{ $uuid }}</div>
                </div>
            @endif
        </div>

        <!-- Note de Sécurité -->
        <div class="security-badge-note">
            <span style="font-size:16px;">🔒</span>
            <span>
                Contrôle cryptographique instantané conforme aux exigences de traçabilité et de protection des données (CNDP).
            </span>
        </div>

        <footer>
            &copy; {{ date('Y') }} DocScolaire SaaS &bull;
            <a href="{{ route('home') }}">Retour à l'accueil</a>
        </footer>
    </div>

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
</body>
</html>
