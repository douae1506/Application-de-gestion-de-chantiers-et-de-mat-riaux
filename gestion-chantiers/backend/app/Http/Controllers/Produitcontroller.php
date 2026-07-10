<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    // ─── Liste du catalogue produits ───────────────────────────
    public function index(Request $request)
    {
        // ⭐ Charger les fournisseurs en plus des stocks
        $query = Produit::query()->with(['stocks', 'fournisseurs']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('categorie', 'like', "%$search%");
            });
        }

        if ($categorie = $request->get('categorie')) {
            $query->where('categorie', $categorie);
        }

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        $produits = $query->latest()->get()->map(fn ($p) => $this->formatProduit($p));

        return response()->json(['data' => $produits]);
    }

    // ─── Détail d'un produit (catalogue + répartition stock) ──
    public function show(Produit $produit)
    {
        // ⭐ Ajout de 'fournisseurs' dans le load
        $produit->load(['stocks', 'fournisseurs', 'entreeStocks.fournisseur', 'entreeStocks.stock', 'sortieStocks.chantier', 'sortieStocks.stock']);
        return response()->json(['data' => $this->formatProduitDetail($produit)]);
    }

    // ─── Créer un produit (catalogue uniquement) ───────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'           => 'required|string|max:255',
            'description'   => 'nullable|string',
            'categorie'     => 'required|string|max:255',
            'unite'         => 'required|string|max:50',
            'prix_unitaire' => 'required|numeric|min:0',
            'fournisseurs'  => 'nullable|array',
            'fournisseurs.*' => 'exists:fournisseurs,id',
        ]);

        $validated['statut'] = 'rupture';

        $produit = Produit::create($validated);

        // Association des fournisseurs (s'il y en a)
        if (!empty($validated['fournisseurs'])) {
            $produit->fournisseurs()->attach($validated['fournisseurs']);
        }

        // ⭐ Recharger les relations pour les inclure dans la réponse
        $produit->load(['stocks', 'fournisseurs']);

        return response()->json([
            'success' => true,
            'message' => 'Produit créé avec succès. Utilisez une entrée de stock pour l\'approvisionner dans un dépôt.',
            'data'    => $this->formatProduit($produit),
        ], 201);
    }

    // ─── Mettre à jour un produit ───────────────────────────────
    public function update(Request $request, Produit $produit)
    {
        $validated = $request->validate([
            'nom'           => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'categorie'     => 'sometimes|string|max:255',
            'unite'         => 'sometimes|string|max:50',
            'prix_unitaire' => 'sometimes|numeric|min:0',
            'statut'        => 'sometimes|in:disponible,rupture,archive',
            'fournisseurs'  => 'nullable|array',
            'fournisseurs.*' => 'exists:fournisseurs,id',
        ]);

        $produit->update($validated);

        // Mise à jour des fournisseurs (sync)
        if (array_key_exists('fournisseurs', $validated)) {
            $produit->fournisseurs()->sync($validated['fournisseurs']);
        }

        // ⭐ Recharger les relations
        $produit->load(['stocks', 'fournisseurs']);

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour',
            'data'    => $this->formatProduit($produit),
        ]);
    }

    // ─── Supprimer un produit ────────────────────────────────────
    public function destroy(Produit $produit)
    {
        $produit->delete();
        return response()->json(['success' => true, 'message' => 'Produit supprimé']);
    }

    // ─── Catégories disponibles ───────────────────────────────
    public function categories()
    {
        $cats = Produit::distinct()->pluck('categorie')->filter()->values();
        return response()->json(['data' => $cats]);
    }

    // ─── Formatage liste ──────────────────────────────────────
    private function formatProduit(Produit $p): array
    {
        $stockTotal = (int) $p->stocks->sum('pivot.quantite');
        $alerte = $p->stocks->contains(fn ($s) => $s->pivot->quantite <= $s->pivot->stock_minimum);
        $statut = $p->statut === 'archive' ? 'archive' : ($stockTotal > 0 ? 'disponible' : 'rupture');

        return [
            'id'            => $p->id,
            'nom'           => $p->nom,
            'description'   => $p->description,
            'categorie'     => $p->categorie,
            'unite'         => $p->unite,
            'prix_unitaire' => (float) $p->prix_unitaire,
            'stock_total'   => $stockTotal,
            'nb_depots'     => $p->stocks->count(),
            'statut'        => $statut,
            'valeur_stock'  => (float) ($stockTotal * $p->prix_unitaire),
            'alerte_stock'  => $alerte,
            'created_at'    => $p->created_at?->format('d/m/Y'),
            'fournisseurs'  => $p->fournisseurs->map(fn ($f) => [
                'id'        => $f->id,
                'nom'       => $f->nom,
                'telephone' => $f->telephone,
                'email'     => $f->email,
            ])->values()->toArray(),
            'depots'        => $p->stocks->map(fn ($s) => [
                'id'       => $s->id,
                'quantite' => $s->pivot->quantite,
                'stock_minimum' => $s->pivot->stock_minimum,
            ])->values()->toArray(),
        ];
    }

    // ─── Formatage détail ─────────────────────────────────────
    private function formatProduitDetail(Produit $p): array
    {
        $base = $this->formatProduit($p);

        $base['depots'] = $p->stocks->map(fn ($s) => [
            'id'            => $s->id,
            'nom'           => $s->nom,
            'code'          => $s->code,
            'quantite'      => $s->pivot->quantite,
            'stock_minimum' => $s->pivot->stock_minimum,
            'emplacement'   => $s->pivot->emplacement,
        ])->values();

        $base['entrees'] = $p->entreeStocks->sortByDesc('date_entree')->map(fn ($e) => [
            'id'             => $e->id,
            'quantite'       => $e->quantite,
            'prix_unitaire'  => (float) $e->prix_unitaire,
            'date_entree'    => $e->date_entree?->format('d/m/Y'),
            'numero_facture' => $e->numero_facture,
            'observations'   => $e->observations,
            'fournisseur'    => $e->fournisseur?->nom,
            'depot'          => $e->stock?->nom,
        ])->values();

        $base['sorties'] = $p->sortieStocks->sortByDesc('date_sortie')->map(fn ($s) => [
            'id'           => $s->id,
            'quantite'     => $s->quantite,
            'date_sortie'  => $s->date_sortie?->format('d/m/Y'),
            'beneficiaire' => $s->beneficiaire,
            'observations' => $s->observations,
            'chantier'     => $s->chantier?->nom,
            'chantier_id'  => $s->chantier_id,
            'depot'        => $s->stock?->nom,
        ])->values();

        $base['total_entrees'] = $p->entreeStocks->sum('quantite');
        $base['total_sorties'] = $p->sortieStocks->sum('quantite');

        return $base;
    }
}