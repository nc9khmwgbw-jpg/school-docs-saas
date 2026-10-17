<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Document PDF généré.
 * - expire_le : date d'expiration calculée depuis modele.expire_dans_jours
 * - Le chemin_fichier pointe vers storage/app/private/ (jamais accessible directement via URL)
 */
class DocumentGenere extends Model
{
    use BelongsToTenant;

    protected $table = 'documents_generes';

    protected $fillable = [
        'uuid',
        'ecole_id',
        'demande_id',
        'chemin_fichier',
        'nom_fichier',
        'taille_octets',
        'hash_sha256',
        'telechargements_count',
        'expire_le',          // Date d'expiration du document (null = illimité)
    ];

    protected $casts = [
        'expire_le'   => 'datetime',
        'created_at'  => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($doc) {
            if (empty($doc->uuid)) {
                $doc->uuid = (string) Str::uuid();
            }
            if (empty($doc->created_at)) {
                $doc->created_at = now();
            }
        });
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDocument::class, 'demande_id');
    }

    /**
     * Vérifie si le document est encore valide (non expiré).
     */
    public function estValide(): bool
    {
        return $this->expire_le === null || $this->expire_le->isFuture();
    }

    /**
     * URL de vérification QR code publique.
     */
    public function urlVerification(): string
    {
        return route('public.verifier', ['uuid' => $this->uuid]);
    }
}
