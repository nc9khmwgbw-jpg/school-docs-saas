@extends('layouts.admin_ecole')

@section('title', 'Modèles de Documents')
@section('page_title', 'Modèles de Documents Officiels')

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
    .page-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
    }
    .badge {
        font-family: var(--font-mono);
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 9999px;
        display: inline-block;
        letter-spacing: 0.04em;
    }
    .badge-blue  { background: var(--canvas-subtle); color: var(--ink-primary); border: 1px solid var(--hairline-strong); }
    .badge-amber { background: rgba(217, 119, 6, 0.1); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.25); }
    .badge-green { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); }
    .badge-slate { background: var(--canvas-subtle); color: var(--ink-secondary); border: 1px solid var(--hairline-strong); }

    .action-links {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
    }
    .btn-action {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-main);
        background: #fbfbfa;
        border: 1px solid var(--border);
        transition: all 0.2s;
    }
    .btn-action:hover {
        background: #f0f3f1;
        border-color: #0b3b2a;
    }
    .btn-action.delete {
        color: #dc2626;
        cursor: pointer;
    }
    .btn-action.delete:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }
</style>
@endpush

@section('content')

    <div class="header-actions">
        <div>
            <h1 class="page-title">Modèles Configurés</h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px;">Gérez les types de documents délivrables à vos étudiants avec templates HTML dynamiques</p>
        </div>
        <a href="{{ route('admin_ecole.modeles.create') }}" class="btn-primary-action">
            <span>➕ Créer un modèle</span>
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Titre du Modèle</th>
                    <th>Type</th>
                    <th>Validation</th>
                    <th>Langue</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modeles as $modele)
                    <tr>
                        <td>
                            <div style="font-weight:700;">{{ $modele->titre }}</div>
                            @if($modele->description)
                                <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">{{ Str::limit($modele->description, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-blue">{{ ucfirst(str_replace('_', ' ', $modele->type_document)) }}</span>
                        </td>
                        <td>
                            @if($modele->validation_requise)
                                <span class="badge badge-amber">Manuelle requise</span>
                            @else
                                <span class="badge badge-green">Automatique</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-slate">{{ strtoupper($modele->langue) }}</span>
                        </td>
                        <td>
                            @if($modele->expire_dans_jours)
                                <span style="font-size:12.5px;color:var(--text-muted);">{{ $modele->expire_dans_jours }} jours</span>
                            @else
                                <span style="font-size:12.5px;color:var(--text-muted);">Illimitée</span>
                            @endif
                        </td>
                        <td>
                            @if($modele->actif)
                                <span class="badge badge-green">● Actif</span>
                            @else
                                <span class="badge badge-slate">○ Inactif</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div class="action-links">
                                <a href="{{ route('admin_ecole.modeles.apercu', $modele->id) }}" class="btn-action" title="Aperçu du modèle" target="_blank">👁️ Aperçu</a>
                                <a href="{{ route('admin_ecole.modeles.edit', $modele->id) }}" class="btn-action" title="Modifier le modèle">✏️ Modifier</a>
                                <form action="{{ route('admin_ecole.modeles.destroy', $modele->id) }}" method="POST" onsubmit="return confirm('Supprimer ce modèle de document ?');" style="display:inline;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted);">
                            Aucun modèle de document configuré. Cliquez sur « Créer un modèle » pour démarrer.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
