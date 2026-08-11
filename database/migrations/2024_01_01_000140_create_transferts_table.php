<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('magasin_source_id')->constrained('magasins')->cascadeOnDelete();
            $table->foreignId('magasin_dest_id')->constrained('magasins')->cascadeOnDelete();
            $table->decimal('qte', 12, 2)->default(0);
            $table->enum('statut', ['en_cours', 'valide', 'rejete'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};
