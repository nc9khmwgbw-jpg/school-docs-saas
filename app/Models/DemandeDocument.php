<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Demande de document enrichie :
 * - statut : en_attente | en_traitement | genere | valide | refuse
 * - motif_refus : texte libre saisi par l'admin en cas de refus
 * - date_traitement : horodatage de la validation ou du refus
 * - traite_par : ID de l'administrateur ayant traité la demande
 */
class DemandeDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'demandes_documents';

    // Statuts possibles
    public const STATUT_EN_ATTENTE    = 'en_attente';
    public const STATUT_EN_TRAITEMENT = 'en_traitement';
    public const STATUT_GENERE        = 'genere';
    public const STATUT_VALIDE        = 'valide';       // Validé par admin, PDF en cours
    public const STATUT_REFUSE        = 'refuse';

    protected $fillable = [
        'uuid',
        'ecole_id',
        'etudiant_id',
        'modele_id',
        'statut',
        'remarque',
        'motif_refus',        // Motif saisi par l'admin en cas de refus
        'date_traitement',    // Horodatage de la décision admin
        'traite_par',         // ID de l'utilisateur admin ayant traité
    ];

    protected $casts = [
        'date_traitement' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($demande) {
            if (empty($demande->uuid)) {
                $demande->uuid = (string) Str::uuid();
            }
        });
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'etudiant_id');
    }

    public function modele(): BelongsTo
    {
        return $this->belongsTo(ModeleDocument::class, 'modele_id');
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'traite_par');
    }

    public function documentGenere(): HasOne
    {
        return $this->hasOne(DocumentGenere::class, 'demande_id');
    }

    // Helpers d'état
    public function estEnAttente(): bool    { return $this->statut === self::STATUT_EN_ATTENTE; }
    public function estGenere(): bool       { return $this->statut === self::STATUT_GENERE; }
    public function estRefuse(): bool       { return $this->statut === self::STATUT_REFUSE; }
    public function estTelechargeable(): bool { return $this->estGenere() && $this->documentGenere !== null; }
}
