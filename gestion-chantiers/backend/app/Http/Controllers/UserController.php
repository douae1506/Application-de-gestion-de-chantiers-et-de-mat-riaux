<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * GET /api/admin/users
     * Liste avec recherche, filtre rôle, filtre statut, pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Recherche nom / prénom / email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom',    'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email',  'like', "%{$search}%");
            });
        }

        // Filtre rôle
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Filtre statut actif
        if ($request->has('est_actif') && $request->get('est_actif') !== '') {
            $query->where('est_actif', filter_var($request->get('est_actif'), FILTER_VALIDATE_BOOLEAN));
        }

        // Tri
        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['nom', 'prenom', 'email', 'role', 'created_at', 'derniere_connexion_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min((int) $request->get('per_page', 7), 50);
        $users   = $query->paginate($perPage);

        // Compteurs par rôle (pour les pills stats)
        $counts = User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        return response()->json([
            'data'       => $users->items(),
            'meta'       => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
            ],
            'counts' => [
                'total'       => User::count(),
                'admin'       => $counts['admin']       ?? 0,
                'responsable'  => $counts['responsable'] ?? 0,
                'chef_projet' => $counts['chef_projet'] ?? 0,
                'magasinier'  => $counts['magasinier']  ?? 0,
                'actifs'      => User::where('est_actif', true)->count(),
                'inactifs'    => User::where('est_actif', false)->count(),
            ],
        ]);
    }

    /**
     * GET /api/admin/users/{id}
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user]);
    }

    /**
     * POST /api/admin/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'nom'              => ['required','string','max:100'],
            'prenom'           => ['required','string','max:100'],
            'email'            => ['required','email','max:150','unique:users,email'],
            'telephone_pro'    => ['nullable','string','max:20'],
            'telephone_mobile' => ['nullable','string','max:20'],
            'photo_profil'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'role' => ['required','in:admin,responsable,chef_projet,magasinier'],
            'password'         => ['required','confirmed',Password::min(8)],
            'est_actif'        => ['boolean'],
        ]);
        $photo = null;
        if($request->hasFile('photo_profil')){
            $photo = $request->file('photo_profil')
                             ->store('users','public');
        }
        $user = User::create([
            'nom'              => $validated['nom'],
            'prenom'           => $validated['prenom'],
            'email'            => $validated['email'],
            'telephone_pro'    => $validated['telephone_pro'] ?? null,
            'telephone_mobile' => $validated['telephone_mobile'] ?? null,
            'photo_profil'     => $photo,
            'role'             => $validated['role'],
            'password'         => Hash::make($validated['password']),
            'est_actif'        => $validated['est_actif'] ?? true,
        ]);
        \App\Services\NotificationService::utilisateurCree($user);

        return response()->json([
            'message'=>'Utilisateur créé avec succès.',
            'data'=>$user
        ],201);
    }
    /**
     * PUT /api/admin/users/{id}
     */
    public function update(Request $request, User $user): JsonResponse
    {
    $validated = $request->validate([

        'nom'              => ['sometimes','required','string','max:100'],
        'prenom'           => ['sometimes','required','string','max:100'],
        'email'            => ['sometimes','required','email','max:150',"unique:users,email,$user->id"],
        'telephone_pro'    => ['nullable','string','max:20'],
        'telephone_mobile' => ['nullable','string','max:20'],
        'photo_profil'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        'role' => ['sometimes','required','in:admin,responsable,chef_projet,magasinier'],
        'est_actif'        => ['sometimes','boolean'],
        'password'         => ['nullable','confirmed',Password::min(8)]

    ]);
    
    if($request->hasFile('photo_profil')){

        $photo = $request->file('photo_profil')
                         ->store('users','public');

        $validated['photo_profil'] = $photo;
    }

    if(!empty($validated['password'])){

        $validated['password'] = Hash::make($validated['password']);
        
    }else{

        unset($validated['password']);

    }

    $user->update($validated);

    return response()->json([

        'message'=>'Utilisateur modifié avec succès.',

        'data'=>$user->fresh()

    ]);
    }

    /**
     * DELETE /api/admin/users (suppression multiple)
     */

    public function destroy(User $user): JsonResponse
    {
    if ($user->id === auth()->id()) {
        return response()->json([
            'message' => 'Impossible de supprimer votre propre compte.'
        ], 403);
    }

    $nomComplet = "{$user->prenom} {$user->nom}";

    $user->delete();

    \App\Services\NotificationService::utilisateurSupprime($nomComplet);

    return response()->json([
        'message' => 'Utilisateur supprimé avec succès.'
    ]);
    }
    public function destroyMany(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($request->ids)->reject(fn($id) => $id === auth()->id());
        $noms = User::whereIn('id', $ids)->get(['nom', 'prenom'])->map(fn ($u) => "{$u->prenom} {$u->nom}");

        User::whereIn('id', $ids)->delete();

        foreach ($noms as $nomComplet) {
            \App\Services\NotificationService::utilisateurSupprime($nomComplet);
        }

        return response()->json([
            'message' => "{$ids->count()} utilisateur(s) supprimé(s) avec succès.",
        ]);
    }

    /**
     * PATCH /api/admin/users/{id}/toggle-status
     */
    public function toggleStatus(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Impossible de désactiver votre propre compte.'], 403);
        }

        $user->update(['est_actif' => !$user->est_actif]);

        return response()->json([
            'message'   => $user->est_actif ? 'Compte activé.' : 'Compte désactivé.',
            'est_actif' => $user->est_actif,
        ]);
    }

    public function login(Request $request)
    {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Identifiants incorrects.'], 401);
    }
    $user->updateLastLogin();
    $token = $user->createToken('auth_token')->plainTextToken;
    return response()->json([
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'user'         => $user,
    ]);
    }  
}