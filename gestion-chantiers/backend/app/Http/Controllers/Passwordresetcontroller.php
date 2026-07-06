<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    private const OTP_EXPIRES_MINUTES = 15;
    private const MAX_ATTEMPTS        = 5;   // Tentatives de vérification max
    private const SEND_COOLDOWN       = 60;  // Secondes entre deux envois

    // Envoyer le code OTP
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email'    => 'L\'adresse email n\'est pas valide.',
        ]);

        $email = strtolower(trim($request->email));

        // Rate limiting : 1 envoi toutes les 60 secondes par email
        $rateLimitKey = 'otp-send:' . $email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'message' => "Veuillez patienter {$seconds} secondes avant de renvoyer un code.",
                'retry_after' => $seconds,
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, self::SEND_COOLDOWN);

        // Réponse identique que l'email existe ou non (sécurité anti-enumération)
        $user = User::where('email', $email)->first();

        if ($user) {
            // Invalider tous les anciens codes de cet email
            PasswordResetOtp::where('email', $email)->update(['used' => true]);

            // Générer un code à 6 chiffres
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Stocker le code hashé en base
            PasswordResetOtp::create([
                'email'      => $email,
                'code'       => Hash::make($code),
                'expires_at' => now()->addMinutes(self::OTP_EXPIRES_MINUTES),
                'ip_address' => $request->ip(),
            ]);

            // Envoyer l'email
            Mail::to($email)->send(new PasswordResetOtpMail(
                code:             $code,
                prenom:           $user->prenom,
                expiresInMinutes: self::OTP_EXPIRES_MINUTES,
            ));
        }

        return response()->json([
            'message'          => 'Si cette adresse est enregistrée, un code vous a été envoyé.',
            'expires_in'       => self::OTP_EXPIRES_MINUTES * 60, // secondes pour le timer frontend
        ]);
    }

    // Vérifier le code OTP (sans encore changer le mdp)
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($request->email));

        // Rate limiting : 5 tentatives par email
        $rateLimitKey = 'otp-verify:' . $email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
                'retry_after' => $seconds,
            ], 429);
        }

        $otp = PasswordResetOtp::where('email', $email)
            ->where('used', false)
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid() || ! Hash::check($request->code, $otp->code)) {
            RateLimiter::hit($rateLimitKey, 300); // blocage 5 min après 5 échecs
            return response()->json([
                'message' => 'Code invalide ou expiré.',
                'seconds_remaining' => $otp?->secondsRemaining() ?? 0,
            ], 422);
        }

        RateLimiter::clear($rateLimitKey);

        // Retourner un token de session temporaire (signé) pour l'étape 3
        $resetToken = encrypt([
            'email'      => $email,
            'otp_id'     => $otp->id,
            'expires_at' => now()->addMinutes(10)->timestamp, // 10 min pour saisir le nouveau mdp
        ]);

        return response()->json([
            'message'      => 'Code valide. Vous pouvez maintenant définir votre nouveau mot de passe.',
            'reset_token'  => $resetToken,
        ]);
    }

    // Changer le mot de passe avec le token de l'étape 2
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token'          => ['required', 'string'],
            'password'             => ['required', 'confirmed', 'min:8'],
        ], [
            'password.required'   => 'Le mot de passe est obligatoire.',
            'password.confirmed'  => 'La confirmation ne correspond pas.',
            'password.min'        => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Déchiffrer et valider le token
        try {
            $payload = decrypt($request->reset_token);
        } catch (\Exception) {
            return response()->json(['message' => 'Jeton invalide ou corrompu.'], 422);
        }

        if (now()->timestamp > $payload['expires_at']) {
            return response()->json(['message' => 'Le délai de réinitialisation a expiré. Recommencez depuis le début.'], 422);
        }

        // Vérifier que l'OTP n'a pas déjà été utilisé
        $otp = PasswordResetOtp::find($payload['otp_id']);
        if (! $otp || $otp->used) {
            return response()->json(['message' => 'Ce code a déjà été utilisé.'], 422);
        }

        $user = User::where('email', $payload['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        // Marquer l'OTP comme utilisé et mettre à jour le mot de passe
        $otp->update(['used' => true]);
        $user->update(['password' => Hash::make($request->password)]);

        // Révoquer tous les tokens de session existants (déconnexion de tous les appareils)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.',
        ]);
    }
}