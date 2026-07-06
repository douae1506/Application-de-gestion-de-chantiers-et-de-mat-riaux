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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Libellé lisible de l'action (created, updated, deleted, ...)
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'        => 'Création',
            'updated'        => 'Modification',
            'deleted'        => 'Suppression',
            'status_changed' => 'Changement de statut',
            'login'          => 'Connexion',
            'logout'         => 'Déconnexion',
            default          => ucfirst($this->action),
        };
    }

    /**
     * Libellé lisible du rôle utilisateur
     */
    public function getUserRoleLabelAttribute(): string
    {
        return match ($this->user_role) {
            'admin'       => 'Administrateur',
            'responsable' => 'Responsable',
            'chef_projet' => 'Chef de projet',
            'magasinier'  => 'Magasinier',
            default       => $this->user_role ? ucfirst($this->user_role) : '—',
        };
    }

    /**
     * Nom "humain" du type de modèle concerné (Chantier, Projet, ...)
     */
    public function getSubjectTypeLabelAttribute(): string
    {
        $labels = [
            'Chantier'       => 'Chantier',
            'Projet'         => 'Projet',
            'Client'         => 'Client',
            'User'           => 'Utilisateur',
            'Fournisseur'    => 'Fournisseur',
            'Produit'        => 'Produit',
            'Stock'          => 'Dépôt',
            'EntreeStock'    => 'Entrée de stock',
            'SortieStock'    => 'Sortie de stock',
            'Transfert'      => 'Transfert de stock',
            'Document'       => 'Document',
            'ProjectExpense' => 'Dépense',
            'Phase'          => 'Phase',
            'Event'          => 'Événement',
        ];

        $short = class_basename($this->subject_type);

        return $labels[$short] ?? $short;
    }

    /**
     * Enregistre une nouvelle entrée d'historique.
     */
    public static function log(string $action, $subject, string $description, ?array $properties = null): self
    {
        $user = auth()->user();

        return self::create([
            'user_id'       => $user?->id,
            'user_nom'      => $user ? trim($user->prenom . ' ' . $user->nom) : 'Système',
            'user_role'     => $user?->role,
            'action'        => $action,
            'subject_type'  => is_object($subject) ? get_class($subject) : (string) $subject,
            'subject_id'    => is_object($subject) ? $subject->getKey() : null,
            'subject_label' => is_object($subject) ? self::guessLabel($subject) : null,
            'description'   => $description,
            'properties'    => $properties,
        ]);
    }

    private static function guessLabel($model): ?string
    {
        foreach (['nom', 'reference', 'titre', 'email'] as $field) {
            if (isset($model->{$field})) {
                return (string) $model->{$field};
            }
        }
        return null;
    }
}