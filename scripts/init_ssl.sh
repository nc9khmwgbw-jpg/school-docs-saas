#!/usr/bin/env bash
# ==============================================================================
# INITIALISATION DU CERTIFICAT SSL LET'S ENCRYPT (DOCKER)
# DocScolaire SaaS Production
# ==============================================================================
# Usage : ./scripts/init_ssl.sh <domaine.com> <email_admin@domaine.com>
# ==============================================================================

set -euo pipefail

DOMAIN="${1:-}"
EMAIL="${2:-}"

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo "Usage: $0 <domaine.com> <email_contact@domaine.com>"
    echo "Exemple: $0 scolarite.univ-maroc.ma contact@univ-maroc.ma"
    exit 1
fi

echo "=================================================================="
echo "  INITIALISATION SSL LET'S ENCRYPT : $DOMAIN"
echo "=================================================================="

# 1. Création des volumes et dossiers de certificats
echo "[1/5] Préparation du dossier de certificats temporaires..."
docker volume create school-docs-saas_certbot_etc >/dev/null 2>&1 || true
docker volume create school-docs-saas_certbot_var >/dev/null 2>&1 || true

# Montage temporaire d'un conteneur pour préparer les certificats
docker run --rm -v school-docs-saas_certbot_etc:/etc/letsencrypt alpine sh -c "
    mkdir -p /etc/letsencrypt/live/active
    if [ ! -f /etc/letsencrypt/live/active/fullchain.pem ]; then
        apk add --no-cache openssl >/dev/null 2>&1
        openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
            -keyout /etc/letsencrypt/live/active/privkey.pem \
            -out /etc/letsencrypt/live/active/fullchain.pem \
            -subj '/CN=localhost' >/dev/null 2>&1
    fi
"

# 2. Démarrage de Nginx avec le certificat temporaire
echo "[2/5] Démarrage du serveur web Nginx..."
docker compose -f docker-compose.prod.yml up -d web

# 3. Demande du vrai certificat Let's Encrypt
echo "[3/5] Obtention du certificat SSL officiel auprès de Let's Encrypt..."
docker compose -f docker-compose.prod.yml run --rm --entrypoint "\
    certbot certonly --webroot -w /var/www/certbot \
    --email ${EMAIL} \
    -d ${DOMAIN} \
    --rsa-key-size 4096 \
    --agree-tos \
    --force-renewal \
    --non-interactive" certbot

# 4. Activation du certificat officiel
echo "[4/5] Remplacement par le certificat officiel sécurisé..."
docker run --rm -v school-docs-saas_certbot_etc:/etc/letsencrypt alpine sh -c "
    cp -L /etc/letsencrypt/live/${DOMAIN}/fullchain.pem /etc/letsencrypt/live/active/fullchain.pem
    cp -L /etc/letsencrypt/live/${DOMAIN}/privkey.pem /etc/letsencrypt/live/active/privkey.pem
"

# 5. Rechargement de Nginx
echo "[5/5] Rechargement de Nginx avec le certificat SSL actif..."
docker exec saas_school_nginx nginx -s reload

echo "=================================================================="
echo "  SUCCÈS : HTTPS Let's Encrypt est activé sur https://${DOMAIN}"
echo "  Renouvellement automatique configuré toutes les 12h."
echo "=================================================================="
