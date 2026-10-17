@extends('layouts.superadmin')

@section('title', 'Nouvel Établissement')
@section('page_title', 'Provisionner un Établissement (Tenant)')

@push('styles')
<style>
    .back-nav {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        margin-bottom: 20px;
        transition: color 0.15s;
    }
    .back-nav:hover {
        color: #0b3b2a;
    }
    .section-title {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
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
    .form-group.full {
        grid-column: 1 / -1;
    }
    label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #4b5563;
    }
    input[type="text"], input[type="email"], input[type="password"], textarea {
        padding: 11px 14px;
        background: #fbfbfa;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 13.5px;
        color: var(--text-main);
        font-family: inherit;
        outline: none;
        transition: all 0.2s;
    }
    input:focus, textarea:focus {
        border-color: #0b3b2a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(11, 59, 42, 0.08);
    }
    .hint {
        font-size: 11px;
        color: var(--text-muted);
    }
    .btn-submit {
        padding: 12px 24px;
        background: #0b3b2a;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
    }
    .btn-submit:hover {
        background: #16402e;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(11, 59, 42, 0.2);
    }
    .btn-cancel {
        padding: 12px 20px;
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.15s;
    }
    .btn-cancel:hover {
        background: #fbfbfa;
        color: var(--text-main);
    }
</style>
@endpush

@section('content')
<div style="max-width: 860px;">
    <a href="{{ route('superadmin.ecoles.index') }}" class="back-nav">← Retour à la liste des établissements</a>

    <form action="{{ route('superadmin.ecoles.store') }}" method="POST">
        @csrf

        <!-- 1. Identité Tenant -->
        <div class="card">
            <h2 class="section-title">1. Identité de l'Établissement (Tenant Isolé)</h2>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="nom">Nom officiel de l'école *</label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Institut Supérieur de Management et d'Informatique" value="{{ old('nom') }}" required>
                </div>
                <div class="form-group">
                    <label for="code_unique">Code Identifiant Unique *</label>
                    <input type="text" id="code_unique" name="code_unique" placeholder="Ex: ISMI-2026" value="{{ old('code_unique') }}" style="text-transform:uppercase;" required>
                    <span class="hint">Identifiant portail utilisé par les étudiants pour se connecter</span>
                </div>
                <div class="form-group">
                    <label for="email_contact">Email institutionnel de contact *</label>
                    <input type="email" id="email_contact" name="email_contact" placeholder="scolarite@ismi.ma" value="{{ old('email_contact') }}" required>
                </div>
                <div class="form-group">
                    <label for="couleur_primaire">Couleur identitaire (HEX)</label>
                    <input type="text" id="couleur_primaire" name="couleur_primaire" value="{{ old('couleur_primaire', '#0b3b2a') }}" placeholder="#0b3b2a">
                    <span class="hint">Apparence du portail de l'établissement</span>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" placeholder="+212 5..." value="{{ old('telephone') }}">
                </div>
                <div class="form-group full">
                    <label for="adresse">Adresse géographique</label>
                    <textarea id="adresse" name="adresse" rows="2" placeholder="Campus Universitaire, Avenue Hassan II...">{{ old('adresse') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Administrateur initial -->
        <div class="card">
            <h2 class="section-title">2. Compte Administrateur Principal de l'École</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="admin_nom">Nom de famille *</label>
                    <input type="text" id="admin_nom" name="admin_nom" placeholder="El Amrani" value="{{ old('admin_nom') }}" required>
                </div>
                <div class="form-group">
                    <label for="admin_prenom">Prénom *</label>
                    <input type="text" id="admin_prenom" name="admin_prenom" placeholder="Yassine" value="{{ old('admin_prenom') }}" required>
                </div>
                <div class="form-group full">
                    <label for="admin_email">Email professionnel (identifiant de connexion) *</label>
                    <input type="email" id="admin_email" name="admin_email" placeholder="admin@ismi.ma" value="{{ old('admin_email') }}" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Mot de passe temporaire *</label>
                    <input type="password" id="admin_password" name="admin_password" placeholder="Minimum 10 caractères" required minlength="10">
                </div>
                <div class="form-group">
                    <label for="admin_password_confirmation">Confirmer le mot de passe *</label>
                    <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" placeholder="••••••••••" required minlength="10">
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;">
            <a href="{{ route('superadmin.ecoles.index') }}" class="btn-cancel">Annuler</a>
            <button type="submit" class="btn-submit">🚀 Provisionner et activer l'école</button>
        </div>
    </form>
</div>
@endsection
