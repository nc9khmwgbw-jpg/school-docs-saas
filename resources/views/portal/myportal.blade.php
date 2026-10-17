<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="MyPortal — Espace Étudiant Universel. Accédez au portail sécurisé et cloisonné de votre établissement d'enseignement.">
    <title>MyPortal — Espace Étudiant & Sélection de votre Établissement | DocScolaire SaaS</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/icon-192.png">
    <meta name="theme-color" content="#2563eb">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-page: #f8fafc;
            --bg-surface: #ffffff;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;
            --navy-dark: #0f172a;
            --navy-soft: #1e293b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            --border-light: #e2e8f0;
            --border-input: #cbd5e1;
            --success: #10b981;
            --success-light: #ecfdf5;
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-card: 0 10px 30px -5px rgba(15, 23, 42, 0.07), 0 4px 6px -2px rgba(15, 23, 42, 0.03);
            --radius-card: 20px;
            --radius-pill: 9999px;
        }

        html, body {
            min-height: 100vh;
            background-color: var(--bg-page);
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.07) 0px, transparent 45%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 40%),
                radial-gradient(at 50% 100%, rgba(226, 232, 240, 0.5) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            font-family: var(--font-main);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            line-height: 1.5;
        }

        /* ── Header ── */
        .portal-header {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid var(--border-light);
            padding: 14px clamp(16px, 4vw, 40px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .portal-logo-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .portal-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .portal-logo-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--navy-dark);
        }
        .portal-logo-title span {
            color: var(--primary);
        }

        .portal-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
            color: var(--navy-soft);
            box-shadow: var(--shadow-sm);
        }
        .portal-header-badge .badge-ping {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            animation: pulseDot 2s infinite ease-in-out;
        }

        /* ── Main Container ── */
        .gateway-container {
            max-width: 1140px;
            margin: 40px auto 80px;
            padding: 0 clamp(16px, 4vw, 36px);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .gateway-title {
            font-size: clamp(28px, 3vw + 1rem, 48px);
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -0.035em;
            color: var(--navy-dark);
            max-width: 840px;
            margin-bottom: 16px;
        }
        .gateway-title em {
            font-style: normal;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gateway-subtitle {
            font-size: clamp(15px, 0.8vw + 0.6rem, 18px);
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 700px;
            margin-bottom: 36px;
        }

        /* ── Barre de Détection Rapide par Matricule ── */
        .matricule-search-box {
            width: 100%;
            max-width: 680px;
            background: #ffffff;
            border: 1.5px solid var(--border-light);
            border-radius: 18px;
            padding: 16px 20px;
            box-shadow: var(--shadow-card);
            margin-bottom: 50px;
            text-align: left;
            position: relative;
        }
        .matricule-search-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .matricule-search-row {
            display: flex;
            gap: 10px;
        }
        .matricule-input {
            flex: 1;
            height: 48px;
            padding: 0 16px;
            font-family: var(--font-mono);
            font-size: 15px;
            color: var(--navy-dark);
            background: #f8fafc;
            border: 1px solid var(--border-input);
            border-radius: 10px;
            outline: none;
            transition: all 0.2s ease;
        }
        .matricule-input:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .matricule-btn {
            height: 48px;
            padding: 0 22px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .matricule-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
        }

        /* ── Grille des Établissements Cloisonnés ── */
        .schools-grid-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin-bottom: 20px;
            width: 100%;
            text-align: left;
        }
        .schools-gateway-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .school-dedicated-card {
            background: #ffffff;
            border: 1.5px solid var(--border-light);
            border-radius: 20px;
            padding: 26px;
            text-align: left;
            box-shadow: var(--shadow-card);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 20px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .school-dedicated-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .school-dedicated-card:hover {
            border-color: var(--primary-border);
            box-shadow: 0 16px 36px -8px rgba(37, 99, 235, 0.12);
            transform: translateY(-3px);
        }
        .school-dedicated-card:hover::before {
            opacity: 1;
        }

        .card-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .card-sigle-badge {
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            background: var(--primary-light);
            border: 1px solid var(--primary-border);
            padding: 4px 10px;
            border-radius: 8px;
        }
        .card-code-meta {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-muted);
        }

        .card-school-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--navy-dark);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .card-school-sub {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .btn-access-school {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 46px;
            border-radius: 12px;
            background: #ffffff;
            border: 1.5px solid var(--primary-border);
            color: var(--primary);
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .school-dedicated-card:hover .btn-access-school {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }

        /* ── Sécurité Footer Note ── */
        .gateway-security-notice {
            margin-top: 50px;
            padding: 16px 24px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 800px;
            box-shadow: var(--shadow-sm);
        }
        .security-lock {
            font-size: 20px;
            color: var(--success);
            flex-shrink: 0;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
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

    <!-- En-tête -->
    <header class="portal-header">
        <a href="{{ route('home') }}" class="portal-logo-wrap" title="Retour à l'accueil">
            <div class="portal-logo-icon">🎓</div>
            <div class="portal-logo-title">DocScolaire<span>.Portail</span></div>
        </a>

        <div class="portal-header-badge">
            <span class="badge-ping" aria-hidden="true"></span>
            <span>Cloisonnement Multi-Tenants Garanti</span>
        </div>
    </header>

    <!-- Conteneur Principal -->
    <main class="gateway-container">

        <div class="security-badge">
            <span>🔒 Espaces Académiques Cloisonnés &amp; Indépendants</span>
        </div>

        <h1 class="gateway-title">
            Accédez au portail officiel <em>exclusif</em> de votre établissement.
        </h1>

        <p class="gateway-subtitle">
            Afin de préserver la confidentialité stricte et l'étanchéité des données de chaque université et école d'ingénieurs, chaque établissement dispose de son propre portail d'authentification dédié.
        </p>

        <!-- Recherche / Détection par matricule ou email -->
        <div class="matricule-search-box">
            <div class="matricule-search-label">
                <span>Accès Direct par Matricule ou Email</span>
                <span style="color:var(--primary);font-weight:600;">Orientation instantanée</span>
            </div>
            <form action="{{ route('ecole.recherche') }}" method="POST" id="gateway-search-form">
                @csrf
                <div class="matricule-search-row">
                    <input
                        type="text"
                        name="code_unique"
                        id="matricule-input"
                        class="matricule-input"
                        placeholder="Ex : E2026-9901, sara.alami@ens.ma ou code ENS-2026"
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

        <div class="schools-grid-title">Sélectionnez votre établissement pour entrer dans son portail dédié :</div>

        <!-- Grille des Établissements Partenaires Hermétiques -->
        <div class="schools-gateway-grid">
            @forelse($ecoles as $ec)
            <div class="school-dedicated-card">
                <div>
                    <div class="card-header-top">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($ec->logo_url)
                                <img src="{{ $ec->logo_url }}" alt="Logo {{ $ec->nom }}" style="height:28px;max-width:90px;object-fit:contain;">
                            @endif
                            <span class="card-sigle-badge">{{ $ec->sigle }}</span>
                        </div>
                        <span class="card-code-meta">Code : {{ $ec->code_unique }}</span>
                    </div>
                    <h2 class="card-school-name">{{ $ec->nom }}</h2>
                    <p class="card-school-sub">
                        Espace étudiant officiel pour la commande et le téléchargement des attestations scellées de {{ $ec->sigle }}.
                    </p>
                </div>

                <a href="{{ route('tenant.portal', ['code_unique' => $ec->code_unique]) }}" class="btn-access-school">
                    <span>Accéder au portail {{ $ec->sigle }}</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
            @empty
            <div class="school-dedicated-card">
                <div>
                    <div class="card-header-top">
                        <span class="card-sigle-badge">ENS</span>
                        <span class="card-code-meta">Code : ENS-2026</span>
                    </div>
                    <h2 class="card-school-name">École Nationale Supérieure (ENS)</h2>
                    <p class="card-school-sub">Espace étudiant officiel pour l'ENS Rabat.</p>
                </div>
                <a href="{{ route('tenant.portal', ['code_unique' => 'ENS-2026']) }}" class="btn-access-school">
                    <span>Accéder au portail ENS &rarr;</span>
                </a>
            </div>
            @endforelse
        </div>

        <div class="gateway-security-notice">
            <span class="security-lock">🛡️</span>
            <div>
                <strong>Sécurité &amp; Souveraineté :</strong> Un étudiant d'un établissement ne peut en aucun cas s'authentifier sur le portail d'un autre établissement. Les sessions, registres et clés de scellement sont hermétiquement cloisonnés.
            </div>
        </div>

    </main>

    <script>
        // Détection automatique en direct : si l'étudiant tape un matricule ou email connu, on le redirige immédiatement
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
