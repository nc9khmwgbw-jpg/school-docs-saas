<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('demandes_documents')) {
            Schema::create('demandes_documents', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->foreignId('etudiant_id')->constrained('utilisateurs')->cascadeOnDelete();
                $table->foreignId('modele_id')->constrained('modeles_documents')->restrictOnDelete();
                $table->enum('statut', ['en_attente', 'en_traitement', 'genere', 'valide', 'refuse'])->default('en_attente');
                $table->string('remarque', 500)->nullable();
                $table->string('motif_refus', 500)->nullable();
                $table->foreignId('traite_par')->nullable()->constrained('utilisateurs')->nullOnDelete();
                $table->timestamp('date_traitement')->nullable();
                $table->timestamps();

                $table->index(['ecole_id', 'statut'], 'idx_demandes_ecole_statut');
                $table->index('etudiant_id', 'idx_demandes_etudiant');
                $table->index('created_at', 'idx_demandes_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_documents');
    }
};
