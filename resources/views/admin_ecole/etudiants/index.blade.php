@extends('layouts.admin_ecole')

@section('title', 'Gestion des Étudiants — ' . $ecole->nom)
@section('page_title', 'Annuaire des Étudiants de l\'Établissement')

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
    .input-search {
        padding: 9px 14px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 13px;
        font-family: inherit;
        color: var(--text-main);
        outline: none;
        transition: all 0.15s;
        min-width: 280px;
        flex: 1;
    }
    .input-search:focus {
        border-color: var(--ink-primary);
        background: var(--canvas-card-solid);
        box-shadow: 0 0 0 3px rgba(20, 20, 20, 0.08);
    }
    .btn-filter {
        padding: 9px 18px;
        background: var(--ink-primary);
        color: var(--canvas-bg);
        border: none;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-filter:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-reset {
        padding: 9px 16px;
        background: var(--canvas-subtle);
        color: var(--ink-muted);
        text-decoration: none;
        border-radius: 9999px;
        border: 1px solid var(--hairline-strong);
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-reset:hover { color: var(--ink-primary); border-color: var(--ink-primary); }
    .btn-primary-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--ink-primary);
        color: var(--canvas-bg);
        text-decoration: none;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-primary-action:hover {
        transform: translateY(-1px);
        opacity: 0.92;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.16);
    }

    .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 9999px;
        background: var(--ink-primary);
        color: var(--canvas-bg);
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
        <h1 class="page-head-title">Gestion des Étudiants — {{ $ecole->nom }}</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
            Consultation, recherche et suivi de l'ensemble des étudiants inscrits dans votre établissement.
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('admin_ecole.import.index') }}" class="btn-primary-action">
            <span>➕ Importer / Ajouter des étudiants</span>
        </a>
    </div>
</div>

<!-- Filtres et Recherche -->
<div class="filter-bar">
    <form action="{{ route('admin_ecole.etudiants.index') }}" method="GET" class="filter-form">
        <input type="text" name="recherche" class="input-search" placeholder="Rechercher par nom, prénom, matricule ou email..." value="{{ request('recherche') }}">
        <button type="submit" class="btn-filter">Rechercher</button>
        @if(request()->filled('recherche'))
            <a href="{{ route('admin_ecole.etudiants.index') }}" class="btn-reset">Réinitialiser</a>
        @endif
    </form>
    <div style="font-size:12.5px;font-weight:700;color:var(--text-muted);">
        Total : <strong style="color:var(--text-main);">{{ $etudiants->total() }}</strong> étudiant(s) inscrit(s)
    </div>
</div>

<!-- Tableau des Étudiants -->
<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>Étudiant</th>
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
                    $scolaire = $donneesScolaires[$etudiant->matricule] ?? null;
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
                            <form action="{{ route('admin_ecole.etudiants.reinitialiser_mdp', $etudiant->id) }}" method="POST" onsubmit="return confirm('Réinitialiser le mot de passe de {{ $etudiant->prenom }} {{ $etudiant->nom }} au mot de passe temporaire ? L\'étudiant sera obligé de le changer à sa prochaine connexion.');" style="margin:0;display:inline;">
                                @csrf
                                <button type="submit" class="btn-modern-ghost" style="padding:4px 10px;font-size:11.5px;color:#d97706;border-color:rgba(217,119,6,0.3);" title="Réinitialiser le mot de passe de cet étudiant">
                                    🔑 Réinitialiser MDP
                                </button>
                            </form>
                            <a href="{{ route('admin_ecole.demandes.index', ['recherche' => $etudiant->matricule]) }}" class="btn-modern-ghost" style="padding:4px 12px;font-size:11.5px;">
                                📋 Voir demandes &rarr;
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px 24px;color:var(--text-muted);">
                        <div style="font-size:32px;margin-bottom:8px;">🎓</div>
                        <div style="font-weight:700;color:var(--text-main);font-size:15px;">Aucun étudiant trouvé</div>
                        <div style="font-size:13px;margin-top:4px;">Utilisez le module d'import pour importer votre liste d'étudiants par fichier Excel/CSV ou par ajout manuel.</div>
                        <div style="margin-top:16px;">
                            <a href="{{ route('admin_ecole.import.index') }}" class="btn-primary-action" style="display:inline-flex;">
                                📥 Aller à l'import d'étudiants
                            </a>
                        </div>
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
