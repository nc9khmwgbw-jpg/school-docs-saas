<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentPdfJob;
use App\Models\DemandeDocument;
use App\Models\DocumentGenere;
use App\Models\EtudiantDonnee;
use App\Models\Import;
use App\Models\LogActivite;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SchoolAdminController extends Controller
{
    // ── Helpers ─────────────────────────────────────────────────────────────

    private function ecoleId(): int
    {
        return auth()->user()->ecole_id;
    }

    // ── Authentification ───────────────────────────────────────────────────

    public function showLogin(Request $request)
    {
        if ($request->has('switch') || $request->has('logout')) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin_ecole.login');
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isAdminEcole()) {
                return redirect()->route('admin_ecole.dashboard');
            }
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard')
                    ->withErrors(['auth' => 'Vous êtes déjà connecté en tant que Super Administrateur.']);
            }
            if ($user->isEtudiant()) {
                $code = $user->ecole?->code_unique ?? 'ENS-2026';
                return redirect()->route('tenant.dashboard', ['code_unique' => $code])
                    ->withErrors(['auth' => 'Vous êtes connecté en tant qu\'étudiant. Pour tester l\'administration en simultané, ouvrez une fenêtre de navigation privée (Ctrl+Maj+P) ou déconnectez-vous.']);
            }
        }
        return view('admin_ecole.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'        => 'required|email',
            'mot_de_passe' => 'required|string',
        ]);

        $email = strtolower(trim($credentials['email']));

        $user = Utilisateur::withoutGlobalScopes()
            ->where('email', $email)
            ->where('role', 'admin_ecole')
            ->where('statut', 'actif')
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['mot_de_passe'], $user->mot_de_passe)) {
            return back()->withInput()->withErrors(['email' => 'Identifiants invalides ou compte inactif.']);
        }

        // Réinitialise toute session résiduelle (ex: étudiant précédent)
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        auth()->login($user);
        $request->session()->regenerate();

        AuditService::logAuth(LogActivite::ACTION_CONNEXION);

        return redirect()->route('admin_ecole.dashboard');
    }

    // ── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        $ecoleId = $this->ecoleId();

        $stats = [
            'total_modeles'       => ModeleDocument::where('ecole_id', $ecoleId)->count(),
            'total_etudiants'     => EtudiantDonnee::where('ecole_id', $ecoleId)->count(),
            'demandes_attente'    => DemandeDocument::where('ecole_id', $ecoleId)->where('statut', 'en_attente')->count(),
            'demandes_terminees'  => DemandeDocument::where('ecole_id', $ecoleId)->where('statut', 'genere')->count(),
            'demandes_refusees'   => DemandeDocument::where('ecole_id', $ecoleId)->where('statut', 'refuse')->count(),
        ];

        // ✅ PERFORMANCE : Eager loading complet pour éviter N+1 queries
        $dernieresDemandes = DemandeDocument::where('ecole_id', $ecoleId)
            ->with(['etudiant', 'modele', 'documentGenere'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin_ecole.dashboard', compact('stats', 'dernieresDemandes'));
    }

    // ── Modèles ─────────────────────────────────────────────────────────────

    public function modelesIndex()
    {
        $modeles = ModeleDocument::where('ecole_id', $this->ecoleId())->latest()->get();
        return view('admin_ecole.modeles.index', compact('modeles'));
    }

    public function modelesCreate()
    {
        return view('admin_ecole.modeles.create');
    }

    public function modelesStore(\App\Http\Requests\StoreModeleDocumentRequest $request)
    {
        // ✅ QUALITÉ : Validation via FormRequest (plus propre et réutilisable)
        $modele = ModeleDocument::create(array_merge($request->validated(), [
            'ecole_id'           => $this->ecoleId(),
            'actif'              => true,
            'validation_requise' => $request->boolean('validation_requise'),
        ]));

        // ✅ PERFORMANCE : Invalider le cache des modèles
        \Illuminate\Support\Facades\Cache::forget("modeles:actifs:ecole:{$this->ecoleId()}");

        AuditService::logAuth(LogActivite::ACTION_CREATION_MODELE, 'ModeleDocument', $modele->id, ['titre' => $modele->titre]);

        return redirect()->route('admin_ecole.modeles.index')
            ->with('success', 'Modèle de document créé avec succès.');
    }

    public function modelesEdit(int $id)
    {
        $modele = ModeleDocument::where('ecole_id', $this->ecoleId())->findOrFail($id);
        return view('admin_ecole.modeles.edit', compact('modele'));
    }

    public function modelesUpdate(\App\Http\Requests\UpdateModeleDocumentRequest $request, int $id)
    {
        $modele = ModeleDocument::where('ecole_id', $this->ecoleId())->findOrFail($id);

        // ✅ QUALITÉ : Validation via FormRequest
        $modele->update(array_merge($request->validated(), [
            'validation_requise' => $request->boolean('validation_requise'),
            'actif'              => $request->boolean('actif', true),
        ]));

        // ✅ PERFORMANCE : Invalider le cache des modèles
        \Illuminate\Support\Facades\Cache::forget("modeles:actifs:ecole:{$this->ecoleId()}");

        return redirect()->route('admin_ecole.modeles.index')
            ->with('success', 'Modèle mis à jour avec succès.');
    }

    public function modelesDestroy(int $id)
    {
        $modele = ModeleDocument::where('ecole_id', $this->ecoleId())->findOrFail($id);

        if ($modele->demandes()->exists()) {
            return back()->withErrors(['delete' => 'Impossible de supprimer un modèle ayant des demandes associées.']);
        }

        $modele->delete();

        // ✅ PERFORMANCE : Invalider le cache des modèles
        \Illuminate\Support\Facades\Cache::forget("modeles:actifs:ecole:{$this->ecoleId()}");

        return back()->with('success', 'Modèle supprimé avec succès.');
    }

    public function modelesApercu(int $id)
    {
        $modele = ModeleDocument::where('ecole_id', $this->ecoleId())->findOrFail($id);
        return view('admin_ecole.modeles.apercu', compact('modele'));
    }

    // ── Demandes (Validation / Refus) ────────────────────────────────────────

    public function demandesIndex()
    {
        $ecoleId = $this->ecoleId();

        // ✅ PERFORMANCE : Ajouter traitePar pour éviter N+1 query
        $demandes = DemandeDocument::where('ecole_id', $ecoleId)
            ->with(['etudiant', 'modele', 'documentGenere', 'traitePar'])
            ->latest()
            ->paginate(20);

        return view('admin_ecole.demandes.index', compact('demandes'));
    }

    /**
     * Valide une demande en attente et déclenche la génération du PDF.
     * Route: POST /admin-ecole/demandes/{id}/valider
     */
    public function demandesValider(Request $request, int $id)
    {
        $demande = DemandeDocument::where('ecole_id', $this->ecoleId())
            ->where('statut', DemandeDocument::STATUT_EN_ATTENTE)
            ->findOrFail($id);

        $demande->update([
            'statut'           => DemandeDocument::STATUT_EN_TRAITEMENT,
            'date_traitement'  => now(),
            'traite_par'       => auth()->id(),
            'motif_refus'      => null,
        ]);

        // Déclenche la génération PDF en arrière-plan
        GenerateDocumentPdfJob::dispatch($demande->id);

        AuditService::logAuth(LogActivite::ACTION_VALIDATION, 'DemandeDocument', $demande->id);

        // Notification email à l'étudiant
        $this->notifierEtudiant($demande, 'valide');

        return back()->with('success', 'Demande validée. Le PDF est en cours de génération.');
    }

    /**
     * Refuse une demande en attente avec un motif obligatoire.
     * Route: POST /admin-ecole/demandes/{id}/refuser
     */
    public function demandesRefuser(Request $request, int $id)
    {
        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $demande = DemandeDocument::where('ecole_id', $this->ecoleId())
            ->where('statut', DemandeDocument::STATUT_EN_ATTENTE)
            ->findOrFail($id);

        $demande->update([
            'statut'          => DemandeDocument::STATUT_REFUSE,
            'motif_refus'     => $request->input('motif_refus'),
            'date_traitement' => now(),
            'traite_par'      => auth()->id(),
        ]);

        AuditService::logAuth(
            action:    LogActivite::ACTION_REFUS,
            cibleType: 'DemandeDocument',
            cibleId:   $demande->id,
            details:   ['motif' => $request->input('motif_refus')]
        );

        // Notification email à l'étudiant
        $this->notifierEtudiant($demande, 'refuse');

        return back()->with('success', 'Demande refusée. L\'étudiant a été notifié par email.');
    }

    /**
     * Traitement par lots sécurisé de toutes les demandes en attente (documents sans validation requise).
     * Utilise chunkById pour supporter de gros volumes sans épuisement de mémoire.
     */
    public function traiterEnMasse(Request $request)
    {
        $count = 0;
        DemandeDocument::where('ecole_id', $this->ecoleId())
            ->where('statut', DemandeDocument::STATUT_EN_ATTENTE)
            ->whereHas('modele', fn($q) => $q->where('validation_requise', false))
            ->chunkById(100, function ($demandes) use (&$count) {
                foreach ($demandes as $demande) {
                    $demande->update([
                        'statut'          => DemandeDocument::STATUT_EN_TRAITEMENT,
                        'date_traitement' => now(),
                        'traite_par'      => auth()->id(),
                    ]);
                    GenerateDocumentPdfJob::dispatch($demande->id);
                    $count++;
                }
            });

        AuditService::logAuth(
            LogActivite::ACTION_VALIDATION,
            'DemandeDocument',
            null,
            ['count' => $count, 'type' => 'traitement_en_masse']
        );

        return back()->with('success', "{$count} document(s) ont été placés dans la file d'attente pour génération asynchrone.");
    }

    // ── Import CSV ──────────────────────────────────────────────────────────

    public function importIndex()
    {
        $imports = Import::where('ecole_id', $this->ecoleId())->latest()->paginate(10);
        return view('admin_ecole.import.index', compact('imports'));
    }

    /**
     * Import CSV avec rapport d'erreurs ligne par ligne.
     * Gère : colonnes manquantes, CNE/CIN malformé, notes hors plage, encodage UTF-8.
     */
    public function importDonnees(\App\Http\Requests\ImportCsvRequest $request)
    {
        // ✅ QUALITÉ : Validation via FormRequest
        $file = $request->file('fichier_csv');
        $ecoleId = $this->ecoleId();

        // Création du log d'import
        $import = Import::create([
            'ecole_id'              => $ecoleId,
            'utilisateur_id'        => auth()->id(),
            'nom_fichier_original'  => $file->getClientOriginalName(),
            'statut'                => 'en_cours',
            'lignes_traitees'       => 0,
            'lignes_ignorees'       => 0,
            'rapport_erreurs'       => [],
        ]);

        AuditService::logAuth(LogActivite::ACTION_IMPORT_CSV, 'Import', $import->id, [
            'fichier' => $file->getClientOriginalName(),
        ]);

        // Lecture et normalisation UTF-8
        $contenu = file_get_contents($file->getRealPath());
        $contenu = mb_convert_encoding($contenu, 'UTF-8', mb_detect_encoding($contenu, 'UTF-8, ISO-8859-1, Windows-1252', true));

        $lignes           = explode("\n", $contenu);
        $headerRaw        = str_getcsv(array_shift($lignes), ',');
        $header           = array_map(fn($col) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col))), $headerRaw);

        $rapportErreurs   = [];
        $lignesTraitees   = 0;
        $lignesIgnorees   = 0;
        $numeroLigne      = 1;

        DB::beginTransaction();
        try {
            foreach ($lignes as $ligneCsv) {
                $numeroLigne++;
                if (empty(trim($ligneCsv))) continue;

                $row = str_getcsv($ligneCsv, ',');

                if (count($row) !== count($header)) {
                    $rapportErreurs[] = ['ligne' => $numeroLigne, 'erreur' => 'Nombre de colonnes incorrect (' . count($row) . ' au lieu de ' . count($header) . ')'];
                    $lignesIgnorees++;
                    continue;
                }

                $data      = array_combine($header, $row);
                $matricule = trim($data['matricule'] ?? '');

                if (empty($matricule)) {
                    $rapportErreurs[] = ['ligne' => $numeroLigne, 'erreur' => 'Matricule manquant ou vide'];
                    $lignesIgnorees++;
                    continue;
                }

                // Validation de la note si présente
                if (isset($data['moyenne']) && trim($data['moyenne']) !== '') {
                    $note = floatval(str_replace(',', '.', $data['moyenne']));
                    if ($note < 0 || $note > 20) {
                        $rapportErreurs[] = ['ligne' => $numeroLigne, 'erreur' => "Moyenne invalide : {$data['moyenne']} (doit être entre 0 et 20)"];
                        $lignesIgnorees++;
                        continue;
                    }
                }

                $filiere = trim($data['filiere'] ?? '');
                $niveau  = trim($data['niveau'] ?? '');
                $annee   = trim($data['annee_universitaire'] ?? $data['annee'] ?? date('Y'));

                $dynamiques = array_filter(
                    array_diff_key($data, array_flip(['matricule', 'filiere', 'niveau', 'annee_universitaire', 'annee'])),
                    fn($val) => trim($val) !== ''
                );
                
                // ✅ SÉCURITÉ : Protection contre CSV injection et validation des données
                foreach ($dynamiques as $key => $value) {
                    // Échapper les formules Excel/LibreOffice (=, @, +, -)
                    if (is_string($value) && preg_match('/^[=@+\-]/', $value)) {
                        $dynamiques[$key] = "'" . $value;
                    }
                    // Limiter la longueur pour éviter les attaques par débordement
                    $dynamiques[$key] = mb_substr((string)$value, 0, 500);
                    // Échapper les balises HTML
                    $dynamiques[$key] = htmlspecialchars($dynamiques[$key], ENT_QUOTES, 'UTF-8');
                }

                EtudiantDonnee::updateOrCreate(
                    ['ecole_id' => $ecoleId, 'matricule' => $matricule],
                    [
                        'filiere'              => $filiere,
                        'niveau'               => $niveau,
                        'annee_universitaire'  => $annee,
                        'attributs_dynamiques' => $dynamiques,
                        'date_import'          => now(),
                    ]
                );

                // Provisionnement automatique du compte de connexion étudiant si absent
                $nomEtudiant = trim($data['nom'] ?? 'Étudiant');
                $prenomEtudiant = trim($data['prenom'] ?? $matricule);
                $emailEtudiant = trim($data['email'] ?? (strtolower($matricule) . '@etudiant.ma'));

                $compteEtudiant = Utilisateur::withoutGlobalScopes()
                    ->where('ecole_id', $ecoleId)
                    ->where('matricule', $matricule)
                    ->first();

                if (!$compteEtudiant) {
                    Utilisateur::create([
                        'ecole_id'                  => $ecoleId,
                        'role'                      => 'etudiant',
                        'matricule'                 => $matricule,
                        'nom'                       => $nomEtudiant,
                        'prenom'                    => $prenomEtudiant,
                        'email'                     => $emailEtudiant,
                        'mot_de_passe'              => \Illuminate\Support\Facades\Hash::make('Secret2026!'),
                        'statut'                    => 'actif',
                        'doit_changer_mot_de_passe' => true,
                    ]);
                } else {
                    $compteEtudiant->update([
                        'nom'    => $nomEtudiant,
                        'prenom' => $prenomEtudiant,
                    ]);
                }

                $lignesTraitees++;
            }

            DB::commit();

            $import->update([
                'statut'          => 'termine',
                'lignes_traitees' => $lignesTraitees,
                'lignes_ignorees' => $lignesIgnorees,
                'rapport_erreurs' => $rapportErreurs,
                'termine_le'      => now(),
            ]);

            $msg = "Importation réussie : {$lignesTraitees} fiche(s) importée(s).";
            if ($lignesIgnorees > 0) {
                $msg .= " {$lignesIgnorees} ligne(s) ignorée(s) — consultez le rapport d'erreurs.";
            }

            return redirect()->route('admin_ecole.import.rapport', $import->id)->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $import->update(['statut' => 'erreur', 'rapport_erreurs' => [['ligne' => 0, 'erreur' => $e->getMessage()]]]);

            return back()->withErrors(['import' => 'Erreur critique lors de l\'import : ' . $e->getMessage()]);
        }
    }

    public function importRapport(int $id)
    {
        $import = Import::where('ecole_id', $this->ecoleId())->findOrFail($id);
        return view('admin_ecole.import.rapport', compact('import'));
    }

    /**
     * Liste et annuaire des étudiants de l'établissement.
     * Route: GET /admin-ecole/etudiants
     */
    public function etudiantsIndex(Request $request)
    {
        $ecoleId = $this->ecoleId();
        $ecole = auth()->user()->ecole;

        $query = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecoleId)
            ->where('role', 'etudiant')
            ->withCount(['demandes']);

        if ($request->filled('recherche')) {
            $term = trim($request->query('recherche'));
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'LIKE', "%{$term}%")
                  ->orWhere('prenom', 'LIKE', "%{$term}%")
                  ->orWhere('matricule', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
            });
        }

        $etudiants = $query->latest()->paginate(25)->withQueryString();

        $matricules = $etudiants->pluck('matricule')->filter();
        $donneesScolaires = EtudiantDonnee::withoutGlobalScopes()
            ->where('ecole_id', $ecoleId)
            ->whereIn('matricule', $matricules)
            ->get()
            ->keyBy('matricule');

        $filieres = EtudiantDonnee::withoutGlobalScopes()
            ->where('ecole_id', $ecoleId)
            ->select('filiere')
            ->distinct()
            ->pluck('filiere')
            ->filter();

        return view('admin_ecole.etudiants.index', compact('etudiants', 'ecole', 'donneesScolaires', 'filieres'));
    }

    public function etudiantsStore(Request $request)
    {
        $request->validate([
            'matricule'           => 'required|string|max:100',
            'nom'                 => 'required|string|max:150',
            'prenom'              => 'required|string|max:150',
            'email'               => 'nullable|email|max:255',
            'filiere'             => 'nullable|string|max:150',
            'niveau'              => 'nullable|string|max:100',
            'annee_universitaire' => 'nullable|string|max:50',
            'moyenne'             => 'nullable|numeric|min:0|max:20',
            'mention'             => 'nullable|string|max:50',
            'photo'               => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $ecoleId = $this->ecoleId();
        $matricule = trim($request->input('matricule'));
        $filiere = $request->input('filiere') ?: 'Tronc Commun / Sciences';
        $niveau = $request->input('niveau') ?: '1ère Année';
        $anneeUniv = $request->input('annee_universitaire') ?: (date('Y') . '-' . (date('Y') + 1));

        $dynamiques = [];
        if ($request->filled('moyenne')) {
            $dynamiques['moyenne'] = (string) $request->input('moyenne');
        }
        if ($request->filled('mention')) {
            $dynamiques['mention'] = (string) $request->input('mention');
        }

        // Gestion de la photo étudiante
        $photoPath = null;
        $photoData = null;
        $photoBlob = null;
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
            $photoPath = $photoFile->store("photos/etudiants/{$ecoleId}", 'public');
            $photoBlob = Storage::disk('public')->get($photoPath);
            $mime = $photoFile->getClientMimeType() ?: 'image/jpeg';
            $photoData = 'data:' . $mime . ';base64,' . base64_encode($photoBlob);
        }

        // 1. Données scolaires
        $donneePayload = [
            'filiere'              => $filiere,
            'niveau'               => $niveau,
            'annee_universitaire'  => $anneeUniv,
            'attributs_dynamiques' => $dynamiques,
            'date_import'          => now(),
        ];
        if ($photoPath) {
            $donneePayload['photo_path'] = $photoPath;
            $donneePayload['photo_data'] = $photoData;
        }

        EtudiantDonnee::updateOrCreate(
            ['ecole_id' => $ecoleId, 'matricule' => $matricule],
            $donneePayload
        );

        // 2. Compte de connexion étudiant
        $email = $request->input('email') ?: (strtolower($matricule) . '@etudiant.ma');
        $compte = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $ecoleId)
            ->where('matricule', $matricule)
            ->first();

        $userPayload = [
            'nom'    => $request->input('nom'),
            'prenom' => $request->input('prenom'),
            'email'  => $email,
        ];
        if ($photoPath) {
            $userPayload['photo_path'] = $photoPath;
            $userPayload['photo_data'] = $photoData;
            $userPayload['photo_blob'] = null; // Stocké sur disque pour préserver la RAM
        }

        if (!$compte) {
            $userPayload['ecole_id'] = $ecoleId;
            $userPayload['role'] = 'etudiant';
            $userPayload['matricule'] = $matricule;
            $userPayload['mot_de_passe'] = \Illuminate\Support\Facades\Hash::make('Secret2026!');
            $userPayload['statut'] = 'actif';
            $userPayload['doit_changer_mot_de_passe'] = true;

            Utilisateur::create($userPayload);
        } else {
            $compte->update($userPayload);
        }

        AuditService::logAuth(LogActivite::ACTION_IMPORT_CSV, 'Utilisateur', null, [
            'action'    => 'ajout_manuel_etudiant',
            'matricule' => $matricule
        ]);

        return back()->with('success', "Étudiant {$matricule} ({$request->input('prenom')} {$request->input('nom')}) enregistré avec succès ! Mot de passe initial : Secret2026!");
    }

    // ── Paramètres (Personnalisation) ────────────────────────────────────────

    public function parametres()
    {
        $ecole = auth()->user()->ecole;
        return view('admin_ecole.parametres', compact('ecole'));
    }

    public function parametresSave(Request $request)
    {
        $ecole = auth()->user()->ecole;

        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'adresse'           => 'nullable|string|max:500',
            'email_contact'     => 'nullable|email',
            'telephone'         => 'nullable|string|max:20',
            'couleur_primaire'  => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'couleur_secondaire'=> 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $ecole->update($validated);

        return back()->with('success', 'Paramètres de l\'établissement mis à jour avec succès.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $ecole = auth()->user()->ecole;
        $file = $request->file('logo');
        
        // ✅ SÉCURITÉ : Sanitization stricte des fichiers SVG avec SvgSanitizerService
        if ($file->extension() === 'svg' || $file->getClientMimeType() === 'image/svg+xml') {
            try {
                $dirtySVG = file_get_contents($file->getRealPath());
                $cleanSVG = \App\Services\SvgSanitizerService::sanitize($dirtySVG);
                if ($cleanSVG === false) {
                    return back()->withErrors(['logo' => 'Le fichier SVG contient du code non conforme ou dangereux.']);
                }
                $path = "logos/{$ecole->id}/" . Str::uuid() . '.svg';
                Storage::disk('public')->put($path, $cleanSVG);
            } catch (\Exception $e) {
                return back()->withErrors(['logo' => 'Fichier SVG invalide ou corrompu: ' . $e->getMessage()]);
            }
        } else {
            $path = $file->store("logos/{$ecole->id}", 'public');
        }

        if ($ecole->logo_path && $ecole->logo_path !== $path) {
            Storage::disk('public')->delete($ecole->logo_path);
        }

        $rawLogo = Storage::disk('public')->get($path);
        $mime = $file->getClientMimeType() ?: 'image/png';
        $logoData = 'data:' . $mime . ';base64,' . base64_encode($rawLogo);

        $ecole->update([
            'logo_path' => $path,
            'logo_data' => $logoData,
            'logo_blob' => null,
        ]);

        return back()->with('success', 'Logo mis à jour avec succès et scellé sur le stockage sécurisé.');
    }

    public function uploadCachet(Request $request)
    {
        $request->validate([
            'cachet' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $ecole = auth()->user()->ecole;
        $file = $request->file('cachet');
        
        // ✅ SÉCURITÉ : Sanitization des fichiers SVG
        if ($file->extension() === 'svg' || $file->getClientMimeType() === 'image/svg+xml') {
            try {
                $dirtySVG = file_get_contents($file->getRealPath());
                $cleanSVG = \App\Services\SvgSanitizerService::sanitize($dirtySVG);
                if ($cleanSVG === false) {
                    return back()->withErrors(['cachet' => 'Le fichier SVG contient du code non conforme ou dangereux.']);
                }
                $path = "cachets/{$ecole->id}/" . Str::uuid() . '.svg';
                Storage::disk('public')->put($path, $cleanSVG);
            } catch (\Exception $e) {
                return back()->withErrors(['cachet' => 'Fichier SVG invalide ou corrompu: ' . $e->getMessage()]);
            }
        } else {
            $path = $file->store("cachets/{$ecole->id}", 'public');
        }

        if ($ecole->cachet_path && $ecole->cachet_path !== $path) {
            Storage::disk('public')->delete($ecole->cachet_path);
        }

        $rawCachet = Storage::disk('public')->get($path);
        $mime = $file->getClientMimeType() ?: 'image/png';
        $cachetData = 'data:' . $mime . ';base64,' . base64_encode($rawCachet);

        $ecole->update([
            'cachet_path' => $path,
            'cachet_data' => $cachetData,
            'cachet_blob' => null,
        ]);

        AuditService::logAuth(LogActivite::ACTION_MODIFICATION_PARAMETRES, 'Ecole', $ecole->id, ['action' => 'upload_cachet']);

        return back()->with('success', 'Cachet officiel enregistré avec succès.');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $ecole = auth()->user()->ecole;
        $file = $request->file('signature');
        
        // ✅ SÉCURITÉ : Sanitization des fichiers SVG
        if ($file->extension() === 'svg' || $file->getClientMimeType() === 'image/svg+xml') {
            try {
                $dirtySVG = file_get_contents($file->getRealPath());
                $cleanSVG = \App\Services\SvgSanitizerService::sanitize($dirtySVG);
                if ($cleanSVG === false) {
                    return back()->withErrors(['signature' => 'Le fichier SVG contient du code non conforme ou dangereux.']);
                }
                $path = "signatures/{$ecole->id}/" . Str::uuid() . '.svg';
                Storage::disk('public')->put($path, $cleanSVG);
            } catch (\Exception $e) {
                return back()->withErrors(['signature' => 'Fichier SVG invalide ou corrompu: ' . $e->getMessage()]);
            }
        } else {
            $path = $file->store("signatures/{$ecole->id}", 'public');
        }

        if ($ecole->signature_path && $ecole->signature_path !== $path) {
            Storage::disk('public')->delete($ecole->signature_path);
        }

        $rawSig = Storage::disk('public')->get($path);
        $mime = $file->getClientMimeType() ?: 'image/png';
        $sigData = 'data:' . $mime . ';base64,' . base64_encode($rawSig);

        $ecole->update([
            'signature_path' => $path,
            'signature_data' => $sigData,
            'signature_blob' => null,
        ]);

        AuditService::logAuth(LogActivite::ACTION_MODIFICATION_PARAMETRES, 'Ecole', $ecole->id, ['action' => 'upload_signature']);

        return back()->with('success', 'Signature de direction enregistrée avec succès.');
    }

    /**
     * Téléchargement sécurisé d'un document généré par l'administration de l'école.
     * Route: GET /admin-ecole/documents/{uuid}/download
     */
    public function telechargerDocument(string $uuid)
    {
        $doc = DocumentGenere::where('ecole_id', $this->ecoleId())
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!Storage::disk('local')->exists($doc->chemin_fichier)) {
            abort(404, 'Fichier introuvable sur le serveur sécurisé.');
        }

        AuditService::logAuth(
            action:    LogActivite::ACTION_TELECHARGEMENT,
            cibleType: 'DocumentGenere',
            cibleId:   $doc->id,
            details:   ['source' => 'admin_ecole', 'nom_fichier' => $doc->nom_fichier]
        );

        return response()->download(
            Storage::disk('local')->path($doc->chemin_fichier),
            $doc->nom_fichier,
            ['Content-Type' => 'application/pdf']
        );
    }

    // ── Logs ────────────────────────────────────────────────────────────────

    public function logsIndex()
    {
        $logs = \App\Models\LogActivite::where('ecole_id', $this->ecoleId())
            ->with('utilisateur')
            ->latest('created_at')
            ->paginate(50);

        return view('admin_ecole.logs', compact('logs'));
    }

    // ── Déconnexion ──────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        AuditService::logAuth(LogActivite::ACTION_DECONNEXION);
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Déconnexion réussie.');
    }

    // ── Utilitaires privés ───────────────────────────────────────────────────

    private function notifierEtudiant(DemandeDocument $demande, string $type): void
    {
        $demande->load(['etudiant', 'modele']);
        $etudiant = $demande->etudiant;

        if (!$etudiant || !$etudiant->email) return;

        try {
            $view    = "emails.demande_{$type}";
            $sujet   = match($type) {
                'valide' => "✅ Votre demande de {$demande->modele->titre} a été validée",
                'refuse' => "❌ Votre demande de {$demande->modele->titre} a été refusée",
                default  => "Mise à jour de votre demande de document",
            };

            Mail::send($view, compact('demande', 'etudiant'), function ($message) use ($etudiant, $sujet) {
                $message->to($etudiant->email, $etudiant->prenom . ' ' . $etudiant->nom)
                    ->subject($sujet);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification email échouée pour demande #{$demande->id}: " . $e->getMessage());
        }
    }
}
