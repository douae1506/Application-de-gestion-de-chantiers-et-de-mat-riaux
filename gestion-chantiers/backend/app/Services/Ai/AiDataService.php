<?php

namespace App\Services\Ai;

use App\Models\Chantier;
use App\Models\EntreeStock;
use App\Models\Produit;
use App\Models\Projet;
use App\Models\SortieStock;
use App\Models\Stock;
use App\Models\Transfert;


class AiDataService
{
    public function buildSnapshot(): array
    {
        return [
            'date_du_jour' => now()->toDateString(),
            'produits' => $this->produitsSnapshot(),
            'chantiers' => $this->chantiersSnapshot(),
            'fournisseur_le_plus_utilise' => $this->fournisseurLePlusUtilise(),
            'cinq_derniers_mouvements' => $this->derniersMouvements(5),
            'stocks' => $this->stocksSnapshot(),
        ];
    }

    private function produitsSnapshot(): array
    {
        $produits = Produit::with('stocks')->get();

        $enRupture = $produits
            ->filter(fn (Produit $p) => $p->stock_total <= 0)
            ->map(fn (Produit $p) => ['nom' => $p->nom, 'categorie' => $p->categorie])
            ->values();

        $sousSeuil = $produits
            ->filter(fn (Produit $p) => $p->stock_total > 0 && $p->alerte_stock)
            ->map(fn (Produit $p) => ['nom' => $p->nom, 'stock_total' => $p->stock_total])
            ->values();

        $produitIdsAvecSortie = SortieStock::query()->distinct()->pluck('produit_id');
        $jamaisUtilises = $produits
            ->whereNotIn('id', $produitIdsAvecSortie)
            ->pluck('nom')
            ->values();

        return [
            'nombre_total' => $produits->count(),
            'en_rupture_de_stock' => $enRupture,
            'sous_seuil_minimum' => $sousSeuil,
            'jamais_utilises' => $jamaisUtilises,
            'plus_consommes' => $this->produitsPlusConsommes(),
        ];
    }

    private function produitsPlusConsommes(int $limit = 5): array
    {
        return SortieStock::selectRaw('produit_id, SUM(quantite) as total')
            ->groupBy('produit_id')
            ->orderByDesc('total')
            ->with('produit:id,nom')
            ->limit($limit)
            ->get()
            ->filter(fn ($s) => $s->produit !== null)
            ->map(fn ($s) => ['produit' => $s->produit->nom, 'quantite_sortie' => (float) $s->total])
            ->values()
            ->toArray();
    }

    private function chantiersSnapshot(): array
    {
        $chantiers = Chantier::with('client')->get();

        $enRetard = $chantiers
            ->filter(fn (Chantier $c) => $c->date_fin_prevue
                && $c->date_fin_prevue->isPast()
                && !in_array($c->statut, ['termine', 'annule'], true))
            ->map(fn (Chantier $c) => [
                'nom' => $c->nom,
                'date_fin_prevue' => $c->date_fin_prevue->toDateString(),
                'statut' => $c->statut_label,
                'progression' => $c->progression,
            ])
            ->values();

        $enCours = $chantiers
            ->where('statut', 'en_cours')
            ->map(fn (Chantier $c) => ['nom' => $c->nom, 'progression' => $c->progression])
            ->values();

        return [
            'nombre_total' => $chantiers->count(),
            'en_retard' => $enRetard,
            'en_cours' => $enCours,
            'chantier_plus_gros_consommateur_de_materiaux' => $this->chantierPlusConsommateur(),
            'projets_termines' => Projet::where('statut', 'termine')->count(),
            'projets_total' => Projet::count(),
        ];
    }

    private function chantierPlusConsommateur(): ?array
    {
        $top = SortieStock::selectRaw('chantier_id, SUM(quantite) as total_quantite')
            ->whereNotNull('chantier_id')
            ->groupBy('chantier_id')
            ->orderByDesc('total_quantite')
            ->with('chantier:id,nom')
            ->first();

        if (!$top || !$top->chantier) {
            return null;
        }

        return ['nom' => $top->chantier->nom, 'quantite_totale_sortie' => (float) $top->total_quantite];
    }

    private function fournisseurLePlusUtilise(): ?array
    {
        $top = EntreeStock::selectRaw('fournisseur_id, COUNT(*) as nb_entrees, SUM(quantite) as total_quantite')
            ->whereNotNull('fournisseur_id')
            ->groupBy('fournisseur_id')
            ->orderByDesc('nb_entrees')
            ->with('fournisseur:id,nom')
            ->first();

        if (!$top || !$top->fournisseur) {
            return null;
        }

        return [
            'nom' => $top->fournisseur->nom,
            'nombre_entrees' => (int) $top->nb_entrees,
            'quantite_totale' => (float) $top->total_quantite,
        ];
    }

    private function derniersMouvements(int $limit): array
    {
        $entrees = EntreeStock::with(['produit:id,nom', 'stock:id,nom'])
            ->latest('date_entree')->limit($limit)->get()
            ->map(fn ($e) => [
                'type' => 'Entrée',
                'produit' => $e->produit->nom ?? '—',
                'quantite' => (float) $e->quantite,
                'depot' => $e->stock->nom ?? '—',
                'date' => optional($e->date_entree)->toDateString(),
            ]);

        $sorties = SortieStock::with(['produit:id,nom', 'stock:id,nom', 'chantier:id,nom'])
            ->latest('date_sortie')->limit($limit)->get()
            ->map(fn ($s) => [
                'type' => 'Sortie',
                'produit' => $s->produit->nom ?? '—',
                'quantite' => (float) $s->quantite,
                'depot' => $s->stock->nom ?? '—',
                'chantier' => $s->chantier->nom ?? null,
                'date' => optional($s->date_sortie)->toDateString(),
            ]);

        $transferts = Transfert::with(['produit:id,nom', 'stockSource:id,nom', 'stockDestination:id,nom'])
            ->latest('date_transfert')->limit($limit)->get()
            ->map(fn ($t) => [
                'type' => 'Transfert',
                'produit' => $t->produit->nom ?? '—',
                'quantite' => (float) $t->quantite,
                'de' => $t->stockSource->nom ?? '—',
                'vers' => $t->stockDestination->nom ?? '—',
                'date' => optional($t->date_transfert)->toDateString(),
            ]);

        return $entrees->concat($sorties)->concat($transferts)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function stocksSnapshot(): array
    {
        $stocks = Stock::withCount('produits')->orderByDesc('produits_count')->get();

        return [
            'depot_avec_le_plus_de_produits' => $stocks->first()
                ? ['nom' => $stocks->first()->nom, 'nombre_produits' => $stocks->first()->produits_count]
                : null,
            'classement' => $stocks
                ->map(fn ($s) => ['nom' => $s->nom, 'nombre_produits' => $s->produits_count])
                ->values(),
        ];
    }
}
