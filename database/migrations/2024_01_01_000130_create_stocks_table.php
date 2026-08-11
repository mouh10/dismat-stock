<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasins')->cascadeOnDelete();
            $table->decimal('quantite', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['produit_id', 'magasin_id']);
        });

        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasins')->cascadeOnDelete();
            $table->enum('type', ['entree', 'sortie', 'transfert', 'ajustement', 'inventaire']);
            $table->decimal('quantite', 12, 2)->default(0);
            $table->decimal('stock_avant', 12, 2)->nullable();
            $table->decimal('stock_apres', 12, 2)->nullable();
            $table->string('motif')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvement_stocks');
        Schema::dropIfExists('stocks');
    }
};
