<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Projet;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Chantier::with(['client', 'projets', 'responsable'])
            ->latest();

        if ($user) {
            if ($user->role === 'responsable') {
                $query->where('responsable_id', $user->id);
            } elseif ($user->role === 'chef_projet') {
                $query->whereHas('projets', function ($q) use ($user) {
                    $q->where('responsable_id', $user->id);
                });
            }
        }

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        // Filtre statut
        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }
        // Filtre type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        $chantiers = $query->get()->map(fn($c) => $this->formatChantier($c));
        return response()->json(['data' => $chantiers]);
    }

    /**
     * Vérifie qu'un utilisateur "responsable"/"chef_projet" a bien accès
     * à ce chantier précis (sinon 403). Les autres rôles (admin, magasinier
     * via permissions) ne sont pas concernés par cette restriction.
     */
    private function assertAccess(Chantier $chantier, ?\App\Models\User $user): void
    {
        if (!$user) return;
        if ($user->role === 'responsable' && $chantier->responsable_id !== $user->id) {
            abort(403, "Vous n'avez pas accès à ce chantier.");
        }
        if ($user->role === 'chef_projet') {
            $hasProjet = $chantier->projets()->where('responsable_id', $user->id)->exists();
            if (!$hasProjet) {
                abort(403, "Vous n'avez pas accès à ce chantier.");
            }
        }
    }

    // ─── Détail complet ───────────────────────────────────────
    public function show(Request $request, Chantier $chantier)
    {
        $this->assertAccess($chantier, $request->user());
        $chantier->load([
            'client',
            'responsable',
            'projets.phases',
            'projets.responsable',
            'documents',
            'sortieStocks.produit',
        ]);
        return response()->json(['data' => $this->formatChantierDetail($chantier)]);
    }

    // ─── Création ─────────────────────────────────────────────
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id'       => 'required|exists:clients,id',
                'nom'             => 'required|string|max:255',
                'description'     => 'nullable|string',
                'type'            => 'required|in:residentiel,commercial,industriel,public',
                'adresse'         => 'required|string|max:500',
                'ville'           => 'required|string|max:255',
                'pays'            => 'nullable|string|max:100',
                'code_postal'     => 'nullable|string|max:20',
                'surface'         => 'nullable|numeric|min:0',
                'budget_total'    => 'nullable|numeric|min:0',
                'date_debut'      => 'required|date',
                'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
                'architecte'      => 'nullable|string|max:255',
                'observations'    => 'nullable|string',
                'responsable_id'  => 'nullable|exists:users,id',
                'latitude'        => 'nullable|numeric|between:-9000,9000',
                'longitude'       => 'nullable|numeric|between:-9000,9000',
            ]);
            $validated['reference'] = Chantier::genererReference();
            $validated['statut'] = 'planifie';
            $validated['latitude'] = $validated['latitude'] ?? null;
            $validated['longitude'] = $validated['longitude'] ?? null;
            $chantier = Chantier::create($validated);
            return response()->json($chantier);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }
    // ─── Mise à jour ──────────────────────────────────────────
    public function update(Request $request, Chantier $chantier)
    {
        $this->assertAccess($chantier, $request->user());
        $validated = $request->validate([
            'client_id'       => 'sometimes|exists:clients,id',
            'nom'             => 'sometimes|required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'sometimes|in:residentiel,commercial,industriel,public',
            'adresse'         => 'sometimes|string|max:500',
            'ville'           => 'sometimes|string|max:255',
            'pays'            => 'nullable|string|max:100',
            'code_postal'     => 'nullable|string|max:20',
            'surface'         => 'nullable|numeric|min:0',
            'budget_total'    => 'nullable|numeric|min:0',
            'cout_reel'       => 'nullable|numeric|min:0',
            'date_debut'      => 'sometimes|date',
            'date_fin_prevue' => 'nullable|date',
            'date_fin_reelle' => 'nullable|date',
            'statut'          => 'sometimes|in:planifie,en_cours,suspendu,termine,annule',
            'progression'     => 'sometimes|integer|min:0|max:100',
            'architecte'      => 'nullable|string|max:255',
            'observations'    => 'nullable|string',
            'latitude'        => 'nullable|numeric|between:-9000,9000',
        'longitude'       => 'nullable|numeric|between:-9000,9000',
        ]);
        $chantier->update($validated);
        $chantier->load(['client', 'projets']);
        return response()->json([
            'success' => true,
            'message' => 'Chantier mis à jour',
            'data'    => $this->formatChantier($chantier),
        ]);
    }

    // ─── Suppression ──────────────────────────────────────────
    public function destroy(Chantier $chantier)
    {
        $chantier->delete();
        return response()->json(['success' => true, 'message' => 'Chantier supprimé']);
    }

    // ─── Projets d'un chantier ────────────────────────────────
    public function projets(Request $request, Chantier $chantier)
    {
        $this->assertAccess($chantier, $request->user());
        $projets = $chantier->projets()->with('phases')->get();
        return response()->json(['data' => $projets]);
    }

    // ─── Ajouter un projet ────────────────────────────────────
    public function storeProjet(Request $request, Chantier $chantier)
    {
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
        $validated['chantier_id'] = $chantier->id;
        $validated['reference']   = sprintf('PRJ-%d-%03d', $chantier->id, $count + 1);
        $validated['statut']      = 'non_commence';

        $projet = Projet::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Projet ajouté',
            'data'    => $projet,
        ], 201);
    }

    // ─── Formatage liste ──────────────────────────────────────
    private function formatChantier(Chantier $c): array
    {
        return [
            'id'                  => $c->id,
            'reference'           => $c->reference,
            'nom'                 => $c->nom,
            'description'         => $c->description,
            'type'                => $c->type,
            'type_label'          => $c->type_label,
            'statut'              => $c->statut,
            'statut_label'        => $c->statut_label,
            'adresse'             => $c->adresse,
            'ville'               => $c->ville,
            'pays'                => $c->pays,
            'surface'             => $c->surface,
            'budget_total'        => (float) $c->budget_total,
            'cout_reel'           => (float) $c->cout_reel,
            'budget_restant'      => $c->budget_restant,
            'pourcentage_depense' => $c->pourcentage_depense,
            'progression' => $c->calculerProgression(),
            'jours_restants'      => $c->jours_restants,
            'date_debut'          => $c->date_debut?->format('d M Y'),
            'date_fin_prevue'     => $c->date_fin_prevue?->format('d M Y'),
            'architecte'          => $c->architecte,
            'nb_projets'          => $c->projets ? $c->projets->count() : 0,
            'responsable'         => $c->responsable ? [
                'id'          => $c->responsable->id,
                'nom_complet' => $c->responsable->nom_complet,
                'email'       => $c->responsable->email,
                'telephone'   => $c->responsable->telephone_mobile ?? $c->responsable->telephone_pro,
            ] : null,
            'client'              => $c->client ? [
                'id'         => $c->client->id,
                'nom'        => $c->client->nom,
                'prenom'     => $c->client->prenom,
                'entreprise' => $c->client->entreprise,
                'nom_complet'=> trim($c->client->prenom . ' ' . $c->client->nom),
            ] : null,
            'created_at' => $c->created_at?->format('d/m/Y'),
            'latitude'  => $c->latitude,
        'longitude' => $c->longitude,
        ];
    }

    // ─── Formatage détail ─────────────────────────────────────
    private function formatChantierDetail(Chantier $c): array
    {
        $base = $this->formatChantier($c);
        $base['progression'] = $c->calculerProgression();
        $base['observations'] = $c->observations;
        $base['code_postal']  = $c->code_postal;
        $base['latitude']     = $c->latitude;
        $base['longitude']    = $c->longitude;
        $base['date_fin_reelle'] = $c->date_fin_reelle?->format('d M Y');
        $base['date_debut_raw']     = $c->date_debut?->toDateString();
        $base['date_fin_prevue_raw']= $c->date_fin_prevue?->toDateString();

        // Projets avec phases
        $base['projets'] = $c->projets->map(fn($p) => [
            'id'              => $p->id,
            'reference'       => $p->reference,
            'nom'             => $p->nom,
            'description'     => $p->description,
            'categorie'       => $p->categorie,
            'statut'          => $p->statut,
            'statut_label'    => $p->statut_label,
            'progression'     => $p->progression,
            'budget'          => (float) $p->budget,
            'cout_reel'       => (float) $p->cout_reel,
            'priorite'        => $p->priorite,
            'couleur'         => $p->couleur ?? '#1a56db',
            'date_debut'      => $p->date_debut?->format('d M Y'),
            'date_fin_prevue' => $p->date_fin_prevue?->format('d M Y'),
            'date_debut_raw'  => $p->date_debut?->toDateString(),
            'date_fin_raw'    => $p->date_fin_prevue?->toDateString(),
            'responsable'     => $p->responsable ? [
                'id'          => $p->responsable->id,
                'nom_complet' => $p->responsable->nom_complet,
            ] : null,
            'nb_phases'       => $p->phases->count(),
            'phases'          => $p->phases->map(fn($ph) => [
                'id'          => $ph->id,
                'nom'         => $ph->nom,
                'statut'      => $ph->statut,
                'progression' => $ph->progression,
                'ordre'       => $ph->ordre,
            ]),
        ])->values();

        // Documents
        $base['documents'] = $c->documents->map(fn($d) => [
            'id'           => $d->id,
            'nom'          => $d->nom,
            'type'         => $d->type,
            'taille'       => $d->taille,
            'taille_format'=> $d->taille_formatee,
            'fichier'      => $d->fichier,
            'created_at'   => $d->created_at?->format('d/m/Y'),
        ])->values();

        // Matériaux utilisés (sorties stock) — le plus récent en premier
        $base['materiaux'] = $c->sortieStocks->sortByDesc('id')->values()->map(fn($s) => [
            'sortie_id'  => $s->id,
            'projet_id'    => $s->projet_id, 
            'produit'    => $s->produit?->nom,
            'categorie'  => $s->produit?->categorie,
            'quantite'   => $s->quantite,
            'unite'      => $s->produit?->unite,
            'cout_unitaire' => $s->produit?->prix_unitaire ?? 0,
            'cout_total' => $s->quantite * ($s->produit?->prix_unitaire ?? 0),
            'date_sortie'=> $s->date_sortie?->format('d/m/Y'),
        ])->values();

        return $base;
    }
}