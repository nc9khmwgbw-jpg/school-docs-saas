<?php

namespace App\Http\Middleware;

use App\Models\Ecole;
use App\Scopes\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Intercepte la requête HTTP pour identifier le tenant (école) actif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // 1. Détection via paramètre de route (ex: /ecole/{code_unique})
        $codeUnique = $request->route('code_unique');
        if ($codeUnique) {
            $tenant = Ecole::where('code_unique', $codeUnique)
                ->where('statut', 'actif')
                ->first();

            if (!$tenant) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Établissement introuvable ou compte inactif."
                    ], 404);
                }
                return redirect()->route('home')->withErrors([
                    'code_unique' => "Établissement '{$codeUnique}' introuvable ou inactif."
                ]);
            }
        }

        // 2. Détection via l'utilisateur connecté en session/token
        if (!$tenant && auth()->check()) {
            $user = auth()->user();
            if ($user->ecole_id) {
                $tenant = Ecole::find($user->ecole_id);
            }
        }

        // 3. Détection via en-tête HTTP X-Tenant-Code (pour les APIs)
        if (!$tenant && $request->hasHeader('X-Tenant-Code')) {
            $tenant = Ecole::where('code_unique', $request->header('X-Tenant-Code'))
                ->where('statut', 'actif')
                ->first();
        }

        if ($tenant) {
            $isPublicPortalRoute = in_array($request->route()?->getName(), [
                'tenant.portal',
                'tenant.login',
                'tenant.logout',
                'tenant.password.forgot',
                'tenant.password.forgot.send',
                'tenant.password.reset',
                'tenant.password.reset.update',
            ], true);

            if ($isPublicPortalRoute) {
                // Si l'utilisateur est connecté mais avec une session d'un autre établissement,
                // on réinitialise la session pour qu'il puisse accéder au portail de cet établissement sans être redirigé vers l'autre école.
                if (auth()->check()) {
                    $user = auth()->user();
                    if ($user->ecole_id && $user->ecole_id !== $tenant->id) {
                        auth()->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    }
                }
            } else {
                // Vérification stricte d'étanchéité multi-tenants pour les étudiants connectés sur les routes privées
                if (auth()->check()) {
                    $user = auth()->user();
                    if ($user->isEtudiant() && $user->ecole_id !== $tenant->id) {
                        $userEcole = $user->ecole;
                        $userEcoleCode = $userEcole?->code_unique;
                        if ($userEcoleCode) {
                            return redirect()->route('tenant.dashboard', ['code_unique' => $userEcoleCode])
                                ->withErrors(['auth' => "Accès refusé : vous êtes inscrit(e) à l'établissement '{$userEcole->nom}'. Vous ne pouvez pas accéder aux services de '{$tenant->nom}'."]);
                        }
                        abort(403, "Accès refusé : vous n'appartenez pas à cet établissement.");
                    }
                }
            }

            // Enregistrement dans le Scope global
            TenantScope::setTenantId($tenant->id);
            
            // Partage dans la requête pour contrôleurs et vues Blade
            $request->attributes->set('tenant', $tenant);
            view()->share('tenantActuel', $tenant);
        }

        return $next($request);
    }
}
