<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ecole extends Model
{
    protected $table = 'ecoles';

    protected $fillable = [
        'uuid',
        'nom',
        'code_unique',
        'adresse',
        'email_contact',
        'telephone',
        'logo_path',
        'logo_data',
        'logo_blob',
        'cachet_path',      // Image du cachet officiel
        'cachet_data',
        'cachet_blob',
        'signature_path',   // Image de la signature du directeur
        'signature_data',
        'signature_blob',
        'couleur_primaire',
        'couleur_secondaire',
        'statut',
    ];

    protected $hidden = [
        'logo_blob',
        'cachet_blob',
        'signature_blob',
    ];

    /**
     * Data URI ou URL directe du logo officiel de l'établissement.
     */
    public function getLogoUrlAttribute(): string
    {
        if (!empty($this->logo_data)) {
            return $this->logo_data;
        }

        if (!empty($this->logo_blob)) {
            return 'data:image/png;base64,' . base64_encode($this->logo_blob);
        }

        if (!empty($this->logo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/' . $this->logo_path);
        }

        return '';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ecole) {
            if (empty($ecole->uuid)) {
                $ecole->uuid = (string) Str::uuid();
            }
        });
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'ecole_id');
    }

    public function modelesDocuments(): HasMany
    {
        return $this->hasMany(ModeleDocument::class, 'ecole_id');
    }

    public function etudiantsDonnees(): HasMany
    {
        return $this->hasMany(EtudiantDonnee::class, 'ecole_id');
    }

    public function demandesDocuments(): HasMany
    {
        return $this->hasMany(DemandeDocument::class, 'ecole_id');
    }

    public function documentsGeneres(): HasMany
    {
        return $this->hasMany(DocumentGenere::class, 'ecole_id');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class, 'ecole_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LogActivite::class, 'ecole_id');
    }

    /**
     * Sigle / mot clé emblématique pour l'affichage du portail et de l'animation géante.
     * Exemples:
     *  - "École Nationale Supérieure (ENS)" -> "ENS"
     *  - "Institut Polytechnique de Casablanca" (POLY-2026) -> "POLY"
     *  - "Faculté des Sciences et Techniques" (FST-2026) -> "FST"
     */
    public function getSigleAttribute(): string
    {
        // 1. Sigle explicite entre parenthèses dans le nom (ex: "(ENS)")
        if (preg_match('/\(([^)]+)\)/u', (string)$this->nom, $matches)) {
            $val = trim($matches[1]);
            if (mb_strlen($val) >= 2 && mb_strlen($val) <= 10) {
                return strtoupper($val);
            }
        }

        // 2. Préfixe du code unique (ex: "POLY-2026" -> "POLY", "ENS-2026" -> "ENS")
        if (!empty($this->code_unique) && str_contains($this->code_unique, '-')) {
            $prefix = trim(explode('-', $this->code_unique)[0]);
            if (mb_strlen($prefix) >= 2 && mb_strlen($prefix) <= 12) {
                return strtoupper($prefix);
            }
        }

        // 3. Code unique direct si court (ex: "EMSI", "HEC")
        if (!empty($this->code_unique) && mb_strlen($this->code_unique) <= 8) {
            return strtoupper(trim($this->code_unique));
        }

        // 4. Si le nom lui-même est concis (ex: "Polytechnique")
        $cleanNom = trim((string)$this->nom);
        if (mb_strlen($cleanNom) <= 12 && !str_contains($cleanNom, ' ')) {
            return strtoupper($cleanNom);
        }

        // 5. Acronyme généré à partir des initiales des mots significatifs
        $words = preg_split('/\s+/u', $cleanNom);
        $acronym = '';
        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) > 2 && !in_array(mb_strtolower($w), ['des', 'les', 'aux', 'une', 'par', 'sur', 'pour', 'dans', 'et', 'de', 'du'])) {
                $acronym .= mb_substr($w, 0, 1);
            }
        }

        return strtoupper($acronym ?: 'ECOLE');
    }

    public function getNomCourtAttribute(): string
    {
        return $this->sigle;
    }
}
