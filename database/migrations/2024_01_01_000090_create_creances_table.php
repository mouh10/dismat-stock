<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->decimal('montant_initial', 12, 2)->default(0);
            $table->decimal('montant_restant', 12, 2)->default(0);
            $table->decimal('montant_acompte', 12, 2)->default(0);
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['en_cours', 'partiel', 'reglee', 'perte'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creances');
    }
};
