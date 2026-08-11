<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achats', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('num_achat');
            $table->string('mode_paiement')->nullable()->default('especes')->after('montant_paye');
            $table->boolean('inclure_tva')->default(false)->after('taux_tva');
        });
    }

    public function down(): void
    {
        Schema::table('achats', function (Blueprint $table) {
            $table->dropColumn(['reference', 'mode_paiement', 'inclure_tva']);
        });
    }
};
