<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ecoles')) {
            Schema::create('ecoles', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->string('nom', 255);
                $table->string('code_unique', 50)->unique();
                $table->text('adresse')->nullable();
                $table->string('email_contact', 255)->nullable();
                $table->string('telephone', 50)->nullable();
                $table->string('logo_path', 255)->nullable();
                $table->longText('logo_data')->nullable();
                $table->binary('logo_blob')->nullable();
                $table->string('cachet_path', 255)->nullable();
                $table->longText('cachet_data')->nullable();
                $table->binary('cachet_blob')->nullable();
                $table->string('signature_path', 255)->nullable();
                $table->longText('signature_data')->nullable();
                $table->binary('signature_blob')->nullable();
                $table->string('couleur_primaire', 7)->default('#2563EB');
                $table->string('couleur_secondaire', 7)->default('#1E40AF');
                $table->enum('statut', ['actif', 'suspendu', 'en_attente'])->default('actif');
                $table->timestamps();

                $table->index('code_unique', 'idx_ecole_code');
                $table->index('statut', 'idx_ecole_statut');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
