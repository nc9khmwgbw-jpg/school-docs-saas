<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de votre demande — DocScolaire®</title>
</head>
<body style="font-family:'Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#edece8;margin:0;padding:36px 16px;color:#141414;">
<div style="max-width:540px;margin:0 auto;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid rgba(0,0,0,0.08);box-shadow:0 12px 36px rgba(0,0,0,0.04);">
    
    <!-- Header Forma -->
    <div style="background:#141414;padding:32px 36px;color:#ffffff;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="font-size:18px;font-weight:900;letter-spacing:-0.04em;color:#ffffff;">
                DocScolaire<span style="color:#a8a49c;font-size:10px;vertical-align:super;">®</span>
            </div>
            <div style="font-family:monospace;font-size:10.5px;color:#f87171;background:rgba(239,68,68,0.18);border:1px solid rgba(239,68,68,0.35);padding:3px 10px;border-radius:9999px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">
                Notification Scolarité
            </div>
        </div>
        <div style="font-size:22px;font-weight:800;letter-spacing:-0.035em;color:#f5f4f0;">Statut de votre demande</div>
        <div style="font-size:12.5px;color:#a8a49c;margin-top:4px;">{{ $demande->ecole->nom ?? 'Établissement Universitaire' }}</div>
    </div>

    <!-- Contenu -->
    <div style="padding:34px 36px;">
        <p style="font-size:15px;font-weight:700;color:#141414;margin:0 0 14px;">
            Bonjour {{ $etudiant->prenom }} {{ $etudiant->nom }},
        </p>
        <p style="font-size:13.5px;color:#524f4b;line-height:1.65;margin:0 0 20px;">
            Votre demande pour la pièce <strong style="color:#141414;">{{ $demande->modele->titre }}</strong> n'a pas pu être validée par le service de scolarité de votre établissement.
        </p>

        @if($demande->motif_refus)
        <!-- Motif Officiel -->
        <div style="background:#fff5f5;border:1px solid rgba(239,68,68,0.25);border-radius:16px;padding:18px 22px;margin-bottom:24px;">
            <div style="font-family:monospace;font-size:10.5px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;">Motif officiel communiqué</div>
            <div style="font-size:13.5px;color:#991b1b;line-height:1.6;">{{ $demande->motif_refus }}</div>
        </div>
        @endif

        <div style="text-align:center;margin-bottom:24px;">
            <a href="{{ route('tenant.portal', ['code_unique' => $demande->ecole->code_unique]) }}" style="display:inline-block;padding:12px 28px;background:transparent;border:1px solid rgba(0,0,0,0.15);color:#141414;border-radius:9999px;text-decoration:none;font-weight:700;font-size:13px;">
                Accéder à mon espace étudiant &rarr;
            </a>
        </div>

        <p style="font-size:12px;color:#8a857b;margin:0;line-height:1.55;text-align:center;">
            Pour tout renseignement complémentaire, nous vous invitons à contacter directement le secrétariat pédagogique de votre établissement.
        </p>
    </div>

    <!-- Footer -->
    <div style="background:#f7f6f2;border-top:1px solid rgba(0,0,0,0.06);padding:18px 36px;text-align:center;font-size:11.5px;color:#8a857b;">
        © {{ date('Y') }} DocScolaire® — Plateforme de Délivrance Sécurisée de Documents Académiques
    </div>
</div>
</body>
</html>
