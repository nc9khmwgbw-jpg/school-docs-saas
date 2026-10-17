@extends('layouts.admin_ecole')

@section('title', 'Gestion des Demandes')
@section('page_title', 'Gestion & Scellement des Demandes')

@push('styles')
<style>
    .demandes-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .demandes-filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .status-tab-group {
        display: inline-flex;
        background: var(--canvas-subtle);
        padding: 4px;
        border-radius: 9999px;
        gap: 4px;
        border: 1px solid var(--hairline-strong);
    }
    .status-tab-btn {
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: var(--ink-secondary);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .status-tab-btn.active {
        background: var(--ink-primary);
        color: var(--canvas-bg);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .modal-overlay-refusal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(14, 14, 13, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-overlay-refusal.active { display: flex; }
    .modal-box-refusal {
        background: var(--canvas-card-solid);
        border: 1px solid var(--hairline-strong);
        border-radius: 24px;
        padding: 28px;
        max-width: 480px;
        width: 100%;
        box-shadow: var(--shadow-elevated);
        animation: scaleModal 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
@endpush

@section('content')

    <div class="demandes-header-bar">
        <div>
            <h1 style="font-size:22px;font-weight:900;letter-spacing:-0.035em;color:var(--ink-primary);">Demandes de Documents <em>Officiels</em></h1>
            <p style="font-size:13px;color:var(--ink-secondary);margin-top:3px;">Approuvez, refusez avec motif officiel ou téléchargez les pièces certifiées.</p>
        </div>
        <form action="{{ route('admin_ecole.traiter_en_masse') }}" method="POST">
            @csrf
            <button type="submit" class="btn-modern-primary">
                <span>⚡ Traiter &amp; Sceller les Demandes Automatiques</span>
            </button>
        </form>
    </div>

    <!-- Toolbar de Filtrage & Recherche Rapide -->
    <div class="demandes-filter-toolbar">
        <div class="status-tab-group" id="status-filter-tabs">
            <button type="button" class="status-tab-btn active" data-filter-status="all">Toutes ({{ $demandes->total() ?? $demandes->count() }})</button>
            <button type="button" class="status-tab-btn" data-filter-status="en_attente">⏳ En attente</button>
            <button type="button" class="status-tab-btn" data-filter-status="genere">✓ Validées &amp; Scellées</button>
            <button type="button" class="status-tab-btn" data-filter-status="refuse">✕ Refusées</button>
        </div>

        <div style="display:flex;align-items:center;gap:10px;background:var(--canvas-card);border:1px solid var(--hairline-strong);border-radius:9999px;padding:6px 16px;width:min(100%, 300px);box-shadow:var(--shadow-soft);">
            <span style="color:var(--ink-muted);font-size:12px;">🔍</span>
            <input type="text" placeholder="Filtrer par nom ou matricule..." class="text-filter" data-table-filter="demandes-main-table" style="border:none;outline:none;font-size:12.5px;width:100%;font-family:inherit;background:transparent;color:var(--ink-primary);">
        </div>
    </div>

    <!-- Tableau Moderne Style Linear -->
    <div class="modern-table-wrap">
        <table class="modern-table" id="demandes-main-table">
            <thead>
                <tr>
                    <th>Date Demande</th>
                    <th>Étudiant</th>
                    <th>Matricule</th>
                    <th>Pièce Demandée</th>
                    <th>Statut</th>
                    <th>Date Traitement</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                    <tr data-row-status="{{ $demande->statut }}">
                        <td style="font-size:12px;color:#64748b;font-family:var(--font-mono);">
                            {{ $demande->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($demande->etudiant)
                                    <img src="{{ $demande->etudiant->photo_url }}" alt="Photo de {{ $demande->etudiant->prenom }} {{ $demande->etudiant->nom }}" style="width:34px;height:34px;border-radius:9px;object-fit:cover;border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.06);flex-shrink:0;">
                                @endif
                                <div>
                                    <div style="font-weight:700;color:#0f172a;">{{ $demande->etudiant->nom ?? '—' }} {{ $demande->etudiant->prenom ?? '' }}</div>
                                    <div style="font-size:11.5px;color:#64748b;">{{ $demande->etudiant->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-tenant">
                                {{ $demande->etudiant->matricule ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600;color:#334155;">{{ $demande->modele->titre ?? 'Document' }}</div>
                        </td>
                        <td>
                            @if($demande->statut === 'en_attente')
                                <span class="badge-status badge-warning">
                                    <span class="beacon-dot"></span>
                                    En attente
                                </span>
                            @elseif($demande->statut === 'en_traitement')
                                <span class="badge-status badge-warning">
                                    <span class="beacon-dot"></span>
                                    En cours
                                </span>
                            @elseif(in_array($demande->statut, ['genere', 'valide']))
                                <span class="badge-status badge-success">
                                    <span class="beacon-dot"></span>
                                    Scellé &bull; Prêt
                                </span>
                            @elseif($demande->statut === 'refuse')
                                <span class="badge-status badge-danger">
                                    <span class="beacon-dot"></span>
                                    Refusé
                                </span>
                                @if($demande->motif_refus)
                                    <div style="font-size:11px;color:#dc2626;margin-top:3px;">Motif : {{ $demande->motif_refus }}</div>
                                @endif
                            @endif
                        </td>
                        <td style="font-size:12px;color:#64748b;font-family:var(--font-mono);">
                            {{ $demande->date_traitement ? $demande->date_traitement->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="text-align:right;">
                            @if($demande->statut === 'en_attente')
                                <div style="display:inline-flex;gap:6px;">
                                    <form action="{{ route('admin_ecole.demandes.valider', $demande->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-modern-emerald" style="padding:6px 12px;font-size:12px;">
                                            ✓ Valider &amp; Sceller
                                        </button>
                                    </form>
                                    <button type="button" class="btn-modern-ghost" style="padding:6px 12px;font-size:12px;color:#dc2626;border-color:#fecaca;background:#fef2f2;" onclick="openRefusalModal('{{ $demande->id }}', '{{ addslashes($demande->etudiant->nom ?? '') }} {{ addslashes($demande->etudiant->prenom ?? '') }}')">
                                        ✕ Refuser
                                    </button>
                                </div>
                            @elseif(in_array($demande->statut, ['genere', 'valide']) && $demande->documentGenere)
                                <a href="{{ route('admin_ecole.documents.download', ['uuid' => $demande->documentGenere->uuid]) }}" class="btn-modern-ghost" style="padding:6px 12px;font-size:12px;font-weight:700;" target="_blank">
                                    <span>📥 Télécharger PDF</span>
                                </a>
                            @else
                                <span style="font-size:12px;color:#94a3b8;">Clôturé</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;">
                            Aucune demande de document enregistrée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($demandes, 'hasPages') && $demandes->hasPages())
        <div style="margin-top:20px;display:flex;justify-content:space-between;align-items:center;font-size:13px;color:#64748b;">
            <div>Affichage de {{ $demandes->firstItem() ?? 0 }} à {{ $demandes->lastItem() ?? 0 }} sur {{ $demandes->total() ?? 0 }} demandes</div>
            <div>{{ $demandes->links() }}</div>
        </div>
    @endif

    <!-- Modal Moderne de Refus avec Motif -->
    <div class="modal-overlay-refusal" id="refuseModal">
        <div class="modal-box-refusal">
            <h3 style="font-size:18px;font-weight:800;color:#0f172a;margin-bottom:6px;">Motif de Refus de la Demande</h3>
            <p style="font-size:13px;color:#64748b;margin-bottom:16px;" id="modalStudentName"></p>
            
            <form id="refuseForm" method="POST">
                @csrf
                <div class="form-group-modern">
                    <label class="label-modern">Motif officiel (notifié par email à l'étudiant) :</label>
                    <textarea 
                        name="motif_refus" 
                        class="input-modern" 
                        placeholder="Ex: Situation administrative ou financière non régularisée pour le semestre en cours..." 
                        style="height:100px;padding:12px;resize:vertical;"
                        required
                    ></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                    <button type="button" class="btn-modern-ghost" onclick="closeRefusalModal()">Annuler</button>
                    <button type="submit" class="btn-modern-primary" style="background:#dc2626;box-shadow:0 4px 14px rgba(220,38,38,0.35);">
                        Confirmer le refus
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openRefusalModal(demandeId, studentName) {
        document.getElementById('refuseForm').action = '/admin-ecole/demandes/' + demandeId + '/refuser';
        document.getElementById('modalStudentName').textContent = 'Étudiant concerné : ' + studentName;
        document.getElementById('refuseModal').classList.add('active');
    }
    function closeRefusalModal() {
        document.getElementById('refuseModal').classList.remove('active');
    }

    // Filtrage dynamique par statut sans rechargement
    document.querySelectorAll('[data-filter-status]').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-status]').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.getAttribute('data-filter-status');
            const rows = document.querySelectorAll('#demandes-main-table tbody tr');

            rows.forEach(row => {
                const status = row.getAttribute('data-row-status');
                if (filter === 'all' || status === filter || (filter === 'genere' && (status === 'genere' || status === 'valide'))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
