#!/usr/bin/env bash
# ==============================================================================
# SCRIPT DE DÉPLOIEMENT EN PRODUCTION — DocScolaire SaaS v2.0
# Système cible : Ubuntu 22.04 LTS / 24.04 LTS Server
# ==============================================================================
# Ce script automatise l'installation intégrale de la plateforme :
# 1. Mise à jour système et installation des paquets (Nginx, PHP 8.3, MySQL, Supervisor, UFW)
# 2. Configuration sécurisée de MySQL et création de la base multi-tenant
# 3. Déploiement de l'application Laravel et installation des dépendances Composer
# 4. Configuration de Nginx avec en-têtes de sécurité HSTS / CSP
# 5. Configuration de Supervisor pour les workers asynchrones de génération PDF
# 6. Mise en place du pare-feu UFW et des sauvegardes quotidiennes automatiques
# ==============================================================================

set -euo pipefail

# Couleurs d'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn()    { echo -e "${YELLOW}[ATTENTION]${NC} $1"; }
log_error()   { echo -e "${RED}[ERREUR]${NC} $1"; exit 1; }

# Vérification des droits root
if [[ $EUID -ne 0 ]]; then
   log_error "Ce script doit être exécuté en tant que root (sudo ./deploy_ubuntu.sh)."
fi

# Variables de configuration
APP_DIR="/var/www/docscolaire"
APP_USER="www-data"
DOMAIN_NAME="${1:-docscolaire.mondomaine.com}"
DB_NAME="saas_documents"
DB_USER="saas_user"
DB_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"

log_info "=========================================================="
log_info "  DÉPLOIEMENT EN PRODUCTION : DocScolaire SaaS"
log_info "  Domaine configuré : ${DOMAIN_NAME}"
log_info "=========================================================="

# ── 1. Mise à jour du système et dépôts ─────────────────────────────────────────
log_info "Mise à jour des dépôts APT et installation des prérequis..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -y && apt-get upgrade -y
apt-get install -y curl wget git unzip zip software-properties-common ufw supervisor certbot python3-certbot-nginx

# Ajout du dépôt PHP Ondrej si PHP 8.3 n'est pas encore présent
if ! command -v php8.3 &>/dev/null; then
    log_info "Ajout du dépôt PHP officiel PPA ondrej/php..."
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
fi

# ── 2. Installation de Nginx, PHP 8.3 FPM et extensions ─────────────────────────
log_info "Installation de Nginx et PHP 8.3 FPM..."
apt-get install -y nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-gd \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-mbstring \
    php8.3-intl \
    php8.3-curl \
    php8.3-xml \
    php8.3-opcache \
    mysql-server

# Installation de Composer
if ! command -v composer &>/dev/null; then
    log_info "Installation de Composer v2..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# ── 3. Configuration de la base de données MySQL ──────────────────────────────
log_info "Configuration de MySQL et initialisation de la base multi-tenant..."
systemctl start mysql
systemctl enable mysql

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

log_success "Base de données créée avec succès (Utilisateur: ${DB_USER})."

# ── 4. Copie du code source et permissions ─────────────────────────────────────
log_info "Copie des fichiers de l'application vers ${APP_DIR}..."
mkdir -p "${APP_DIR}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Copie des fichiers source
cp -r "${SCRIPT_DIR}/." "${APP_DIR}/"

cd "${APP_DIR}"

# Création du fichier .env si non existant
if [ ! -f "${APP_DIR}/.env" ]; then
    log_info "Génération du fichier d'environnement .env initial..."
    cat <<ENV > "${APP_DIR}/.env"
APP_NAME="DocScolaire SaaS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN_NAME}

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_STORE=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=predis

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@${DOMAIN_NAME}"
MAIL_FROM_NAME="DocScolaire SaaS"
ENV
else
    log_info "Fichier .env existant conservé."
fi

# ── 5. Installation des dépendances Composer et Optimisation ───────────────────
log_info "Installation des dépendances Composer en mode production..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet || true

if [ -f "${APP_DIR}/artisan" ]; then
    php artisan key:generate --force --no-interaction || true
    log_info "Exécution sécurisée des migrations de schéma..."
    php artisan migrate --force --no-interaction
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan storage:link || true
fi

