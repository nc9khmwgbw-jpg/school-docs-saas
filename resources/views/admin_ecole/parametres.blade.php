@extends('layouts.admin_ecole')

@section('title', 'Paramètres de l\'Établissement')
@section('page_title', 'Configuration & Charte de l\'Établissement')

@push('styles')
<style>
    .portal-url-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--canvas-subtle);
        border: 1px solid var(--hairline-strong);
        border-radius: 14px;
        padding: 12px 18px;
    }
    .portal-url-text {
        font-family: var(--font-mono);
        font-size: 13px;
        color: var(--ink-primary);
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .btn-copy {
        padding: 7px 16px;
        background: var(--canvas-card);
        border: 1px solid var(--hairline-strong);
        border-radius: 9999px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        color: var(--ink-primary);
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-copy:hover {
        background: var(--ink-primary);
        color: var(--canvas-bg);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group.full { grid-column: 1 / -1; }

    label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #555e58;
    }
    input[type="text"], input[type="email"], textarea {
        padding: 12px 14px;
        background: #fbfbfa;
        border: 1px solid rgba(16, 38, 29, 0.18);
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        transition: all 0.2s;
    }
    input:focus, textarea:focus {
        border-color: #0b3b2a;
        box-shadow: 0 0 0 3px rgba(110, 231, 183, 0.35);
    }

    .color-picker-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    input[type="color"] {
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        background: transparent;
    }
</style>
@endpush

@section('content')

    <!-- Lien direct portail -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;">🔗 Lien direct du portail étudiant</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;">Partagez ce lien avec vos étudiants ou intégrez-le sur le site de votre école :</p>
        <div class="portal-url-box">
            <span class="portal-url-text" id="portalUrl">{{ url('/portal/' . $ecole->code_unique) }}</span>
            <button type="button" class="btn-copy" onclick="copyPortalUrl()">📋 Copier le lien</button>
        </div>
    </div>

    <!-- Logo -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">🖼️ Logo Officiel de l'Établissement</h3>
        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="width:84px;height:84px;border-radius:14px;background:#fbfbfa;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                @if($ecole->logo_url)
                    <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                @else
                    <span style="font-size:32px;">🏫</span>
                @endif
            </div>
            <form action="{{ route('admin_ecole.parametres.logo') }}" method="POST" enctype="multipart/form-data" style="flex:1;min-width:260px;">
                @csrf
                <label for="logo">Nouveau logo (PNG, JPG, SVG max 2 Mo) :</label>
                <div style="display:flex;gap:10px;margin-top:8px;">
                    <input type="file" id="logo" name="logo" accept="image/*" required style="padding-top:10px;flex:1;">
                    <button type="submit" class="btn-primary-action">Mettre à jour le logo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sceaux & Signatures Officielles -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;">🖋️ Cachet Officiel &amp; Signature de Direction</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px;">Ces éléments graphiques sont intégrés dynamiquement dans les attestations et relevés officiels délivrés aux étudiants.</p>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;">
            <!-- Cachet -->
            <div style="border:1px solid var(--border);border-radius:12px;padding:16px;background:#fbfbfa;">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:10px;color:#0b3b2a;">Cachet Officiel (Sceau)</h4>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
                    <div style="width:72px;height:72px;border-radius:10px;background:#fff;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        @if($ecole->cachet_path)
                            <img src="{{ asset('storage/' . $ecole->cachet_path) }}" alt="Cachet" style="max-width:100%;max-height:100%;object-fit:contain;">
                        @else
                            <span style="font-size:24px;">💮</span>
                        @endif
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);">
                        @if($ecole->cachet_path)
                            <span style="color:#065f46;font-weight:700;">✓ Cachet actif</span>
                        @else
                            <span>Aucun cachet enregistré</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('admin_ecole.parametres.cachet') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="cachet" accept="image/*" required style="font-size:12px;width:100%;margin-bottom:8px;">
                    <button type="submit" class="btn-copy" style="width:100%;">Mettre à jour le cachet</button>
                </form>
            </div>

            <!-- Signature -->
            <div style="border:1px solid var(--border);border-radius:12px;padding:16px;background:#fbfbfa;">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:10px;color:#0b3b2a;">Signature du Directeur / Doyen</h4>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
                    <div style="width:72px;height:72px;border-radius:10px;background:#fff;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        @if($ecole->signature_path)
                            <img src="{{ asset('storage/' . $ecole->signature_path) }}" alt="Signature" style="max-width:100%;max-height:100%;object-fit:contain;">
                        @else
                            <span style="font-size:24px;">✍️</span>
                        @endif
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);">
                        @if($ecole->signature_path)
                            <span style="color:#065f46;font-weight:700;">✓ Signature active</span>
                        @else
                            <span>Aucune signature enregistrée</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('admin_ecole.parametres.signature') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="signature" accept="image/*" required style="font-size:12px;width:100%;margin-bottom:8px;">
                    <button type="submit" class="btn-copy" style="width:100%;">Mettre à jour la signature</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Coordonnées et couleurs -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Informations Générales &amp; Charte Graphique</h3>
        <form action="{{ route('admin_ecole.parametres.save') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group full">
                    <label for="nom">Nom officiel de l'établissement *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $ecole->nom) }}" required>
                </div>
                <div class="form-group">
                    <label for="code_unique">Code Identifiant Unique (Fixe)</label>
                    <input type="text" id="code_unique" value="{{ $ecole->code_unique }}" disabled style="opacity:.6;cursor:not-allowed;">
                </div>
                <div class="form-group">
                    <label for="email_contact">Email de contact</label>
                    <input type="email" id="email_contact" name="email_contact" value="{{ old('email_contact', $ecole->email_contact) }}">
                </div>
                <div class="form-group">
                    <label for="telephone">Numéro de téléphone</label>
                    <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $ecole->telephone) }}">
                </div>
                <div class="form-group full">
                    <label for="adresse">Adresse géographique</label>
                    <textarea id="adresse" name="adresse" rows="2">{{ old('adresse', $ecole->adresse) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="couleur_primaire">Couleur Primaire (Portail &amp; Documents)</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="couleur_primaire_picker" value="{{ old('couleur_primaire', $ecole->couleur_primaire ?? '#0b3b2a') }}" onchange="document.getElementById('couleur_primaire').value = this.value">
                        <input type="text" id="couleur_primaire" name="couleur_primaire" value="{{ old('couleur_primaire', $ecole->couleur_primaire ?? '#0b3b2a') }}" style="max-width:120px;" onchange="document.getElementById('couleur_primaire_picker').value = this.value">
                    </div>
                </div>
                <div class="form-group">
                    <label for="couleur_secondaire">Couleur Secondaire</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="couleur_secondaire_picker" value="{{ old('couleur_secondaire', $ecole->couleur_secondaire ?? '#6ee7b7') }}" onchange="document.getElementById('couleur_secondaire').value = this.value">
                        <input type="text" id="couleur_secondaire" name="couleur_secondaire" value="{{ old('couleur_secondaire', $ecole->couleur_secondaire ?? '#6ee7b7') }}" style="max-width:120px;" onchange="document.getElementById('couleur_secondaire_picker').value = this.value">
                    </div>
                </div>
            </div>
            <div style="margin-top:24px;display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-primary-action">💾 Enregistrer les modifications</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    function copyPortalUrl() {
        const url = document.getElementById('portalUrl').textContent;
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
</script>
@endpush
