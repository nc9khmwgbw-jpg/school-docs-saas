<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de vérification des rôles utilisateur et de cloisonnement strict des espaces.
 * Usage : middleware('role:admin_ecole'), middleware('role:super_admin'), middleware('role:etudiant')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // 1. Utilisateur non authentifié : redirection ciblée selon l'espace demandé
        if (!$user) {
            if (in_array('etudiant', $roles, true)) {
                $codeUnique = $request->route('code_unique');
                return $codeUnique 
                    ? redirect()->route('tenant.portal', ['code_unique' => $codeUnique])->withErrors(['auth' => 'Authentification requise pour accéder à votre espace étudiant.'])
                    : redirect()->route('myportal')->withErrors(['auth' => 'Authentification requise pour accéder à votre espace étudiant.']);
            }

            if (in_array('admin_ecole', $roles, true)) {
                return redirect()->route('admin_ecole.login')
                    ->withErrors(['email' => 'Veuillez vous connecter à l\'espace d\'administration de votre établissement.']);
            }

            if (in_array('super_admin', $roles, true)) {
                return redirect()->route('superadmin.login')
                    ->withErrors(['auth' => 'Authentification Super Administrateur requise.']);
            }

            return redirect()->route('home')->withErrors(['auth' => 'Authentification requise.']);
        }

        // 2. Utilisateur authentifié mais avec un profil différent : INTERDICTION STRICTE
        // On ne le déconnecte PAS et on ne le redirige PAS vers le login de l'autre profil.
        // On le maintient dans son espace légitime avec un message d'accès refusé.
        if (!in_array($user->role, $roles, true)) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard')
                    ->withErrors(['auth' => 'Accès refusé : vous êtes connecté en tant que Super Administrateur.']);
            }

            if ($user->isAdminEcole()) {
                return redirect()->route('admin_ecole.dashboard')
                    ->withErrors(['auth' => 'Accès non autorisé : cette section n\'est pas accessible depuis l\'administration d\'établissement.']);
            }

            if ($user->isEtudiant()) {
                $codeUnique = $user->ecole?->code_unique;
                return $codeUnique
                    ? redirect()->route('tenant.dashboard', ['code_unique' => $codeUnique])
                        ->withErrors(['auth' => 'Accès refusé : votre profil étudiant ne dispose pas des droits d\'administration.'])
                    : redirect()->route('myportal')
                        ->withErrors(['auth' => 'Accès refusé : espace d\'administration réservé.']);
            }

            return redirect()->route('home')->withErrors(['auth' => 'Accès non autorisé pour ce profil.']);
        }

        // 3. Vérification d'isolation pour l'admin école : doit avoir une école associée
        if ($user->role === 'admin_ecole' && $user->ecole_id === null) {
            auth()->logout();
            return redirect()->route('admin_ecole.login')
                ->withErrors(['email' => 'Compte administrateur non associé à un établissement scolaire.']);
        }

        // 4. Cloisonnement strict multi-tenant pour les étudiants : interdiction d'accéder à un autre établissement
        if ($user->isEtudiant()) {
            $routeCode = $request->route('code_unique');
            if ($routeCode && $user->ecole && strtoupper((string) $user->ecole->code_unique) !== strtoupper((string) $routeCode)) {
                return redirect()->route('tenant.dashboard', ['code_unique' => $user->ecole->code_unique])
                    ->withErrors(['auth' => 'Accès refusé : vous êtes inscrit(e) à l\'établissement ' . ($user->ecole->nom ?? $user->ecole->code_unique) . '. Vous ne pouvez pas accéder à un autre établissement.']);
            }
        }

        return $next($request);
    }
}
