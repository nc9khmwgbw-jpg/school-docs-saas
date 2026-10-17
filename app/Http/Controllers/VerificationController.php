<?php

namespace App\Http\Controllers;

use App\Models\DocumentGenere;
use App\Models\LogActivite;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Page publique de vérification de l'authenticité d'un document par QR code.
 * Accessible sans authentification. Affiche uniquement les infos minimales.
 * Protégée par rate-limiting pour éviter l'énumération.
 */
class VerificationController extends Controller
{
    /**
     * Vérifie l'authenticité d'un document via son UUID.
     * Route: GET /verifier/{uuid}
     */
    public function verifier(Request $request, string $uuid)
    {
        // Rate limiting : max 20 vérifications par IP par minute
        $rateLimitKey = 'verification:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            abort(429, 'Trop de requêtes. Veuillez patienter avant de réessayer.');
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Recherche par UUID uniquement (jamais par ID incrémental)
        $document = DocumentGenere::withoutGlobalScopes()
            ->with(['demande.modele', 'demande.etudiant', 'demande.ecole'])
            ->where('uuid', $uuid)
            ->first();

        $estAuthentique = $document !== null;
        $estValide      = $document?->estValide() ?? false;
        $estExpire      = $document !== null && !$estValide;

        // Audit log de la vérification QR code
        AuditService::log(
            action:    LogActivite::ACTION_VERIFICATION_QR,
            ecoleId:   $document?->ecole_id,
            cibleType: 'DocumentGenere',
            cibleId:   $document?->id,
            details:   [
                'uuid'          => $uuid,
                'authentique'   => $estAuthentique,
                'valide'        => $estValide,
            ]
        );

        // Données complètes certifiées pour détection anti-fraude
        $infos = null;
        if ($estAuthentique) {
            $demande  = $document->demande;
            $etudiant = $demande?->etudiant;
            $ecole    = $demande?->ecole;

            $etudiantData = \App\Models\EtudiantDonnee::where('ecole_id', $document->ecole_id)
                ->where('matricule', $etudiant?->matricule)
                ->first();

            $infos = [
                'type_document'       => $demande?->modele?->titre ?? 'Document scolaire',
                'prenom_etudiant'     => $etudiant?->prenom,
                'nom_etudiant'        => $etudiant?->nom,
                'matricule'           => $etudiant?->matricule ?? 'N/A',
                'filiere'             => $etudiantData?->filiere ?? 'N/A',
                'niveau'              => $etudiantData?->niveau ?? 'N/A',
                'annee_universitaire' => $etudiantData?->annee_universitaire ?? date('Y'),
                'ecole'               => $ecole?->nom,
                'date_delivrance'     => $document->created_at?->format('d/m/Y à H:i'),
                'date_expiration'     => $document->expire_le?->format('d/m/Y'),
                'hash_sha256'         => $document->hash_sha256,
                'taille_ko'           => round($document->taille_octets / 1024, 1),
                'photo_url'           => $etudiant?->photo_url,
                'logo_url'            => $ecole?->logo_url,
            ];
        }

        return view('public.verification', compact('estAuthentique', 'estExpire', 'infos', 'uuid'));
    }

    /**
     * Permet à l'autorité ou au recruteur de consulter/télécharger la copie originale
     * conservée dans le coffre-fort numérique afin de comparer avec le papier présenté.
     * Route: GET /verifier/{uuid}/original
     */
    public function telechargerOriginal(Request $request, string $uuid)
    {
        $rateLimitKey = 'verification_download:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            abort(429, 'Trop de téléchargements. Veuillez patienter.');
        }
        RateLimiter::hit($rateLimitKey, 60);

        $document = DocumentGenere::withoutGlobalScopes()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($document->chemin_fichier)) {
            abort(404, 'Copie originale introuvable sur le serveur sécurisé.');
        }

        AuditService::log(
            action:    LogActivite::ACTION_VERIFICATION_QR,
            ecoleId:   $document->ecole_id,
            cibleType: 'DocumentGenere',
            cibleId:   $document->id,
            details:   ['action' => 'consultation_original_anti_fraude', 'uuid' => $uuid]
        );

        return response()->file(
            \Illuminate\Support\Facades\Storage::disk('local')->path($document->chemin_fichier),
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ORIGINAL_' . $document->nom_fichier . '"'
            ]
        );
    }
}
