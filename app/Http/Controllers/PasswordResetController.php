<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\Utilisateur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Gestion de la réinitialisation de mot de passe par email.
 * Classé "Essentiel" (reclassé depuis "Utile") car indispensable en production.
 */
class PasswordResetController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation.
     * Route: GET /portal/{code_unique}/mot-de-passe/oublie
     */
    public function showForgotForm(string $code_unique)
    {
        return view('portal.forgot_password', compact('code_unique'));
    }

    /**
     * Envoie un email avec le lien de réinitialisation.
     * Route: POST /portal/{code_unique}/mot-de-passe/oublie
     */
    public function sendResetLink(Request $request, string $code_unique)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $utilisateur = Utilisateur::where('email', $request->input('email'))
            ->whereHas('ecole', fn($q) => $q->where('code_unique', $code_unique))
            ->where('statut', 'actif')
            ->first();

        // Réponse identique qu'on trouve ou non l'utilisateur (évite l'énumération)
        if ($utilisateur) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $utilisateur->email],
                [
                    'email'      => $utilisateur->email,
                    'token'      => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = route('tenant.password.reset', [
                'code_unique' => $code_unique,
                'token'       => $token,
                'email'       => $utilisateur->email,
            ]);

            // Envoi de l'email
            Mail::send('emails.password_reset', [
                'utilisateur' => $utilisateur,
                'resetUrl'    => $resetUrl,
                'expireMin'   => 60,
            ], function ($message) use ($utilisateur) {
                $message->to($utilisateur->email, $utilisateur->prenom . ' ' . $utilisateur->nom)
                    ->subject('Réinitialisation de votre mot de passe');
            });

            AuditService::log(
                action:        LogActivite::ACTION_RESET_MDP,
                utilisateurId: $utilisateur->id,
                ecoleId:       $utilisateur->ecole_id,
                details:       ['etape' => 'lien_envoye']
            );
        }

        return back()->with('success', 'Si cet email existe dans notre système, vous recevrez un lien de réinitialisation.');
    }

    /**
     * Affiche le formulaire de nouveau mot de passe.
     * Route: GET /portal/{code_unique}/mot-de-passe/reinitialiser
     */
    public function showResetForm(Request $request, string $code_unique)
    {
        return view('portal.reset_password', [
            'code_unique' => $code_unique,
            'token'       => $request->query('token'),
            'email'       => $request->query('email'),
        ]);
    }

    /**
     * Applique le nouveau mot de passe.
     * Route: POST /portal/{code_unique}/mot-de-passe/reinitialiser
     */
    public function resetPassword(Request $request, string $code_unique)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'mot_de_passe'          => 'required|min:8|confirmed',
            'mot_de_passe_confirmation' => 'required',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (
            !$record ||
            !Hash::check($request->input('token'), $record->token) ||
            Carbon::parse($record->created_at)->addMinutes(60)->isPast()
        ) {
            return back()->withErrors(['token' => 'Lien invalide ou expiré. Veuillez recommencer.']);
        }

        $utilisateur = Utilisateur::where('email', $request->input('email'))
            ->whereHas('ecole', fn($q) => $q->where('code_unique', $code_unique))
            ->firstOrFail();

        $utilisateur->update([
            'mot_de_passe'              => Hash::make($request->input('mot_de_passe')),
            'doit_changer_mot_de_passe' => false,
        ]);

        DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

        AuditService::log(
            action:        LogActivite::ACTION_RESET_MDP,
            utilisateurId: $utilisateur->id,
            ecoleId:       $utilisateur->ecole_id,
            details:       ['etape' => 'mot_de_passe_reinitialise']
        );

        return redirect()->route('tenant.portal', ['code_unique' => $code_unique])
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
