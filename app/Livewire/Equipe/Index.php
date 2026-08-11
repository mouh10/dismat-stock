<?php

namespace App\Livewire\Equipe;

use App\Models\Magasin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'role')]
    public string $filterRole = '';
    #[Url(as: 'statut')]
    public string $filterActif = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmTitle = 'Retirer ce membre ?';
    public string $confirmMessage = "Ce membre perdra l'accès à l'application.";

    public string $nom = '';
    public string $prenom = '';
    public string $email = '';
    public string $telephone = '';
    public string $role = 'caissier';
    public ?int $magasin_id = null;
    public string $password = '';
    public bool $actif = true;

    protected function rules(): array
    {
        $emailRule = \Illuminate\Validation\Rule::unique('users', 'email');
        if ($this->editingId) {
            $emailRule = $emailRule->ignore($this->editingId);
        }

        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', $emailRule],
            'telephone' => 'nullable|string|max:30',
            'role' => 'required|in:admin,caissier,gestionnaire',
            'magasin_id' => 'nullable|exists:magasins,id',
            'actif' => 'boolean',
        ];

        if (! $this->editingId) {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }

    public function create()
    {
        $this->reset(['nom', 'prenom', 'email', 'telephone', 'magasin_id', 'password', 'editingId']);
        $this->role = 'caissier';
        $this->actif = true;
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->nom = $u->nom;
        $this->prenom = (string) $u->prenom;
        $this->email = $u->email;
        $this->telephone = (string) $u->telephone;
        $this->role = $u->role === 'super_admin' ? 'admin' : $u->role;
        $this->magasin_id = $u->magasin_id;
        $this->password = '';
        $this->actif = (bool) $u->actif;
        $this->showModal = true;
    }

    public function save()
    {
        $data = $this->validate();
        unset($data['password']);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }
            session()->flash('success', 'Membre mis à jour.');
        } else {
            $data['password'] = Hash::make($this->password);
            $data['tenant_id'] = auth()->user()->tenant_id;
            User::create($data);
            session()->flash('success', 'Membre ajouté à l\'équipe.');
        }

        $this->showModal = false;
        $this->reset(['nom', 'prenom', 'email', 'telephone', 'magasin_id', 'password', 'editingId']);
    }

    public function confirmDelete(int $id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas vous supprimer vous-même.');
            return;
        }
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
    }

    public function delete()
    {
        if (! $this->confirmingDeleteId || $this->confirmingDeleteId === auth()->id()) {
            $this->confirmingDeleteId = null;
            return;
        }
        User::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Membre retiré.');
        $this->confirmingDeleteId = null;
    }

    public function resetFilters()
    {
        $this->reset(['filterRole', 'filterActif']);
    }

    public function render()
    {
        $membres = User::with('magasin')
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->when($this->filterActif === 'actif', fn ($q) => $q->where('actif', true))
            ->when($this->filterActif === 'inactif', fn ($q) => $q->where('actif', false))
            ->orderBy('nom')
            ->get();

        $magasins = Magasin::orderBy('nom')->get();

        $nbTotal = User::count();

        return view('livewire.equipe.index', compact('membres', 'magasins', 'nbTotal'))
            ->layout('layouts.app', ['title' => 'Équipe']);
    }
}
