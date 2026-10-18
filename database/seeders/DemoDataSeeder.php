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
        $officialCss = 'body { font-family: "DejaVu Serif", Georgia, serif; color: #000; margin: 0; padding: 0; } .cert-container { border: 3px double #000; padding: 24px 28px; background: #fff; min-height: 880px; position: relative; } .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; } .header-left { width: 22%; text-align: left; } .header-center { width: 56%; text-align: center; } .header-right { width: 22%; text-align: right; font-size: 10px; } .republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; } .ministere { font-size: 8px; margin-top: 2px; text-transform: uppercase; } .school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; } .dept-name { font-size: 9.5px; font-style: italic; } .header-divider { border-top: 2px solid #000; border-bottom: 1px solid #000; height: 3px; margin: 8px 0 16px 0; } .doc-title { font-size: 20px; font-weight: bold; text-align: center; letter-spacing: 2px; text-transform: uppercase; } .doc-ref { font-size: 9.5px; text-align: center; font-family: "DejaVu Sans Mono", monospace; margin-bottom: 16px; } .student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; } .student-info-table td { padding: 5px 8px; font-size: 12.5px; } .info-label { width: 38%; border-bottom: 1px dotted #888; } .info-value { width: 62%; border-bottom: 1px dotted #888; } .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; } .sig-col-left { width: 50%; text-align: left; font-size: 11px; } .sig-col-right { width: 50%; text-align: center; font-size: 11px; }';

        ModeleDocument::firstOrCreate(
            ['ecole_id' => $ecole->id, 'type_document' => 'attestation_reussite'],
            [
                'uuid'                => Str::uuid(),
                'titre'               => 'Attestation de Réussite',
                'description'         => 'Modèle certifiant la réussite académique.',
                'contenu_html'        => '<div class="cert-container"><table class="header-table"><tr><td class="header-left">{logo}</td><td class="header-center"><div class="republique">ROYAUME DU MAROC</div><div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div><div class="school-name">{nom_ecole}</div><div class="dept-name">Jury Académique d\'Évaluation &amp; Délibération</div></td><td class="header-right"><div class="academic-year">Session Pédagogique<br/><strong>Juin {annee_actuelle}</strong></div></td></tr></table><div class="header-divider"></div><h1 class="doc-title">ATTESTATION DE RÉUSSITE</h1><div class="doc-ref">Réf : REUSS/{annee_actuelle}/{matricule}</div><div class="body-content"><p>Le Président du Jury et Directeur de l\'établissement <strong>{nom_ecole}</strong>, atteste par la présente que :</p><table class="student-info-table"><tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr><tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr><tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong></td></tr><tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr><tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr><tr><td class="info-label">Décision du Jury :</td><td class="info-value"><strong>ADMIS(E) {mention}</strong></td></tr></table><p>A satisfait aux examens de fin d\'études et aux exigences pédagogiques requises pour la validation intégrale des modules et des crédits de la filière.</p><p>La présente attestation lui est délivrée pour servir et valoir ce que de droit.</p></div><table class="signatures-table"><tr><td class="sig-col-left"><div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div><div class="sig-role">Le Président du Jury</div></td><td class="sig-col-right"><div class="sig-role">Le Directeur de l\'Établissement</div><table style="width:100%;margin-top:6px;border-collapse:collapse;"><tr><td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td><td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td></tr></table></td></tr></table><div class="anti-fraud-footer">{qr_code}</div></div>',
                'styles_css'          => $officialCss,
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
                'contenu_html'        => '<div class="cert-container"><table class="header-table"><tr><td class="header-left">{logo}</td><td class="header-center"><div class="republique">ROYAUME DU MAROC</div><div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div><div class="school-name">{nom_ecole}</div><div class="dept-name">Direction des Études et des Affaires Académiques</div></td><td class="header-right"><div class="academic-year">Année Universitaire<br/><strong>{annee_universitaire}</strong></div></td></tr></table><div class="header-divider"></div><h1 class="doc-title">CERTIFICAT DE SCOLARITÉ</h1><div class="doc-ref">Réf : SCOL/{annee_actuelle}/{matricule}</div><div class="body-content"><p>Le Directeur de l\'établissement <strong>{nom_ecole}</strong>, certifie par la présente que :</p><table class="student-info-table"><tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr><tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr><tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong></td></tr><tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr><tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr></table><p>Est régulièrement inscrit(e) au sein de notre établissement pour l\'année universitaire en cours et poursuit son cursus conformément aux dispositions et règlements pédagogiques en vigueur.</p><p>Le présent certificat lui est délivré sur sa demande pour servir et valoir ce que de droit auprès des administrations et organismes compétents.</p></div><table class="signatures-table"><tr><td class="sig-col-left"><div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div><div style="margin-top:10px;"><div class="sig-role">Pour la Direction des Études</div><div class="sig-sub">Le Secrétariat Académique</div></div></td><td class="sig-col-right"><div class="sig-role">Le Directeur de l\'Établissement</div><table style="width:100%;margin-top:6px;border-collapse:collapse;"><tr><td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td><td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td></tr></table></td></tr></table><div class="anti-fraud-footer">{qr_code}</div></div>',
                'styles_css'          => $officialCss,
                'actif'               => true,
                'validation_requise'  => true,
                'langue'              => 'fr',
            ]
        );

        ModeleDocument::firstOrCreate(
            ['ecole_id' => $ecole->id, 'type_document' => 'releve_notes'],
            [
                'uuid'                => Str::uuid(),
                'titre'               => 'Relevé de Notes Officiel',
                'description'         => 'Relevé de notes officiel certifié avec détail des semestres et crédits ECTS.',
                'contenu_html'        => '<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Direction des Études et des Examens Académiques</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Année Universitaire<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="title-container">
        <h1 class="doc-title">RELEVÉ DE NOTES OFFICIEL</h1>
        <div class="doc-ref">Réf : TRANS/{annee_actuelle}/{matricule}</div>
    </div>
    <div class="body-content">
        <table class="student-info-table">
            <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
            <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
            <tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong> ({niveau})</td></tr>
            <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
        </table>
        {tableau_notes}
    </div>
    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Responsable des Délibérations</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l\'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="anti-fraud-footer">{qr_code}</div>
</div>',
                'styles_css'          => 'body { font-family: "DejaVu Serif", serif; } .cert-container { border: 2px solid #000; padding: 20px; }',
                'actif'               => true,
                'validation_requise'  => true,
                'langue'              => 'fr',
            ]
        );

        // 7. Notes et Résultats Académiques Réels (Sara Alami - Master 2 Génie Logiciel)
        $modulesSara = [
            ['semestre' => 'Semestre 1', 'module' => 'Architecture des Logiciels & Design Patterns', 'note' => 18.00, 'coeff' => 6.0],
            ['semestre' => 'Semestre 1', 'module' => 'Cryptographie Appliquée & Sécurité Réseau', 'note' => 17.50, 'coeff' => 6.0],
            ['semestre' => 'Semestre 1', 'module' => 'Bases de Données Distribuées & Cloud NoSQL', 'note' => 16.50, 'coeff' => 6.0],
            ['semestre' => 'Semestre 1', 'module' => 'Algorithmique Avancée & Complexité', 'note' => 15.50, 'coeff' => 6.0],
            ['semestre' => 'Semestre 1', 'module' => 'Anglais Professionnel & Communication', 'note' => 17.00, 'coeff' => 6.0],
            ['semestre' => 'Semestre 2', 'module' => 'DevSecOps & Orchestration Kubernetes', 'note' => 18.50, 'coeff' => 6.0],
            ['semestre' => 'Semestre 2', 'module' => 'Intelligence Artificielle & Machine Learning', 'note' => 16.00, 'coeff' => 6.0],
            ['semestre' => 'Semestre 2', 'module' => 'Ingénierie des Systèmes d\'Information', 'note' => 17.00, 'coeff' => 6.0],
            ['semestre' => 'Semestre 2', 'module' => 'Audit de Code & Sécurité Applicative', 'note' => 19.00, 'coeff' => 6.0],
            ['semestre' => 'Semestre 2', 'module' => 'Projet de Fin d\'Études Pédagogique', 'note' => 18.00, 'coeff' => 6.0],
        ];

        foreach ($modulesSara as $mod) {
            \App\Models\Resultat::firstOrCreate(
                [
                    'ecole_id'        => $ecole->id,
                    'etudiant_id'     => $sara->id,
                    'intitule_module' => $mod['module'],
                ],
                [
                    'note'                => $mod['note'],
                    'note_max'            => 20.00,
                    'coefficient'         => $mod['coeff'],
                    'semestre'            => $mod['semestre'],
                    'session'             => 'Principale',
                    'annee_universitaire' => '2025-2026',
                    'verrouille'          => false,
                ]
            );
        }

        $this->command->info("✨ Données de démonstration, modèles et résultats académiques initialisés !");
    }
}
