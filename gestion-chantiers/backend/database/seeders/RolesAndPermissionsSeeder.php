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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_chantiers',
            'create_chantiers',
            'edit_chantiers',
            'delete_chantiers',

            'view_projets',
            'create_projets',
            'edit_projets',
            'delete_projets',

            'view_phases',
            'create_phases',
            'edit_phases',
            'delete_phases',

            'view_depenses',
            'create_depenses',
            'edit_depenses',
            'delete_depenses',

            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',

            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            'view_produits',
            'create_produits',
            'edit_produits',
            'delete_produits',

            'view_fournisseurs',
            'create_fournisseurs',
            'edit_fournisseurs',
            'delete_fournisseurs',

            'view_stocks',
            'manage_stocks',      // affecter des produits à un stock

            'view_mouvements',
            'create_entree',
            'create_sortie',
            'create_transfert',
            'delete_mouvement',

            'view_documents',
            'create_documents',
            'delete_documents',

            'view_evenements',
            'create_evenements',
            'edit_evenements',
            'delete_evenements',

            'view_historique',

            'view_dashboard',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }


        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::all());

        $responsableRole = Role::firstOrCreate(['name' => 'responsable', 'guard_name' => 'api']);
        $responsableRole->syncPermissions([
            'view_chantiers',

            'view_projets',
            'create_projets',
            'edit_projets',
            'delete_projets',

            'view_phases',
            'create_phases',
            'edit_phases',
            'delete_phases',

            'view_depenses',
            'create_depenses',
            'edit_depenses',
            'delete_depenses',

            'view_clients',

            'view_produits',

            'view_documents',
            'create_documents',
            'delete_documents',

            'view_evenements',
            'create_evenements',
            'edit_evenements',
            'delete_evenements',

            'view_dashboard',
        ]);

        $magasinierRole = Role::firstOrCreate(['name' => 'magasinier', 'guard_name' => 'api']);
        $magasinierRole->syncPermissions([
            'view_produits',
            'create_produits',
            'edit_produits',
            'delete_produits',

            'view_fournisseurs',
            'create_fournisseurs',
            'edit_fournisseurs',
            'delete_fournisseurs',

            'view_stocks',
            'manage_stocks',

            'view_mouvements',
            'create_entree',
            'create_sortie',
            'create_transfert',
            'delete_mouvement',

            'view_chantiers',

            'view_dashboard',
        ]);

        $chefProjetRole = Role::firstOrCreate([
    'name' => 'chef_projet',
    'guard_name' => 'api'
]);

$chefProjetRole->syncPermissions([
    'view_chantiers',

    'view_projets',
    
    'view_phases',
    'create_phases',
    'edit_phases',
    'delete_phases',

    'view_depenses',
    'create_depenses',
    'edit_depenses',
    'delete_depenses',

    'view_produits',
 
    'view_documents',
    'create_documents',
    'delete_documents',

    'view_evenements',
    'create_evenements',
    'edit_evenements',
    'delete_evenements',

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