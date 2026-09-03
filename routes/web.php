<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    // Accessible à tous les rôles connectés : vente au comptoir et suivi client
    Route::middleware('role:admin,gestionnaire,caissier')->group(function () {
        Route::get('/caisse', \App\Livewire\Ventes\Caisse::class)->name('caisse.index');
        Route::get('/ventes', \App\Livewire\Ventes\Historique::class)->name('ventes.index');
        Route::get('/factures/{facture}/pdf', [\App\Http\Controllers\FactureController::class, 'pdf'])->name('factures.pdf');
        Route::get('/factures/{facture}/bon-livraison', [\App\Http\Controllers\FactureController::class, 'bonLivraison'])->name('factures.bon-livraison');
        Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
    });

    // Gestion opérationnelle : catalogue, stock, achats, finances — admin + gestionnaire
    Route::middleware('role:admin,gestionnaire')->group(function () {
        Route::get('/categories', \App\Livewire\Categories\Index::class)->name('categories.index');
        Route::get('/produits', \App\Livewire\Produits\Index::class)->name('produits.index');
        Route::get('/stocks', \App\Livewire\Stocks\Index::class)->name('stocks.index');
        Route::get('/achats', \App\Livewire\Achats\Index::class)->name('achats.index');
        Route::get('/fournisseurs', \App\Livewire\Fournisseurs\Index::class)->name('fournisseurs.index');
        Route::get('/creances', \App\Livewire\Creances\Index::class)->name('creances.index');
        Route::get('/dettes', \App\Livewire\Dettes\Index::class)->name('dettes.index');
        Route::get('/tresorerie', \App\Livewire\Tresorerie\Index::class)->name('tresorerie.index');
        Route::get('/rapports', \App\Livewire\Rapports\Index::class)->name('rapports.index');
    });

    // Administration : magasins, équipe, paramètres — admin uniquement
    Route::middleware('role:admin')->group(function () {
        Route::get('/magasins', \App\Livewire\Magasins\Index::class)->name('magasins.index');
        Route::get('/equipe', \App\Livewire\Equipe\Index::class)->name('equipe.index');
        Route::get('/parametres', \App\Livewire\Parametres\Index::class)->name('parametres.index');
    });
});
