<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table d'audit : trace chaque action sensible (connexion, demande, téléchargement, import, validation).
 * Indispensable pour la conformité légale (loi 09-08) et la traçabilité sur une plateforme multi-écoles.
 */
class LogActivite extends Model
{
    public $timestamps = false;

    protected $table = 'logs_activite';

    protected $fillable = [
        'ecole_id',
        'utilisateur_id',
        'action',
        'cible_type',
        'cible_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    // ── Actions disponibles ──────────────────────────────────────────────────
    public const ACTION_CONNEXION         = 'connexion';
    public const ACTION_DECONNEXION       = 'deconnexion';
    public const ACTION_ECHEC_CONNEXION   = 'echec_connexion';
    public const ACTION_DEMANDE_DOCUMENT  = 'demande_document';
    public const ACTION_VALIDATION        = 'validation_demande';
    public const ACTION_REFUS            = 'refus_demande';
    public const ACTION_TELECHARGEMENT   = 'telechargement_pdf';
    public const ACTION_IMPORT_CSV       = 'import_csv';
    public const ACTION_CREATION_MODELE  = 'creation_modele';
    public const ACTION_CREATION_ECOLE   = 'creation_ecole';
    public const ACTION_RESET_MDP        = 'reset_mot_de_passe';
    public const ACTION_VERIFICATION_QR  = 'verification_qrcode';

    // ── Relations ────────────────────────────────────────────────────────────

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
