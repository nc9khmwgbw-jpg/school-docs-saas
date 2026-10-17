<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Mot de passe oublié | {{ $code_unique }}</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
            --ink-primary: #141414;
            --ink-secondary: #524f4b;
            --ink-muted: #8a857b;
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.16);
            --input-bg: rgba(255, 255, 255, 0.95);
            --btn-bg: #141414;
            --btn-text: #ffffff;
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
            --btn-text: #0e0e0d;
            --btn-hover: #ffffff;
        }

        body {
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .auth-container-clean {
            position: relative;
            max-width: 460px;
            width: 100%;
        }

        .auth-card-clean {
            background: var(--canvas-card);
            backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--hairline-strong);
            border-radius: 24px;
            padding: clamp(28px, 5vw, 42px);
            box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.06);
            position: relative;
        }

        .badge-school-code {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-muted);
            background: rgba(0, 0, 0, 0.04);
            border: 1px solid var(--hairline);
            padding: 4px 12px;
            border-radius: 9999px;
            display: inline-block;
            margin-bottom: 16px;
        }
        [data-theme="dark"] .badge-school-code {
            background: rgba(255, 255, 255, 0.05);
        }

        .auth-card-title {
            font-size: 26px;
            font-weight: 900;
            color: var(--ink-primary);
            letter-spacing: -0.04em;
            margin-bottom: 6px;
        }
        .auth-card-title em {
            font-family: var(--font-serif);
            font-style: italic;
            font-weight: 400;
        }

        .auth-card-sub {
            font-size: 13.5px;
            color: var(--ink-secondary);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .label-clean {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-secondary);
            margin-bottom: 8px;
        }

        .input-clean-light {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            background: var(--input-bg);
            border: 1px solid var(--hairline-strong);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            color: var(--ink-primary);
            outline: none;
            transition: all 0.2s ease;
        }
        .input-clean-light:focus {
            border-color: var(--ink-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }
        [data-theme="dark"] .input-clean-light:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }
        .forma-solid-btn:hover {
            background: var(--btn-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.22);
        }

        .forma-link-subtle {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink-secondary);
            text-decoration: none;
            transition: color 0.15s;
        }
        .forma-link-subtle:hover {
            color: var(--ink-primary);
        }
    </style>
</head>
<body>
    <div class="auth-container-clean">
        <div class="auth-card-clean">
            <span class="badge-school-code">— {{ $code_unique }}</span>
            <h1 class="auth-card-title">Récupération <em>d'accès.</em></h1>
            <p class="auth-card-sub">Saisissez votre adresse email académique pour recevoir un lien de réinitialisation sécurisé valable 60 minutes.</p>

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

            <form action="{{ route('tenant.password.forgot.send', ['code_unique' => $code_unique]) }}" method="POST">
                @csrf
                <div style="margin-bottom:18px;">
                    <label for="email" class="label-clean">Adresse email académique</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="input-clean-light"
                        placeholder="sara.alami@ens.ma" 
                        required 
                        autocomplete="email"
                    >
                </div>
                <button type="submit" class="forma-solid-btn">
                    <span>Envoyer le lien sécurisé</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--hairline);text-align:center;">
                <a href="{{ route('tenant.portal', ['code_unique' => $code_unique]) }}" class="forma-link-subtle">
                    &larr; Retour au portail {{ $code_unique }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
