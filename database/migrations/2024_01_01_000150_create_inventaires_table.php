<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasins')->cascadeOnDelete();
            $table->string('nom')->nullable();
            $table->date('date');
            $table->enum('statut', ['en_cours', 'valide', 'annule'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('produits')->cascadeOnDelete();
            $table->decimal('stock_systeme', 12, 2)->default(0);
            $table->decimal('stock_physique', 12, 2)->nullable();
            $table->decimal('ecart', 12, 2)->nullable();
            $table->decimal('valorisation_ecart', 12, 2)->nullable();
            $table->boolean('checked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
    }
};
