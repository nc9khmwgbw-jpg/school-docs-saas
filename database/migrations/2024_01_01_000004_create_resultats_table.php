<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('resultats')) {
            Schema::create('resultats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->foreignId('etudiant_id')->constrained('utilisateurs')->cascadeOnDelete();
                $table->string('intitule_module', 200);
                $table->decimal('note', 5, 2);
                $table->decimal('note_max', 5, 2)->default(20.00);
                $table->decimal('coefficient', 4, 2)->default(1.00);
                $table->string('semestre', 50)->nullable();
                $table->string('session', 50)->nullable();
                $table->string('annee_universitaire', 50)->nullable();
                $table->boolean('verrouille')->default(false);
                $table->timestamp('verrouille_le')->nullable();
                $table->timestamps();

                $table->index(['ecole_id', 'etudiant_id', 'annee_universitaire'], 'idx_resultats_etudiant');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
