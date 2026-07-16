<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\NotificationService;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'reference',
        'nom',
        'description',
        'categorie',
        'budget',
        'cout_reel',
        'priorite',
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'progression',
        'statut',
        'couleur',
        'observations',
        'responsable_id',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'cout_reel' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'progression' => 'integer',
    ];

    public function getStatutLabelAttribute()
    {
        $labels = [
            'non_commence' => 'Non commencé',
            'en_cours'     => 'En cours',
            'termine'      => 'Terminé',
            'bloque'       => 'Bloqué',
        ];
        return $labels[$this->statut] ?? $this->statut;
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    public function expenses()
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function responsable()
    {
    return $this->belongsTo(User::class, 'responsable_id');
    }

    protected static function booted()
    {
        static::saving(function ($projet) {
            if ($projet->isDirty('statut')) {
                if ($projet->statut === 'termine') {
                    $projet->date_fin_reelle = $projet->date_fin_reelle ?: now();
                } elseif ($projet->getOriginal('statut') === 'termine') {
                    $projet->date_fin_reelle = null;
                }
            }
        });

        static::saved(function ($projet) {
            $projet->chantier?->mettreAJourStatistiques();
        });

        static::deleted(function ($projet) {
            $projet->chantier?->mettreAJourStatistiques();
        });

        static::saved(function ($projet) {
            $projet->chantier?->updateStatusAndStats();
        });

        static::deleted(function ($projet) {
            $projet->chantier?->updateStatusAndStats();
        });

        static::created(function ($projet) {
            NotificationService::projetCree($projet);
        });

        static::updated(function ($projet) {
            $champsAuto = ['progression', 'cout_reel', 'statut', 'updated_at'];
            $champsMetier = array_diff(array_keys($projet->getChanges()), $champsAuto);

            if ($projet->wasChanged('statut') && $projet->statut === 'termine') {
                NotificationService::projetTermine($projet);
            } elseif (!empty($champsMetier)) {
                NotificationService::projetModifie($projet);
            }
        });
    }

    public function updateCoutReel(): void
    {
        $total = $this->expenses()->sum('montant');
        $this->update(['cout_reel' => $total]);
    }

    /**
 * Met à jour la progression et le statut du projet à partir de ses phases,
 * puis déclenche la mise à jour du chantier parent.
 */
    public function updateStatusAndProgress()
    {
        $this->load('phases');
        $phases = $this->phases;

        $this->progression = $phases->isEmpty() ? 0 : round($phases->avg('progression'));
            if ($phases->isEmpty()) {
            $newStatus = 'non_commence';
        } else {
            $hasEnCours  = $phases->contains('statut', 'en_cours');
            $hasTerminee = $phases->contains('statut', 'terminee');
            $hasBloquee  = $phases->contains('statut', 'bloquee');
            $allTerminees = $phases->every(fn($p) => $p->statut === 'terminee');

            if ($allTerminees) {
                $newStatus = 'termine';
            } elseif ($hasEnCours) {
                $newStatus = 'en_cours';
            } elseif ($hasBloquee) {
                $newStatus = 'bloque';
            } else {
                $newStatus = 'non_commence';
            }
        }
        if ($this->statut !== $newStatus) {
            $this->statut = $newStatus;
        }
        $this->save();
        $this->chantier?->updateStatusAndStats();
    }
}