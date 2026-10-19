<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Console Super Admin | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <meta name="theme-color" content="#edece8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('forma_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --canvas-bg: #edece8;
            --canvas-card: rgba(255, 255, 255, 0.90);
            --ink-primary: #121210;
            --ink-secondary: #5a5953;
            --ink-muted: #8c8a82;
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.16);
            --input-bg: rgba(255, 255, 255, 0.95);
            --btn-bg: #121210;
            --btn-text: #f5f4f0;
            --btn-hover: #000000;
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        [data-theme="dark"] {
            --canvas-bg: #0e0e0d;
            --canvas-card: rgba(26, 26, 24, 0.90);
            --ink-primary: #f5f4f0;
            --ink-secondary: #aba79e;
            --ink-muted: #737067;
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
            --input-bg: rgba(20, 20, 18, 0.9);
            --btn-bg: #f5f4f0;
            --btn-text: #121210;
            --btn-hover: #ffffff;
        }

        body {
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Barre supérieure Minimaliste */
        .forma-top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            padding: 0 clamp(20px, 4vw, 48px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
            border-bottom: 1px solid var(--hairline);
            backdrop-filter: blur(12px);
            background: var(--canvas-card);
        }

        .forma-top-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ink-primary);
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .forma-top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--hairline-strong);
            background: transparent;
            color: var(--ink-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .theme-toggle-btn:hover {
            color: var(--ink-primary);
            border-color: var(--ink-primary);
            transform: scale(1.05);
        }

        /* Carte de Connexion */
        .super-login-wrapper {
            position: relative;
            max-width: 460px;
            width: 100%;
            margin-top: 40px;
        }

        .super-login-card {
            background: var(--canvas-card);
            border: 1px solid var(--hairline);
            border-radius: 24px;
            padding: clamp(28px, 5vw, 44px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(20px);
        }

        .kicker-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .badge-kicker {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-muted);
        }

        .badge-root {
            font-family: var(--font-mono);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        [data-theme="dark"] .badge-root {
            background: rgba(16, 185, 129, 0.18);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.35);
        }

        .super-title {
            font-family: var(--font-sans);
            font-size: clamp(24px, 3.2vw, 30px);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.15;
            color: var(--ink-primary);
            margin-bottom: 8px;
        }

        .super-title em {
            font-family: var(--font-serif);
            font-weight: 400;
            font-style: italic;
        }

        .super-sub {
            font-size: 13.5px;
            color: var(--ink-secondary);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        /* Demo box */
        .demo-super-box {
            background: rgba(0, 0, 0, 0.03);
            border: 1px dashed var(--hairline-strong);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 12.5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        [data-theme="dark"] .demo-super-box {
            background: rgba(255, 255, 255, 0.03);
        }
        .demo-super-box:hover {
            border-color: var(--ink-primary);
            transform: translateY(-1px);
        }

        .demo-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            color: var(--ink-primary);
        }

        .demo-pill-btn {
            font-size: 11px;
            font-weight: 600;
            background: var(--ink-primary);
            color: var(--canvas-bg);
            padding: 2px 8px;
            border-radius: 9999px;
        }

        .demo-creds {
            color: var(--ink-muted);
            font-family: var(--font-mono);
            font-size: 11px;
            margin-top: 4px;
        }

        /* Formulaire */
        .auth-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--ink-primary);
            margin-bottom: 7px;
        }

        .forma-input-field {
            width: 100%;
            height: 46px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid var(--hairline-strong);
            background: var(--input-bg);
            color: var(--ink-primary);
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
        }
        .forma-input-field:focus {
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.06);
        }
        [data-theme="dark"] .forma-input-field:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.12);
        }

        .field-wrap-rel {
            position: relative;
            display: flex;
            align-items: center;
        }
        .field-wrap-rel .forma-input-field {
            padding-right: 44px;
        }

        .pwd-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--ink-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            transition: color 0.15s;
        }
        .pwd-toggle-btn:hover {
            color: var(--ink-primary);
        }

        /* Bouton Plein Noir Arrondi */
        .forma-solid-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 42px;
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
            margin-top: 14px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        .forma-solid-btn:hover {
            background: var(--btn-hover);
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

        .forma-card-footer {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--hairline);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
        }

        .forma-link-subtle {
            color: var(--ink-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }
        .forma-link-subtle:hover {
            color: var(--ink-primary);
        }

        .security-badge {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-muted);
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>

    <!-- Barre supérieure Minimaliste -->
    <header class="forma-top-bar">
        <a href="{{ route('home') }}" class="forma-top-brand">
            <span>DocScolaire<sup>®</sup></span>
        </a>

        <div class="forma-top-actions">
            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Basculer Mode Clair / Sombre" aria-label="Mode Clair / Sombre">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </div>
    </header>

    <div class="super-login-wrapper">
        <div class="super-login-card">
            <div class="kicker-row">
                <span class="badge-kicker">— SUPERVISION GLOBALE</span>
                <span class="badge-root">Master Root</span>
            </div>

            <h1 class="super-title">Console <em>SuperAdmin.</em></h1>
            <p class="super-sub">Supervision du réseau multi-établissements, gestion des licences et monitoring de la plateforme SaaS.</p>

            @if($errors->any())
                <div style="background:rgba(239, 68, 68, 0.1);border:1px solid rgba(239, 68, 68, 0.3);color:#ef4444;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:20px;">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            @if(session('info'))
                <div style="background:rgba(16, 185, 129, 0.1);border:1px solid rgba(16, 185, 129, 0.3);color:#059669;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:20px;">
                    ℹ️ {{ session('info') }}
                </div>
            @endif

            <!-- Démo Shortcut SuperAdmin -->
            <div class="demo-super-box" id="fill-super-btn" title="Cliquer pour pré-remplir les identifiants SuperAdmin">
                <div class="demo-title-row">
                    <span>💡 Compte Master SaaS :</span>
                    <span class="demo-pill-btn">Remplir</span>
                </div>
                <div class="demo-creds">
                    superadmin@plateforme-saas.com &bull; Secret2026!
                </div>
            </div>

            <form action="{{ route('superadmin.login.post') }}" method="POST" id="super-login-form">
                @csrf
                <div style="margin-bottom:18px;">
                    <label for="email" class="auth-label">Adresse email Master</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="forma-input-field"
                        required 
                        autocomplete="email" 
                        value="{{ old('email', 'superadmin@plateforme-saas.com') }}"
                        placeholder="superadmin@plateforme-saas.com"
                    >
                </div>

                <div style="margin-bottom:18px;">
                    <label for="mot_de_passe" class="auth-label">Mot de passe Master</label>
                    <div class="field-wrap-rel">
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="forma-input-field"
                            required 
                            autocomplete="current-password" 
                            value="Secret2026!"
                            placeholder="••••••••••••"
                        >
                        <button type="button" class="pwd-toggle-btn" id="togglePasswordBtn" title="Afficher/masquer">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="forma-solid-btn" id="btn-super-submit">
                    <span>Accéder à la Console Root</span>
                    <span class="btn-arrow" aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <div class="forma-card-footer">
                <a href="{{ route('home') }}" class="forma-link-subtle">&larr; Retour à l'accueil</a>
                <span class="security-badge">MFA / 2FA Sécurisé</span>
            </div>
        </div>
    </div>

    <script>
        // Gestion du thème clair / sombre
        const themeBtn = document.getElementById('themeToggleBtn');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme') || 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('forma_theme', next);
            });
        }

        // Autofill démo
        document.getElementById('fill-super-btn')?.addEventListener('click', () => {
            document.getElementById('email').value = 'superadmin@plateforme-saas.com';
            document.getElementById('mot_de_passe').value = 'Secret2026!';
        });

        // Toggle Password visibility
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const pwdInput = document.getElementById('mot_de_passe');
        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
            });
        }
    </script>
</body>
</html>
