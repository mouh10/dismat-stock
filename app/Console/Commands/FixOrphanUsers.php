<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

class FixOrphanUsers extends Command
{
    protected $signature = 'users:fix-tenant';

    protected $description = "Assigne un tenant_id aux utilisateurs qui n'en ont pas (bug de création corrigé)";

    public function handle(): int
    {
        $orphans = User::whereNull('tenant_id')->get();

        if ($orphans->isEmpty()) {
            $this->info('Aucun utilisateur orphelin trouvé. Rien à faire.');
            return self::SUCCESS;
        }

        $tenant = Tenant::first();

        if (! $tenant) {
            $this->error('Aucun tenant trouvé en base — impossible de corriger.');
            return self::FAILURE;
        }

        foreach ($orphans as $user) {
            $user->update(['tenant_id' => $tenant->id]);
            $this->line("Corrigé : {$user->email}");
        }

        $this->info("{$orphans->count()} utilisateur(s) corrigé(s) avec le tenant « {$tenant->nom} ».");
        return self::SUCCESS;
    }
}
