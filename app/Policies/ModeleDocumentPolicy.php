<?php

namespace App\Policies;

use App\Models\ModeleDocument;
use App\Models\Utilisateur;

class ModeleDocumentPolicy
{
    /**
     * Détermine si l'utilisateur peut créer un modèle.
     */
    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin_ecole']);
    }

    /**
     * Détermine si l'utilisateur peut modifier le modèle.
     */
    public function update(Utilisateur $user, ModeleDocument $modele): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'admin_ecole') {
            return $modele->ecole_id === $user->ecole_id;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer le modèle.
     */
    public function delete(Utilisateur $user, ModeleDocument $modele): bool
    {
        return $this->update($user, $modele);
    }

    /**
     * Détermine si l'utilisateur peut voir le modèle.
     */
    public function view(Utilisateur $user, ModeleDocument $modele): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin et étudiants peuvent voir les modèles de leur école
        return $modele->ecole_id === $user->ecole_id;
    }
}
