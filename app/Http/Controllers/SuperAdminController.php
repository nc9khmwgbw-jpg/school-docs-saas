<?php

namespace App\Http\Controllers;

use App\Models\DemandeDocument;
use App\Models\DocumentGenere;
use App\Models\Ecole;
use App\Models\EtudiantDonnee;
use App\Models\LogActivite;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    // ── Authentification super admin ─────────────────────────────────────────

    public function showLogin(Request $request)
    {
        if ($request->has('switch') || $request->has('logout')) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('superadmin.login');
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
            if ($user->isAdminEcole()) {
                return redirect()->route('admin_ecole.dashboard')
                    ->withErrors(['auth' => 'Accès refusé : la zone SuperAdmin est strictement réservée au superviseur général SaaS.']);
            }
            if ($user->isEtudiant()) {
                $code = $user->ecole?->code_unique ?? 'ENS-2026';
                return redirect()->route('tenant.dashboard', ['code_unique' => $code])
                    ->withErrors(['auth' => 'Accès refusé : l\'espace SuperAdmin est réservé.']);
            }
        }
        return view('superadmin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'        => 'required|email',
            'mot_de_passe' => 'required|string',
        ]);

        $email = strtolower(trim($request->input('email')));

        $user = Utilisateur::withoutGlobalScopes()
            ->where('email', $email)
            ->where('role', 'super_admin')
            ->where('statut', 'actif')
            ->first();

        if (!$user || !Hash::check($request->input('mot_de_passe'), $user->mot_de_passe)) {
            return back()->withErrors(['auth' => 'Identifiants incorrects.']);
        }

        // Réinitialise toute session résiduelle
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        auth()->login($user);
        session(['last_activity_at' => time()]);

        AuditService::log(LogActivite::ACTION_CONNEXION, $user->id, null, null, null, ['role' => 'super_admin']);

        return redirect()->route('superadmin.dashboard');
    }

    public function logout(Request $request)
    {
        AuditService::logAuth(LogActivite::ACTION_DECONNEXION);
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }

    // ── Dashboard global ─────────────────────────────────────────────────────

    public function dashboard()
    {
        $ecoles = Ecole::withCount(['utilisateurs', 'modelesDocuments', 'documentsGeneres', 'demandesDocuments'])
            ->latest()
            ->get();

        $stats = [
            'total_ecoles'         => $ecoles->count(),
            'ecoles_actives'       => $ecoles->where('statut', 'actif')->count(),
            'ecoles_suspendues'    => $ecoles->where('statut', 'suspendu')->count(),
            'total_documents'      => DB::table('documents_generes')->count(),
            'total_demandes'       => DB::table('demandes_documents')->count(),
            'demandes_ce_mois'     => DB::table('demandes_documents')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $demandesParMois = DB::table('demandes_documents')
            ->selectRaw('YEAR(created_at) as annee, MONTH(created_at) as mois, COUNT(*) as total')
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->take(12)
            ->get();

        return view('superadmin.dashboard', compact('ecoles', 'stats', 'demandesParMois'));
    }

    // ── Gestion des écoles ───────────────────────────────────────────────────

    public function ecolesIndex()
    {
        $ecoles = Ecole::withCount(['utilisateurs', 'documentsGeneres', 'demandesDocuments'])
            ->latest()
            ->paginate(20);

        return view('superadmin.ecoles.index', compact('ecoles'));
    }

    public function ecolesCreate()
    {
        return view('superadmin.ecoles.create');
    }

    public function ecolesShow(int $id)
    {
        $ecole = Ecole::withCount(['utilisateurs', 'modelesDocuments', 'documentsGeneres', 'demandesDocuments'])
            ->findOrFail($id);

        $statsEcole = [
            'demandes_ce_mois' => DemandeDocument::where('ecole_id', $id)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'docs_generes'     => DocumentGenere::where('ecole_id', $id)->count(),
        ];

        // Étudiants rattachés à cet établissement
        $etudiants = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $id)
            ->where('role', 'etudiant')
            ->withCount(['demandes'])
            ->latest()
            ->get();

        // Administrateurs de cet établissement
        $adminsEcole = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $id)
            ->where('role', 'admin_ecole')
            ->latest()
            ->get();

        // Données académiques (filière, niveau, moyenne)
        $donneesScolaires = EtudiantDonnee::withoutGlobalScopes()
            ->where('ecole_id', $id)
            ->get()
            ->keyBy('matricule');

        $logsRecents = LogActivite::where('ecole_id', $id)
            ->with('utilisateur')
            ->latest('created_at')
            ->take(20)
            ->get();

        return view('superadmin.ecoles.show', compact('ecole', 'statsEcole', 'etudiants', 'adminsEcole', 'donneesScolaires', 'logsRecents'));
    }

    /**
     * Annuaire complet des étudiants avec filtrage par école et recherche.
     */
    public function etudiantsIndex(Request $request)
    {
        $ecoles = Ecole::orderBy('nom')->get();

        $query = Utilisateur::withoutGlobalScopes()
            ->where('role', 'etudiant')
            ->with('ecole')
            ->withCount(['demandes']);

        if ($request->filled('ecole_id')) {
            $query->where('ecole_id', (int) $request->query('ecole_id'));
        }

        if ($request->filled('recherche')) {
            $term = trim($request->query('recherche'));
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'LIKE', "%{$term}%")
                  ->orWhere('prenom', 'LIKE', "%{$term}%")
                  ->orWhere('matricule', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
            });
        }

        $etudiants = $query->latest()->paginate(20)->withQueryString();

        $matricules = $etudiants->pluck('matricule')->filter();
        $donneesScolaires = EtudiantDonnee::withoutGlobalScopes()
            ->whereIn('matricule', $matricules)
            ->get()
            ->keyBy(fn($d) => $d->ecole_id . '_' . $d->matricule);

        $selectedEcole = $request->filled('ecole_id') ? $ecoles->firstWhere('id', (int) $request->query('ecole_id')) : null;

        return view('superadmin.etudiants.index', compact('etudiants', 'ecoles', 'donneesScolaires', 'selectedEcole'));
    }

    /**
     * Impersonation d'un administrateur d'école (Accès Master sans mot de passe).
     */
    public function impersonateEcole(int $id)
    {
        $admin = Utilisateur::withoutGlobalScopes()
            ->where('ecole_id', $id)
            ->where('role', 'admin_ecole')
            ->first();

        if (!$admin) {
            return back()->withErrors(['error' => 'Aucun compte administrateur trouvé pour cet établissement.']);
        }

        AuditService::logAuth(
            'impersonation_debut',
            'Ecole',
            $id,
            ['super_admin_id' => auth()->id(), 'cible_user_id' => $admin->id, 'role' => 'admin_ecole']
        );

        session(['impersonated_by_superadmin' => auth()->id()]);
        auth()->login($admin);

        return redirect()->route('admin_ecole.dashboard')
            ->with('success', "⚡ Accès Master actif : Vous êtes connecté en tant qu'administrateur de {$admin->ecole->nom}.");
    }

    /**
     * Impersonation d'un étudiant (Accès Master sur son espace officiel).
     */
    public function impersonateEtudiant(int $id)
    {
        $etudiant = Utilisateur::withoutGlobalScopes()
            ->where('id', $id)
            ->where('role', 'etudiant')
            ->firstOrFail();

        AuditService::logAuth(
            'impersonation_debut',
            'Utilisateur',
            $etudiant->id,
            ['super_admin_id' => auth()->id(), 'cible_user_id' => $etudiant->id, 'role' => 'etudiant']
        );

        session(['impersonated_by_superadmin' => auth()->id()]);
        auth()->login($etudiant);

        $codeUnique = $etudiant->ecole?->code_unique ?? 'ENS-2026';
        return redirect()->route('tenant.dashboard', ['code_unique' => $codeUnique])
            ->with('success', "⚡ Accès Master actif : Vous êtes connecté sur l'espace étudiant de {$etudiant->prenom} {$etudiant->nom}.");
    }

    /**
     * Création d'une nouvelle école avec son administrateur principal.
     */
    public function storeEcole(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'code_unique'       => 'required|string|max:50|unique:ecoles,code_unique|alpha_dash',
            'email_contact'     => 'required|email|max:255',
            'couleur_primaire'  => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'adresse'           => 'nullable|string|max:500',
            'telephone'         => 'nullable|string|max:20',
            'admin_nom'         => 'required|string|max:100',
            'admin_prenom'      => 'required|string|max:100',
            'admin_email'       => 'required|email|unique:utilisateurs,email',
            'admin_password'    => 'required|string|min:10|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $ecole = Ecole::create([
                'uuid'             => (string) Str::uuid(),
                'nom'              => $validated['nom'],
                'code_unique'      => strtoupper(trim($validated['code_unique'])),
                'email_contact'    => $validated['email_contact'],
                'couleur_primaire' => $validated['couleur_primaire'] ?? '#2563EB',
                'adresse'          => $validated['adresse'] ?? null,
                'telephone'        => $validated['telephone'] ?? null,
                'statut'           => 'actif',
            ]);

            Utilisateur::create([
                'uuid'         => (string) Str::uuid(),
                'ecole_id'     => $ecole->id,
                'role'         => 'admin_ecole',
                'nom'          => $validated['admin_nom'],
                'prenom'       => $validated['admin_prenom'],
                'email'        => $validated['admin_email'],
                'mot_de_passe' => Hash::make($validated['admin_password']),
                'statut'       => 'actif',
            ]);

            // Provisionnement immédiat des modèles de documents clés en main
            $this->seedModelesParDefaut($ecole);

            DB::commit();

            AuditService::logAuth(LogActivite::ACTION_CREATION_ECOLE, 'Ecole', $ecole->id, [
                'nom'         => $ecole->nom,
                'code_unique' => $ecole->code_unique,
            ]);

            return redirect()->route('superadmin.ecoles.index')
                ->with('success', "L'établissement {$ecole->nom} ({$ecole->code_unique}) a été créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création : ' . $e->getMessage()]);
        }
    }

    /**
     * Activation / Suspension d'un compte école.
     */
    public function toggleStatut(int $id)
    {
        $ecole         = Ecole::findOrFail($id);
        $nouveauStatut = ($ecole->statut === 'actif') ? 'suspendu' : 'actif';
        $ecole->update(['statut' => $nouveauStatut]);

        AuditService::logAuth(
            action:    'toggle_statut_ecole',
            cibleType: 'Ecole',
            cibleId:   $id,
            details:   ['ancien' => $ecole->getOriginal('statut'), 'nouveau' => $nouveauStatut]
        );

        return back()->with('success', "Statut de {$ecole->nom} : {$nouveauStatut}.");
    }

    // ── Audit log global ─────────────────────────────────────────────────────

    public function logsGlobal()
    {
        $logs = LogActivite::with(['utilisateur', 'ecole'])
            ->latest('created_at')
            ->paginate(100);

        return view('superadmin.logs', compact('logs'));
    }

    // ── Statistiques globales ────────────────────────────────────────────────

    public function statistiques()
    {
        $statsParEcole = Ecole::withCount([
            'documentsGeneres',
            'demandesDocuments',
            'utilisateurs as etudiants_count' => fn($q) => $q->where('role', 'etudiant'),
        ])
            ->orderByDesc('documents_generes_count')
            ->get();

        return view('superadmin.statistiques', compact('statsParEcole'));
    }

    /**
     * Crée les modèles administratifs officiels clés en main pour une nouvelle école.
     */
    private function seedModelesParDefaut(Ecole $ecole): void
    {
        $primaryColor = $ecole->couleur_primaire ?: '#0b3b2a';

        // 1. Attestation de Réussite (instantanée)
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Attestation de Réussite',
            'type_document'      => 'attestation_reussite',
            'description'        => 'Modèle certifiant l\'admission et la validation de l\'année universitaire.',
            'contenu_html'       => '<div class="cert-container">
    <div class="header">
        <h2>{nom_ecole}</h2>
        <p>Direction des Affaires Pédagogiques &amp; Scolarité</p>
    </div>
    <hr class="separator"/>
    <h1 class="title">ATTESTATION DE RÉUSSITE</h1>
    <div class="body-text">
        <p>La Direction de l\'établissement <strong>{nom_ecole}</strong> atteste par la présente que :</p>
        <p class="student-name">L\'étudiant(e) : <strong>{prenom} {nom}</strong></p>
        <p>Numéro de matricule : <strong>{matricule}</strong></p>
        <p>Inscrit(e) en : <strong>{filiere} — {niveau}</strong></p>
        <p>A validé avec succès l\'année académique <strong>{annee_universitaire}</strong> avec la mention <strong>{mention}</strong> (Moyenne : <strong>{moyenne}/20</strong>).</p>
        <p>La présente attestation lui est délivrée pour servir et valoir ce que de droit.</p>
    </div>
    <div class="footer">
        <div class="date-issue">Fait le {date_du_jour}</div>
        <div class="signature">Signature &amp; Sceau Officiel</div>
    </div>
    {qr_code}
</div>',
            'styles_css'         => 'body { font-family: "DejaVu Sans", sans-serif; color: #1f2937; margin: 30px; }
.cert-container { border: 3px double ' . $primaryColor . '; padding: 40px; }
.header { text-align: center; margin-bottom: 25px; }
.separator { border: 0; border-top: 1px solid #d1d5db; margin: 20px 0; }
.title { text-align: center; font-size: 24px; color: ' . $primaryColor . '; letter-spacing: 2px; margin: 30px 0; }
.student-name { font-size: 18px; margin: 15px 0; }
.body-text p { font-size: 14px; line-height: 1.8; }
.footer { margin-top: 50px; display: flex; justify-content: space-between; }',
            'actif'              => true,
            'validation_requise' => false,
            'langue'             => 'fr',
        ]);

        // 2. Certificat de Scolarité (avec validation requise)
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Certificat de Scolarité',
            'type_document'      => 'certificat_scolarite',
            'description'        => 'Certifie l\'inscription régulière pour l\'année académique en cours.',
            'contenu_html'       => '<div class="cert-container">
    <div class="header">
        <h2>{nom_ecole}</h2>
        <p>Bureau des Inscriptions &amp; Scolarité — {annee_actuelle}</p>
    </div>
    <hr class="separator"/>
    <h1 class="title">CERTIFICAT DE SCOLARITÉ</h1>
    <div class="body-text">
        <p>Je soussigné, Directeur de l\'établissement <strong>{nom_ecole}</strong>, certifie que :</p>
        <p class="student-name"><strong>{prenom} {nom}</strong> (Matricule : <strong>{matricule}</strong>)</p>
        <p>est régulièrement inscrit(e) au titre de l\'année <strong>{annee_universitaire}</strong></p>
        <p>en cursus : <strong>{filiere} — {niveau}</strong>.</p>
        <p>Le présent certificat est délivré à l\'intéressé(e) pour faire valoir ce que de droit.</p>
    </div>
    <div class="footer">
        <div class="date-issue">Le {date_du_jour}</div>
        <div class="signature">Le Directeur des Études</div>
    </div>
    {qr_code}
</div>',
            'styles_css'         => 'body { font-family: "DejaVu Sans", sans-serif; color: #1f2937; margin: 30px; }
.cert-container { border: 2px solid ' . $primaryColor . '; padding: 35px; }
.header { text-align: center; margin-bottom: 20px; }
.separator { border: 0; border-top: 1px solid #d1d5db; margin: 20px 0; }
.title { text-align: center; font-size: 22px; color: ' . $primaryColor . '; margin: 25px 0; }
.body-text p { font-size: 14px; line-height: 2; }
.footer { margin-top: 50px; display: flex; justify-content: space-between; }',
            'actif'              => true,
            'validation_requise' => true,
            'langue'             => 'fr',
        ]);
    }
}
