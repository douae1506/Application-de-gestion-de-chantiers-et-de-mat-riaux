<?php

namespace App\Console\Commands;

use App\Models\Chantier;
use App\Models\Projet;
use App\Services\NotificationService;
use Illuminate\Console\Command;


class CheckOverdueCommand extends Command
{
    /** Nombre de jours avant la date de fin prévue à notifier. */
    private const JOURS_ALERTE = [7, 1];

    protected $signature = 'notifications:check-overdue';

    protected $description = "Notifie les responsables des chantiers et projets en retard, ainsi que des échéances proches (J-7, J-1)";

    public function handle(): int
    {
        $chantiersEnRetard = Chantier::whereNotIn('statut', ['termine', 'annule'])
            ->whereNotNull('date_fin_prevue')
            ->whereDate('date_fin_prevue', '<', now()->toDateString())
            ->whereNotNull('responsable_id')
            ->get();

        foreach ($chantiersEnRetard as $chantier) {
            NotificationService::chantierEnRetard($chantier);
        }

        $projetsEnRetard = Projet::whereNotIn('statut', ['termine'])
            ->whereNotNull('date_fin_prevue')
            ->whereDate('date_fin_prevue', '<', now()->toDateString())
            ->whereNotNull('responsable_id')
            ->get();

        foreach ($projetsEnRetard as $projet) {
            NotificationService::projetEnRetard($projet);
        }

        $chantiersEcheanceCount = 0;
        $projetsEcheanceCount = 0;

        foreach (self::JOURS_ALERTE as $jours) {
            $dateCible = now()->addDays($jours)->toDateString();

            $chantiersEcheance = Chantier::whereNotIn('statut', ['termine', 'annule'])
                ->whereDate('date_fin_prevue', '=', $dateCible)
                ->get();

            foreach ($chantiersEcheance as $chantier) {
                NotificationService::chantierEcheanceProche($chantier, $jours);
                $chantiersEcheanceCount++;
            }

            $projetsEcheance = Projet::whereNotIn('statut', ['termine'])
                ->whereDate('date_fin_prevue', '=', $dateCible)
                ->get();

            foreach ($projetsEcheance as $projet) {
                NotificationService::projetEcheanceProche($projet, $jours);
                $projetsEcheanceCount++;
            }
        }

        $this->info(
            "Chantiers en retard notifiés : {$chantiersEnRetard->count()}. Projets en retard notifiés : {$projetsEnRetard->count()}. ".
            "Échéances proches (chantiers) : {$chantiersEcheanceCount}. Échéances proches (projets) : {$projetsEcheanceCount}."
        );

        return self::SUCCESS;
    }
}