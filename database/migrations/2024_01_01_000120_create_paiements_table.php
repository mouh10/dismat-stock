<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('nom');
            $table->string('code');
            $table->string('icon_name')->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->foreignId('creance_id')->nullable()->constrained('creances')->nullOnDelete();
            $table->foreignId('dette_fournisseur_id')->nullable()->constrained('dettes_fournisseurs')->nullOnDelete();
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode_paiement')->default('especes');
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->date('date_paiement');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('payment_methods');
    }
};
