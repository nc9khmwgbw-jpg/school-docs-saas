<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. POINT D'ENTRÉE UNIVERSEL (Portail Public)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $ecoles = \App\Models\Ecole::where('statut', 'actif')->get();
    return view('welcome', compact('ecoles'));
})->name('home');

// Redirection transparente vers l'accueil (cloisonnement strict multi-tenant : chaque étudiant passe par son école)
Route::get('/myportal', [TenantPortalController::class, 'showMyPortal'])->name('myportal');
Route::get('/myportal/detect-school', [TenantPortalController::class, 'detectSchool'])
    ->middleware('throttle:30,1')
    ->name('myportal.detect_school');
Route::post('/myportal/login', [TenantPortalController::class, 'loginMyPortal'])
    ->middleware('throttle.login')
    ->name('myportal.login');
Route::get('/portal', fn() => redirect()->route('home'));
Route::get('/etudiant', fn() => redirect()->route('home'));

// Vue visuelle immersive (strictement réservée aux utilisateurs authentifiés)
Route::get('/sublime', function (Request $request) {
    if (!auth()->check()) {
        return redirect()->route('home')->withErrors(['auth' => 'Veuillez vous connecter pour prévisualiser cet espace.']);
    }
    $ecoles = \App\Models\Ecole::where('statut', 'actif')->get();
    $user = auth()->user();
    $ecole = $user->ecole ?? $ecoles->first();
    $etudiant = $user->isEtudiant() ? $user : null;

    return view('portal.sublime', compact('etudiant', 'ecole', 'ecoles'));
})->middleware(['auth'])->name('portal.sublime');

Route::post('/trouver-ecole', function (Request $request) {
    $request->validate(['code_unique' => 'required|string']);
    $input = trim($request->input('code_unique'));
    $codeUpper = strtoupper($input);

    $ecole = \App\Models\Ecole::where('code_unique', $codeUpper)->first();

    if (!$ecole && str_contains($input, '@')) {
        $user = \App\Models\Utilisateur::withoutGlobalScopes()->where('email', strtolower($input))->first();
        if ($user && $user->ecole) {
            $ecole = $user->ecole;
        }
    }

    if (!$ecole) {
        $ecole = \App\Models\Ecole::where('code_unique', 'LIKE', "{$codeUpper}%")
            ->orWhere('nom', 'LIKE', "%{$input}%")
            ->first();
    }

    $finalCode = $ecole ? $ecole->code_unique : $codeUpper;
    return redirect()->route('tenant.portal', ['code_unique' => $finalCode]);
})->name('ecole.recherche');

