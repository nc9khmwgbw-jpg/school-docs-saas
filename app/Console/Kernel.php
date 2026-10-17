<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ MAINTENANCE : Nettoyage automatique des jobs échoués (chaque jour à 2h du matin)
        $schedule->command('queue:clean-failed --days=30')->dailyAt('02:00');
        
        // ✅ MAINTENANCE : Nettoyage des logs anciens (chaque semaine le dimanche à 3h)
        $schedule->command('logs:clean --months=12')->weekly()->sundays()->at('03:00');
        
        // ✅ MAINTENANCE : Nettoyage des sessions expirées Laravel
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
