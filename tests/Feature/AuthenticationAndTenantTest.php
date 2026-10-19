<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Utilisateur;
use App\Models\ModeleDocument;
use Illuminate\Foundation\Testing\TestCase;

class AuthenticationAndTenantTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Teste l'accès à la page d'accueil publique.
     */
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('DocScolaire SaaS');
    }

    /**
     * Teste que la recherche d'école redirige vers le bon portail.
     */
    public function test_ecole_recherche_redirects_correctly(): void
    {
        $response = $this->post('/trouver-ecole', [
            'code_unique' => 'ENS-2026'
        ]);

        $response->assertRedirect('/portal/ENS-2026');
    }

    /**
     * Teste la connexion réussie d'un étudiant avec le champ 'identifiant' et matricule.
     */
    public function test_student_can_login_with_identifiant_and_matricule(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'E2026-9901',
            'mot_de_passe' => 'Secret2026!'
        ]);

        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('sara.alami@ens.ma', auth()->user()->email);
    }

    /**
     * Teste la connexion réussie d'un étudiant avec son email académique.
     */
    public function test_student_can_login_with_email(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'sara.alami@ens.ma',
            'mot_de_passe' => 'Secret2026!'
        ]);

        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Teste la connexion avec un matricule en minuscules.
     */
    public function test_student_can_login_with_lowercase_matricule(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'e2026-9901',
            'mot_de_passe' => 'Secret2026!'
        ]);

        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Teste la connexion avec le champ 'matricule' historique.
     */
    public function test_student_can_login_with_matricule_field(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'matricule'    => 'E2026-9901',
            'mot_de_passe' => 'Secret2026!'
        ]);

        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Teste que l'isolation empêche un étudiant de se connecter sur une autre école.
     */
    public function test_cross_tenant_login_fails(): void
    {
        $response = $this->post('/portal/POLY-2026/login', [
            'matricule'    => 'E2026-9901',
            'mot_de_passe' => 'Secret2026!'
        ]);

        $response->assertSessionHasErrors('auth');
    }

    /**
     * Teste la page publique de vérification avec un UUID invalide.
     */
    public function test_verification_with_invalid_uuid(): void
    {
        $response = $this->get('/verifier/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(200);
        $response->assertSee('Document Non Trouvé');
    }

    /**
     * Teste qu'un code établissement inexistant redirige vers l'accueil avec un message d'erreur clair.
     */
    public function test_invalid_school_code_redirects_to_home(): void
    {
        $response = $this->get('/portal/CODE-INEXISTANT-999');
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('code_unique');
    }

    /**
     * Teste le paramètre ?switch=1 qui permet de déconnecter proprement l'étudiant pour changer de compte.
     */
    public function test_portal_switch_clears_session(): void
    {
        $response = $this->get('/portal/ENS-2026?switch=1');
        $response->assertRedirect('/portal/ENS-2026');
        $this->assertGuest();
    }

    /**
     * Teste la connexion admin école.
     */
    public function test_school_admin_login(): void
    {
        $response = $this->post('/admin-ecole/login', [
            'email'        => 'admin@ens.ma',
            'mot_de_passe' => 'Secret2026!',
        ]);

        $response->assertRedirect('/admin-ecole');
        $this->assertAuthenticated();
    }

    /**
     * Teste la demande de réinitialisation de mot de passe par email.
     */
    public function test_password_reset_request_dispatches_email(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $response = $this->post('/portal/ENS-2026/mot-de-passe/oublie', [
            'email' => 'sara.alami@ens.ma'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Teste que l'URL MyPortal redirige vers l'accueil (isolation stricte multi-tenant).
     */
    public function test_myportal_page_redirects_to_home(): void
    {
        $response = $this->get('/myportal');
        $response->assertRedirect('/');
    }

    /**
     * Teste la connexion directe sur MyPortal avec détection automatique de l'école.
     */
    public function test_myportal_direct_login_with_auto_school_detection(): void
    {
        $response = $this->post('/myportal/login', [
            'identifiant'  => 'E2026-9901',
            'mot_de_passe' => 'Secret2026!',
        ]);

        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Teste la consultation de la copie originale certifiée conforme via UUID public.
     */
    public function test_verifier_original_document_returns_pdf(): void
    {
        $doc = \App\Models\DocumentGenere::first();
        if ($doc) {
            $response = $this->get("/verifier/{$doc->uuid}/original");
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'application/pdf');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Teste que les identifiants d'un administrateur d'une autre école (Poly) sont catégoriquement
     * rejetés sur le portail d'une autre école (ENS), sans ouvrir sa page.
     */
    public function test_poly_admin_credentials_rejected_on_ens_portal(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'contact@poly.ma',
            'mot_de_passe' => 'Secret2026!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('auth');
    }

    /**
     * Teste que les identifiants d'un étudiant d'une autre école (Poly) sont catégoriquement
     * rejetés sur le portail d'une autre école (ENS), sans ouvrir sa page.
     */
    public function test_poly_student_credentials_rejected_on_ens_portal(): void
    {
        $response = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'P2026-7701',
            'mot_de_passe' => 'Secret2026!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('auth');
    }

    /**
     * Teste qu'un étudiant d'une autre école ayant une session active qui visite le portail public d'ENS
     * ne soit pas renvoyé vers son dashboard Poly, mais voit le portail ENS avec session réinitialisée.
     */
    public function test_authenticated_poly_student_visiting_ens_portal_resets_session(): void
    {
        $etudiantPoly = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'P2026-7701')->firstOrFail();

        $response = $this->actingAs($etudiantPoly)->get('/portal/ENS-2026');
        $response->assertStatus(200);
        $response->assertSee('ENS');
        $this->assertGuest();
    }

    /**
     * Teste qu'un admin d'une autre école ayant une session active qui visite le portail public d'ENS
     * ne soit pas renvoyé vers son dashboard admin Poly, mais voit le portail ENS avec session réinitialisée.
     */
    public function test_authenticated_poly_admin_visiting_ens_portal_resets_session(): void
    {
        $adminPoly = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'contact@poly.ma')->firstOrFail();

        $response = $this->actingAs($adminPoly)->get('/portal/ENS-2026');
        $response->assertStatus(200);
        $response->assertSee('ENS');
        $this->assertGuest();
    }
}

