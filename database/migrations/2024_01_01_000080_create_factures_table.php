<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasins')->cascadeOnDelete();
            $table->enum('type_doc', ['facture', 'ticket', 'devis', 'avoir'])->default('ticket');
            $table->string('num_facture')->unique();
            $table->enum('statut', ['brouillon', 'envoyee', 'payee', 'annulee', 'partiel'])->default('payee');
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('montant_remise', 12, 2)->default(0);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('partage_whatsapp')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'date_facture']);
        });

        Schema::create('facture_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->decimal('qte', 12, 2)->default(1);
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_items');
        Schema::dropIfExists('factures');
    }
};
