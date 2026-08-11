<?php

namespace App\Livewire\Ventes;

use App\Models\Facture;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Historique extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'statut')]
    public string $filterStatut = '';
    #[Url(as: 'periode')]
    public string $filterPeriode = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterPeriode']);
        $this->resetPage();
    }

    protected function debutPeriode(): ?\Illuminate\Support\Carbon
    {
        return match ($this->filterPeriode) {
            'jour' => today(),
            'semaine' => now()->startOfWeek(),
            'mois' => now()->startOfMonth(),
            'annee' => now()->startOfYear(),
            default => null,
        };
    }

    public function render()
    {
        $debut = $this->debutPeriode();

        $query = Facture::with(['client', 'utilisateur'])
            ->when(! auth()->user()->hasFullAccess(), fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('num_facture', 'ilike', "%{$this->search}%")
                   ->orWhereHas('client', fn ($q3) => $q3->where('nom', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatut, fn ($q) => $q->where('statut', $this->filterStatut))
            ->when($debut, fn ($q) => $q->whereDate('date_facture', '>=', $debut));

        $totalPeriode = (clone $query)->sum('montant_ttc');

        $factures = $query->latest('date_facture')->latest('id')->paginate(10);

        $subtitle = "{$factures->total()} vente(s) - <span class='font-semibold text-ink-950'>" . number_format($totalPeriode, 0, ',', ' ') . ' F CFA</span>';
        if (! auth()->user()->hasFullAccess()) {
            $subtitle .= " <span class='text-slate-400'>(mes ventes uniquement)</span>";
        }

        return view('livewire.ventes.historique', compact('factures', 'totalPeriode', 'subtitle'))
            ->layout('layouts.app', ['title' => 'Ventes']);
    }
}
