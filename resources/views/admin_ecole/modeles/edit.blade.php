@extends('layouts.admin_ecole')

@section('title', 'Modifier le Modèle')
@section('page_title', 'Modifier : ' . $modele->titre)

@push('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0b3b2a;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 16px;
    }
    .back-link:hover { text-decoration: underline; }

    .page-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 20px;
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
    input[type="text"], input[type="number"], select {
        height: 46px;
        padding: 0 14px;
        background: #fbfbfa;
        border: 1px solid rgba(16, 38, 29, 0.18);
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        transition: all 0.2s;
    }
    input:focus, select:focus {
        border-color: #0b3b2a;
        box-shadow: 0 0 0 3px rgba(110, 231, 183, 0.35);
    }

    .code-editor {
        width: 100%;
        padding: 14px;
        background: #fbfbfa;
        border: 1px solid rgba(16, 38, 29, 0.18);
        border-radius: 10px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        color: #18251e;
        line-height: 1.5;
        outline: none;
    }
    .code-editor:focus {
        border-color: #0b3b2a;
        box-shadow: 0 0 0 3px rgba(110, 231, 183, 0.35);
    }

    .variables-box {
        background: #f4f7f5;
        border: 1px solid rgba(16, 38, 29, 0.12);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 16px;
    }
    .variables-title {
        font-size: 12px;
        font-weight: 700;
        color: #0b3b2a;
        margin-bottom: 8px;
    }
    .tags-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .tag-token {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px;
        background: #ffffff;
        border: 1px solid rgba(16, 38, 29, 0.15);
        color: #0b3b2a;
        padding: 4px 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tag-token:hover {
        background: #0b3b2a;
        color: #ffffff;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-main);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    .btn-submit {
        padding: 12px 24px;
        background: #0b3b2a;
        color: #ffffff;
        border: 0;
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-submit:hover { background: #144935; }
    .btn-cancel {
        padding: 12px 20px;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        text-decoration: none;
    }
</style>
@endpush

@section('content')

    <a href="{{ route('admin_ecole.modeles.index') }}" class="back-link">&larr; Retour aux modèles</a>
    <h1 class="page-title">Modifier : {{ $modele->titre }}</h1>

    <form action="{{ route('admin_ecole.modeles.update', $modele->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="form-grid">
                <div class="form-group">
                    <label for="titre">Titre du document *</label>
                    <input type="text" id="titre" name="titre" value="{{ old('titre', $modele->titre) }}" required>
                </div>
                <div class="form-group">
                    <label for="type_document">Type de document *</label>
                    <select id="type_document" name="type_document" required>
                        <option value="attestation_reussite" {{ old('type_document', $modele->type_document) == 'attestation_reussite' ? 'selected' : '' }}>Attestation de Réussite</option>
                        <option value="certificat_scolarite" {{ old('type_document', $modele->type_document) == 'certificat_scolarite' ? 'selected' : '' }}>Certificat de Scolarité</option>
                        <option value="releve_notes" {{ old('type_document', $modele->type_document) == 'releve_notes' ? 'selected' : '' }}>Relevé de Notes</option>
                        <option value="convention_stage" {{ old('type_document', $modele->type_document) == 'convention_stage' ? 'selected' : '' }}>Convention de Stage</option>
                        <option value="certificat" {{ old('type_document', $modele->type_document) == 'certificat' ? 'selected' : '' }}>Autre Certificat</option>
                        <option value="duplicata" {{ old('type_document', $modele->type_document) == 'duplicata' ? 'selected' : '' }}>Duplicata</option>
                        <option value="autre" {{ old('type_document', $modele->type_document) == 'autre' ? 'selected' : '' }}>Autre Document</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" value="{{ old('description', $modele->description) }}">
                </div>
                <div class="form-group">
                    <label for="langue">Langue du modèle</label>
                    <select id="langue" name="langue">
                        <option value="fr" {{ old('langue', $modele->langue) == 'fr' ? 'selected' : '' }}>Français (FR)</option>
                        <option value="ar" {{ old('langue', $modele->langue) == 'ar' ? 'selected' : '' }}>Arabe (AR)</option>
                        <option value="fr_ar" {{ old('langue', $modele->langue) == 'fr_ar' ? 'selected' : '' }}>Bilingue (FR / AR)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="expire_dans_jours">Durée de validité (jours)</label>
                    <input type="number" id="expire_dans_jours" name="expire_dans_jours" min="1" max="3650" value="{{ old('expire_dans_jours', $modele->expire_dans_jours) }}">
                </div>
                <div class="form-group full">
                    <label class="checkbox-label">
                        <input type="checkbox" name="validation_requise" value="1" {{ old('validation_requise', $modele->validation_requise) ? 'checked' : '' }}>
                        <span>🔒 Exiger une validation administrative avant chaque génération de PDF</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">Gabarit HTML &amp; Variables</h3>
            <div class="variables-box">
                <div class="variables-title">📌 Variables dynamiques officielles (cliquez pour insérer) :</div>
                <div class="tags-list">
                    <span class="tag-token" onclick="insertTag('{nom}')">{nom}</span>
                    <span class="tag-token" onclick="insertTag('{prenom}')">{prenom}</span>
                    <span class="tag-token" onclick="insertTag('{matricule}')">{matricule}</span>
                    <span class="tag-token" onclick="insertTag('{filiere}')">{filiere}</span>
                    <span class="tag-token" onclick="insertTag('{niveau}')">{niveau}</span>
                    <span class="tag-token" onclick="insertTag('{annee_universitaire}')">{annee_universitaire}</span>
                    <span class="tag-token" onclick="insertTag('{date_du_jour}')">{date_du_jour}</span>
                    <span class="tag-token" onclick="insertTag('{nom_ecole}')">{nom_ecole}</span>
                    <span class="tag-token" onclick="insertTag('{adresse_ecole}')">{adresse_ecole}</span>
                    <span class="tag-token" onclick="insertTag('{email_ecole}')">{email_ecole}</span>
                    <span class="tag-token" onclick="insertTag('{telephone_ecole}')">{telephone_ecole}</span>
                    <span class="tag-token" onclick="insertTag('{logo}')" style="background:#e0e7ff;border-color:#818cf8;color:#3730a3;">{logo}</span>
                    <span class="tag-token" onclick="insertTag('{cachet}')" style="background:#fef3c7;border-color:#f59e0b;color:#92400e;">{cachet}</span>
                    <span class="tag-token" onclick="insertTag('{signature}')" style="background:#ecfdf5;border-color:#10b981;color:#065f46;">{signature}</span>
                    <span class="tag-token" onclick="insertTag('{photo_etudiant}')" style="background:#f3e8ff;border-color:#a855f7;color:#6b21a8;">{photo_etudiant}</span>
                    <span class="tag-token" onclick="insertTag('{qr_code}')" style="background:#f1f5f9;border-color:#64748b;color:#0f172a;font-weight:700;">{qr_code}</span>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="contenu_html">Contenu HTML du template *</label>
                <textarea id="contenu_html" name="contenu_html" class="code-editor" rows="12" required>{{ old('contenu_html', $modele->contenu_html) }}</textarea>
            </div>

            <div class="form-group">
                <label for="styles_css">Styles CSS additionnels (optionnel)</label>
                <textarea id="styles_css" name="styles_css" class="code-editor" rows="6">{{ old('styles_css', $modele->styles_css) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">💾 Enregistrer les modifications</button>
                <a href="{{ route('admin_ecole.modeles.index') }}" class="btn-cancel">Annuler</a>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    function insertTag(tag) {
        const textarea = document.getElementById('contenu_html');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    }
</script>
@endpush
