<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notes et résultats importés pour alimenter les relevés de notes.
 * Le champ 'verrouille' empêche la modification d'un résultat déjà utilisé dans un PDF généré.
 */
class Resultat extends Model
{
    use BelongsToTenant;

    protected $table = 'resultats';

    protected $fillable = [
        'ecole_id',
        'etudiant_id',
        'intitule_module',
        'note',
        'note_max',
        'coefficient',
        'semestre',
        'session',
        'annee_universitaire',
        'verrouille',       // true dès qu'un PDF a été généré avec ce résultat
        'verrouille_le',
    ];

    protected $casts = [
        'note'         => 'decimal:2',
        'note_max'     => 'decimal:2',
        'coefficient'  => 'decimal:2',
        'verrouille'   => 'boolean',
        'verrouille_le' => 'datetime',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'etudiant_id');
    }

    /**
     * Vérifie si la note est valide (entre 0 et note_max).
     */
    public function estValide(): bool
    {
        return $this->note >= 0 && $this->note <= ($this->note_max ?? 20);
    }
}
