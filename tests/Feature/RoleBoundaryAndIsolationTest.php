<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\TestCase;

class RoleBoundaryAndIsolationTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);
    }

    protected function tearDown(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        parent::tearDown();
    }

    /**
     * Test 1: Un invité accédant à /portal/ENS-2026/dashboard doit être redirigé vers le portail étudiant (et PAS vers le login admin).
     */
    public function test_guest_accessing_student_dashboard_redirects_to_student_portal(): void
    {
        $response = $this->get('/portal/ENS-2026/dashboard');
        $response->assertRedirect('/portal/ENS-2026');
    }

    /**
     * Test 2: Un invité accédant à /admin-ecole doit être redirigé vers le login admin école.
     */
    public function test_guest_accessing_admin_ecole_redirects_to_admin_ecole_login(): void
    {
        $response = $this->get('/admin-ecole');
        $response->assertRedirect('/admin-ecole/login');
    }

    /**
     * Test 3: Un invité accédant à /superadmin doit être redirigé vers le login superadmin.
     */
    public function test_guest_accessing_superadmin_redirects_to_superadmin_login(): void
    {
        $response = $this->get('/superadmin');
        $response->assertRedirect('/superadmin/login');
    }

    /**
     * Test 4: Un étudiant connecté essayant d'accéder à /admin-ecole ne doit PAS être déconnecté
     * et doit être renvoyé vers son propre dashboard étudiant.
     */
    public function test_student_cannot_access_school_admin_and_stays_in_student_dashboard(): void
    {
        $etudiant = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'E2026-9901')->firstOrFail();

        $response = $this->actingAs($etudiant)->get('/admin-ecole');
        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticatedAs($etudiant);
    }

    /**
     * Test 5: Un étudiant connecté essayant d'accéder à /superadmin doit être renvoyé vers son dashboard étudiant.
     */
    public function test_student_cannot_access_superadmin_and_stays_in_student_dashboard(): void
    {
        $etudiant = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'E2026-9901')->firstOrFail();

        $response = $this->actingAs($etudiant)->get('/superadmin');
        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticatedAs($etudiant);
    }

    /**
     * Test 6: Un admin d'école connecté essayant d'accéder à /superadmin ne doit PAS être déconnecté
     * et doit être renvoyé vers son dashboard d'école /admin-ecole.
     */
    public function test_school_admin_cannot_access_superadmin_and_stays_in_school_dashboard(): void
    {
        $admin = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'admin@ens.ma')->firstOrFail();

        $response = $this->actingAs($admin)->get('/superadmin');
        $response->assertRedirect('/admin-ecole');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test 7: La page étudiante MyPortal ne doit comporter AUCUN lien vers admin-ecole/login.
     */
    public function test_myportal_does_not_contain_admin_ecole_link(): void
    {
        $response = $this->get('/myportal');
        $response->assertStatus(200);
        $response->assertDontSee('/admin-ecole/login');
        $response->assertDontSee('Portail Établissement');
    }

    /**
     * Test 8: La page d'accueil dirige les administrateurs vers admin-ecole/login et non pas superadmin.
     */
    public function test_welcome_page_routes_school_admins_properly(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('/admin-ecole/login');
        $response->assertDontSee('/superadmin/login');
    }

    /**
     * Test 9: Un étudiant d'une école (ENS-2026) tentant d'accéder au dashboard d'une autre école est redirigé vers son propre dashboard.
     */
    public function test_student_cannot_access_other_school_portal_dashboard(): void
    {
        $etudiant = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'E2026-9901')->firstOrFail();

        // On vérifie ou crée une deuxième école
        $autreEcole = Ecole::firstOrCreate(
            ['code_unique' => 'FST-2026'],
            ['nom' => 'Faculté des Sciences et Techniques', 'statut' => 'actif']
        );

        $response = $this->actingAs($etudiant)->get('/portal/FST-2026/dashboard');
        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticatedAs($etudiant);
    }

    /**
     * Test 10: Un admin d'école tentant d'accéder au dashboard étudiant est redirigé vers son dashboard admin d'école.
     */
    public function test_school_admin_cannot_access_student_dashboard(): void
    {
        $admin = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'admin@ens.ma')->firstOrFail();

        $response = $this->actingAs($admin)->get('/portal/ENS-2026/dashboard');
        $response->assertRedirect('/admin-ecole');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test 11: Un superadmin tentant d'accéder à l'espace admin d'école est redirigé vers le dashboard superadmin.
     */
    public function test_superadmin_cannot_access_school_admin_dashboard(): void
    {
        $superadmin = Utilisateur::withoutGlobalScopes()->where('role', 'super_admin')->firstOrFail();

        $response = $this->actingAs($superadmin)->get('/admin-ecole');
        $response->assertRedirect('/superadmin');
        $this->assertAuthenticatedAs($superadmin);
    }

    /**
     * Test 12: Un étudiant connecté visitant la page login admin est maintenu dans son dashboard étudiant.
     */
    public function test_authenticated_student_visiting_school_admin_login_redirects_to_student_dashboard(): void
    {
        $etudiant = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'E2026-9901')->firstOrFail();

        $response = $this->actingAs($etudiant)->get('/admin-ecole/login');
        $response->assertRedirect('/portal/ENS-2026/dashboard');
        $this->assertAuthenticatedAs($etudiant);
    }

    /**
     * Test 13: Un admin d'école connecté visitant la page login superadmin est maintenu dans son dashboard admin.
     */
    public function test_authenticated_school_admin_visiting_superadmin_login_redirects_to_school_dashboard(): void
    {
        $admin = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'admin@ens.ma')->firstOrFail();

        $response = $this->actingAs($admin)->get('/superadmin/login');
        $response->assertRedirect('/admin-ecole');
        $this->assertAuthenticatedAs($admin);
    }
}
