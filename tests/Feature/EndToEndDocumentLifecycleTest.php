<?php

namespace Tests\Feature;

use App\Jobs\GenerateDocumentPdfJob;
use App\Models\DemandeDocument;
use App\Models\DocumentGenere;
use App\Models\Ecole;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use App\Services\PdfGeneratorService;
use App\Services\TemplateEngineService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EndToEndDocumentLifecycleTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Teste le cycle de vie métier intégral de bout en bout :
     * 1. Soumission d'une demande par l'étudiant
     * 2. Validation par l'administrateur de l'école
     * 3. Exécution du job de génération PDF asynchrone
     * 4. Stockage sécurisé du fichier PDF et calcul du hash SHA-256
     * 5. Contrôle public d'authenticité via URL /verifier/{uuid}
     * 6. Téléchargement du document sécurisé par l'étudiant
     */
    public function test_full_document_lifecycle_from_student_request_to_qr_verification(): void
    {
        Mail::fake();

        // 1. Récupération des entités de test
        $ecole = Ecole::where('code_unique', 'ENS-2026')->firstOrFail();
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('role', 'etudiant')
            ->where('email', 'sara.alami@ens.ma')
            ->firstOrFail();

        $adminEcole = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('role', 'admin_ecole')
            ->firstOrFail();

        $modele = ModeleDocument::where('ecole_id', $ecole->id)
            ->where('actif', true)
            ->firstOrFail();

        // 2. L'étudiant connecté soumet une demande
        $this->actingAs($etudiant);
        $responseDemande = $this->post(route('tenant.demandes.store', ['code_unique' => $ecole->code_unique]), [
            'modele_id' => $modele->id,
        ]);
        $responseDemande->assertRedirect(route('tenant.dashboard', ['code_unique' => $ecole->code_unique]));

        $demande = DemandeDocument::where('etudiant_id', $etudiant->id)
            ->where('modele_id', $modele->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertNotNull($demande);

        // 3. Si le modèle nécessite une validation, l'admin valide la demande
        if ($modele->validation_requise && $demande->statut === DemandeDocument::STATUT_EN_ATTENTE) {
            $this->actingAs($adminEcole);
            $responseValidation = $this->post(route('admin_ecole.demandes.valider', ['id' => $demande->id]));
            $responseValidation->assertSessionHas('success');
            $demande->refresh();
        }

        // 4. Exécution du générateur PDF
        $templateEngine = app(TemplateEngineService::class);
        $pdfGenerator   = app(PdfGeneratorService::class);
        $job = new GenerateDocumentPdfJob($demande->id);
        $job->handle($templateEngine, $pdfGenerator);

        $demande->refresh();
        $this->assertEquals(DemandeDocument::STATUT_GENERE, $demande->statut);

        // 5. Vérification du DocumentGenere en base de données
        $doc = DocumentGenere::withoutGlobalScopes()
            ->where('demande_id', $demande->id)
            ->firstOrFail();

        $this->assertNotEmpty($doc->uuid);
        $this->assertNotEmpty($doc->hash_sha256);
        $this->assertGreaterThan(0, $doc->taille_octets);

        // 6. Vérification de l'existence physique du PDF sur le disque local sécurisé
        $this->assertTrue(Storage::disk('local')->exists($doc->chemin_fichier));

        // 7. Vérification publique via l'endpoint QR Code (non authentifié)
        auth()->logout();
        $responseVerif = $this->get(route('public.verifier', ['uuid' => $doc->uuid]));
        $responseVerif->assertStatus(200);
        $responseVerif->assertSee($ecole->nom);

        // 8. Téléchargement original par le public (document officiel infalsifiable)
        $responseDownload = $this->get(route('public.verifier.original', ['uuid' => $doc->uuid]));
        $responseDownload->assertStatus(200);
        $responseDownload->assertHeader('content-type', 'application/pdf');

        // 9. Téléchargement authentifié par l'étudiant
        $this->actingAs($etudiant);
        $responseEtudiantDownload = $this->get(route('tenant.documents.download', [
            'code_unique' => $ecole->code_unique,
            'uuid' => $doc->uuid
        ]));
        $responseEtudiantDownload->assertStatus(200);
        $responseEtudiantDownload->assertHeader('content-type', 'application/pdf');
    }
}
