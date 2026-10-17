<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('etudiants_donnees')) {
            Schema::create('etudiants_donnees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->string('matricule', 100);
                $table->string('filiere', 150)->nullable();
                $table->string('niveau', 100)->nullable();
                $table->string('annee_universitaire', 50)->nullable();
                $table->string('photo_path', 255)->nullable();
                $table->longText('photo_data')->nullable();
                $table->json('attributs_dynamiques')->default(new \Illuminate\Database\Query\Expression('(JSON_OBJECT())'));
                $table->timestamp('date_import')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['ecole_id', 'matricule'], 'uq_etudiant_ecole_mat');
                $table->index(['ecole_id', 'matricule', 'filiere'], 'idx_etudiant_recherche');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiants_donnees');
    }
};
