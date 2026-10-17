-- ========================================================
-- SCHÉMA DE BASE DE DONNÉES COMPLET v2.0
-- Plateforme SaaS Universelle de Documents Scolaires
-- Cloisonnement Multi-Tenant par ecole_id & UUID v4
-- Conforme : Loi 09-08 (Maroc) - CNDP
-- ========================================================

CREATE DATABASE IF NOT EXISTS saas_documents CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saas_documents;

-- Note de durabilité : Les tables utilisent CREATE TABLE IF NOT EXISTS.
-- Les migrations officielles Laravel doivent être privilégiées via `php artisan migrate`.

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 1. ÉTABLISSEMENTS (Tenants)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS ecoles (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                CHAR(36)     NOT NULL UNIQUE,
    nom                 VARCHAR(255) NOT NULL,
    code_unique         VARCHAR(50)  NOT NULL UNIQUE COMMENT 'Identifiant saisi par étudiant (ex: ENS-2026)',
    adresse             TEXT         NULL,
    email_contact       VARCHAR(255) NULL,
    telephone           VARCHAR(50)  NULL,
    logo_path           VARCHAR(255) NULL,
    logo_data           LONGTEXT     NULL COMMENT 'Base64 data URI du logo (image directe en BDD)',
    logo_blob           LONGBLOB     NULL COMMENT 'Image binaire du logo en BDD',
    cachet_path         VARCHAR(255) NULL COMMENT 'Image du cachet officiel',
    cachet_data         LONGTEXT     NULL,
    cachet_blob         LONGBLOB     NULL,
    signature_path      VARCHAR(255) NULL COMMENT 'Image signature du directeur',
    signature_data      LONGTEXT     NULL,
    signature_blob      LONGBLOB     NULL,
    couleur_primaire    VARCHAR(7)   DEFAULT '#2563EB' COMMENT 'Code HEX couleur principale',
    couleur_secondaire  VARCHAR(7)   DEFAULT '#1E40AF' COMMENT 'Code HEX couleur secondaire',
    statut              ENUM('actif', 'suspendu', 'en_attente') DEFAULT 'actif',
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ecole_code   (code_unique),
    INDEX idx_ecole_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 2. UTILISATEURS (Super Admin, Admins Écoles, Étudiants)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    ecole_id        BIGINT UNSIGNED NULL COMMENT 'NULL exclusivement pour le Super Admin',
    role            ENUM('super_admin', 'admin_ecole', 'etudiant') NOT NULL,
    matricule       VARCHAR(100) NULL COMMENT 'Utilisé par les étudiants pour se connecter',
    nom             VARCHAR(150) NOT NULL,
    prenom          VARCHAR(150) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL COMMENT 'Haché bcrypt - jamais en clair',
    photo_path      VARCHAR(255) NULL COMMENT 'Chemin du fichier photo',
    photo_data      LONGTEXT     NULL COMMENT 'Base64 data URI de la photo étudiant (image directe)',
    photo_blob      LONGBLOB     NULL COMMENT 'Image binaire de la photo étudiant en BDD',
    statut          ENUM('actif', 'inactif') DEFAULT 'actif',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_utilisateurs_ecole FOREIGN KEY (ecole_id) REFERENCES ecoles(id) ON DELETE CASCADE,
    INDEX idx_user_ecole_role             (ecole_id, role),
    UNIQUE KEY uq_user_ecole_matricule    (ecole_id, matricule),
    UNIQUE KEY uq_user_ecole_email        (ecole_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 3. DONNÉES SCOLAIRES IMPORTÉES
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS etudiants_donnees (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ecole_id                BIGINT UNSIGNED NOT NULL,
    matricule               VARCHAR(100)    NOT NULL,
    filiere                 VARCHAR(150)    NULL,
    niveau                  VARCHAR(100)    NULL,
    annee_universitaire     VARCHAR(50)     NULL,
    photo_path              VARCHAR(255)    NULL,
    photo_data              LONGTEXT        NULL,
    attributs_dynamiques    JSON            NOT NULL DEFAULT (JSON_OBJECT())
        COMMENT 'Ex: {"moyenne":"16.2","mention":"Très Bien","session":"Juin 2026"}',
    date_import             TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_etudiants_ecole FOREIGN KEY (ecole_id) REFERENCES ecoles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_etudiant_ecole_mat    (ecole_id, matricule),
    INDEX idx_etudiant_recherche        (ecole_id, matricule, filiere)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 4. RÉSULTATS / NOTES (pour relevés de notes)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS resultats (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ecole_id            BIGINT UNSIGNED  NOT NULL,
    etudiant_id         BIGINT UNSIGNED  NOT NULL,
    intitule_module     VARCHAR(200)     NOT NULL,
    note                DECIMAL(5,2)     NOT NULL,
    note_max            DECIMAL(5,2)     NOT NULL DEFAULT 20.00,
    coefficient         DECIMAL(4,2)     DEFAULT 1.00,
    semestre            VARCHAR(50)      NULL,
    session             VARCHAR(50)      NULL COMMENT 'Principale, Rattrapage...',
    annee_universitaire VARCHAR(50)      NULL,
    verrouille          BOOLEAN          DEFAULT FALSE
        COMMENT 'TRUE dès qu un PDF utilisant ce résultat a été généré',
    verrouille_le       TIMESTAMP        NULL,
    created_at          TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_resultats_ecole    FOREIGN KEY (ecole_id)    REFERENCES ecoles(id)       ON DELETE CASCADE,
    CONSTRAINT fk_resultats_etudiant FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_resultats_etudiant (ecole_id, etudiant_id, annee_universitaire),
    CONSTRAINT chk_note_valide CHECK (note >= 0 AND note <= note_max)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 5. MODÈLES DE DOCUMENTS DYNAMIQUES (Templates)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS modeles_documents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                CHAR(36)     NOT NULL UNIQUE,
    ecole_id            BIGINT UNSIGNED NOT NULL,
    titre               VARCHAR(200) NOT NULL,
    type_document       ENUM('attestation_reussite','certificat_scolarite','releve_notes',
                             'convention_stage','certificat','duplicata','autre') NOT NULL,
    description         TEXT         NULL,
    contenu_html        MEDIUMTEXT   NOT NULL COMMENT 'Contient {nom},{prenom},{matricule}, etc.',
    styles_css          TEXT         NULL,
    actif               BOOLEAN      DEFAULT TRUE,
    validation_requise  BOOLEAN      DEFAULT FALSE
        COMMENT 'TRUE = l admin valide avant génération du PDF',
    langue              ENUM('fr','ar','fr_ar') DEFAULT 'fr',
    expire_dans_jours   INT UNSIGNED NULL
        COMMENT 'Durée de validité du document; NULL = illimité',
    variables_schema    JSON         NULL COMMENT 'Liste des variables disponibles',
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_modeles_ecole FOREIGN KEY (ecole_id) REFERENCES ecoles(id) ON DELETE CASCADE,
    INDEX idx_modeles_ecole_actif (ecole_id, actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 6. DEMANDES DE DOCUMENTS
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS demandes_documents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                CHAR(36)     NOT NULL UNIQUE,
    ecole_id            BIGINT UNSIGNED NOT NULL,
    etudiant_id         BIGINT UNSIGNED NOT NULL,
    modele_id           BIGINT UNSIGNED NOT NULL,
    statut              ENUM('en_attente','en_traitement','genere','valide','refuse')
                        DEFAULT 'en_attente',
    remarque            VARCHAR(500) NULL COMMENT 'Note interne de traitement',
    motif_refus         VARCHAR(500) NULL COMMENT 'Motif saisi par l admin en cas de refus',
    traite_par          BIGINT UNSIGNED NULL COMMENT 'ID de l admin ayant traité la demande',
    date_traitement     TIMESTAMP    NULL COMMENT 'Horodatage de la décision',
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_demandes_ecole    FOREIGN KEY (ecole_id)    REFERENCES ecoles(id)              ON DELETE CASCADE,
    CONSTRAINT fk_demandes_etudiant FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id)        ON DELETE CASCADE,
    CONSTRAINT fk_demandes_modele   FOREIGN KEY (modele_id)   REFERENCES modeles_documents(id)   ON DELETE RESTRICT,
    CONSTRAINT fk_demandes_traiteur FOREIGN KEY (traite_par)  REFERENCES utilisateurs(id)        ON DELETE SET NULL,
    INDEX idx_demandes_ecole_statut (ecole_id, statut),
    INDEX idx_demandes_etudiant     (etudiant_id),
    INDEX idx_demandes_date         (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 7. DOCUMENTS PDF GÉNÉRÉS
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS documents_generes (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                    CHAR(36)        NOT NULL UNIQUE COMMENT 'UUID v4 alimentant le QR code',
    ecole_id                BIGINT UNSIGNED NOT NULL,
    demande_id              BIGINT UNSIGNED NOT NULL,
    chemin_fichier          VARCHAR(255)    NOT NULL COMMENT 'Chemin relatif dans storage/app/private/',
    nom_fichier             VARCHAR(255)    NOT NULL,
    taille_octets           BIGINT UNSIGNED NOT NULL DEFAULT 0,
    hash_sha256             CHAR(64)        NOT NULL COMMENT 'Empreinte d intégrité anti-falsification',
    telechargements_count   INT UNSIGNED    DEFAULT 0,
    expire_le               TIMESTAMP       NULL COMMENT 'Date expiration; NULL = illimité',
    created_at              TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_ecole   FOREIGN KEY (ecole_id)   REFERENCES ecoles(id)              ON DELETE CASCADE,
    CONSTRAINT fk_documents_demande FOREIGN KEY (demande_id) REFERENCES demandes_documents(id)  ON DELETE CASCADE,
    INDEX idx_doc_ecole_uuid (ecole_id, uuid),
    UNIQUE KEY uq_doc_demande (demande_id) COMMENT 'Une seule génération par demande'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 8. IMPORTS CSV / EXCEL
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS imports (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                    CHAR(36)        NOT NULL UNIQUE,
    ecole_id                BIGINT UNSIGNED NOT NULL,
    utilisateur_id          BIGINT UNSIGNED NULL,
    nom_fichier_original    VARCHAR(255)    NULL,
    statut                  ENUM('en_cours','termine','erreur') DEFAULT 'en_cours',
    lignes_traitees         INT UNSIGNED    DEFAULT 0,
    lignes_ignorees         INT UNSIGNED    DEFAULT 0,
    rapport_erreurs         JSON            NULL COMMENT '[{ligne: 14, erreur: "CNE manquant"}, ...]',
    termine_le              TIMESTAMP       NULL,
    created_at              TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_imports_ecole        FOREIGN KEY (ecole_id)        REFERENCES ecoles(id)       ON DELETE CASCADE,
    CONSTRAINT fk_imports_utilisateur  FOREIGN KEY (utilisateur_id)  REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_imports_ecole (ecole_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 9. JOURNAL D'AUDIT (Traçabilité - Conformité loi 09-08)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS logs_activite (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ecole_id        BIGINT UNSIGNED  NULL COMMENT 'NULL pour actions du super admin',
    utilisateur_id  BIGINT UNSIGNED  NULL,
    action          VARCHAR(100)     NOT NULL COMMENT 'connexion, demande_document, telechargement...',
    cible_type      VARCHAR(100)     NULL COMMENT 'Nom de la classe cible (DemandeDocument, Ecole...)',
    cible_id        BIGINT UNSIGNED  NULL,
    details         JSON             NULL COMMENT 'Données contextuelles supplémentaires',
    ip_address      VARCHAR(45)      NULL COMMENT 'IPv4 ou IPv6',
    user_agent      VARCHAR(500)     NULL,
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_ecole        (ecole_id, created_at),
    INDEX idx_logs_utilisateur  (utilisateur_id),
    INDEX idx_logs_action       (action),
    CONSTRAINT fk_logs_ecole       FOREIGN KEY (ecole_id)       REFERENCES ecoles(id)       ON DELETE SET NULL,
    CONSTRAINT fk_logs_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 10. RÉINITIALISATION MOT DE PASSE
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email       VARCHAR(255) NOT NULL PRIMARY KEY,
    token       VARCHAR(255) NOT NULL COMMENT 'Token haché bcrypt',
    created_at  TIMESTAMP    NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 11. SESSIONS LARAVEL
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS sessions (
    id              VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id         BIGINT UNSIGNED NULL,
    ip_address      VARCHAR(45)  NULL,
    user_agent      TEXT         NULL,
    payload         LONGTEXT     NOT NULL,
    last_activity   INT          NOT NULL,
    INDEX idx_sessions_user_id      (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- 12. FILE D'ATTENTE (Laravel Queue - Standard)
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CREATE TABLE IF NOT EXISTS jobs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue           VARCHAR(255)    NOT NULL,
    payload         LONGTEXT        NOT NULL,
    attempts        TINYINT UNSIGNED NOT NULL,
    reserved_at     INT UNSIGNED    NULL,
    available_at    INT UNSIGNED    NOT NULL,
    created_at      INT UNSIGNED    NOT NULL,
    INDEX idx_jobs_queue (queue, reserved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid        VARCHAR(255) NOT NULL UNIQUE,
    connection  TEXT         NOT NULL,
    queue       TEXT         NOT NULL,
    payload     LONGTEXT     NOT NULL,
    exception   LONGTEXT     NOT NULL,
    failed_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache (
    `key`       VARCHAR(255) NOT NULL PRIMARY KEY,
    value       MEDIUMTEXT   NOT NULL,
    expiration  INT          NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DONNÉES DE DÉMONSTRATION (SEEDS)
-- ⚠️ IMPORTANT : Utilisez plutôt les seeders Laravel (php artisan db:seed)
-- Ces données sont fournies uniquement pour les tests initiaux
-- ========================================================

-- Les seeds ont été déplacés vers database/seeders/
-- Exécutez : php artisan db:seed pour initialiser les données

-- Admin de l'école ENS
INSERT INTO utilisateurs (uuid, ecole_id, role, matricule, nom, prenom, email, mot_de_passe, statut)
VALUES (
    '11111111-1111-1111-1111-111111111102', 1, 'admin_ecole', NULL,
    'El Mansouri', 'Karim', 'admin@ens.ma',
    '$2y$12$jCFPnqQKxQ1kC3ZE46haEe.1qimjqjvMYOFrGF1wmLoa4u0Cy/hPK', 'actif'
);

-- Étudiant test ENS (mot de passe : Secret2026!)
INSERT INTO utilisateurs (uuid, ecole_id, role, matricule, nom, prenom, email, mot_de_passe, statut)
VALUES (
    '11111111-1111-1111-1111-111111111103', 1, 'etudiant', 'E2026-9901',
    'Alami', 'Sara', 'sara.alami@ens.ma',
    '$2y$12$jCFPnqQKxQ1kC3ZE46haEe.1qimjqjvMYOFrGF1wmLoa4u0Cy/hPK', 'actif'
);

-- Données scolaires de Sara Alami
INSERT INTO etudiants_donnees (ecole_id, matricule, filiere, niveau, annee_universitaire, attributs_dynamiques)
VALUES (
    1, 'E2026-9901', 'Génie Logiciel', 'Master 2', '2025-2026',
    '{"moyenne": "17.40", "mention": "Très Bien", "session": "Principale Juin 2026", "date_deliberation": "30 Juin 2026", "rang": "1/42"}'
);

-- École 2 : POLY-2026 (pour tester l'isolation multi-tenant)
INSERT INTO ecoles (id, uuid, nom, code_unique, adresse, email_contact, couleur_primaire, couleur_secondaire, statut)
VALUES (
    2, '22222222-2222-2222-2222-222222222222',
    'Institut Polytechnique de Casablanca', 'POLY-2026',
    'Boulevard Hassan II, Casablanca 20000', 'contact@poly.ma',
    '#059669', '#047857', 'actif'
);

-- Admin de l'école Polytechnique (mot de passe : Secret2026!)
INSERT INTO utilisateurs (uuid, ecole_id, role, matricule, nom, prenom, email, mot_de_passe, statut)
VALUES (
    '22222222-2222-2222-2222-222222222202', 2, 'admin_ecole', NULL,
    'Benjelloun', 'Tariq', 'contact@poly.ma',
    '$2y$12$jCFPnqQKxQ1kC3ZE46haEe.1qimjqjvMYOFrGF1wmLoa4u0Cy/hPK', 'actif'
);

-- Modèle : Attestation de Réussite (délivrance automatique)
INSERT INTO modeles_documents (uuid, ecole_id, titre, type_document, description, contenu_html, styles_css, actif, validation_requise, langue)
VALUES (
    '11111111-1111-1111-1111-111111111120', 1,
    'Attestation de Réussite',
    'attestation_reussite',
    'Modèle officiel certifiant la validation de l''année universitaire.',
    '<div class="cert-container">
        <table class="header-table">
            <tr>
                <td class="header-left">{logo}</td>
                <td class="header-center">
                    <div class="republique">ROYAUME DU MAROC</div>
                    <div class="ministere">MINISTÈRE DE L''ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                    <div class="school-name">{nom_ecole}</div>
                    <div class="dept-name">Jury Académique d''Évaluation &amp; Délibération</div>
                </td>
                <td class="header-right">
                    <div class="academic-year">Session Pédagogique<br/><strong>Juin {annee_actuelle}</strong></div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
        <div class="title-container">
            <h1 class="doc-title">ATTESTATION DE RÉUSSITE</h1>
            <div class="doc-ref">Réf : REUSS/{annee_actuelle}/{matricule}</div>
        </div>
        <div class="body-content">
            <p class="intro-paragraph">Le Président du Jury et Directeur de l''établissement <strong>{nom_ecole}</strong>, atteste par la présente que :</p>
            <table class="student-info-table">
                <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
                <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
                <tr><td class="info-label">Filière d''études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
                <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
                <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
                <tr><td class="info-label">Résultat &amp; Mention :</td><td class="info-value"><strong>ADMIS(E) — Mention {mention}</strong> (Moyenne : <strong>{moyenne}/20</strong>)</td></tr>
            </table>
            <p class="status-paragraph">A satisfait aux examens de fin d''année et aux exigences pédagogiques requises pour la validation intégrale des modules et des crédits de la filière.</p>
            <p class="closing-paragraph">La présente attestation lui est délivrée pour servir et valoir ce que de droit.</p>
        </div>
        <table class="signatures-table">
            <tr>
                <td class="sig-col-left">
                    <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                    <div class="sig-role">Le Président du Jury</div>
                </td>
                <td class="sig-col-right">
                    <div class="sig-role">Le Directeur de l''Établissement</div>
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
    'body { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; color: #000000; margin: 0; padding: 0; }
     .cert-container { border: 3px double #000000; padding: 24px 28px; background-color: #ffffff; min-height: 880px; position: relative; }
     .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
     .header-left { width: 22%; vertical-align: middle; text-align: left; }
     .header-center { width: 56%; vertical-align: middle; text-align: center; }
     .header-right { width: 22%; vertical-align: middle; text-align: right; font-size: 10px; color: #000000; }
     .republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
     .ministere { font-size: 8px; letter-spacing: 0.5px; margin-top: 2px; text-transform: uppercase; }
     .school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.8px; }
     .dept-name { font-size: 9.5px; font-style: italic; margin-top: 2px; }
     .header-divider { border-top: 2px solid #000000; border-bottom: 1px solid #000000; height: 3px; margin: 8px 0 16px 0; }
     .title-container { text-align: center; margin-bottom: 18px; }
     .doc-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 3px 0; color: #000000; }
     .doc-ref { font-size: 9.5px; font-family: "DejaVu Sans Mono", monospace; letter-spacing: 0.5px; }
     .body-content { font-size: 13px; line-height: 1.65; margin-bottom: 16px; }
     .intro-paragraph { margin-bottom: 12px; text-align: justify; }
     .student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; }
     .student-info-table td { padding: 5px 8px; font-size: 12.5px; vertical-align: top; }
     .info-label { width: 38%; font-weight: normal; color: #222222; border-bottom: 1px dotted #888888; }
     .info-value { width: 62%; border-bottom: 1px dotted #888888; }
     .status-paragraph { margin-bottom: 12px; text-align: justify; text-indent: 20px; }
     .closing-paragraph { margin-bottom: 18px; text-align: justify; text-indent: 20px; }
     .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
     .sig-col-left { width: 50%; vertical-align: top; text-align: left; font-size: 11px; }
     .sig-col-right { width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
     .sig-role { font-weight: bold; margin-bottom: 3px; }
     .sig-sub { font-size: 10px; font-style: italic; }
     .anti-fraud-footer { margin-top: 12px; }',
    TRUE, FALSE, 'fr'
);

-- Modèle : Certificat de Scolarité (avec validation admin requise)
INSERT INTO modeles_documents (uuid, ecole_id, titre, type_document, description, contenu_html, styles_css, actif, validation_requise, langue)
VALUES (
    '11111111-1111-1111-1111-111111111121', 1,
    'Certificat de Scolarité',
    'certificat_scolarite',
    'Certifie l''inscription en cours pour l''année universitaire.',
    '<div class="cert-container">
        <table class="header-table">
            <tr>
                <td class="header-left">{logo}</td>
                <td class="header-center">
                    <div class="republique">ROYAUME DU MAROC</div>
                    <div class="ministere">MINISTÈRE DE L''ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                    <div class="school-name">{nom_ecole}</div>
                    <div class="dept-name">Direction des Études et des Affaires Académiques</div>
                </td>
                <td class="header-right">
                    <div class="academic-year">Année Universitaire<br/><strong>{annee_universitaire}</strong></div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
        <div class="title-container">
            <h1 class="doc-title">CERTIFICAT DE SCOLARITÉ</h1>
            <div class="doc-ref">Réf : SCOL/{annee_actuelle}/{matricule}</div>
        </div>
        <div class="body-content">
            <p class="intro-paragraph">Le Directeur de l''établissement <strong>{nom_ecole}</strong>, certifie par la présente que :</p>
            <table class="student-info-table">
                <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
                <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
                <tr><td class="info-label">Filière d''études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
                <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
                <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
            </table>
            <p class="status-paragraph">Est régulièrement inscrit(e) au sein de notre établissement pour l''année universitaire en cours et poursuit son cursus conformément aux dispositions et règlements pédagogiques en vigueur.</p>
            <p class="closing-paragraph">Le présent certificat lui est délivré sur sa demande pour servir et valoir ce que de droit auprès des administrations et organismes compétents.</p>
        </div>
        <table class="signatures-table">
            <tr>
                <td class="sig-col-left">
                    <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                    <div style="margin-top:10px;">
                        <div class="sig-role">Pour la Direction des Études</div>
                        <div class="sig-sub">Le Secrétariat Académique</div>
                    </div>
                </td>
                <td class="sig-col-right">
                    <div class="sig-role">Le Directeur de l''Établissement</div>
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
    'body { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; color: #000000; margin: 0; padding: 0; }
     .cert-container { border: 3px double #000000; padding: 24px 28px; background-color: #ffffff; min-height: 880px; position: relative; }
     .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
     .header-left { width: 22%; vertical-align: middle; text-align: left; }
     .header-center { width: 56%; vertical-align: middle; text-align: center; }
     .header-right { width: 22%; vertical-align: middle; text-align: right; font-size: 10px; color: #000000; }
     .republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
     .ministere { font-size: 8px; letter-spacing: 0.5px; margin-top: 2px; text-transform: uppercase; }
     .school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.8px; }
     .dept-name { font-size: 9.5px; font-style: italic; margin-top: 2px; }
     .header-divider { border-top: 2px solid #000000; border-bottom: 1px solid #000000; height: 3px; margin: 8px 0 16px 0; }
     .title-container { text-align: center; margin-bottom: 18px; }
     .doc-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 3px 0; color: #000000; }
     .doc-ref { font-size: 9.5px; font-family: "DejaVu Sans Mono", monospace; letter-spacing: 0.5px; }
     .body-content { font-size: 13px; line-height: 1.65; margin-bottom: 16px; }
     .intro-paragraph { margin-bottom: 12px; text-align: justify; }
     .student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; }
     .student-info-table td { padding: 5px 8px; font-size: 12.5px; vertical-align: top; }
     .info-label { width: 38%; font-weight: normal; color: #222222; border-bottom: 1px dotted #888888; }
     .info-value { width: 62%; border-bottom: 1px dotted #888888; }
     .status-paragraph { margin-bottom: 12px; text-align: justify; text-indent: 20px; }
     .closing-paragraph { margin-bottom: 18px; text-align: justify; text-indent: 20px; }
     .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
     .sig-col-left { width: 50%; vertical-align: top; text-align: left; font-size: 11px; }
     .sig-col-right { width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
     .sig-role { font-weight: bold; margin-bottom: 3px; }
     .sig-sub { font-size: 10px; font-style: italic; }
     .anti-fraud-footer { margin-top: 12px; }',
    TRUE, TRUE, 'fr'
);

-- Modèle École 2 (Polytechnique) : Attestation de Scolarité
INSERT INTO modeles_documents (uuid, ecole_id, titre, type_document, description, contenu_html, styles_css, actif, validation_requise, langue)
VALUES (
    '22222222-2222-2222-2222-222222222220', 2,
    'Attestation d''Inscription',
    'certificat_scolarite',
    'Attestation officielle d''inscription à l''Institut Polytechnique.',
    '<div class="cert-container">
        <table class="header-table">
            <tr>
                <td class="header-left">{logo}</td>
                <td class="header-center">
                    <div class="republique">ROYAUME DU MAROC</div>
                    <div class="ministere">MINISTÈRE DE L''ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                    <div class="school-name">{nom_ecole}</div>
                    <div class="dept-name">Bureau des Admissions &amp; Inscriptions</div>
                </td>
                <td class="header-right">
                    <div class="academic-year">Inscription<br/><strong>{annee_universitaire}</strong></div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
        <div class="title-container">
            <h1 class="doc-title">ATTESTATION D''INSCRIPTION</h1>
            <div class="doc-ref">Réf : INSC/{annee_actuelle}/{matricule}</div>
        </div>
        <div class="body-content">
            <p class="intro-paragraph">La Direction de <strong>{nom_ecole}</strong> certifie par la présente que :</p>
            <table class="student-info-table">
                <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
                <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
                <tr><td class="info-label">Filière d''études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
                <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
                <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
            </table>
            <p class="status-paragraph">L''étudiant(e) susmentionné(e) est régulièrement inscrit(e) au titre de l''année <strong>{annee_universitaire}</strong>.</p>
            <p class="closing-paragraph">La présente attestation est délivrée pour faire valoir ce que de droit.</p>
        </div>
        <table class="signatures-table">
            <tr>
                <td class="sig-col-left">
                    <div class="issue-date">Casablanca, le {date_du_jour}</div>
                    <div class="sig-role">Le Secrétariat Général</div>
                </td>
                <td class="sig-col-right">
                    <div class="sig-role">Le Directeur de l''Établissement</div>
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
    'body { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; color: #000000; margin: 0; padding: 0; }
     .cert-container { border: 3px double #000000; padding: 24px 28px; background-color: #ffffff; min-height: 880px; position: relative; }
     .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
     .header-left { width: 22%; vertical-align: middle; text-align: left; }
     .header-center { width: 56%; vertical-align: middle; text-align: center; }
     .header-right { width: 22%; vertical-align: middle; text-align: right; font-size: 10px; color: #000000; }
     .republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
     .ministere { font-size: 8px; letter-spacing: 0.5px; margin-top: 2px; text-transform: uppercase; }
     .school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.8px; }
     .dept-name { font-size: 9.5px; font-style: italic; margin-top: 2px; }
     .header-divider { border-top: 2px solid #000000; border-bottom: 1px solid #000000; height: 3px; margin: 8px 0 16px 0; }
     .title-container { text-align: center; margin-bottom: 18px; }
     .doc-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 3px 0; color: #000000; }
     .doc-ref { font-size: 9.5px; font-family: "DejaVu Sans Mono", monospace; letter-spacing: 0.5px; }
     .body-content { font-size: 13px; line-height: 1.65; margin-bottom: 16px; }
     .intro-paragraph { margin-bottom: 12px; text-align: justify; }
     .student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; }
     .student-info-table td { padding: 5px 8px; font-size: 12.5px; vertical-align: top; }
     .info-label { width: 38%; font-weight: normal; color: #222222; border-bottom: 1px dotted #888888; }
     .info-value { width: 62%; border-bottom: 1px dotted #888888; }
     .status-paragraph { margin-bottom: 12px; text-align: justify; text-indent: 20px; }
     .closing-paragraph { margin-bottom: 18px; text-align: justify; text-indent: 20px; }
     .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
     .sig-col-left { width: 50%; vertical-align: top; text-align: left; font-size: 11px; }
     .sig-col-right { width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
     .sig-role { font-weight: bold; margin-bottom: 3px; }
     .sig-sub { font-size: 10px; font-style: italic; }
     .anti-fraud-footer { margin-top: 12px; }',
    TRUE, FALSE, 'fr'
);
