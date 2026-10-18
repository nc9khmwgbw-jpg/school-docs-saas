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

        // Étudiant test Sara Alami (ENS)
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
                'statut'       => 'actif',
            ]
        );

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

        // Modèle par défaut : Attestation de Réussite
        \App\Models\ModeleDocument::firstOrCreate(
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

        // Modèle par défaut : Certificat de Scolarité
        \App\Models\ModeleDocument::firstOrCreate(
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

        $this->command->info("\n✨ Données de démonstration ENS initialisées avec succès !");
    }
}
