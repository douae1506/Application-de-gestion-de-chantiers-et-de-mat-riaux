<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Phase;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResponsableController extends Controller
{
    // ───────────────────────────────────────────────────────────
    // Helpers d'autorisation
    // ───────────────────────────────────────────────────────────

    /** Vérifie que le chantier est bien accessible au responsable connecté. */
    private function authorizeChantier(Chantier $chantier): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;

        $estResponsableChantier = $chantier->responsable_id === $user->id;
        $estResponsableProjet   = $chantier->projets()->where('responsable_id', $user->id)->exists();

        if (! $estResponsableChantier && ! $estResponsableProjet) {
            abort(403, "Vous n'avez pas accès à ce chantier.");
        }
    }

    /** Vérifie que le projet appartient bien au responsable connecté. */
    private function authorizeProjet(Projet $projet): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;

        if ($projet->responsable_id !== $user->id) {
            abort(403, "Vous n'avez pas accès à ce projet.");
        }
    }

    /** Vérifie que la phase appartient (via son projet) au responsable connecté. */
    private function authorizePhase(Phase $phase): void
    {
        $this->authorizeProjet($phase->projet);
    }

    /** Requête de base : chantiers visibles par le responsable connecté. */
    private function chantiersScope()
    {
        $user = auth()->user();

        $query = Chantier::with(['client', 'projets']);

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('responsable_id', $user->id)
                  ->orWhereHas('projets', fn ($p) => $p->where('responsable_id', $user->id));
            });
        }

        return $query;
    }

    /** Requête de base : projets visibles par le responsable connecté. */
    private function projetsScope()
    {
        $user = auth()->user();

        $query = Projet::with(['chantier', 'phases', 'expenses']);

        if (! $user->isAdmin()) {
            $query->where('responsable_id', $user->id);
        }

        return $query;
    }

    // ───────────────────────────────────────────────────────────
    // 1. Consulter tableau de bord
    // ───────────────────────────────────────────────────────────
    public function dashboard()
    {
        $user = auth()->user();

        $chantiers = $this->chantiersScope()->get();
        $projets   = $this->projetsScope()->get();

        $totalChantiers   = $chantiers->count();
        $totalProjets     = $projets->count();
        $projetsEnCours   = $projets->where('statut', 'en_cours')->count();
        $projetsTermines  = $projets->where('statut', 'termine')->count();
        $projetsBloques   = $projets->where('statut', 'bloque')->count();
        $projetsRetard    = $projets->filter(fn ($p) => $p->statut !== 'termine'
            && $p->date_fin_prevue && $p->date_fin_prevue->isPast())->count();

        $progressionMoyenne = $projets->count() ? round($projets->avg('progression')) : 0;
        $budgetTotal   = (float) $projets->sum('budget');
        $coutReelTotal = (float) $projets->sum('cout_reel');

        $prochainesPhases = Phase::whereHas('projet', function ($q) use ($user) {
                if (! $user->isAdmin()) {
                    $q->where('responsable_id', $user->id);
                }
            })
            ->whereIn('statut', ['non_commencee', 'en_cours'])
            ->orderBy('date_debut')
            ->limit(6)
            ->get()
            ->map(fn ($ph) => [
                'id'          => $ph->id,
                'nom'         => $ph->nom,
                'projet'      => $ph->projet?->nom,
                'projet_id'   => $ph->projet_id,
                'statut'      => $ph->statut,
                'progression' => $ph->progression,
                'date_debut'  => $ph->date_debut?->format('d M Y'),
            ]);

        $chantiersRecents = $chantiers->sortByDesc('created_at')->take(5)->map(fn ($c) => [
            'id'           => $c->id,
            'nom'          => $c->nom,
            'reference'    => $c->reference,
            'statut'       => $c->statut,
            'statut_label' => $c->statut_label,
            'progression'  => $c->calculerProgression(),
            'ville'        => $c->ville,
        ])->values();

        return response()->json([
            'data' => [
                'kpis' => [
                    'chantiers'    => $totalChantiers,
                    'projets'      => $totalProjets,
                    'en_cours'     => $projetsEnCours,
                    'termines'     => $projetsTermines,
                    'bloques'      => $projetsBloques,
                    'en_retard'    => $projetsRetard,
                    'progression'  => $progressionMoyenne,
                    'budget_total' => $budgetTotal,
                    'cout_reel'    => $coutReelTotal,
                ],
                'chantiers_recents' => $chantiersRecents,
                'prochaines_phases'  => $prochainesPhases,
            ],
        ]);
    }

    // ───────────────────────────────────────────────────────────
    // 2. Consulter la liste des chantiers
    // ───────────────────────────────────────────────────────────
    public function chantiers(Request $request)
    {
        $query = $this->chantiersScope();

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        $chantiers = $query->latest()->get()->map(fn ($c) => [
            'id'                  => $c->id,
            'reference'           => $c->reference,
            'nom'                 => $c->nom,
            'type'                => $c->type,
            'type_label'          => $c->type_label,
            'statut'              => $c->statut,
            'statut_label'        => $c->statut_label,
            'ville'               => $c->ville,
            'adresse'             => $c->adresse,
            'budget_total'        => (float) $c->budget_total,
            'cout_reel'           => (float) $c->cout_reel,
            'progression'         => $c->calculerProgression(),
            'jours_restants'      => $c->jours_restants,
            'date_debut'          => $c->date_debut?->format('d M Y'),
            'date_fin_prevue'     => $c->date_fin_prevue?->format('d M Y'),
            'nb_projets'          => $c->projets->count(),
            'client'              => $c->client ? [
                'nom_complet' => trim($c->client->prenom . ' ' . $c->client->nom),
                'entreprise'  => $c->client->entreprise,
            ] : null,
        ]);

        return response()->json(['data' => $chantiers]);
    }

    // ───────────────────────────────────────────────────────────
    // Détail d'un chantier (+ ses projets)
    // ───────────────────────────────────────────────────────────
    public function chantierShow(Chantier $chantier)
    {
        $this->authorizeChantier($chantier);

        $chantier->load(['client', 'projets.phases', 'documents']);

        $user = auth()->user();
        $projets = $chantier->projets;
        if (! $user->isAdmin()) {
            // Le responsable ne voit que ses propres projets sur ce chantier
            $projets = $projets->where('responsable_id', $user->id)->values();
        }

        return response()->json(['data' => [
            'id'              => $chantier->id,
            'reference'       => $chantier->reference,
            'nom'             => $chantier->nom,
            'description'     => $chantier->description,
            'type_label'      => $chantier->type_label,
            'statut_label'    => $chantier->statut_label,
            'adresse'         => $chantier->adresse,
            'ville'           => $chantier->ville,
            'budget_total'    => (float) $chantier->budget_total,
            'cout_reel'       => (float) $chantier->cout_reel,
            'progression'     => $chantier->calculerProgression(),
            'date_debut'      => $chantier->date_debut?->format('d M Y'),
            'date_fin_prevue' => $chantier->date_fin_prevue?->format('d M Y'),
            'client'          => $chantier->client ? [
                'nom_complet' => trim($chantier->client->prenom . ' ' . $chantier->client->nom),
                'entreprise'  => $chantier->client->entreprise,
                'telephone'   => $chantier->client->telephone,
            ] : null,
            'projets' => $projets->map(fn ($p) => [
                'id'              => $p->id,
                'reference'       => $p->reference,
                'nom'             => $p->nom,
                'categorie'       => $p->categorie,
                'statut'          => $p->statut,
                'statut_label'    => $p->statut_label,
                'progression'     => $p->progression,
                'budget'          => (float) $p->budget,
                'cout_reel'       => (float) $p->cout_reel,
                'nb_phases'       => $p->phases->count(),
                'date_debut'      => $p->date_debut?->format('d M Y'),
                'date_fin_prevue' => $p->date_fin_prevue?->format('d M Y'),
            ])->values(),
        ]]);
    }

    // ───────────────────────────────────────────────────────────
    // 3. Créer un projet (au sein d'un chantier accessible)
    // ───────────────────────────────────────────────────────────
    public function storeProjet(Request $request, Chantier $chantier)
    {
        $this->authorizeChantier($chantier);

        $validated = $request->validate([
            'nom'             => 'required|string|max:255',
            'description'     => 'nullable|string',
            'categorie'       => 'required|string|max:255',
            'budget'          => 'nullable|numeric|min:0',
            'priorite'        => 'nullable|in:faible,normale,haute',
            'date_debut'      => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'couleur'         => 'nullable|string|max:20',
            'observations'    => 'nullable|string',
        ]);

        $count = $chantier->projets()->count();
        $validated['chantier_id']     = $chantier->id;
        $validated['reference']       = sprintf('PRJ-%d-%03d', $chantier->id, $count + 1);
        $validated['statut']          = 'non_commence';
        $validated['progression']     = 0;
        // Le projet est automatiquement assigné au responsable connecté
        $validated['responsable_id']  = auth()->id();

        $projet = Projet::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Projet créé avec succès',
            'data'    => $projet,
        ], 201);
    }

    // ───────────────────────────────────────────────────────────
    // Liste des projets du responsable
    // ───────────────────────────────────────────────────────────
    public function projets()
    {
        $projets = $this->projetsScope()->latest()->get()->map(fn ($p) => $this->formatProjet($p));
        return response()->json(['data' => $projets]);
    }

    // ───────────────────────────────────────────────────────────
    // Détail d'un projet (+ phases)
    // ───────────────────────────────────────────────────────────
    public function projetShow(Projet $projet)
    {
        $this->authorizeProjet($projet);
        $projet->load(['chantier', 'phases' => fn ($q) => $q->orderBy('ordre'), 'expenses']);

        return response()->json(['data' => $this->formatProjet($projet, true)]);
    }

    private function formatProjet(Projet $p, bool $detail = false): array
    {
        $data = [
            'id'              => $p->id,
            'reference'       => $p->reference,
            'nom'             => $p->nom,
            'description'     => $p->description,
            'categorie'       => $p->categorie,
            'statut'          => $p->statut,
            'statut_label'    => $p->statut_label,
            'priorite'        => $p->priorite,
            'progression'     => $p->progression,
            'budget'          => (float) $p->budget,
            'cout_reel'       => (float) $p->cout_reel,
            'couleur'         => $p->couleur ?? '#1a56db',
            'date_debut'      => $p->date_debut?->format('Y-m-d'),
            'date_fin_prevue' => $p->date_fin_prevue?->format('Y-m-d'),
            'date_fin_reelle' => $p->date_fin_reelle?->format('Y-m-d'),
            'nb_phases'       => $p->phases->count(),
            'chantier'        => $p->chantier ? [
                'id'   => $p->chantier->id,
                'nom'  => $p->chantier->nom,
                'ville'=> $p->chantier->ville,
            ] : null,
        ];

        if ($detail) {
            $data['observations'] = $p->observations;
            $data['phases'] = $p->phases->map(fn ($ph) => [
                'id'              => $ph->id,
                'reference'       => $ph->reference,
                'nom'             => $ph->nom,
                'description'     => $ph->description,
                'ordre'           => $ph->ordre,
                'statut'          => $ph->statut,
                'progression'     => $ph->progression,
                'responsable'     => $ph->responsable,
                'validation'      => (bool) $ph->validation,
                'observations'    => $ph->observations,
                'date_debut'      => $ph->date_debut?->format('Y-m-d'),
                'date_fin_prevue' => $ph->date_fin_prevue?->format('Y-m-d'),
                'date_fin_reelle' => $ph->date_fin_reelle?->format('Y-m-d'),
            ])->values();
        }

        return $data;
    }

    // ───────────────────────────────────────────────────────────
    // 4. Planifier des phases (extension de "Créer un projet")
    // ───────────────────────────────────────────────────────────
    public function storePhase(Request $request, Projet $projet)
    {
        $this->authorizeProjet($projet);

        $validated = $request->validate([
            'nom'             => 'required|string|max:255',
            'description'     => 'nullable|string',
            'ordre'           => 'nullable|integer|min:1',
            'date_debut'      => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'responsable'     => 'nullable|string|max:255',
            'observations'    => 'nullable|string',
        ]);

        $count = $projet->phases()->count() + 1;
        $validated['reference']   = sprintf('PHS-%d-%03d', $projet->id, $count);
        $validated['ordre']       = $validated['ordre'] ?? $count;
        $validated['projet_id']   = $projet->id;
        $validated['statut']      = 'non_commencee';
        $validated['progression'] = 0;
        $validated['validation']  = false;

        $phase = Phase::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Phase planifiée avec succès',
            'data'    => $phase,
        ], 201);
    }

    public function updatePhase(Request $request, Phase $phase)
    {
        $this->authorizePhase($phase);

        $validated = $request->validate([
            'nom'             => 'sometimes|required|string|max:255',
            'description'     => 'nullable|string',
            'ordre'           => 'nullable|integer|min:1',
            'date_debut'      => 'sometimes|required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle' => 'nullable|date',
            'responsable'     => 'nullable|string|max:255',
            'observations'    => 'nullable|string',
        ]);

        $phase->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Phase mise à jour',
            'data'    => $phase,
        ]);
    }

    public function destroyPhase(Phase $phase)
    {
        $this->authorizePhase($phase);
        $phase->delete();

        return response()->json(['success' => true, 'message' => 'Phase supprimée']);
    }

    // ───────────────────────────────────────────────────────────
    // 5. Mettre à jour l'avancement (progression / statut d'une phase)
    // ───────────────────────────────────────────────────────────
    public function updateAvancement(Request $request, Phase $phase)
    {
        $this->authorizePhase($phase);

        $validated = $request->validate([
            'progression'     => 'required|integer|min:0|max:100',
            'statut'          => 'sometimes|in:non_commencee,en_cours,terminee,bloquee',
            'date_fin_reelle' => 'nullable|date',
            'observations'    => 'nullable|string',
        ]);

        // Déduire le statut automatiquement si non fourni
        if (! isset($validated['statut'])) {
            $validated['statut'] = match (true) {
                $validated['progression'] >= 100 => 'terminee',
                $validated['progression'] > 0    => 'en_cours',
                default                          => 'non_commencee',
            };
        }
        if ($validated['statut'] === 'terminee' && empty($validated['date_fin_reelle'])) {
            $validated['date_fin_reelle'] = now()->toDateString();
        }

        $phase->update($validated);
        $phase->refresh();

        return response()->json([
            'success' => true,
            'message' => "Avancement mis à jour",
            'data'    => [
                'phase'   => $phase,
                'projet'  => $this->formatProjet($phase->projet->fresh(['phases'])),
            ],
        ]);
    }

    // ───────────────────────────────────────────────────────────
    // 6. Visualiser le planning (toutes les phases des projets du responsable)
    // ───────────────────────────────────────────────────────────
    public function planning(Request $request)
    {
        $user = auth()->user();

        $query = Phase::with('projet.chantier')
            ->whereHas('projet', function ($q) use ($user) {
                if (! $user->isAdmin()) {
                    $q->where('responsable_id', $user->id);
                }
            });

        if ($projetId = $request->get('projet_id')) {
            $query->where('projet_id', $projetId);
        }

        $phases = $query->orderBy('date_debut')->get()->map(fn ($ph) => [
            'id'              => $ph->id,
            'nom'             => $ph->nom,
            'statut'          => $ph->statut,
            'progression'     => $ph->progression,
            'ordre'           => $ph->ordre,
            'date_debut'      => $ph->date_debut?->format('Y-m-d'),
            'date_fin_prevue' => $ph->date_fin_prevue?->format('Y-m-d'),
            'date_fin_reelle' => $ph->date_fin_reelle?->format('Y-m-d'),
            'projet_id'       => $ph->projet_id,
            'projet_nom'      => $ph->projet?->nom,
            'chantier_nom'    => $ph->projet?->chantier?->nom,
        ]);

        // Regrouper par projet pour affichage type Gantt
        $parProjet = $phases->groupBy('projet_id')->map(function ($items, $projetId) {
            return [
                'projet_id'  => (int) $projetId,
                'projet_nom' => $items->first()['projet_nom'],
                'chantier_nom' => $items->first()['chantier_nom'],
                'phases'     => $items->values(),
            ];
        })->values();

        return response()->json(['data' => $parProjet]);
    }

    // ───────────────────────────────────────────────────────────
    // 7. Visualiser la consommation d'un projet
    // ───────────────────────────────────────────────────────────
    public function consommation(Projet $projet)
    {
        $this->authorizeProjet($projet);

        $projet->load(['chantier.sortieStocks' => function ($q) use ($projet) {
            $q->where('projet_id', $projet->id)->with('produit');
        }, 'expenses']);

        $materiaux = $projet->chantier->sortieStocks->sortByDesc('id')->values()->map(fn ($s) => [
            'id'            => $s->id,
            'produit'       => $s->produit?->nom,
            'categorie'     => $s->produit?->categorie,
            'quantite'      => $s->quantite,
            'unite'         => $s->produit?->unite,
            'prix_unitaire' => (float) ($s->produit?->prix_unitaire ?? 0),
            'cout_total'    => (float) ($s->quantite * ($s->produit?->prix_unitaire ?? 0)),
            'date_sortie'   => $s->date_sortie?->format('d/m/Y'),
        ])->values();

        $depenses = $projet->expenses->map(fn ($e) => [
            'id'          => $e->id,
            'nom'         => $e->nom,
            'montant'     => (float) $e->montant,
            'date'        => $e->date ? \Carbon\Carbon::parse($e->date)->format('d/m/Y') : null,
            'description' => $e->description,
        ])->values();

        $totalMateriaux = (float) $materiaux->sum('cout_total');
        $totalDepenses  = (float) $depenses->sum('montant');

        return response()->json(['data' => [
            'projet' => [
                'id'         => $projet->id,
                'nom'        => $projet->nom,
                'reference'  => $projet->reference,
                'budget'     => (float) $projet->budget,
                'cout_reel'  => (float) $projet->cout_reel,
            ],
            'resume' => [
                'total_materiaux'    => $totalMateriaux,
                'total_depenses'     => $totalDepenses,
                'total_consommation' => $totalMateriaux + $totalDepenses,
                'budget_restant'     => max(0, (float) $projet->budget - ($totalMateriaux + $totalDepenses)),
                'pct_consomme'       => $projet->budget > 0
                    ? (int) min(100, round((($totalMateriaux + $totalDepenses) / $projet->budget) * 100))
                    : 0,
            ],
            'materiaux' => $materiaux,
            'depenses'  => $depenses,
        ]]);
    }
}