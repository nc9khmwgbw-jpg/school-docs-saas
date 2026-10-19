<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>429 — Trop de Requêtes | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --canvas-bg: #edece8;
            --canvas-card: #ffffff;
            --ink-primary: #141414;
            --ink-secondary: #524f4b;
            --ink-muted: #8a857b;
            --hairline: rgba(0, 0, 0, 0.08);
            --hairline-strong: rgba(0, 0, 0, 0.16);
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
            --hairline: rgba(255, 255, 255, 0.08);
            --hairline-strong: rgba(255, 255, 255, 0.16);
        }
        body {
            font-family: var(--font-sans);
            background-color: var(--canvas-bg);
            color: var(--ink-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }
        .error-card {
            background: var(--canvas-card);
            border: 1px solid var(--hairline-strong);
            border-radius: 20px;
            max-width: 580px;
            width: 100%;
            padding: 48px 36px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.06);
            position: relative;
        }
        .seal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 4px 12px;
            background: rgba(217, 119, 6, 0.08);
            border: 1px solid rgba(217, 119, 6, 0.25);
            border-radius: 9999px;
            color: #d97706;
            margin-bottom: 24px;
        }
        .error-code {
            font-family: var(--font-mono);
            font-size: 56px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            color: var(--ink-primary);
            margin-bottom: 12px;
        }
        .error-title {
            font-family: var(--font-serif);
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 14px;
            line-height: 1.25;
        }
        .error-title em {
            font-style: italic;
            font-weight: 400;
        }
        .error-desc {
            font-size: 14px;
            color: var(--ink-secondary);
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .action-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--ink-primary);
            color: var(--canvas-card);
            border-radius: 9999px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: opacity 0.15s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .error-footer {
            margin-top: 32px;
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-muted);
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="seal-badge">
            <span>🛡️</span>
            <span>Régulation de Trafic &bull; Anti-Abus</span>
        </div>

        <div class="error-code">429</div>

        <h1 class="error-title">
            Trop de <em>Requêtes</em>
        </h1>

        <p class="error-desc">
            Le nombre de requêtes émises depuis votre adresse IP a dépassé le seuil de tolérance de sécurité. Veuillez patienter une minute avant de renouveler votre opération.
        </p>

        <div class="action-row">
            <button onclick="setTimeout(() => window.location.reload(), 1000);" class="btn-primary">
                <span>Réessayer dans un instant</span>
                <span>&rarr;</span>
            </button>
        </div>

        <div class="error-footer">
            DocScolaire SaaS &bull; Protection Anti-Bruteforce &bull; Redis Rate-Limiter
        </div>
    </div>
</body>
</html>