/*
|--------------------------------------------------------------------------
| 2. VÉRIFICATION PUBLIQUE QR CODE
|--------------------------------------------------------------------------
| Accessible sans authentification - protégée par rate-limiting uniquement.
| UUID v4 aléatoire : impossible d'énumérer les documents.
| ✅ Rate limiting : 60 requêtes par minute par IP
*/
Route::get('/verifier/{uuid}', [VerificationController::class, 'verifier'])
    ->middleware('throttle:60,1')
    ->name('public.verifier')
    ->where('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

Route::get('/verifier/{uuid}/original', [VerificationController::class, 'telechargerOriginal'])
    ->middleware('throttle:30,1')
    ->name('public.verifier.original')
    ->where('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

/*
|--------------------------------------------------------------------------
| 3. PARCOURS ÉTUDIANT (Cloisonné par {code_unique})
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'portal/{code_unique}', 'middleware' => ['web', 'identify.tenant', 'session.timeout']], function () {

    // Pages publiques du portail (sans authentification)
    Route::get('/', [TenantPortalController::class, 'showPortal'])->name('tenant.portal');
    Route::post('/login', [TenantPortalController::class, 'login'])
        ->middleware('throttle.login')
        ->name('tenant.login');
    Route::post('/logout', [TenantPortalController::class, 'logout'])->name('tenant.logout');

    // Réinitialisation de mot de passe (Essentiel)
    Route::get('/mot-de-passe/oublie', [PasswordResetController::class, 'showForgotForm'])->name('tenant.password.forgot');
    Route::post('/mot-de-passe/oublie', [PasswordResetController::class, 'sendResetLink'])->name('tenant.password.forgot.send');
    Route::get('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'showResetForm'])->name('tenant.password.reset');
    Route::post('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'resetPassword'])->name('tenant.password.reset.update');

    // Espace sécurisé étudiant
    Route::group(['middleware' => ['auth', 'role:etudiant']], function () {
        Route::get('/dashboard', [TenantPortalController::class, 'dashboard'])->name('tenant.dashboard');
        Route::post('/demandes', [TenantPortalController::class, 'storeDemande'])->name('tenant.demandes.store');
        Route::get('/historique', [TenantPortalController::class, 'historique'])->name('tenant.historique');
        Route::get('/documents/{uuid}/download', [TenantPortalController::class, 'downloadDocument'])->name('tenant.documents.download');
    });
});

/*
|--------------------------------------------------------------------------
| 4. PARCOURS ADMINISTRATEUR ÉCOLE (Gestionnaire)
|--------------------------------------------------------------------------
*/
// Authentification Admin École (public)
Route::group(['prefix' => 'admin-ecole', 'middleware' => ['web']], function () {
    Route::get('/login', [SchoolAdminController::class, 'showLogin'])->name('admin_ecole.login');
    Route::post('/login', [SchoolAdminController::class, 'login'])->name('admin_ecole.login.post');
});

// Redirection globale vers login admin
Route::get('/login', [SchoolAdminController::class, 'showLogin'])->name('login');

// Espace sécurisé Admin École
Route::group(['prefix' => 'admin-ecole', 'middleware' => ['web', 'auth', 'role:admin_ecole', 'session.timeout']], function () {

    // Dashboard principal
    Route::get('/', [SchoolAdminController::class, 'dashboard'])->name('admin_ecole.dashboard');

    // Gestion des modèles de documents
    Route::get('/modeles', [SchoolAdminController::class, 'modelesIndex'])->name('admin_ecole.modeles.index');
    Route::get('/modeles/creer', [SchoolAdminController::class, 'modelesCreate'])->name('admin_ecole.modeles.create');
    Route::post('/modeles', [SchoolAdminController::class, 'modelesStore'])->name('admin_ecole.modeles.store');
    Route::get('/modeles/{id}/editer', [SchoolAdminController::class, 'modelesEdit'])->name('admin_ecole.modeles.edit');
    Route::put('/modeles/{id}', [SchoolAdminController::class, 'modelesUpdate'])->name('admin_ecole.modeles.update');
    Route::delete('/modeles/{id}', [SchoolAdminController::class, 'modelesDestroy'])->name('admin_ecole.modeles.destroy');
    Route::get('/modeles/{id}/apercu', [SchoolAdminController::class, 'modelesApercu'])->name('admin_ecole.modeles.apercu');

    // Gestion des demandes (validation / refus)
    Route::get('/demandes', [SchoolAdminController::class, 'demandesIndex'])->name('admin_ecole.demandes.index');
    Route::post('/demandes/{id}/valider', [SchoolAdminController::class, 'demandesValider'])->name('admin_ecole.demandes.valider');
    Route::post('/demandes/{id}/refuser', [SchoolAdminController::class, 'demandesRefuser'])->name('admin_ecole.demandes.refuser');
    Route::post('/traiter-en-masse', [SchoolAdminController::class, 'traiterEnMasse'])->name('admin_ecole.traiter_en_masse');

    // Import des étudiants & Ajout manuel & Liste
    Route::get('/etudiants', [SchoolAdminController::class, 'etudiantsIndex'])->name('admin_ecole.etudiants.index');
    Route::get('/import', [SchoolAdminController::class, 'importIndex'])->name('admin_ecole.import.index');
    Route::post('/import', [SchoolAdminController::class, 'importDonnees'])->name('admin_ecole.import');
    Route::post('/etudiants', [SchoolAdminController::class, 'etudiantsStore'])->name('admin_ecole.etudiants.store');
    Route::get('/import/{id}/rapport', [SchoolAdminController::class, 'importRapport'])->name('admin_ecole.import.rapport');

    // Téléchargement sécurisé document par admin école
    Route::get('/documents/{uuid}/download', [SchoolAdminController::class, 'telechargerDocument'])->name('admin_ecole.documents.download');

    // Personnalisation de l'espace école
    Route::get('/parametres', [SchoolAdminController::class, 'parametres'])->name('admin_ecole.parametres');
    Route::post('/parametres', [SchoolAdminController::class, 'parametresSave'])->name('admin_ecole.parametres.save');
    Route::post('/parametres/logo', [SchoolAdminController::class, 'uploadLogo'])->name('admin_ecole.parametres.logo');
    Route::post('/parametres/cachet', [SchoolAdminController::class, 'uploadCachet'])->name('admin_ecole.parametres.cachet');
    Route::post('/parametres/signature', [SchoolAdminController::class, 'uploadSignature'])->name('admin_ecole.parametres.signature');

    // Logs d'activité de l'école
    Route::get('/logs', [SchoolAdminController::class, 'logsIndex'])->name('admin_ecole.logs');

    // Déconnexion admin
    Route::post('/logout', [SchoolAdminController::class, 'logout'])->name('admin_ecole.logout');
});

/*
|--------------------------------------------------------------------------
| 5. PARCOURS SUPER ADMINISTRATEUR (Propriétaire SaaS)
|--------------------------------------------------------------------------
*/
// Authentification Super Admin (public)
Route::group(['prefix' => 'superadmin', 'middleware' => ['web']], function () {
    Route::get('/login', [SuperAdminController::class, 'showLogin'])->name('superadmin.login');
    Route::post('/login', [SuperAdminController::class, 'login'])->name('superadmin.login.post');
});

// Espace sécurisé Super Admin
Route::group(['prefix' => 'superadmin', 'middleware' => ['web', 'auth', 'role:super_admin']], function () {
    Route::post('/logout', [SuperAdminController::class, 'logout'])->name('superadmin.logout');

    // Dashboard global
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // Gestion des écoles
    Route::get('/ecoles', [SuperAdminController::class, 'ecolesIndex'])->name('superadmin.ecoles.index');
    Route::get('/ecoles/creer', [SuperAdminController::class, 'ecolesCreate'])->name('superadmin.ecoles.create');
    Route::post('/ecoles', [SuperAdminController::class, 'storeEcole'])->name('superadmin.ecoles.store');
    Route::get('/ecoles/{id}', [SuperAdminController::class, 'ecolesShow'])->name('superadmin.ecoles.show');
    Route::post('/ecoles/{id}/toggle-statut', [SuperAdminController::class, 'toggleStatut'])->name('superadmin.ecoles.toggle_statut');

    // Annuaire des étudiants (par école ou global)
    Route::get('/etudiants', [SuperAdminController::class, 'etudiantsIndex'])->name('superadmin.etudiants.index');

    // Impersonation / Accès Master (prendre le contrôle en un clic sans mot de passe)
    Route::post('/impersonate/ecole/{id}', [SuperAdminController::class, 'impersonateEcole'])->name('superadmin.impersonate.ecole');
    Route::post('/impersonate/etudiant/{id}', [SuperAdminController::class, 'impersonateEtudiant'])->name('superadmin.impersonate.etudiant');

    // Audit log global plateforme
    Route::get('/logs', [SuperAdminController::class, 'logsGlobal'])->name('superadmin.logs');

    // Statistiques globales
    Route::get('/statistiques', [SuperAdminController::class, 'statistiques'])->name('superadmin.statistiques');
});

// Sortie du mode d'accès délégué (revenir au SuperAdmin)
Route::match(['get', 'post'], '/stop-impersonate', function () {
    if (session()->has('impersonated_by_superadmin')) {
        $originalSuperAdminId = session()->pull('impersonated_by_superadmin');
        $superAdmin = \App\Models\Utilisateur::withoutGlobalScopes()->find($originalSuperAdminId);
        if ($superAdmin) {
            auth()->login($superAdmin);
            \App\Services\AuditService::logAuth(
                'impersonation_fin',
                'Utilisateur',
                $superAdmin->id,
                ['message' => 'Fin de la session impersonée et retour au compte SuperAdmin']
            );
            return redirect()->route('superadmin.dashboard')
                ->with('success', '⚡ Session SuperAdmin restaurée avec succès.');
        }
    }
    return redirect()->route('home');
})->name('impersonate.stop');
