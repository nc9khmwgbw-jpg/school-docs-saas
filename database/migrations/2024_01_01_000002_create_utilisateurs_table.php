<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('utilisateurs')) {
            Schema::create('utilisateurs', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('ecole_id')->nullable()->constrained('ecoles')->cascadeOnDelete();
                $table->enum('role', ['super_admin', 'admin_ecole', 'etudiant']);
                $table->string('matricule', 100)->nullable();
                $table->string('nom', 150);
                $table->string('prenom', 150);
                $table->string('email', 255);
                $table->string('mot_de_passe', 255);
                $table->string('photo_path', 255)->nullable();
                $table->longText('photo_data')->nullable();
                $table->binary('photo_blob')->nullable();
                $table->enum('statut', ['actif', 'inactif'])->default('actif');
                $table->boolean('doit_changer_mot_de_passe')->default(false);
                $table->timestamps();

                $table->index(['ecole_id', 'role'], 'idx_user_ecole_role');
                $table->unique(['ecole_id', 'matricule'], 'uq_user_ecole_matricule');
                $table->unique(['ecole_id', 'email'], 'uq_user_ecole_email');
            });
        } elseif (!Schema::hasColumn('utilisateurs', 'doit_changer_mot_de_passe')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->boolean('doit_changer_mot_de_passe')->default(false)->after('statut');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