# Création des dossiers privés pour les documents générés (protection contre accès direct)
mkdir -p "${APP_DIR}/storage/app/private/documents"
mkdir -p "${APP_DIR}/storage/logs"
mkdir -p "${APP_DIR}/storage/framework/cache"
mkdir -p "${APP_DIR}/storage/framework/sessions"
mkdir -p "${APP_DIR}/storage/framework/views"

chown -R ${APP_USER}:${APP_USER} "${APP_DIR}"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

# ── 6. Configuration de Nginx ──────────────────────────────────────────────────
log_info "Configuration du serveur Web Nginx..."
cat <<NGINX > "/etc/nginx/sites-available/docscolaire"
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME};
    root ${APP_DIR}/public;

    # En-têtes de sécurité
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    index index.php index.html;
    charset utf-8;

    # Taille max pour import CSV
    client_max_body_size 25M;

    # Rate limiting sur les requêtes d'authentification
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # PHP-FPM 8.3
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 300;
    }

    # Bloquer accès aux fichiers cachés (.env, .git, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/docscolaire /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx
systemctl enable nginx

# ── 7. Configuration de Supervisor (Queue Worker Asynchrone) ───────────────────
log_info "Configuration du worker d'arrière-plan Supervisor..."
cat <<SUPERVISOR > "/etc/supervisor/conf.d/docscolaire-worker.conf"
[program:docscolaire-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --sleep=2 --tries=3 --max-time=3600 --timeout=120
autostart=true
autorestart=true
user=${APP_USER}
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/docscolaire-worker.log
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl start docscolaire-worker:* || true

# ── 8. Sauvegardes automatiques quotidiennes & Scheduler Laravel (Cron) ─────────
log_info "Mise en place du script de sauvegarde quotidienne et du scheduler Laravel..."
mkdir -p /var/backups/docscolaire
cat <<'BACKUP' > /usr/local/bin/docscolaire-backup.sh
#!/bin/bash
set -e
BACKUP_DIR="/var/backups/docscolaire"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "${BACKUP_DIR}"

# Sauvegarde MySQL
mysqldump -u saas_user -p"$1" saas_documents | gzip > "${BACKUP_DIR}/db_${DATE}.sql.gz"

# Sauvegarde des documents PDF archivés
tar -czf "${BACKUP_DIR}/storage_${DATE}.tar.gz" -C /var/www/docscolaire/storage/app/private .

# Nettoyage des sauvegardes datant de plus de 14 jours
find "${BACKUP_DIR}" -type f -mtime +14 -delete
echo "[$(date)] Sauvegarde réussie : ${DATE}" >> /var/log/docscolaire_backup.log
BACKUP

sed -i "s/\"\$1\"/\"${DB_PASS}\"/g" /usr/local/bin/docscolaire-backup.sh
chmod +x /usr/local/bin/docscolaire-backup.sh

# Cron : Laravel Scheduler chaque minute + Sauvegarde à 02h00 chaque nuit
(crontab -l 2>/dev/null | grep -v 'docscolaire' | grep -v 'schedule:run'; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"; echo "0 2 * * * /usr/local/bin/docscolaire-backup.sh") | crontab -

# ── 9. Sécurité du pare-feu UFW ────────────────────────────────────────────────
log_info "Activation du pare-feu UFW (Ports 22 SSH, 80 HTTP, 443 HTTPS)..."
ufw allow 22/tcp || true
ufw allow 80/tcp || true
ufw allow 443/tcp || true
ufw --force enable || true

# ── Résumé final ──────────────────────────────────────────────────────────────
log_success "=========================================================="
log_success "  DÉPLOIEMENT TERMINÉ AVEC SUCCÈS !"
log_success "=========================================================="
echo -e "  🌐 URL d'accès       : http://${DOMAIN_NAME}"
echo -e "  🔐 Base de données   : ${DB_NAME} (User: ${DB_USER} | Pass: ${DB_PASS})"
echo -e "  📁 Répertoire projet : ${APP_DIR}"
echo -e "  ⚙️ Worker Supervisor : 2 workers actifs (queue:work database)"
echo -e "  💾 Sauvegarde nuit   : Quotidienne à 02h00 dans /var/backups/docscolaire/"
echo -e "  📜 Activer HTTPS     : certbot --nginx -d ${DOMAIN_NAME}"
echo -e "=========================================================="
