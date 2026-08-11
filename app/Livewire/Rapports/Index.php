<?php

namespace App\Livewire\Rapports;

use App\Models\Achat;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public string $periode = 'mois'; // jour | semaine | mois | annee

    public function render()
    {
        $debut = match ($this->periode) {
            'jour' => today(),
            'semaine' => now()->startOfWeek(),
            'annee' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $ventes = Facture::where('date_facture', '>=', $debut)->sum('montant_ttc');
        $achats = Achat::where('date_achat', '>=', $debut)->sum('montant_ttc');
        $nbVentes = Facture::where('date_facture', '>=', $debut)->count();

        $topProduits = FactureItem::query()
            ->join('factures', 'factures.id', '=', 'facture_items.facture_id')
            ->where('factures.date_facture', '>=', $debut)
            ->select('facture_items.designation', DB::raw('SUM(facture_items.qte) as qte_totale'), DB::raw('SUM(facture_items.total_ht) as montant_total'))
            ->groupBy('facture_items.designation')
            ->orderByDesc('qte_totale')
            ->take(8)
            ->get();

        $margeEstimee = $ventes - $achats;

        // Évolution du CA sur la période sélectionnée (regroupé par jour)
        $ventesParJour = Facture::where('date_facture', '>=', $debut)
            ->selectRaw('date_facture, SUM(montant_ttc) as total')
            ->groupBy('date_facture')
            ->orderBy('date_facture')
            ->get();

        $evolutionLabels = $ventesParJour->pluck('date_facture')->map(fn ($d) => $d->translatedFormat('d M'));
        $evolutionData = $ventesParJour->pluck('total')->map(fn ($v) => (float) $v);

        // Répartition des modes de paiement (sur les factures de la période)
        $repartitionPaiements = Paiement::whereHas('facture', fn ($q) => $q->where('date_facture', '>=', $debut))
            ->selectRaw('mode_paiement, SUM(montant) as total')
            ->groupBy('mode_paiement')
            ->get();

        return view('livewire.rapports.index', compact(
            'ventes', 'achats', 'nbVentes', 'topProduits', 'margeEstimee',
            'evolutionLabels', 'evolutionData', 'repartitionPaiements'
        ))->layout('layouts.app', ['title' => 'Rapports']);
    }
}
