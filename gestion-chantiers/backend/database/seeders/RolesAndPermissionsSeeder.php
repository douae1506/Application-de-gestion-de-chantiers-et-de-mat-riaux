<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Réinitialiser les caches des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================================
        // 1. DÉFINITION DE TOUTES LES PERMISSIONS
        // ============================================================
        $permissions = [
            // ---- Chantiers ----
            'view_chantiers',
            'create_chantiers',
            'edit_chantiers',
            'delete_chantiers',

            // ---- Projets ----
            'view_projets',
            'create_projets',
            'edit_projets',
            'delete_projets',

            // ---- Phases ----
            'view_phases',
            'create_phases',
            'edit_phases',
            'delete_phases',

            // ---- Dépenses (projets) ----
            'view_depenses',
            'create_depenses',
            'edit_depenses',
            'delete_depenses',

            // ---- Clients ----
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',

            // ---- Utilisateurs ----
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // ---- Produits ----
            'view_produits',
            'create_produits',
            'edit_produits',
            'delete_produits',

            // ---- Fournisseurs ----
            'view_fournisseurs',
            'create_fournisseurs',
            'edit_fournisseurs',
            'delete_fournisseurs',

            // ---- Stocks ----
            'view_stocks',
            'manage_stocks',      // affecter des produits à un stock

            // ---- Mouvements (entrées/sorties/transferts) ----
            'view_mouvements',
            'create_entree',
            'create_sortie',
            'create_transfert',
            'delete_mouvement',

            // ---- Documents ----
            'view_documents',
            'create_documents',
            'delete_documents',

            // ---- Événements ----
            'view_evenements',
            'create_evenements',
            'edit_evenements',
            'delete_evenements',

            // ---- Historique (activités) ----
            'view_historique',

            // ---- Dashboard ----
            'view_dashboard',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        // ============================================================
        // 2. CRÉATION DES RÔLES ET ASSIGNATION DES PERMISSIONS
        // ============================================================

        // --- ADMIN (toutes les permissions) ---
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::all());

        // --- RESPONSABLE (chef de projet) ---
        $responsableRole = Role::firstOrCreate(['name' => 'responsable', 'guard_name' => 'api']);
        $responsableRole->syncPermissions([
            // Chantiers (lecture uniquement)
            'view_chantiers',

            // Projets (CRUD complet)
            'view_projets',
            'create_projets',
            'edit_projets',
            'delete_projets',

            // Phases (CRUD complet)
            'view_phases',
            'create_phases',
            'edit_phases',
            'delete_phases',

            // Dépenses (CRUD complet)
            'view_depenses',
            'create_depenses',
            'edit_depenses',
            'delete_depenses',

            // Clients (lecture uniquement)
            'view_clients',

            // Produits (lecture uniquement)
            'view_produits',

            // Documents (CRUD complet)
            'view_documents',
            'create_documents',
            'delete_documents',

            // Événements (CRUD complet)
            'view_evenements',
            'create_evenements',
            'edit_evenements',
            'delete_evenements',

            // Dashboard
            'view_dashboard',
        ]);

        // --- MAGASINIER ---
        $magasinierRole = Role::firstOrCreate(['name' => 'magasinier', 'guard_name' => 'api']);
        $magasinierRole->syncPermissions([
            // Produits (CRUD complet)
            'view_produits',
            'create_produits',
            'edit_produits',
            'delete_produits',

            // Fournisseurs (CRUD complet)
            'view_fournisseurs',
            'create_fournisseurs',
            'edit_fournisseurs',
            'delete_fournisseurs',

            // Stocks (gestion complète)
            'view_stocks',
            'manage_stocks',

            // Mouvements (CRUD complet)
            'view_mouvements',
            'create_entree',
            'create_sortie',
            'create_transfert',
            'delete_mouvement',

            // Chantiers (lecture uniquement)
            'view_chantiers',

            // Dashboard
            'view_dashboard',
        ]);

        $chefProjetRole = Role::firstOrCreate([
    'name' => 'chef_projet',
    'guard_name' => 'api'
]);

$chefProjetRole->syncPermissions([
    // Chantiers
    'view_chantiers',

    // Projets
    'view_projets',
    

    // Phases
    'view_phases',
    'create_phases',
    'edit_phases',
    'delete_phases',

    // Dépenses
    'view_depenses',
    'create_depenses',
    'edit_depenses',
    'delete_depenses',

    // Produits
    'view_produits',

    // Documents
    'view_documents',
    'create_documents',
    'delete_documents',

    // Évènements
    'view_evenements',
    'create_evenements',
    'edit_evenements',
    'delete_evenements',

    // Dashboard
    'view_dashboard',
]);


$users = User::all();

foreach ($users as $user) {

    if ($user->role) {
        $user->syncRoles([$user->role]);
    }

}
        $this->command->info('✅ Rôles et permissions créés avec succès.');

    }
}