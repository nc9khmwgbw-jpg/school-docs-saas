<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Officiel Validé — DocScolaire®</title>
</head>
<body style="font-family:'Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#edece8;margin:0;padding:36px 16px;color:#141414;">
<div style="max-width:540px;margin:0 auto;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid rgba(0,0,0,0.08);box-shadow:0 12px 36px rgba(0,0,0,0.04);">
    
    <!-- Header Forma -->
    <div style="background:#141414;padding:32px 36px;color:#ffffff;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="font-size:18px;font-weight:900;letter-spacing:-0.04em;color:#ffffff;">
                DocScolaire<span style="color:#a8a49c;font-size:10px;vertical-align:super;">®</span>
            </div>
            <div style="font-family:monospace;font-size:10.5px;color:#34d399;background:rgba(16,185,129,0.18);border:1px solid rgba(16,185,129,0.35);padding:3px 10px;border-radius:9999px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">
                Sceau Certifié
            </div>
        </div>
        <div style="font-size:22px;font-weight:800;letter-spacing:-0.035em;color:#f5f4f0;">Votre document officiel est prêt</div>
        <div style="font-size:12.5px;color:#a8a49c;margin-top:4px;">{{ $demande->ecole->nom ?? 'Établissement Universitaire' }}</div>
    </div>

    <!-- Contenu -->
    <div style="padding:34px 36px;">
        <p style="font-size:15px;font-weight:700;color:#141414;margin:0 0 14px;">
            Bonjour {{ $etudiant->prenom }} {{ $etudiant->nom }},
        </p>
        <p style="font-size:13.5px;color:#524f4b;line-height:1.65;margin:0 0 24px;">
            Votre demande pour la pièce <strong style="color:#141414;">{{ $demande->modele->titre }}</strong> a été formellement validée, signée et scellée avec le cachet officiel de votre établissement.
        </p>

        <!-- Fiche Récapitulative -->
        <div style="background:#f7f6f2;border:1px solid rgba(0,0,0,0.08);border-radius:16px;padding:20px 22px;margin-bottom:28px;">
            <div style="font-family:monospace;font-size:10.5px;font-weight:700;color:#8a857b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Attestation Certifiée</div>
            <div style="font-size:13px;color:#141414;margin-bottom:6px;"><strong>Pièce :</strong> {{ $demande->modele->titre }}</div>
            <div style="font-size:13px;color:#141414;margin-bottom:6px;"><strong>Matricule :</strong> {{ $etudiant->matricule ?? '—' }}</div>
            <div style="font-size:13px;color:#141414;"><strong>Date d'émission :</strong> {{ $demande->date_traitement?->format('d/m/Y à H:i') ?? date('d/m/Y à H:i') }}</div>
        </div>

        <!-- Bouton Carbone Pill -->
        <div style="text-align:center;margin-bottom:28px;">
            <a href="{{ route('tenant.portal', ['code_unique' => $demande->ecole->code_unique]) }}" style="display:inline-block;padding:14px 32px;background:#141414;color:#edece8;border-radius:9999px;text-decoration:none;font-weight:700;font-size:13.5px;letter-spacing:-0.01em;box-shadow:0 4px 14px rgba(0,0,0,0.12);">
                Télécharger mon Document PDF &rarr;
            </a>
        </div>

        <p style="font-size:12px;color:#8a857b;margin:0;text-align:center;line-height:1.55;">
            Ce document intègre un QR Code infalsifiable permettant sa vérification instantanée par les tiers et autorités.
        </p>
    </div>

    <!-- Footer -->
    <div style="background:#f7f6f2;border-top:1px solid rgba(0,0,0,0.06);padding:18px 36px;text-align:center;font-size:11.5px;color:#8a857b;">
        © {{ date('Y') }} DocScolaire® — Plateforme de Délivrance Sécurisée de Documents Académiques
    </div>
</div>
</body>
</html>
