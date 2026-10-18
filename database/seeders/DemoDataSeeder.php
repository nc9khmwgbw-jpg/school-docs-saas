<?php

namespace Database\Seeders;

use App\Models\Ecole;
use App\Models\Utilisateur;
use App\Models\EtudiantDonnee;
use App\Models\ModeleDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Données de démonstration et fixtures pour tests unitaires & E2E.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error("❌ Ce seeder ne doit PAS être exécuté en production !");
            return;
        }

        $placeholderPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $placeholderJpg = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        // 1. École ENS-2026
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
                'logo_data'          => $placeholderPng,
                'statut'             => 'actif',
            ]
        );
        if (empty($ecole->logo_data)) {
            $ecole->update(['logo_data' => $placeholderPng]);
        }

        // 2. École POLY-2026 (Isolation cross-tenant)
        $poly = Ecole::firstOrCreate(
            ['code_unique' => 'POLY-2026'],
            [
                'uuid'               => Str::uuid(),
                'nom'                => 'Institut Polytechnique de Casablanca',
                'adresse'            => 'Boulevard Hassan II, Casablanca 20000',
                'email_contact'      => 'contact@poly.ma',
                'couleur_primaire'   => '#059669',
                'couleur_secondaire' => '#047857',
                'statut'             => 'actif',
            ]
        );

        // 2.1 Admin POLY-2026
        Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'contact@poly.ma'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $poly->id,
                'role'         => 'admin_ecole',
                'matricule'    => null,
                'nom'          => 'Benjelloun',
                'prenom'       => 'Tariq',
                'mot_de_passe' => Hash::make('Secret2026!'),
                'statut'       => 'actif',
            ]
        );

        // 2.2 Étudiant POLY-2026 (P2026-7701)
        Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['matricule' => 'P2026-7701'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $poly->id,
                'role'         => 'etudiant',
                'nom'          => 'Benali',
                'prenom'       => 'Yassine',
                'email'        => 'yassine.benali@poly.ma',
                'mot_de_passe' => Hash::make('Secret2026!'),
                'photo_data'   => $placeholderJpg,
                'statut'       => 'actif',
            ]
        );

        // 3. Admin ENS
        $adminPassword = env('DEMO_ADMIN_PASSWORD', 'Secret2026!');
        $adminEcole = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'admin@ens.ma'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'admin_ecole',
                'matricule'    => null,
                'nom'          => 'El Mansouri',
                'prenom'       => 'Karim',
                'mot_de_passe' => Hash::make($adminPassword),
                'statut'       => 'actif',
            ]
        );

        // 4. Étudiante Sara Alami (ENS)
        $saraPassword = env('DEMO_ETUDIANT_PASSWORD', 'Secret2026!');
        $sara = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'sara.alami@ens.ma'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'etudiant',
                'matricule'    => 'E2026-9901',
                'nom'          => 'Alami',
                'prenom'       => 'Sara',
                'mot_de_passe' => Hash::make($saraPassword),
                'photo_data'   => $placeholderJpg,
                'statut'       => 'actif',
            ]
        );
        if (empty($sara->photo_data)) {
            $sara->update(['photo_data' => $placeholderJpg]);
        }

        EtudiantDonnee::firstOrCreate(
            ['ecole_id' => $ecole->id, 'matricule' => 'E2026-9901'],
            [
                'filiere'              => 'Génie Logiciel',
                'niveau'               => 'Master 2',
                'annee_universitaire'  => '2025-2026',
                'attributs_dynamiques' => [
                    'moyenne'           => '17.40',
                    'mention'           => 'Très Bien',
                    'session'           => 'Principale Juin 2026',
                    'date_deliberation' => '30 Juin 2026',
                    'rang'              => '1/42',
                ],
            ]
        );

        // 5. Étudiant Mehdi Tazi (ENS - avec photo pour StudentPhotoAndLogoTest)
        $mehdi = Utilisateur::withoutGlobalScopes()->firstOrCreate(
            ['matricule' => 'E2026-8888'],
            [
                'uuid'         => Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'etudiant',
                'matricule'    => 'E2026-8888',
                'nom'          => 'Tazi',
                'prenom'       => 'Mehdi',
                'email'        => 'e2026-8888@etudiant.ma',
                'mot_de_passe' => Hash::make('Secret2026!'),
                'photo_data'   => $placeholderJpg,
                'statut'       => 'actif',
            ]
        );
        if (empty($mehdi->photo_data)) {
            $mehdi->update(['photo_data' => $placeholderJpg]);
        }

        EtudiantDonnee::firstOrCreate(
            ['ecole_id' => $ecole->id, 'matricule' => 'E2026-8888'],
            [
                'filiere'              => 'Informatique',
                'niveau'               => 'Licence 3',
                'annee_universitaire'  => '2025-2026',
            ]
        );

        // 6. Modèles de documents ENS
        ModeleDocument::firstOrCreate(
            ['ecole_id' => $ecole->id, 'type_document' => 'attestation_reussite'],
            [
                'uuid'                => Str::uuid(),
                'titre'               => 'Attestation de Réussite',
                'description'         => 'Modèle certifiant la réussite académique.',
                'contenu_html'        => '<div class="cert-container"><h1>Attestation de Réussite</h1><p>{nom} {prenom} - {matricule}</p><p>{filiere} - {niveau}</p>{qr_code}</div>',
                'styles_css'          => 'body { font-family: sans-serif; }',
                'actif'               => true,
                'validation_requise'  => false,
                'langue'              => 'fr',
            ]
        );

        ModeleDocument::firstOrCreate(
            ['ecole_id' => $ecole->id, 'type_document' => 'certificat_scolarite'],
            [
                'uuid'                => Str::uuid(),
                'titre'               => 'Certificat de Scolarité',
                'description'         => 'Modèle certifiant la scolarité active.',
                'contenu_html'        => '<div class="cert-container"><h1>Certificat de Scolarité</h1><p>{nom} {prenom} - {matricule}</p><p>{filiere} - {niveau}</p>{qr_code}</div>',
                'styles_css'          => 'body { font-family: sans-serif; }',
                'actif'               => true,
                'validation_requise'  => true,
                'langue'              => 'fr',
            ]
        );

        $this->command->info("✨ Données de démonstration et fixtures de test initialisées !");
    }
}
