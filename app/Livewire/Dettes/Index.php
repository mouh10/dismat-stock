<?php

namespace App\Livewire\Dettes;

use App\Models\DetteFournisseur;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'retard')]
    public bool $filterEnRetard = false;

    public bool $showPayModal = false;
    public ?int $detteId = null;
    public float $montant = 0;
    public string $mode_paiement = 'especes';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterEnRetard() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterEnRetard']);
        $this->resetPage();
    }

    public function openPay(int $id)
    {
        $this->detteId = $id;
        $this->montant = 0;
        $this->mode_paiement = 'especes';
        $this->showPayModal = true;
    }

    public function enregistrerPaiement()
    {
        $this->validate(['montant' => 'required|numeric|gt:0']);

        $dette = DetteFournisseur::findOrFail($this->detteId);

        if ($this->montant > (float) $dette->montant_restant) {
            $this->addError('montant', 'Le montant dépasse le solde restant (' . number_format($dette->montant_restant, 0, ',', ' ') . ' F).');
            return;
        }

        $montant = $this->montant;

        DB::transaction(function () use ($dette, $montant) {
            Paiement::create([
                'dette_fournisseur_id' => $dette->id,
                'utilisateur_id' => auth()->id(),
                'mode_paiement' => $this->mode_paiement,
                'montant' => $montant,
                'date_paiement' => today(),
            ]);

            $restant = (float) $dette->montant_restant - $montant;
            $dette->update([
                'montant_restant' => max(0, $restant),
                'statut' => $restant <= 0 ? 'reglee' : 'partiel',
            ]);
        });

        session()->flash('success', 'Paiement enregistré.');
        $this->showPayModal = false;
    }

    public function render()
    {
        $dettes = DetteFournisseur::with('fournisseur')
            ->whereIn('statut', ['en_cours', 'partiel'])
            ->when($this->search, fn ($q) => $q->whereHas('fournisseur', fn ($q2) => $q2->where('nom', 'ilike', "%{$this->search}%")))
            ->when($this->filterEnRetard, fn ($q) => $q->whereNotNull('date_echeance')->where('date_echeance', '<', today()))
            ->latest()
            ->paginate(10);

        $nbTotal = DetteFournisseur::whereIn('statut', ['en_cours', 'partiel'])->count();
        $totalRestant = (float) DetteFournisseur::whereIn('statut', ['en_cours', 'partiel'])->sum('montant_restant');

        return view('livewire.dettes.index', compact('dettes', 'nbTotal', 'totalRestant'))
            ->layout('layouts.app', ['title' => 'Dettes fournisseurs']);
    }
}
