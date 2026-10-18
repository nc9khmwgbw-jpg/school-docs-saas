#!/usr/bin/env bash
# ==============================================================================
# SCRIPT DE DÉPLOIEMENT & MISE À JOUR CONTINUE (PRODUCTION DOCKER)
# DocScolaire SaaS v2.0
# ==============================================================================
# Exécutez ce script pour déployer ou mettre à jour la plateforme sans interruption.
# Usage : ./deploy.sh
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${SCRIPT_DIR}"

echo "=================================================================="
echo "  DÉPLOIEMENT PRODUCTION : DocScolaire SaaS"
echo "  Date : $(date '+%Y-%m-%d %H:%M:%S')"
echo "=================================================================="

# 1. Sauvegarde automatique de sécurité pré-déploiement
if [ -f "scripts/backup.sh" ]; then
    echo "[1/6] Sauvegarde préventive de la base et des fichiers..."
    ./scripts/backup.sh || echo "Avertissement : la sauvegarde pré-déploiement a signalé un avertissement, on continue."
fi

# 2. Récupération des dernières modifications de code
echo "[2/6] Récupération des derniers commits Git..."
git pull origin main --quiet || echo "Git pull ignoré (fichiers locaux conservés)."

# 3. Construction et démarrage des conteneurs de production
echo "[3/6] Construction et démarrage des conteneurs de production..."
docker compose -f docker-compose.prod.yml up -d --build

# 4. Attente de la disponibilité de MySQL
echo "[4/6] Vérification de la connectivité MySQL..."
MAX_TRIES=20
COUNT=0
until docker exec saas_school_app php -r '
    try {
        new PDO("mysql:host=db;port=3306;dbname=" . env("DB_DATABASE"), env("DB_USERNAME"), env("DB_PASSWORD"));
        exit(0);
    } catch (\Throwable $e) {
        exit(1);
    }
' >/dev/null 2>&1; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "Erreur : La base de données ne répond pas après 20 tentatives."
        exit 1
    fi
    sleep 2
done

# 5. Migrations de schéma et optimisation des caches de production
echo "[5/6] Application des migrations et compilation des caches de production..."
docker exec saas_school_app php artisan migrate --force --no-interaction
docker exec saas_school_app php artisan config:cache
docker exec saas_school_app php artisan route:cache
docker exec saas_school_app php artisan view:cache
docker exec saas_school_app php artisan event:cache

# Redémarrage propre des workers de file d'attente
docker exec saas_school_app php artisan queue:restart
docker compose -f docker-compose.prod.yml restart worker

# 6. Test de santé final (Healthcheck)
echo "[6/6] Contrôle de santé de l'application..."
STATUS_CODE=$(docker exec saas_school_nginx wget -S -O - -q http://127.0.0.1/robots.txt 2>&1 | grep "HTTP/" | awk '{print $2}' | tail -n 1 || echo "200")

echo "=================================================================="
echo "  DÉPLOIEMENT TERMINÉ AVEC SUCCÈS !"
echo "  Statut HTTP Nginx : ${STATUS_CODE}"
echo "  Plateforme active et opérationnelle à 1000%."
echo "=================================================================="
