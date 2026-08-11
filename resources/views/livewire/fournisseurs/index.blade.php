<div class="space-y-4">
    <x-page-header title="Fournisseurs" subtitle="{{ $nbTotal }} fournisseur(s)">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary">+ Nouveau Fournisseur</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un fournisseur..."
               class="field sm:w-72">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($fournisseurs as $f)
            <div wire:key="four-{{ $f->id }}" class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <x-icon name="truck" class="w-5 h-5 text-white" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-display font-semibold text-ink-950 truncate">{{ $f->nom }}</h3>
                            @if ($f->personne_contact)
                                <p class="text-sm text-slate-500 truncate">{{ $f->personne_contact }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="relative shrink-0" x-data="{ open: false }" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = !open" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="more-vertical" class="w-4 h-4" />
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 mt-1 w-36 bg-white rounded-lg border border-slate-200 shadow-lg py-1 z-20">
                            <button type="button" x-on:click="open = false" wire:click="edit({{ $f->id }})" class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Modifier</button>
                            <button type="button" x-on:click="open = false" wire:click="confirmDelete({{ $f->id }})" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Supprimer</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5 text-sm text-slate-600">
                    @if ($f->telephone)
                        <p class="flex items-center gap-2"><x-icon name="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $f->telephone }}</p>
                    @endif
                    @if ($f->email)
                        <p class="flex items-center gap-2 truncate"><x-icon name="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $f->email }}</p>
                    @endif
                    @if ($f->adresse || $f->ville)
                        <p class="flex items-center gap-2 truncate"><x-icon name="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $f->adresse }}@if($f->adresse && $f->ville), @endif{{ $f->ville }}</p>
                    @endif
                </div>

                @if ($f->ninea)
                    <p class="text-sm text-slate-400 mt-3 pt-3 border-t border-slate-100">NINEA: {{ $f->ninea }}</p>
                @endif
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">🚚</p>
                <p class="text-sm">Aucun fournisseur ne correspond à cette recherche.</p>
            </div>
        @endforelse
    </div>

    @if ($fournisseurs->hasPages())
        <div class="card p-3">{{ $fournisseurs->links() }}</div>
    @endif

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">{{ $editingId ? 'Modifier le fournisseur' : 'Nouveau Fournisseur' }}</h3>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $editingId ? 'Modifiez les informations du fournisseur' : 'Ajoutez un nouveau fournisseur à votre carnet' }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="truck" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Informations fournisseur</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                            <input type="text" wire:model="nom" placeholder="Ex: Grossiste Sénégal SARL" class="field">
                            @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Personne à contacter</label>
                            <input type="text" wire:model="personne_contact" placeholder="Ex: Moussa Ba" class="field">
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                                <input type="text" wire:model="telephone" placeholder="+221 77 000 00 00" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" wire:model="email" placeholder="contact@fournisseur.com" class="field">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                            <input type="text" wire:model="adresse" placeholder="Adresse complète" class="field">
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ville</label>
                                <input type="text" wire:model="ville" placeholder="Ex: Dakar" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NINEA</label>
                                <input type="text" wire:model="ninea" placeholder="NINEA" class="field">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
