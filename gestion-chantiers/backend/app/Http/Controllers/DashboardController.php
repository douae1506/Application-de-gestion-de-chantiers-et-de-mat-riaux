<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Projet;
use App\Models\Event;
use App\Models\Activity;
use App\Models\User;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\Fournisseur;
use App\Models\EntreeStock;
use App\Models\SortieStock;
use App\Models\Transfert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            $role = $user?->role ?? 'admin';

            $rangeStart = $request->query('start');
            $rangeEnd = $request->query('end');

            if ($rangeStart && $rangeEnd) {
                $periodStart = \Carbon\Carbon::parse($rangeStart)->startOfDay();
                $periodEnd = \Carbon\Carbon::parse($rangeEnd)->endOfDay();
            } else {
                $periodStart = now()->subMonth();
                $periodEnd = now();
            }

            if ($role === 'magasinier') {
                return response()->json($this->statsMagasinier($periodStart, $periodEnd));
            }

            return response()->json($this->statsChantiersProjets($user, $role, $periodStart, $periodEnd));

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Erreur interne du serveur',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Requête Chantier de base, cloisonnée selon le rôle :
     * - admin : tous les chantiers
     * - responsable : uniquement les chantiers dont il est responsable
     * - chef_projet : uniquement les chantiers contenant au moins un de ses projets
     */
    private function scopedChantierQuery(?User $user, string $role)
    {
        $query = Chantier::query();
        if ($role === 'responsable' && $user) {
            $query->where('responsable_id', $user->id);
        } elseif ($role === 'chef_projet' && $user) {
            $query->whereHas('projets', fn($q) => $q->where('responsable_id', $user->id));
        }
        return $query;
    }

    /**
     * Requête Projet de base, cloisonnée selon le rôle :
     * - admin : tous les projets
     * - responsable : uniquement les projets des chantiers dont il est responsable
     * - chef_projet : uniquement les projets dont il est lui-même responsable
     */
    private function scopedProjetQuery(?User $user, string $role)
    {
        $query = Projet::query();
        if ($role === 'responsable' && $user) {
            $query->whereHas('chantier', fn($q) => $q->where('responsable_id', $user->id));
        } elseif ($role === 'chef_projet' && $user) {
            $query->where('responsable_id', $user->id);
        }
        return $query;
    }

    // ═══════════════════════════════════════════════════════════
    // DASHBOARD "CHANTIERS / PROJETS" (admin, responsable, chef_projet)
    // ═══════════════════════════════════════════════════════════
    private function statsChantiersProjets(?User $user, string $role, $periodStart, $periodEnd): array
    {
        $chantierQuery = fn() => $this->scopedChantierQuery($user, $role);
        $projetQuery = fn() => $this->scopedProjetQuery($user, $role);
        $chantierIds = $chantierQuery()->pluck('id');

        // ─── KPI ───────────────────────────────────────────────
        $totalChantiers = $chantierQuery()->count();
        $newChantiers = $chantierQuery()->whereBetween('created_at', [$periodStart, $periodEnd])->count();

        $totalProjets = $projetQuery()->count();
        $newProjets = $projetQuery()->whereBetween('created_at', [$periodStart, $periodEnd])->count();

        $budgetTotal = $chantierQuery()->sum('budget_total') ?: 0;
        $depensesTotal = $chantierQuery()->sum('cout_reel') ?: 0;

        $lastMonthBudget = $chantierQuery()->where('created_at', '<', $periodStart)->sum('budget_total') ?: 0;
        $budgetEvolution = $lastMonthBudget ? round((($budgetTotal - $lastMonthBudget) / $lastMonthBudget) * 100) : 0;

        $lastMonthDepenses = $chantierQuery()->where('created_at', '<', $periodStart)->sum('cout_reel') ?: 0;
        $depensesEvolution = $lastMonthDepenses ? round((($depensesTotal - $lastMonthDepenses) / $lastMonthDepenses) * 100) : 0;

        // Le total "employés" ne concerne que le périmètre admin (vue globale RH)
        if ($role === 'admin') {
            $totalEmployes = User::count();
            $newEmployes = User::whereBetween('created_at', [$periodStart, $periodEnd])->count();
        } else {
            $totalEmployes = null;
            $newEmployes = null;
        }

        $kpis = [
            'chantiers' => ['total' => $totalChantiers, 'new' => $newChantiers],
            'projets' => ['total' => $totalProjets, 'new' => $newProjets],
            'budget' => ['total' => $budgetTotal, 'evolution' => $budgetEvolution],
            'depenses' => ['total' => $depensesTotal, 'evolution' => $depensesEvolution],
            'employes' => ['total' => $totalEmployes, 'new' => $newEmployes],
        ];

        // ─── Répartition des chantiers par statut ──────────────
        $statutRepartition = $chantierQuery()
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        // ─── Avancement global des projets ─────────────────────
        $progressionGlobale = round($projetQuery()->avg('progression') ?: 0);
        $projetsEnCours = $projetQuery()->where('statut', 'en_cours')->count();
        $projetsTermines = $projetQuery()->where('statut', 'termine')->count();
        $projetsRetard = $projetQuery()->where('statut', 'en_cours')
            ->where('date_fin_prevue', '<', now())
            ->count();
        $projetsAvenir = $projetQuery()->where('statut', 'non_commence')->count();

        // ─── Budget vs Dépenses (6 derniers mois) ─────────────
        $months = collect(range(0, 5))->map(function ($i) use ($chantierQuery) {
            $date = now()->subMonths($i);
            return [
                'month' => $date->format('M'),
                'budget' => (float) $chantierQuery()->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('budget_total'),
                'depenses' => (float) $chantierQuery()->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('cout_reel'),
            ];
        })->reverse()->values();

        // ─── Chantiers récents ──────────────────────────────────
        $chantiersRecents = $chantierQuery()
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(fn($c) => [
                'nom' => $c->nom ?? 'Sans nom',
                'ville' => $c->ville ?? '—',
                'date_debut' => $c->date_debut?->format('d M Y') ?? '—',
                'date_fin_prevue' => $c->date_fin_prevue?->format('d M Y') ?? '—',
            ]);

        // ─── Tâches à venir (événements liés au périmètre) ─────
        $tachesQuery = Event::with('chantier')
            ->where('date', '>=', now()->toDateString())
            ->where('statut', 'a_venir');
        if ($role !== 'admin') {
            $tachesQuery->whereIn('chantier_id', $chantierIds);
        }
        $tachesAvenir = $tachesQuery
            ->orderBy('date', 'asc')
            ->limit(4)
            ->get()
            ->map(fn($e) => [
                'titre' => $e->titre ?? 'Tâche',
                'chantier_nom' => $e->chantier?->nom ?? '—',
                'date' => $e->date?->format('d M Y') ?? '—',
            ]);

        $activitesQuery = Activity::with('user')->orderBy('created_at', 'desc');
        if ($role !== 'admin' && $user) {
            $projetIds = $projetQuery()->pluck('id');
            $activitesQuery->where(function ($q) use ($user, $chantierIds, $projetIds) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($q2) use ($chantierIds) {
                      $q2->where('subject_type', 'Chantier')->whereIn('subject_id', $chantierIds);
                  })
                  ->orWhere(function ($q2) use ($projetIds) {
                      $q2->where('subject_type', 'Projet')->whereIn('subject_id', $projetIds);
                  });
            });
        }
        $activitesRecentes = $activitesQuery
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'description' => $a->description ?? 'Activité',
                'subject_label' => $a->subject_label ?? '—',
                'time_ago' => $a->created_at?->diffForHumans() ?? '—',
            ]);

        return [
            'scope' => $role, // indique au front quel jeu de widgets afficher
            'kpis' => $kpis,
            'statut_repartition' => $statutRepartition,
            'progression' => [
                'globale' => $progressionGlobale,
                'en_cours' => $projetsEnCours,
                'termines' => $projetsTermines,
                'retard' => $projetsRetard,
                'avenir' => $projetsAvenir,
            ],
            'budget_vs_depenses' => $months,
            'chantiers_recents' => $chantiersRecents,
            'taches_avenir' => $tachesAvenir,
            'activites_recentes' => $activitesRecentes,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // DASHBOARD "STOCK" (magasinier) : fournisseurs, produits, stock,
    // dépôts, mouvements — aucune donnée chantier/projet/budget.
    // ═══════════════════════════════════════════════════════════
    private function statsMagasinier($periodStart, $periodEnd): array
    {
        $totalProduits = Produit::count();
        $totalDepots = Stock::count();
        $totalFournisseurs = Fournisseur::count();

        $valeurStockTotale = 0;
        foreach (Produit::all() as $p) {
            $valeurStockTotale += (float) ($p->valeur_stock ?? 0);
        }

        $produitsEnAlerte = Produit::all()->filter(fn($p) => $p->alerte_stock)->values();

        $entreesPeriode = EntreeStock::whereBetween('date_entree', [$periodStart, $periodEnd])->count();
        $sortiesPeriode = SortieStock::whereBetween('date_sortie', [$periodStart, $periodEnd])->count();
        $transfertsPeriode = Transfert::whereBetween('date_transfert', [$periodStart, $periodEnd])->count();

        $valeurEntreesPeriode = (float) EntreeStock::whereBetween('date_entree', [$periodStart, $periodEnd])
            ->get()
            ->sum(fn($e) => $e->quantite * $e->prix_unitaire);

        $valeurSortiesPeriode = (float) SortieStock::whereBetween('date_sortie', [$periodStart, $periodEnd])
            ->get()
            ->sum(fn($s) => $s->quantite * ($s->produit?->prix_unitaire ?? 0));

        // ─── Mouvements récents (fusion entrées/sorties/transferts) ───
        $entrees = EntreeStock::with(['produit', 'stock'])->latest('date_entree')->limit(5)->get()
            ->map(fn($e) => [
                'type' => 'entree',
                'type_label' => 'Entrée',
                'produit' => $e->produit?->nom ?? '—',
                'quantite' => $e->quantite,
                'depot' => $e->stock?->nom ?? '—',
                'date' => $e->date_entree?->format('d M Y') ?? '—',
            ]);
        $sorties = SortieStock::with(['produit', 'stock'])->latest('date_sortie')->limit(5)->get()
            ->map(fn($s) => [
                'type' => 'sortie',
                'type_label' => 'Sortie',
                'produit' => $s->produit?->nom ?? '—',
                'quantite' => $s->quantite,
                'depot' => $s->stock?->nom ?? '—',
                'date' => $s->date_sortie?->format('d M Y') ?? '—',
            ]);
        $transferts = Transfert::with(['produit'])->latest('date_transfert')->limit(5)->get()
            ->map(fn($t) => [
                'type' => 'transfert',
                'type_label' => 'Transfert',
                'produit' => $t->produit?->nom ?? '—',
                'quantite' => $t->quantite,
                'depot' => '—',
                'date' => $t->date_transfert?->format('d M Y') ?? '—',
            ]);
        $mouvementsRecents = $entrees->concat($sorties)->concat($transferts)
            ->sortByDesc('date')
            ->take(6)
            ->values();

        // ─── Répartition du stock par dépôt (valeur) ───────────
        $stockParDepot = Stock::with('produits')->get()->map(function ($s) {
            $valeur = 0;
            foreach ($s->produits as $p) {
                $valeur += (float) $p->prix_unitaire * (float) $p->pivot->quantite;
            }
            return ['depot' => $s->nom, 'valeur' => $valeur];
        })->values();

        return [
            'scope' => 'magasinier',
            'kpis' => [
                'produits' => ['total' => $totalProduits],
                'depots' => ['total' => $totalDepots],
                'fournisseurs' => ['total' => $totalFournisseurs],
                'valeur_stock' => ['total' => $valeurStockTotale],
                'alertes' => ['total' => $produitsEnAlerte->count()],
                'mouvements' => [
                    'entrees' => $entreesPeriode,
                    'sorties' => $sortiesPeriode,
                    'transferts' => $transfertsPeriode,
                    'valeur_entrees' => $valeurEntreesPeriode,
                    'valeur_sorties' => $valeurSortiesPeriode,
                ],
            ],
            'produits_en_alerte' => $produitsEnAlerte->take(6)->map(fn($p) => [
                'nom' => $p->nom,
                'stock_total' => $p->stock_total,
                'unite' => $p->unite,
            ])->values(),
            'stock_par_depot' => $stockParDepot,
            'mouvements_recents' => $mouvementsRecents,
        ];
    }
}
