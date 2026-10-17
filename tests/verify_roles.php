<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Utilisateur;
use App\Models\Ecole;
use Illuminate\Support\Facades\Auth;

echo "=== VÉRIFICATION DE LA SÉCURITÉ ET DU CLOISONNEMENT DES RÔLES ===\n\n";

// 1. Invité -> /admin-ecole
$res = app()->handle(\Illuminate\Http\Request::create('/admin-ecole', 'GET'));
echo "1. Invité vers /admin-ecole: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/admin-ecole/login'));

// 2. Invité -> /superadmin
$res = app()->handle(\Illuminate\Http\Request::create('/superadmin', 'GET'));
echo "2. Invité vers /superadmin: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/superadmin/login'));

// 3. Invité -> /portal/ENS-2026/dashboard
$res = app()->handle(\Illuminate\Http\Request::create('/portal/ENS-2026/dashboard', 'GET'));
echo "3. Invité vers /portal/ENS-2026/dashboard: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/portal/ENS-2026'));

// 4. Étudiant ENS connecté -> /admin-ecole
$etudiant = Utilisateur::withoutGlobalScopes()->where('role', 'etudiant')->where('matricule', 'E2026-9901')->firstOrFail();
Auth::login($etudiant);
$res = app()->handle(\Illuminate\Http\Request::create('/admin-ecole', 'GET'));
echo "4. Étudiant ENS vers /admin-ecole: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/portal/ENS-2026/dashboard'));

// 5. Étudiant ENS connecté -> /superadmin
$res = app()->handle(\Illuminate\Http\Request::create('/superadmin', 'GET'));
echo "5. Étudiant ENS vers /superadmin: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/portal/ENS-2026/dashboard'));

// 6. Admin École connecté -> /superadmin
$admin = Utilisateur::withoutGlobalScopes()->where('role', 'admin_ecole')->where('email', 'admin@ens.ma')->firstOrFail();
Auth::login($admin);
$res = app()->handle(\Illuminate\Http\Request::create('/superadmin', 'GET'));
echo "6. Admin École vers /superadmin: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/admin-ecole'));

// 7. Admin École connecté -> /portal/ENS-2026/dashboard
$res = app()->handle(\Illuminate\Http\Request::create('/portal/ENS-2026/dashboard', 'GET'));
echo "7. Admin École vers /portal/ENS-2026/dashboard: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/admin-ecole'));

// 8. Super Admin connecté -> /admin-ecole
$superadmin = Utilisateur::withoutGlobalScopes()->where('role', 'super_admin')->firstOrFail();
Auth::login($superadmin);
$res = app()->handle(\Illuminate\Http\Request::create('/admin-ecole', 'GET'));
echo "8. Super Admin vers /admin-ecole: Code " . $res->getStatusCode() . " -> Redirection: " . $res->headers->get('Location') . "\n";
assert(str_contains($res->headers->get('Location') ?? '', '/superadmin'));

echo "\nTOUTES LES VÉRIFICATIONS SONT VALIDÉES AVEC SUCCÈS !\n";
