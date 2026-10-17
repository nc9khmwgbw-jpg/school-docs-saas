<?php

namespace App\Policies;

use App\Models\DocumentGenere;
use App\Models\Utilisateur;

class DocumentGenerePolicy
{
    /**
     * Détermine si l'utilisateur peut télécharger le document.
     */
    public function download(Utilisateur $user, DocumentGenere $document): bool
    {
        // Super admin peut tout télécharger
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin école peut télécharger les documents de son école
        if ($user->role === 'admin_ecole') {
            return $document->ecole_id === $user->ecole_id;
        }

        // Étudiant peut télécharger uniquement ses propres documents
        if ($user->role === 'etudiant') {
            return $document->demande 
                && $document->demande->etudiant_id === $user->id
                && $document->ecole_id === $user->ecole_id;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut voir le document.
     */
    public function view(Utilisateur $user, DocumentGenere $document): bool
    {
        // Même logique que download
        return $this->download($user, $document);
    }

    /**
     * Détermine si l'utilisateur peut supprimer le document.
     */
    public function delete(Utilisateur $user, DocumentGenere $document): bool
    {
        // Seul le super admin peut supprimer
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin école peut supprimer les documents de son école
        if ($user->role === 'admin_ecole') {
            return $document->ecole_id === $user->ecole_id;
        }

        return false;
    }
}
