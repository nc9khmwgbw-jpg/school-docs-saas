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
            $previousTenant = \App\Scopes\TenantScope::getTenantId();
            \App\Scopes\TenantScope::setTenantId($ecole->id);
            try {
                $this->seedModelesParDefaut($ecole);
            } finally {
                \App\Scopes\TenantScope::setTenantId($previousTenant);
            }

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
        $css = <<<CSS
body { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; color: #000000; margin: 0; padding: 0; }
.cert-container { border: 3px double #000000; padding: 24px 28px; background-color: #ffffff; min-height: 880px; position: relative; }
.header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
.header-left { width: 22%; vertical-align: middle; text-align: left; }
.header-center { width: 56%; vertical-align: middle; text-align: center; }
.header-right { width: 22%; vertical-align: middle; text-align: right; font-size: 10px; color: #000000; }
.republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
.ministere { font-size: 8px; letter-spacing: 0.5px; margin-top: 2px; text-transform: uppercase; }
.school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.8px; }
.dept-name { font-size: 9.5px; font-style: italic; margin-top: 2px; }
.header-divider { border-top: 2px solid #000000; border-bottom: 1px solid #000000; height: 3px; margin: 8px 0 16px 0; }
.title-container { text-align: center; margin-bottom: 18px; }
.doc-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 3px 0; color: #000000; }
.doc-ref { font-size: 9.5px; font-family: "DejaVu Sans Mono", monospace; letter-spacing: 0.5px; }
.body-content { font-size: 13px; line-height: 1.65; margin-bottom: 16px; }
.intro-paragraph { margin-bottom: 12px; text-align: justify; }
.student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; }
.student-info-table td { padding: 5px 8px; font-size: 12.5px; vertical-align: top; }
.info-label { width: 38%; font-weight: normal; color: #222222; border-bottom: 1px dotted #888888; }
.info-value { width: 62%; border-bottom: 1px dotted #888888; }
.status-paragraph { margin-bottom: 12px; text-align: justify; text-indent: 20px; }
.closing-paragraph { margin-bottom: 18px; text-align: justify; text-indent: 20px; }
.signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
.sig-col-left { width: 50%; vertical-align: top; text-align: left; font-size: 11px; }
.sig-col-right { width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
.sig-role { font-weight: bold; margin-bottom: 3px; }
.sig-sub { font-size: 10px; font-style: italic; }
.anti-fraud-footer { margin-top: 12px; }
CSS;

        // 1. Certificat de Scolarité
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Certificat de Scolarité',
            'type_document'      => 'certificat_scolarite',
            'description'        => 'Certifie l\'inscription régulière pour l\'année académique en cours.',
            'contenu_html'       => '<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Direction des Études et des Affaires Académiques</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Année Universitaire<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="title-container">
        <h1 class="doc-title">CERTIFICAT DE SCOLARITÉ</h1>
        <div class="doc-ref">Réf : SCOL/{annee_actuelle}/{matricule}</div>
    </div>
    <div class="body-content">
        <p class="intro-paragraph">Le Directeur de l\'établissement <strong>{nom_ecole}</strong>, certifie par la présente que :</p>
        <table class="student-info-table">
            <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
            <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
            <tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
            <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
            <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
        </table>
        <p class="status-paragraph">Est régulièrement inscrit(e) au sein de notre établissement pour l\'année universitaire en cours et poursuit son cursus conformément aux dispositions et règlements pédagogiques en vigueur.</p>
        <p class="closing-paragraph">Le présent certificat lui est délivré sur sa demande pour servir et valoir ce que de droit auprès des administrations et organismes compétents.</p>
    </div>
    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div style="margin-top:10px;"><div class="sig-role">Pour la Direction des Études</div><div class="sig-sub">Le Secrétariat Académique</div></div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l\'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="anti-fraud-footer">{qr_code}</div>
</div>',
            'styles_css'         => $css,
            'actif'              => true,
            'validation_requise' => true,
            'langue'             => 'fr',
        ]);

        // 2. Attestation de Réussite
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Attestation de Réussite',
            'type_document'      => 'attestation_reussite',
            'description'        => 'Modèle certifiant l\'admission et la validation du cycle académique.',
            'contenu_html'       => '<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Jury Académique d\'Évaluation &amp; Délibération</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Session Pédagogique<br/><strong>Juin {annee_actuelle}</strong></div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="title-container">
        <h1 class="doc-title">ATTESTATION DE RÉUSSITE</h1>
        <div class="doc-ref">Réf : REUSS/{annee_actuelle}/{matricule}</div>
    </div>
    <div class="body-content">
        <p class="intro-paragraph">Le Président du Jury et Directeur de l\'établissement <strong>{nom_ecole}</strong>, atteste par la présente que :</p>
        <table class="student-info-table">
            <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
            <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
            <tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
            <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
            <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
            <tr><td class="info-label">Décision du Jury :</td><td class="info-value"><strong>ADMIS(E) {mention}</strong></td></tr>
        </table>
        <p class="status-paragraph">A satisfait aux examens de fin d\'études et aux exigences pédagogiques requises pour la validation intégrale des modules et des crédits de la filière.</p>
        <p class="closing-paragraph">La présente attestation lui est délivrée pour servir et valoir ce que de droit.</p>
    </div>
    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Président du Jury</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l\'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="anti-fraud-footer">{qr_code}</div>
</div>',
            'styles_css'         => $css,
            'actif'              => true,
            'validation_requise' => false,
            'langue'             => 'fr',
        ]);

        // 3. Attestation d'Inscription
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Attestation d\'Inscription',
            'type_document'      => 'certificat_scolarite',
            'description'        => 'Atteste de l\'inscription administrative formelle au début de l\'année universitaire.',
            'contenu_html'       => '<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Service Central des Inscriptions &amp; Admissions</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Rentrée Académique<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="title-container">
        <h1 class="doc-title">ATTESTATION D\'INSCRIPTION</h1>
        <div class="doc-ref">Réf : INSC/{annee_actuelle}/{matricule}</div>
    </div>
    <div class="body-content">
        <p class="intro-paragraph">Le Service des Inscriptions de l\'établissement <strong>{nom_ecole}</strong> certifie que :</p>
        <table class="student-info-table">
            <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
            <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
            <tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong></td></tr>
            <tr><td class="info-label">Cycle &amp; Niveau :</td><td class="info-value"><strong>{niveau}</strong></td></tr>
            <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
        </table>
        <p class="status-paragraph">L\'étudiant(e) susmentionné(e) a accompli l\'ensemble des formalités administratives requises et est formellement inscrit(e) sur les registres officiels de l\'établissement.</p>
        <p class="closing-paragraph">Cette attestation est délivrée pour faire valoir auprès des organismes et administrations concernés.</p>
    </div>
    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Service des Admissions</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l\'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="anti-fraud-footer">{qr_code}</div>
</div>',
            'styles_css'         => $css,
            'actif'              => true,
            'validation_requise' => false,
            'langue'             => 'fr',
        ]);

        // 4. Convention & Attestation de Stage
        ModeleDocument::create([
            'uuid'               => (string) Str::uuid(),
            'ecole_id'           => $ecole->id,
            'titre'              => 'Convention & Attestation de Stage',
            'type_document'      => 'convention_stage',
            'description'        => 'Document conventionné autorisant l\'étudiant à effectuer son stage professionnel en entreprise.',
            'contenu_html'       => '<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L\'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Direction des Relations Entreprises &amp; Insertion Professionnelle</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Convention<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="title-container">
        <h1 class="doc-title">CONVENTION &amp; ATTESTATION DE STAGE</h1>
        <div class="doc-ref">Réf : STAGE/{annee_actuelle}/{matricule}</div>
    </div>
    <div class="body-content">
        <p class="intro-paragraph">La Direction de l\'établissement <strong>{nom_ecole}</strong> certifie par la présente que :</p>
        <table class="student-info-table">
            <tr><td class="info-label">Nom et Prénom :</td><td class="info-value"><strong>{nom} {prenom}</strong></td></tr>
            <tr><td class="info-label">Matricule académique :</td><td class="info-value"><strong>{matricule}</strong></td></tr>
            <tr><td class="info-label">Filière d\'études :</td><td class="info-value"><strong>{filiere}</strong> ({niveau})</td></tr>
            <tr><td class="info-label">Année universitaire :</td><td class="info-value"><strong>{annee_universitaire}</strong></td></tr>
        </table>
        <p class="status-paragraph">Est autorisé(e) à effectuer son stage d\'immersion professionnelle ou stage de fin d\'études en entreprise, conformément au cursus pédagogique prescrit. L\'étudiant(e) demeure sous statut d\'assurance scolaire et académique pendant toute la durée conventionnelle du stage.</p>
        <p class="closing-paragraph">La présente convention et attestation est délivrée pour valoir ce que de droit auprès de l\'organisme d\'accueil.</p>
    </div>
    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Responsable des Stages</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l\'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="anti-fraud-footer">{qr_code}</div>
</div>',
            'styles_css'         => $css,
            'actif'              => true,
            'validation_requise' => true,
            'langue'             => 'fr',
        ]);
    }
}
