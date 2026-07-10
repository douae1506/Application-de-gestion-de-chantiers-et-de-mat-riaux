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
        // Compatibilité : si l'appelant envoie encore l'ancien format à plat
        // (produit_id / quantite au niveau racine), on le convertit en une
        // seule ligne pour réutiliser exactement la même logique.
        $payload = $request->all();
        if (empty($payload['lignes']) && $request->filled('produit_id')) {
            $payload['lignes'] = [[
                'produit_id'    => $request->input('produit_id'),
                'quantite'      => $request->input('quantite'),
                'prix_unitaire' => $request->input('prix_unitaire'),
                'stock_minimum' => $request->input('stock_minimum'),
            ]];
        }

        $validated = validator($payload, [
            'stock_id'                     => 'required|exists:stocks,id',
            'fournisseur_id'                => 'nullable|exists:fournisseurs,id',
            'date_entree'                   => 'required|date',
            'numero_facture'                => 'nullable|string|max:100',
            'observations'                  => 'nullable|string',
            'lignes'                        => 'required|array|min:1',
            'lignes.*.produit_id'           => 'required|exists:produits,id',
            'lignes.*.quantite'             => 'required|integer|min:1',
            'lignes.*.prix_unitaire'        => 'nullable|numeric|min:0',
            'lignes.*.stock_minimum'        => 'nullable|integer|min:0',
        ])->validate();

        $stock = Stock::findOrFail($validated['stock_id']);
        $recap = [];

        DB::transaction(function () use ($validated, $stock, &$recap) {
            foreach ($validated['lignes'] as $ligne) {
                $produit = Produit::findOrFail($ligne['produit_id']);
                $prixUnitaire = $ligne['prix_unitaire'] ?? $produit->prix_unitaire;
                $stockMinimum = $ligne['stock_minimum'] ?? 0;

                EntreeStock::create([
                    'stock_id'       => $stock->id,
                    'produit_id'     => $produit->id,
                    'quantite'       => $ligne['quantite'],
                    'prix_unitaire'  => $prixUnitaire,
                    'stock_minimum'  => $stockMinimum,
                    'date_entree'    => $validated['date_entree'],
                    'fournisseur_id' => $validated['fournisseur_id'] ?? null,
                    'numero_facture' => $validated['numero_facture'] ?? null,
                    'observations'   => $validated['observations'] ?? null,
                ]);

                $existing = $stock->produits()->where('produit_id', $produit->id)->first();
                if ($existing) {
                    $stock->produits()->updateExistingPivot($produit->id, [
                        'quantite' => $existing->pivot->quantite + $ligne['quantite'],
                        'stock_minimum' => $stockMinimum,
                    ]);
                } else {
                    $stock->produits()->attach($produit->id, [
                        'quantite' => $ligne['quantite'],
                        'stock_minimum' => $stockMinimum,
                        'emplacement' => null,
                    ]);
                }

                if ($produit->statut !== 'archive') {
                    $produit->update(['statut' => 'disponible']);
                }

                $recap[] = "{$ligne['quantite']} {$produit->unite}(s) de {$produit->nom}";
            }
        });

        $nbLignes = count($validated['lignes']);
        $message = $nbLignes > 1
            ? "Entrée de {$nbLignes} produits enregistrée dans {$stock->nom} : " . implode(', ', $recap)
            : "Entrée de " . $recap[0] . " enregistrée dans {$stock->nom}";

        return response()->json([
            'success' => true,
            'message' => $message,
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
            'message' => "Sortie de {$validated['quantite']} {$produit->unite}(s) effectuée depuis {$stock->nom}",
        ], 201);
    }

    // ─── Transfert entre deux dépôts ─────────────────────────────
    public function transfert(Request $request)
    {
        // Compatibilité avec l'ancien format à plat (un seul produit)
        $payload = $request->all();
        if (empty($payload['lignes']) && $request->filled('produit_id')) {
            $payload['lignes'] = [[
                'produit_id' => $request->input('produit_id'),
                'quantite'   => $request->input('quantite'),
            ]];
        }

        $validated = validator($payload, [
            'stock_source_id'      => 'required|exists:stocks,id|different:stock_destination_id',
            'stock_destination_id' => 'required|exists:stocks,id',
            'date_transfert'       => 'required|date',
            'observations'         => 'nullable|string',
            'lignes'               => 'required|array|min:1',
            'lignes.*.produit_id'  => 'required|exists:produits,id',
            'lignes.*.quantite'    => 'required|integer|min:1',
        ])->validate();

        $source = Stock::findOrFail($validated['stock_source_id']);
        $destination = Stock::findOrFail($validated['stock_destination_id']);

        // On vérifie la disponibilité de chaque ligne avant toute écriture
        $produitsParLigne = [];
        foreach ($validated['lignes'] as $ligne) {
            $produit = Produit::findOrFail($ligne['produit_id']);
            $pivotSource = $source->produits()->where('produit_id', $produit->id)->first();
            $dispo = $pivotSource?->pivot->quantite ?? 0;

            if ($dispo < $ligne['quantite']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuffisant pour {$produit->nom} dans {$source->nom}. Disponible : {$dispo} {$produit->unite}(s)",
                ], 422);
            }
            $produitsParLigne[] = ['produit' => $produit, 'quantite' => $ligne['quantite'], 'dispo' => $dispo];
        }

        $recap = [];

        DB::transaction(function () use ($validated, $source, $destination, $produitsParLigne, &$recap) {
            foreach ($produitsParLigne as $item) {
                $produit = $item['produit'];

                Transfert::create([
                    'stock_source_id'      => $source->id,
                    'stock_destination_id' => $destination->id,
                    'produit_id'           => $produit->id,
                    'quantite'             => $item['quantite'],
                    'date_transfert'       => $validated['date_transfert'],
                    'observations'         => $validated['observations'] ?? null,
                ]);

                $source->produits()->updateExistingPivot($produit->id, ['quantite' => $item['dispo'] - $item['quantite']]);

                $pivotDest = $destination->produits()->where('produit_id', $produit->id)->first();
                if ($pivotDest) {
                    $destination->produits()->updateExistingPivot($produit->id, [
                        'quantite' => $pivotDest->pivot->quantite + $item['quantite'],
                    ]);
                } else {
                    $destination->produits()->attach($produit->id, [
                        'quantite' => $item['quantite'],
                        'stock_minimum' => 0,
                    ]);
                }

                $recap[] = "{$item['quantite']} {$produit->unite}(s) de {$produit->nom}";
            }
        });

        $nbLignes = count($produitsParLigne);
        $message = $nbLignes > 1
            ? "Transfert de {$nbLignes} produits effectué : {$source->nom} → {$destination->nom} (" . implode(', ', $recap) . ")"
            : "Transfert de " . $recap[0] . " effectué : {$source->nom} → {$destination->nom}";

        return response()->json([
            'success' => true,
            'message' => $message,
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
    $request->validate([
        'projet_id' => 'required|exists:projets,id',
        'quantite'  => 'nullable|integer|min:1|max:' . $sortie->quantite,
    ]);

    $projet = Projet::find($request->projet_id);
    $prixUnitaire = $sortie->produit->prix_unitaire ?? 0;
    $quantiteAffectee = $request->filled('quantite') ? (int) $request->quantite : $sortie->quantite;

    DB::transaction(function () use ($sortie, $projet, $prixUnitaire, $quantiteAffectee) {
        // Supprimer l'ancienne dépense si elle existe (ré-affectation)
        if ($sortie->projet_id) {
            $oldExpense = ProjectExpense::where('sortie_stock_id', $sortie->id)->first();
            if ($oldExpense) {
                $oldExpense->delete(); // déclenche le recalcul du cout_reel
            }
        }

        if ($quantiteAffectee < $sortie->quantite) {
            // Affectation partielle : on scinde la sortie en deux écritures.
            // La ligne d'origine garde le reliquat non affecté, une nouvelle
            // ligne est créée pour la quantité affectée au projet.
            $sortieAffectee = SortieStock::create([
                'stock_id'     => $sortie->stock_id,
                'produit_id'   => $sortie->produit_id,
                'chantier_id'  => $sortie->chantier_id,
                'projet_id'    => $projet->id,
                'quantite'     => $quantiteAffectee,
                'date_sortie'  => $sortie->date_sortie,
                'beneficiaire' => $sortie->beneficiaire,
                'observations' => $sortie->observations,
            ]);

            $sortie->update(['quantite' => $sortie->quantite - $quantiteAffectee]);
        } else {
            $sortieAffectee = $sortie;
            $sortie->update(['projet_id' => $projet->id]);
        }

        $cout = $quantiteAffectee * $prixUnitaire;

        ProjectExpense::create([
            'projet_id'       => $projet->id,
            'sortie_stock_id' => $sortieAffectee->id,
            'nom'             => "Affectation sortie : {$sortie->produit->nom}",
            'montant'         => $cout,
            'date'            => $sortie->date_sortie,
            'description'     => "Sortie affectée au projet {$projet->nom}",
        ]);
    });

    return response()->json(['success' => true, 'message' => 'Sortie affectée au projet.']);
}

// ─── Annulation groupée (retour au stock de plusieurs sorties) ──
public function retourStockBulk(Request $request)
{
    $request->validate([
        'sortie_ids'   => 'required|array|min:1',
        'sortie_ids.*' => 'integer|exists:sortie_stocks,id',
    ]);

    $traitees = 0;
    $erreurs = [];

    foreach ($request->sortie_ids as $sortieId) {
        $sortie = SortieStock::find($sortieId);
        if (!$sortie) continue;

        if ($sortie->projet_id) {
            $erreurs[] = "{$sortie->produit?->nom} est déjà affecté à un projet et ne peut pas être retourné directement.";
            continue;
        }

        DB::transaction(function () use ($sortie) {
            $stock = $sortie->stock;
            $pivot = $stock->produits()->where('produit_id', $sortie->produit_id)->first();
            if ($pivot) {
                $stock->produits()->updateExistingPivot($sortie->produit_id, [
                    'quantite' => $pivot->pivot->quantite + $sortie->quantite,
                ]);
            } else {
                $stock->produits()->attach($sortie->produit_id, [
                    'quantite' => $sortie->quantite,
                    'stock_minimum' => 0,
                ]);
            }
            $sortie->delete();
        });

        $traitees++;
    }

    return response()->json([
        'success'  => count($erreurs) === 0,
        'message'  => $traitees > 1
            ? "{$traitees} produits retournés au stock."
            : ($traitees === 1 ? 'Produit retourné au stock.' : 'Aucun produit retourné.'),
        'erreurs'  => $erreurs,
    ]);
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