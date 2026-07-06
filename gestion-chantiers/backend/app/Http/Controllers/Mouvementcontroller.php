<?php

namespace App\Http\Controllers;

use App\Models\EntreeStock;
use App\Models\Produit;
use App\Models\Projet;
use App\Models\SortieStock;
use App\Models\Stock;
use App\Models\Transfert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectExpense;

class MouvementController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type'); 
        $stockId = $request->get('stock_id');
        $produitId = $request->get('produit_id');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $mouvements = collect();

        if (!$type || $type === 'entree') {
            $q = EntreeStock::with(['produit', 'stock', 'fournisseur']);
            if ($stockId) $q->where('stock_id', $stockId);
            if ($produitId) $q->where('produit_id', $produitId);
            if ($dateDebut) $q->whereDate('date_entree', '>=', $dateDebut);
            if ($dateFin) $q->whereDate('date_entree', '<=', $dateFin);

            $mouvements = $mouvements->concat($q->get()->map(fn ($e) => [
                'id'           => 'entree-' . $e->id,
                'type'         => 'entree',
                'type_label'   => 'Entrée',
                'produit'      => $e->produit?->nom,
                'produit_id'   => $e->produit_id,
                'quantite'     => $e->quantite,
                'unite'        => $e->produit?->unite,
                'valeur'       => (float) ($e->quantite * $e->prix_unitaire),
                'date'         => $e->date_entree?->format('Y-m-d'),
                'date_label'   => $e->date_entree?->format('d/m/Y'),
                'depot'        => $e->stock?->nom,
                'depot_destination' => $e->stock?->nom,
                'fournisseur'  => $e->fournisseur?->nom,
                'numero_facture' => $e->numero_facture,
                'observations' => $e->observations,
            ]));
        }

        if (!$type || $type === 'sortie') {
            $q = SortieStock::with(['produit', 'stock', 'chantier', 'projet']);
            if ($stockId) $q->where('stock_id', $stockId);
            if ($produitId) $q->where('produit_id', $produitId);
            if ($dateDebut) $q->whereDate('date_sortie', '>=', $dateDebut);
            if ($dateFin) $q->whereDate('date_sortie', '<=', $dateFin);

            $mouvements = $mouvements->concat($q->get()->map(fn ($s) => [
                'id'           => 'sortie-' . $s->id,
                'type'         => 'sortie',
                'type_label'   => 'Sortie',
                'produit'      => $s->produit?->nom,
                'produit_id'   => $s->produit_id,
                'quantite'     => $s->quantite,
                'unite'        => $s->produit?->unite,
                'valeur'       => (float) ($s->quantite * ($s->produit?->prix_unitaire ?? 0)),
                'date'         => $s->date_sortie?->format('Y-m-d'),
                'date_label'   => $s->date_sortie?->format('d/m/Y'),
                'depot'        => $s->stock?->nom,
                'projet'       => $s->projet?->nom,
                'chantier'     => $s->chantier?->nom,
                'beneficiaire' => $s->beneficiaire,
                'observations' => $s->observations,
            ]));
        }

        if (!$type || $type === 'transfert') {
            $q = Transfert::with(['produit', 'stockSource', 'stockDestination']);
            if ($stockId) $q->where(fn ($w) => $w->where('stock_source_id', $stockId)->orWhere('stock_destination_id', $stockId));
            if ($produitId) $q->where('produit_id', $produitId);
            if ($dateDebut) $q->whereDate('date_transfert', '>=', $dateDebut);
            if ($dateFin) $q->whereDate('date_transfert', '<=', $dateFin);

            $mouvements = $mouvements->concat($q->get()->map(fn ($t) => [
                'id'           => 'transfert-' . $t->id,
                'type'         => 'transfert',
                'type_label'   => 'Transfert',
                'produit'      => $t->produit?->nom,
                'produit_id'   => $t->produit_id,
                'quantite'     => $t->quantite,
                'unite'        => $t->produit?->unite,
                'valeur'       => (float) ($t->quantite * ($t->produit?->prix_unitaire ?? 0)),
                'date'         => $t->date_transfert?->format('Y-m-d'),
                'date_label'   => $t->date_transfert?->format('d/m/Y'),
                'depot_source'      => $t->stockSource?->nom,
                'depot_destination' => $t->stockDestination?->nom,
                'observations' => $t->observations,
            ]));
        }

        $mouvements = $mouvements->sortByDesc('date')->values();

        return response()->json(['data' => $mouvements]);
    }

    public function entree(Request $request)
    {
        $validated = $request->validate([
            'stock_id'       => 'required|exists:stocks,id',
            'produit_id'     => 'required|exists:produits,id',
            'quantite'       => 'required|integer|min:1',
            'prix_unitaire'  => 'nullable|numeric|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
            'date_entree'    => 'required|date',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'numero_facture' => 'nullable|string|max:100',
            'observations'   => 'nullable|string',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $stock = Stock::findOrFail($validated['stock_id']);

        if (!isset($validated['prix_unitaire'])) {
            $validated['prix_unitaire'] = $produit->prix_unitaire;
        }

        DB::transaction(function () use ($validated, $produit, $stock) {
            EntreeStock::create($validated);

            $existing = $stock->produits()->where('produit_id', $produit->id)->first();
            if ($existing) {
                $stock->produits()->updateExistingPivot($produit->id, [
                    'quantite' => $existing->pivot->quantite + $validated['quantite'],
                    'stock_minimum' => $validated['stock_minimum'] ?? 0,
                ]);
            } else {
                $stock->produits()->attach($produit->id, [
                    'quantite' => $validated['quantite'],
                    'stock_minimum' => $validated['stock_minimum'] ?? 0, 
                    'emplacement' => null,
                ]);
            }

            if ($produit->statut !== 'archive') {
                $produit->update(['statut' => 'disponible']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Entrée de {$validated['quantite']} {$produit->unite}(s) enregistrée dans {$stock->nom}",
        ], 201);
    }

    public function sortie(Request $request)
    {
        $validated = $request->validate([
            'stock_id'     => 'required|exists:stocks,id',
            'produit_id'   => 'required|exists:produits,id',
            'chantier_id'  => 'required|exists:chantiers,id',
            'projet_id'    => 'nullable|exists:projets,id',
            'quantite'     => 'required|integer|min:1',
            'date_sortie'  => 'required|date',
            'beneficiaire' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $stock = Stock::findOrFail($validated['stock_id']);
        $pivot = $stock->produits()->where('produit_id', $produit->id)->first();
        $dispo = $pivot?->pivot->quantite ?? 0;

        if ($dispo < $validated['quantite']) {
            return response()->json([
                'success' => false,
                'message' => "Stock insuffisant dans {$stock->nom}. Disponible : {$dispo} {$produit->unite}(s)",
            ], 422);
        }

        DB::transaction(function () use ($validated, $produit, $stock, $dispo) {

            $nouvelleQte = $dispo - $validated['quantite'];
            $stock->produits()->updateExistingPivot($produit->id, ['quantite' => $nouvelleQte]);

            $sortie = SortieStock::create($validated);   // <-- création avant le if

            if (!empty($validated['projet_id'])) {
                $cout = $validated['quantite'] * $produit->prix_unitaire;
                ProjectExpense::create([
                    'projet_id'       => $validated['projet_id'],
                    'sortie_stock_id' => $sortie->id,     // lien direct avec la sortie
                    'nom'             => "Sortie stock : {$produit->nom}",
                    'montant'         => $cout,
                    'date'            => $validated['date_sortie'],
                    'description'     => $validated['observations'] ?? "Sortie de {$validated['quantite']} {$produit->unite}",
                ]);
            }

            $this->refreshStatutProduit($produit);
        });

        return response()->json([
            'success' => true,
            'message' => "Sortie de {
            ['quantite']} {$produit->unite}(s) effectuée depuis {$stock->nom}",
        ], 201);
    }

    // ─── Transfert entre deux dépôts ─────────────────────────────
    public function transfert(Request $request)
    {
        $validated = $request->validate([
            'stock_source_id'      => 'required|exists:stocks,id|different:stock_destination_id',
            'stock_destination_id' => 'required|exists:stocks,id',
            'produit_id'           => 'required|exists:produits,id',
            'quantite'             => 'required|integer|min:1',
            'date_transfert'       => 'required|date',
            'observations'         => 'nullable|string',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $source = Stock::findOrFail($validated['stock_source_id']);
        $destination = Stock::findOrFail($validated['stock_destination_id']);

        $pivotSource = $source->produits()->where('produit_id', $produit->id)->first();
        $dispo = $pivotSource?->pivot->quantite ?? 0;

        if ($dispo < $validated['quantite']) {
            return response()->json([
                'success' => false,
                'message' => "Stock insuffisant dans {$source->nom}. Disponible : {$dispo} {$produit->unite}(s)",
            ], 422);
        }

        DB::transaction(function () use ($validated, $produit, $source, $destination, $dispo) {
            Transfert::create($validated);

            $source->produits()->updateExistingPivot($produit->id, ['quantite' => $dispo - $validated['quantite']]);

            $pivotDest = $destination->produits()->where('produit_id', $produit->id)->first();
            if ($pivotDest) {
                $destination->produits()->updateExistingPivot($produit->id, [
                    'quantite' => $pivotDest->pivot->quantite + $validated['quantite'],
                ]);
            } else {
                $destination->produits()->attach($produit->id, [
                    'quantite' => $validated['quantite'],
                    'stock_minimum' => 0,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Transfert de {$validated['quantite']} {$produit->unite}(s) effectué : {$source->nom} → {$destination->nom}",
        ], 201);
    }

    private function refreshStatutProduit(Produit $produit): void
    {
        $produit->load('stocks');
        $total = $produit->stocks->sum('pivot.quantite');
        if ($produit->statut !== 'archive') {
            $produit->update(['statut' => $total > 0 ? 'disponible' : 'rupture']);
        }
    }

    public function affecterSortieAProjet(Request $request, SortieStock $sortie)
{
    $request->validate(['projet_id' => 'required|exists:projets,id']);

    $projet = Projet::find($request->projet_id);
    $cout = $sortie->quantite * ($sortie->produit->prix_unitaire ?? 0);

    DB::transaction(function () use ($sortie, $projet, $cout) {
        // Supprimer l'ancienne dépense si elle existe
        if ($sortie->projet_id) {
            $oldExpense = ProjectExpense::where('sortie_stock_id', $sortie->id)->first();
            if ($oldExpense) {
                $oldExpense->delete(); // déclenche le recalcul du cout_reel
            }
        }

        $sortie->update(['projet_id' => $projet->id]);

        ProjectExpense::create([
            'projet_id'       => $projet->id,
            'sortie_stock_id' => $sortie->id,
            'nom'             => "Affectation sortie : {$sortie->produit->nom}",
            'montant'         => $cout,
            'date'            => $sortie->date_sortie,
            'description'     => "Sortie affectée au projet {$projet->nom}",
        ]);
    });

    return response()->json(['success' => true, 'message' => 'Sortie affectée au projet.']);
}
public function retourStock(SortieStock $sortie)
{
    DB::transaction(function () use ($sortie) {
        // 1. Remettre la quantité dans le stock
        $stock = $sortie->stock;
        $pivot = $stock->produits()->where('produit_id', $sortie->produit_id)->first();
        if ($pivot) {
            $stock->produits()->updateExistingPivot($sortie->produit_id, [
                'quantite' => $pivot->pivot->quantite + $sortie->quantite,
            ]);
        } else {
            // Cas improbable mais sécurisé
            $stock->produits()->attach($sortie->produit_id, [
                'quantite' => $sortie->quantite,
                'stock_minimum' => 0,
            ]);
        }

        // 2. Si une dépense existe, la supprimer (le modèle ProjectExpense mettra à jour cout_reel)
        if ($sortie->projet_id) {
            $depense = ProjectExpense::where('sortie_stock_id', $sortie->id)->first();
            if ($depense) {
                $depense->delete(); // déclenche l'événement deleted qui recalculera le cout_reel
            }
        }

        // 3. Supprimer la sortie
        $sortie->delete();
    });

    return response()->json(['success' => true, 'message' => 'Sortie annulée, stock restitué.']);
}
}