<?php

/**
 * Carte des permissions par rôle.
 *
 * Système léger "maison" (pas de package externe) : chaque rôle possède
 * une liste de permissions. Le rôle "admin" possède TOUTES les permissions
 * automatiquement (voir User::hasPermission()).
 *
 * Pour ajouter une permission : l'ajouter dans ALL_PERMISSIONS puis dans
 * la liste du/des rôle(s) concerné(s) ci-dessous.
 */

return [

    'all' => [
        // ---- Chantiers ----
        'view_chantiers', 'create_chantiers', 'edit_chantiers', 'delete_chantiers',
        // ---- Projets ----
        'view_projets', 'create_projets', 'edit_projets', 'delete_projets',
        // ---- Phases ----
        'view_phases', 'create_phases', 'edit_phases', 'delete_phases',
        // ---- Dépenses (projets) ----
        'view_depenses', 'create_depenses', 'edit_depenses', 'delete_depenses',
        // ---- Clients ----
        'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
        // ---- Utilisateurs ----
        'view_users', 'create_users', 'edit_users', 'delete_users',
        // ---- Produits ----
        'view_produits', 'create_produits', 'edit_produits', 'delete_produits',
        // ---- Fournisseurs ----
        'view_fournisseurs', 'create_fournisseurs', 'edit_fournisseurs', 'delete_fournisseurs',
        // ---- Stocks ----
        'view_stocks', 'manage_stocks',
        // ---- Mouvements ----
        'view_mouvements', 'create_entree', 'create_sortie', 'create_transfert', 'delete_mouvement',
        // ---- Documents ----
        'view_documents', 'create_documents', 'delete_documents',
        // ---- Événements ----
        'view_evenements', 'create_evenements', 'edit_evenements', 'delete_evenements',
        // ---- Historique (activités) ----
        'view_historique',
        // ---- Dashboard ----
        'view_dashboard',
    ],

    'roles' => [

        // admin = accès total, calculé automatiquement (voir User::permissions())
        'admin' => '*',

        'responsable' => [
            'view_chantiers',
            'view_projets', 'create_projets', 'edit_projets', 'delete_projets',
            'view_phases', 'create_phases', 'edit_phases', 'delete_phases',
            'view_depenses', 'create_depenses', 'edit_depenses', 'delete_depenses',
            'view_clients',
            'view_produits',
            'view_documents', 'create_documents', 'delete_documents',
            'view_evenements', 'create_evenements', 'edit_evenements', 'delete_evenements',
            'view_dashboard',
        ],

        'magasinier' => [
            'view_produits', 'create_produits', 'edit_produits', 'delete_produits',
            'view_fournisseurs', 'create_fournisseurs', 'edit_fournisseurs', 'delete_fournisseurs',
            'view_stocks', 'manage_stocks',
            'view_mouvements', 'create_entree', 'create_sortie', 'create_transfert', 'delete_mouvement',
            'view_chantiers',
            'view_dashboard',
        ],

        'chef_projet' => [
            'view_chantiers',
            'view_projets',
            'view_phases', 'create_phases', 'edit_phases', 'delete_phases',
            'view_depenses', 'create_depenses', 'edit_depenses', 'delete_depenses',
            'view_produits',
            'view_documents', 'create_documents', 'delete_documents',
            'view_evenements', 'create_evenements', 'edit_evenements', 'delete_evenements',
            'view_dashboard',
        ],
    ],

];
