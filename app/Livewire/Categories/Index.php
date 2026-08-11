<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Cette catégorie sera définitivement supprimée.';

    public string $nom = '';
    public string $description = '';
    public string $couleur = '#10b981';
    public bool $active = true;

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:20',
            'active' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['nom', 'description', 'editingId']);
        $this->couleur = '#10b981';
        $this->active = true;
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $c = Category::findOrFail($id);
        $this->editingId = $c->id;
        $this->nom = $c->nom;
        $this->description = (string) $c->description;
        $this->couleur = $c->couleur ?? '#10b981';
        $this->active = (bool) $c->active;
        $this->showModal = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Catégorie mise à jour.');
        } else {
            Category::create($data);
            session()->flash('success', 'Catégorie créée.');
        }

        $this->showModal = false;
        $this->reset(['nom', 'description', 'editingId']);
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
        Category::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Catégorie supprimée.');
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        $categories = Category::withCount(['produits', 'fields'])
            ->when($this->search, fn ($q) => $q->where('nom', 'ilike', "%{$this->search}%"))
            ->orderBy('nom')
            ->paginate(12);

        $nbTotal = Category::count();

        return view('livewire.categories.index', compact('categories', 'nbTotal'))
            ->layout('layouts.app', ['title' => 'Catégories']);
    }
}
