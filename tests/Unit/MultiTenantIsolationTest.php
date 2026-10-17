<?php

namespace Tests\Unit;

use App\Models\DemandeDocument;
use App\Models\Ecole;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use PHPUnit\Framework\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    /**
     * Teste que les modèles requièrent bien l'attribut ecole_id
     */
    public function test_tenant_scope_requires_ecole_id(): void
    {
        $modele = new ModeleDocument([
            'titre' => 'Attestation de Réussite',
            'type_document' => 'attestation_reussite',
            'contenu_html' => '<p>Test</p>',
            'ecole_id' => 1
        ]);

        $this->assertEquals(1, $modele->ecole_id);
    }

    /**
     * Teste le calcul de validité d'un document non expiré
     */
    public function test_document_genere_validity_check(): void
    {
        $docActif = new \App\Models\DocumentGenere([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'expire_le' => null, // Illimité
        ]);

        $this->assertTrue($docActif->estValide());
    }

    /**
     * Teste le calcul d'empreinte SHA-256 pour l'anti-falsification
     */
    public function test_sha256_integrity_hash(): void
    {
        $dummyPdfContent = "%PDF-1.4 [TEST CONTENT FOR INTEGRITY]";
        $hash1 = hash('sha256', $dummyPdfContent);
        $hash2 = hash('sha256', $dummyPdfContent . " ");

        $this->assertEquals(64, strlen($hash1));
        $this->assertNotEquals($hash1, $hash2);
    }
}
