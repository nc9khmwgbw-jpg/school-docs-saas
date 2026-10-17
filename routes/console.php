<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées (Laravel 11 Scheduler)
|--------------------------------------------------------------------------
| Ces tâches sont automatiquement exécutées en production via cron :
| * * * * * cd /var/www/docscolaire && php artisan schedule:run >> /dev/null 2>&1
*/

// 1. Nettoyage automatique des jobs échoués de plus de 30 jours (chaque jour à 02:00)
Schedule::command('queue:clean-failed --days=30')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 2. Nettoyage des logs d'activité plus anciens que 12 mois pour conformité CNDP (chaque dimanche à 03:00)
Schedule::command('logs:clean --months=12')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 3. Purge des tokens de réinitialisation de mot de passe expirés (toutes les 15 minutes)
Schedule::command('auth:clear-resets')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
