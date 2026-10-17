@extends('layouts.superadmin')

@section('title', $ecole->nom . ' — Fiche Établissement')
@section('page_title', 'Fiche Établissement : ' . $ecole->nom)

@push('styles')
<style>
    .super-show-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .kpi-ecole-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .grid-details-layout {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 960px) {
        .grid-details-layout { grid-template-columns: 1fr; }
    }
    .info-row-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
    }
    .info-row-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

    <div style="margin-bottom:18px;">
        <a href="{{ route('superadmin.ecoles.index') }}" class="btn-modern-ghost" style="padding:6px 14px;font-size:12.5px;">
            &larr; Retour à l'annuaire des établissements
        </a>
    </div>

    <!-- Header Actions -->
    <div class="super-show-header">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            @if($ecole->logo_url)
                <img src="{{ $ecole->logo_url }}" alt="Logo {{ $ecole->nom }}" style="height:42px;width:auto;max-width:140px;object-fit:contain;">
            @endif
            <h1 style="font-size:24px;font-weight:800;letter-spacing:-0.03em;color:#0f172a;margin:0;">
                {{ $ecole->nom }}
            </h1>
            @if($ecole->statut === 'actif')
                <span class="badge-status badge-success">
                    <span class="beacon-dot"></span>
                    Tenant Actif
                </span>
            @else
                <span class="badge-status badge-warning">
                    <span class="beacon-dot"></span>
                    Tenant Suspendu
                </span>
            @endif
        </div>

        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <form action="{{ route('superadmin.impersonate.ecole', $ecole->id) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-modern-primary" style="background:#0f172a;height:40px;font-size:12.5px;">
                    ⚡ Prendre le Contrôle (Admin)
                </button>
            </form>
            <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-modern-ghost" style="height:40px;font-size:12.5px;" target="_blank">
                🔗 Portail Public ↗
            </a>
            <form action="{{ route('superadmin.ecoles.toggle_statut', $ecole->id) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-modern-ghost" style="height:40px;font-size:12.5px;color:{{ $ecole->statut === 'actif' ? '#dc2626' : '#059669' }};">
                    {{ $ecole->statut === 'actif' ? 'Suspendre l\'accès' : 'Réactiver l\'accès' }}
                </button>
            </form>
        </div>
    </div>

    <!-- KPIs Établissement -->
    <div class="kpi-ecole-grid">
        <div class="metric-kpi-card indigo">
            <div class="metric-header">
                <span class="metric-title">Étudiants Inscrits</span>
                <div class="metric-icon-wrap icon-indigo">🎓</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $etudiants->count() }}</span>
            </div>
        </div>

        <div class="metric-kpi-card emerald">
            <div class="metric-header">
                <span class="metric-title">Administrateurs</span>
                <div class="metric-icon-wrap icon-emerald">👔</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $adminsEcole->count() }}</span>
            </div>
        </div>

        <div class="metric-kpi-card amber">
            <div class="metric-header">
                <span class="metric-title">Modèles de Docs</span>
                <div class="metric-icon-wrap icon-amber">📄</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $ecole->modeles_documents_count }}</span>
            </div>
        </div>

        <div class="metric-kpi-card emerald">
            <div class="metric-header">
                <span class="metric-title">Documents Certifiés</span>
                <div class="metric-icon-wrap icon-emerald">🛡️</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#059669;">{{ $statsEcole['docs_generes'] }}</span>
            </div>
        </div>

        <div class="metric-kpi-card rose">
            <div class="metric-header">
                <span class="metric-title">Demandes du Mois</span>
                <div class="metric-icon-wrap icon-rose">📈</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $statsEcole['demandes_ce_mois'] }}</span>
            </div>
        </div>
    </div>

    <!-- Détails Coordonnées & Administrateurs -->
    <div class="grid-details-layout">
        <div class="modern-card">
            <h3 style="font-size:16px;font-weight:800;letter-spacing:-0.02em;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;color:#0f172a;">
                🏢 Spécifications Tenant
            </h3>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">Code unique portail</span>
                <span class="badge-tenant">{{ $ecole->code_unique }}</span>
            </div>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">Email de contact</span>
                <span style="font-weight:700;color:#0f172a;">{{ $ecole->email_contact ?? '—' }}</span>
            </div>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">Téléphone</span>
                <span style="font-weight:700;color:#0f172a;">{{ $ecole->telephone ?? '—' }}</span>
            </div>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">Adresse</span>
                <span style="font-weight:700;color:#0f172a;text-align:right;">{{ $ecole->adresse ?? '—' }}</span>
            </div>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">UUID d'isolation</span>
                <span style="font-family:var(--font-mono);font-size:11px;color:#64748b;">{{ $ecole->uuid }}</span>
            </div>
            <div class="info-row-item">
                <span style="color:#64748b;font-weight:600;">Date de création</span>
                <span style="font-weight:700;color:#0f172a;">{{ $ecole->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="modern-card" style="padding:0;overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:16px;font-weight:800;letter-spacing:-0.02em;color:#0f172a;margin:0;">
                    👔 Administrateurs de l'Établissement ({{ $adminsEcole->count() }})
                </h3>
            </div>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Administrateur</th>
                        <th>Email Officiel</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminsEcole as $admin)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="user-avatar-badge" style="width:30px;height:30px;font-size:12px;background:#0f172a;">
                                        {{ strtoupper(substr($admin->prenom, 0, 1) . substr($admin->nom, 0, 1)) }}
                                    </div>
                                    <div style="font-weight:700;color:#0f172a;">{{ $admin->prenom }} {{ $admin->nom }}</div>
                                </div>
                            </td>
                            <td><span style="font-family:var(--font-mono);font-size:12px;color:#334155;">{{ $admin->email }}</span></td>
                            <td><span style="font-size:11.5px;font-weight:600;color:#64748b;">Admin École</span></td>
                            <td>
                                <span class="badge-status badge-success" style="font-size:10.5px;">
                                    <span class="beacon-dot"></span>
                                    Actif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;">
                                Aucun administrateur assigné pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste Complète des Étudiants de l'École -->
    <div class="modern-card" style="padding:0;overflow:hidden;margin-bottom:24px;">
        <div style="padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <h3 style="font-size:16px;font-weight:800;letter-spacing:-0.02em;color:#0f172a;margin:0;">
                🎓 Étudiants Rattachés à cet Établissement ({{ $etudiants->count() }})
            </h3>
            <a href="{{ route('superadmin.etudiants.index', ['ecole_id' => $ecole->id]) }}" class="btn-modern-ghost" style="padding:5px 12px;font-size:12px;">
                Annuaire Complet &rarr;
            </a>
        </div>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Matricule</th>
                    <th>Filière &amp; Niveau</th>
                    <th>Email Académique</th>
                    <th>Demandes</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Contrôle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($etudiants as $etudiant)
                    @php
                        $scolaire = $donneesScolaires[$etudiant->matricule] ?? null;
                        $initiales = strtoupper(substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1));
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="{{ $etudiant->photo_url }}" alt="Photo de {{ $etudiant->prenom }} {{ $etudiant->nom }}" style="width:32px;height:32px;border-radius:8px;object-fit:cover;border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.06);flex-shrink:0;">
                                <div>
                                    <div style="font-weight:700;color:#0f172a;">{{ $etudiant->prenom }} {{ $etudiant->nom }}</div>
                                    <div style="font-size:11px;color:#64748b;">Inscrit le {{ $etudiant->created_at ? $etudiant->created_at->format('d/m/Y') : '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-tenant">{{ $etudiant->matricule ?? '—' }}</span>
                        </td>
                        <td>
                            @if($scolaire)
                                <div style="font-weight:600;font-size:13px;color:#0f172a;">{{ $scolaire->filiere ?? 'Filière générale' }}</div>
                                <div style="font-size:11.5px;color:#64748b;">{{ $scolaire->niveau ?? 'Niveau standard' }} @if($scolaire->annee_universitaire) &bull; {{ $scolaire->annee_universitaire }} @endif</div>
                            @else
                                <span style="font-size:12px;color:#94a3b8;">Données en attente</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:var(--font-mono);font-size:12px;color:#334155;">{{ $etudiant->email }}</span>
                        </td>
                        <td>
                            <span style="font-weight:700;color:#0f172a;background:#f8fafc;padding:3px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;">
                                📄 {{ $etudiant->demandes_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status badge-success">
                                <span class="beacon-dot"></span>
                                {{ ucfirst($etudiant->statut) }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <form action="{{ route('superadmin.impersonate.etudiant', $etudiant->id) }}" method="POST" style="display:inline;margin:0;">
                                @csrf
                                <button type="submit" class="btn-modern-ghost" style="font-size:11.5px;padding:4px 10px;color:#4f46e5;border-color:#c7d2fe;background:#eef2ff;">
                                    👤 Prendre la main
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:36px;color:#94a3b8;">
                            Aucun étudiant inscrit dans cet établissement pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
