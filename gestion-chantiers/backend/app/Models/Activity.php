<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_nom',
        'user_role',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs
    public function getActionLabelAttribute()
    {
        return [
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'status_changed' => 'Changement de statut',
            'restored' => 'Restauration',
            'assigned' => 'Affectation',
            'completed' => 'Finalisation',
            'archived' => 'Archivage',
        ][$this->action] ?? $this->action;
    }

    public function getSubjectTypeLabelAttribute()
    {
        $map = [
            'Chantier' => 'Chantier',
            'Projet' => 'Projet',
            'Phase' => 'Phase',
            'User' => 'Utilisateur',
            'Client' => 'Client',
            'Produit' => 'Produit',
            'Stock' => 'Stock',
            'Document' => 'Document',
            'Event' => 'Événement',
        ];
        return $map[$this->subject_type] ?? $this->subject_type;
    }

    public function getIconAttribute()
    {
        $icons = [
            'created' => '➕',
            'updated' => '✏️',
            'deleted' => '🗑️',
            'status_changed' => '🔄',
            'restored' => '♻️',
            'assigned' => '👤',
            'completed' => '✅',
            'archived' => '📦',
        ];
        return $icons[$this->action] ?? '📋';
    }

    public function getColorAttribute()
    {
        $colors = [
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'status_changed' => 'orange',
            'restored' => 'teal',
            'assigned' => 'purple',
            'completed' => 'indigo',
            'archived' => 'gray',
        ];
        return $colors[$this->action] ?? 'gray';
    }

    // Scopes
    public function scopeForSubject($query, $type, $id)
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}