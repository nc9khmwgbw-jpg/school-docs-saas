<?php

namespace App\Models\Traits;

use App\Models\Ecole;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Initialisation du trait : application automatique du scope et assignation de l'ID tenant.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            $currentTenantId = TenantScope::getTenantId();
            if ($currentTenantId !== null && empty($model->ecole_id)) {
                $model->ecole_id = $currentTenantId;
            }
        });
    }

    /**
     * Relation vers l'école parente.
     */
    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }
}
