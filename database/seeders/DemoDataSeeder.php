<?php

namespace Database\Seeders;

use App\Models\Ecole;
use App\Models\Utilisateur;
use App\Models\EtudiantDonnee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Créer des données de démonstration pour tester la plateforme.
     * ⚠️ À UTILISER UNIQUEMENT EN DÉVELOPPEMENT !
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error("❌ Ce seeder ne doit PAS être exécuté en production !");
            return;
        }

        // École de démonstration
        $ecole = Ecole::firstOrCreate(
            ['code_unique' => 'ENS-2026'],
            [
                'uuid'               => Str::uuid(),
                'nom'                => 'École Nationale Supérieure (ENS)',
                'adresse'            => 'Avenue de la République, Rabat 10000',
                'email_contact'      => 'contact@ens.ma',
                'telephone'          => '+212 537 000 001',
                'couleur_primaire'   => '#1D4ED8',
                'couleur_secondaire' => '#1E40AF',
                'statut'             => 'actif',
            ]
        );

        $this->command->info("✅ École de démo créée : {$ecole->nom}");

        // Admin école de démo
        $adminPassword = Str::random(12);
        $adminEcole = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'admin@ens.ma'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'admin_ecole',
                'matricule'    => null,
                'nom'          => 'Administrateur',
                'prenom'       => 'ENS',
                'mot_de_passe' => Hash::make($adminPassword),
                'statut'       => 'actif',
            ]
        );

        if ($adminEcole->wasRecentlyCreated) {
            $this->command->info("✅ Admin école créé");
            $this->command->info("   📧 Email : admin@ens.ma");
            $this->command->warn("   🔐 Mot de passe : {$adminPassword}");
        }

        // Étudiant de démo
        $etudiantPassword = Str::random(12);
        $etudiant = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'etudiant.demo@ens.ma'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'etudiant',
                'matricule'    => 'ENS2026001',
                'nom'          => 'DÉMONSTRATION',
                'prenom'       => 'Étudiant',
                'mot_de_passe' => Hash::make($etudiantPassword),
                'statut'       => 'actif',
            ]
        );

        if ($etudiant->wasRecentlyCreated) {
            $this->command->info("✅ Étudiant de démo créé");
            $this->command->info("   📧 Email : etudiant.demo@ens.ma");
            $this->command->info("   🎓 Matricule : ENS2026001");
            $this->command->warn("   🔐 Mot de passe : {$etudiantPassword}");

            // Données académiques de l'étudiant
            EtudiantDonnee::create([
                'ecole_id'             => $ecole->id,
                'matricule'            => 'ENS2026001',
                'filiere'              => 'Informatique',
                'niveau'               => 'Master 2',
                'annee_universitaire'  => '2025-2026',
                'attributs_dynamiques' => [
                    'moyenne' => '16.5',
                    'mention' => 'Très Bien',
                    'session' => 'Juin 2026',
                ],
            ]);
        }

        $this->command->info("\n✨ Données de démonstration créées avec succès !");
        $this->command->warn("⚠️  Notez les mots de passe ci-dessus - ils ne seront pas réaffichés.");
    }
}
