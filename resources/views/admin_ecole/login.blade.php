<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Administration Établissement | DocScolaire®</title>
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
            --canvas-card: rgba(255, 255, 255, 0.85);
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
            --canvas-card: rgba(26, 26, 24, 0.85);
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
            transition: background-color 0.3s ease, color 0.3s ease;
            position: relative;
        }

        /* Top Bar Minimaliste */
        .forma-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 18px clamp(20px, 4vw, 48px);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .forma-top-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ink-primary);
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }
        .forma-top-brand sup {
            font-size: 10px;
            color: var(--ink-muted);
        }

        .theme-toggle-btn {
            width: 38px;
            height: 38px;
            border-radius: 9999px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            color: var(--ink-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.05);
            background: rgba(0, 0, 0, 0.05);
        }
        [data-theme="dark"] .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .admin-login-wrapper {
            position: relative;
            max-width: 480px;
            width: 100%;
            margin-top: 20px;
        }

        .admin-login-card {
            background: var(--canvas-card);
            backdrop-filter: blur(28px) saturate(180%);
            border: 1px solid var(--hairline-strong);
            border-radius: 28px;
            padding: clamp(30px, 5vw, 44px);
            box-shadow: 0 20px 48px -12px rgba(0, 0, 0, 0.07);
            position: relative;
        }

        .kicker-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .badge-kicker {
            font-family: var(--font-mono);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-muted);
            background: rgba(0, 0, 0, 0.04);
            border: 1px solid var(--hairline);
            padding: 4px 12px;
            border-radius: 9999px;
        }
        [data-theme="dark"] .badge-kicker {
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-title {
            font-size: 27px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
            margin-bottom: 8px;
            line-height: 1.15;
        }
        .admin-title em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
        }

        .admin-sub {
            font-size: 13.5px;
            color: var(--ink-secondary);
            margin-bottom: 24px;
            line-height: 1.55;
        }

        /* Demo credentials shortcuts */
        .demo-credentials-box {
            background: rgba(0, 0, 0, 0.025);
            border: 1px solid var(--hairline);
            border-radius: 16px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        [data-theme="dark"] .demo-credentials-box {
            background: rgba(255, 255, 255, 0.03);
        }

        .demo-cred-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .demo-school-label {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--ink-secondary);
        }
        .demo-cred-pill {
            cursor: pointer;
            padding: 4px 10px;
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
            color: var(--ink-primary);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .demo-cred-pill:hover {
            background: var(--ink-primary);
            color: var(--canvas-bg);
            transform: translateY(-1px);
        }

        .auth-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-secondary);
            margin-bottom: 8px;
        }

        .forma-input-field {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: var(--input-bg);
            border: 1px solid var(--hairline-strong);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            color: var(--ink-primary);
            outline: none;
            transition: all 0.2s ease;
        }
        .forma-input-field:focus {
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }
        [data-theme="dark"] .forma-input-field:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .field-wrap-rel {
            position: relative;
            display: flex;
            align-items: center;
        }
        .pwd-toggle-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--ink-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 4px;
            transition: color 0.15s;
        }
        .pwd-toggle-btn:hover {
            color: var(--ink-primary);
        }

        .forma-solid-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 48px;
            border-radius: 9999px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            margin-top: 14px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        .forma-solid-btn:hover {
            background: var(--btn-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .forma-card-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--hairline);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
        }
        .forma-link-subtle {
            font-weight: 600;
            color: var(--ink-secondary);
            text-decoration: none;
            transition: color 0.15s;
        }
        .forma-link-subtle:hover {
            color: var(--ink-primary);
        }
        .security-badge-sub {
            font-family: var(--font-mono);
            font-size: 10.5px;
            color: var(--ink-muted);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Toast notification */
        .forma-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--ink-primary);
            color: var(--canvas-bg);
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 999;
        }
        .forma-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Barre supérieure Minimaliste -->
    <header class="forma-top-bar">
        <a href="{{ route('home') }}" class="forma-top-brand">
            <span>DocScolaire<sup>®</sup></span>
        </a>

        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Basculer le thème">
            <svg id="themeIconMoon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
    </header>

    <div class="admin-login-wrapper">
        <div class="admin-login-card">
            @if(isset($ecole) && $ecole)
                @if($ecole->logo_url)
                    <div style="margin-bottom:14px;">
                        <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="height:38px;max-width:140px;object-fit:contain;">
                    </div>
                @endif
                <div class="kicker-row">
                    <span class="badge-kicker">— ÉTABLISSEMENT : {{ $ecole->sigle }}</span>
                    <span class="badge-kicker">{{ $ecole->code_unique }}</span>
                </div>
                <h1 class="admin-title">{{ $ecole->nom }} <em>Administration.</em></h1>
                <p class="admin-sub">Accès sécurisé réservé aux directions et gestionnaires de scolarité de {{ $ecole->sigle }}.</p>
            @else
                <div class="kicker-row">
                    <span class="badge-kicker">— GOUVERNANCE ACADÉMIQUE</span>
                    <span class="badge-kicker">DOCSCOLAIRE.ADMIN</span>
                </div>
                <h1 class="admin-title">Espace <em>Établissement.</em></h1>
                <p class="admin-sub">Accès sécurisé réservé aux directions et gestionnaires de scolarité pour le scellement et la validation des documents officiels.</p>
            @endif

            @if(session('success'))
                <div style="background:rgba(16, 185, 129, 0.1);border:1px solid rgba(16, 185, 129, 0.3);color:#059669;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:20px;">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:rgba(239, 68, 68, 0.1);border:1px solid rgba(239, 68, 68, 0.3);color:#ef4444;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:20px;">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif



            <form action="{{ route('admin_ecole.login.post') }}" method="POST">
                @csrf
                <div style="margin-bottom:18px;">
                    <label for="email" class="auth-label">Email professionnel</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="forma-input-field"
                        placeholder="admin@ecole.ma" 
                        required 
                        autofocus
                    >
                </div>

                <div style="margin-bottom:18px;">
                    <label for="mot_de_passe" class="auth-label">Mot de passe</label>
                    <div class="field-wrap-rel">
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            value="" 
                            class="forma-input-field"
                            placeholder="••••••••••••" 
                            required
                        >
                        <button type="button" class="pwd-toggle-btn" id="togglePasswordBtn" title="Afficher/masquer">
                            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="forma-solid-btn">
                    <span>Accéder à l'Administration</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <div class="forma-card-footer">
                @if(isset($ecole) && $ecole)
                    <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="forma-link-subtle">&larr; Portail {{ $ecole->sigle }}</a>
                @else
                    <a href="{{ route('home') }}" class="forma-link-subtle">&larr; Retour à l'accueil</a>
                @endif
                <span class="security-badge-sub">Certifié SSL &bull; CNDP</span>
            </div>
        </div>
    </div>

    <div id="formaToast" class="forma-toast"></div>

    <script>
        // Password toggle
        const pwdInput = document.getElementById('mot_de_passe');
        const pwdToggleBtn = document.getElementById('togglePasswordBtn');
        if (pwdToggleBtn && pwdInput) {
            pwdToggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
            });
        }

        // Demo autofill
        function fillAdmin(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('mot_de_passe').value = pass;
            showToast('Identifiants pré-remplis avec succès.');
        }

        // Toast
        function showToast(msg) {
            const toast = document.getElementById('formaToast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        // Theme toggle
        const themeBtn = document.getElementById('themeToggleBtn');
        themeBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('forma_theme', next);
        });
    </script>
</body>
</html>
