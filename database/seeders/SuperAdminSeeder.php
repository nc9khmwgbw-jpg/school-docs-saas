<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Créer le compte Super Administrateur initial.
     * ⚠️ IMPORTANT : Changez le mot de passe après le premier déploiement !
     */
    public function run(): void
    {
        $defaultPassword = env('SUPERADMIN_DEFAULT_PASSWORD', Str::random(16));

        $superAdmin = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'superadmin@plateforme-saas.com'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => null,
                'role'         => 'super_admin',
                'matricule'    => null,
                'nom'          => 'Système',
                'prenom'       => 'SuperAdmin',
                'mot_de_passe' => Hash::make($defaultPassword),
                'statut'       => 'actif',
            ]
        );

        if ($superAdmin->wasRecentlyCreated) {
            $this->command->info("✅ Super Administrateur créé avec succès !");
            $this->command->info("📧 Email : superadmin@plateforme-saas.com");
            $this->command->warn("🔐 Mot de passe : {$defaultPassword}");
            $this->command->error("⚠️  CHANGEZ CE MOT DE PASSE IMMÉDIATEMENT après la première connexion !");
        } else {
            $this->command->info("ℹ️  Super Administrateur existe déjà.");
        }
    }
}
