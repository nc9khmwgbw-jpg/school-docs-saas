<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:clean-failed {--days=30 : Nombre de jours à conserver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les jobs échoués plus anciens que X jours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        
        $count = DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subDays($days))
            ->delete();

        $this->info("✅ {$count} jobs échoués supprimés (plus anciens que {$days} jours).");

        return Command::SUCCESS;
    }
}
