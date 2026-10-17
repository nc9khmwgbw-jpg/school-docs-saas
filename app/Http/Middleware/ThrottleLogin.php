<?php

namespace App\Http\Middleware;

use App\Models\LogActivite;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate-limiting robuste sur la connexion (Défense en profondeur) :
 * 1. Protection globale par IP : max 30 tentatives par IP sur 10 minutes (anti credential stuffing)
 * 2. Protection ciblée par compte : max 5 tentatives par compte sur 10 minutes (anti force brute ciblée)
 * Journalise les incidents dans les logs d'activité (Loi 09-08 / CNDP).
 */
class ThrottleLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $identifiant = trim((string) ($request->input('identifiant') ?? $request->input('matricule') ?? $request->input('email') ?? ''));
        $ip = $request->ip();

        // 1. Clé globale par IP (bloque les attaques distribuées sur plusieurs comptes depuis la même IP)
        $ipKey = 'login_ip:' . $ip;
        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $seconds = RateLimiter::availableIn($ipKey);

            AuditService::log(
                action:        LogActivite::ACTION_ECHEC_CONNEXION,
                utilisateurId: null,
                ecoleId:       null,
                details: [
                    'raison'      => 'rate_limit_ip_global_atteint',
                    'ip'          => $ip,
                    'attendre'    => $seconds . 's',
                ]
            );

            return back()->withErrors([
                'auth' => "Trop de requêtes de connexion depuis votre adresse IP. Veuillez patienter {$seconds} secondes avant de réessayer."
            ])->withInput($request->except('mot_de_passe'));
        }

        // 2. Clé par IP + Compte ciblé
        $accountKey = 'login_acc:' . $ip . '|' . strtolower($identifiant);
        if (RateLimiter::tooManyAttempts($accountKey, 5)) {
            $seconds = RateLimiter::availableIn($accountKey);

            AuditService::log(
                action:        LogActivite::ACTION_ECHEC_CONNEXION,
                utilisateurId: null,
                ecoleId:       null,
                details: [
                    'raison'      => 'rate_limit_compte_atteint',
                    'identifiant' => $identifiant,
                    'matricule'   => $identifiant,
                    'attendre'    => $seconds . 's',
                ]
            );

            return back()->withErrors([
                'auth' => "Nombre maximal de tentatives atteint pour ce compte. Veuillez patienter {$seconds} secondes."
            ])->withInput($request->except('mot_de_passe'));
        }

        $response = $next($request);

        // Si la connexion a échoué (redirection avec erreurs dans la session), on incrémente les compteurs
        if ($response->isRedirect() && session()->has('errors')) {
            RateLimiter::hit($ipKey, 600);
            RateLimiter::hit($accountKey, 600);
        } else {
            // Connexion réussie : on réinitialise le compteur du compte
            RateLimiter::clear($accountKey);
        }

        return $response;
    }
}
