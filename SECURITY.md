# 🔐 Politique de Sécurité

## ✅ Corrections de Sécurité Appliquées

Ce projet a été audité et corrigé pour les vulnérabilités suivantes :

### Critiques (Corrigées)
- ✅ APP_KEY retirée du dépôt Git
- ✅ Mots de passe des seeds déplacés vers seeders Laravel sécurisés
- ✅ Port MySQL restreint à localhost uniquement
- ✅ Credentials MySQL externalisés via variables d'environnement

### Importantes (Corrigées)
- ✅ Protection XSS via HTMLPurifier sur templates de documents
- ✅ Sanitization SVG avec enshrined/svg-sanitize
- ✅ Validation anti-bypass du TenantScope dans les Jobs
- ✅ Protection CSV injection avec échappement des formules
- ✅ Session timeout corrigé avec cache Redis

### Modérées (Corrigées)
- ✅ Rate limiting harmonisé (10 minutes)
- ✅ Rate limiting ajouté sur routes publiques
- ✅ Nettoyage automatique des jobs échoués
- ✅ Nettoyage automatique des logs anciens

## 📋 Checklist de Sécurité pour Déploiement

### Avant le Premier Déploiement

1. **Variables d'environnement** :
   ```bash
   # Regénérer APP_KEY
   php artisan key:generate
   
   # Définir des mots de passe forts
   DB_ROOT_PASSWORD=$(openssl rand -base64 32)
   DB_PASSWORD=$(openssl rand -base64 24)
   SUPERADMIN_DEFAULT_PASSWORD=$(openssl rand -base64 16)
   ```

2. **Permissions des fichiers** :
   ```bash
   chmod 755 storage bootstrap/cache
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

3. **Configuration production** :
   ```env
   APP_ENV=production
   APP_DEBUG=false
   LOG_LEVEL=error
   ```

4. **HTTPS obligatoire** :
   - Configurer SSL/TLS sur Nginx
   - Forcer HTTPS dans Laravel :
     ```php
     // Dans App\Providers\AppServiceProvider::boot()
     if ($this->app->environment('production')) {
         \Illuminate\Support\Facades\URL::forceScheme('https');
     }
     ```

5. **Firewall** :
   ```bash
   # Bloquer tous les ports sauf 80 et 443
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw enable
   ```

### Maintenance Continue

1. **Mises à jour** :
   ```bash
   # Mettre à jour les dépendances mensuellement
   composer update
   npm update
   ```

2. **Surveillance** :
   - Configurer alertes sur failed_jobs
   - Monitorer l'espace disque (logs, documents)
   - Surveiller les logs d'audit pour activités suspectes

3. **Sauvegardes** :
   ```bash
   # Sauvegarder quotidiennement
   mysqldump -u root -p saas_documents > backup.sql
   tar -czf storage_backup.tar.gz storage/
   ```

## 🚨 Signaler une Vulnérabilité

Si vous découvrez une faille de sécurité, **ne la publiez PAS publiquement**.

Contactez : security@plateforme-saas.com

Nous nous engageons à :
- Répondre sous 48h
- Fournir un patch sous 7 jours pour les vulnérabilités critiques
- Créditer les chercheurs en sécurité (si souhaité)

## 📚 Ressources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/11.x/security)
- [Documentation complète](./docs/ARCHITECTURE.md)
