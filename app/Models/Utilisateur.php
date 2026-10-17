<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Utilisateur extends Authenticatable
{
    use BelongsToTenant;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'uuid',
        'ecole_id',
        'role',
        'matricule',
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'photo_path',
        'photo_data',
        'photo_blob',
        'statut',
        'doit_changer_mot_de_passe',
    ];

    protected $casts = [
        'doit_changer_mot_de_passe' => 'boolean',
    ];

    protected $hidden = [
        'mot_de_passe',
        'photo_blob'
    ];

    /**
     * URL directe ou Data URI de la photo étudiante.
     */
    public function getPhotoUrlAttribute(): string
    {
        if (!empty($this->photo_data)) {
            return $this->photo_data;
        }

        if (!empty($this->photo_blob)) {
            return 'data:image/jpeg;base64,' . base64_encode($this->photo_blob);
        }

        if (!empty($this->photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }

        // SVG Avatar haute définition par défaut avec initiales
        $initials = strtoupper(mb_substr($this->prenom ?? 'E', 0, 1) . mb_substr($this->nom ?? 'T', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
            . '<rect width="100" height="100" rx="20" fill="#1e293b"/>'
            . '<text x="50" y="58" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif" font-size="34" font-weight="700" fill="#f8fafc" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</text>'
            . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photo_data) || !empty($this->photo_blob) || (!empty($this->photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path));
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminEcole(): bool
    {
        return $this->role === 'admin_ecole';
    }

    public function isEtudiant(): bool
    {
        return $this->role === 'etudiant';
    }

    public function ecole(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeDocument::class, 'etudiant_id');
    }
}
