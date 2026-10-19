<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\TestCase;

class StudentPhotoAndLogoTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }
    public function test_student_has_photo_in_database()
    {
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('matricule', 'E2026-8888')
            ->first();

        $this->assertNotNull($etudiant, "L'étudiant E2026-8888 doit exister");
        $this->assertTrue($etudiant->hasPhoto(), "L'étudiant doit avoir une photo configurée");
        $this->assertNotEmpty($etudiant->photo_url, "L'URL de la photo ne doit pas être vide");
        $this->assertTrue(
            str_starts_with($etudiant->photo_url, 'http') || str_starts_with($etudiant->photo_url, 'data:image/') || str_starts_with($etudiant->photo_url, '/storage'),
            "La photo doit être servie via une URL publique optimisée ou Data URI"
        );
    }

    public function test_ecole_has_logo_in_database()
    {
        $ecole = Ecole::where('code_unique', 'ENS-2026')->first();

        $this->assertNotNull($ecole, "L'école ENS-2026 doit exister");
        $this->assertNotEmpty($ecole->logo_url, "Le logo_url de l'école ne doit pas être vide");
        $this->assertTrue(
            str_starts_with($ecole->logo_url, 'http') || str_starts_with($ecole->logo_url, 'data:image/') || str_starts_with($ecole->logo_url, '/storage'),
            "Le logo doit être accessible via URL publique optimisée ou Data URI"
        );
    }

    public function test_dashboard_renders_student_photo_and_school_logo()
    {
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('matricule', 'E2026-8888')
            ->first();

        $response = $this->actingAs($etudiant)
            ->get(route('tenant.dashboard', ['code_unique' => 'ENS-2026']));

        $response->assertStatus(200);
        $response->assertSee('forma-user-photo-thumb');
        $response->assertSee('forma-profile-photo-hero');
        $response->assertSee('Mehdi Tazi');
    }

    public function test_historique_renders_student_photo()
    {
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('matricule', 'E2026-8888')
            ->first();

        $response = $this->actingAs($etudiant)
            ->get(route('tenant.historique', ['code_unique' => 'ENS-2026']));

        $response->assertStatus(200);
        $response->assertSee('forma-user-photo-thumb');
        $response->assertSee('forma-profile-photo-hero');
        $response->assertSee('Mehdi Tazi');
    }

    public function test_admin_etudiants_table_renders_student_photo()
    {
        $admin = Utilisateur::withoutGlobalScopes()
            ->where('email', 'admin@ens.ma')
            ->first();

        $response = $this->actingAs($admin)
            ->get(route('admin_ecole.etudiants.index'));

        $response->assertStatus(200);
        $response->assertSee('student-avatar-img');
        $response->assertSee('Tazi');
    }
}
