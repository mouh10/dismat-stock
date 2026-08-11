<?php

namespace App\Livewire\Fournisseurs;

use App\Models\Fournisseur;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Ce fournisseur sera définitivement supprimé.';

    public string $nom = '';
    public string $personne_contact = '';
    public string $telephone = '';
    public string $email = '';
    public string $adresse = '';
    public string $ville = '';
    public string $ninea = '';

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'personne_contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'ninea' => 'nullable|string|max:100',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->reset(['nom', 'personne_contact', 'telephone', 'email', 'adresse', 'ville', 'ninea', 'editingId']);
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $f = Fournisseur::findOrFail($id);
        $this->editingId = $f->id;
        $this->nom = $f->nom;
        $this->personne_contact = (string) $f->personne_contact;
        $this->telephone = (string) $f->telephone;
        $this->email = (string) $f->email;
        $this->adresse = (string) $f->adresse;
        $this->ville = (string) $f->ville;
        $this->ninea = (string) $f->ninea;
        $this->showModal = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            Fournisseur::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Fournisseur mis à jour.');
        } else {
            Fournisseur::create($data);
            session()->flash('success', 'Fournisseur créé.');
        }

        $this->showModal = false;
        $this->reset(['nom', 'personne_contact', 'telephone', 'email', 'adresse', 'ville', 'ninea', 'editingId']);
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
        Fournisseur::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Fournisseur supprimé.');
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        $fournisseurs = Fournisseur::when($this->search, fn ($q) => $q->where('nom', 'ilike', "%{$this->search}%"))
            ->orderBy('nom')
            ->paginate(10);

        $nbTotal = Fournisseur::count();

        return view('livewire.fournisseurs.index', compact('fournisseurs', 'nbTotal'))
            ->layout('layouts.app', ['title' => 'Fournisseurs']);
    }
}
