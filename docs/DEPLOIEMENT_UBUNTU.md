# Guide de Déploiement et d'Exploitation sur Serveur Linux Ubuntu

Ce guide détaille l'installation, la mise en production, l'exploitation et la restauration de la plateforme **DocScolaire SaaS v2.0** sur un serveur dédié ou VPS Linux Ubuntu (22.04 LTS ou 24.04 LTS).

---

## 1. Prérequis Système Recommandés

- **Système d'exploitation :** Ubuntu 22.04 LTS ou 24.04 LTS Server (64 bits)
- **Ressources minimales :** 2 vCPUs, 4 Go de mémoire RAM, 40 Go d'espace disque NVMe/SSD
- **Réseau :** Une adresse IP publique fixe avec un nom de domaine pointant vers l'IP (enregistrement DNS type A).

---

## 2. Déploiement Automatisé en 1 Commande (Recommandé)

Le projet intègre un script d'installation clé en main dans le dossier `scripts/` :

```bash
# 1. Se connecter au serveur Ubuntu en SSH
ssh root@votre-serveur-ip

# 2. Cloner ou transférer l'archive du projet
git clone <votre-depot> /var/www/docscolaire
cd /var/www/docscolaire

# 3. Rendre exécutable et lancer le déploiement automatique avec votre domaine
chmod +x scripts/deploy_ubuntu.sh
sudo ./scripts/deploy_ubuntu.sh docscolaire.mondomaine.com
```

Le script installe automatiquement Nginx, PHP 8.3 FPM, MySQL 8, Supervisor, configure les droits et active les sauvegardes nocturnes.

---

## 3. Procédure Manuelle Étape par Étape

Si vous préférez installer chaque composant manuellement :

### Étape 1 : Paquets Système & PHP 8.3
```bash
sudo apt update && sudo apt upgrade -y
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

sudo apt install -y nginx mysql-server supervisor git unzip curl \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-gd php8.3-zip \
    php8.3-bcmath php8.3-mbstring php8.3-intl php8.3-curl php8.3-xml
```

### Étape 2 : Configuration MySQL
```bash
sudo mysql
```
```sql
CREATE DATABASE saas_documents CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'saas_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseFort!';
GRANT ALL PRIVILEGES ON saas_documents.* TO 'saas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
Importez le schéma initial :
```bash
mysql -u saas_user -pVotreMotDePasseFort! saas_documents < database/sql/schema.sql
```

### Étape 3 : Installation de l'Application Laravel
```bash
cd /var/www/docscolaire
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Modifiez .env avec vos identifiants MySQL et URL
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Étape 4 : Permissions des Répertoires
```bash
sudo chown -R www-data:www-data /var/www/docscolaire
sudo chmod -R 775 /var/www/docscolaire/storage /var/www/docscolaire/bootstrap/cache
```

### Étape 5 : Configuration Nginx
Créez `/etc/nginx/sites-available/docscolaire` :
```nginx
server {
    listen 80;
    server_name docscolaire.mondomaine.com;
    root /var/www/docscolaire/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Activez le site et redémarrez Nginx :
```bash
sudo ln -s /etc/nginx/sites-available/docscolaire /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl restart nginx
```

### Étape 6 : Activation HTTPS (SSL Gratuit Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d docscolaire.mondomaine.com
```

### Étape 7 : Gestionnaire de Travaux Supervisor
Créez `/etc/supervisor/conf.d/docscolaire.conf` :
```ini
[program:docscolaire-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/docscolaire/artisan queue:work database --sleep=2 --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/docscolaire-worker.log
```
Démarrez les workers :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start docscolaire-worker:*
```

---

## 4. Maintenance & Commandes Utiles

### Vérifier l'état des workers de génération PDF
```bash
sudo supervisorctl status
```

### Voir les logs d'erreurs en temps réel
```bash
tail -f /var/www/docscolaire/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
tail -f /var/log/docscolaire-worker.log
```

### Procédure de Restauration d'une Sauvegarde
En cas d'incident ou de migration de serveur :
```bash
# 1. Restauration de la base de données
gunzip < /var/backups/docscolaire/db_20261013_020000.sql.gz | mysql -u saas_user -p saas_documents

# 2. Restauration des fichiers PDF certifiés
tar -xzf /var/backups/docscolaire/storage_20261013_020000.tar.gz -C /var/www/docscolaire/storage/app/private/
```
