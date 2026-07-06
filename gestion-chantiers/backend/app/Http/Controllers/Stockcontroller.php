<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Produit;
use Illuminate\Http\Request;

class StockController extends Controller
{
    //  Liste des dépôts 
    public function index(Request $request)
    {
        $query = Stock::query()->withCount('produits');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhere('adresse', 'like', "%$search%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $stocks = $query->latest()->get()->map(fn ($s) => $this->formatStock($s));

        return response()->json(['data' => $stocks]);
    }

    //  Détail d'un dépôt (avec produits et quantités) 
    public function show(Stock $stock)
    {
        $stock->load('produits');

        $produits = $stock->produits->map(fn ($p) => [
            'id'             => $p->id,
            'nom'            => $p->nom,
            'categorie'      => $p->categorie,
            'unite'          => $p->unite,
            'prix_unitaire'  => (float) $p->prix_unitaire,
            'quantite'       => $p->pivot->quantite,
            'stock_minimum'  => $p->pivot->stock_minimum,
            'emplacement'    => $p->pivot->emplacement,
            'valeur'         => (float) ($p->pivot->quantite * $p->prix_unitaire),
            'alerte'         => $p->pivot->quantite <= $p->pivot->stock_minimum,
        ]);

        $data = $this->formatStock($stock);
        $data['produits'] = $produits->values();
        $data['valeur_totale'] = $produits->sum('valeur');

        return response()->json(['data' => $data]);
    }

    //  Créer un dépôt 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:stocks,code',
            'adresse'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:principal,secondaire,chantier',
        ]);

        $stock = Stock::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dépôt créé avec succès',
            'data'    => $this->formatStock($stock),
        ], 201);
    }

    //  Mettre à jour un dépôt 
    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'nom'         => 'sometimes|string|max:255',
            'code'        => 'sometimes|string|max:50|unique:stocks,code,' . $stock->id,
            'adresse'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:principal,secondaire,chantier',
        ]);

        $stock->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dépôt mis à jour',
            'data'    => $this->formatStock($stock),
        ]);
    }

    //  Supprimer un dépôt 
    public function destroy(Stock $stock)
    {
        $stock->delete();
        return response()->json(['success' => true, 'message' => 'Dépôt supprimé']);
    }

    //  Affecter un produit à un dépôt (sans mouvement) 
    public function affecterProduit(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'produit_id'    => 'required|exists:produits,id',
            'stock_minimum' => 'nullable|integer|min:0',
            'emplacement'   => 'nullable|string|max:255',
        ]);

        $stock->produits()->syncWithoutDetaching([
            $validated['produit_id'] => [
                'stock_minimum' => $validated['stock_minimum'] ?? 0,
                'emplacement'   => $validated['emplacement'] ?? null,
            ],
        ]);

        return response()->json(['success' => true, 'message' => 'Produit affecté au dépôt']);
    }

    private function formatStock(Stock $s): array
    {
        return [
            'id'           => $s->id,
            'nom'          => $s->nom,
            'code'         => $s->code,
            'adresse'      => $s->adresse,
            'description'  => $s->description,
            'type'         => $s->type,
            'type_label'   => $s->type_label,
            'nb_produits'  => $s->produits_count ?? $s->produits()->count(),
            'created_at'   => $s->created_at?->format('d/m/Y'),
        ];
    }
    public function updateProduitPivot(Request $request, Stock $stock, Produit $produit)
{
    $validated = $request->validate([
        'stock_minimum' => 'nullable|integer|min:0',
        'emplacement'   => 'nullable|string|max:255',
    ]);

    // Met à jour le pivot (table stock_produit)
    $stock->produits()->updateExistingPivot($produit->id, $validated);

    return response()->json([
        'success' => true,
        'message' => 'Seuil d’alerte mis à jour avec succès.'
    ]);
}

public function getPivot($stockId, $produitId)
{
    $stock = Stock::findOrFail($stockId);
    $pivot = $stock->produits()->where('produit_id', $produitId)->first();

    if (!$pivot) {
        return response()->json(['stock_minimum' => 0], 200);
    }

    return response()->json([
        'stock_minimum' => $pivot->pivot->stock_minimum,
        // you can also return other pivot fields if needed
    ]);
}
}