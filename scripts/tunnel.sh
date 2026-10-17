#!/usr/bin/env bash
# ==============================================================================
# LANCEMENT DU TUNNEL PUBLIC HTTPS — DocScolaire SaaS
# ==============================================================================
# Génère une URL HTTPS publique sécurisée accessible depuis n'importe quel
# smartphone ou PC sans aucune configuration réseau.
# ==============================================================================

set -euo pipefail

echo "Lancement du tunnel public sécurisé..."
ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -R 80:localhost:8080 nokey@localhost.run
