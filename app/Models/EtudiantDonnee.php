<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class EtudiantDonnee extends Model
{
    use BelongsToTenant;

    protected $table = 'etudiants_donnees';

    protected $fillable = [
        'ecole_id',
        'matricule',
        'filiere',
        'niveau',
        'annee_universitaire',
        'photo_path',
        'photo_data',
        'attributs_dynamiques',
        'date_import'
    ];

    protected $casts = [
        'attributs_dynamiques' => 'array',
        'date_import' => 'datetime'
    ];

    public $timestamps = false;
}
