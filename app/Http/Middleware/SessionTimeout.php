<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de gestion automatique de l'expiration de session par inactivité.
 * Utilise le cache haute performance (Redis) pour tracker l'activité sans dépendre de l'écriture en session.
 */
class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $cacheKey = "user_last_activity:{$userId}";
            $timeoutSeconds = config('session.lifetime', 120) * 60;

            $lastActivity = Cache::get($cacheKey);

            if ($lastActivity && (time() - $lastActivity) > $timeoutSeconds) {
                Cache::forget($cacheKey);
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('home')->withErrors([
                    'auth' => 'Votre session a expiré par inactivité. Veuillez vous reconnecter.'
                ]);
            }

            // Met à jour l'horodatage d'activité avec un TTL égal à la durée de session
            Cache::put($cacheKey, time(), now()->addMinutes(config('session.lifetime', 120)));
            session(['last_activity_at' => time()]);
        }

        return $next($request);
    }
}
