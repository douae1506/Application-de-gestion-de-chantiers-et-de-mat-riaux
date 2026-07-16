<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    /**
     * Afficher la liste des projets (optionnel).
     */
    public function index(Request $request)
{
    $user = $request->user();

    $query = Projet::with([
    'chantier',
    'phases',
    'expenses',
    'responsable'
    ]);

    // ─── Cloisonnement par rôle ────────────────────────────
    // "chef_projet" : uniquement les projets dont il est responsable.
    // "responsable" : uniquement les projets appartenant à un chantier
    // dont il est le responsable.
    if ($user) {
        if ($user->role === 'chef_projet') {
            $query->where('responsable_id', $user->id);
        } elseif ($user->role === 'responsable') {
            $query->whereHas('chantier', function ($q) use ($user) {
                $q->where('responsable_id', $user->id);
            });
        }
    }

    $projets = $query->get();
    return response()->json($this->formatProjets($projets));
}

    /**
     * Afficher un projet spécifique avec ses phases.
     */
    public function show(Request $request, Projet $projet)
    {
        $projet->load(['phases', 'chantier', 'responsable', 'expenses']);
        $user = $request->user();
        if ($user) {
            if ($user->role === 'chef_projet' && $projet->responsable_id !== $user->id) {
                abort(403, "Vous n'avez pas accès à ce projet.");
            }
            if ($user->role === 'responsable' && $projet->chantier?->responsable_id !== $user->id) {
                abort(403, "Vous n'avez pas accès à ce projet.");
            }
        }
        return response()->json([
            'data' => $this->formatProjetDetail($projet)
        ]);
    }

    /**
     * Créer un nouveau projet (optionnel).
     */
    public function store(Request $request)
    {
        // Validation et création
        $validated = $request->validate([
            'chantier_id' => 'required|exists:chantiers,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie' => 'required|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'priorite' => 'nullable|in:faible,normale,haute',
            'date_debut' => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'couleur' => 'nullable|string|max:20',
            'observations' => 'nullable|string',
            'responsable_id'  => 'nullable|exists:users,id' ,
        ]);

        // Générer une référence unique.
        // Ancien code : basé sur COUNT(*), donc si un projet du chantier a été
        // supprimé, le compteur redescend et régénère une référence déjà
        // utilisée -> violation de contrainte unique. On se base plutôt sur
        // le plus grand numéro déjà attribué pour ce chantier.
        $chantier = \App\Models\Chantier::find($validated['chantier_id']);
        $validated['statut'] = 'non_commence';
        $validated['progression'] = 0;

        $attempts = 0;
        do {
            $lastNumber = \App\Models\Projet::where('chantier_id', $chantier->id)
                ->where('reference', 'like', "PRJ-{$chantier->id}-%")
                ->get()
                ->map(fn ($p) => (int) substr($p->reference, strrpos($p->reference, '-') + 1))
                ->max();

            $nextNumber = ($lastNumber ?? 0) + 1 + $attempts;
            $validated['reference'] = sprintf('PRJ-%d-%03d', $chantier->id, $nextNumber);

            try {
                $projet = Projet::create($validated);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                $attempts++;
                if ($attempts >= 5) {
                    throw $e;
                }
            }
        } while (true);

        return response()->json([
            'success' => true,
            'message' => 'Projet créé avec succès',
            'data' => $projet
        ], 201);
    }

    /**
     * Mettre à jour un projet (optionnel).
     */
    public function update(Request $request, Projet $projet)
    {
        $validated = $request->validate([
            'chantier_id' => 'sometimes|exists:chantiers,id',
            'responsable_id' => 'nullable|exists:users,id',
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'categorie' => 'sometimes|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'cout_reel' => 'nullable|numeric|min:0',
            'priorite' => 'nullable|in:faible,normale,haute',
            'date_debut' => 'sometimes|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle' => 'nullable|date',
            'progression' => 'nullable|integer|min:0|max:100',
            'statut' => 'sometimes|in:non_commence,en_cours,termine,bloque',
            'couleur' => 'nullable|string|max:20',
            'observations' => 'nullable|string',
        ]);

        $projet->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Projet mis à jour',
            'data' => $this->formatProjetDetail($projet)
        ]);
    }

    /**
     * Supprimer un projet (optionnel).
     */
    public function destroy(Projet $projet)
{
    try {
        $projet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projet supprimé'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);
    }
}
public function byChantier($chantierId)
{
    $projets = Projet::where('chantier_id', $chantierId)
        ->orderBy('nom')
        ->get();

    return response()->json([
        'data' => $projets
    ]);
}

    /**
     * Formater un projet pour la liste (optionnel).
     */
    private function formatProjets($projets)
    {
        return $projets->map(fn($p) => $this->formatProjetDetail($p));
    }

    /**
     * Formater les détails d'un projet (inclut phases).
     */
    private function formatProjetDetail(Projet $projet)
    {
        $projet->load([
        'phases',
        'chantier',
        'expenses',
        'responsable'
        ]);
        return [
            'id' => $projet->id,
            'reference' => $projet->reference,
            'chantier_id' => $projet->chantier_id,
            'nom' => $projet->nom,
            'description' => $projet->description,
            'categorie' => $projet->categorie,
            'budget' => (float) $projet->budget,
            'cout_reel' => (float) $projet->cout_reel,
            'priorite' => $projet->priorite,
            'date_debut' => $projet->date_debut?->format('Y-m-d'),
            'date_fin_prevue' => $projet->date_fin_prevue?->format('Y-m-d'),
            'date_fin_reelle' => $projet->date_fin_reelle?->format('Y-m-d'),
            'progression' => $projet->progression,
            'statut' => $projet->statut,
            'statut_label' => $projet->statut_label, // Accesseur à ajouter
            'couleur' => $projet->couleur ?? '#1a56db',
            'observations' => $projet->observations,
            'phases' => $projet->phases->map(fn($ph) => [
                'id' => $ph->id,
                'nom' => $ph->nom,
                'statut' => $ph->statut,
                'progression' => $ph->progression,
                'ordre' => $ph->ordre,
                'date_debut' => $ph->date_debut?->format('Y-m-d'),
                'date_fin_prevue' => $ph->date_fin_prevue?->format('Y-m-d'),
                'responsable' => $ph->responsable,
                'validation' => (bool) $ph->validation,
            ])->values(),
            'chantier' => $projet->chantier ? [
                'id' => $projet->chantier->id,
                'nom' => $projet->chantier->nom,
                'reference' => $projet->chantier->reference,
                'adresse' => $projet->chantier->adresse,
                'ville' => $projet->chantier->ville,
                'pays' => $projet->chantier->pays,
                'code_postal' => $projet->chantier->code_postal,
                'latitude' => $projet->chantier->latitude,
                'longitude' => $projet->chantier->longitude,
                'surface' => (float) $projet->chantier->surface,
                'budget_total' => (float) $projet->chantier->budget_total,
                'statut' => $projet->chantier->statut,
                'statut_label' => $projet->chantier->statut_label,
                'progression' => $projet->chantier->progression,
            ] : null,
            'expenses' => $projet->expenses->map(fn($e) => [
                'id'      => $e->id,
                'nom'     => $e->nom,
                'montant' => (float) $e->montant,
                'date'    => $e->date ? \Carbon\Carbon::parse($e->date)->format('Y-m-d') : null,
            ])->values(),
            'responsable' => $projet->responsable ? [
                'id'      => $projet->responsable->id,
                'prenom'  => $projet->responsable->prenom,
                'nom'     => $projet->responsable->nom,
                'email'   => $projet->responsable->email,
            ] : null,
            ];
    }
}