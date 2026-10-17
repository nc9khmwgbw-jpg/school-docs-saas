<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Trace chaque import CSV/Excel : fichier, statut et rapport d'erreurs ligne par ligne.
 */
class Import extends Model
{
    use BelongsToTenant;

    protected $table = 'imports';

    protected $fillable = [
        'uuid',
        'ecole_id',
        'utilisateur_id',
        'nom_fichier_original',
        'chemin_fichier',
        'statut',               // en_cours, termine, erreur
        'lignes_traitees',
        'lignes_ignorees',
        'rapport_erreurs',      // JSON : [{ligne: 14, erreur: 'CNE manquant'}, ...]
        'termine_le',
    ];

    protected $casts = [
        'rapport_erreurs' => 'array',
        'termine_le'      => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($import) {
            if (empty($import->uuid)) {
                $import->uuid = (string) Str::uuid();
            }
        });
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
