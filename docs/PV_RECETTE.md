# Procès-Verbal de Recette Fonctionnelle et Technique (PV de Recette)

**Projet :** Plateforme SaaS Universelle de Documents Scolaires Sécurisés — *DocScolaire SaaS v2.0*  
**Date de recette :** 13 Octobre 2026  
**Environnement :** Ubuntu 22.04/24.04 LTS / PHP 8.3 FPM / MySQL 8.0 / Nginx  

---

## 1. Cadre du Procès-Verbal

Le présent document a pour objet de consigner les résultats des tests d'acceptation et de recette menés conjointement par le prestataire développeur et le représentant de la société cliente. La validation des critères ci-dessous atteste de la conformité de la solution livrée vis-à-vis du cahier des charges et déclenche la phase de garantie contractuelle.

---

## 2. Grille d'Évaluation des Tests de Recette

| Réf. | Scénario de Test | Résultat Attendu | Statut |
|:---|:---|:---|:---:|
| **T01** | **Isolation Multi-Établissements** | Les étudiants et administrateurs de l'école A ne peuvent en aucun cas visualiser ou modifier les données de l'école B (filtre `ecole_id` + Scopes globaux). | **CONFORME** |
| **T02** | **Accès Portail par Code Unique** | Accès via `/portal/{code_unique}` résolvant le bon établissement ou 404/redirection si le code est invalide ou suspendu. | **CONFORME** |
| **T03** | **Authentification Étudiant** | Connexion réussie par matricule/mot de passe. Limitation à 5 tentatives infructueuses (rate limiting). | **CONFORME** |
| **T04** | **Réinitialisation Mot de Passe** | Demande de token temporaire par email, formulaire de renouvellement sécurisé avec expiration à 60 minutes. | **CONFORME** |
| **T05** | **Génération Asynchrone de PDF** | Les demandes sont prises en charge en moins de 100 ms par le contrôleur, et le PDF est produit en arrière-plan par le worker Supervisor. | **CONFORME** |
| **T06** | **Protection des Fichiers PDF** | Fichiers stockés dans `storage/app/private/`. Aucun accès direct par URL sans authentification. | **CONFORME** |
| **T07** | **Empreinte Cryptographique & Intégrité** | Hash SHA-256 généré et scellé pour chaque document dans la table `documents_generes`. | **CONFORME** |
| **T08** | **Vérification Publique QR Code** | La page `/verifier/{uuid}` affiche le certificat d'authenticité avec statut vert, nom de l'étudiant et école émettrice. | **CONFORME** |
| **T09** | **Gestion des Modèles de Documents** | L'administrateur crée et modifie des modèles HTML/CSS avec balises dynamiques (`{nom}`, `{moyenne}`, `{qr_code}`...). | **CONFORME** |
| **T10** | **Validation Manuelle & Refus avec Motif** | Possibilité d'exiger une validation préalable. Le refus exige un motif textuel envoyé par email à l'étudiant. | **CONFORME** |
| **T11** | **Importation Massive CSV** | Import des données scolaires avec contrôle de format, encodage UTF-8 et journal d'erreurs détaillé ligne par ligne. | **CONFORME** |
| **T12** | **Piste d'Audit & Journalisation** | Inscription automatique dans `logs_activite` de toutes les connexions, téléchargements et validations (Conformité Loi 09-08). | **CONFORME** |
| **T13** | **Panneau Super Administrateur** | Création de nouvelles écoles avec compte administrateur initial, suspension en un clic et statistiques consolidées. | **CONFORME** |
| **T14** | **PWA Mobile (Écran d'Accueil)** | Détection du manifeste PWA (`manifest.json`) et du Service Worker (`sw.js`) pour installation directe sur smartphone. | **CONFORME** |
| **T15** | **Script de Déploiement Ubuntu** | Script `scripts/deploy_ubuntu.sh` automatisant l'installation de Nginx, PHP 8.3, MySQL, Supervisor, UFW et des sauvegardes cron. | **CONFORME** |

---

## 3. Synthèse de la Recette

- **Nombre total de cas de tests validés :** 15 / 15
- **Taux de conformité :** 100 %
- **Bloquants identifiés :** 0
- **Anomalies critiques :** 0

---

## 4. Décision de Réception

- [x] **Réception Définitive sans Réserve** : L'application répond en tous points aux spécifications techniques et fonctionnelles convenues. La livraison est déclarée complète et parfaite.
- [ ] **Réception avec Réserves mineures** *(non applicable)*.
- [ ] **Refus de Réception** *(non applicable)*.

---

## 5. Signatures des Parties

*Fait en deux exemplaires originaux le 13/10/2026 à Casablanca.*

**Pour le Prestataire Développeur :**  
Nom : Ayoub  
Signature : *(Signé électroniquement)*  

**Pour la Société Cliente / Commanditaire :**  
Nom & Qualité : ___________________________  
Signature & Cachet :  
