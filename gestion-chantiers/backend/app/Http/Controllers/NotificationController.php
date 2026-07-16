<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Récupère les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = AppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtrer les non-lues
        if ($request->get('unread') === 'true') {
            $query->whereNull('read_at');
        }

        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'unread_count' => AppNotification::where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $notification = AppNotification::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        AppNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }

    /**
     * Compter les notifications non lues
     */
    public function unreadCount()
    {
        $count = AppNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}