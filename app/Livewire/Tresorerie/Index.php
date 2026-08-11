<?php

namespace App\Livewire\Tresorerie;

use App\Models\Depense;
use App\Models\RevenuDivers;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public string $type = 'depense'; // depense | revenu

    public string $categorie = '';
    public string $motif_source = '';
    public float $montant = 0;
    public string $mode_paiement = 'especes';
    public string $date_mouvement;

    public function mount()
    {
        $this->date_mouvement = today()->toDateString();
    }

    public function openModal(string $type)
    {
        $this->type = $type;
        $this->reset(['categorie', 'motif_source', 'montant']);
        $this->date_mouvement = today()->toDateString();
        $this->mode_paiement = 'especes';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'categorie' => 'required|string|max:255',
            'motif_source' => 'required|string|max:255',
            'montant' => 'required|numeric|gt:0',
            'date_mouvement' => 'required|date',
        ]);

        if ($this->type === 'depense') {
            Depense::create([
                'categorie' => $this->categorie,
                'motif' => $this->motif_source,
                'montant' => $this->montant,
                'mode_paiement' => $this->mode_paiement,
                'date_depense' => $this->date_mouvement,
                'utilisateur_id' => auth()->id(),
            ]);
        } else {
            RevenuDivers::create([
                'categorie' => $this->categorie,
                'source' => $this->motif_source,
                'montant' => $this->montant,
                'mode_paiement' => $this->mode_paiement,
                'date_revenu' => $this->date_mouvement,
                'utilisateur_id' => auth()->id(),
            ]);
        }

        session()->flash('success', 'Mouvement de trésorerie enregistré.');
        $this->showModal = false;
    }

    public function render()
    {
        $depenses = Depense::latest('date_depense')->take(15)->get();
        $revenus = RevenuDivers::latest('date_revenu')->take(15)->get();

        $totalDepenses = Depense::whereMonth('date_depense', now()->month)->sum('montant');
        $totalRevenus = RevenuDivers::whereMonth('date_revenu', now()->month)->sum('montant');

        return view('livewire.tresorerie.index', compact('depenses', 'revenus', 'totalDepenses', 'totalRevenus'))
            ->layout('layouts.app', ['title' => 'Trésorerie']);
    }
}
