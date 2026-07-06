<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'titre',
        'description',
        'date',
        'heure',
        'type',
        'statut',
        'rappel',
    ];

    protected $casts = [
        'date' => 'date',
        'heure' => 'datetime:H:i',
        'rappel' => 'boolean',
    ];

    // Ajouté automatiquement à la sérialisation JSON
    protected $appends = ['type_label', 'statut_label', 'heure_formatee', 'periode'];

    // Relations
    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    // Accesseurs pour l'affichage
    public function getTypeLabelAttribute()
    {
        return [
            'reunion'   => 'Réunion',
            'livraison' => 'Livraison',
            'inspection'=> 'Inspection',
            'autre'     => 'Autre',
        ][$this->type] ?? $this->type;
    }

    public function getStatutLabelAttribute()
    {
        return [
            'a_venir' => 'À venir',
            'termine' => 'Terminé',
            'annule'  => 'Annulé',
        ][$this->statut] ?? $this->statut;
    }

    public function getHeureFormateeAttribute()
    {
        return $this->heure ? $this->heure->format('H:i') : null;
    }

    // Période relative à aujourd'hui, utile pour le frontend (badges / regroupement)
    public function getPeriodeAttribute()
    {
        if (!$this->date) return 'plus_tard';

        $today = Carbon::today();

        if ($this->date->isSameDay($today)) return 'aujourdhui';
        if ($this->date->lt($today)) return 'passe';
        if ($this->date->between($today, $today->copy()->addDays(7))) return 'cette_semaine';

        return 'plus_tard';
    }

    public function scopeAvenir($query)
    {
        return $query->where('statut', 'a_venir');
    }

    public function scopePasse($query)
    {
        return $query->where('statut', '!=', 'a_venir');
    }
}