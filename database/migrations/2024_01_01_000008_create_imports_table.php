<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('imports')) {
            Schema::create('imports', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
                $table->string('nom_fichier_original', 255)->nullable();
                $table->enum('statut', ['en_cours', 'termine', 'erreur'])->default('en_cours');
                $table->unsignedInteger('lignes_traitees')->default(0);
                $table->unsignedInteger('lignes_ignorees')->default(0);
                $table->json('rapport_erreurs')->nullable();
                $table->timestamp('termine_le')->nullable();
                $table->timestamps();

                $table->index(['ecole_id', 'created_at'], 'idx_imports_ecole');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
