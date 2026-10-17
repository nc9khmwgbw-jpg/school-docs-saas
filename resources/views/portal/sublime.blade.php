<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sublime — Espace Étudiant & Documents Officiels</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #090c13;
            color: #18251e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ── Container de présentation exact du composant ── */
        .sublime-viewport-frame {
            width: 100%;
            max-width: 1200px;
            height: min(720px, 94vh);
            background: #ffffff;
            border-radius: 28px;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.08);
            container-type: inline-size;
        }

        /* ── Section Glyph Portal ── */
        .gp-section {
            --gp-paper: #ffffff;
            --gp-ink: #16402e;
            --gp-field: #0b3b2a;
            --gp-foreground: #fbfbfa;
            position: relative;
            isolation: isolate;
            background: var(--gp-paper);
            color: var(--gp-ink);
            font-family: inherit;
        }

        .gp-pin {
            position: sticky;
            top: 0;
            height: min(720px, 94vh);
            overflow: clip;
            isolation: isolate;
        }

        .gp-field {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 18% 8%, rgba(68,125,98,.72), transparent 34%),
                        radial-gradient(circle at 82% 20%, rgba(251,251,250,.12), transparent 28%),
                        radial-gradient(circle at 48% 78%, rgba(9,48,35,.5), transparent 44%),
                        linear-gradient(135deg,#0b3b2a 0%,#14573f 48%,#082d22 100%);
            opacity: 0;
            pointer-events: none;
            transform: scale(var(--gp-field-scale, 1));
        }
        .gp-section[data-gp-ready="true"] .gp-field { opacity: 1; }

        .gp-art {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: visible;
            pointer-events: none;
        }

        .gp-marks {
            fill: none;
            stroke: #16402e;
            opacity: 0.35;
        }

        .gp-choices {
            position: absolute;
            inset: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .gp-section[data-gp-choosing="true"] .gp-choices { visibility: visible; }

        .gp-choices button {
            box-sizing: border-box;
            position: absolute;
            border: 0;
            padding: 0;
            margin: 0;
            background: transparent;
            cursor: pointer;
            pointer-events: auto;
            touch-action: pan-y;
        }
        .gp-choices button:focus-visible {
            outline: 2px solid #0b3b2a;
            outline-offset: 5px;
        }

        /* ── En-tête sublime. exact ── */
        .sublime-header {
            position: absolute;
            inset: clamp(24px, 4.5cqw, 48px) clamp(24px, 5cqw, 64px) auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            z-index: 10;
        }
        .sublime-logo {
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.065em;
            color: #18251e;
        }
        .sublime-category {
            font-size: 13px;
            line-height: 1.5;
            color: #71766f;
            font-weight: 400;
        }

        /* ── Textes au-dessus et en-dessous de SUBLIME ── */
        .sublime-eyebrow {
            position: absolute;
            inset: auto 24px calc(100% - var(--gp-word-top, 35%) + 34px);
            margin: 0;
            text-align: center;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            letter-spacing: 0.005em;
            color: #71766f;
        }

        .sublime-support {
            position: absolute;
            inset: calc(var(--gp-word-bottom, 50%) + 32px) 24px auto;
            margin: 0;
            text-align: center;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.5;
            color: #646a63;
        }

        /* ── Bouton Entrez à l'intérieur / Accéder à vos documents ── */
        .sublime-caption {
            position: absolute;
            inset: calc(var(--gp-word-bottom, 50%) + 82px) 24px auto;
            display: flex;
            justify-content: center;
            opacity: var(--gp-caption, 1);
            pointer-events: var(--gp-caption-hit, auto);
            z-index: 20;
        }

        .btn-sublime-enter {
            display: inline-flex;
            align-items: center;
            gap: 24px;
            min-height: 48px;
            padding: 0 24px;
            background: #142b22;
            border: 1px solid #10261d;
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.01em;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(16, 38, 29, 0.1);
            transition: background 0.18s, box-shadow 0.18s, transform 0.18s;
            cursor: pointer;
        }
        .btn-sublime-enter:hover {
            background: #204434;
            box-shadow: 0 4px 12px rgba(16, 38, 29, 0.2);
            transform: translateY(-1px);
        }
        .btn-sublime-enter:focus-visible {
            outline: 2px solid #176247;
            outline-offset: 4px;
        }

        .sublime-scroll-note {
            position: absolute;
            inset: auto 24px 6%;
            text-align: center;
            color: #7c817b;
            font-size: 11px;
            letter-spacing: 0.01em;
        }

        /* ── Contenu intérieur révélé (La page des documents) ── */
        .gp-content {
            box-sizing: border-box;
            position: relative;
            min-height: min(720px, 94vh);
            padding: 5.5rem clamp(1.25rem, 5cqw, 5rem) 6.5rem;
            display: grid;
            align-content: center;
            color: var(--gp-foreground);
            background: var(--gp-field);
            margin-top: calc((var(--gp-length) - 1) * var(--gp-height, 720px));
            opacity: var(--gp-reveal, 0);
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .gp-section[data-gp-entered="true"] .gp-content {
            pointer-events: auto;
        }

        .slipstream-copy {
            display: flex;
            width: min(100%, 80rem);
            margin: auto;
            flex-direction: column;
            align-items: flex-start;
            gap: clamp(2rem, 5svh, 3.5rem);
        }

        .slipstream-copy h2 {
            max-width: 48rem;
            margin: 0;
            color: #fff;
            font-size: clamp(1.85rem, 1.2rem + 2.2cqw, 2.5rem);
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .slipstream-features {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .slipstream-feature {
            border-top: 1px solid rgba(251, 251, 250, 0.22);
            padding-top: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .slipstream-feature h3 {
            margin: 0;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .slipstream-no {
            color: #6ee7b7;
            font: 600 0.8rem ui-monospace, monospace;
            letter-spacing: 0.08em;
            background: rgba(110, 231, 183, 0.15);
            padding: 2px 8px;
            border-radius: 4px;
        }

        .slipstream-feature p {
            margin: 0;
            color: rgba(251, 251, 250, 0.82);
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .btn-doc-download {
            margin-top: 10px;
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            background: #ffffff;
            color: #0b3b2a;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.15s, background 0.15s;
        }
        .btn-doc-download:hover {
            background: #eef5f1;
            transform: translateY(-1px);
        }

        .btn-vault-full {
            margin-top: 24px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 28px;
            border-radius: 10px;
            background: #6ee7b7;
            color: #0b3b2a;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        .btn-vault-full:hover {
            background: #a7f3d0;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <!-- Barre supérieure de sélection d'école pour tester les différents établissements -->
    @if(isset($ecoles) && $ecoles->count() > 0)
    <div style="width:100%;max-width:1200px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981;"></span>
            <span style="font-size:12px;font-weight:700;color:#a7f3d0;letter-spacing:0.04em;text-transform:uppercase;">Établissement Actif :</span>
            <span style="font-size:13px;font-weight:800;color:#ffffff;">{{ $ecole->nom }}</span>
            <span style="font-family:monospace;font-size:11px;background:rgba(110,231,183,0.18);color:#6ee7b7;padding:2px 8px;border-radius:6px;font-weight:700;">{{ $ecole->code_unique }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:12px;color:#94a3b8;font-weight:600;">Changer d'école :</span>
            @foreach($ecoles as $ec)
                <a href="{{ route('portal.sublime', ['ecole' => $ec->code_unique]) }}" 
                   style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;transition:all 0.15s;{{ ($ecole && $ecole->id === $ec->id) ? 'background:#10b981;color:#062b1e;' : 'background:rgba(255,255,255,0.08);color:#e2e8f0;border:1px solid rgba(255,255,255,0.15);' }}">
                    <span>{{ $ec->sigle }}</span>
                </a>
            @endforeach
            <a href="{{ route('myportal') }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:700;color:#6ee7b7;text-decoration:none;background:rgba(110,231,183,0.1);border:1px solid rgba(110,231,183,0.25);">
                <span>MyPortal &rarr;</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Cadre de démonstration exacte Sublime -->
    <div class="sublime-viewport-frame" id="demo-scroll-container">
        
        <section class="gp-section" id="gp-sublime-section" style="--gp-length: 2.4;">
            <div data-gp-viewport aria-hidden="true" style="position:absolute;inset:0 auto auto 0;height:100svh;width:0;visibility:hidden;"></div>
            
            <div class="gp-pin">
                
                <!-- Fond vert intérieur qui apparaît au zoom -->
                <div class="gp-field" data-gp-field aria-hidden="true"></div>

                <!-- Texte SVG dynamique avec sigle de l'école (ex: ENS, POLY, FST) -->
                <svg class="gp-art" data-gp-art aria-hidden="true" focusable="false">
                    <defs>
                        <clipPath id="gp-sublime-clip" clipPathUnits="userSpaceOnUse">
                            <text data-gp-glyph x="0" y="0" style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-weight:800;font-size:100px;font-kerning:none;letter-spacing:0;">{{ $ecole->sigle ?? 'ENS' }}</text>
                        </clipPath>
                    </defs>
                    <g class="gp-marks" data-gp-marks><path /></g>
                </svg>

                <!-- Boutons de sélection des lettres -->
                <div class="gp-choices" data-gp-choices role="radiogroup" aria-label="Choisir la lettre pour entrer"></div>

                <!-- Face avant blanche : Affichage dynamique de l'école et de l'étudiant -->
                <div data-gp-front style="position:absolute;inset:0;pointer-events:none;">
                    
                    <div class="sublime-header">
                        <span class="sublime-logo">{{ strtolower($ecole->sigle ?? 'ens') }}.</span>
                        <span class="sublime-category">{{ $ecole->nom ?? 'École Nationale Supérieure' }} &bull; {{ $ecole->code_unique ?? 'ENS-2026' }}</span>
                    </div>

                    <p class="sublime-eyebrow">
                        Bonjour {{ $etudiant->prenom ?? 'Sara' }} {{ $etudiant->nom ?? 'Alami' }} — Inscrit(e) à {{ $ecole->nom ?? 'l\'établissement' }}.
                    </p>

                    <p class="sublime-support">
                        Portail Officiel et Coffre-fort Numérique Certifié.
                    </p>

                    <span class="sublime-scroll-note">Faites défiler ou cliquez pour accéder à votre dossier ↓</span>
                </div>

                <!-- Bouton d'entrée centré -->
                <div class="sublime-caption" data-gp-caption>
                    <button type="button" class="btn-sublime-enter" id="btn-enter-vault">
                        <span>Accéder à vos documents {{ $ecole->sigle ?? 'ENS' }}</span>
                        <span aria-hidden="true">↘</span>
                    </button>
                </div>

            </div>

            <!-- Contenu intérieur après avoir traversé la lettre -->
            <div class="gp-content" id="gp-sublime-content" data-gp-content>
                <div class="slipstream-copy">
                    <h2>Documents officiels délivrés par {{ $ecole->nom }}.</h2>
                    
                    <div class="slipstream-features">
                        <div class="slipstream-feature">
                            <h3><span class="slipstream-no">01</span> Attestation de Réussite</h3>
                            <p>Scellée avec signature numérique et empreinte SHA-256 infalsifiable pour {{ $ecole->nom }}.</p>
                            @if(isset($ecole))
                                <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-doc-download">
                                    <span>Accéder au portail {{ $ecole->sigle }}</span> &darr;
                                </a>
                            @else
                                <a href="/portal/ENS-2026/dashboard" class="btn-doc-download">
                                    <span>Accéder au portail</span> &darr;
                                </a>
                            @endif
                        </div>

                        <div class="slipstream-feature">
                            <h3><span class="slipstream-no">02</span> Certificat de Scolarité</h3>
                            <p>QR code certifié pour vérification instantanée auprès des consulats et banques.</p>
                            @if(isset($ecole))
                                <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-doc-download">
                                    <span>Accéder au portail {{ $ecole->sigle }}</span> &darr;
                                </a>
                            @else
                                <a href="/portal/ENS-2026/dashboard" class="btn-doc-download">
                                    <span>Accéder au portail</span> &darr;
                                </a>
                            @endif
                        </div>

                        <div class="slipstream-feature">
                            <h3><span class="slipstream-no">03</span> Relevé de Notes</h3>
                            <p>Historique académique complet certifié conforme au registre officiel de {{ $ecole->sigle }}.</p>
                            @if(isset($ecole))
                                <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-doc-download">
                                    <span>Accéder au portail {{ $ecole->sigle }}</span> &darr;
                                </a>
                            @else
                                <a href="/portal/ENS-2026/dashboard" class="btn-doc-download">
                                    <span>Accéder au portail</span> &darr;
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(isset($ecole))
                        <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-vault-full">
                            <span>Ouvrir l'Espace Étudiant Officiel {{ $ecole->sigle }} &rarr;</span>
                        </a>
                    @else
                        <a href="/portal/ENS-2026/dashboard" class="btn-vault-full">
                            <span>Ouvrir l'Espace Étudiant Officiel &rarr;</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>

    </div>

    <!-- Moteur JS du Glyph Portal -->
    <script>
        (function initSublimeGlyphPortal() {
            const container = document.getElementById('demo-scroll-container');
            const section = document.getElementById('gp-sublime-section');
            if (!container || !section) return;

            const pin = section.querySelector('.gp-pin');
            const field = section.querySelector('[data-gp-field]');
            const art = section.querySelector('[data-gp-art]');
            const clip = section.querySelector('#gp-sublime-clip');
            const glyph = section.querySelector('[data-gp-glyph]');
            const marks = section.querySelector('[data-gp-marks]');
            const choices = section.querySelector('[data-gp-choices]');
            const enterBtn = document.getElementById('btn-enter-vault');

            const text = "{{ $ecole->sigle ?? 'ENS' }}";
            const characters = Array.from(text, (char, index) => ({ char, index }));
            const length = 2.4;
            const weight = 800;
            const fontFamily = '"Plus Jakarta Sans", Arial, sans-serif';

            // Boutons de choix par lettre
            choices.innerHTML = '';
            characters.forEach(({ char, index }, i) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.role = 'radio';
                btn.setAttribute('aria-checked', 'false');
                btn.tabIndex = -1;
                btn.dataset.gpLetter = index;
                btn.setAttribute('aria-label', `${char}, lettre ${i + 1} sur ${characters.length}`);
                choices.appendChild(btn);
            });
            const buttons = Array.from(choices.querySelectorAll('button'));

            const clamp = (n, a = 0, b = 1) => Math.min(b, Math.max(a, n));
            const smooth = (a, b, n) => {
                const t = clamp((n - a) / (b - a));
                return t * t * (3 - 2 * t);
            };

            function interior(context, char, font) {
                const canvas = context.canvas;
                context.font = font;
                const m = context.measureText(char);
                const pad = 8;
                const left = Math.ceil(m.actualBoundingBoxLeft || 0);
                const ascent = Math.ceil(m.actualBoundingBoxAscent || 0);
                canvas.width = Math.max(1, Math.ceil((m.actualBoundingBoxLeft || 0) + (m.actualBoundingBoxRight || m.width)) + pad * 2);
                canvas.height = Math.max(1, Math.ceil((m.actualBoundingBoxAscent || 0) + (m.actualBoundingBoxDescent || 20)) + pad * 2);
                context.font = font;
                context.fontKerning = "none";
                context.fillText(char, pad + left, pad + ascent);
                const { width, height } = canvas;
                const pixels = context.getImageData(0, 0, width, height).data;
                const rows = new Uint16Array(width + 1);
                let size = 0, bx = 0, by = 0;
                for (let y = 0; y < height; y++) {
                    let diagonal = 0;
                    for (let x = 0; x < width; x++) {
                        const above = rows[x + 1];
                        rows[x + 1] = pixels[(y * width + x) * 4 + 3] > 240
                            ? Math.min(above, rows[x], diagonal) + 1 : 0;
                        diagonal = above;
                        if (rows[x + 1] > size) { size = rows[x + 1]; bx = x; by = y; }
                    }
                }
                if (size < 3) return null;
                return {
                    x: (bx + 1 - size / 2 - pad - left) / 3,
                    y: (by + 1 - size / 2 - pad - ascent) / 3,
                    radius: (size / 2 - 1) / 3
                };
            }

            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d", { willReadFrequently: true });
            let W = 1, H = 1, travel = 1, startScale = 1, endScale = 1;
            let center = { x: 0, y: 0 }, target = null;
            let candidates = [], letters = [];
            let bounds = { x: 0, y: 0, width: 1, height: 1 };
            let ready = false;

            const readInk = () => {
                if (!context) return false;
                context.font = `${weight} 100px ${fontFamily}`;
                context.fontKerning = "none";
                const metrics = context.measureText(text);
                const advances = Array.from({ length: text.length }, (_, i) => context.measureText(text.slice(0, i)).width);
                bounds = {
                    x: -(metrics.actualBoundingBoxLeft || 0),
                    y: -(metrics.actualBoundingBoxAscent || 80),
                    width: (metrics.actualBoundingBoxLeft || 0) + (metrics.actualBoundingBoxRight || metrics.width),
                    height: (metrics.actualBoundingBoxAscent || 80) + (metrics.actualBoundingBoxDescent || 20)
                };
                if (!bounds.width || !bounds.height) return false;
                center = { x: bounds.x + bounds.width / 2, y: bounds.y + bounds.height / 2 };

                const scanFont = `${weight} 300px ${fontFamily}`;
                let offset = 0;
                candidates = []; letters = [];
                for (const char of Array.from(text)) {
                    context.font = `${weight} 100px ${fontFamily}`;
                    const m = context.measureText(char);
                    letters.push({
                        index: offset,
                        x: advances[offset] - (m.actualBoundingBoxLeft || 0),
                        y: -(m.actualBoundingBoxAscent || 80),
                        width: (m.actualBoundingBoxLeft || 0) + (m.actualBoundingBoxRight || m.width),
                        height: (m.actualBoundingBoxAscent || 80) + (m.actualBoundingBoxDescent || 20)
                    });
                    const found = interior(context, char, scanFont);
                    if (found) candidates.push({ ...found, x: found.x + advances[offset], index: offset });
                    offset += char.length;
                }
                // Par défaut, cible la plus grande lettre (ex: S ou B ou M)
                target = candidates.sort((a, b) => b.radius - a.radius)[0] || candidates[0] || null;
                return true;
            };

            const select = (next) => {
                target = next;
                endScale = target ? Math.max(startScale, Math.hypot(W, H) / (target.radius * 1.35)) : startScale;
                buttons.forEach(button => {
                    const isSelected = Number(button.dataset.gpLetter) === target?.index;
                    button.setAttribute("aria-checked", String(isSelected));
                });
                const u = 1 / startScale;
                const y = bounds.y + bounds.height + 25 * u;
                const x = bounds.x;
                const right = x + bounds.width;
                const cross = target ? `M${target.x - 9 * u} ${target.y}h${18 * u}M${target.x} ${target.y - 9 * u}v${18 * u}` : "";
                const annotationPath = marks.querySelector("path");
                if (annotationPath) {
                    annotationPath.setAttribute("d", `M${x} ${y}H${right}M${x} ${y - 5 * u}v${10 * u}M${right} ${y - 5 * u}v${10 * u}${cross}`);
                    annotationPath.setAttribute("stroke-width", String(u));
                }
            };

            const paint = (progress) => {
                const p = clamp(progress, 0, 1);
                const t = clamp(p / 0.78);
                const eased = t < 0.5 ? 4 * t ** 3 : 1 - (-2 * t + 2) ** 3 / 2;
                const scale = Math.exp(Math.log(startScale) + Math.log(endScale / startScale) * eased);
                const blend = endScale === startScale ? 0 : (1 / scale - 1 / startScale) / (1 / endScale - 1 / startScale);
                const cx = center.x + ((target?.x ?? center.x) - center.x) * blend;
                const cy = center.y + ((target?.y ?? center.y) - center.y) * blend;
                const roll = -4 * smooth(0.06, 0.5, t) * (1 - smooth(0.62, 0.92, t));
                const transform = `translate(${W / 2} ${H * 0.46 + H * 0.04 * eased}) scale(${scale}) rotate(${roll}) translate(${-cx} ${-cy})`;

                const radians = roll * Math.PI / 180;
                const dx = W / 2 / scale, dy = (H * .46 + H * .04 * eased) / scale;
                clip.setAttribute("transform", `scale(${scale}) rotate(${roll})`);
                glyph.setAttribute("transform", `translate(${Math.cos(radians) * dx + Math.sin(radians) * dy - cx} ${-Math.sin(radians) * dx + Math.cos(radians) * dy - cy})`);
                marks.setAttribute("transform", transform);
                marks.style.opacity = String(1 - smooth(0.015, 0.17, p));

                const choosing = p < .04;
                choices.inert = !choosing;
                section.dataset.gpChoosing = String(choosing);

                field.style.clipPath = t >= 1 ? "none" : "url(#gp-sublime-clip)";
                section.style.setProperty("--gp-caption", String(1 - smooth(0.01, 0.16, p)));
                section.style.setProperty("--gp-caption-hit", p < 0.08 ? "auto" : "none");
                section.style.setProperty("--gp-reveal", String(smooth(0.78, 0.9, p)));
                section.style.setProperty("--gp-field-scale", String(1 + .16 * smooth(0, .82, p)));
                section.dataset.gpEntered = String(p >= 0.9);
            };

            const layout = () => {
                W = container.clientWidth || 1000;
                H = container.clientHeight || 700;
                section.style.setProperty("--gp-height", `${H}px`);
                travel = H * length;
                art.setAttribute("viewBox", `0 0 ${W} ${H}`);
                if (!ready) { ready = readInk(); }
                if (!ready) return;
                startScale = Math.min(W * 0.84 / bounds.width, (H * 0.38) / bounds.height);
                select(target);
                buttons.forEach(button => {
                    const letter = letters.find(item => item.index === Number(button.dataset.gpLetter));
                    if (!letter) return;
                    Object.assign(button.style, {
                        left: `${W / 2 + (letter.x - center.x) * startScale}px`,
                        top: `${H * .46 + (letter.y - center.y) * startScale - Math.max(0, 44 - letter.height * startScale) / 2}px`,
                        width: `${Math.max(1, letter.width * startScale)}px`,
                        height: `${Math.max(44, letter.height * startScale)}px`,
                    });
                });
                section.style.setProperty("--gp-word-top", `${H * .46 - bounds.height * startScale / 2}px`);
                section.style.setProperty("--gp-word-bottom", `${H * .46 + bounds.height * startScale / 2}px`);
                section.dataset.gpReady = "true";
            };

            const getProgress = () => {
                return clamp(container.scrollTop / travel);
            };

            container.addEventListener("scroll", () => {
                paint(getProgress());
            }, { passive: true });

            window.addEventListener("resize", () => { layout(); paint(getProgress()); });

            choices.addEventListener("pointerover", (e) => {
                if (getProgress() >= 0.04) return;
                const btn = e.target.closest("[data-gp-letter]");
                if (!btn) return;
                const next = candidates.find(c => c.index === Number(btn.dataset.gpLetter));
                if (next && next !== target) {
                    select(next);
                    paint(getProgress());
                }
            });

            // Au clic sur "Accéder à vos documents", anime le défilement vers le contenu intérieur
            if (enterBtn) {
                enterBtn.addEventListener('click', () => {
                    container.scrollTo({
                        top: travel * 0.95,
                        behavior: 'smooth'
                    });
                });
            }

            setTimeout(() => { layout(); paint(getProgress()); }, 100);
        })();
    </script>
</body>
</html>
