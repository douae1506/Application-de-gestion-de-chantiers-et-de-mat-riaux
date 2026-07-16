<?php

namespace App\Http\Controllers;

use App\Models\EntreeStock;
use App\Models\SortieStock;
use App\Models\Stock;
use App\Models\Transfert;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    private function author(Request $request): string
    {
        return $request->user()?->nom_complet ?? '—';
    }

    public function bonEntree(Request $request, EntreeStock $entree)
    {
        $entree->load(['produit', 'stock', 'fournisseur']);

        $pdf = Pdf::loadView('pdf.bon-entree', [
            'entree' => $entree,
            'title' => "Bon d'entrée",
            'subtitle' => null,
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('bon-entree-' . $entree->id . '.pdf');
    }

    public function bonSortie(Request $request, SortieStock $sortie)
    {
        $sortie->load(['produit', 'stock', 'chantier']);

        $pdf = Pdf::loadView('pdf.bon-sortie', [
            'sortie' => $sortie,
            'title' => 'Bon de sortie',
            'subtitle' => null,
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('bon-sortie-' . $sortie->id . '.pdf');
    }

    public function bonTransfert(Request $request, Transfert $transfert)
    {
        $transfert->load(['produit', 'stockSource', 'stockDestination']);

        $pdf = Pdf::loadView('pdf.bon-transfert', [
            'transfert' => $transfert,
            'title' => 'Bon de transfert',
            'subtitle' => null,
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('bon-transfert-' . $transfert->id . '.pdf');
    }

    /**
     * Impression personnalisée de la liste de stock : le frontend envoie
     * les colonnes à afficher (columns[]) et, en option, un sous-ensemble
     * de dépôts (stock_ids[]) et/ou produits (produit_ids[]).
     */
    public function stockPersonnalise(Request $request)
    {
        $columns = $request->input('columns', ['produit', 'depot', 'quantite', 'seuil', 'etat']);
        $stockIds = $request->input('stock_ids', []);
        $produitIds = $request->input('produit_ids', []);

        $query = Stock::with('produits');
        if (!empty($stockIds)) $query->whereIn('id', $stockIds);
        $stocks = $query->orderBy('nom')->get();

        $lignes = [];
        foreach ($stocks as $stock) {
            foreach ($stock->produits as $produit) {
                if (!empty($produitIds) && !in_array($produit->id, $produitIds)) continue;
                $lignes[] = [
                    'produit' => $produit->nom,
                    'categorie' => $produit->categorie,
                    'depot' => $stock->nom,
                    'emplacement' => $produit->pivot->emplacement,
                    'quantite' => $produit->pivot->quantite,
                    'unite' => $produit->unite,
                    'seuil' => $produit->pivot->stock_minimum,
                    'prix' => (float) $produit->prix_unitaire,
                    'valeur' => (float) $produit->prix_unitaire * (float) $produit->pivot->quantite,
                ];
            }
        }

        $pdf = Pdf::loadView('pdf.stock-personnalise', [
            'lignes' => $lignes,
            'columns' => $columns,
            'title' => 'Liste de stock personnalisée',
            'subtitle' => count($lignes) . ' ligne(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('stock-personnalise-' . now()->format('Y-m-d_His') . '.pdf');
    }
}
