<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiement_abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('plan');
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('paytech_token')->nullable();
            $table->string('statut')->default('en_attente');
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_fin_abonnement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_abonnements');
    }
};
