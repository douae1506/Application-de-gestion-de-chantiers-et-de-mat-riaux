<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Chantier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    // Récupérer tous les événements d'un chantier
    public function index(Chantier $chantier)
    {
        $events = $chantier->events()->orderBy('date', 'asc')->orderBy('heure', 'asc')->get();
        return response()->json(['data' => $events]);
    }

    // Créer un événement pour un chantier
    public function store(Request $request, Chantier $chantier)
    {
        $validated = $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date|after_or_equal:today', // Un nouvel événement doit être futur
            'heure'       => 'nullable|date_format:H:i',
            'type'        => ['required', Rule::in(['reunion', 'livraison', 'inspection', 'autre'])],
            'statut'      => ['sometimes', Rule::in(['a_venir', 'termine', 'annule'])],
            'rappel'      => 'sometimes|boolean',
        ]);

        $validated['chantier_id'] = $chantier->id;
        $validated['statut'] = $validated['statut'] ?? 'a_venir';

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $event,
        ], 201);
    }

    // Afficher un événement (sécurisé : doit appartenir au chantier de l'URL)
    public function show(Chantier $chantier, Event $event)
    {
        $this->ensureBelongsToChantier($chantier, $event);
        return response()->json(['data' => $event]);
    }

    // Mettre à jour un événement
    public function update(Request $request, Chantier $chantier, Event $event)
    {
        $this->ensureBelongsToChantier($chantier, $event);

        $validated = $request->validate([
            'titre'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'sometimes|date', // Pas de contrainte "future" ici : on doit pouvoir éditer le passé
            'heure'       => 'nullable|date_format:H:i',
            'type'        => ['sometimes', Rule::in(['reunion', 'livraison', 'inspection', 'autre'])],
            'statut'      => ['sometimes', Rule::in(['a_venir', 'termine', 'annule'])],
            'rappel'      => 'sometimes|boolean',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $event,
        ]);
    }

    // Supprimer un événement
    public function destroy(Chantier $chantier, Event $event)
    {
        $this->ensureBelongsToChantier($chantier, $event);
        $event->delete();
        return response()->json(['success' => true]);
    }

    // ─── Sécurité : empêche d'agir sur l'événement d'un autre chantier ───
    private function ensureBelongsToChantier(Chantier $chantier, Event $event): void
    {
        if ($event->chantier_id !== $chantier->id) {
            abort(404, 'Événement introuvable pour ce chantier.');
        }
    }
}