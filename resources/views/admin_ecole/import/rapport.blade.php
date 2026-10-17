@extends('layouts.admin_ecole')

@section('title', 'Rapport d\'Importation')
@section('page_title', 'Rapport : ' . $import->nom_fichier_original)

@push('styles')
<style>
    .kpi-report-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
</style>
@endpush

@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('admin_ecole.import.index') }}" class="btn-modern-ghost" style="padding:6px 14px;font-size:12.5px;">
            &larr; Retour à l'historique des imports
        </a>
    </div>

    <div class="kpi-report-row">
        <div class="metric-kpi-card emerald">
            <div class="metric-header">
                <span class="metric-title">Fiches Traitées &amp; Importées</span>
                <div class="metric-icon-wrap icon-emerald">✓</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#059669;">{{ $import->lignes_traitees }}</span>
                <span class="badge-status badge-success">Succès</span>
            </div>
        </div>

        <div class="metric-kpi-card rose">
            <div class="metric-header">
                <span class="metric-title">Lignes Ignorées / Erreurs</span>
                <div class="metric-icon-wrap icon-rose">✕</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:#dc2626;">{{ $import->lignes_ignorees }}</span>
                <span class="badge-status badge-danger">Anomalies</span>
            </div>
        </div>

        <div class="metric-kpi-card indigo">
            <div class="metric-header">
                <span class="metric-title">Date d'Exécution</span>
                <div class="metric-icon-wrap icon-indigo">⏱️</div>
            </div>
            <div class="metric-number-row">
                <span style="font-size:18px;font-weight:800;color:#0f172a;">
                    {{ $import->termine_le ? $import->termine_le->format('d/m/Y H:i') : 'En cours' }}
                </span>
            </div>
        </div>
    </div>

    @if(!empty($import->rapport_erreurs) && count($import->rapport_erreurs) > 0)
        <div class="modern-card" style="padding:0;overflow:hidden;border-color:#fecaca;">
            <div style="padding:18px 24px;background:#fef2f2;border-bottom:1px solid #fecaca;display:flex;align-items:center;gap:10px;">
                <span style="font-size:20px;">⚠️</span>
                <div>
                    <h3 style="margin:0;font-size:16px;color:#991b1b;font-weight:800;">Anomalies détectées lors du parsing CSV ({{ count($import->rapport_erreurs) }})</h3>
                    <p style="font-size:12.5px;color:#b91c1c;margin-top:2px;">Les lignes ci-dessous ont été ignorées sans interrompre le reste de la promotion.</p>
                </div>
            </div>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width:160px;">Ligne CSV</th>
                        <th>Détail de l'anomalie constatée</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($import->rapport_erreurs as $erreur)
                        <tr>
                            <td>
                                <span class="badge-tenant" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                                    Ligne #{{ $erreur['ligne'] ?? '?' }}
                                </span>
                            </td>
                            <td style="color:#b91c1c;font-size:13.5px;font-weight:500;">
                                {{ $erreur['erreur'] ?? 'Erreur indéterminée' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="modern-card" style="text-align:center;padding:48px;border-color:#a7f3d0;background:#f0fdf4;">
            <div style="font-size:42px;margin-bottom:12px;">🎉</div>
            <h3 style="font-size:18px;font-weight:800;color:#065f46;margin-bottom:4px;">Importation Parfaite Sans Aucune Anomalie</h3>
            <p style="font-size:14px;color:#047857;">Toutes les données de la promotion ont été enregistrées avec succès dans la base hermétique de l'école.</p>
        </div>
    @endif

@endsection
