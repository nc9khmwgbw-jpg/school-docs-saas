@extends('layouts.admin_ecole')

@section('title', 'Tableau de Bord')
@section('page_title')
    Vue d'Ensemble & <em>Pilotage.</em>
@endsection

@push('styles')
<style>
    /* ── Grille Bento KPIs Forma ── */
    .kpi-bento-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    /* ── Bento Layout 2 Colonnes ── */
    .dashboard-bento-split {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 24px;
        margin-bottom: 28px;
    }
    @media (max-width: 1100px) {
        .dashboard-bento-split {
            grid-template-columns: 1fr;
        }
    }

    /* ── Dropzone & Upload Style Forma ── */
    .file-dropzone-styled {
        border: 2px dashed var(--border-card);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        background: var(--canvas-subtle);
        cursor: pointer;
        transition: var(--transition-snappy);
    }
    .file-dropzone-styled:hover {
        border-color: var(--text-primary);
        background: rgba(0, 0, 0, 0.05);
    }
    [data-theme="dark"] .file-dropzone-styled:hover {
        background: rgba(255, 255, 255, 0.06);
    }

    .card-kicker-mono {
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
</style>
@endpush

@section('content')

    <!-- 5 KPIs Métriques Haute Définition Forma -->
    <div class="kpi-bento-grid">
        <div class="metric-kpi-card">
            <div class="metric-header">
                <span class="metric-title">Modèles Actifs</span>
                <div class="metric-icon-wrap">📄</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $stats['total_modeles'] }}</span>
                <span class="metric-trend-badge">Conformes</span>
            </div>
        </div>

        <div class="metric-kpi-card">
            <div class="metric-header">
                <span class="metric-title">Étudiants Inscrits</span>
                <div class="metric-icon-wrap">👥</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number">{{ $stats['total_etudiants'] }}</span>
                <span class="metric-trend-badge">Actifs</span>
            </div>
        </div>

        <div class="metric-kpi-card">
            <div class="metric-header">
                <span class="metric-title">En Attente Validation</span>
                <div class="metric-icon-wrap">⏳</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:var(--brand-amber);">{{ $stats['demandes_attente'] }}</span>
                <span class="badge-status badge-warning">À traiter</span>
            </div>
        </div>

        <div class="metric-kpi-card">
            <div class="metric-header">
                <span class="metric-title">Documents Scellés</span>
                <div class="metric-icon-wrap">🛡️</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:var(--brand-emerald);">{{ $stats['demandes_terminees'] }}</span>
                <span class="badge-status badge-success">100% QR</span>
            </div>
        </div>

        <div class="metric-kpi-card">
            <div class="metric-header">
                <span class="metric-title">Demandes Rejetées</span>
                <div class="metric-icon-wrap">✕</div>
            </div>
            <div class="metric-number-row">
                <span class="metric-big-number" style="color:var(--brand-rose);">{{ $stats['demandes_refusees'] }}</span>
                <span class="badge-status badge-danger">Historique</span>
            </div>
        </div>
    </div>

    <!-- Bento Split : File de Traitement & Actions Rapides -->
    <div class="dashboard-bento-split">

        <!-- Colonne Gauche : Dernières Demandes Récentes -->
        <div class="modern-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <div>
                    <div class="card-kicker-mono">— GESTION SCOLARITÉ • FLUX EN DIRECT</div>
                    <h3 style="font-size:18px;font-weight:900;letter-spacing:-0.03em;color:var(--text-primary);">Demandes Étudiantes <em>Récentes</em></h3>
                    <p style="font-size:13px;color:var(--text-secondary);margin-top:2px;">File de validation et d'émission de documents officiels scellés.</p>
                </div>
                <a href="{{ route('admin_ecole.demandes.index') }}" class="btn-modern-ghost" style="font-size:12px;padding:6px 14px;">
                    Toutes les demandes &rarr;
                </a>
            </div>

            <div class="modern-table-wrap" style="box-shadow:none;border-color:var(--border-light);">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Étudiant</th>
                            <th>Pièce Demandée</th>
                            <th>Statut</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresDemandes as $demande)
                            <tr>
                                <td style="font-family:var(--font-mono);font-size:11.5px;color:var(--text-muted);">
                                    {{ $demande->created_at->format('d/m H:i') }}
                                </td>
                                <td>
                                    <div style="font-weight:700;color:var(--text-primary);">{{ $demande->etudiant->prenom ?? '' }} {{ $demande->etudiant->nom ?? '' }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);font-family:var(--font-mono);">{{ $demande->etudiant->matricule ?? '' }}</div>
                                </td>
                                <td>
                                    <span style="font-weight:600;color:var(--text-secondary);">{{ $demande->modele->titre ?? 'Document' }}</span>
                                </td>
                                <td>
                                    @if($demande->statut === 'en_attente')
                                        <span class="badge-status badge-warning">
                                            <span class="beacon-dot"></span>
                                            En attente
                                        </span>
                                    @elseif($demande->statut === 'genere')
                                        <span class="badge-status badge-success">
                                            <span class="beacon-dot"></span>
                                            Scellé
                                        </span>
                                    @elseif($demande->statut === 'refuse')
                                        <span class="badge-status badge-danger">
                                            <span class="beacon-dot"></span>
                                            Refusé
                                        </span>
                                    @else
                                        <span class="badge-status">{{ $demande->statut }}</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('admin_ecole.demandes.index') }}" class="btn-modern-ghost" style="padding:4px 12px;font-size:11.5px;">
                                        Traiter &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:36px;color:var(--text-muted);font-size:13px;">
                                    Aucune demande récente pour cet établissement.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne Droite : Inscription Rapide & Import CSV -->
        <div style="display:flex;flex-direction:column;gap:24px;">

            <!-- Inscription Manuelle -->
            <div class="modern-card">
                <div style="margin-bottom:16px;">
                    <div class="card-kicker-mono">— AJOUT INDIVIDUEL</div>
                    <h3 style="font-size:17px;font-weight:900;letter-spacing:-0.03em;color:var(--text-primary);">Inscription Rapide <em>d'Étudiant</em></h3>
                    <p style="font-size:12.5px;color:var(--text-secondary);">Ajoutez un étudiant instantanément au registre officiel.</p>
                </div>

                <form action="{{ route('admin_ecole.etudiants.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="label-modern">Nom</label>
                            <input type="text" name="nom" class="input-modern" placeholder="Ex: Bennani" required style="height:42px;">
                        </div>
                        <div>
                            <label class="label-modern">Prénom</label>
                            <input type="text" name="prenom" class="input-modern" placeholder="Ex: Yasmine" required style="height:42px;">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="label-modern">Matricule</label>
                            <input type="text" name="matricule" class="input-modern text-mono" placeholder="E2026-100" required style="height:42px;">
                        </div>
                        <div>
                            <label class="label-modern">Email</label>
                            <input type="email" name="email" class="input-modern" placeholder="y.bennani@ecole.ma" required style="height:42px;">
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label class="label-modern">Photo d'Identité (JPG, PNG)</label>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:4px;">
                            <div id="quick-avatar-preview" style="width:42px;height:42px;border-radius:9999px;background:var(--canvas-subtle);border:1px solid var(--hairline-strong);display:flex;align-items:center;justify-content:center;font-size:18px;overflow:hidden;flex-shrink:0;">
                                👤
                            </div>
                            <input type="file" name="photo" id="student_photo_input" accept="image/*" class="input-modern" style="padding-top:8px;height:42px;font-size:12px;" onchange="
                                const file = this.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        const prev = document.getElementById('quick-avatar-preview');
                                        prev.innerHTML = `<img src='${e.target.result}' style='width:100%;height:100%;object-fit:cover;'>`;
                                    };
                                    reader.readAsDataURL(file);
                                }
                            ">
                        </div>
                    </div>

                    <button type="submit" class="btn-modern-primary" style="width:100%;height:44px;">
                        <span>+ Enregistrer l'étudiant</span>
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </form>
            </div>

            <!-- Import CSV Rapide -->
            <div class="modern-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div>
                        <div class="card-kicker-mono">— IMPORT EN MASSE</div>
                        <h3 style="font-size:17px;font-weight:900;letter-spacing:-0.03em;color:var(--text-primary);">Import de Promotion <em>(CSV)</em></h3>
                        <p style="font-size:12.5px;color:var(--text-secondary);">Importez jusqu'à 5 000 étudiants en un seul clic.</p>
                    </div>
                    <a href="{{ route('admin_ecole.import.index') }}" class="btn-modern-ghost" style="font-size:11px;padding:4px 10px;">Historique &rarr;</a>
                </div>

                <form action="{{ route('admin_ecole.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="file-dropzone-styled" onclick="document.getElementById('fichier_csv_dash').click()">
                        <div style="font-size:24px;margin-bottom:6px;">📥</div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-primary);">Sélectionnez votre fichier CSV</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;font-family:var(--font-mono);">Format UTF-8 séparateur virgule ou point-virgule</div>
                        <input type="file" id="fichier_csv_dash" name="fichier_csv" accept=".csv,.txt" style="display:none;" required onchange="this.parentElement.querySelector('div:nth-child(2)').textContent = this.files[0]?.name || 'Fichier sélectionné'">
                    </div>

                    <button type="submit" class="btn-modern-primary" style="width:100%;height:44px;margin-top:14px;">
                        <span>Lancer l'importation de la promotion &rarr;</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

@endsection
