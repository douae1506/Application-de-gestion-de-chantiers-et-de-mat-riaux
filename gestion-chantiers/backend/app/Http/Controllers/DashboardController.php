<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Projet;
use App\Models\Event;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            // ─── KPI ───────────────────────────────────────────────
            $totalChantiers = Chantier::count();
            $newChantiers = Chantier::where('created_at', '>=', now()->subMonth())->count();

            $totalProjets = Projet::count();
            $newProjets = Projet::where('created_at', '>=', now()->subMonth())->count();

            $budgetTotal = Chantier::sum('budget_total') ?: 0;
            $depensesTotal = Chantier::sum('cout_reel') ?: 0;

            // Évolution simplifiée
            $lastMonthBudget = Chantier::where('created_at', '<', now()->subMonth())->sum('budget_total') ?: 0;
            $budgetEvolution = $lastMonthBudget ? round((($budgetTotal - $lastMonthBudget) / $lastMonthBudget) * 100) : 0;

            $lastMonthDepenses = Chantier::where('created_at', '<', now()->subMonth())->sum('cout_reel') ?: 0;
            $depensesEvolution = $lastMonthDepenses ? round((($depensesTotal - $lastMonthDepenses) / $lastMonthDepenses) * 100) : 0;

            $totalEmployes = User::count();
            $newEmployes = User::where('created_at', '>=', now()->subMonth())->count();

            $kpis = [
                'chantiers' => ['total' => $totalChantiers, 'new' => $newChantiers],
                'projets' => ['total' => $totalProjets, 'new' => $newProjets],
                'budget' => ['total' => $budgetTotal, 'evolution' => $budgetEvolution],
                'depenses' => ['total' => $depensesTotal, 'evolution' => $depensesEvolution],
                'employes' => ['total' => $totalEmployes, 'new' => $newEmployes],
            ];

            // ─── Répartition des chantiers par statut ──────────────
            $statutRepartition = Chantier::select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();

            // ─── Avancement global des projets ─────────────────────
            $progressionGlobale = round(Projet::avg('progression') ?: 0);
            $projetsEnCours = Projet::where('statut', 'en_cours')->count();
            $projetsTermines = Projet::where('statut', 'termine')->count();
            $projetsRetard = Projet::where('statut', 'en_cours')
                ->where('date_fin_prevue', '<', now())
                ->count();
            $projetsAvenir = Projet::where('statut', 'non_commence')->count();

            // ─── Budget vs Dépenses (6 derniers mois) ─────────────
            $months = collect(range(0, 5))->map(function ($i) {
                $date = now()->subMonths($i);
                return [
                    'month' => $date->format('M'),
                    'budget' => (float) Chantier::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('budget_total'),
                    'depenses' => (float) Chantier::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('cout_reel'),
                ];
            })->reverse()->values();

            // ─── Chantiers récents ──────────────────────────────────
            $chantiersRecents = Chantier::with('client')
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(fn($c) => [
                    'nom' => $c->nom ?? 'Sans nom',
                    'ville' => $c->ville ?? '—',
                    'date_debut' => $c->date_debut?->format('d M Y') ?? '—',
                    'date_fin_prevue' => $c->date_fin_prevue?->format('d M Y') ?? '—',
                ]);

            // ─── Tâches à venir (événements) ──────────────────────
            $tachesAvenir = Event::with('chantier')
                ->where('date', '>=', now()->toDateString())
                ->where('statut', 'a_venir')
                ->orderBy('date', 'asc')
                ->limit(4)
                ->get()
                ->map(fn($e) => [
                    'titre' => $e->titre ?? 'Tâche',
                    'chantier_nom' => $e->chantier?->nom ?? '—',
                    'date' => $e->date?->format('d M Y') ?? '—',
                ]);

            // ─── Activités récentes ─────────────────────────────────
            $activitesRecentes = Activity::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'description' => $a->description ?? 'Activité',
                    'subject_label' => $a->subject_label ?? '—',
                    'time_ago' => $a->created_at?->diffForHumans() ?? '—',
                ]);

            return response()->json([
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
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Erreur interne du serveur',
                'message' => $e->getMessage(), // En développement, vous pouvez garder cela
            ], 500);
        }
    }
}