@extends('layouts.superadmin')

@section('title', 'Annuaire des Étudiants — Supervision SaaS')
@section('page_title', 'Annuaire des Étudiants du Réseau')

@push('styles')
<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .page-head-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-main);
    }
    .filter-bar {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
    }
    .filter-form {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        flex: 1;
    }
    .select-school, .input-search {
        padding: 9px 14px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 13px;
        font-family: inherit;
        color: var(--text-main);
        outline: none;
        transition: all 0.15s;
    }
    .select-school:focus, .input-search:focus {
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .input-search {
        min-width: 260px;
        flex: 1;
    }
    .btn-filter {
        padding: 9px 16px;
        background: #0f172a;
        color: #ffffff;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-filter:hover { background: #1e293b; }
    .btn-reset {
        padding: 9px 14px;
        background: #f1f5f9;
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.15s;
    }
    .btn-reset:hover { background: #e2e8f0; color: var(--text-main); }

    .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .student-avatar-img {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        object-fit: cover;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        background: #f8fafc;
        flex-shrink: 0;
        display: block;
    }
    .student-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .matricule-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px;
        font-weight: 700;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .school-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #1e40af;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 3px 9px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s;
    }
    .school-badge:hover {
        background: #dbeafe;
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .pagination-bar {
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border);
        font-size: 12.5px;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-head-title">
            Annuaire des Étudiants
            @if($selectedEcole)
                <span style="font-size:16px;font-weight:600;color:var(--text-muted);">— {{ $selectedEcole->nom }}</span>
            @endif
        </h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Consultation centralisée de tous les étudiants enregistrés dans chaque établissement du SaaS.
        </p>
    </div>
</div>

<!-- Filtres et Recherche -->
<div class="filter-bar">
    <form action="{{ route('superadmin.etudiants.index') }}" method="GET" class="filter-form">
        <select name="ecole_id" class="select-school" onchange="this.form.submit()">
            <option value="">🏛️ Tous les établissements ({{ $ecoles->count() }})</option>
            @foreach($ecoles as $e)
                <option value="{{ $e->id }}" {{ request('ecole_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->nom }} ({{ $e->code_unique }})
                </option>
            @endforeach
        </select>

        <input type="text" name="recherche" class="input-search" placeholder="Rechercher par nom, prénom, matricule ou email..." value="{{ request('recherche') }}">

        <button type="submit" class="btn-filter">Rechercher</button>
        @if(request()->filled('ecole_id') || request()->filled('recherche'))
            <a href="{{ route('superadmin.etudiants.index') }}" class="btn-reset">Réinitialiser</a>
        @endif
    </form>
    <div style="font-size:12.5px;font-weight:700;color:var(--text-muted);">
        Total : <strong style="color:var(--text-main);">{{ $etudiants->total() }}</strong> étudiant(s) trouvé(s)
    </div>
</div>

<!-- Tableau des Étudiants -->
<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Établissement</th>
                <th>Matricule</th>
                <th>Filière &amp; Niveau</th>
                <th>Email Académique</th>
                <th>Demandes</th>
                <th>Statut</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($etudiants as $etudiant)
                @php
                    $scolaireKey = $etudiant->ecole_id . '_' . $etudiant->matricule;
                    $scolaire = $donneesScolaires[$scolaireKey] ?? null;
                    $initiales = strtoupper(substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1));
                @endphp
                <tr>
                    <td>
                        <div class="student-cell">
                            <img src="{{ $etudiant->photo_url }}" alt="Photo de {{ $etudiant->prenom }} {{ $etudiant->nom }}" class="student-avatar-img">
                            <div>
                                <div style="font-weight:700;color:var(--text-main);">{{ $etudiant->prenom }} {{ $etudiant->nom }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted);">Inscrit le {{ $etudiant->created_at ? $etudiant->created_at->format('d/m/Y') : '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($etudiant->ecole)
                            <a href="{{ route('superadmin.ecoles.show', $etudiant->ecole->id) }}" class="school-badge">
                                <span>🏛️ {{ $etudiant->ecole->sigle ?? $etudiant->ecole->code_unique }}</span>
                            </a>
                        @else
                            <span style="color:var(--text-muted);font-size:12px;">Non assigné</span>
                        @endif
                    </td>
                    <td>
                        <span class="matricule-badge">{{ $etudiant->matricule ?? '—' }}</span>
                    </td>
                    <td>
                        @if($scolaire)
                            <div style="font-weight:600;font-size:13px;color:var(--text-main);">{{ $scolaire->filiere ?? 'Filière générale' }}</div>
                            <div style="font-size:11.5px;color:var(--text-muted);">{{ $scolaire->niveau ?? 'Niveau standard' }} @if($scolaire->annee_universitaire) &bull; {{ $scolaire->annee_universitaire }} @endif</div>
                        @else
                            <span style="font-size:12px;color:var(--text-muted);">Données scolaires en attente</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:#334155;">{{ $etudiant->email }}</span>
                    </td>
                    <td>
                        <span style="font-weight:700;color:#0f172a;background:#f8fafc;padding:3px 8px;border-radius:6px;border:1px solid var(--border);font-size:12px;">
                            📄 {{ $etudiant->demandes_count }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status">● {{ ucfirst($etudiant->statut) }}</span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;align-items:center;">
                            <form action="{{ route('superadmin.impersonate.etudiant', $etudiant->id) }}" method="POST" style="display:inline;margin:0;">
                                @csrf
                                <button type="submit" class="school-badge" style="background:#0f172a;color:#ffffff;border-color:#0f172a;cursor:pointer;" title="Accès Master : Se connecter directement sur l'espace de cet étudiant">
                                    ⚡ Accéder
                                </button>
                            </form>
                            @if($etudiant->ecole)
                                <a href="{{ route('superadmin.ecoles.show', $etudiant->ecole->id) }}" class="school-badge" style="background:#ffffff;border-color:var(--border);color:var(--text-main);">
                                    👁️ Fiche école
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:48px 24px;color:var(--text-muted);">
                        <div style="font-size:32px;margin-bottom:8px;">🎓</div>
                        <div style="font-weight:700;color:var(--text-main);font-size:15px;">Aucun étudiant trouvé</div>
                        <div style="font-size:13px;margin-top:4px;">Modifiez vos critères de recherche ou sélectionnez un autre établissement.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-bar">
        <div>Affichage de {{ $etudiants->firstItem() ?? 0 }} à {{ $etudiants->lastItem() ?? 0 }} sur {{ $etudiants->total() ?? 0 }} étudiants</div>
        <div>{{ $etudiants->links() }}</div>
    </div>
</div>
@endsection
