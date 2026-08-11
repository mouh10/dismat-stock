<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Creance;
use App\Models\DetteFournisseur;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Produit;
use Livewire\Component;

class Dashboard extends Component
{
    protected function variation(float $actuel, float $precedent): array
    {
        if ($precedent <= 0) {
            return ['up' => $actuel >= 0, 'pct' => $actuel > 0 ? 100.0 : 0.0];
        }

        $pct = (($actuel - $precedent) / $precedent) * 100;

        return ['up' => $pct >= 0, 'pct' => abs($pct)];
    }

    public function render()
    {
        $mesVentes = ! auth()->user()->hasFullAccess();

        $ventesJour = (float) Facture::whereDate('date_facture', today())
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->sum('montant_ttc');
        $ventesHier = (float) Facture::whereDate('date_facture', today()->subDay())
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->sum('montant_ttc');
        $nbFacturesJour = Facture::whereDate('date_facture', today())
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->count();

        $ventesMois = (float) Facture::whereMonth('date_facture', now()->month)
            ->whereYear('date_facture', now()->year)
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->sum('montant_ttc');
        $ventesMoisDernier = (float) Facture::whereMonth('date_facture', now()->subMonthNoOverflow()->month)
            ->whereYear('date_facture', now()->subMonthNoOverflow()->year)
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->sum('montant_ttc');

        $varJour = $this->variation($ventesJour, $ventesHier);
        $varMois = $this->variation($ventesMois, $ventesMoisDernier);

        // Créances/dettes/clients : données partagées de la boutique, visibles par tous les rôles
        $totalCreances = (float) Creance::whereIn('statut', ['en_cours', 'partiel'])->sum('montant_restant');
        $totalDettes = (float) DetteFournisseur::whereIn('statut', ['en_cours', 'partiel'])->sum('montant_restant');
        $nbClients = Client::count();

        // Évolution des ventes sur les 7 derniers jours (pour le graphique)
        $debutPeriode = today()->subDays(6);
        $ventesParJour = Facture::selectRaw('date_facture, SUM(montant_ttc) as total')
            ->where('date_facture', '>=', $debutPeriode)
            ->when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->groupBy('date_facture')
            ->pluck('total', 'date_facture');

        $chartLabels = [];
        $chartData = [];
        for ($i = 0; $i < 7; $i++) {
            $jour = $debutPeriode->copy()->addDays($i);
            $chartLabels[] = ucfirst($jour->translatedFormat('D j'));
            $chartData[] = (float) ($ventesParJour[$jour->toDateString()] ?? 0);
        }

        // Top produits du mois par revenu
        $topProduits = FactureItem::query()
            ->join('factures', 'factures.id', '=', 'facture_items.facture_id')
            ->whereMonth('factures.date_facture', now()->month)
            ->whereYear('factures.date_facture', now()->year)
            ->when($mesVentes, fn ($q) => $q->where('factures.utilisateur_id', auth()->id()))
            ->selectRaw('facture_items.designation, SUM(facture_items.qte) as total_vendu, SUM(facture_items.total_ht) as revenue')
            ->groupBy('facture_items.designation')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Alertes stock bas : données partagées de la boutique
        $produitsEnAlerte = Produit::where('est_stockable', true)
            ->where('actif', true)
            ->withSum('stocks as stock_total', 'quantite')
            ->get()
            ->filter(fn ($p) => (float) ($p->stock_total ?? 0) <= $p->stock_min)
            ->take(5);

        $nbProduits = Produit::where('actif', true)->count();

        $margeJour = (float) FactureItem::join('factures', 'factures.id', '=', 'facture_items.facture_id')
            ->join('produits', 'produits.id', '=', 'facture_items.product_id')
            ->whereDate('factures.date_facture', today())
            ->when($mesVentes, fn ($q) => $q->where('factures.utilisateur_id', auth()->id()))
            ->selectRaw('COALESCE(SUM(facture_items.total_ht - (produits.prix_achat * facture_items.qte)), 0) as marge')
            ->value('marge');

        $derniereVente = Facture::when($mesVentes, fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->latest('created_at')
            ->first();

        return view('livewire.dashboard', [
            'mesVentes' => $mesVentes,
            'ventesJour' => $ventesJour,
            'nbFacturesJour' => $nbFacturesJour,
            'varJour' => $varJour,
            'ventesMois' => $ventesMois,
            'varMois' => $varMois,
            'totalCreances' => $totalCreances,
            'nbClients' => $nbClients,
            'totalDettes' => $totalDettes,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'topProduits' => $topProduits,
            'produitsEnAlerte' => $produitsEnAlerte,
            'nbProduits' => $nbProduits,
            'margeJour' => $margeJour,
            'derniereVente' => $derniereVente,
        ])->layout('layouts.app', ['title' => 'Tableau de bord']);
    }
}
