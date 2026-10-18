# 🌐 Guide Complet de Mise en Production Réelle (DevOps, Domaine, SSL & Backups)

Ce guide détaille pas à pas comment déployer **DocScolaire SaaS** sur un serveur VPS de production accessible mondialement avec votre propre nom de domaine et un certificat SSL Let's Encrypt sécurisé (HTTPS).

---

## 📋 Table des Matières

1. [Spécifications & Choix du VPS](#1-spécifications--choix-du-vps)
2. [Configuration du Nom de Domaine (DNS)](#2-configuration-du-nom-de-domaine-dns)
3. [Préparation du Serveur VPS (Ubuntu 22.04 / 24.04)](#3-préparation-du-serveur-vps-ubuntu-2204--2404)
4. [Déploiement Automatisé avec Docker & SSL](#4-déploiement-automatisé-avec-docker--ssl)
5. [Mises à Jour Continues (Déploiement en 1 Clic)](#5-mises-à-jour-continues-déploiement-en-1-clic)
6. [Plan de Sauvegarde & Disaster Recovery](#6-plan-de-sauvegarde--disaster-recovery)
7. [Checklist Finale Avant Lancement](#7-checklist-finale-avant-lancement)

---

## 1. Spécifications & Choix du VPS

| Ressource | Minimum Recommandé | Idéal (10 à 50 écoles) |
|---|---|---|
| **CPU** | 2 vCPU | 4 vCPU |
| **RAM** | 2 Go RAM (avec 2 Go Swap) | 4 Go à 8 Go RAM |
| **Stockage** | 30 Go SSD NVMe | 80 Go SSD NVMe |
| **Système** | Ubuntu 22.04 LTS ou 24.04 LTS | Ubuntu 24.04 LTS 64-bit |

### Hébergeurs recommandés (Budget ~4€ à 12€/mois) :
- **Hetzner Cloud** : Modèle *CX22* (2 vCPU, 4 Go RAM) ~3.99€/mois (Excellent rapport qualité/prix).
- **OVHcloud** : Modèle *VPS Starter* ou *Value* (Centres de données en France / Europe).
- **DigitalOcean** : Droplet *Basic 2GB RAM* ~12$/mois.
- **Hostinger / Contabo** : VPS Cloud économiques.

---

## 2. Configuration du Nom de Domaine (DNS)

Chez votre registrar (ex: Namecheap, OVH, Cloudflare, Gandi, GoDaddy), ajoutez les enregistrements DNS pointant vers l'adresse IP publique de votre VPS :

| Type | Nom / Hôte | Valeur / Cible | TTL |
|---|---|---|---|
| **A** | `@` *(ou votre sous-domaine ex: `scolarite`)* | `ADRESSE_IP_DU_VPS` | Automatique (ou 300s) |
| **A** | `www` *(si domaine principal)* | `ADRESSE_IP_DU_VPS` | Automatique (ou 300s) |

> 💡 **Vérification rapide** : Sur votre terminal local, testez la propagation DNS avec :
> ```bash
> ping scolarite.votre-domaine.com
> ```
> Dès que l'IP affichée est celle de votre VPS, vous pouvez passer à l'étape suivante.

---

## 3. Préparation du Serveur VPS (Ubuntu 22.04 / 24.04)

Connectez-vous en SSH à votre serveur :
```bash
ssh root@ADRESSE_IP_DU_VPS
```

### Étape 3.1 : Mise à jour du système et pare-feu sécurisé
```bash
# Mise à jour des paquets
apt update && apt upgrade -y
apt install -y curl wget git unzip ufw fail2ban

# Configuration du pare-feu UFW
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP (Challenge Let's Encrypt & Redirection)
ufw allow 443/tcp   # HTTPS Sécurisé
ufw --force enable
```

### Étape 3.2 : Installation officielle de Docker & Docker Compose
```bash
# Installation automatisée du moteur Docker officiel
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
systemctl enable docker
systemctl start docker

# Vérification
docker --version
docker compose version
```

---

## 4. Déploiement Automatisé avec Docker & SSL

### Étape 4.1 : Cloner le dépôt de votre SaaS
```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/nc9khmwgbw-jpg/school-docs-saas.git
cd school-docs-saas
```

### Étape 4.2 : Configurer les variables d'environnement de production
```bash
cp .env.production.example .env
nano .env
```

Renseignez les variables clés :
```env
APP_NAME="DocScolaire SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://scolarite.votre-domaine.com

DB_PASSWORD=VotreMotDePasseMySQLTresSecret
DB_ROOT_PASSWORD=VotreMotDePasseRootTresSecret

# Configuration SMTP pour les emails réels (ex: Brevo / Resend / Google)
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@domaine.com
MAIL_PASSWORD=votre-cle-smtp
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
```

### Étape 4.3 : Générer le certificat SSL Let's Encrypt en une commande
Exécutez le script d'initialisation SSL (fournissez votre nom de domaine et votre email) :
```bash
chmod +x deploy.sh scripts/*.sh
./scripts/init_ssl.sh scolarite.votre-domaine.com contact@votre-domaine.com
```
Ce script :
1. Démarre temporairement Nginx pour répondre au challenge ACME.
2. Obtient automatiquement les certificats officiels Let's Encrypt 4096-bit.
3. Active le protocole HTTPS et planifie le renouvellement automatique toutes les 12 heures.

### Étape 4.4 : Lancer le déploiement de production
```bash
./deploy.sh
```
Ce script effectue automatiquement :
- La construction des images optimisées (PHP 8.3 FPM + OPcache JIT + Nginx).
- L'isolation stricte des conteneurs (MySQL et Redis inaccessibles depuis Internet).
- L'exécution sécurisée des migrations de schéma SQL.
- La compilation et mise en cache des routes, configurations et vues Blade.
- Le démarrage du worker Supervisor pour la génération instantanée des PDF et QR codes.
- Le lancement du cron interne Laravel pour le traitement planifié.

---

## 5. Mises à Jour Continues (Déploiement en 1 Clic)

Dès que vous poussez de nouvelles fonctionnalités ou des correctifs sur votre dépôt GitHub, mettez à jour votre serveur de production sans aucune interruption de service en exécutant simplement :

```bash
cd /var/www/school-docs-saas
./deploy.sh
```

Le script s'occupe de tout :
1. Sauvegarde préventive automatique de la base de données.
2. `git pull origin main`.
3. Application des nouvelles migrations éventuelles (`migrate --force`).
4. Recompilation des caches Blade et routes.
5. Rechargement transparent des workers (`queue:restart`).

---

## 6. Plan de Sauvegarde & Disaster Recovery

### Sauvegarde automatique quotidienne (Cron)
Sur le serveur VPS, ajoutez une tâche planifiée exécutée chaque nuit à 03h00 :
```bash
crontab -e
```
Ajoutez la ligne suivante :
```cron
0 3 * * * /var/www/school-docs-saas/scripts/backup.sh >> /var/log/docscolaire_backup.log 2>&1
```

- Chaque sauvegarde crée une archive chiffrée avec checksum SHA-256 dans `/var/www/school-docs-saas/storage/app/backups/`.
- La rotation automatique purge les archives de plus de 14 jours pour ne jamais saturer votre disque.

### Restauration d'urgence en cas de sinistre (Disaster Recovery)
Pour restaurer la plateforme entière et tous les documents émis :
```bash
cd /var/www/school-docs-saas
./scripts/restore.sh storage/app/backups/backup_docscolaire_XXXXXXXX_XXXXXX.tar.gz
```

---

## 7. Checklist Finale Avant Lancement

- [ ] L'URL `https://scolarite.votre-domaine.com` s'ouvre avec le cadenas vert SSL (Grade A+ SSL Labs).
- [ ] La redirection `http://` vers `https://` fonctionne automatiquement.
- [ ] La connexion Super Admin (`/superadmin/login`) est active et sécurisée.
- [ ] L'import CSV des étudiants fonctionne via l'espace Admin École.
- [ ] Les documents générés portent le QR code scannable renvoyant vers l'URL officielle de vérification.
- [ ] Les emails de notification de documents sont bien reçus dans la boîte de réception des étudiants.
- [ ] Le pare-feu UFW ne laisse ouverts que les ports 22, 80 et 443.
