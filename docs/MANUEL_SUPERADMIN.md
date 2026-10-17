# Manuel Super Administrateur — Propriétaire de la Plateforme SaaS

Ce guide s'adresse au propriétaire et aux exploitants techniques de la plateforme **DocScolaire SaaS** pour la gestion multi-tenant, le provisionnement d'établissements et la supervision globale du système.

---

## 1. Connexion au Panneau de Contrôle Global

1. Rendez-vous sur `https://plateforme.com/superadmin/login`.
2. Saisissez vos identifiants Super Administrateur (`superadmin@plateforme-saas.com`).
3. Le tableau de bord affiche les indicateurs macro-économiques de la plateforme :
   - Nombre total d'établissements partenaires,
   - Nombre total de comptes étudiants hébergés,
   - Volume total de documents PDF certifiés générés,
   - Demandes traitées ce mois-ci et graphique de tendance mensuelle.

---

## 2. Déployer et Provisionner un Nouvel Établissement (Tenant Onboarding)

1. Dans le menu de navigation, cliquez sur **« ➕ Nouvelle école »** (ou depuis la liste des écoles).
2. Remplissez les deux blocs de configuration :
   - **Identité de l'établissement** :
     - *Nom officiel* : ex. *Institut Supérieur de Management (ISM)*
     - *Code Unique* : ex. *ISM-2026* (identifiant alphanumérique saisi par les étudiants)
     - *Email de contact* et coordonnées
     - *Couleur thématique*
   - **Administrateur Principal de l'École** :
     - Nom, Prénom, Email professionnel
     - Mot de passe temporaire (minimum 10 caractères)
3. Cliquez sur **« 🚀 Provisionner et activer l'école »**.
4. En **1 seconde**, le système :
   - Crée l'espace isolé avec son UUID v4 unique,
   - Génère le compte administrateur avec mot de passe haché par bcrypt,
   - Initialise le dossier de stockage privé dédié (`storage/app/private/documents/{ecole_id}/`),
   - Active immédiatement le portail étudiant à l'adresse `https://plateforme.com/portal/ISM-2026`.

---

## 3. Gestion et Contrôle des Écoles (Activer / Suspendre)

Dans le menu de navigation, cliquez sur **« 🏫 Toutes les écoles »** :
- **Voir la fiche détaillée** : cliquez sur **« 👁️ Détails »** pour inspecter la volumétrie, les quotas de stockage et le journal d'activité de l'école.
- **Suspendre un établissement** : en cas d'impayé d'abonnement ou de résiliation, cliquez sur **« Suspendre »**.
  - L'accès au portail étudiant et au tableau de bord administrateur est instantanément verrouillé.
  - Toutes les données restent conservées de manière sécurisée en base.
- **Réactiver un établissement** : un simple clic sur **« Activer »** rétablit l'accès instantanément sans aucune perte de données.

---

## 4. Statistiques et Analytics SaaS

Dans le menu de navigation, cliquez sur **« 📈 Statistiques »** :
- Suivez la consommation en volume de chaque école.
- Identifiez les établissements les plus actifs pour adapter les forfaits de facturation (post-MVP).
- Visualisez les barres de progression de la part relative de documents générés par chaque client.

---

## 5. Piste d'Audit Globale & Sécurité

Dans le menu de navigation, cliquez sur **« 📜 Journal d'audit »** :
- Visualisez les actions en temps réel sur l'ensemble de la plateforme avec filtre par établissement.
- Traçabilité complète des adresses IP, des tentatives d'accès et des générations de documents certifiés.
