<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('echanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_donneur_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('client_receveur_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('produit_donne_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->foreignId('produit_recu_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->string('imei_donne')->nullable();
            $table->string('imei_recu')->nullable();
            $table->decimal('valeur_appreciation', 12, 2)->nullable();
            $table->decimal('valeur_compense', 12, 2)->nullable();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echanges');
    }
};
