<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'               => ['required', 'string', 'max:100'],
            'prenom'            => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:150', 'unique:users,email'],
            'telephone_pro'     => ['nullable', 'string', 'max:20'],
            'telephone_mobile'  => ['nullable', 'string', 'max:20'],
            'password'          => ['required', 'confirmed', Password::min(8)],
            'role'              => ['required', 'in:admin,responsable,chef_projet,magasinier'],
        ], [
            'nom.required'            => 'Le nom est obligatoire.',
            'prenom.required'         => 'Le prénom est obligatoire.',
            'email.required'          => 'L\'adresse email est obligatoire.',
            'email.unique'            => 'Cette adresse email est déjà utilisée.',
            'password.required'       => 'Le mot de passe est obligatoire.',
            'password.confirmed'      => 'La confirmation du mot de passe ne correspond pas.',
            'role.required'           => 'Le rôle est obligatoire.',
            'role.in'                 => 'Le rôle sélectionné est invalide.',
        ]);

        $user = User::create([
            'nom'              => $validated['nom'],
            'prenom'           => $validated['prenom'],
            'email'            => $validated['email'],
            'telephone_pro'    => $validated['telephone_pro'] ?? null,
            'telephone_mobile' => $validated['telephone_mobile'] ?? null,
            'password'         => Hash::make($validated['password']),
            'role'             => $validated['role'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => $this->formatUser($user),
            'token'   => $token,
        ], 201);
    }

    /**
     * Connexion d'un utilisateur existant.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        if (! $user->est_actif) {
            return response()->json([
                'message' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        // Mettre à jour la date de dernière connexion
        $user->update(['derniere_connexion_at' => now()]);

        // Révoquer les anciens tokens et en créer un nouveau
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => $this->formatUser($user),
            'token'   => $token,
        ]);
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Retourner les informations de l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Formater les données utilisateur pour la réponse.
     */
    private function formatUser(User $user): array
    {
        return [
            'id'               => $user->id,
            'nom'              => $user->nom,
            'prenom'           => $user->prenom,
            'nom_complet'      => $user->nom_complet,
            'email'            => $user->email,
            'telephone_pro'    => $user->telephone_pro,
            'telephone_mobile' => $user->telephone_mobile,
            'role'             => $user->role,
            'est_actif'        => $user->est_actif,
            'permissions'      => $user->permissions(),
            'redirect_to'      => $this->getRedirectPath($user->role),
        ];
    }


    /**
     * Retourner le chemin de redirection selon le rôle.
     * Les 4 rôles partagent désormais le même espace applicatif
     * (/admin/*), l'accès fin étant géré par permission.
     */
    private function getRedirectPath(string $role): string
    {
        return match ($role) {
            'admin'       => '/admin/dashboard',
            'responsable' => '/admin/dashboard',
            'chef_projet' => '/admin/dashboard',
            'magasinier'  => '/admin/dashboard',
            default       => '/',
        };
    }
}