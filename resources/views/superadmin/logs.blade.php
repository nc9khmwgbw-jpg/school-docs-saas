@extends('layouts.superadmin')

@section('title', 'Journal d\'Audit Global')
@section('page_title', 'Journal d\'Audit Plateforme (Conformité & Traçabilité)')

@push('styles')
<style>
    .header-actions {
        margin-bottom: 24px;
    }
    .page-head-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-main);
    }
    .badge-action {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        color: #0b3b2a;
        background: rgba(11, 59, 42, 0.08);
        padding: 4px 8px;
        border-radius: 6px;
    }
    .school-pill {
        font-size: 11px;
        font-weight: 700;
        color: #0b3b2a;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
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
    <h1 class="page-head-title">Piste d'Audit Multi-Établissements</h1>
    <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">Traçabilité intégrale des connexions, modifications de statuts, générations et téléchargements</p>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>Horodatage</th>
                <th>Établissement (Tenant)</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Élément ciblé</th>
                <th>Adresse IP</th>
                <th>Détails</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @if($log->ecole)
                            <span class="school-pill">{{ $log->ecole->nom }}</span>
                        @else
                            <span style="font-size:11px;color:var(--text-muted);font-style:italic;">Plateforme Globale</span>
                        @endif
                    </td>
                    <td>
                        @if($log->utilisateur)
                            <div style="font-weight:700;color:var(--text-main);">{{ $log->utilisateur->prenom }} {{ $log->utilisateur->nom }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $log->utilisateur->email }} ({{ $log->utilisateur->role }})</div>
                        @else
                            <span style="color:var(--text-muted);font-style:italic;">Système / Public</span>
                        @endif
                    </td>
                    <td><span class="badge-action">{{ $log->action }}</span></td>
                    <td style="font-size:12px;">
                        @if($log->cible_type)
                            <span style="font-family:'JetBrains Mono',monospace;font-size:11px;">{{ class_basename($log->cible_type) }} #{{ $log->cible_id }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:11.5px;color:var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                    <td style="font-size:11.5px;color:var(--text-muted);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        @if(!empty($log->details))
                            <span style="font-family:'JetBrains Mono',monospace;">{{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                        Aucune entrée dans le journal d'audit.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-bar">
        <div>Affichage de {{ $logs->firstItem() ?? 0 }} à {{ $logs->lastItem() ?? 0 }} sur {{ $logs->total() ?? 0 }} logs</div>
        <div>{{ $logs->links() }}</div>
    </div>
</div>
@endsection
