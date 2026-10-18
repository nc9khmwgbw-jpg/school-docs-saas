@extends('layouts.admin_ecole')

@section('title', 'Importation des Données Scolaires')
@section('page_title', 'Importation des Données Étudiantes')

@push('styles')
<style>
    .header-actions { margin-bottom: 24px; }
    .page-title { font-size: 20px; font-weight: 700; color: var(--text-main); }

    .grid-import {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) { .grid-import { grid-template-columns: 1fr; } }

    .drop-zone {
        border: 2px dashed rgba(16, 38, 29, 0.2);
        border-radius: 14px;
        padding: 32px 20px;
        text-align: center;
        background: #fbfbfa;
        margin-bottom: 16px;
        position: relative;
    }
    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .btn-upload {
        width: 100%;
        padding: 12px;
        background: #0b3b2a;
        color: #ffffff;
        border: 0;
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-upload:hover { background: #144935; }

    .code-pill {
        font-family: 'JetBrains Mono', monospace;
        background: #f0f3f1;
        color: #0b3b2a;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11.5px;
    }

    .btn-sample {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: #0b3b2a;
        text-decoration: underline;
    }

    .form-input-sm {
        width: 100%;
        height: 42px;
        padding: 0 12px;
        background: #fbfbfa;
        border: 1px solid rgba(16, 38, 29, 0.18);
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        color: var(--text-main);
        outline: none;
    }
    .form-input-sm:focus { border-color: #0b3b2a; }

    .badge-green { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    .badge-amber { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    .badge-red   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
</style>
@endpush

@section('content')

    <div class="header-actions">
        <h1 class="page-title">Importation des Données Étudiantes</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px;">Importez en masse les données de vos étudiants et notes depuis un fichier CSV</p>
    </div>

    <div class="grid-import">
        <!-- Upload -->
        <div class="card">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">Téléverser un fichier CSV</h3>
            <form action="{{ route('admin_ecole.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="drop-zone">
                    <div style="font-size:32px;margin-bottom:8px;">📁</div>
                    <div style="font-size:14px;font-weight:700;">Cliquez ou glissez votre fichier .csv</div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">Encodage UTF-8 (Max 10 Mo)</div>
                    <input type="file" name="fichier_csv" accept=".csv,text/csv,text/plain" required>
                </div>
                <button type="submit" class="btn-upload">🚀 Lancer l'importation</button>
            </form>
        </div>

        <!-- Format Guide -->
        <div class="card">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">Format attendu du fichier CSV</h3>
            <div style="font-size:13px;color:var(--text-muted);line-height:1.6;">
                <p style="margin-bottom:10px;">Le fichier doit comporter une ligne d'en-tête avec les colonnes administratives :</p>
                <ul style="padding-left:18px;margin-bottom:12px;">
                    <li><span class="code-pill">matricule</span> <strong>(obligatoire)</strong> : identifiant unique (ex: E2026-1001)</li>
                    <li><span class="code-pill">nom</span> &amp; <span class="code-pill">prenom</span> : état civil de l'étudiant</li>
                    <li><span class="code-pill">email</span> : adresse email pour l'envoi des documents et accès</li>
                    <li><span class="code-pill">filiere</span> : filière ou spécialité (ex: Génie Informatique)</li>
                    <li><span class="code-pill">niveau</span> : niveau d'études (ex: Master 2, 3ème Année...)</li>
                    <li><span class="code-pill">annee_universitaire</span> : session scolaire (ex: 2025-2026)</li>
                    <li><span class="code-pill">date_naissance</span>, <span class="code-pill">lieu_naissance</span>, <span class="code-pill">cin</span>, <span class="code-pill">cne</span> : données d'état civil (reprises sur les attestations)</li>
                </ul>
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">
                    ℹ️ <em>Aucune note n'est requise. Les certificats de scolarité, attestations de réussite et conventions de stage se basent directement sur ces données d'inscription.</em>
                </p>
                <a href="/sample_etudiants.csv" class="btn-sample" download>📄 Télécharger le modèle CSV officiel prêt à l'emploi</a>
            </div>
        </div>
    </div>

    <!-- Ajout individuel -->
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;">👤 Inscription Manuelle d'un Étudiant</h3>
        <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:18px;">
            Crée la fiche et active immédiatement l'accès étudiant (mot de passe initial : <code>Secret2026!</code>).
        </p>

        <form action="{{ route('admin_ecole.etudiants.store') }}" method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;">
            @csrf
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Matricule *</label>
                <input type="text" name="matricule" placeholder="Ex: E2026-1001" required class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Prénom *</label>
                <input type="text" name="prenom" placeholder="Ex: Mehdi" required class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Nom *</label>
                <input type="text" name="nom" placeholder="Ex: Tazi" required class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Email (optionnel)</label>
                <input type="email" name="email" placeholder="mehdi.tazi@ecole.ma" class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Photo d'identité (JPG/PNG)</label>
                <input type="file" name="photo" accept="image/*" class="form-input-sm" style="padding-top:6px;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Filière *</label>
                <input type="text" name="filiere" placeholder="Ex: Génie Logiciel" required value="Génie Logiciel" class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Niveau *</label>
                <input type="text" name="niveau" placeholder="Ex: Master 2" required value="Master 2" class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Année Universitaire *</label>
                <input type="text" name="annee_universitaire" placeholder="Ex: 2025-2026" required value="2025-2026" class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Moyenne (optionnelle)</label>
                <input type="number" step="0.01" min="0" max="20" name="moyenne" placeholder="Non requise" class="form-input-sm">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Mention (optionnelle)</label>
                <input type="text" name="mention" placeholder="Ex: Très Bien" class="form-input-sm">
            </div>
            <div style="grid-column:1 / -1;margin-top:6px;">
                <button type="submit" class="btn-primary-action">
                    ✓ Enregistrer l'étudiant
                </button>
            </div>
        </form>
    </div>

    <!-- Historique des imports -->
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);">
            <h3 style="font-size:16px;font-weight:700;">Historique des importations</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date &amp; Heure</th>
                    <th>Fichier</th>
                    <th>Lignes traitées</th>
                    <th>Lignes ignorées</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Rapport</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $import)
                    <tr>
                        <td style="font-size:12px;color:var(--text-muted);font-family:'JetBrains Mono',monospace;">{{ $import->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-weight:700;">{{ $import->nom_fichier_original }}</td>
                        <td><span style="color:#065f46;font-weight:700;">{{ $import->lignes_traitees }}</span> fiches</td>
                        <td>
                            @if($import->lignes_ignorees > 0)
                                <span style="color:#dc2626;font-weight:700;">{{ $import->lignes_ignorees }}</span> erreurs
                            @else
                                <span style="color:var(--text-muted);">0</span>
                            @endif
                        </td>
                        <td>
                            @if($import->statut === 'termine')
                                <span class="badge-green">✓ Terminé</span>
                            @elseif($import->statut === 'en_cours')
                                <span class="badge-amber">⏳ En cours</span>
                            @else
                                <span class="badge-red">✕ Erreur</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin_ecole.import.rapport', $import->id) }}" style="font-size:12.5px;font-weight:600;color:#0b3b2a;text-decoration:none;">Détails &rarr;</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:44px;color:var(--text-muted);">
                            Aucune importation réalisée pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
