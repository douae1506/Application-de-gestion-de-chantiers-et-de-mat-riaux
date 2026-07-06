<?php

namespace App\Http\Controllers;

use App\Models\EntreeStock;
use App\Models\Produit;
use App\Models\SortieStock;
use App\Models\Stock;
use App\Models\Transfert;
use App\Models\Chantier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    // ─── Rapport stock global ────────────────────────────────
    public function stock(Request $request)
    {
        $produits = Produit::with('stocks')->where('statut', '!=', 'archive')->get();

        $parDepot = Stock::withCount('produits')->get()->map(fn ($s) => [
            'id'          => $s->id,
            'nom'         => $s->nom,
            'code'        => $s->code,
            'type'        => $s->type_label,
            'nb_produits' => $s->produits_count,
            'valeur'      => (float) $s->produits->sum(fn ($p) => $p->pivot->quantite * $p->prix_unitaire),
        ]);

        $alertes = $produits->flatMap(function ($p) {
            return $p->stocks->filter(fn ($s) => $s->pivot->quantite <= $s->pivot->stock_minimum)->map(fn ($s) => [
                'produit'       => $p->nom,
                'depot'         => $s->nom,
                'quantite'      => $s->pivot->quantite,
                'stock_minimum' => $s->pivot->stock_minimum,
                'statut'        => $s->pivot->quantite === 0 ? 'rupture' : 'bas',
            ]);
        })->values();

        $parCategorie = $produits->groupBy('categorie')->map(fn ($items, $cat) => [
            'categorie'   => $cat,
            'nb_produits' => $items->count(),
            'valeur'      => (float) $items->sum(fn ($p) => $p->stock_total * $p->prix_unitaire),
            'stock_total' => (int) $items->sum(fn ($p) => $p->stock_total),
        ])->values();

        return response()->json([
            'data' => [
                'resume' => [
                    'total_produits'     => $produits->count(),
                    'produits_disponibles'=> $produits->where('statut', 'disponible')->count(),
                    'produits_rupture'   => $produits->where('statut', 'rupture')->count(),
                    'valeur_totale'      => (float) $produits->sum(fn ($p) => $p->stock_total * $p->prix_unitaire),
                    'nb_alertes'         => $alertes->count(),
                    'nb_depots'          => Stock::count(),
                ],
                'par_depot'     => $parDepot,
                'par_categorie' => $parCategorie,
                'alertes'       => $alertes,
            ],
        ]);
    }

    // ─── Rapport mouvements ─────────────────────────────────
    public function mouvements(Request $request)
    {
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
        $dateFin   = $request->get('date_fin', now()->toDateString());
        $stockId   = $request->get('stock_id');

        $entrees = EntreeStock::with(['produit', 'stock', 'fournisseur'])
            ->whereDate('date_entree', '>=', $dateDebut)
            ->whereDate('date_entree', '<=', $dateFin)
            ->when($stockId, fn ($q) => $q->where('stock_id', $stockId))
            ->get();

        $sorties = SortieStock::with(['produit', 'stock', 'chantier'])
            ->whereDate('date_sortie', '>=', $dateDebut)
            ->whereDate('date_sortie', '<=', $dateFin)
            ->when($stockId, fn ($q) => $q->where('stock_id', $stockId))
            ->get();

        $transferts = Transfert::with(['produit', 'stockSource', 'stockDestination'])
            ->whereDate('date_transfert', '>=', $dateDebut)
            ->whereDate('date_transfert', '<=', $dateFin)
            ->when($stockId, fn ($q) => $q->where(fn ($w) => $w->where('stock_source_id', $stockId)->orWhere('stock_destination_id', $stockId)))
            ->get();

        $valeurEntrees = $entrees->sum(fn ($e) => $e->quantite * $e->prix_unitaire);
        $valeurSorties = $sorties->sum(fn ($s) => $s->quantite * ($s->produit?->prix_unitaire ?? 0));

        $parJour = collect();
        for ($d = strtotime($dateDebut); $d <= strtotime($dateFin); $d += 86400) {
            $jour = date('Y-m-d', $d);
            $parJour->push([
                'date'    => $jour,
                'entrees' => $entrees->filter(fn ($e) => $e->date_entree->toDateString() === $jour)->sum('quantite'),
                'sorties' => $sorties->filter(fn ($s) => $s->date_sortie->toDateString() === $jour)->sum('quantite'),
            ]);
        }

        return response()->json([
            'data' => [
                'resume' => [
                    'total_entrees'    => $entrees->sum('quantite'),
                    'total_sorties'    => $sorties->sum('quantite'),
                    'total_transferts' => $transferts->sum('quantite'),
                    'valeur_entrees'   => (float) $valeurEntrees,
                    'valeur_sorties'   => (float) $valeurSorties,
                    'nb_operations'    => $entrees->count() + $sorties->count() + $transferts->count(),
                ],
                'par_jour' => $parJour->values(),
                'entrees'  => $entrees->map(fn ($e) => [
                    'date'        => $e->date_entree?->format('d/m/Y'),
                    'produit'     => $e->produit?->nom,
                    'depot'       => $e->stock?->nom,
                    'quantite'    => $e->quantite,
                    'unite'       => $e->produit?->unite,
                    'valeur'      => (float) ($e->quantite * $e->prix_unitaire),
                    'fournisseur' => $e->fournisseur?->nom,
                ])->values(),
                'sorties' => $sorties->map(fn ($s) => [
                    'date'      => $s->date_sortie?->format('d/m/Y'),
                    'produit'   => $s->produit?->nom,
                    'depot'     => $s->stock?->nom,
                    'quantite'  => $s->quantite,
                    'unite'     => $s->produit?->unite,
                    'valeur'    => (float) ($s->quantite * ($s->produit?->prix_unitaire ?? 0)),
                    'chantier'  => $s->chantier?->nom,
                ])->values(),
            ],
        ]);
    }

    // ─── Rapport chantiers (coûts matériaux) ────────────────
    public function chantiers(Request $request)
    {
        $chantiers = Chantier::with(['projets', 'sortieStocks.produit'])->get();

        $data = $chantiers->map(function ($c) {
            $coutMat = (float) $c->sortieStocks->sum(fn ($s) => $s->quantite * ($s->produit?->prix_unitaire ?? 0));
            return [
                'id'            => $c->id,
                'reference'     => $c->reference,
                'nom'           => $c->nom,
                'statut'        => $c->statut_label,
                'budget_total'  => (float) $c->budget_total,
                'cout_reel'     => (float) $c->cout_reel,
                'cout_materiaux'=> $coutMat,
                'progression'   => $c->progression,
                'budget_restant'=> (float) max(0, $c->budget_total - $c->cout_reel),
                'pct_depense'   => $c->pourcentage_depense,
                'nb_sorties'    => $c->sortieStocks->count(),
            ];
        });

        return response()->json([
            'data' => [
                'resume' => [
                    'nb_chantiers'        => $chantiers->count(),
                    'budget_total'        => (float) $chantiers->sum('budget_total'),
                    'cout_reel_total'     => (float) $chantiers->sum('cout_reel'),
                    'cout_materiaux_total'=> (float) $data->sum('cout_materiaux'),
                ],
                'chantiers' => $data->values(),
            ],
        ]);
    }
}