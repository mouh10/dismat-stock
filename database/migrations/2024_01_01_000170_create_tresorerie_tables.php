<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorie_tresoreries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('type', ['depense', 'revenu']);
            $table->string('label');
            $table->string('value');
            $table->timestamps();
        });

        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('magasin_id')->nullable()->constrained('magasins')->nullOnDelete();
            $table->string('categorie');
            $table->string('motif');
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('mode_paiement')->default('especes');
            $table->date('date_depense');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('revenu_divers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('magasin_id')->nullable()->constrained('magasins')->nullOnDelete();
            $table->string('categorie');
            $table->string('source');
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('mode_paiement')->default('especes');
            $table->date('date_revenu');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenu_divers');
        Schema::dropIfExists('depenses');
        Schema::dropIfExists('categorie_tresoreries');
    }
};
