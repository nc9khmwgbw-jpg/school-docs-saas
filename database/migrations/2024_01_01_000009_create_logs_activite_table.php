<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('logs_activite')) {
            Schema::create('logs_activite', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ecole_id')->nullable()->constrained('ecoles')->nullOnDelete();
                $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
                $table->string('action', 100);
                $table->string('cible_type', 100)->nullable();
                $table->unsignedBigInteger('cible_id')->nullable();
                $table->json('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['ecole_id', 'created_at'], 'idx_logs_ecole');
                $table->index('utilisateur_id', 'idx_logs_utilisateur');
                $table->index('action', 'idx_logs_action');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_activite');
    }
};
