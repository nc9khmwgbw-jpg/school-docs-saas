# Manuel Administrateur — Espace École

Ce guide s'adresse aux équipes administratives et pédagogiques en charge de la gestion des documents officiels pour leur établissement sur la plateforme **DocScolaire SaaS**.

---

## 1. Connexion à l'Espace Administration

1. Rendez-vous sur `https://plateforme.com/admin-ecole`.
2. Saisissez vos identifiants d'administrateur d'école (email et mot de passe).
3. Le tableau de bord affiche immédiatement les indicateurs clés (KPIs) :
   - Nombre de modèles configurés,
   - Nombre d'étudiants enregistrés,
   - Demandes en attente de traitement,
   - Documents générés et délivrés ce mois.

---

## 2. Traiter les Demandes des Étudiants

Dans le menu de gauche, cliquez sur **« 📋 Demandes »** :

### Validation Individuelle
1. Repérez les demandes avec le badge orange **« En attente »**.
2. Cliquez sur le bouton vert **« ✓ Valider »**.
3. Le worker d'arrière-plan génère immédiatement le PDF sécurisé.
4. Un email de confirmation est automatiquement transmis à l'étudiant avec le lien de téléchargement.

### Refus avec Motif Obligatoire
1. Pour refuser une demande (ex: scolarité non régularisée, pièce manquante), cliquez sur le bouton rouge **« ✕ Refuser »**.
2. Une fenêtre modale apparaît.
3. Saisissez le **motif du refus**. Ce motif est contractuel et sera notifié par email à l'étudiant.
4. Cliquez sur **« Confirmer le refus »**.

### Traitement Automatique en Masse
- Si vous avez de nombreuses demandes pour des modèles ne nécessitant pas de validation manuelle, cliquez sur **« ⚡ Générer tous les documents automatiques »** pour lancer le traitement asynchrone par lots.

---

## 3. Gestion des Modèles de Documents (Templates)

Dans le menu de gauche, cliquez sur **« 📄 Modèles »** :

### Créer un Nouveau Modèle
1. Cliquez sur **« ➕ Créer un modèle »**.
2. Renseignez :
   - **Titre** : ex. *Attestation de Réussite Licence*.
   - **Type de document** : sélection dans la liste officielle.
   - **Validation requise** : cochez si l'administration doit vérifier chaque demande avant génération du PDF.
   - **Durée de validité** : nombre de jours (laissez vide pour illimité).
3. **Contenu HTML & Variables Dynamiques** :
   Vous pouvez insérer les balises automatiques suivantes par simple clic :
   - `{nom}`, `{prenom}`, `{matricule}`
   - `{filiere}`, `{niveau}`, `{annee_universitaire}`
   - `{moyenne}`, `{mention}`
   - `{nom_ecole}`, `{date_du_jour}`
   - `{qr_code}` : génère le bloc sécurisé de vérification numérique.
4. Cliquez sur **« Enregistrer le modèle »**.

### Tester un Modèle
- Cliquez sur **« 👁️ Aperçu »** pour visualiser le rendu A4 en conditions réelles avec des données étudiantes fictives et tester l'impression.

---

## 4. Importation Massive des Étudiants (Fichier CSV)

Dans le menu de gauche, cliquez sur **« 📥 Import CSV »** :

1. Cliquez sur **« 📄 Télécharger le fichier CSV exemple »** pour obtenir la structure exacte.
2. Le fichier CSV doit comporter les colonnes suivantes :
   `matricule,filiere,niveau,annee_universitaire,moyenne,mention`
3. Sélectionnez votre fichier CSV et cliquez sur **« 🚀 Lancer l'importation »**.
4. Le système contrôle automatiquement :
   - L'encodage (UTF-8, ISO-8859-1),
   - La présence du matricule,
   - La validité des notes (entre 0.00 et 20.00).
5. Un **rapport détaillé ligne par ligne** affiche les fiches créées et les éventuelles lignes rejetées avec la cause exacte.

---

## 5. Personnalisation de l'Établissement

Dans le menu de gauche, cliquez sur **« ⚙️ Paramètres »** :
- **Logo de l'école** : téléversez votre logo officiel au format PNG ou SVG.
- **Couleurs de la marque** : choisissez votre couleur primaire et secondaire via le sélecteur visuel.
- **Lien du portail** : copiez l'URL officielle de votre portail étudiant pour l'intégrer à votre site web.

---

## 6. Journal d'Audit & Traçabilité (Conformité Loi 09-08)

Dans le menu de gauche, cliquez sur **« 📜 Journal d'audit »** :
- Consultez l'historique complet et inaltérable de toutes les opérations : qui a demandé quel document, quand la demande a été validée, qui a téléchargé le PDF et depuis quelle adresse IP.
