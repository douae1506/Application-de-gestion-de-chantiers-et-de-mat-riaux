<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    // Récupérer toutes les activités (avec filtres)
    public function index(Request $request)
    {
        $query = Activity::with('user')->orderBy('created_at', 'desc');

        // Filtres
        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->subject_type) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('subject_label', 'like', "%{$request->search}%")
                  ->orWhere('user_nom', 'like', "%{$request->search}%");
            });
        }

        // Pagination
        $perPage = $request->per_page ?? 20;
        $activities = $query->paginate($perPage);

        return response()->json($activities);
    }

    // Récupérer les activités d'un sujet spécifique (ex: toutes les activités d'un chantier)
    public function bySubject(Request $request, $type, $id)
    {
        $activities = Activity::with('user')
            ->forSubject($type, $id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($activities);
    }

    // Récupérer les statistiques des activités (pour le dashboard)
    public function stats()
    {
        $stats = [
            'total' => Activity::count(),
            'by_action' => Activity::selectRaw('action, count(*) as total')
                ->groupBy('action')
                ->get(),
            'by_subject' => Activity::selectRaw('subject_type, count(*) as total')
                ->groupBy('subject_type')
                ->get(),
            'last_7_days' => Activity::where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'recent' => Activity::with('user')->recent(10)->get(),
        ];

        return response()->json($stats);
    }

    // Loguer une activité (méthode utilitaire)
    public static function log($userId, $action, $subjectType, $subjectId, $subjectLabel, $description, $properties = null)
    {
        $user = \App\Models\User::find($userId);

        return Activity::create([
            'user_id' => $userId,
            'user_nom' => $user ? $user->prenom . ' ' . $user->nom : 'Système',
            'user_role' => $user ? $user->role : 'system',
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}