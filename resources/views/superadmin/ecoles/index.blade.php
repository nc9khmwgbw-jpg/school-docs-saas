@extends('layouts.superadmin')

@section('title', 'Gestion des Établissements')
@section('page_title', 'Établissements Partenaires (Tenants)')

@push('styles')
<style>
    .super-head-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
</style>
@endpush

@section('content')

    <div class="super-head-actions">
        <div>
            <h1 style="font-size:22px;font-weight:800;letter-spacing:-0.03em;color:#0f172a;">Gestion des Établissements Partenaires</h1>
            <p style="font-size:13.5px;color:#64748b;margin-top:3px;">Supervision de tous les tenants isolés abonnés à la plateforme SaaS.</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:8px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:10px;padding:4px 14px;width:260px;">
                <span style="color:#94a3b8;">🔍</span>
                <input type="text" placeholder="Filtrer une école..." data-table-filter="ecoles-main-table" style="border:none;outline:none;font-size:13px;width:100%;font-family:inherit;">
            </div>
            <a href="{{ route('superadmin.ecoles.create') }}" class="btn-modern-primary" style="height:42px;font-size:13px;">
                <span>➕ Déployer une Nouvelle École</span>
            </a>
        </div>
    </div>

    <div class="modern-card" style="padding:0;overflow:hidden;">
        <div class="modern-table-wrap" style="box-shadow:none;border:none;">
            <table class="modern-table" id="ecoles-main-table">
                <thead>
                    <tr>
                        <th>Code Unique</th>
                        <th>Établissement</th>
                        <th>Contact</th>
                        <th>Utilisateurs Inscrits</th>
                        <th>Documents Scellés</th>
                        <th>Statut Licence</th>
                        <th style="text-align:right;">Actions Master</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ecoles as $ecole)
                        <tr>
                            <td>
                                <span class="badge-tenant">{{ $ecole->code_unique }}</span>
                            </td>
                            <td>
                                <a href="{{ route('superadmin.ecoles.show', $ecole->id) }}" style="font-weight:700;color:#0f172a;text-decoration:none;">
                                    {{ $ecole->nom }}
                                </a>
                                <div style="font-size:11.5px;color:#64748b;">{{ $ecole->adresse ?? 'Adresse non renseignée' }}</div>
                            </td>
                            <td style="font-size:12.5px;">
                                <div style="color:#0f172a;font-weight:500;">{{ $ecole->email_contact ?? '—' }}</div>
                                <div style="color:#64748b;font-size:11.5px;">{{ $ecole->telephone ?? '' }}</div>
                            </td>
                            <td>
                                <a href="{{ route('superadmin.etudiants.index', ['ecole_id' => $ecole->id]) }}" style="font-weight:700;color:#4f46e5;text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:#eef2ff;padding:4px 10px;border-radius:8px;border:1px solid #c7d2fe;font-size:12px;">
                                    <span>🎓 {{ $ecole->utilisateurs_count }} inscrits</span>
                                </a>
                            </td>
                            <td>
                                <span style="color:#059669;font-weight:800;font-size:14px;">{{ $ecole->documents_generes_count }}</span>
                            </td>
                            <td>
                                @if($ecole->statut === 'actif')
                                    <span class="badge-status badge-success">
                                        <span class="beacon-dot"></span>
                                        Actif
                                    </span>
                                @else
                                    <span class="badge-status badge-warning">
                                        <span class="beacon-dot"></span>
                                        Suspendu
                                    </span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;gap:6px;align-items:center;">
                                    <form action="{{ route('superadmin.impersonate.ecole', $ecole->id) }}" method="POST" style="display:inline;margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-modern-primary" style="padding:6px 11px;font-size:11.5px;background:#0f172a;box-shadow:none;" title="Piloter cette école sans mot de passe">
                                            ⚡ Contrôle
                                        </button>
                                    </form>
                                    <a href="{{ route('superadmin.ecoles.show', $ecole->id) }}" class="btn-modern-ghost" style="padding:6px 10px;font-size:11.5px;">Fiche</a>
                                    <a href="{{ route('tenant.portal', ['code_unique' => $ecole->code_unique]) }}" class="btn-modern-ghost" style="padding:6px 10px;font-size:11.5px;" target="_blank">Portail ↗</a>
                                    <form action="{{ route('superadmin.ecoles.toggle_statut', $ecole->id) }}" method="POST" style="display:inline;margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-modern-ghost" style="padding:6px 10px;font-size:11.5px;color:{{ $ecole->statut === 'actif' ? '#dc2626' : '#059669' }};">
                                            {{ $ecole->statut === 'actif' ? 'Suspendre' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;">
                                Aucun établissement inscrit pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#64748b;">
            <div>Affichage de {{ $ecoles->firstItem() ?? 0 }} à {{ $ecoles->lastItem() ?? 0 }} sur {{ $ecoles->total() ?? 0 }} écoles</div>
            <div>{{ $ecoles->links() }}</div>
        </div>
    </div>

@endsection
