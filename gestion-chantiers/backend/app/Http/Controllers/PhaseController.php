<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\Projet;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    /**
     * Créer une nouvelle phase pour un projet
     */
    public function store(Request $request, Projet $projet)
    {
        $validated = $request->validate([
            'nom'             => 'required|string|max:255',
            'description'     => 'nullable|string',
            'ordre'           => 'nullable|integer|min:1',
            'date_debut'      => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle' => 'nullable|date',
            'progression'     => 'nullable|integer|min:0|max:100',
            'responsable'     => 'nullable|string|max:255',
            'statut'          => 'sometimes|in:non_commencee,en_cours,terminee,bloquee',
            'validation'      => 'sometimes|boolean',
            'observations'    => 'nullable|string',
        ]);

        // Générer une référence unique
        $count = $projet->phases()->count() + 1;
        $validated['reference'] = sprintf('PHS-%d-%03d', $projet->id, $count);
        $validated['projet_id'] = $projet->id;
        if (!isset($validated['statut'])) {
            $validated['statut'] = 'non_commencee';
        }

        $phase = Phase::create($validated);
$this->updateProjectProgress($phase->projet_id);
        return response()->json([
            'success' => true,
            'data'    => $phase,
        ], 201);
    }

    /**
     * Mettre à jour une phase
     */
    public function update(Request $request, Phase $phase)
    {
        $validated = $request->validate([
            'nom'             => 'sometimes|required|string|max:255',
            'description'     => 'nullable|string',
            'ordre'           => 'nullable|integer|min:1',
            'date_debut'      => 'sometimes|required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle' => 'nullable|date',
            'progression'     => 'nullable|integer|min:0|max:100',
            'responsable'     => 'nullable|string|max:255',
            'statut'          => 'sometimes|in:non_commencee,en_cours,terminee,bloquee',
            'validation'      => 'sometimes|boolean',
            'observations'    => 'nullable|string',
        ]);

        $phase->update($validated);
        $this->updateProjectProgress($phase->projet_id);
        return response()->json([
            'success' => true,
            'data'    => $phase,
        ]);
    }

    /**
     * Supprimer une phase
     */
    public function destroy(Phase $phase)
    {
        $projectId = $phase->projet_id;

$phase->delete();

$this->updateProjectProgress($projectId);

return response()->json([
    'success' => true,
    'message' => 'Phase supprimée',
]);
    }
    private function updateProjectProgress($projectId)
{
    $projet = Projet::with('phases')->find($projectId);

    if (!$projet) {
        return;
    }

    $progression = $projet->phases->count()
        ? round($projet->phases->avg('progression'))
        : 0;

    $projet->progression = $progression;
    $projet->save();

}

}