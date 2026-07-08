<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ActivityController;

class Chantier extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'reference',
        'nom',
        'description',
        'type',
        'adresse',
        'ville',
        'pays',
        'code_postal',
        'latitude',
        'longitude',
        'surface',
        'budget_total',
        'cout_reel',
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'statut',
        'progression',
        'architecte',
        'observations',
        'responsable_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'budget_total' => 'decimal:2',
        'cout_reel' => 'decimal:2',
        'surface' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Un chantier possède plusieurs projets
    public function projets()
    {
        return $this->hasMany(Projet::class);
    }
    public function sortieStocks()
    {
        return $this->hasMany(SortieStock::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    
       public function getJoursRestantsAttribute(): int
        {
            if (!$this->date_fin_prevue) return 0;
            return max(0, (int) now()->diffInDays($this->date_fin_prevue, false));
        }
    
        /** Budget restant */
        public function getBudgetRestantAttribute(): float
        {
            return max(0, (float) $this->budget_total - (float) $this->cout_reel);
        }
    
        /** Pourcentage dépensé */
        public function getPourcentageDepenseAttribute(): int
        {
            if (!$this->budget_total || $this->budget_total == 0) return 0;
            return (int) min(100, round(($this->cout_reel / $this->budget_total) * 100));
        }
    
        public function getStatutLabelAttribute(): string
        {
            return match($this->statut) {
                'planifie'  => 'Planifié',
                'en_cours'  => 'En cours',
                'suspendu'  => 'Suspendu',
                'termine'   => 'Terminé',
                'annule'    => 'Annulé',
                default     => ucfirst($this->statut),
            };
        }
    
        /** Label type lisible */
        public function getTypeLabelAttribute(): string
        {
            return match($this->type) {
                'residentiel' => 'Résidentiel',
                'commercial'  => 'Commercial',
                'industriel'  => 'Industriel',
                'public'      => 'Public',
                default       => ucfirst($this->type),
            };
        }
    
        /** Auto-génère la prochaine référence */
        public static function genererReference(): string
    {
        $annee = now()->year;
    
        $lastReference = self::where('reference', 'like', "CHT-$annee-%")
            ->max('reference');
    
        if (!$lastReference) {
            return "CHT-$annee-001";
        }
    
        $numero = (int) substr($lastReference, -3);
    
        return sprintf("CHT-%d-%03d", $annee, $numero + 1);
    } // ─── Scopes ───────────────────────────────────────
    
        public function scopeSearch($query, string $term)
        {
            return $query->where(function ($q) use ($term) {
                $q->where('nom',       'like', "%{$term}%")
                  ->orWhere('reference', 'like', "%{$term}%")
                  ->orWhere('ville',     'like', "%{$term}%")
                  ->orWhereHas('client', fn($c) => $c->where('nom', 'like', "%{$term}%"));
            });
        }
        public function calculerProgression()
    {
        if ($this->projets()->count() == 0) {
            return 0;
        }
    
        return round(
            $this->projets()->avg('progression')
        );
    }
    
    public function calculerCoutReel(): float
        {
             return (float) $this->projets->sum('cout_reel');
        }
    
    public function updateCoutReel(): void
    {
        $total = $this->projets()->sum('cout_reel');
        $this->update(['cout_reel' => $total]);
    }
    public function mettreAJourStatistiques(): void
    {
        $this->load('projets');
        
        $this->progression = $this->calculerProgression();
        $this->cout_reel    = $this->calculerCoutReel();
        $this->save();
    }
    
    public function updateStatusAndStats()
    {
        $this->load('projets');
        $projets = $this->projets;
            $this->progression = $projets->isEmpty() ? 0 : round($projets->avg('progression'));
            $this->cout_reel = $projets->sum('cout_reel');
            if ($projets->isEmpty()) {
            $newStatus = 'planifie';
            } else {
            $hasEnCours  = $projets->contains('statut', 'en_cours');
            $hasTermine  = $projets->contains('statut', 'termine');
            $hasSuspendu = $projets->contains('statut', 'suspendu');
            $hasAnnule   = $projets->contains('statut', 'annule');
            $allTermines = $projets->every(fn($p) => $p->statut === 'termine');
    
            if ($allTermines) {
                $newStatus = 'termine';
            } elseif ($hasEnCours) {
                $newStatus = 'en_cours';
            } elseif ($hasSuspendu) {
                $newStatus = 'suspendu';
            } elseif ($hasAnnule) {
                $newStatus = 'annule';
            } else {
                $newStatus = 'planifie';
            }
        }
        if ($this->statut !== $newStatus) {
            $this->statut = $newStatus;
        }
        $this->save();
    }

    // app/Models/Chantier.php
protected static function booted()
{
    static::created(function ($chantier) {
        ActivityController::log(
            auth()->id(),
            'created',
            'Chantier',
            $chantier->id,
            $chantier->nom,
            "a créé le chantier « {$chantier->nom} »",
            ['reference' => $chantier->reference, 'type' => $chantier->type]
        );
    });

    static::updated(function ($chantier) {
        $changes = $chantier->getChanges();
        $original = $chantier->getOriginal();
        $dirty = [];

        foreach ($changes as $key => $value) {
            if ($key === 'updated_at') continue;
            $dirty[$key] = ['avant' => $original[$key] ?? null, 'après' => $value];
        }

        if (!empty($dirty)) {
            ActivityController::log(
                auth()->id(),
                'updated',
                'Chantier',
                $chantier->id,
                $chantier->nom,
                "a modifié le chantier « {$chantier->nom} »",
                $dirty
            );
        }
    });

    static::deleted(function ($chantier) {
        ActivityController::log(
            auth()->id(),
            'deleted',
            'Chantier',
            $chantier->id,
            $chantier->nom,
            "a supprimé le chantier « {$chantier->nom} »",
            ['reference' => $chantier->reference]
        );
    });
}
}