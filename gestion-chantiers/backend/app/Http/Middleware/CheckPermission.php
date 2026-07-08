<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Vérifie que l'utilisateur possède au moins une des permissions listées.
     *
     * Usage dans les routes :
     *   ->middleware('permission:view_produits')
     *   ->middleware('permission:create_produits,edit_produits')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->est_actif) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Accès refusé. Vous n\'avez pas les droits nécessaires.',
        ], 403);
    }
}
