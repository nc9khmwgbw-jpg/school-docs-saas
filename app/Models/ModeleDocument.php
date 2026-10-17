<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Modèle de document avec :
 * - validation_requise : si true, l'admin valide avant génération du PDF
 * - langue : fr | ar | fr_ar (bilingue)
 * - expire_dans_jours : durée de validité du document généré (null = illimité)
 * - variables_schema : liste JSON des variables disponibles dans le modèle
 */
class ModeleDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'modeles_documents';

    protected $fillable = [
        'uuid',
        'ecole_id',
        'titre',
        'type_document',
        'description',
        'contenu_html',
        'styles_css',
        'actif',
        'validation_requise',    // boolean - si true : workflow admin requis
        'langue',                // 'fr' | 'ar' | 'fr_ar'
        'expire_dans_jours',     // null = illimité, int = durée de validité
        'variables_schema',      // JSON : liste des variables utilisées dans le modèle
    ];

    protected $casts = [
        'actif'              => 'boolean',
        'validation_requise' => 'boolean',
        'expire_dans_jours'  => 'integer',
        'variables_schema'   => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($modele) {
            if (empty($modele->uuid)) {
                $modele->uuid = (string) Str::uuid();
            }
        });
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeDocument::class, 'modele_id');
    }

    /**
     * Extrait les noms de variables ({variable}) du contenu HTML.
     * Utile pour l'aperçu et la validation des templates.
     */
    public function extraireVariables(): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $this->contenu_html ?? '', $matches);
        return array_unique($matches[1] ?? []);
    }
}
