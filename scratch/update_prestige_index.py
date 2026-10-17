import os

content = """<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portail académique officiel — {{ $ecole->nom }}. Certification et délivrance sécurisée de documents scolaires.">
    <title>Espace Étudiant Officiel | {{ $ecole->nom }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        :root {
            /* ── Palette d'Excellence Universitaire (Matte, Classy & Noble) ── */
            --bg-deep: #070a14;
            --bg-card: rgba(13, 19, 35, 0.88);
            --primary: {{ $ecole->couleur_primaire ?? '#1e3a8a' }};
            --p-r: {{ hexdec(substr(ltrim($ecole->couleur_primaire ?? '#1e3a8a', '#'), 0, 2)) }};
            --p-g: {{ hexdec(substr(ltrim($ecole->couleur_primaire ?? '#1e3a8a', '#'), 2, 2)) }};
            --p-b: {{ hexdec(substr(ltrim($ecole->couleur_primaire ?? '#1e3a8a', '#'), 4, 2)) }};
            
            /* Dorure & Champagne Nobles (Dorure à chaud sans reflets criards) */
            --gold: #c5a059;
            --gold-light: #dfba73;
            --gold-dark: #937338;
            --gold-subtle: rgba(197, 160, 89, 0.22);
            --gold-border: rgba(197, 160, 89, 0.35);

            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-card: rgba(197, 160, 89, 0.26);

            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-gold: #e5c07b;
            --accent-emerald: #10b981;

            --font-display: 'Plus Jakarta Sans', sans-serif;
            --font-title: 'Cinzel', serif;
            --font-code: 'JetBrains Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* ── Arrière-Plan 3D Guilloché Sécuritaire (WebGL) ── */
        #webgl-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
        }

        /* Subtils voiles d'ambiance sobres (pas de néon criard) */
        .ambient-security-mask {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 30%, transparent 40%, rgba(7, 10, 20, 0.75) 100%);
            pointer-events: none;
            z-index: 1;
        }

        /* ── Header Officiel ── */
        .portal-header {
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-subtle);
            background: rgba(7, 10, 20, 0.85);
            backdrop-filter: blur(20px);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: #fff;
        }

        .school-logo-wrap {
            width: 46px; height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(197, 160, 89, 0.2), rgba(15, 23, 42, 0.9));
            border: 1px solid var(--gold-border);
            display: flex; align-items: center; justify-content: center;
            font-family: var(--font-title);
            font-weight: 700; font-size: 20px;
            color: var(--gold-light);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            flex-shrink: 0;
        }
        .school-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }

        .school-name-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 17px;
            letter-spacing: -0.2px;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 440px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .school-code-tag {
            font-family: var(--font-code);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 6px;
            background: rgba(197, 160, 89, 0.12);
            border: 1px solid var(--gold-border);
            color: var(--gold-light);
            flex-shrink: 0;
            letter-spacing: 0.5px;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .back-pill-btn:hover {
            color: #fff;
            border-color: var(--gold-border);
            background: rgba(197, 160, 89, 0.08);
        }

        .btn-audio-toggle {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 15px;
        }
        .btn-audio-toggle:hover {
            color: var(--gold-light);
            border-color: var(--gold-border);
        }

        /* ── Academic Hero Banner (Prestige & Sobriété) ── */
        .academic-hero-banner {
            position: relative;
            z-index: 20;
            text-align: center;
            padding: 40px 24px 20px;
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .academic-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(197, 160, 89, 0.1);
            border: 1px solid var(--gold-border);
            padding: 6px 18px;
            border-radius: 999px;
            font-family: var(--font-code);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            color: var(--gold-light);
            text-transform: uppercase;
        }

        .academic-hero-title {
            font-family: var(--font-display);
            font-size: 40px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.2;
            color: #ffffff;
        }

        .academic-hero-subtitle {
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-muted);
            max-width: 680px;
            margin: 0 auto;
        }

        /* ── Main Workspace (2-Column Balanced) ── */
        .portal-workspace {
            position: relative;
            z-index: 20;
            width: 100%;
            max-width: 1180px;
            margin: 10px auto 40px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 1.12fr 0.88fr;
            gap: 36px;
            align-items: stretch;
        }

        /* ── Left Column: Official Authentication Card ── */
        .interactive-card-3d {
            position: relative;
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid var(--border-card);
            padding: 38px 40px;
            backdrop-filter: blur(28px);
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.7),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Coin doré subtil */
        .interactive-card-3d::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 48px; height: 48px;
            border-top: 2px solid var(--gold);
            border-left: 2px solid var(--gold);
            border-top-left-radius: 24px;
            pointer-events: none;
            opacity: 0.6;
        }

        .card-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            color: var(--text-gold);
            font-family: var(--font-code);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 14px;
            width: fit-content;
        }

        .card-title-3d {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
            color: #ffffff;
        }

        .card-subtitle-3d {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 22px;
        }

        /* Demo Helper Chip */
        .demo-helper-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(197, 160, 89, 0.08);
            border: 1px dashed var(--gold-border);
            padding: 9px 14px;
            border-radius: 12px;
            font-family: var(--font-display);
            font-size: 12px;
            color: #e2e8f0;
            cursor: pointer;
            margin-bottom: 22px;
            transition: all 0.2s;
            width: fit-content;
        }
        .demo-helper-chip:hover {
            background: rgba(197, 160, 89, 0.16);
            border-style: solid;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .demo-fill-tag {
            background: rgba(197, 160, 89, 0.25);
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--gold-light);
            border: 1px solid var(--gold-border);
        }

        /* Inputs & Autofill */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.4px;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .form-input-wrap { position: relative; }
        .form-input-wrap svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-3d {
            width: 100%;
            padding: 15px 16px 15px 46px;
            background: rgba(10, 15, 28, 0.75);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all 0.25s;
        }
        .input-3d:focus {
            border-color: var(--gold);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.2);
        }
        .input-3d:focus + svg, .form-input-wrap:focus-within svg {
            color: var(--gold-light);
        }

        /* Autofill override */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #0b1120 inset !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .btn-submit-3d {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            background: linear-gradient(135deg, #c5a059, #937338);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #080c16;
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.35);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 22px;
        }
        .btn-submit-3d:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #dfba73, #a68444);
            box-shadow: 0 10px 25px rgba(197, 160, 89, 0.25);
        }

        .forgot-link {
            display: inline-block;
            font-family: var(--font-display);
            font-size: 12px;
            color: var(--text-muted);
            text-decoration: none;
            margin-top: 14px;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: var(--gold-light); }

        .portal-alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .portal-alert-error { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; }
        .portal-alert-success { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; }

        /* ── Right Column: Academic 3D Model & Prestige Smart Card ── */
        .card-showcase-wrap {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
        }

        /* 3D Mortarboard & Rolled Diploma Canvas */
        .academic-model-wrap {
            position: relative;
            width: 100%;
            height: 150px;
            margin-bottom: -6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
        }
        .academic-model-wrap:active { cursor: grabbing; }
        #academic-crest-canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Double-Sided 3D Student ID Card */
        .card-flipper-box {
            width: 330px;
            height: 480px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.75s cubic-bezier(0.2, 0.8, 0.2, 1);
            cursor: pointer;
        }
        .card-flipper-box.is-flipped { transform: rotateY(180deg); }

        .card-face {
            position: absolute;
            inset: 0;
            border-radius: 22px;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            border: 1px solid var(--gold-border);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .card-face-front {
            background: linear-gradient(150deg, #0e1526 0%, #080c16 100%);
        }
        .card-face-back {
            background: linear-gradient(150deg, #0a0f1d 0%, #060913 100%);
            transform: rotateY(180deg);
        }

        /* Guilloché Filigrane Intégré dans la carte */
        .card-watermark-guilloche {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 80% 20%, rgba(197, 160, 89, 0.08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }

        /* Coin ornemental carte */
        .card-face::after {
            content: '';
            position: absolute;
            bottom: 12px; right: 12px;
            width: 24px; height: 24px;
            border-bottom: 1px solid var(--gold);
            border-right: 1px solid var(--gold);
            opacity: 0.4;
            pointer-events: none;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 5;
        }

        /* Puce EMV / Smart Chip en or véritable */
        .nfc-gold-chip {
            width: 38px; height: 28px;
            border-radius: 6px;
            background: linear-gradient(135deg, #e5c07b 0%, #b89047 50%, #dfba73 100%);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.5), 0 2px 6px rgba(0,0,0,0.5);
            position: relative;
        }
        .nfc-gold-chip::after {
            content: '';
            position: absolute;
            inset: 4px 6px;
            border: 1px solid rgba(80, 50, 10, 0.4);
            border-radius: 2px;
        }

        .card-inst-code {
            font-family: var(--font-code);
            font-size: 11px;
            font-weight: 700;
            color: var(--gold-light);
            letter-spacing: 1px;
            background: rgba(197, 160, 89, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid var(--gold-border);
        }

        .card-avatar-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 5;
        }
        .card-avatar-ring {
            width: 78px; height: 78px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            padding: 2px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
            margin-bottom: 10px;
        }
        .card-avatar-inner {
            width: 100%; height: 100%;
            border-radius: 50%;
            background: #090e1a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #fff;
        }

        .card-holder-name {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.2px;
        }
        .card-holder-title {
            font-family: var(--font-code);
            font-size: 10px;
            color: var(--gold-light);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* Clean Meta Box */
        .card-meta-clean {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            z-index: 5;
        }
        .meta-full-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 6px;
        }
        .meta-split-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .card-meta-label { font-family: var(--font-code); font-size: 9px; color: var(--text-muted); text-transform: uppercase; }
        .card-meta-val {
            font-family: var(--font-code);
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-bottom-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 5;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .barcode-mini {
            height: 18px; width: 68px;
            background: repeating-linear-gradient(90deg, #c5a059, #c5a059 2px, transparent 2px, transparent 4px, #c5a059 4px, #c5a059 5px, transparent 5px, transparent 8px);
            opacity: 0.65;
        }

        /* Back Face Elements */
        .mag-stripe {
            height: 36px;
            background: #020408;
            border-radius: 4px;
            margin-top: -6px;
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-code);
            font-size: 9px;
            letter-spacing: 2px;
            color: rgba(197, 160, 89, 0.4);
        }
        .back-qr-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: rgba(255,255,255,0.02);
            border-radius: 14px;
            border: 1px solid var(--border-subtle);
        }
        .qr-placeholder-svg {
            width: 86px; height: 86px;
            background: #fff;
            padding: 6px;
            border-radius: 8px;
        }
        .signature-strip {
            background: rgba(255, 255, 255, 0.05);
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: var(--font-code);
            font-size: 10px;
            color: #cbd5e1;
        }

        .btn-flip-card {
            margin-top: 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            padding: 7px 16px;
            border-radius: 999px;
            font-family: var(--font-display);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-flip-card:hover { color: #fff; border-color: var(--gold-border); background: rgba(197, 160, 89, 0.08); }

        /* ── Reassurance Strip ── */
        .academic-trust-strip {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto 50px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            position: relative;
            z-index: 20;
        }

        .trust-box {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px 20px;
            border-radius: 18px;
            background: rgba(13, 19, 35, 0.65);
            border: 1px solid var(--border-subtle);
            backdrop-filter: blur(20px);
            transition: all 0.25s;
        }
        .trust-box:hover {
            border-color: var(--gold-border);
            transform: translateY(-2px);
        }
        .trust-box-icon {
            font-size: 22px;
            line-height: 1;
            padding: 10px;
            border-radius: 12px;
            background: rgba(197, 160, 89, 0.08);
            border: 1px solid var(--gold-border);
            flex-shrink: 0;
            color: var(--gold-light);
        }
        .trust-box-content h4 {
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }
        .trust-box-content p {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .portal-footer {
            margin-top: auto;
            border-top: 1px solid var(--border-subtle);
            padding: 20px 40px;
            background: rgba(7, 10, 20, 0.85);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: var(--font-code);
            font-size: 11px;
            color: var(--text-muted);
            position: relative;
            z-index: 20;
        }

        @media (max-width: 960px) {
            .portal-workspace { grid-template-columns: 1fr; }
            .card-showcase-wrap { order: -1; margin-bottom: 20px; }
            .academic-hero-title { font-size: 30px; }
            .academic-trust-strip { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Fullscreen 3D WebGL Canvas Background: Guilloché Security Wave Matrix -->
    <canvas id="webgl-bg"></canvas>
    <div class="ambient-security-mask"></div>

    <!-- Header -->
    <header class="portal-header">
        <div class="header-brand">
            <div class="school-logo-wrap">
                @if($ecole->logo_path)
                    <img src="{{ asset('storage/' . $ecole->logo_path) }}" alt="{{ $ecole->nom }}">
                @else
                    {{ mb_substr($ecole->nom, 0, 1) }}
                @endif
            </div>
            <div>
                <div class="school-name-text">
                    {{ $ecole->nom }}
                    <span class="school-code-tag">{{ $ecole->code_unique }}</span>
                </div>
            </div>
        </div>

        <div class="header-controls">
            <button class="btn-audio-toggle" id="btn-sound" onclick="toggleAudio()" title="Activer/Désactiver les signaux sonores">
                🔊
            </button>
            <a href="{{ route('myportal') }}" class="back-pill-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Changer d'école</span>
            </a>
            <a href="{{ route('home') }}#verifier" class="back-pill-btn">
                <span>Vérification QR</span>
            </a>
        </div>
    </header>

    <!-- Academic Hero Banner -->
    <section class="academic-hero-banner">
        <div class="academic-badge-pill">
            <span>🏛️</span>
            <span>PORTAIL ACADÉMIQUE OFFICIEL &bull; {{ $ecole->nom }}</span>
        </div>
        <h1 class="academic-hero-title">
            Espace Numérique Étudiant
        </h1>
        <p class="academic-hero-subtitle">
            Délivrance certifiée et vérification instantanée de vos documents officiels : attestations de réussite, certificats d'inscription et relevés scellés.
        </p>
    </section>

    <!-- Main Workspace -->
    <main class="portal-workspace">
        
        <!-- Left Column: Official Authentication Card -->
        <div class="interactive-card-3d">
            <div>
                <div class="card-header-badge">&#10022; Authentification Sécurisée</div>
                <h2 class="card-title-3d">Accès à votre Dossier</h2>
                <p class="card-subtitle-3d">
                    Saisissez votre matricule étudiant pour accéder à votre espace de délivrance et consulter vos pièces académiques certifiées.
                </p>

                <div class="demo-helper-chip" onclick="quickFillDemoStudent()" title="Cliquer pour pré-remplir l'étudiante officielle de test">
                    <span>💡</span>
                    <span>Étudiant Démo : <b>Sara Alami (E2026-9901)</b></span>
                    <span class="demo-fill-tag">Remplir &rarr;</span>
                </div>

                @if(session('success'))
                    <div class="portal-alert portal-alert-success">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(isset($errors) && $errors->has('auth'))
                    <div class="portal-alert portal-alert-error">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->first('auth') }}</span>
                    </div>
                @endif

                <form action="{{ route('tenant.login', ['code_unique' => $ecole->code_unique]) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="matricule">
                            <span>Matricule Étudiant</span>
                            <span style="color:var(--gold-light);font-family:var(--font-code);font-size:10px;">{{ $ecole->code_unique }}-...</span>
                        </label>
                        <div class="form-input-wrap">
                            <input 
                                type="text" 
                                name="matricule" 
                                id="matricule" 
                                class="input-3d" 
                                placeholder="ex. E2026-9901" 
                                value="{{ old('matricule') }}"
                                required 
                                autofocus
                            >
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">
                            <span>Mot de Passe</span>
                        </label>
                        <div class="form-input-wrap">
                            <input 
                                type="password" 
                                name="mot_de_passe" 
                                id="mot_de_passe" 
                                class="input-3d" 
                                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                                required
                            >
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-3d">
                        <span>Ouvrir mon Dossier Académique</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                @if(Route::has('tenant.password.forgot'))
                    <a href="{{ route('tenant.password.forgot', ['code_unique' => $ecole->code_unique]) }}" class="forgot-link">
                        Mot de passe oublié ? &rarr;
                    </a>
                @endif
            </div>

            <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--text-muted);">
                <span>🔒 Espace officiel scellé {{ $ecole->nom }}</span>
                <span style="color:var(--gold-light);font-family:var(--font-code);">Protection Cryptographique</span>
            </div>
        </div>

        <!-- Right Column: 3D Mortarboard & Double-Sided Prestige Smart Card -->
        <div class="card-showcase-wrap">
            <!-- 3D Mortarboard & Rolled Diploma Canvas -->
            <div class="academic-model-wrap" title="Mortier académique et diplôme scellé à la cire rouge (3D interactif)">
                <canvas id="academic-crest-canvas"></canvas>
            </div>

            <!-- Double-Sided 3D Student ID Card -->
            <div class="card-flipper-box" id="student-card-3d" onclick="flipStudentCard()">
                
                <!-- FRONT FACE -->
                <div class="card-face card-face-front">
                    <div class="card-watermark-guilloche"></div>

                    <div class="card-top">
                        <div class="nfc-gold-chip"></div>
                        <div class="card-inst-code">{{ $ecole->code_unique }}</div>
                    </div>

                    <div class="card-avatar-row">
                        <div class="card-avatar-ring">
                            <div class="card-avatar-inner">🎓</div>
                        </div>
                        <div class="card-holder-name" id="card-student-name">Étudiant</div>
                        <div class="card-holder-title">{{ $ecole->nom }}</div>
                    </div>

                    <!-- Clean Meta Info (No Text Overflow) -->
                    <div class="card-meta-clean">
                        <div class="meta-full-row">
                            <div class="card-meta-label">ÉTABLISSEMENT OFFICIEL</div>
                            <div class="card-meta-val">{{ $ecole->nom }}</div>
                        </div>
                        <div class="meta-split-row">
                            <div>
                                <div class="card-meta-label">STATUT</div>
                                <div class="card-meta-val" style="color:var(--accent-emerald);">INSCRIT ✓</div>
                            </div>
                            <div>
                                <div class="card-meta-label">PROMOTION</div>
                                <div class="card-meta-val">2025 - 2026</div>
                            </div>
                        </div>
                        <div class="meta-split-row">
                            <div>
                                <div class="card-meta-label">ACCÈS PIÈCES</div>
                                <div class="card-meta-val" style="color:var(--gold-light);">ILLIMITÉ</div>
                            </div>
                            <div>
                                <div class="card-meta-label">SÉCURITÉ</div>
                                <div class="card-meta-val" style="color:#cbd5e1;">SHA-256</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-bottom-bar">
                        <div class="barcode-mini"></div>
                        <div style="font-family:var(--font-code);font-size:10px;color:var(--gold-light);font-weight:700;">
                            CARTE OFFICIELLE &bull; SAAS
                        </div>
                    </div>
                </div>

                <!-- BACK FACE -->
                <div class="card-face card-face-back">
                    <div class="mag-stripe">
                        <span>●●●● {{ $ecole->code_unique }} ●●●● VERIFIED CREDENTIAL ●●●●</span>
                    </div>

                    <div class="back-qr-box">
                        <svg class="qr-placeholder-svg" viewBox="0 0 100 100">
                            <!-- Clean Scalable Vector QR Code -->
                            <rect width="100" height="100" fill="white"/>
                            <rect x="10" y="10" width="24" height="24" fill="#080c16"/>
                            <rect x="14" y="14" width="16" height="16" fill="white"/>
                            <rect x="18" y="18" width="8" height="8" fill="#c5a059"/>
                            
                            <rect x="66" y="10" width="24" height="24" fill="#080c16"/>
                            <rect x="70" y="14" width="16" height="16" fill="white"/>
                            <rect x="74" y="18" width="8" height="8" fill="#c5a059"/>
                            
                            <rect x="10" y="66" width="24" height="24" fill="#080c16"/>
                            <rect x="14" y="70" width="16" height="16" fill="white"/>
                            <rect x="18" y="74" width="8" height="8" fill="#c5a059"/>
                            
                            <rect x="42" y="14" width="6" height="6" fill="#080c16"/>
                            <rect x="52" y="24" width="6" height="6" fill="#080c16"/>
                            <rect x="42" y="42" width="16" height="16" fill="#c5a059"/>
                            <rect x="66" y="42" width="6" height="12" fill="#080c16"/>
                            <rect x="42" y="66" width="12" height="6" fill="#080c16"/>
                            <rect x="62" y="62" width="26" height="26" fill="#080c16"/>
                            <rect x="66" y="66" width="18" height="18" fill="white"/>
                            <rect x="72" y="72" width="6" height="6" fill="#c5a059"/>
                        </svg>
                        <div style="font-family:var(--font-code);font-size:9px;color:var(--text-muted);text-align:center;">
                            Sceau de Contrôle Anti-Fraude
                        </div>
                    </div>

                    <div class="signature-strip">
                        <span style="font-size:9px;color:var(--text-muted);">Signature du Doyen</span>
                        <span style="font-family:serif;font-style:italic;color:var(--gold-light);font-size:12px;">S. Alami</span>
                    </div>

                    <div style="font-family:var(--font-code);font-size:9px;color:#64748b;text-align:center;line-height:1.4;">
                        Document certifié conforme &bull; DocScolaire SaaS<br>
                        Toute reproduction est strictement prohibée.
                    </div>
                </div>

            </div>

            <!-- Flip Trigger Button -->
            <button type="button" class="btn-flip-card" onclick="flipStudentCard()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Retourner la carte (Verso sécurisé)</span>
            </button>
        </div>
    </main>

    <!-- Institutional Trust Reassurance Strip -->
    <section class="academic-trust-strip">
        <div class="trust-box">
            <div class="trust-box-icon">📜</div>
            <div class="trust-box-content">
                <h4>Documents Certifiés Conformes</h4>
                <p>Attestations, relevés et certificats scellés avec valeur probante officielle.</p>
            </div>
        </div>
        <div class="trust-box">
            <div class="trust-box-icon">🛡️</div>
            <div class="trust-box-content">
                <h4>Vérification QR Anti-Fraude</h4>
                <p>Chaque document dispose d'une signature cryptographique vérifiable en direct.</p>
            </div>
        </div>
        <div class="trust-box">
            <div class="trust-box-icon">🏛️</div>
            <div class="trust-box-content">
                <h4>Délivrance Immédiate 24/7</h4>
                <p>Génération instantanée en PDF haute résolution avec filigrane de l'école.</p>
            </div>
        </div>
    </section>

    <footer class="portal-footer">
        <div>{{ $ecole->nom }} &bull; Espace Étudiant Officiel &bull; DocScolaire SaaS</div>
        <div>Données scolaires sécurisées et isolées 🔒</div>
    </footer>

    <!-- Three.js 3D Background & Logic -->
    <script>
        /* ── 1. Arrière-Plan 3D Guilloché Sécuritaire Fiduciaire (Anti-Fraude) ── */
        (function initGuillocheSecurityMatrix() {
            if (typeof THREE === 'undefined') return;
            const canvas = document.getElementById('webgl-bg');
            if (!canvas) return;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 1, 2000);
            camera.position.set(0, 0, 360);

            const renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance'
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

            // Couleurs nobles : Or Champagne & Bleu Oxford
            const goldColor = new THREE.Color(0xc5a059);
            const slateBlue = new THREE.Color(0x1e293b);
            const schoolColor = new THREE.Color('{{ $ecole->couleur_primaire ?? "#1e3a8a" }}');

            // Guilloché Anti-Contrefaçon : Courbes sinusoïdales entrelacées
            const ribbonCount = 16;
            const pointsPerRibbon = 130;
            const ribbons = [];

            for (let r = 0; r < ribbonCount; r++) {
                const geometry = new THREE.BufferGeometry();
                const positions = new Float32Array(pointsPerRibbon * 3);
                const colors = new Float32Array(pointsPerRibbon * 3);
                
                const isGold = (r % 3 === 0);
                const baseCol = isGold ? goldColor : (r % 2 === 0 ? schoolColor : slateBlue);
                const opacity = isGold ? 0.38 : 0.22;

                geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

                const material = new THREE.LineBasicMaterial({
                    vertexColors: true,
                    transparent: true,
                    opacity: opacity,
                    blending: THREE.NormalBlending
                });

                const line = new THREE.Line(geometry, material);
                scene.add(line);

                ribbons.push({
                    line: line,
                    geo: geometry,
                    offset: (r / ribbonCount) * Math.PI * 2,
                    baseY: (r - ribbonCount / 2) * 22,
                    z: -160 + (r * 18),
                    amp1: 40 + (r % 4) * 10,
                    amp2: 20 + (r % 3) * 8,
                    color: baseCol
                });
            }

            // Sceau Académique Filigrane en Arrière-Plan (Rosette)
            const watermarkGroup = new THREE.Group();
            watermarkGroup.position.set(0, 0, -200);
            scene.add(watermarkGroup);

            const ringGeo1 = new THREE.TorusGeometry(120, 0.7, 16, 120);
            const ringMat1 = new THREE.MeshBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.12 });
            watermarkGroup.add(new THREE.Mesh(ringGeo1, ringMat1));

            const ringGeo2 = new THREE.TorusGeometry(85, 0.5, 16, 90);
            const ringMat2 = new THREE.MeshBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.09 });
            watermarkGroup.add(new THREE.Mesh(ringGeo2, ringMat2));

            // Petits pétales de rosace
            const rosettePetalCount = 36;
            const petalPoints = [];
            for (let p = 0; p <= 360; p += 3) {
                const rad = (p * Math.PI) / 180;
                const radius = 100 + Math.sin(rad * rosettePetalCount / 2) * 10;
                petalPoints.push(new THREE.Vector3(Math.cos(rad) * radius, Math.sin(rad) * radius, 0));
            }
            const rosetteGeo = new THREE.BufferGeometry().setFromPoints(petalPoints);
            const rosetteMat = new THREE.LineBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.14 });
            watermarkGroup.add(new THREE.Line(rosetteGeo, rosetteMat));

            // Micro-poussière de dorure à chaud (très sobre, pas de néon)
            const fleckCount = 35;
            const fleckGeo = new THREE.BufferGeometry();
            const fleckPos = new Float32Array(fleckCount * 3);
            for (let i = 0; i < fleckCount; i++) {
                fleckPos[i * 3]     = (Math.random() - 0.5) * 800;
                fleckPos[i * 3 + 1] = (Math.random() - 0.5) * 600;
                fleckPos[i * 3 + 2] = (Math.random() - 0.5) * 350;
            }
            fleckGeo.setAttribute('position', new THREE.BufferAttribute(fleckPos, 3));
            const fleckMat = new THREE.PointsMaterial({
                color: 0xdfba73,
                size: 2.0,
                transparent: true,
                opacity: 0.4
            });
            const flecks = new THREE.Points(fleckGeo, fleckMat);
            scene.add(flecks);

            // Suivi souris noble et doux (parallaxe)
            let targetX = 0, targetY = 0;
            let curX = 0, curY = 0;

            window.addEventListener('mousemove', (e) => {
                targetX = (e.clientX - window.innerWidth / 2) * 0.12;
                targetY = (e.clientY - window.innerHeight / 2) * 0.12;
            });

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });

            let time = 0;
            function animate() {
                requestAnimationFrame(animate);
                time += 0.01;

                curX += (targetX - curX) * 0.03;
                curY += (targetY - curY) * 0.03;

                camera.position.x = curX;
                camera.position.y = -curY;
                camera.lookAt(0, 0, 0);

                watermarkGroup.rotation.z += 0.0004;
                flecks.rotation.y += 0.0002;

                // Ondulation des rubans de sécurité Guilloché
                for (let r = 0; r < ribbons.length; r++) {
                    const rib = ribbons[r];
                    const pos = rib.geo.attributes.position.array;
                    const col = rib.geo.attributes.color.array;
                    const t = time + rib.offset;

                    for (let i = 0; i < pointsPerRibbon; i++) {
                        const u = (i / pointsPerRibbon) * 2 - 1;
                        const x = u * 420;
                        const y = rib.baseY + 
                                  Math.sin(u * 4.0 + t * 0.7) * rib.amp1 + 
                                  Math.cos(u * 8.0 - t * 0.4) * rib.amp2;
                        const z = rib.z + Math.sin(u * 3.0 + t) * 30;

                        pos[i * 3]     = x;
                        pos[i * 3 + 1] = y;
                        pos[i * 3 + 2] = z;

                        const edgeFade = 1.0 - Math.pow(Math.abs(u), 2.2);
                        col[i * 3]     = rib.color.r * edgeFade;
                        col[i * 3 + 1] = rib.color.g * edgeFade;
                        col[i * 3 + 2] = rib.color.b * edgeFade;
                    }
                    rib.geo.attributes.position.needsUpdate = true;
                    rib.geo.attributes.color.needsUpdate = true;
                }

                renderer.render(scene, camera);
            }
            animate();
        })();

        /* ── 2. Signaux Sonores Subtils ── */
        class AudioSynth {
            constructor() {
                this.ctx = null;
                this.enabled = true;
            }
            init() {
                if (!this.ctx) {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) this.ctx = new AudioContext();
                }
            }
            flip() {
                if (!this.enabled) return;
                this.init();
                if (!this.ctx) return;
                if (this.ctx.state === 'suspended') this.ctx.resume();
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(240, this.ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(460, this.ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start();
                osc.stop(this.ctx.currentTime + 0.12);
            }
            chime() {
                if (!this.enabled) return;
                this.init();
                if (!this.ctx) return;
                if (this.ctx.state === 'suspended') this.ctx.resume();
                const now = this.ctx.currentTime;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.08);
                osc.frequency.setValueAtTime(783.99, now + 0.16);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start();
                osc.stop(now + 0.35);
            }
        }
        const sound = new AudioSynth();

        function toggleAudio() {
            sound.enabled = !sound.enabled;
            const btn = document.getElementById('btn-sound');
            if (btn) {
                btn.textContent = sound.enabled ? '🔊' : '🔇';
                btn.style.opacity = sound.enabled ? '1' : '0.5';
            }
        }

        /* ── 3. Modèle 3D Mortier Académique & Parchemin Scellé (Three.js) ── */
        (function initAcademic3D() {
            const canvas = document.getElementById('academic-crest-canvas');
            if (!canvas || typeof THREE === 'undefined') return;

            const container = canvas.parentElement;
            let width = container.clientWidth || 380;
            let height = container.clientHeight || 150;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 1000);
            camera.position.set(0, 10, 92);

            const renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance'
            });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

            // Éclairage studio noble
            const ambient = new THREE.AmbientLight(0xfffaed, 1.2);
            scene.add(ambient);

            const keyLight = new THREE.DirectionalLight(0xfffaed, 2.0);
            keyLight.position.set(40, 60, 50);
            scene.add(keyLight);

            const warmRim = new THREE.PointLight(0xc5a059, 2.0, 150);
            warmRim.position.set(-40, -10, 40);
            scene.add(warmRim);

            const crestGroup = new THREE.Group();
            scene.add(crestGroup);

            // 1. Toque Académique (Mortier en feutre noir mat & or)
            const capGroup = new THREE.Group();
            crestGroup.add(capGroup);

            // Calotte
            const skullGeo = new THREE.CylinderGeometry(14, 16.5, 9, 32);
            const capMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                roughness: 0.7,
                metalness: 0.1
            });
            const skull = new THREE.Mesh(skullGeo, capMat);
            skull.position.y = -1;
            capGroup.add(skull);

            // Plateau carré
            const boardGeo = new THREE.BoxGeometry(44, 2.2, 44);
            const boardMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,
                roughness: 0.6,
                metalness: 0.15
            });
            const board = new THREE.Mesh(boardGeo, boardMat);
            board.position.y = 4.2;
            board.rotation.y = Math.PI / 4;
            board.rotation.x = -0.08;
            capGroup.add(board);

            // Bouton d'or
            const buttonGeo = new THREE.CylinderGeometry(2.5, 2.5, 2, 16);
            const goldMat = new THREE.MeshStandardMaterial({
                color: 0xc5a059,
                metalness: 0.85,
                roughness: 0.25
            });
            const button = new THREE.Mesh(buttonGeo, goldMat);
            button.position.y = 5.8;
            capGroup.add(button);

            // Pompon doré
            const tasselGeo = new THREE.CylinderGeometry(1.6, 3.2, 11, 16);
            const tassel = new THREE.Mesh(tasselGeo, goldMat);
            tassel.position.set(16, -1, 16);
            tassel.rotation.z = -0.2;
            capGroup.add(tassel);

            // 2. Parchemin Officiel Scellé à la Cire Rouge
            const dipGroup = new THREE.Group();
            crestGroup.add(dipGroup);

            // Rouleau de parchemin ivoire
            const scrollGeo = new THREE.CylinderGeometry(4.8, 4.8, 46, 32);
            const scrollMat = new THREE.MeshStandardMaterial({
                color: 0xfbf6ea,
                roughness: 0.45,
                metalness: 0.05
            });
            const scroll = new THREE.Mesh(scrollGeo, scrollMat);
            scroll.rotation.z = Math.PI / 3;
            scroll.position.set(0, -9, 8);
            dipGroup.add(scroll);

            // Ruban doré
            const ribbonGeo = new THREE.CylinderGeometry(5.1, 5.1, 7, 32);
            const ribbonMat = new THREE.MeshStandardMaterial({
                color: 0xc5a059,
                metalness: 0.8,
                roughness: 0.3
            });
            const ribbon = new THREE.Mesh(ribbonGeo, ribbonMat);
            ribbon.rotation.z = Math.PI / 3;
            ribbon.position.set(0, -9, 8);
            dipGroup.add(ribbon);

            // Sceau de cire carmin noble
            const sealGeo = new THREE.CylinderGeometry(3.6, 3.6, 1.4, 16);
            const sealMat = new THREE.MeshStandardMaterial({
                color: 0x991b1b,
                roughness: 0.35,
                metalness: 0.15
            });
            const seal = new THREE.Mesh(sealGeo, sealMat);
            seal.rotation.x = Math.PI / 2;
            seal.position.set(0, -9, 13.5);
            dipGroup.add(seal);

            // Suivi souris & physique
            let targetRotX = 0, targetRotY = 0;
            let currentRotX = 0, currentRotY = 0;

            window.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                const x = (e.clientX - (rect.left + rect.width / 2)) / (window.innerWidth / 2);
                const y = (e.clientY - (rect.top + rect.height / 2)) / (window.innerHeight / 2);
                targetRotY = x * 0.4;
                targetRotX = -y * 0.25;
            });

            canvas.addEventListener('click', () => {
                sound.chime();
                crestGroup.position.y += 4;
            });

            function onResize() {
                width = container.clientWidth || 380;
                height = container.clientHeight || 150;
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.setSize(width, height);
            }
            window.addEventListener('resize', onResize);

            window.quickFillDemoStudent = function() {
                sound.chime();
                const matInput = document.getElementById('matricule') || document.getElementById('identifiant');
                const pwdInput = document.getElementById('mot_de_passe');
                const cardName = document.getElementById('card-student-name');
                if (matInput) {
                    matInput.value = 'E2026-9901';
                    matInput.style.borderColor = '#c5a059';
                    matInput.style.boxShadow = '0 0 15px rgba(197, 160, 89, 0.4)';
                    setTimeout(() => {
                        matInput.style.borderColor = '';
                        matInput.style.boxShadow = '';
                    }, 2000);
                    matInput.focus();
                }
                if (pwdInput) {
                    pwdInput.value = 'Secret2026!';
                }
                if (cardName) {
                    cardName.textContent = 'Sara Alami (E2026-9901)';
                }
            };

            let clock = new THREE.Clock();
            function animate() {
                requestAnimationFrame(animate);
                const time = clock.getElapsedTime();

                crestGroup.position.y += (Math.sin(time * 1.5) * 2.2 - crestGroup.position.y) * 0.1;

                currentRotX += (targetRotX - currentRotX) * 0.05;
                currentRotY += (targetRotY - currentRotY) * 0.05;

                crestGroup.rotation.y = currentRotY + Math.sin(time * 0.5) * 0.1;
                crestGroup.rotation.x = currentRotX + 0.08;

                renderer.render(scene, camera);
            }
            animate();
        })();

        /* ── 4. Rotation 3D & Flip de la Carte Étudiante ── */
        const cardFlipper = document.getElementById('student-card-3d');
        let isCardFlipped = false;

        function flipStudentCard() {
            isCardFlipped = !isCardFlipped;
            cardFlipper.classList.toggle('is-flipped', isCardFlipped);
            sound.flip();
        }

        cardFlipper.parentElement.addEventListener('mousemove', (e) => {
            const rect = cardFlipper.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const cx = rect.width / 2;
            const cy = rect.height / 2;
            const rotX = -((y - cy) / cy) * 12;
            const rotY = ((x - cx) / cx) * 12;
            const baseRotY = isCardFlipped ? 180 : 0;
            cardFlipper.style.transform = `rotateX(${rotX}deg) rotateY(${baseRotY + rotY}deg) scale3d(1.02, 1.02, 1.02)`;
        });

        cardFlipper.parentElement.addEventListener('mouseleave', () => {
            const baseRotY = isCardFlipped ? 180 : 0;
            cardFlipper.style.transform = `rotateX(0deg) rotateY(${baseRotY}deg) scale3d(1, 1, 1)`;
        });

        const matInput = document.getElementById('matricule');
        if (matInput) {
            matInput.addEventListener('input', (e) => {
                const val = e.target.value.trim();
                const cardName = document.getElementById('card-student-name');
                if (val.length > 0) {
                    cardName.textContent = val.toUpperCase();
                } else {
                    cardName.textContent = 'Étudiant';
                }
            });
        }
    </script>
</body>
</html>
"""

with open('/home/ayoub/school-docs-saas/resources/views/portal/index.blade.php', 'w') as f:
    f.write(content)

print("Updated portal/index.blade.php successfully!")
