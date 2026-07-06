<?php

namespace App\Traits;

use App\Models\Activity;

/**
 * Ajoute la journalisation automatique des actions create / update / delete
 * dans la table `activities`, sans dupliquer de code dans chaque modèle.
 *
 * Chaque modèle peut personnaliser le libellé affiché en définissant
 * une méthode `activityLabel(): string` (sinon "nom" ou "reference" est utilisé).
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('created', "a créé " . $model->activityArticle() . " " . $model->activityDisplayName());
        });

        static::updated(function ($model) {
            // Évite de journaliser les mises à jour "silencieuses" internes
            // (ex : recalcul automatique de progression / cout_reel déclenché par un observer)
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) {
                return;
            }

            if (method_exists($model, 'activitySilentFields')) {
                $ignored = $model->activitySilentFields();
                $changes = array_diff_key($changes, array_flip($ignored));
                if (empty($changes)) {
                    return;
                }
            }

            // Cas particulier : changement de statut
            if (array_key_exists('statut', $changes)) {
                $model->recordActivity(
                    'status_changed',
                    "a changé le statut de " . $model->activityArticle() . " " . $model->activityDisplayName() . " vers « " . ($model->statut_label ?? $changes['statut']) . " »",
                    ['champs' => array_keys($changes)]
                );
                return;
            }

            $model->recordActivity(
                'updated',
                "a modifié " . $model->activityArticle() . " " . $model->activityDisplayName(),
                ['champs' => array_keys($changes)]
            );
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted', "a supprimé " . $model->activityArticle() . " " . $model->activityDisplayName());
        });
    }

    public function recordActivity(string $action, string $description, ?array $properties = null): void
    {
        Activity::log($action, $this, $description, $properties);
    }

    /**
     * Nom affiché dans la phrase d'historique.
     */
    public function activityDisplayName(): string
    {
        foreach (['nom', 'reference', 'titre', 'email'] as $field) {
            if (isset($this->{$field}) && $this->{$field} !== '') {
                return (string) $this->{$field};
            }
        }
        return '#' . $this->getKey();
    }

    /**
     * Article français ("le", "la", "l'") + nom du type d'objet, ex: "le chantier".
     * Personnalisable via la constante ACTIVITY_LABEL sur le modèle.
     */
    public function activityArticle(): string
    {
        $labels = [
            'Chantier'       => 'le chantier',
            'Projet'         => 'le projet',
            'Client'         => 'le client',
            'User'           => "l'utilisateur",
            'Fournisseur'    => 'le fournisseur',
            'Produit'        => 'le produit',
            'Stock'          => 'le dépôt',
            'EntreeStock'    => 'une entrée de stock pour',
            'SortieStock'    => 'une sortie de stock pour',
            'Transfert'      => 'un transfert de stock pour',
            'Document'       => 'le document',
            'ProjectExpense' => 'la dépense',
            'Phase'          => 'la phase',
            'Event'          => "l'événement",
        ];

        $short = class_basename($this);
        return $labels[$short] ?? 'l\'élément';
    }
}