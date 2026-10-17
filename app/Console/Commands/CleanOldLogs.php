<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--months=12 : Nombre de mois à conserver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les logs d\'activité plus anciens que X mois';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $months = (int) $this->option('months');
        
        $count = DB::table('logs_activite')
            ->where('created_at', '<', now()->subMonths($months))
            ->delete();

        $this->info("✅ {$count} logs d'activité supprimés (plus anciens que {$months} mois).");

        return Command::SUCCESS;
    }
}
