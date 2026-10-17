<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Registre des Archives &amp; Historique | {{ $ecole->nom }}</title>
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
            display: flex;
            flex-direction: column;
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

        .forma-profile-avatar-wrap {
            position: relative;
            width: 68px;
            height: 68px;
            flex-shrink: 0;
        }
        .forma-profile-photo-hero {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            object-fit: cover;
            border: 2px solid var(--hairline-strong);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            background: var(--canvas-subtle);
            display: block;
        }
        .forma-profile-photo-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--accent-emerald);
            color: #ffffff;
            font-size: 10px;
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

        .forma-outline-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 36px;
            padding: 0 16px;
            border-radius: 9999px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .forma-outline-btn:hover {
            border-color: var(--ink-primary);
            transform: translateY(-1px);
        }

        /* ── Container Historique ── */
        .forma-hist-container {
            max-width: 1240px;
            width: 100%;
            margin: 32px auto 80px;
            padding: 0 clamp(20px, 4vw, 56px);
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .forma-hist-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .forma-hist-kicker {
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
        .forma-hist-kicker::before {
            content: '—';
            color: var(--ink-muted);
        }

        .forma-hist-title {
            font-size: clamp(24px, 2.2vw + 0.5rem, 34px);
            font-weight: 900;
            letter-spacing: -0.035em;
            color: var(--ink-primary);
            line-height: 1.15;
        }
        .forma-hist-title em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
            letter-spacing: -0.02em;
            display: block;
            font-size: 0.85em;
            color: var(--ink-secondary);
            margin-top: 4px;
        }

        /* Barre de Recherche Épurée */
        .forma-search-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--canvas-card);
            backdrop-filter: blur(14px);
            border: 1px solid var(--hairline-strong);
            border-radius: 9999px;
            padding: 0 16px;
            height: 42px;
            width: min(100%, 340px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }
        .forma-search-wrap:focus-within {
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }
        .forma-search-input {
            background: transparent;
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 13px;
            color: var(--ink-primary);
            width: 100%;
        }
        .forma-search-input::placeholder {
            color: var(--ink-muted);
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

        .forma-doc-tag {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
            color: var(--ink-secondary);
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--canvas-subtle);
            border: 1px solid var(--hairline);
            display: inline-block;
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
    </style>
</head>
<body>

    <!-- ── Barre Supérieure Dédiée Établissement ── -->
    <header class="forma-nav-header">
        <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="forma-brand-wrap">
            @if($ecole->logo_url)
                <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="height:28px;width:auto;max-width:110px;object-fit:contain;vertical-align:middle;margin-right:8px;filter:grayscale(100%);">
            @endif
            <span class="forma-brand-sigle">{{ $ecole->sigle }}<sup>&reg;</sup></span>
            <span class="forma-brand-sep">/</span>
            <span class="forma-brand-sub">Registre des Archives</span>
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

            <a href="{{ route('tenant.dashboard', ['code_unique' => $ecole->code_unique]) }}" class="forma-outline-btn">
                <span aria-hidden="true">&larr;</span>
                <span>Retour au Coffre-Fort</span>
            </a>
        </div>
    </header>

    <!-- ── Contenu Principal ── -->
    <main class="forma-hist-container">

        <div class="forma-hist-head">
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <div class="forma-profile-avatar-wrap">
                    <img src="{{ $etudiant->photo_url }}" alt="Photo officielle de {{ $etudiant->prenom }} {{ $etudiant->nom }}" class="forma-profile-photo-hero">
                    <span class="forma-profile-photo-badge" title="Profil certifié">✓</span>
                </div>
                <div>
                    <div class="forma-hist-kicker">
                        ARCHIVES ACADÉMIQUES CERTIFIÉES &bull; {{ $ecole->code_unique }}
                    </div>
                    <h1 class="forma-hist-title">
                        Historique Intégral des Documents
                        <em>Titulaire : {{ $etudiant->prenom }} {{ $etudiant->nom }} &bull; Matricule : {{ $etudiant->matricule }}</em>
                    </h1>
                </div>
            </div>

            <div class="forma-search-wrap">
                <span style="color:var(--ink-muted);font-size:13px;">🔍</span>
                <input type="text" class="forma-search-input" id="searchFilterInput" placeholder="Filtrer un document...">
            </div>
        </div>

        <div class="forma-table-card">
            <table class="forma-table" id="archiveTable">
                <thead>
                    <tr>
                        <th>Titre du Document</th>
                        <th>Type Officiel</th>
                        <th>Date de Demande</th>
                        <th>Date de Scellement</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions &amp; Sceau</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                        <tr>
                            <td>
                                <div style="font-weight:700;color:var(--ink-primary);font-size:14px;">
                                    {{ $demande->modele?->titre ?? 'Document Académique' }}
                                </div>
                                @if($demande->documentGenere)
                                    <div style="font-size:11px;color:var(--ink-muted);font-family:var(--font-mono);margin-top:2px;">
                                        UUID : {{ $demande->documentGenere->uuid }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="forma-doc-tag">
                                    {{ $demande->modele?->type_document ?? 'Officiel' }}
                                </span>
                            </td>
                            <td style="font-family:var(--font-mono);font-size:12px;color:var(--ink-secondary);">
                                {{ $demande->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="font-family:var(--font-mono);font-size:12px;color:var(--ink-secondary);">
                                {{ $demande->date_traitement ? $demande->date_traitement->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td>
                                @if($demande->statut === 'genere')
                                    <span class="forma-status-chip forma-status-success">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#10b981;"></span>
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
                            <td style="text-align:right;">
                                @if($demande->documentGenere)
                                    <div style="display:inline-flex;gap:8px;justify-content:flex-end;">
                                        <a href="{{ route('tenant.documents.download', ['code_unique' => $ecole->code_unique, 'uuid' => $demande->documentGenere->uuid]) }}" class="forma-action-btn-sm">
                                            <span>Télécharger PDF</span>
                                            <span aria-hidden="true">&darr;</span>
                                        </a>
                                        <a href="{{ route('public.verifier', ['uuid' => $demande->documentGenere->uuid]) }}" class="forma-action-ghost-sm" target="_blank" title="Contrôler par QR code">
                                            <span>Vérifier</span>
                                            <span aria-hidden="true">&nearr;</span>
                                        </a>
                                    </div>
                                @elseif($demande->statut === 'refuse')
                                    <span style="font-size:12px;color:#dc2626;font-weight:600;">Demande rejetée</span>
                                @else
                                    <span style="font-size:12px;color:var(--ink-muted);">En traitement</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:48px 24px;color:var(--ink-muted);">
                                Aucun document dans l'historique pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>

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

        // Filtrage dynamique du tableau
        const filterInput = document.getElementById('searchFilterInput');
        const table = document.getElementById('archiveTable');
        if (filterInput && table) {
            filterInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
