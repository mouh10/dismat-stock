<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->decimal('prix_achat', 12, 2)->default(0);
            $table->decimal('prix_vente', 12, 2)->default(0);
            $table->decimal('prix_vente_gros', 12, 2)->nullable();
            $table->integer('stock_min')->default(0);
            $table->string('code_barres')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('unite')->nullable()->default('unité');
            $table->string('type_produit')->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('est_stockable')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'designation']);
            $table->index('code_barres');
        });

        Schema::create('product_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('category_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_field_values');
        Schema::dropIfExists('produits');
    }
};
