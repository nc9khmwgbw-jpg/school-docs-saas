@extends('layouts.superadmin')

@section('title', 'Cockpit Global SaaS')
@section('page_title', 'Cockpit de Supervision Multi-Tenants')

@push('styles')
<style>
    .kpi-super-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }
    .form-provision-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 800px) {
        .form-provision-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

    <!-- 5 KPIs Métriques SaaS Multi-Tenants -->
    <div class="kpi-super-grid">
        <div class="metric-kpi-card indigo">
            <div class="metric-header">
                <span class="metric-title">Établissements Totaux</span>
                <div class="metric-icon-wrap icon-indigo">🏫</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $stats['total_ecoles'] }}</span>
                <span class="metric-trend-badge">Partenaires</span>
            </div>
        </div>

        <div class="metric-kpi-card emerald">
            <div class="metric-header">
                <span class="metric-title">Écoles Actives</span>
                <div class="metric-icon-wrap icon-emerald">⚡</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#059669;">{{ $stats['ecoles_actives'] }}</span>
                <span class="badge-status badge-success" style="font-size:10.5px;">En production</span>
            </div>
        </div>

        <div class="metric-kpi-card amber">
            <div class="metric-header">
                <span class="metric-title">Écoles Suspendues</span>
                <div class="metric-icon-wrap icon-amber">⏸</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#d97706;">{{ $stats['ecoles_suspendues'] }}</span>
                <span style="font-size:11px;color:#94a3b8;font-weight:600;">Hors ligne</span>
            </div>
        </div>

        <div class="metric-kpi-card emerald">
            <div class="metric-header">
                <span class="metric-title">Documents Émis</span>
                <div class="metric-icon-wrap icon-emerald">🛡️</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#059669;">{{ $stats['total_documents'] }}</span>
                <span class="metric-trend-badge">Certifiés SHA-256</span>
            </div>
        </div>

        <div class="metric-kpi-card rose">
            <div class="metric-header">
                <span class="metric-title">Demandes du Mois</span>
                <div class="metric-icon-wrap icon-rose">📈</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#4f46e5;">{{ $stats['demandes_ce_mois'] }}</span>
                <span style="font-size:11px;color:#64748b;font-weight:600;">Flux actif</span>
            </div>
        </div>
    </div>

    <!-- Annuaire & Pilotage Rapide des Établissements Partenaires -->
    <div class="modern-card" style="margin-bottom:28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="font-size:18px;font-weight:800;letter-spacing:-0.02em;color:#0f172a;">🏫 Établissements Partenaires (Tenants Actifs)</h3>
                <p style="font-size:13px;color:#64748b;margin-top:2px;">Contrôle d'accès, statut de licence et prise de contrôle master en un clic.</p>
            </div>
            <a href="{{ route('superadmin.ecoles.index') }}" class="btn-modern-ghost" style="font-size:12.5px;padding:6px 12px;">
                Consulter tout l'annuaire &rarr;
            </a>
        </div>

        <div class="modern-table-wrap" style="box-shadow:none;border-color:#f1f5f9;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Établissement</th>
                        <th>Code Unique</th>
                        <th>Utilisateurs</th>
                        <th>Documents Scellés</th>
                        <th>Statut Plateforme</th>
                        <th style="text-align:right;">Actions Master</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ecoles as $ecole)
                        <tr>
                            <td>
                                <a href="{{ route('superadmin.ecoles.show', $ecole->id) }}" style="font-weight:700;color:#0f172a;text-decoration:none;">
                                    {{ $ecole->nom }}
                                </a>
                                <div style="font-size:11.5px;color:#64748b;">{{ $ecole->email_contact }}</div>
                            </td>
                            <td>
                                <span class="badge-tenant">{{ $ecole->code_unique }}</span>
                            </td>
                            <td>
                                <strong style="color:#0f172a;">{{ $ecole->utilisateurs_count }}</strong>
                            </td>
                            <td>
                                <span style="font-weight:700;color:#059669;">{{ $ecole->documents_generes_count }}</span>
                            </td>
                            <td>
                                @if($ecole->statut === 'actif')
                                    <span class="badge-status badge-success">
                                        <span class="beacon-dot"></span>
                                        Actif
                                    </span>
                                @elseif($ecole->statut === 'suspendu')
                                    <span class="badge-status badge-warning">
                                        <span class="beacon-dot"></span>
                                        Suspendu
                                    </span>
                                @else
                                    <span class="badge-status">{{ $ecole->statut }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;gap:8px;align-items:center;">
                                    <!-- Bouton Impersonate / Accès Master en 1 clic -->
                                    <form action="{{ route('superadmin.impersonate.ecole', $ecole->id) }}" method="POST" style="display:inline;margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-modern-ghost" style="padding:5px 10px;font-size:11.5px;color:#4f46e5;border-color:#c7d2fe;background:#eef2ff;" title="Prendre la main sur l'espace école sans mot de passe">
                                            ⚡ Piloter
                                        </button>
                                    </form>

                                    <a href="{{ route('superadmin.ecoles.show', $ecole->id) }}" class="btn-modern-ghost" style="padding:5px 10px;font-size:11.5px;">
                                        Fiche
                                    </a>

                                    <form action="{{ route('superadmin.ecoles.toggle_statut', $ecole->id) }}" method="POST" style="display:inline;margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-modern-ghost" style="padding:5px 10px;font-size:11.5px;color:{{ $ecole->statut === 'actif' ? '#dc2626' : '#059669' }};">
                                            {{ $ecole->statut === 'actif' ? 'Suspendre' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:36px;color:#94a3b8;">
                                Aucun établissement inscrit pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Provisionner un Nouvel Établissement (Bento Form) -->
    <div class="modern-card">
        <div style="padding-bottom:18px;margin-bottom:20px;border-bottom:1px solid #e2e8f0;">
            <h3 style="font-size:18px;font-weight:800;letter-spacing:-0.02em;color:#0f172a;">➕ Provisionner un Nouvel Établissement (Tenant Cloisonné)</h3>
            <p style="font-size:13px;color:#64748b;margin-top:2px;">Création instantanée d'un nouvel environnement hermétique et de son administrateur délégué.</p>
        </div>

        <form action="{{ route('superadmin.ecoles.store') }}" method="POST">
            @csrf
            <div class="form-provision-grid">
                <div>
                    <label class="label-modern">Nom officiel de l'école *</label>
                    <input type="text" name="nom" class="input-modern" required placeholder="Ex: École Nationale Supérieure" value="{{ old('nom') }}">
                </div>
                <div>
                    <label class="label-modern">Code unique (identifiant portail) *</label>
                    <input type="text" name="code_unique" class="input-modern text-mono" required placeholder="Ex: ENS-2026" value="{{ old('code_unique') }}" style="text-transform:uppercase;">
                </div>
                <div>
                    <label class="label-modern">Email institutionnel de contact *</label>
                    <input type="email" name="email_contact" class="input-modern" required placeholder="contact@ecole.ma" value="{{ old('email_contact') }}">
                </div>
                <div>
                    <label class="label-modern">Couleur identitaire (HEX)</label>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <input type="color" name="couleur_primaire" value="{{ old('couleur_primaire', '#4f46e5') }}" style="height:48px;width:64px;border:1px solid #cbd5e1;border-radius:10px;cursor:pointer;padding:2px;">
                        <span style="font-size:12.5px;color:#64748b;">Couleur officielle du portail étudiant</span>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; border-top:1px solid #f1f5f9; padding-top:16px; margin-top:8px;">
                    <label style="font-size:13px;color:#4f46e5;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;">👤 Administrateur Initial de l'Établissement</label>
                </div>

                <div>
                    <label class="label-modern">Nom de famille *</label>
                    <input type="text" name="admin_nom" class="input-modern" required placeholder="Dupont" value="{{ old('admin_nom') }}">
                </div>
                <div>
                    <label class="label-modern">Prénom *</label>
                    <input type="text" name="admin_prenom" class="input-modern" required placeholder="Jean" value="{{ old('admin_prenom') }}">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="label-modern">Email administrateur (identifiant de connexion) *</label>
                    <input type="email" name="admin_email" class="input-modern" required placeholder="admin@ecole.ma" value="{{ old('admin_email') }}">
                </div>
                <div>
                    <label class="label-modern">Mot de passe temporaire * (min. 10 caractères)</label>
                    <input type="password" name="admin_password" class="input-modern" required placeholder="••••••••••••" minlength="10">
                </div>
                <div>
                    <label class="label-modern">Confirmation du mot de passe *</label>
                    <input type="password" name="admin_password_confirmation" class="input-modern" required placeholder="••••••••••••" minlength="10">
                </div>
            </div>

            <div style="margin-top:24px;display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-modern-primary" style="height:48px;font-size:14.5px;">
                    <span>🚀 Provisionner et Déployer le Tenant</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </form>
    </div>

@endsection
