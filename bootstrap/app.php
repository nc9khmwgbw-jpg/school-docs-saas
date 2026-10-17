<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ SÉCURITÉ : Restreindre la confiance aux réseaux internes (Docker bridge, localhost, reverse proxy)
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('portal/*')) {
                $code = $request->route('code_unique');
                return $code ? route('tenant.portal', ['code_unique' => $code]) : route('myportal');
            }
            if ($request->is('myportal') || $request->is('myportal/*')) {
                return route('myportal');
            }
            if ($request->is('superadmin') || $request->is('superadmin/*')) {
                return route('superadmin.login');
            }
            if ($request->is('admin-ecole') || $request->is('admin-ecole/*')) {
                return route('admin_ecole.login');
            }
            return route('home');
        });

        $middleware->alias([
            'identify.tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'role'            => \App\Http\Middleware\CheckRole::class,
            'throttle.login'  => \App\Http\Middleware\ThrottleLogin::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
