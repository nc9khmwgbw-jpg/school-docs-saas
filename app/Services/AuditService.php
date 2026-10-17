<?php

namespace App\Services;

use App\Models\LogActivite;
use Illuminate\Http\Request;

/**
 * Service de journalisation d'audit.
 * Enregistre chaque action sensible dans la table logs_activite.
 * Conforme aux exigences de traçabilité de la loi marocaine 09-08.
 */
class AuditService
{
    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string      $action       Constante LogActivite::ACTION_*
     * @param int|null    $utilisateurId
     * @param int|null    $ecoleId
     * @param string|null $cibleType    Ex: 'DemandeDocument', 'Ecole'
     * @param int|null    $cibleId
     * @param array       $details      Données contextuelles supplémentaires
     * @param Request|null $request
     */
    public static function log(
        string   $action,
        ?int     $utilisateurId = null,
        ?int     $ecoleId       = null,
        ?string  $cibleType     = null,
        ?int     $cibleId       = null,
        array    $details       = [],
        ?Request $request       = null
    ): void {
        try {
            $req = $request ?? request();

            LogActivite::create([
                'action'         => $action,
                'utilisateur_id' => $utilisateurId,
                'ecole_id'       => $ecoleId,
                'cible_type'     => $cibleType,
                'cible_id'       => $cibleId,
                'details'        => $details ?: null,
                'ip_address'     => $req?->ip(),
                'user_agent'     => $req?->userAgent(),
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            // L'audit ne doit jamais bloquer le flux principal
            \Illuminate\Support\Facades\Log::warning('AuditService::log() erreur : ' . $e->getMessage());
        }
    }

    /**
     * Raccourci pour logger l'utilisateur authentifié courant.
     */
    public static function logAuth(string $action, ?string $cibleType = null, ?int $cibleId = null, array $details = []): void
    {
        $user = auth()->user();
        static::log(
            action:        $action,
            utilisateurId: $user?->id,
            ecoleId:       $user?->ecole_id,
            cibleType:     $cibleType,
            cibleId:       $cibleId,
            details:       $details
        );
    }
}
