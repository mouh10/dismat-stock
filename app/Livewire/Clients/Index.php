<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'type')]
    public string $filterType = '';
    #[Url(as: 'creance')]
    public bool $filterAvecCreance = false;

    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Ce client sera définitivement supprimé.';

    public string $nom = '';
    public string $prenom = '';
    public string $telephone = '';
    public string $email = '';
    public string $adresse = '';
    public string $ville = '';
    public string $type_client = 'particulier';

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'type_client' => 'required|in:particulier,entreprise',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterAvecCreance() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterAvecCreance']);
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['nom', 'prenom', 'telephone', 'email', 'adresse', 'ville', 'editingId']);
        $this->type_client = 'particulier';
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $c = Client::findOrFail($id);
        $this->editingId = $c->id;
        $this->nom = $c->nom;
        $this->prenom = (string) $c->prenom;
        $this->telephone = (string) $c->telephone;
        $this->email = (string) $c->email;
        $this->adresse = (string) $c->adresse;
        $this->ville = (string) $c->ville;
        $this->type_client = $c->type_client;
        $this->showModal = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            Client::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Client mis à jour.');
        } else {
            Client::create($data);
            session()->flash('success', 'Client créé.');
        }

        $this->showModal = false;
        $this->reset(['nom', 'prenom', 'telephone', 'email', 'adresse', 'ville', 'editingId']);
    }

    public function confirmDelete(int $id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
    }

    public function delete()
    {
        if (! $this->confirmingDeleteId) {
            return;
        }
        Client::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Client supprimé.');
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        $clients = Client::when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('nom', 'ilike', "%{$this->search}%")
                   ->orWhere('prenom', 'ilike', "%{$this->search}%")
                   ->orWhere('telephone', 'ilike', "%{$this->search}%");
            }))
            ->when($this->filterType, fn ($q) => $q->where('type_client', $this->filterType))
            ->when($this->filterAvecCreance, fn ($q) => $q->where('solde_creance', '>', 0))
            ->orderBy('nom')
            ->paginate(10);

        $nbTotal = Client::count();
        $totalCreances = (float) Client::sum('solde_creance');

        $subtitle = "{$nbTotal} client(s)";
        if ($totalCreances > 0) {
            $subtitle .= " - <span class='text-red-600 font-semibold'>" . number_format($totalCreances, 0, ',', ' ') . " F CFA</span> en créances";
        }

        return view('livewire.clients.index', compact('clients', 'nbTotal', 'totalCreances', 'subtitle'))
            ->layout('layouts.app', ['title' => 'Clients']);
    }
}
