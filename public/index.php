<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vérification de la présence de Composer autoloader
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    // Mode de démarrage préliminaire avant le premier "docker compose up" ou "composer install"
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>SaaS Documents Scolaires - Initialisation</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-50 p-8 flex items-center justify-center min-h-screen"><div class="max-w-xl bg-white p-8 rounded-2xl shadow-lg border border-slate-100 text-center"><div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl mx-auto flex items-center justify-center font-bold mb-4">🚀</div><h1 class="text-xl font-bold text-slate-900 mb-2">Socle SaaS Prêt pour Déploiement</h1><p class="text-sm text-slate-600 mb-6">Les fichiers sources, modèles multi-tenants, migrations et configurations Docker sont initialisés. Exécutez la commande suivante pour lancer les conteneurs :</p><code class="block bg-slate-900 text-emerald-400 p-4 rounded-xl text-xs font-mono text-left mb-6">docker compose up -d --build</code><p class="text-xs text-slate-400">Stack : Nginx + PHP 8.3-FPM + MySQL 8.0 + Supervisor Worker</p></div></body></html>';
    exit;
}

// Enregistrement de l'autoloader
require __DIR__.'/../vendor/autoload.php';

// Démarrage de l'application Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

if (method_exists($app, 'handleRequest')) {
    $app->handleRequest(Request::capture());
} else {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Request::capture())->send();
    $kernel->terminate($request, $response);
}
