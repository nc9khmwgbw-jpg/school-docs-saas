# 🚀 Guide d'Installation - SaaS Documents Scolaires

## Prérequis

- Docker & Docker Compose
- Git
- (Optionnel) PHP 8.2+ et Composer pour développement local

## Installation avec Docker (Recommandé)

### 1. Cloner le projet

```bash
git clone https://github.com/votre-organisation/school-docs-saas.git
cd school-docs-saas
```

### 2. Configuration de l'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Générer une clé d'application Laravel sécurisée
php artisan key:generate
# OU si PHP n'est pas installé localement :
docker run --rm -v $(pwd):/app composer php /app/artisan key:generate

# Définir des mots de passe forts
export DB_ROOT_PASSWORD=$(openssl rand -base64 32)
export DB_PASSWORD=$(openssl rand -base64 24)
export SUPERADMIN_DEFAULT_PASSWORD=$(openssl rand -base64 16)

# Éditer .env et remplacer les valeurs par défaut
nano .env
```

**Variables critiques à configurer dans .env** :
```env
APP_KEY=base64:... # Généré automatiquement
APP_ENV=production
APP_DEBUG=false

DB_ROOT_PASSWORD=VotreMotDePasseRootFort
DB_PASSWORD=VotreMotDePasseBDFort

CACHE_STORE=redis
REDIS_HOST=redis
```

### 3. Installation des dépendances

```bash
# Installer les dépendances PHP
docker run --rm -v $(pwd):/app composer install --no-dev --optimize-autoloader

# OU si composer est installé localement :
composer install --no-dev --optimize-autoloader
```

### 4. Lancer les conteneurs Docker

```bash
# Construire et démarrer tous les services
docker-compose up -d

# Vérifier que tous les services sont actifs
docker-compose ps
```

Vous devriez voir 6 services actifs :
- `saas_school_web` (Nginx)
- `saas_school_app` (PHP-FPM)
- `saas_school_db` (MySQL)
- `saas_school_redis` (Redis)
- `saas_school_mailpit` (Mail de test)
- `saas_school_worker` (Queue worker)

### 5. Initialiser la base de données

```bash
# Exécuter les migrations sécurisées Laravel
docker exec saas_school_app php artisan migrate --force

# Créer le Super Administrateur
docker exec saas_school_app php artisan db:seed --class=SuperAdminSeeder

# (Optionnel) Créer des données de démonstration
docker exec saas_school_app php artisan db:seed --class=DemoDataSeeder
```

### 6. Configurer les permissions

```bash
docker exec saas_school_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec saas_school_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### 7. Vérification

Accédez à l'application :
- **Application** : http://localhost:8080
- **Mailpit (emails de test)** : http://localhost:8025
- **Super Admin** : http://localhost:8080/superadmin/login

**Identifiants Super Admin** (notés lors du seed) :
- Email : `superadmin@plateforme-saas.com`
- Mot de passe : (affiché dans la console lors du seeder)

⚠️ **IMPORTANT** : Changez immédiatement le mot de passe après la première connexion !

## Installation en Production

### Configuration HTTPS

1. **Obtenir un certificat SSL** (Let's Encrypt recommandé) :
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d votre-domaine.com
```

2. **Modifier la configuration Nginx** :
```nginx
# Dans docker/nginx/default.conf
server {
    listen 443 ssl http2;
    server_name votre-domaine.com;
    
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    
    # ... reste de la configuration
}
```

3. **Forcer HTTPS dans Laravel** :
```php
// Dans app/Providers/AppServiceProvider.php boot()
if ($this->app->environment('production')) {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```

### Sécurité Production

```bash
# Firewall : N'autoriser que ports 80 et 443
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Vérifier que MySQL n'est PAS accessible depuis l'extérieur
sudo netstat -tlnp | grep 3306
# Devrait montrer 127.0.0.1:3306 uniquement
```

### Sauvegarde Automatique

```bash
# Créer un script de sauvegarde
sudo nano /usr/local/bin/backup-saas.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/school-docs-saas"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup base de données
docker exec saas_school_db mysqldump -uroot -p${DB_ROOT_PASSWORD} saas_documents | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup fichiers storage
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" storage/

# Supprimer backups de plus de 30 jours
find "$BACKUP_DIR" -name "*.gz" -mtime +30 -delete
```

```bash
# Rendre exécutable
sudo chmod +x /usr/local/bin/backup-saas.sh

# Ajouter au cron (tous les jours à 3h)
sudo crontab -e
0 3 * * * /usr/local/bin/backup-saas.sh
```

## Maintenance

### Mettre à jour l'application

```bash
# Arrêter les workers
docker-compose stop worker

# Mettre à jour le code
git pull origin main

# Mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# Mettre à jour le schéma de base (si nécessaire)
docker exec -i saas_school_db mysql -uroot -p${DB_ROOT_PASSWORD} saas_documents < database/sql/migrations/new_migration.sql

# Vider les caches
docker exec saas_school_app php artisan cache:clear
docker exec saas_school_app php artisan config:clear
docker exec saas_school_app php artisan view:clear

# Redémarrer les services
docker-compose up -d
```

### Surveiller les logs

```bash
# Logs en temps réel
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs -f app

# Logs Laravel
docker exec saas_school_app tail -f storage/logs/laravel.log

# Logs des jobs échoués
docker exec saas_school_app php artisan queue:failed
```

### Nettoyer les données anciennes

```bash
# Nettoyer les jobs échoués (plus de 30 jours)
docker exec saas_school_app php artisan queue:clean-failed --days=30

# Nettoyer les logs (plus de 12 mois)
docker exec saas_school_app php artisan logs:clean --months=12
```

## Dépannage

### Problème : "Permission denied" sur storage/
```bash
docker exec saas_school_app chown -R www-data:www-data /var/www/html/storage
docker exec saas_school_app chmod -R 775 /var/www/html/storage
```

### Problème : MySQL ne démarre pas
```bash
# Vérifier les logs
docker-compose logs db

# Réinitialiser le volume (⚠️ PERTE DE DONNÉES)
docker-compose down
docker volume rm school-docs-saas_db_data
docker-compose up -d db
```

### Problème : Les PDFs ne se génèrent pas
```bash
# Vérifier le worker
docker-compose logs worker

# Redémarrer le worker
docker-compose restart worker

# Vérifier les jobs en attente
docker exec saas_school_app php artisan queue:work --once --verbose
```

### Problème : Cache Redis ne fonctionne pas
```bash
# Tester la connexion
docker exec saas_school_app php artisan tinker
>>> \Illuminate\Support\Facades\Cache::store('redis')->put('test', 'value', 60);
>>> \Illuminate\Support\Facades\Cache::store('redis')->get('test');

# Devrait afficher "value"
```

## Support

Pour toute question ou problème :
- 📧 Email : support@plateforme-saas.com
- 📚 Documentation : https://docs.plateforme-saas.com
- 🐛 Signaler un bug : https://github.com/votre-organisation/school-docs-saas/issues

## Licence

Propriétaire - Tous droits réservés © 2026
