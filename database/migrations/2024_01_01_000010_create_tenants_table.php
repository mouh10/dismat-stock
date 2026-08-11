<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('subdomain')->nullable()->unique();
            $table->enum('plan', ['essentiel', 'pro', 'entreprise'])->default('essentiel');
            $table->string('logo_url')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->nullable()->default('Sénégal');
            $table->string('ninea')->nullable();
            $table->string('rccm')->nullable();
            $table->enum('status', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->decimal('tva_defaut', 5, 2)->nullable()->default(18);
            $table->string('format_num_facture')->nullable();
            $table->text('mentions_legales')->nullable();
            $table->boolean('alerte_stock_bas')->default(true);
            $table->boolean('creances_echeance')->default(true);
            $table->boolean('rapports_quotidiens')->default(false);
            $table->boolean('activer_multi_magasin')->default(false);
            $table->string('subscription_status')->nullable()->default('trial');
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_period')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
