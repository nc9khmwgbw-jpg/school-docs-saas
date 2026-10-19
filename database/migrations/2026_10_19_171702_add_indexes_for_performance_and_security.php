<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('documents_generes') && !Schema::hasIndex('documents_generes', 'idx_doc_hash_sha256')) {
            Schema::table('documents_generes', function (Blueprint $table) {
                $table->index('hash_sha256', 'idx_doc_hash_sha256');
            });
        }

        if (Schema::hasTable('utilisateurs') && !Schema::hasIndex('utilisateurs', 'idx_user_email')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->index('email', 'idx_user_email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('documents_generes') && Schema::hasIndex('documents_generes', 'idx_doc_hash_sha256')) {
            Schema::table('documents_generes', function (Blueprint $table) {
                $table->dropIndex('idx_doc_hash_sha256');
            });
        }

        if (Schema::hasTable('utilisateurs') && Schema::hasIndex('utilisateurs', 'idx_user_email')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropIndex('idx_user_email');
            });
        }
    }
};
