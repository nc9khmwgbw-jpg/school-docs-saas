#!/bin/sh
set -e

# Si l'autoloader composer n'existe pas encore
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "=========================================================="
    echo "  [DocScolaire Docker] Premier démarrage : Installation Composer..."
    echo "=========================================================="
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Préparation du .env si absent
if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Génération de la clé APP_KEY si vide
if [ -f /var/www/html/artisan ] && [ -f /var/www/html/.env ]; then
    if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
        echo "  [DocScolaire Docker] Génération de la clé d'application..."
        php /var/www/html/artisan key:generate --force || true
    fi
fi

# Permissions sur storage et bootstrap/cache
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/app/private/documents \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

php /var/www/html/artisan storage:link 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

exec "$@"
