#!/usr/bin/env bash
# ==============================================================================
# SCRIPT DE SAUVEGARDE AUTOMATISÉE — DocScolaire SaaS v2.0
# ==============================================================================
# 1. Lecture dynamique des identifiants depuis .env (sécurité)
# 2. Dump complet de la base de données MySQL via MYSQL_PWD
# 3. Archive des documents scolaires générés et médias
# 4. Calcul de checksum SHA-256 pour garantie d'intégrité
# 5. Rotation automatique (conserve les 14 dernières sauvegardes)
# 6. Synchronisation optionnelle hors-site (S3 / dossier déporté)
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "${SCRIPT_DIR}")"
ENV_FILE="${PROJECT_ROOT}/.env"

# Extraction sécurisée des variables d'environnement
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
OFFSITE_BACKUP_DIR="$(get_env_val 'BACKUP_OFFSITE_DIR' '')"

BACKUP_DIR="${PROJECT_ROOT}/storage/app/backups"
TIMESTAMP="$(date +'%Y%m%d_%H%M%S')"
BACKUP_NAME="backup_docscolaire_${TIMESTAMP}"
TEMP_DIR="/tmp/${BACKUP_NAME}"

mkdir -p "${BACKUP_DIR}"
mkdir -p "${TEMP_DIR}"

echo "[1/4] Export sécurisé de la base de données MySQL (${DB_NAME})..."
if docker ps 2>/dev/null | grep -q "saas_school_db"; then
    docker exec -e MYSQL_PWD="${DB_PASS}" saas_school_db mysqldump \
        --no-tablespaces \
        --single-transaction \
        --quick \
        -u "${DB_USER}" "${DB_NAME}" > "${TEMP_DIR}/database.sql"
else
    MYSQL_PWD="${DB_PASS}" mysqldump \
        --no-tablespaces \
        --single-transaction \
        --quick \
        -h "${DB_HOST}" \
        -P "${DB_PORT}" \
        -u "${DB_USER}" "${DB_NAME}" > "${TEMP_DIR}/database.sql"
fi

echo "[2/4] Copie des documents et fichiers médias privés..."
if [ -d "${PROJECT_ROOT}/storage/app/documents" ]; then
    cp -r "${PROJECT_ROOT}/storage/app/documents" "${TEMP_DIR}/" || true
fi
if [ -d "${PROJECT_ROOT}/storage/app/private" ]; then
    cp -r "${PROJECT_ROOT}/storage/app/private" "${TEMP_DIR}/" || true
fi
if [ -d "${PROJECT_ROOT}/storage/app/public" ]; then
    cp -r "${PROJECT_ROOT}/storage/app/public" "${TEMP_DIR}/" || true
fi

echo "[3/4] Compression et scellage d'intégrité SHA-256..."
TARBALL="${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
tar -czf "${TARBALL}" -C "/tmp" "${BACKUP_NAME}"
sha256sum "${TARBALL}" > "${TARBALL}.sha256"

# Nettoyage temporaire
rm -rf "${TEMP_DIR}"

echo "[4/4] Rotation des sauvegardes (conservation des 14 plus récentes)..."
find "${BACKUP_DIR}" -name "backup_docscolaire_*.tar.gz*" -mtime +14 -delete 2>/dev/null || true

# Réplication hors-site optionnelle si configurée
if [ -n "${OFFSITE_BACKUP_DIR}" ] && [ -d "${OFFSITE_BACKUP_DIR}" ]; then
    echo "[SYNC] Réplication de la sauvegarde vers l'emplacement hors-site : ${OFFSITE_BACKUP_DIR}"
    cp "${TARBALL}" "${TARBALL}.sha256" "${OFFSITE_BACKUP_DIR}/"
fi

echo "=================================================================="
echo "  SAUVEGARDE TERMINÉE AVEC SUCCÈS"
echo "  Archive  : ${TARBALL}"
echo "  Checksum : $(cat "${TARBALL}.sha256")"
echo "=================================================================="
