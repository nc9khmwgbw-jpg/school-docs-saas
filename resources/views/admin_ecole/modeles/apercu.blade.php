<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu : {{ $modele->titre }} | DocScolaire SaaS</title>
    <link rel="icon" type="image/png" href="/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; background: #eef2ef; min-height: 100vh; display: flex; flex-direction: column; }

        .toolbar { background: #0b3b2a; color: #fff; padding: 12px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 16px rgba(11,59,42,0.25); }
        .toolbar-title { font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .toolbar-badge { background: rgba(110,231,183,0.18); color: #6ee7b7; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 700; }
        .toolbar-actions { display: flex; align-items: center; gap: 10px; }
        .btn-tool { padding: 8px 14px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; font-family: inherit; transition: opacity .15s; }
        .btn-back { background: rgba(255,255,255,.1); color: #fbfbfa; }
        .btn-edit { background: #ffffff; color: #0b3b2a; }
        .btn-print { background: #6ee7b7; color: #0b3b2a; border: none; }
        .btn-tool:hover { opacity: .9; }

        .preview-container { flex: 1; padding: 40px 20px; display: flex; justify-content: center; }
        .a4-sheet {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            box-shadow: 0 8px 30px rgba(0,0,0,.12);
            border-radius: 4px;
            box-sizing: border-box;
            position: relative;
        }

        /* Custom model CSS injected safely */
        {!! $modele->styles_css !!}

        @media print {
            .toolbar { display: none; }
            body { background: #fff; padding: 0; }
            .preview-container { padding: 0; }
            .a4-sheet { box-shadow: none; padding: 15mm; width: 100%; min-height: auto; }
        }
    </style>
</head>
<body>
    @php
        $dummyVars = [
            '{nom}'                => 'ALAMI',
            '{prenom}'             => 'Sara',
            '{matricule}'          => 'E2026-9901',
            '{filiere}'            => 'Génie Logiciel',
            '{niveau}'             => 'Master 2',
            '{annee_universitaire}'=> '2025-2026',
            '{annee_actuelle}'     => date('Y'),
            '{moyenne}'            => '17.40',
            '{mention}'            => 'Très Bien',
            '{date_du_jour}'       => date('d/m/Y'),
            '{nom_ecole}'          => auth()->user()->ecole->nom ?? 'Établissement Universitaire',
            '{logo}'               => (auth()->user()->ecole && auth()->user()->ecole->logo_url)
                ? '<img src="' . auth()->user()->ecole->logo_url . '" style="max-height:60px;max-width:180px;" alt="Logo">'
                : '<div style="font-size:22px;font-weight:800;color:#0b3b2a;">' . e(auth()->user()->ecole->nom ?? 'UNIVERSITÉ') . '</div>',
            '{cachet}'             => (auth()->user()->ecole && auth()->user()->ecole->cachet_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->ecole->cachet_path))
                ? '<img src="' . asset('storage/' . auth()->user()->ecole->cachet_path) . '" style="max-height:80px;max-width:140px;" alt="Cachet">'
                : '<div style="border:2px dashed #0b3b2a;border-radius:50%;width:76px;height:76px;display:inline-flex;align-items:center;justify-content:center;color:#0b3b2a;font-size:9px;font-weight:700;text-align:center;">SCEAU<br/>OFFICIEL</div>',
            '{signature}'          => (auth()->user()->ecole && auth()->user()->ecole->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->ecole->signature_path))
                ? '<img src="' . asset('storage/' . auth()->user()->ecole->signature_path) . '" style="max-height:65px;max-width:160px;" alt="Signature">'
                : '<div style="font-family:cursive,sans-serif;font-size:18px;color:#1e3a8a;font-style:italic;">Le Directeur</div>',
            '{qr_code}'            => '<div style="margin-top:30px;display:flex;align-items:center;gap:15px;padding:12px;border:1px dashed #cbd5e1;background:#f8fafc;width:fit-content;border-radius:8px;">
                                        <div style="width:70px;height:70px;background:#0b3b2a;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#6ee7b7;font-size:10px;text-align:center;font-weight:700;">[QR CODE<br/>UUID v4]</div>
                                        <div style="font-size:11px;color:#64748b;line-height:1.4;"><strong>Document Sécurisé DocScolaire</strong><br/>Scannez pour vérifier l\'authenticité<br/><span style="font-family:monospace;font-size:10px;">ID: ' . Str::uuid() . '</span></div>
                                       </div>',
        ];

        $renderedHtml = str_replace(array_keys($dummyVars), array_values($dummyVars), $modele->contenu_html);
    @endphp

    <div class="toolbar">
        <div class="toolbar-title">
            <span>Aperçu en conditions réelles : {{ $modele->titre }}</span>
            <span class="toolbar-badge">Simulation Données Étudiant</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('admin_ecole.modeles.index') }}" class="btn-tool btn-back">&larr; Retour</a>
            <a href="{{ route('admin_ecole.modeles.edit', $modele->id) }}" class="btn-tool btn-edit">✏️ Modifier</a>
            <button onclick="window.print()" class="btn-tool btn-print">🖨️ Imprimer / Tester PDF</button>
        </div>
    </div>

    <div class="preview-container">
        <div class="a4-sheet">
            {!! $renderedHtml !!}
        </div>
    </div>
</body>
</html>
