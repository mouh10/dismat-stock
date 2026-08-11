<?php

namespace App\Livewire\Creances;

use App\Models\Creance;
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
    public ?int $creanceId = null;
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
        $this->creanceId = $id;
        $this->montant = 0;
        $this->mode_paiement = 'especes';
        $this->showPayModal = true;
    }

    public function enregistrerPaiement()
    {
        $this->validate(['montant' => 'required|numeric|gt:0']);

        $creance = Creance::findOrFail($this->creanceId);

        if ($this->montant > (float) $creance->montant_restant) {
            $this->addError('montant', 'Le montant dépasse le solde restant (' . number_format($creance->montant_restant, 0, ',', ' ') . ' F).');
            return;
        }

        $montant = $this->montant;

        DB::transaction(function () use ($creance, $montant) {
            Paiement::create([
                'creance_id' => $creance->id,
                'utilisateur_id' => auth()->id(),
                'mode_paiement' => $this->mode_paiement,
                'montant' => $montant,
                'date_paiement' => today(),
            ]);

            $restant = (float) $creance->montant_restant - $montant;
            $creance->update([
                'montant_restant' => max(0, $restant),
                'montant_acompte' => (float) $creance->montant_acompte + $montant,
                'statut' => $restant <= 0 ? 'reglee' : 'partiel',
            ]);

            if ($creance->client) {
                $creance->client->decrement('solde_creance', $montant);
            }
        });

        session()->flash('success', 'Paiement enregistré.');
        $this->showPayModal = false;
    }

    public function render()
    {
        $creances = Creance::with('client')
            ->whereIn('statut', ['en_cours', 'partiel'])
            ->when($this->search, fn ($q) => $q->whereHas('client', fn ($q2) => $q2->where('nom', 'ilike', "%{$this->search}%")))
            ->when($this->filterEnRetard, fn ($q) => $q->whereNotNull('date_echeance')->where('date_echeance', '<', today()))
            ->latest()
            ->paginate(10);

        $nbTotal = Creance::whereIn('statut', ['en_cours', 'partiel'])->count();
        $totalRestant = (float) Creance::whereIn('statut', ['en_cours', 'partiel'])->sum('montant_restant');

        return view('livewire.creances.index', compact('creances', 'nbTotal', 'totalRestant'))
            ->layout('layouts.app', ['title' => 'Créances clients']);
    }
}
