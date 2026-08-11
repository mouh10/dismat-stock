<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gstock_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titre');
            $table->text('message')->nullable();
            $table->enum('type', ['stock', 'creance', 'dette', 'vente', 'info', 'alerte'])->default('info');
            $table->string('lien')->nullable();
            $table->boolean('lu')->default(false);
            $table->boolean('important')->default(false);
            $table->timestamps();
        });

        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entite_type')->nullable();
            $table->unsignedBigInteger('entite_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
        Schema::dropIfExists('gstock_notifications');
    }
};
