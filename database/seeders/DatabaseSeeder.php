<?php

namespace Database\Seeders;

use App\Models\Magasin;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Crée ta boutique et ton compte administrateur.
     * Modifie les valeurs ci-dessous avec tes vraies informations avant de lancer
     * "php artisan db:seed" (ou change-les directement après ta première connexion
     * dans Paramètres > Informations de la boutique / Équipe).
     */
    public function run(): void
    {
        $tenant = Tenant::create([
            'nom' => 'DISMAT',
            'plan' => 'entreprise',
            'status' => 'actif',
            'subscription_status' => 'actif',
            'tva_defaut' => 18,
            'pays' => 'Sénégal',
        ]);

        $magasin = Magasin::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenant->id,
            'nom' => 'Magasin principal',
            'est_principal' => true,
            'actif' => true,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'magasin_id' => $magasin->id,
            'nom' => 'Admin',                     // <- Ton nom
            'email' => 'mouhamed@dismat.sn',          // <- Ton email de connexion
            'password' => Hash::make('Rassoul@2025'), // <- Ton mot de passe (à changer !)
            'role' => 'admin',
            'actif' => true,
        ]);

        //$this->command->info('Compte créé : admin@dismat.sn / changeme123');
        //$this->command->warn('Pense à changer ces identifiants (email + mot de passe) dans ce fichier avant de lancer le seeder, ou depuis Équipe une fois connecté.');
    }
}
