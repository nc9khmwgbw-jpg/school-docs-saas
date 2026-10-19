<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentPdfJob;
use App\Models\DemandeDocument;
use App\Models\DocumentGenere;
use App\Models\Ecole;
use App\Models\LogActivite;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantPortalController extends Controller
{
    /**
     * Page d'accueil universelle MyPortal - Redirigée vers l'accueil (Marque Blanche stricte).
     * Route: GET /myportal
     */
    public function showMyPortal(Request $request)
    {
        if ($request->has('switch') || $request->has('logout')) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('home');
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isEtudiant() && $user->ecole) {
                return redirect()->route('tenant.dashboard', ['code_unique' => $user->ecole->code_unique]);
            }
            if ($user->isAdminEcole()) {
                return redirect()->route('admin_ecole.dashboard');
            }
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
        }

        if ($request->filled('ecole')) {
            $code = strtoupper(trim($request->input('ecole')));
            $selectedEcole = Ecole::where('statut', 'actif')
                ->where(fn($q) => $q->where('code_unique', $code)->orWhere('sigle', $code))
                ->first();
            if ($selectedEcole) {
                return redirect()->route('tenant.portal', ['code_unique' => $selectedEcole->code_unique]);
            }
        }

        return redirect()->route('home');
    }

    /**
     * Détection dynamique de l'établissement et de l'étudiant selon matricule ou email.
     * Route: GET /myportal/detect-school
     */
    public function detectSchool(Request $request)
    {
        $query = trim($request->input('query', ''));
        if (empty($query)) {
            return response()->json(['found' => false]);
        }

        $queryLower = strtolower($query);
        $queryUpper = strtoupper($query);

        // 1. Recherche par matricule ou email étudiant
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('role', 'etudiant')
            ->where('statut', 'actif')
            ->where(function ($q) use ($query, $queryLower, $queryUpper) {
                $q->where('matricule', $query)
                  ->orWhere('matricule', $queryUpper)
                  ->orWhere('email', $queryLower);
            })
            ->with('ecole')
            ->first();

        if ($etudiant && $etudiant->ecole && $etudiant->ecole->statut === 'actif') {
            return response()->json([
                'found'   => true,
                'type'    => 'student',
                'ecole'   => [
                    'id'          => $etudiant->ecole->id,
                    'nom'         => $etudiant->ecole->nom,
                    'sigle'       => $etudiant->ecole->sigle,
                    'code_unique' => $etudiant->ecole->code_unique,
                ],
            ]);
        }

        // 2. Recherche par sigle, code unique ou domaine email de l'école
        $ecole = Ecole::where('statut', 'actif')
            ->where(function ($q) use ($queryUpper, $queryLower) {
                $q->where('code_unique', $queryUpper)
                  ->orWhere('code_unique', 'LIKE', "{$queryUpper}%")
                  ->orWhere('sigle', $queryUpper)
                  ->orWhere('sigle', 'LIKE', "{$queryUpper}%")
                  ->orWhere('nom', 'LIKE', "%{$queryUpper}%");
            })
            ->first();

        if (!$ecole && str_contains($queryLower, '@')) {
            $parts = explode('@', $queryLower);
            $domain = $parts[1] ?? '';
            if ($domain) {
                $domainPrefix = explode('.', $domain)[0] ?? '';
                $ecole = Ecole::where('statut', 'actif')
                    ->where(function ($q) use ($domain, $domainPrefix) {
                        $q->where('email_contact', 'LIKE', "%@{$domain}%")
                          ->orWhere('nom', 'LIKE', "%{$domainPrefix}%")
                          ->orWhere('sigle', 'LIKE', "%" . strtoupper($domainPrefix) . "%");
                    })
                    ->first();
            }
        }

        if ($ecole) {
            return response()->json([
                'found' => true,
                'type'  => 'school',
                'ecole' => [
                    'id'          => $ecole->id,
                    'nom'         => $ecole->nom,
                    'sigle'       => $ecole->sigle,
                    'code_unique' => $ecole->code_unique,
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Connexion universelle MyPortal avec détection automatique de l'école par matricule ou email.
     * Route: POST /myportal/login
     */
    public function loginMyPortal(Request $request)
    {
        $rawIdent = $request->input('identifiant') ?? $request->input('matricule') ?? $request->input('email');
        if ($rawIdent !== null) {
            $request->merge([
                'identifiant' => trim($rawIdent),
                'matricule'   => trim($rawIdent),
            ]);
        }

        $request->validate([
            'identifiant'  => 'required|string',
            'mot_de_passe' => 'required|string',
            'ecole_id'     => 'nullable|exists:ecoles,id',
        ]);

        $identifiant = trim($request->input('identifiant'));
        $motDePasse  = $request->input('mot_de_passe');
        $ecoleId     = $request->input('ecole_id');
        $identLower  = strtolower($identifiant);
        $identUpper  = strtoupper($identifiant);

        $query = Utilisateur::withoutGlobalScopes()
            ->where('role', 'etudiant')
            ->where('statut', 'actif')
            ->where(function ($q) use ($identifiant, $identLower, $identUpper) {
                $q->where('matricule', $identifiant)
                  ->orWhere('matricule', $identUpper)
                  ->orWhere('matricule', $identLower)
                  ->orWhere('email', $identLower)
                  ->orWhere('email', $identifiant);
            });

        if ($ecoleId) {
            $query->where('ecole_id', $ecoleId);
        }

        $etudiant = $query->first();

        // Si étudiant trouvé et mot de passe correct
        if ($etudiant && Hash::check($motDePasse, $etudiant->mot_de_passe)) {
            $ecole = $etudiant->ecole;
            if (!$ecole || $ecole->statut !== 'actif') {
                return back()->withErrors(['auth' => 'Votre établissement est inactif ou introuvable.']);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            auth()->login($etudiant, remember: false);
            session(['last_activity_at' => time()]);

            AuditService::log(
                action:        LogActivite::ACTION_CONNEXION,
                utilisateurId: $etudiant->id,
                ecoleId:       $ecole->id,
                details:       ['portail' => 'myportal']
            );

            return redirect()->to("/portal/{$ecole->code_unique}/dashboard");
        }

        // Si non étudiant : vérifier compte administrateur école
        $admin = Utilisateur::withoutGlobalScopes()
            ->where('role', 'admin_ecole')
            ->where('statut', 'actif')
            ->where(function ($q) use ($identifiant, $identLower) {
                $q->where('email', $identLower)
                  ->orWhere('email', $identifiant);
            })
            ->first();

        if ($admin && Hash::check($motDePasse, $admin->mot_de_passe)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            auth()->login($admin, remember: false);
            session(['last_activity_at' => time()]);

            AuditService::log(
                action:        LogActivite::ACTION_CONNEXION,
                utilisateurId: $admin->id,
                ecoleId:       $admin->ecole_id,
                details:       ['portail' => 'myportal_redirection_admin']
            );

            return redirect()->route('admin_ecole.dashboard')
                ->with('success', 'Bienvenue sur votre espace d\'administration ' . ($admin->ecole?->nom ?? '') . '.');
        }

        // Vérifier compte superadministrateur
        $superAdmin = Utilisateur::withoutGlobalScopes()
            ->whereIn('role', ['super_admin', 'superadmin'])
            ->where('statut', 'actif')
            ->where(function ($q) use ($identifiant, $identLower) {
                $q->where('email', $identLower)
                  ->orWhere('email', $identifiant);
            })
            ->first();

        if ($superAdmin && Hash::check($motDePasse, $superAdmin->mot_de_passe)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            auth()->login($superAdmin, remember: false);
            session(['last_activity_at' => time()]);

            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Bienvenue sur votre console SuperAdmin.');
        }

        return back()->withErrors(['auth' => 'Identifiant ou mot de passe incorrect.'])->withInput($request->except('mot_de_passe'));
    }

    /**
     * Page d'accueil du portail étudiant pour un établissement donné.
     * Route: GET /portal/{code_unique}
     */
    public function showPortal(\Illuminate\Http\Request $request, string $code_unique)
    {
        $ecole = Ecole::where('code_unique', $code_unique)
            ->where('statut', 'actif')
            ->firstOrFail();

        if ($request->has('switch') || $request->has('logout')) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('tenant.portal', ['code_unique' => $code_unique]);
        }

        if (auth()->check()) {
            $user = auth()->user();
            // Si la session active appartient à un autre établissement, on déconnecte pour afficher proprement ce portail
            if ($user->ecole_id && $user->ecole_id !== $ecole->id) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } else {
                if ($user->isEtudiant()) {
                    return redirect()->route('tenant.dashboard', ['code_unique' => $code_unique]);
                }
                if ($user->isAdminEcole()) {
                    return redirect()->route('admin_ecole.dashboard');
                }
                if ($user->isSuperAdmin()) {
                    return redirect()->route('superadmin.dashboard');
                }
            }
        }

        $ecoles = Ecole::where('statut', 'actif')->get();
        return view('portal.index', compact('ecole', 'ecoles'));
    }

    /**
     * Connexion de l'étudiant ou de l'administrateur via matricule/email et mot de passe.
     * Route: POST /portal/{code_unique}/login (protégée par ThrottleLogin)
     */
    public function login(Request $request, string $code_unique)
    {
        $ecole = Ecole::where('code_unique', $code_unique)->firstOrFail();

        // 1. Normaliser l'identifiant pour supporter indistinctement 'identifiant', 'matricule' ou 'email'
        $rawIdentifiant = $request->input('identifiant') ?? $request->input('matricule') ?? $request->input('email');
        if ($rawIdentifiant !== null) {
            $request->merge([
                'matricule'   => trim($rawIdentifiant),
                'identifiant' => trim($rawIdentifiant),
            ]);
        }

        $request->validate([
            'matricule'    => 'required|string',
            'mot_de_passe' => 'required|string'
        ]);

        $matricule = trim($request->input('matricule'));
        $motDePasse = $request->input('mot_de_passe');
        $matriculeLower = strtolower($matricule);
        $matriculeUpper = strtoupper($matricule);

        // 1. Recherche étudiant STRICTEMENT au sein de cette école (ecole_id = $ecole->id)
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('role', 'etudiant')
            ->where(function ($query) use ($matricule, $matriculeLower, $matriculeUpper) {
                $query->where('matricule', $matricule)
                      ->orWhere('matricule', $matriculeUpper)
                      ->orWhere('matricule', $matriculeLower)
                      ->orWhere('email', $matricule)
                      ->orWhere('email', $matriculeLower);
            })
            ->where('statut', 'actif')
            ->first();

        // Succès étudiant de CETTE école
        if ($etudiant && Hash::check($motDePasse, $etudiant->mot_de_passe)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            auth()->login($etudiant, remember: false);
            session(['last_activity_at' => time()]);

            AuditService::log(
                action:        LogActivite::ACTION_CONNEXION,
                utilisateurId: $etudiant->id,
                ecoleId:       $ecole->id
            );

            return redirect()->route('tenant.dashboard', ['code_unique' => $code_unique]);
        }

        // 2. Vérifier si c'est un administrateur de CETTE école UNIQUEMENT (ecole_id = $ecole->id)
        $admin = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecole->id)
            ->where('role', 'admin_ecole')
            ->where(function ($q) use ($matricule, $matriculeLower) {
                $q->where('email', $matricule)
                  ->orWhere('email', $matriculeLower);
            })
            ->where('statut', 'actif')
            ->first();

        if ($admin && Hash::check($motDePasse, $admin->mot_de_passe)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            auth()->login($admin, remember: false);
            session(['last_activity_at' => time()]);

            AuditService::log(
                action:        LogActivite::ACTION_CONNEXION,
                utilisateurId: $admin->id,
                ecoleId:       $ecole->id,
                details:       ['source' => 'portail_ecole_redirection_admin']
            );

            return redirect()->route('admin_ecole.dashboard')
                ->with('success', 'Bienvenue sur votre espace d\'administration ' . $ecole->nom . '.');
        }

        // 3. Détection de tentative cross-tenant (compte appartenant à une AUTRE école)
        $autreCompte = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', '!=', $ecole->id)
            ->where(function ($query) use ($matricule, $matriculeLower, $matriculeUpper) {
                $query->where('matricule', $matricule)
                      ->orWhere('matricule', $matriculeUpper)
                      ->orWhere('matricule', $matriculeLower)
                      ->orWhere('email', $matricule)
                      ->orWhere('email', $matriculeLower);
            })
            ->first();

        if ($autreCompte) {
            // Rejet catégorique : cet identifiant n'est pas dans la base de données de cette école
            AuditService::log(
                action:    LogActivite::ACTION_ECHEC_CONNEXION,
                ecoleId:   $ecole->id,
                details:   [
                    'motif' => 'tentative_cross_tenant_bloquee',
                    'identifiant' => $matricule,
                    'ecole_reelle_id' => $autreCompte->ecole_id
                ]
            );

            return back()->withErrors([
                'auth' => "Identifiant non reconnu pour cet établissement ({$ecole->code_unique}). Ce compte n'existe pas dans la base de données de {$ecole->nom}."
            ])->withInput($request->except('mot_de_passe'));
        }

        // Échec d'authentification standard
        AuditService::log(
            action:    LogActivite::ACTION_ECHEC_CONNEXION,
            ecoleId:   $ecole->id,
            details:   ['matricule' => $matricule]
        );

        return back()->withErrors(['auth' => 'Identifiant (matricule ou email) ou mot de passe incorrect.'])->withInput($request->except('mot_de_passe'));
    }

    /**
     * Déconnexion sécurisée de l'étudiant.
     * Route: POST /portal/{code_unique}/logout
     */
    public function logout(Request $request, string $code_unique)
    {
        AuditService::logAuth(LogActivite::ACTION_DECONNEXION);
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.portal', ['code_unique' => $code_unique])
            ->with('success', 'Vous avez été déconnecté(e) avec succès.');
    }

    /**
     * Espace personnel de l'étudiant : catalogue et historique.
     * Route: GET /portal/{code_unique}/dashboard
     */
    public function dashboard(string $code_unique)
    {
        $ecole    = Ecole::where('code_unique', $code_unique)->firstOrFail();
        $etudiant = auth()->user();

        // Cloisonnement strict multi-tenants : l'étudiant doit appartenir à cette école
        if (!$etudiant || $etudiant->ecole_id !== $ecole->id) {
            $userEcoleCode = $etudiant?->ecole?->code_unique;
            if ($userEcoleCode) {
                return redirect()->route('tenant.dashboard', ['code_unique' => $userEcoleCode])
                    ->withErrors(['auth' => "Accès refusé : vous êtes inscrit(e) à l'établissement '{$etudiant->ecole->nom}'. Vous ne pouvez pas accéder aux services de '{$ecole->nom}'."]);
            }
            abort(403, "Accès refusé : vous n'appartenez pas à cet établissement.");
        }

        // Isolation stricte : on ne charge que les demandes de cet étudiant dans cette école
        $demandes = DemandeDocument::where('etudiant_id', $etudiant->id)
            ->where('ecole_id', $ecole->id)
            ->with(['modele', 'documentGenere'])
            ->latest()
            ->take(5)
            ->get();

        $modeles = ModeleDocument::where('ecole_id', $ecole->id)
            ->where('actif', true)
            ->get();

        return view('portal.dashboard', compact('ecole', 'etudiant', 'demandes', 'modeles'));
    }

    /**
     * Historique complet des demandes de l'étudiant (paginé).
     * Route: GET /portal/{code_unique}/historique
     */
    public function historique(string $code_unique)
    {
        $ecole    = Ecole::where('code_unique', $code_unique)->firstOrFail();
        $etudiant = auth()->user();

        // Cloisonnement strict multi-tenants
        if (!$etudiant || $etudiant->ecole_id !== $ecole->id) {
            $userEcoleCode = $etudiant?->ecole?->code_unique;
            if ($userEcoleCode) {
                return redirect()->route('tenant.dashboard', ['code_unique' => $userEcoleCode])
                    ->withErrors(['auth' => "Accès refusé : vous êtes inscrit(e) à l'établissement '{$etudiant->ecole->nom}'. Vous ne pouvez pas accéder aux services de '{$ecole->nom}'."]);
            }
            abort(403, "Accès refusé : vous n'appartenez pas à cet établissement.");
        }

        $demandes = DemandeDocument::where('etudiant_id', $etudiant->id)
            ->where('ecole_id', $ecole->id)
            ->with(['modele', 'documentGenere'])
            ->latest()
            ->paginate(15);

        return view('portal.historique', compact('ecole', 'etudiant', 'demandes'));
    }

    /**
     * Création d'une nouvelle demande de document.
     * Route: POST /portal/{code_unique}/demandes
     */
    public function storeDemande(Request $request, string $code_unique)
    {
        $ecole    = Ecole::where('code_unique', $code_unique)->firstOrFail();
        $etudiant = auth()->user();

        // Cloisonnement strict multi-tenants
        if (!$etudiant || $etudiant->ecole_id !== $ecole->id) {
            abort(403, "Tentative de commande de document non autorisée pour un autre établissement.");
        }

        $request->validate([
            'modele_id' => 'required|exists:modeles_documents,id'
        ]);

        // Vérification d'isolation : le modèle appartient bien à cette école
        $modele = ModeleDocument::where('id', $request->input('modele_id'))
            ->where('ecole_id', $ecole->id)
            ->where('actif', true)
            ->firstOrFail();

        // 🛡️ ANTI-SPAM & ANTI-DOUBLONS : Empêcher les commandes en double tant que la précédente est en cours
        $demandeEnCours = DemandeDocument::where('ecole_id', $ecole->id)
            ->where('etudiant_id', $etudiant->id)
            ->where('modele_id', $modele->id)
            ->whereIn('statut', [DemandeDocument::STATUT_EN_ATTENTE, DemandeDocument::STATUT_EN_TRAITEMENT])
            ->first();

        if ($demandeEnCours) {
            return redirect()->route('tenant.dashboard', ['code_unique' => $code_unique])
                ->withErrors(['demande' => "Une demande pour « {$modele->titre} » est déjà en cours de traitement par l'administration."]);
        }

        $demande = DemandeDocument::create([
            'ecole_id'    => $ecole->id,
            'etudiant_id' => $etudiant->id,
            'modele_id'   => $modele->id,
            'statut'      => $modele->validation_requise
                ? DemandeDocument::STATUT_EN_ATTENTE     // Doit passer par l'admin
                : DemandeDocument::STATUT_EN_TRAITEMENT,  // Génération automatique
        ]);

        AuditService::logAuth(
            action:    LogActivite::ACTION_DEMANDE_DOCUMENT,
            cibleType: 'DemandeDocument',
            cibleId:   $demande->id,
            details:   ['modele' => $modele->titre, 'validation_requise' => $modele->validation_requise]
        );

        // Génération immédiate uniquement si pas de validation requise
        if (!$modele->validation_requise) {
            GenerateDocumentPdfJob::dispatch($demande->id);
        }

        $message = $modele->validation_requise
            ? 'Votre demande a été transmise à l\'administration. Vous serez notifié(e) par email.'
            : 'Votre document est en cours de génération. Rafraîchissez la page dans quelques instants.';

        return redirect()->route('tenant.dashboard', ['code_unique' => $code_unique])
            ->with('success', $message);
    }

    /**
     * Téléchargement sécurisé du PDF via son UUID.
     * Route: GET /portal/{code_unique}/documents/{uuid}/download
     */
    public function downloadDocument(string $code_unique, string $uuid)
    {
        $ecole    = Ecole::where('code_unique', $code_unique)->firstOrFail();
        $etudiant = auth()->user();

        if (!$etudiant || $etudiant->ecole_id !== $ecole->id) {
            abort(403, "Téléchargement non autorisé pour un autre établissement.");
        }

        // Isolation stricte : le document appartient à cet étudiant dans cette école
        $doc = DocumentGenere::where('ecole_id', $ecole->id)
            ->where('uuid', $uuid)
            ->whereHas('demande', fn($q) => $q->where('etudiant_id', $etudiant->id))
            ->firstOrFail();

        // Vérification expiration
        if (!$doc->estValide()) {
            return back()->withErrors(['download' => 'Ce document a expiré et ne peut plus être téléchargé.']);
        }

        if (!Storage::disk('local')->exists($doc->chemin_fichier)) {
            abort(404, 'Fichier physique non trouvé sur le serveur.');
        }

        $doc->increment('telechargements_count');

        AuditService::logAuth(
            action:    LogActivite::ACTION_TELECHARGEMENT,
            cibleType: 'DocumentGenere',
            cibleId:   $doc->id,
            details:   ['nom_fichier' => $doc->nom_fichier]
        );

        return response()->download(
            Storage::disk('local')->path($doc->chemin_fichier),
            $doc->nom_fichier,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Mise à jour sécurisée du mot de passe de l'étudiant.
     * Route: POST /portal/{code_unique}/profil/mot-de-passe
     */
    public function updatePassword(Request $request, string $code_unique)
    {
        $ecole = Ecole::where('code_unique', $code_unique)->firstOrFail();
        $etudiant = auth()->user();

        if (!$etudiant || $etudiant->ecole_id !== $ecole->id) {
            abort(403, "Action non autorisée pour cet établissement.");
        }

        $request->validate([
            'mot_de_passe_actuel'               => 'required|string',
            'nouveau_mot_de_passe'              => 'required|string|min:8|confirmed|different:mot_de_passe_actuel',
            'nouveau_mot_de_passe_confirmation' => 'required|string',
        ], [
            'nouveau_mot_de_passe.different' => 'Le nouveau mot de passe doit être différent du mot de passe actuel.',
            'nouveau_mot_de_passe.min'       => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'nouveau_mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if (!Hash::check($request->input('mot_de_passe_actuel'), $etudiant->mot_de_passe)) {
            return back()->withErrors(['mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.']);
        }

        $etudiant->update([
            'mot_de_passe'              => Hash::make($request->input('nouveau_mot_de_passe')),
            'doit_changer_mot_de_passe' => false,
        ]);

        AuditService::logAuth(
            action:    LogActivite::ACTION_RESET_MDP,
            cibleType: 'Utilisateur',
            cibleId:   $etudiant->id,
            details:   ['motif' => 'modification_personnelle_profil']
        );

        return redirect()->route('tenant.dashboard', ['code_unique' => $code_unique])
            ->with('success', 'Votre mot de passe a été modifié avec succès. Votre compte est désormais sécurisé.');
    }
}

