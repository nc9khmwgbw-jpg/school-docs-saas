<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Console Super Admin | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/modern-ui.css">
    <style>
        body {
            background-color: #060913;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 40%),
                radial-gradient(circle at 80% 30%, rgba(236, 72, 153, 0.1) 0px, transparent 40%),
                linear-gradient(180deg, #060913 0%, #0d1222 100%);
            background-attachment: fixed;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .super-login-wrapper {
            position: relative;
            max-width: 460px;
            width: 100%;
        }

        .super-login-glow {
            position: absolute;
            inset: -4px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.5) 0%, rgba(236, 72, 153, 0.4) 100%);
            border-radius: 28px;
            filter: blur(24px);
            opacity: 0.65;
            z-index: 1;
        }

        .super-login-card {
            position: relative;
            z-index: 2;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(28px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 26px;
            padding: clamp(28px, 5vw, 44px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7);
        }

        .super-brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .super-brand-title {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }
        .super-brand-title span { color: #c084fc; }

        .super-badge-pill {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #f472b6;
            background: rgba(244, 114, 182, 0.15);
            border: 1px solid rgba(244, 114, 182, 0.35);
            padding: 3px 10px;
            border-radius: 8px;
        }

        .demo-super-box {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 12.5px;
            cursor: pointer;
            transition: var(--transition-snappy);
        }
        .demo-super-box:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body>

    <div class="super-login-wrapper">
        <div class="super-login-glow"></div>
        <div class="super-login-card">
            <div class="super-brand-row">
                <a href="{{ route('home') }}" class="super-brand-title">
                    <span style="background:linear-gradient(135deg, #6366f1, #ec4899);width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">⚡</span>
                    <span>DocScolaire<span>.Core</span></span>
                </a>
                <span class="super-badge-pill">Master Root</span>
            </div>

            <h1 style="font-size:24px;font-weight:800;letter-spacing:-0.03em;color:#ffffff;margin-bottom:6px;">Console Super Admin</h1>
            <p style="font-size:13.5px;color:#94a3b8;margin-bottom:22px;line-height:1.5;">Supervision globale du réseau multi-tenants, gestion des licences et monitoring de la plateforme SaaS.</p>

            @if($errors->any())
                <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:18px;">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            @if(session('info'))
                <div style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.4);color:#c7d2fe;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:18px;">
                    ℹ️ {{ session('info') }}
                </div>
            @endif

            <!-- Démo Shortcut SuperAdmin -->
            <div class="demo-super-box" id="fill-super-btn">
                <div style="font-weight:700;color:#c7d2fe;display:flex;align-items:center;justify-content:space-between;">
                    <span>💡 Compte Master SaaS :</span>
                    <span style="font-size:11px;background:#6366f1;color:#fff;padding:2px 7px;border-radius:4px;">Remplir</span>
                </div>
                <div style="color:#94a3b8;font-family:var(--font-mono);font-size:11.5px;margin-top:3px;">
                    superadmin@plateforme-saas.com &bull; Secret2026!
                </div>
            </div>

            <form action="{{ route('superadmin.login.post') }}" method="POST" id="super-login-form">
                @csrf
                <div class="form-group-modern">
                    <label for="email" class="label-modern" style="color:#cbd5e1;">Adresse email Master</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="input-modern"
                        required 
                        autocomplete="email" 
                        value="{{ old('email', 'superadmin@plateforme-saas.com') }}"
                        style="background:rgba(15,23,42,0.8);border-color:rgba(255,255,255,0.12);color:#ffffff;"
                    >
                </div>

                <div class="form-group-modern">
                    <label for="mot_de_passe" class="label-modern" style="color:#cbd5e1;">Mot de passe Master</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="input-modern"
                            required 
                            autocomplete="current-password" 
                            value="Secret2026!"
                            style="background:rgba(15,23,42,0.8);border-color:rgba(255,255,255,0.12);color:#ffffff;"
                        >
                        <button type="button" class="auth-input-toggle" data-toggle-password="mot_de_passe" title="Afficher/masquer" style="position:absolute;right:14px;background:none;border:none;color:#64748b;cursor:pointer;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-modern-primary" id="btn-super-submit" style="width:100%;height:50px;font-size:15px;margin-top:12px;background:linear-gradient(135deg, #6366f1 0%, #a855f7 100%);box-shadow:0 4px 16px rgba(99,102,241,0.4);">
                    <span>Accéder à la Console Root</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.08);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <a href="{{ route('home') }}" style="color:#94a3b8;text-decoration:none;">&larr; Retour au site public</a>
                <span style="color:#64748b;font-size:11.5px;">Accès Protégé MFA / 2FA</span>
            </div>
        </div>
    </div>

    <script src="/js/modern-ui.js"></script>
    <script>
        document.getElementById('fill-super-btn')?.addEventListener('click', () => {
            document.getElementById('email').value = 'superadmin@plateforme-saas.com';
            document.getElementById('mot_de_passe').value = 'Secret2026!';
            if (window.showModernToast) {
                window.showModernToast('Identifiants SuperAdmin appliqués !', 'success');
            }
        });
    </script>
</body>
</html>
