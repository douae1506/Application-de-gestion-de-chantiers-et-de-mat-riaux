<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Collection;


class NotificationService
{
    
    public static function sendToUser(?int $userId, string $type, string $title, string $message, array $data = [], string $icon = '🔔'): ?AppNotification
    {
        if (!$userId) {
            return null;
        }

        return AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'icon'    => $icon,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function sendToUsers(iterable $users, string $type, string $title, string $message, array $data = [], string $icon = '🔔'): void
    {
        foreach ($users as $user) {
            $userId = $user instanceof User ? $user->id : $user;
            static::sendToUser($userId, $type, $title, $message, $data, $icon);
        }
    }

    // Envoie une notification à tous les utilisateurs actifs d'un rôle donné.

    public static function sendToRole(string $role, string $type, string $title, string $message, array $data = [], string $icon = '🔔'): void
    {
        $ids = User::where('role', $role)->where('est_actif', true)->pluck('id');
        static::sendToUsers($ids, $type, $title, $message, $data, $icon);
    }

    
    // Envoie une notification à tous les rôles listés.

    public static function sendToRoles(array $roles, string $type, string $title, string $message, array $data = [], string $icon = '🔔'): void
    {
        $ids = User::whereIn('role', $roles)->where('est_actif', true)->pluck('id');
        static::sendToUsers($ids, $type, $title, $message, $data, $icon);
    }


    public static function sendToAdmins(string $type, string $title, string $message, array $data = [], string $icon = '🔔'): void
    {
        static::sendToRole('admin', $type, $title, $message, $data, $icon);
    }

 
    public static function sendToTeam(string $type, string $title, string $message, array $data = [], string $icon = '🔔', ?int $excludeUserId = null): void
    {
        $ids = User::where('est_actif', true)
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->pluck('id');

        static::sendToUsers($ids, $type, $title, $message, $data, $icon);
    }

    public static function sendOncePerWindow(?int $userId, string $type, string $refKey, string $title, string $message, array $data = [], string $icon = '⚠️', int $windowHours = 20): ?AppNotification
    {
        if (!$userId) {
            return null;
        }

        $data['ref'] = $refKey;

        $alreadySent = AppNotification::where('user_id', $userId)
            ->where('type', $type)
            ->where('data->ref', $refKey)
            ->where('created_at', '>=', now()->subHours($windowHours))
            ->exists();

        if ($alreadySent) {
            return null;
        }

        return static::sendToUser($userId, $type, $title, $message, $data, $icon);
    }

    // Raccourcis "métier" utilisés dans les modèles / contrôleurs

    public static function chantierCree($chantier): void
    {
        $lien = "/admin/chantiers/{$chantier->id}";

        // Le responsable concerné (s'il existe)
        static::sendToUser(
            $chantier->responsable_id,
            'chantier_created',
            'Nouveau chantier qui vous est confié',
            "Le chantier « {$chantier->nom} » vous a été assigné.",
            ['chantier_id' => $chantier->id, 'link' => $lien],
            '🚧'
        );

        // Toute l'équipe est informée de la création
        static::sendToTeam(
            'chantier_created',
            'Nouveau chantier',
            "Un nouveau chantier « {$chantier->nom} » a été créé.",
            ['chantier_id' => $chantier->id, 'link' => $lien],
            '🚧',
            $chantier->responsable_id
        );
    }

    public static function chantierModifie($chantier): void
    {
        static::sendToUser(
            $chantier->responsable_id,
            'chantier_updated',
            'Chantier modifié',
            "Le chantier « {$chantier->nom} » a été modifié.",
            ['chantier_id' => $chantier->id, 'link' => "/admin/chantiers/{$chantier->id}"],
            '✏️'
        );
    }

    public static function chantierSupprime($chantier): void
    {
        static::sendToUser(
            $chantier->responsable_id,
            'chantier_deleted',
            'Chantier supprimé',
            "Le chantier « {$chantier->nom} » a été supprimé.",
            ['chantier_nom' => $chantier->nom],
            '🗑️'
        );
    }

    public static function chantierTermine($chantier): void
    {
        $lien = "/admin/chantiers/{$chantier->id}";

        static::sendToUser(
            $chantier->responsable_id,
            'chantier_termine',
            'Chantier terminé',
            "Le chantier « {$chantier->nom} » est maintenant terminé.",
            ['chantier_id' => $chantier->id, 'link' => $lien],
            '✅'
        );

        static::sendToAdmins(
            'chantier_termine',
            'Chantier terminé',
            "Le chantier « {$chantier->nom} » est maintenant terminé.",
            ['chantier_id' => $chantier->id, 'link' => $lien],
            '✅'
        );
    }

    public static function projetTermine($projet): void
    {
        $lien = "/admin/projets/{$projet->id}";
        $nomChantier = $projet->chantier?->nom;

        static::sendToAdmins(
            'projet_termine',
            'Projet terminé',
            $nomChantier
                ? "Le projet « {$projet->nom} » ({$nomChantier}) est terminé."
                : "Le projet « {$projet->nom} » est terminé.",
            ['projet_id' => $projet->id, 'link' => $lien],
            '📊'
        );

        if ($projet->responsable_id) {
            static::sendToUser(
                $projet->responsable_id,
                'projet_termine',
                'Projet terminé',
                "Le projet « {$projet->nom} » est terminé.",
                ['projet_id' => $projet->id, 'link' => $lien],
                '📊'
            );
        }
    }

    public static function stockEntree($stock, $produit, int $quantite): void
    {
        static::sendToRoles(
            ['admin', 'magasinier'],
            'stock_entree',
            'Nouvelle entrée stock',
            "{$quantite} {$produit->unite}(s) de « {$produit->nom} » ajouté(s) au stock « {$stock->nom} ».",
            ['stock_id' => $stock->id, 'produit_id' => $produit->id, 'link' => '/admin/mouvements'],
            '📦'
        );
    }

    public static function stockSortie($stock, $produit, int $quantite, $chantier = null): void
    {
        $suffixe = $chantier ? " pour le chantier « {$chantier->nom} »" : '';

        static::sendToRoles(
            ['admin', 'magasinier'],
            'stock_sortie',
            'Nouvelle sortie stock',
            "{$quantite} {$produit->unite}(s) de « {$produit->nom} » sorti(s) du stock « {$stock->nom} »{$suffixe}.",
            ['stock_id' => $stock->id, 'produit_id' => $produit->id, 'link' => '/admin/mouvements'],
            '📤'
        );
    }

    public static function stockTransfert($produit, int $quantite, $source, $destination): void
    {
        static::sendToRoles(
            ['admin', 'magasinier'],
            'stock_transfert',
            'Transfert de stock',
            "{$quantite} {$produit->unite}(s) de « {$produit->nom} » transféré(s) de « {$source->nom} » vers « {$destination->nom} ».",
            ['produit_id' => $produit->id, 'link' => '/admin/mouvements'],
            '🔄'
        );
    }

    public static function stockSeuilAtteint($stock, $produit, int $quantite, int $seuil): void
    {
        $message = "Le produit « {$produit->nom} » dans « {$stock->nom} » est proche de la rupture : {$quantite} {$produit->unite}(s) restant(s) (seuil : {$seuil}).";

        static::sendOncePerWindowToRoles(
            ['admin', 'magasinier'],
            'stock_seuil',
            "seuil-{$stock->id}-{$produit->id}",
            'Stock sous le seuil minimum',
            $message,
            ['stock_id' => $stock->id, 'produit_id' => $produit->id, 'link' => '/admin/stocks'],
            '⚠️'
        );
    }

    public static function stockRupture($stock, $produit): void
    {
        $message = "Rupture de stock : « {$produit->nom} » n'est plus disponible dans « {$stock->nom} ».";

        static::sendOncePerWindowToRoles(
            ['admin', 'magasinier'],
            'stock_rupture',
            "rupture-{$stock->id}-{$produit->id}",
            'Rupture de stock',
            $message,
            ['stock_id' => $stock->id, 'produit_id' => $produit->id, 'link' => '/admin/stocks'],
            '🚫'
        );
    }

    public static function materielValide($sortie): void
    {
        $chantier = $sortie->chantier;
        $projet = $sortie->projet;

        if (!$chantier || !$chantier->responsable_id) {
            return;
        }

        static::sendToUser(
            $chantier->responsable_id,
            'materiel_valide',
            'Matériel validé',
            $projet
                ? "Une sortie de « {$sortie->produit?->nom} » a été validée pour le projet « {$projet->nom} » ({$chantier->nom})."
                : "Une sortie de « {$sortie->produit?->nom} » a été validée pour le chantier « {$chantier->nom} ».",
            ['chantier_id' => $chantier->id, 'link' => "/admin/chantiers/{$chantier->id}"],
            '✔️'
        );
    }

    public static function demandeMaterielAnnulee($sortie): void
    {
        $nomChantier = $sortie->chantier?->nom;

        static::sendToRole(
            'magasinier',
            'demande_annulee',
            'Demande de matériel annulée',
            $nomChantier
                ? "La sortie de « {$sortie->produit?->nom} » ({$nomChantier}) a été annulée et remise en stock."
                : "Une sortie de « {$sortie->produit?->nom} » a été annulée et remise en stock.",
            ['link' => '/admin/mouvements'],
            '❌'
        );
    }

    public static function projetCree($projet): void
    {
        $lien = "/admin/projets/{$projet->id}";
        $nomChantier = $projet->chantier?->nom;

        static::sendToAdmins(
            'projet_created',
            'Nouveau projet',
            $nomChantier
                ? "Un nouveau projet « {$projet->nom} » a été créé pour le chantier « {$nomChantier} »."
                : "Un nouveau projet « {$projet->nom} » a été créé.",
            ['projet_id' => $projet->id, 'link' => $lien],
            '📁'
        );
    }

    public static function projetModifie($projet): void
    {
        $lien = "/admin/projets/{$projet->id}";

        static::sendToAdmins(
            'projet_updated',
            'Projet modifié',
            "Le projet « {$projet->nom} » a été modifié.",
            ['projet_id' => $projet->id, 'link' => $lien],
            '✏️'
        );
    }

    public static function chantierEnRetard($chantier): void
    {
        if (!$chantier->responsable_id) {
            return;
        }

        static::sendOncePerWindow(
            $chantier->responsable_id,
            'chantier_retard',
            "chantier-retard-{$chantier->id}",
            'Chantier en retard',
            "Le chantier « {$chantier->nom} » a dépassé sa date de fin prévue et n'est pas terminé.",
            ['chantier_id' => $chantier->id, 'link' => "/admin/chantiers/{$chantier->id}"],
            '⏰'
        );
    }

    public static function projetEnRetard($projet): void
    {
        if (!$projet->responsable_id) {
            return;
        }

        static::sendOncePerWindow(
            $projet->responsable_id,
            'projet_retard',
            "projet-retard-{$projet->id}",
            'Projet en retard',
            "Le projet « {$projet->nom} » a dépassé sa date de fin prévue et n'est pas terminé.",
            ['projet_id' => $projet->id, 'link' => "/admin/projets/{$projet->id}"],
            '⏰'
        );
    }

    
     // Notifie l'admin + le responsable qu'un chantier approche de sa date de
     // fin prévue (ex. J-7, J-1). $joursRestants sert de clé pour que les
     // deux échéances (7 jours, 1 jour) soient bien notifiées séparément et
     // une seule fois chacune.
    
    public static function chantierEcheanceProche($chantier, int $joursRestants): void
    {
        $lien = "/admin/chantiers/{$chantier->id}";
        $delai = $joursRestants <= 1
            ? "demain"
            : "dans {$joursRestants} jours";
        $message = "Le chantier « {$chantier->nom} » doit se terminer {$delai} (date de fin prévue).";
        $refKey = "chantier-echeance-{$joursRestants}-{$chantier->id}";

        if ($chantier->responsable_id) {
            static::sendOncePerWindow(
                $chantier->responsable_id,
                'chantier_echeance',
                $refKey,
                'Échéance de chantier proche',
                $message,
                ['chantier_id' => $chantier->id, 'link' => $lien, 'jours_restants' => $joursRestants],
                '⏳'
            );
        }

        static::sendOncePerWindowToRoles(
            ['admin'],
            'chantier_echeance',
            $refKey,
            'Échéance de chantier proche',
            $message,
            ['chantier_id' => $chantier->id, 'link' => $lien, 'jours_restants' => $joursRestants],
            '⏳'
        );
    }

    
    // Notifie l'admin + le chef de projet (responsable_id du projet) qu'un
    // projet approche de sa date de fin prévue (ex. J-7, J-1).
     
    public static function projetEcheanceProche($projet, int $joursRestants): void
    {
        $lien = "/admin/projets/{$projet->id}";
        $delai = $joursRestants <= 1
            ? "demain"
            : "dans {$joursRestants} jours";
        $message = "Le projet « {$projet->nom} » doit se terminer {$delai} (date de fin prévue).";
        $refKey = "projet-echeance-{$joursRestants}-{$projet->id}";

        if ($projet->responsable_id) {
            static::sendOncePerWindow(
                $projet->responsable_id,
                'projet_echeance',
                $refKey,
                'Échéance de projet proche',
                $message,
                ['projet_id' => $projet->id, 'link' => $lien, 'jours_restants' => $joursRestants],
                '⏳'
            );
        }

        static::sendOncePerWindowToRoles(
            ['admin'],
            'projet_echeance',
            $refKey,
            'Échéance de projet proche',
            $message,
            ['projet_id' => $projet->id, 'link' => $lien, 'jours_restants' => $joursRestants],
            '⏳'
        );
    }

    public static function utilisateurCree($user): void
    {
        static::sendToAdmins(
            'user_created',
            'Nouvel utilisateur',
            "Le compte « {$user->prenom} {$user->nom} » ({$user->role}) a été créé.",
            ['user_id' => $user->id, 'link' => "/admin/users/{$user->id}"],
            '👤'
        );
    }

    public static function utilisateurSupprime(string $nomComplet): void
    {
        static::sendToAdmins(
            'user_deleted',
            'Utilisateur supprimé',
            "Le compte « {$nomComplet} » a été supprimé.",
            ['link' => '/admin/utilisateurs'],
            '🗑️'
        );
    }

    
    // Comme sendOncePerWindow, mais pour plusieurs rôles à la fois.
    
    public static function sendOncePerWindowToRoles(array $roles, string $type, string $refKey, string $title, string $message, array $data = [], string $icon = '⚠️', int $windowHours = 20): void
    {
        $ids = User::whereIn('role', $roles)->where('est_actif', true)->pluck('id');

        foreach ($ids as $userId) {
            static::sendOncePerWindow($userId, $type, $refKey, $title, $message, $data, $icon, $windowHours);
        }
    }
}