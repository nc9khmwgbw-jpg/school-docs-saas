@extends('layouts.admin_ecole')

@section('title', 'Journal d\'Audit')
@section('page_title', 'Journal d\'Audit & Traçabilité')

@push('styles')
<style>
    .header-actions { margin-bottom: 24px; }
    .page-title { font-size: 20px; font-weight: 700; color: var(--text-main); }

    .badge-action {
        font-family: var(--font-mono);
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 10px;
        background: var(--canvas-subtle);
        color: var(--ink-primary);
        border: 1px solid var(--hairline-strong);
        border-radius: 9999px;
        display: inline-block;
        letter-spacing: 0.04em;
    }
    .ip-badge {
        font-family: var(--font-mono);
        font-size: 11px;
        color: var(--ink-muted);
    }
</style>
@endpush

@section('content')

    <div class="header-actions">
        <h1 class="page-title">Piste d'Audit de Sécurité</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px;">Journal immuable des actions et accès aux documents scolaires (Conforme Loi 09-08 / CNDP)</p>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Horodatage</th>
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
                        <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;font-family:'JetBrains Mono',monospace;">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            @if($log->utilisateur)
                                <div style="font-weight:700;">{{ $log->utilisateur->prenom }} {{ $log->utilisateur->nom }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $log->utilisateur->email }}</div>
                            @else
                                <span style="color:var(--text-muted);font-style:italic;">Système / Public</span>
                            @endif
                        </td>
                        <td><span class="badge-action">{{ $log->action }}</span></td>
                        <td style="font-size:12.5px;color:var(--text-main);">
                            @if($log->cible_type)
                                {{ class_basename($log->cible_type) }} #{{ $log->cible_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="ip-badge">{{ $log->ip_address ?? '—' }}</span></td>
                        <td style="font-size:11.5px;color:var(--text-muted);max-width:280px;">
                            @if(!empty($log->details))
                                <span style="font-family:'JetBrains Mono',monospace;">{{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted);">
                            Aucune entrée dans le journal d'audit pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:var(--text-muted);">
                <div>Affichage de {{ $logs->firstItem() ?? 0 }} à {{ $logs->lastItem() ?? 0 }} sur {{ $logs->total() ?? 0 }} logs</div>
                <div>{{ $logs->links() }}</div>
            </div>
        @endif
    </div>

@endsection
