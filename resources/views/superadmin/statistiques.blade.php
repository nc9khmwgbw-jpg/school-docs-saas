@extends('layouts.superadmin')

@section('title', 'Statistiques SaaS')
@section('page_title', 'Statistiques & Volumétrie Plateforme')

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
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 4px 20px rgba(16, 38, 29, 0.03);
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 34px;
        font-weight: 800;
        letter-spacing: -0.04em;
        color: var(--text-main);
    }
    .kpi-value.emerald { color: #0b3b2a; }

    .code-pill {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        background: rgba(11, 59, 42, 0.08);
        color: #0b3b2a;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .progress-bar {
        width: 100%;
        height: 7px;
        background: rgba(16, 38, 29, 0.08);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 6px;
    }
    .progress-fill {
        height: 100%;
        background: #0b3b2a;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="header-actions">
    <h1 class="page-head-title">Performances & Utilisation Globale</h1>
    <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">Suivi de l'adoption par établissement, des générations de PDF et de l'activité des étudiants</p>
</div>

@php
    $totalDocs = $statsParEcole->sum('documents_generes_count');
    $totalDemandes = $statsParEcole->sum('demandes_documents_count');
    $totalEtudiants = $statsParEcole->sum('etudiants_count');
    $maxDocs = max(1, $statsParEcole->max('documents_generes_count'));
@endphp

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total Documents PDF Générés</div>
        <div class="kpi-value emerald">{{ number_format($totalDocs, 0, ',', ' ') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Demandes Traitées</div>
        <div class="kpi-value">{{ number_format($totalDemandes, 0, ',', ' ') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Comptes Étudiants Actifs</div>
        <div class="kpi-value emerald">{{ number_format($totalEtudiants, 0, ',', ' ') }}</div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);">
        <h2 style="font-size:16px;font-weight:800;letter-spacing:-0.02em;margin:0;">Classement des Établissements par Volume de Documents</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Établissement</th>
                <th>Étudiants inscrits</th>
                <th>Demandes reçues</th>
                <th>Documents générés</th>
                <th style="width:220px;">Part du volume total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statsParEcole as $stat)
                @php
                    $percent = $maxDocs > 0 ? round(($stat->documents_generes_count / $maxDocs) * 100) : 0;
                @endphp
                <tr>
                    <td><span class="code-pill">{{ $stat->code_unique }}</span></td>
                    <td style="font-weight:700;color:var(--text-main);">{{ $stat->nom }}</td>
                    <td>{{ number_format($stat->etudiants_count, 0, ',', ' ') }}</td>
                    <td>{{ number_format($stat->demandes_documents_count, 0, ',', ' ') }}</td>
                    <td><span style="color:#0b3b2a;font-weight:800;">{{ number_format($stat->documents_generes_count, 0, ',', ' ') }}</span></td>
                    <td>
                        <div style="font-size:11px;color:var(--text-muted);display:flex;justify-content:space-between;font-weight:600;">
                            <span>{{ $percent }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percent }}%;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                        Aucune donnée statistique disponible pour le moment.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
