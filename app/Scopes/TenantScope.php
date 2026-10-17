<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * ID de l'établissement tenant actif pour la requête courante.
     */
    protected static ?int $tenantId = null;

    /**
     * Définit le tenant actif.
     */
    public static function setTenantId(?int $id): void
    {
        static::$tenantId = $id;
    }

    /**
     * Récupère le tenant actif.
     */
    public static function getTenantId(): ?int
    {
        return static::$tenantId;
    }

    /**
     * Applique la clause WHERE ecole_id = ? systématiquement.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (static::$tenantId !== null) {
            $builder->where($model->getTable() . '.ecole_id', '=', static::$tenantId);
        }
    }
}
