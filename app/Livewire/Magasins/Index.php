<?php

namespace App\Livewire\Magasins;

use App\Models\Magasin;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Ce magasin sera définitivement supprimé.';

    public string $nom = '';
    public string $adresse = '';
    public string $ville = '';
    public string $pays = 'Sénégal';
    public string $telephone = '';
    public string $email = '';
    public bool $est_principal = false;
    public bool $actif = true;

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'est_principal' => 'boolean',
            'actif' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        //
    }

    public function create()
    {
        $this->reset(['nom', 'adresse', 'ville', 'telephone', 'email', 'editingId']);
        $this->pays = 'Sénégal';
        $this->est_principal = false;
        $this->actif = true;
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $m = Magasin::findOrFail($id);
        $this->editingId = $m->id;
        $this->nom = $m->nom;
        $this->adresse = (string) $m->adresse;
        $this->ville = (string) $m->ville;
        $this->pays = (string) ($m->pays ?: 'Sénégal');
        $this->telephone = (string) $m->telephone;
        $this->email = (string) $m->email;
        $this->est_principal = (bool) $m->est_principal;
        $this->actif = (bool) $m->actif;
        $this->showModal = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->est_principal) {
            Magasin::query()->update(['est_principal' => false]);
        }

        if ($this->editingId) {
            Magasin::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Magasin mis à jour.');
        } else {
            Magasin::create($data);
            session()->flash('success', 'Magasin créé.');
        }

        $this->showModal = false;
        $this->reset(['nom', 'adresse', 'ville', 'telephone', 'email', 'editingId']);
    }

    public function toggleActif(int $id)
    {
        $m = Magasin::findOrFail($id);
        $m->update(['actif' => ! $m->actif]);
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
        Magasin::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Magasin supprimé.');
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        $magasins = Magasin::when($this->search, fn ($q) => $q->where('nom', 'ilike', "%{$this->search}%"))
            ->orderByDesc('est_principal')
            ->orderBy('nom')
            ->get();

        $nbTotal = Magasin::count();

        return view('livewire.magasins.index', compact('magasins', 'nbTotal'))
            ->layout('layouts.app', ['title' => 'Magasins']);
    }
}
