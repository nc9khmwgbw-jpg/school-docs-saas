#!/usr/bin/env bash
# ==============================================================================
# SCÉNARIO DE DÉMONSTRATION & VALIDATION MULTI-ÉCOLES — DocScolaire SaaS
# ==============================================================================
# Ce script réinitialise les données de test et affiche les identifiants
# pour la démonstration client avec deux écoles distinctes (isolation étanche).
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "${SCRIPT_DIR}")"

# Option de réinitialisation si demandé
if [[ "${1:-}" == "--reset" ]]; then
    echo "Réinitialisation des données de test..."
    if command -v docker &>/dev/null && docker ps | grep -q "saas_school_db"; then
        docker exec -i saas_school_db mysql -u saas_user -psaas_password saas_documents < "${PROJECT_ROOT}/database/sql/schema.sql"
    elif command -v mysql &>/dev/null; then
        mysql -u saas_user -psaas_password saas_documents < "${PROJECT_ROOT}/database/sql/schema.sql" || true
    fi
    echo "Base de données réinitialisée avec succès."
fi

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}==================================================================${NC}"
echo -e "${BLUE}  SCÉNARIO DE DÉMONSTRATION COMMERCIAL — DocScolaire SaaS v2.0   ${NC}"
echo -e "${BLUE}==================================================================${NC}"
echo ""

echo -e "${CYAN}--- COMPTES DE DÉMONSTRATION PRÊTS À L'EMPLOI ---${NC}"
echo ""
echo -e "${YELLOW}1. Super Administrateur (Gestionnaire SaaS global) :${NC}"
echo "   URL      : /superadmin/login"
echo "   Email    : superadmin@plateforme-saas.com"
echo "   Mot passe: Secret2026!"
echo "   Rôle     : Supervision de toutes les écoles, création de nouveaux tenants, audit global."
echo ""
echo -e "${YELLOW}2. École #1 — École Nationale Supérieure (ENS-2026) :${NC}"
echo "   Code École : ENS-2026"
echo "   Portail    : /portal/ENS-2026"
echo "   Admin      : admin@ens.ma | Mot de passe : Secret2026!"
echo "   Étudiant 1 : Sara Alami (Matricule : E2026-9901) | Mot de passe : Secret2026!"
echo "   Étudiant 2 : Mehdi Tazi (Matricule : E2026-8888) | Mot de passe : Secret2026!"
echo "   Documents  : Attestation de réussite (Auto), Certificat de scolarité (Validation manuelle)"
echo ""
echo -e "${YELLOW}3. École #2 — Institut Polytechnique de Casablanca (POLY-2026) :${NC}"
echo "   Code École : POLY-2026"
echo "   Portail    : /portal/POLY-2026"
echo "   Admin      : contact@poly.ma | Mot de passe : Secret2026!"
echo "   Isolation  : L'admin de POLY-2026 ne peut accéder à aucune donnée de l'ENS-2026."
echo ""
echo -e "${GREEN}--- PROCÉDURE DE DÉMONSTRATION RECOMMANDÉE ---${NC}"
echo "1. Connexion SuperAdmin (/superadmin/login) -> Vérifier les 2 écoles, les métriques globales et l'audit."
echo "2. Accès direct Étudiant via MyPortal (/myportal) -> Connexion 1-clic avec Sara Alami (E2026-9901)."
echo "3. Demande d'attestation -> Génération asynchrone instantanée avec vrai QR Code PNG 2D scannable."
echo "4. Téléchargement immédiat du PDF officiel certifié."
echo "5. Détection Anti-Fraude : Scanner le QR Code avec un smartphone (appareil photo) -> Page de conformité officielle."
echo "6. Clic sur 'Consulter la Copie Originale Certifiée (PDF)' -> Comparer le document original avec le papier pour détecter toute falsification."
echo "7. Boîte mail de test en direct : http://localhost:8025 pour voir les emails envoyés en temps réel."
echo ""
echo -e "${BLUE}==================================================================${NC}"
