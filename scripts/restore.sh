#!/usr/bin/env bash
# ==============================================================================
# SCRIPT DE RESTAURATION DU SAAS (DISASTER RECOVERY)
# DocScolaire SaaS v2.0
# ==============================================================================
# Usage : ./scripts/restore.sh /chemin/vers/backup_docscolaire_YYYYMMDD_HHMMSS.tar.gz
# ==============================================================================

set -euo pipefail

TARBALL="${1:-}"

if [ -z "$TARBALL" ] || [ ! -f "$TARBALL" ]; then
    echo "Usage: $0 /chemin/vers/backup_docscolaire_YYYYMMDD_HHMMSS.tar.gz"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "${SCRIPT_DIR}")"
ENV_FILE="${PROJECT_ROOT}/.env"

get_env_val() {
    local key="$1"
    local default_val="${2:-}"
    if [ -f "${ENV_FILE}" ]; then
        local val
        val="$(grep -E "^${key}=" "${ENV_FILE}" | cut -d '=' -f2- | tr -d '"\r' || true)"
        echo "${val:-$default_val}"
    else
        echo "$default_val"
    fi
}

DB_NAME="$(get_env_val 'DB_DATABASE' 'saas_documents')"
DB_USER="$(get_env_val 'DB_USERNAME' 'saas_user')"
DB_PASS="$(get_env_val 'DB_PASSWORD' '')"
DB_HOST="$(get_env_val 'DB_HOST' '127.0.0.1')"
DB_PORT="$(get_env_val 'DB_PORT' '3306')"

echo "=================================================================="
echo "  RESTAURATION DU SYSTÈME ET BASE DE DONNÉES"
echo "  Archive source : ${TARBALL}"
echo "=================================================================="

# 1. Vérification de checksum si présent
if [ -f "${TARBALL}.sha256" ]; then
    echo "[1/5] Vérification de l'intégrité SHA-256..."
    sha256sum -c "${TARBALL}.sha256"
else
    echo "[1/5] Avertissement : fichier .sha256 absent, vérification ignorée."
fi

# 2. Extraction temporaire
TEMP_DIR="/tmp/restore_$(date +%s)"
mkdir -p "${TEMP_DIR}"
echo "[2/5] Décompression de l'archive de sauvegarde..."
tar -xzf "${TARBALL}" -C "${TEMP_DIR}"

RESTORE_FOLDER="$(find "${TEMP_DIR}" -mindepth 1 -maxdepth 1 -type d | head -n 1)"
if [ -z "${RESTORE_FOLDER}" ] || [ ! -d "${RESTORE_FOLDER}" ]; then
    echo "Erreur : archive corrompue ou format invalide."
    rm -rf "${TEMP_DIR}"
    exit 1
fi

# 3. Restauration MySQL
echo "[3/5] Restauration de la base de données (${DB_NAME})..."
if [ -f "${RESTORE_FOLDER}/database.sql" ]; then
    if docker ps 2>/dev/null | grep -q "saas_school_db"; then
        docker exec -i -e MYSQL_PWD="${DB_PASS}" saas_school_db mysql -u "${DB_USER}" "${DB_NAME}" < "${RESTORE_FOLDER}/database.sql"
    else
        MYSQL_PWD="${DB_PASS}" mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" < "${RESTORE_FOLDER}/database.sql"
    fi
    echo "  -> Base de données restaurée avec succès."
else
    echo "  -> Aucun dump SQL trouvé dans l'archive !"
fi

# 4. Restauration des fichiers de stockage (documents générés, cachets, signatures)
echo "[4/5] Restauration des documents PDF et pièces jointes..."
mkdir -p "${PROJECT_ROOT}/storage/app"

if [ -d "${RESTORE_FOLDER}/documents" ]; then
    cp -r "${RESTORE_FOLDER}/documents" "${PROJECT_ROOT}/storage/app/"
fi
if [ -d "${RESTORE_FOLDER}/private" ]; then
    cp -r "${RESTORE_FOLDER}/private" "${PROJECT_ROOT}/storage/app/"
fi
if [ -d "${RESTORE_FOLDER}/public" ]; then
    cp -r "${RESTORE_FOLDER}/public" "${PROJECT_ROOT}/storage/app/"
fi

# 5. Nettoyage et permissions
echo "[5/5] Recompilation des caches et permissions..."
rm -rf "${TEMP_DIR}"

if docker ps 2>/dev/null | grep -q "saas_school_app"; then
    docker exec saas_school_app php artisan cache:clear || true
    docker exec saas_school_app php artisan view:clear || true
fi

echo "=================================================================="
echo "  RESTAURATION TERMINÉE AVEC SUCCÈS"
echo "  Votre plateforme et vos documents sont restaurés à l'état de la sauvegarde."
echo "=================================================================="
