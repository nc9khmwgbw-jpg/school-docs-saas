<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modeles_documents')) {
            Schema::create('modeles_documents', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->string('titre', 200);
                $table->enum('type_document', [
                    'attestation_reussite',
                    'certificat_scolarite',
                    'releve_notes',
                    'convention_stage',
                    'certificat',
                    'duplicata',
                    'autre'
                ]);
                $table->text('description')->nullable();
                $table->mediumText('contenu_html');
                $table->text('styles_css')->nullable();
                $table->boolean('actif')->default(true);
                $table->boolean('validation_requise')->default(false);
                $table->enum('langue', ['fr', 'ar', 'fr_ar'])->default('fr');
                $table->unsignedInteger('expire_dans_jours')->nullable();
                $table->json('variables_schema')->nullable();
                $table->timestamps();

                $table->index(['ecole_id', 'actif'], 'idx_modeles_ecole_actif');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modeles_documents');
    }
};
