<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@chantiers.com'],
            [
                'nom'      => 'Administrateur',
                'prenom'   => 'Super',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
                'est_actif' => true,
            ]
        );

        $this->command->info('Compte admin créé : admin@chantiers.com / Admin@1234');
    }
}