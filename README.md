# Plateforme SaaS Universelle de Documents Scolaires Sécurisés (DocScolaire SaaS v2.0)

[![Statut](https://img.shields.io/badge/Statut-Pr%C3%AAt_%C3%A0_Livrer-34d399.svg)](#)
[![Multi-Tenant](https://img.shields.io/badge/Architecture-Multi--Tenant_Isol%C3%A9-2563eb.svg)](#)
[![PHP](https://img.shields.io/badge/PHP-8.3_FPM-7c3aed.svg)](#)
[![Conformit%C3%A9](https://img.shields.io/badge/CNDP-Loi_09--08-059669.svg)](#)

Plateforme universelle web et mobile (PWA) permettant à tout établissement scolaire ou universitaire de délivrer à ses étudiants des documents certifiés en PDF (attestations de réussite, certificats de scolarité, relevés de notes) avec **QR code d'authentification publique**, **génération asynchrone par worker d'arrière-plan** et **cloisonnement multi-écoles étanche**.

---

## 1. Vue d'Ensemble & Fonctionnalités Réalisées

| Module | Fonctionnalités |
| :--- | :--- |
| **Cloisonnement Multi-Tenant** | Isolation stricte par `ecole_id`, scope applicatif automatique `TenantScope`, masquage des IDs par **UUID v4**, URLs dédiées `/portal/{code_unique}`. |
| **Portail Étudiant (PWA)** | Interface responsive, ajout sur l'écran d'accueil sans Store (`manifest.json` + `sw.js`), formulaires de demande, historique paginé avec motifs de refus, téléchargement PDF sécurisé, réinitialisation de mot de passe par email. |
| **Administration École** | Gestionnaire complet : création/édition de gabarits HTML/CSS avec balises dynamiques (`{nom}`, `{moyenne}`, `{qr_code}`...), validation/refus avec motif obligatoire, génération par lots, import CSV avec rapport d'erreurs ligne par ligne, personnalisation du logo et charte graphique, journal d'audit interne. |
| **Super Administration** | Provisionnement instantané d'une nouvelle école et de son admin, suspension/activation en un clic, statistiques consolidées et volumétrie par école, audit log global plateforme. |
| **Sécurité & Anti-Falsification** | Stockage privé hors racine web (`storage/app/private/`), empreinte cryptographique **SHA-256**, vérification publique par QR Code `/verifier/{uuid}` avec rate-limiting, traçabilité immuable (`logs_activite`) conforme à la **Loi 09-08 (CNDP)**. |
| **Haute Performance** | Traitement asynchrone non-bloquant (< 50 ms pour l'utilisateur) avec dépilage en arrière-plan via **Supervisor**. |

---

## 2. Dossier de Livraison Complet

Le projet comprend l'ensemble des livrables contractuels pour une autonomie totale du client :

```text
school-docs-saas/
├── scripts/
│   ├── deploy_ubuntu.sh         # Script d'installation automatique en production sur Ubuntu Server
│   ├── backup.sh                # Script de sauvegarde MySQL + PDFs avec rotation 14 jours
│   └── demo_scenario.sh         # Scénario guidé de démonstration avec 2 écoles pré-configurées
├── docs/
│   ├── ARCHITECTURE.md          # Architecture technique détaillée & sécurité multi-tenant
│   ├── MANUEL_ETUDIANT.md       # Manuel d'utilisation du portail étudiant et installation PWA
│   ├── MANUEL_ADMIN_ECOLE.md    # Manuel de gestion pour l'administrateur d'établissement
│   ├── MANUEL_SUPERADMIN.md     # Manuel du propriétaire SaaS pour la supervision multi-écoles
│   ├── DEPLOIEMENT_UBUNTU.md    # Guide pas à pas de déploiement et maintenance Ubuntu
│   └── PV_RECETTE.md            # Procès-verbal formel de recette technique et fonctionnelle
├── database/
│   └── sql/
│       └── schema.sql           # Schéma relationnel complet v2.0 + données de démonstration
├── docker/                      # Environnement conteneurisé Nginx + PHP 8.3 + MySQL 8 + Supervisor
├── docker-compose.yml
├── sample_etudiants.csv         # Fichier CSV d'exemple pour l'import de données
└── routes/web.php               # Routage complet des 5 parcours utilisateurs
```

---

## 3. Déploiement Rapide

### Option A : Déploiement en Production sur Ubuntu Server (Bare-Metal / VPS)
```bash
sudo chmod +x scripts/deploy_ubuntu.sh
sudo ./scripts/deploy_ubuntu.sh docscolaire.mondomaine.com
```
*Le script installe et configure Nginx, PHP 8.3 FPM, MySQL 8, Supervisor, UFW et le cron de sauvegarde quotidienne.*

### Option B : Déploiement via Docker Compose
```bash
docker compose up -d --build
```
- **Portail Web :** `http://localhost:8080`
- **Base MySQL :** `localhost:3306` (`saas_documents` / `saas_user` / `saas_password`)

---

## 4. Comptes de Démonstration & Scénario de Test

Exécutez `scripts/demo_scenario.sh` ou utilisez les accès pré-configurés :

| Rôle | URL d'accès | Identifiant | Mot de passe | Description |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `/superadmin/login` | `superadmin@plateforme-saas.com` | `Secret2026!` | Panneau global : gestion des écoles, statistiques et journal d'audit. |
| **Admin École 1 (ENS)** | `/admin-ecole` | `admin@ens.ma` | `Secret2026!` | Gestionnaire de l'ENS : validation de demandes, gabarits, import CSV. |
| **Étudiante École 1** | `/portal/ENS-2026` | Matricule : `E2026-9901` | `Secret2026!` | Sara Alami : demande d'attestation, téléchargement PDF officiel. |
| **École 2 (Polytechnique)** | `/portal/POLY-2026` | `contact@poly.ma` | `Secret2026!` | Établissement isolé pour valider l'étanchéité multi-tenant. |
| **Vérification QR Code** | `/verifier/{uuid}` | Public (scan QR) | *Aucun* | Page officielle de contrôle de validité et d'authenticité. |

---

## 5. Sauvegardes & Restauration

- **Fréquence :** Quotidienne à 02h00 via cron (`/usr/local/bin/docscolaire-backup.sh`).
- **Emplacement :** `/var/backups/docscolaire/`.
- **Restauration :**
  ```bash
  # Base de données
  gunzip < /var/backups/docscolaire/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u saas_user -p saas_documents
  # Fichiers privés
  tar -xzf /var/backups/docscolaire/storage_YYYYMMDD_HHMMSS.tar.gz -C /var/www/docscolaire/storage/app/private/
  ```

---

## 6. Conformité Réglementaire & Sécurité
- **Loi 09-08 (Maroc / CNDP) :** Journalisation d'audit immuable (`logs_activite`), droit d'accès et de rectification, durées d'expiration des tokens de session et mots de passe.
- **Protection Anti-Falsification :** Signature SHA-256 vérifiable pour chaque document PDF émis.
- **Isolation :** Row-Level Tenant Security + masquage UUID v4.
