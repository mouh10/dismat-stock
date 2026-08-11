<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dettes_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->foreignId('achat_id')->nullable()->constrained('achats')->nullOnDelete();
            $table->decimal('montant_initial', 12, 2)->default(0);
            $table->decimal('montant_restant', 12, 2)->default(0);
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['en_cours', 'partiel', 'reglee'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dettes_fournisseurs');
    }
};
