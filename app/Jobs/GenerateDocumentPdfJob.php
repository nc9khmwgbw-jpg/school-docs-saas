<?php

namespace App\Jobs;

use App\Models\DemandeDocument;
use App\Models\DocumentGenere;
use App\Services\PdfGeneratorService;
use App\Services\TemplateEngineService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDocumentPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    protected int $demandeId;

    /**
     * Crée une nouvelle tâche asynchrone de génération de document.
     */
    public function __construct(int $demandeId)
    {
        $this->demandeId = $demandeId;
    }

    /**
     * Exécute le job en arrière-plan (dépilé par Supervisor).
     */
    public function handle(TemplateEngineService $templateEngine, PdfGeneratorService $pdfGenerator): void
    {
        $demande = DemandeDocument::withoutGlobalScopes()
            ->with(['etudiant', 'modele', 'ecole'])
            ->find($this->demandeId);

        if (!$demande) {
            Log::error("GenerateDocumentPdfJob: Demande ID {$this->demandeId} introuvable.");
            return;
        }

        // ✅ SÉCURITÉ : Vérification anti-bypass du TenantScope
        $expectedEcoleId = \App\Scopes\TenantScope::getTenantId();
        if ($expectedEcoleId !== null && $demande->ecole_id !== $expectedEcoleId) {
            Log::critical("GenerateDocumentPdfJob: Tentative d'accès cross-tenant détectée ! Demande #{$this->demandeId} appartient à l'école #{$demande->ecole_id} mais le contexte attend #{$expectedEcoleId}");
            throw new \RuntimeException("Cross-tenant access denied in PDF generation job");
        }

        // Idempotence : si le document a déjà été généré pour cette demande, éviter l'erreur de clé unique
        $docExistant = DocumentGenere::withoutGlobalScopes()
            ->where('demande_id', $demande->id)
            ->first();

        if ($docExistant) {
            $demande->update([
                'statut'   => DemandeDocument::STATUT_GENERE,
                'remarque' => 'Document déjà généré.'
            ]);
            Log::info("GenerateDocumentPdfJob: Document déjà présent pour demande #{$demande->id}.");
            return;
        }

        $previousTenantId = \App\Scopes\TenantScope::getTenantId();
        try {
            // ✅ ISOLATION : Fixe le tenant ID pour la durée exclusive de ce job
            \App\Scopes\TenantScope::setTenantId($demande->ecole_id);

            // 1. Passage en état de traitement
            $demande->update(['statut' => DemandeDocument::STATUT_EN_TRAITEMENT]);

            $etudiant = $demande->etudiant;
            $modele = $demande->modele;
            $ecole = $demande->ecole;

            $docUuid = (string) \Illuminate\Support\Str::uuid();
            $expireLe = $modele->expire_dans_jours ? now()->addDays($modele->expire_dans_jours) : null;

            // 2. Fusion HTML avec les variables dynamiques & QR code
            $htmlRendu = $templateEngine->render($modele, $etudiant, $ecole, $docUuid);

            // 3. Rendu PDF et calcul du hash SHA-256
            $pdfResultat = $pdfGenerator->generate($htmlRendu, $ecole->id, $modele->titre);

            // 4. Enregistrement du document généré
            DocumentGenere::withoutGlobalScopes()->create([
                'uuid'                 => $docUuid,
                'ecole_id'             => $ecole->id,
                'demande_id'           => $demande->id,
                'chemin_fichier'       => $pdfResultat['chemin_fichier'],
                'nom_fichier'          => $pdfResultat['nom_fichier'],
                'taille_octets'        => $pdfResultat['taille_octets'],
                'hash_sha256'          => $pdfResultat['hash_sha256'],
                'expire_le'            => $expireLe,
                'telechargements_count'=> 0,
            ]);

            // 4.1. Verrouillage préventif optionnel si module de notes actif
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('resultats')) {
                    \App\Models\Resultat::where('ecole_id', $ecole->id)
                        ->where('etudiant_id', $etudiant->id)
                        ->update(['verrouille' => true, 'verrouille_le' => now()]);
                }
            } catch (\Throwable $ignored) {}

            // 5. Mise à jour de la demande en état final
            $demande->update([
                'statut'   => DemandeDocument::STATUT_GENERE,
                'remarque' => 'Document généré avec succès par le worker asynchrone.'
            ]);

            Log::info("GenerateDocumentPdfJob: Document généré avec succès pour demande #{$demande->id} (École #{$ecole->id}).");

        } catch (\Throwable $e) {
            // Si le document a déjà été inséré en parallèle par un autre worker, considérer le job comme réussi
            $docExistant = DocumentGenere::withoutGlobalScopes()
                ->where('demande_id', $demande->id)
                ->first();

            if ($docExistant) {
                $demande->update([
                    'statut'   => DemandeDocument::STATUT_GENERE,
                    'remarque' => 'Document généré avec succès.'
                ]);
                Log::info("GenerateDocumentPdfJob: Concurrence résolue, document déjà généré pour demande #{$demande->id}.");
                return;
            }

            Log::error("GenerateDocumentPdfJob Erreur sur demande #{$this->demandeId}: " . $e->getMessage());

            $demande->update([
                'statut'   => DemandeDocument::STATUT_EN_TRAITEMENT,
                'remarque' => mb_substr('Tentative échouée : ' . $e->getMessage(), 0, 490)
            ]);

            throw $e; // Permet à Supervisor de retenter (jusqu'à $tries = 3)
        } finally {
            // ✅ ISOLATION GARANTIE : Restaure systématiquement le contexte pour le worker Supervisor
            \App\Scopes\TenantScope::setTenantId($previousTenantId);
        }
    }

    /**
     * Exécuté lorsque le job a épuisé l'ensemble de ses 3 tentatives.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("GenerateDocumentPdfJob: Échec définitif pour demande #{$this->demandeId} après 3 tentatives: " . $exception->getMessage());

        $demande = DemandeDocument::withoutGlobalScopes()->find($this->demandeId);
        if ($demande && $demande->statut !== DemandeDocument::STATUT_GENERE) {
            $demande->update([
                'statut'      => DemandeDocument::STATUT_REFUSE,
                'motif_refus' => 'Incident technique lors de la génération PDF. Veuillez renouveler votre demande.',
                'remarque'    => mb_substr('Échec définitif worker : ' . $exception->getMessage(), 0, 490),
            ]);
        }
    }
}
