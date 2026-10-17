<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe — DocScolaire®</title>
</head>
<body style="font-family:'Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#edece8;margin:0;padding:36px 16px;color:#141414;">
<div style="max-width:540px;margin:0 auto;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid rgba(0,0,0,0.08);box-shadow:0 12px 36px rgba(0,0,0,0.04);">
    
    <!-- Header Forma -->
    <div style="background:#141414;padding:32px 36px;color:#ffffff;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="font-size:18px;font-weight:900;letter-spacing:-0.04em;color:#ffffff;">
                DocScolaire<span style="color:#a8a49c;font-size:10px;vertical-align:super;">®</span>
            </div>
            <div style="font-family:monospace;font-size:10.5px;color:#a8a49c;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);padding:3px 10px;border-radius:9999px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">
                Sécurité
            </div>
        </div>
        <div style="font-size:22px;font-weight:800;letter-spacing:-0.035em;color:#f5f4f0;">Réinitialisation d'accès</div>
        <div style="font-size:12.5px;color:#a8a49c;margin-top:4px;">Procédure de sécurité authentifiée</div>
    </div>

    <!-- Contenu -->
    <div style="padding:34px 36px;">
        <p style="font-size:15px;font-weight:700;color:#141414;margin:0 0 14px;">
            Bonjour {{ $utilisateur->prenom }} {{ $utilisateur->nom }},
        </p>
        <p style="font-size:13.5px;color:#524f4b;line-height:1.65;margin:0 0 24px;">
            Une demande de réinitialisation de votre mot de passe a été initiée pour votre compte. Cliquez sur le bouton ci-dessous pour définir votre nouvel identifiant secret. Ce lien sécurisé est valable pendant <strong>{{ $expireMin }} minutes</strong>.
        </p>

        <!-- Bouton Carbone Pill -->
        <div style="text-align:center;margin-bottom:28px;">
            <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 32px;background:#141414;color:#edece8;border-radius:9999px;text-decoration:none;font-weight:700;font-size:13.5px;letter-spacing:-0.01em;box-shadow:0 4px 14px rgba(0,0,0,0.12);">
                Définir un nouveau mot de passe &rarr;
            </a>
        </div>

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:14px 18px;font-size:12px;color:#92400e;line-height:1.55;margin-bottom:22px;">
            ⚠️ Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité. Votre compte reste protégé.
        </div>

        <div style="font-size:11.5px;color:#8a857b;word-break:break-all;line-height:1.5;">
            Lien de secours : <a href="{{ $resetUrl }}" style="color:#141414;text-decoration:underline;">{{ $resetUrl }}</a>
        </div>
    </div>

    <!-- Footer -->
    <div style="background:#f7f6f2;border-top:1px solid rgba(0,0,0,0.06);padding:18px 36px;text-align:center;font-size:11.5px;color:#8a857b;">
        © {{ date('Y') }} DocScolaire® — Plateforme de Délivrance Sécurisée de Documents Académiques
    </div>
</div>
</body>
</html>
