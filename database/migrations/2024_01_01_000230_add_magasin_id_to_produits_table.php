<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->foreignId('magasin_id')->nullable()->after('tenant_id')->constrained('magasins')->nullOnDelete();
        });

        // Rattache les produits existants (créés avant cette mise à jour) au magasin
        // principal de leur boutique, pour que rien ne devienne invisible.
        DB::table('produits')->whereNull('magasin_id')->orderBy('tenant_id')->get()->each(function ($produit) {
            $magasinId = DB::table('magasins')
                ->where('tenant_id', $produit->tenant_id)
                ->orderByDesc('est_principal')
                ->value('id');

            if ($magasinId) {
                DB::table('produits')->where('id', $produit->id)->update(['magasin_id' => $magasinId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('magasin_id');
        });
    }
};
