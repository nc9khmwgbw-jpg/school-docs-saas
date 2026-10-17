# Architecture Technique — DocScolaire SaaS v2.0

## 1. Vue d'Ensemble

DocScolaire SaaS est une plateforme web multi-tenant conçue pour automatiser la génération, la sécurisation et la délivrance de documents scolaires officiels (attestations, certificats, relevés de notes) au format PDF avec QR code de vérification publique.

```
                           ┌──────────────────────────┐
                           │      Navigateur Web      │
                           │  (Mobile PWA / Desktop)  │
                           └─────────────┬────────────┘
                                         │ HTTPS (Port 443)
                                         ▼
                           ┌──────────────────────────┐
                           │     Serveur Web Nginx    │
                           │  (SSL, Headers, RateLim) │
                           └─────────────┬────────────┘
                                         │ FastCGI
                                         ▼
                           ┌──────────────────────────┐
                           │    Back-End PHP 8.3 FPM  │
                           │     (Laravel Framework)  │
                           └──────┬─────────────┬─────┘
                                  │             │
                Stockage SQL      │             │ Tâche Asynchrone
                (Isolation Tenant)▼             ▼ (Queue Database)
       ┌────────────────────────────┐    ┌────────────────────────────┐
       │   MySQL 8.0 (InnoDB utf8)  │    │  Supervisor Worker PHP-FPM │
       │  ecoles, utilisateurs,     │    │  Génération PDF (DomPDF)   │
       │  modeles, logs_activite... │    │  Calcul Hash SHA-256       │
       └────────────────────────────┘    └──────────────┬─────────────┘
                                                        │
                                                        ▼ Stockage Privé
                                         ┌────────────────────────────┐
                                         │   storage/app/private/     │
                                         │   (Accès contrôlé via PHP) │
                                         └────────────────────────────┘
```

---

## 2. Cloisonnement Multi-Tenant Étanche

L'isolation des établissements scolaires repose sur un principe de **Single Database / Shared Schema avec Row-Level Security** :

1. **Identification du Tenant (`IdentifyTenant` middleware)** :
   - Chaque école dispose d'un identifiant unique (ex: `ENS-2026`).
   - L'URL du portail étudiant est de la forme `/portal/{code_unique}`.
   - Le middleware résout l'entité `Ecole` correspondante ou renvoie une erreur 404 si l'établissement est introuvable ou suspendu.

2. **Scope Global Automatique (`TenantScope` & trait `BelongsToTenant`)** :
   - Tous les modèles sensibles (`Utilisateur`, `ModeleDocument`, `DemandeDocument`, `DocumentGenere`, `EtudiantDonnee`, `Resultat`, `Import`, `LogActivite`) implémentent le trait `BelongsToTenant`.
   - À chaque requête SQL `SELECT`, `UPDATE` ou `DELETE`, Laravel injecte automatiquement la condition `WHERE ecole_id = ?`.
   - Il est **techniquement impossible** qu'un administrateur d'une école accède aux données d'une autre école, même en modifiant manuellement les identifiants dans la barre d'adresse.

3. **Protection contre l'énumération (UUID v4)** :
   - Tous les identifiants exposés publiquement sont des identifiants aléatoires UUID v4 de 128 bits (ex: `11111111-1111-1111-1111-111111111111`).
   - Les clés primaires séquentielles (`id AUTO_INCREMENT`) restent strictement internes et ne sont jamais exposées dans les URLs publiques.

---

## 3. Stockage et Sécurisation des Documents PDF

1. **Stockage Hors Racine Web (Private Storage)** :
   - Les fichiers PDF générés sont enregistrés dans `storage/app/private/documents/{ecole_id}/{hash}.pdf`.
   - Ce dossier n'est **jamais accessible directement** via une URL HTTP.
   - Le téléchargement s'effectue exclusivement au travers de la route authentifiée `tenant.documents.download`, qui vérifie :
     - Que l'utilisateur est connecté,
     - Qu'il appartient bien à l'école propriétaire du document,
     - Que le document n'est pas expiré (`expire_le`).

2. **Empreinte d'Intégrité Anti-Falsification (SHA-256)** :
   - Lors de la génération du PDF, une empreinte cryptographique SHA-256 est calculée sur le flux binaire brut et stockée dans la table `documents_generes.hash_sha256`.
   - Lors de la vérification publique, l'empreinte permet d'attester que le fichier n'a subi aucune modification a posteriori.

3. **Vérification Publique par QR Code** :
   - Chaque PDF généré embarque un QR Code pointant vers l'URL `/verifier/{uuid}`.
   - Cette route est publique et ne nécessite pas de compte utilisateur.
   - Elle affiche le statut d'authenticité (Valide, Expiré ou Révoqué), le nom de l'étudiant, le type de document et l'établissement émetteur.
   - Protégée par rate-limiting (60 requêtes/minute/IP) pour prévenir tout balayage automatisé.

---

## 4. Traitement Asynchrone de Génération PDF

La génération d'un PDF vectoriel (parsing CSS, calcul de mise en page, embedding de polices) prend entre 300 ms et 1 500 ms. Afin d'éviter tout blocage de l'interface utilisateur, la génération est déportée en arrière-plan :

1. L'étudiant soumet sa demande via `/portal/{code}/demandes`.
2. Le contrôleur crée l'enregistrement `demandes_documents` en statut `en_attente` (ou `en_traitement`).
3. La tâche `GenerateDocumentPdfJob` est placée dans la table `jobs`.
4. La réponse HTTP est renvoyée à l'étudiant en moins de **50 millisecondes**.
5. Deux processus d'arrière-plan supervisés par **Supervisor** dépilent les tâches en continu :
   - Lecture du modèle et des données académiques.
   - Fusion HTML via `TemplateEngineService`.
   - Rendu binaire via `PdfGeneratorService` (DomPDF).
   - Enregistrement dans `documents_generes` et notification email.

---

## 5. Journalisation d'Audit & Conformité Légale (Loi 09-08 / CNDP)

La table `logs_activite` enregistre de façon immuable :
- `ecole_id` : établissement concerné (ou NULL pour SuperAdmin)
- `utilisateur_id` : acteur de l'action
- `action` : type d'opération (`connexion`, `demande_document`, `validation`, `refus`, `telechargement`, `import_csv`...)
- `cible_type` & `cible_id` : entité concernée
- `ip_address` : adresse IP IPv4/IPv6 de la requête
- `details` : métadonnées au format JSON (ex: motif de refus, nom du fichier importé)
- `created_at` : horodatage certifié du serveur

---

## 6. Progressive Web App (PWA)

L'application respecte les standards PWA :
- Fichier `public/manifest.json` déclarant le nom, les couleurs et le mode `standalone`.
- Fichier `public/sw.js` (Service Worker) assurant la mise en cache des assets statiques et l'interception réseau.
- Permet l'affichage de l'invite *"Ajouter à l'écran d'accueil"* sur iOS (Safari) et Android (Chrome), offrant l'apparence d'une application native sans dépendre d'un app store payant.
