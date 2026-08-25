<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magasin_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'magasin_id']);
        });

        // Pour ne rien casser : chaque gestionnaire déjà assigné à un magasin unique
        // (via users.magasin_id) est rattaché à ce même magasin dans la nouvelle table.
        DB::table('users')
            ->where('role', 'gestionnaire')
            ->whereNotNull('magasin_id')
            ->get(['id', 'magasin_id'])
            ->each(function ($user) {
                DB::table('magasin_user')->insertOrIgnore([
                    'user_id' => $user->id,
                    'magasin_id' => $user->magasin_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('magasin_user');
    }
};
