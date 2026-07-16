<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vérifie chaque jour les chantiers/projets en retard et les échéances
// proches (J-7, J-1) pour notifier admin + responsable / chef de projet.
Schedule::command('notifications:check-overdue')->dailyAt('07:00');
