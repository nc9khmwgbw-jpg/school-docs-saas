<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_generes')) {
            Schema::create('documents_generes', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
                $table->foreignId('demande_id')->constrained('demandes_documents')->cascadeOnDelete();
                $table->string('chemin_fichier', 255);
                $table->string('nom_fichier', 255);
                $table->unsignedBigInteger('taille_octets')->default(0);
                $table->char('hash_sha256', 64);
                $table->unsignedInteger('telechargements_count')->default(0);
                $table->timestamp('expire_le')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique('demande_id', 'uq_doc_demande');
                $table->index(['ecole_id', 'uuid'], 'idx_doc_ecole_uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_generes');
    }
};
