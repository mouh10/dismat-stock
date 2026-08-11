<div class="space-y-4">
    <x-page-header title="Magasins" subtitle="Gérez vos points de vente et succursales">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary">+ Nouveau Magasin</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un magasin..." class="field sm:w-72">
        <span class="badge border border-slate-200 text-slate-600">{{ $nbTotal }} magasin(s)</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($magasins as $m)
            <div wire:key="mag-{{ $m->id }}" class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $m->est_principal ? 'bg-brand-500' : 'bg-brand-300' }}">
                            <x-icon name="store" class="w-5 h-5 text-white" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-display font-semibold text-ink-950 truncate">{{ $m->nom }}</h3>
                            @if ($m->est_principal)
                                <span class="badge border border-slate-200 text-slate-600 mt-1">Principal</span>
                            @endif
                        </div>
                    </div>
                    <div class="relative shrink-0" x-data="{ open: false }" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = !open" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="more-vertical" class="w-4 h-4" />
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 mt-1 w-36 bg-white rounded-lg border border-slate-200 shadow-lg py-1 z-20">
                            <button type="button" x-on:click="open = false" wire:click="edit({{ $m->id }})" class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Modifier</button>
                            <button type="button" x-on:click="open = false" wire:click="confirmDelete({{ $m->id }})" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Supprimer</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5 text-sm text-slate-600">
                    @if ($m->adresse || $m->ville)
                        <p class="flex items-center gap-2 truncate"><x-icon name="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $m->adresse }}@if($m->adresse && $m->ville), @endif{{ $m->ville }}</p>
                    @endif
                    @if ($m->telephone)
                        <p class="flex items-center gap-2"><x-icon name="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $m->telephone }}</p>
                    @endif
                    @if ($m->email)
                        <p class="flex items-center gap-2 truncate"><x-icon name="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $m->email }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                    <span class="text-sm text-slate-500">Statut</span>
                    <div class="flex items-center gap-2">
                        <span class="badge {{ $m->actif ? 'bg-ink-950 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $m->actif ? 'Actif' : 'Inactif' }}</span>
                        <x-toggle wire:click="toggleActif({{ $m->id }})" @checked($m->actif) />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">🏢</p>
                <p class="text-sm">Aucun magasin ne correspond à cette recherche.</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-lg max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">{{ $editingId ? 'Modifier le magasin' : 'Créer un nouveau magasin' }}</h3>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $editingId ? 'Modifiez les informations de ce point de vente' : 'Ajoutez un nouveau point de vente ou succursale' }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom du magasin *</label>
                            <input type="text" wire:model="nom" placeholder="Ex: Boutique Principale" class="field">
                            @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                            <input type="text" wire:model="adresse" placeholder="Ex: 123 Rue de la Liberté" class="field">
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ville</label>
                                <input type="text" wire:model="ville" placeholder="Dakar" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Pays</label>
                                <input type="text" wire:model="pays" placeholder="Sénégal" class="field">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                                <input type="text" wire:model="telephone" placeholder="+221 77 123 45 67" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" wire:model="email" placeholder="contact@magasin.com" class="field">
                            </div>
                        </div>

                        <label class="flex items-center gap-3 pt-1">
                            <x-toggle wire:model="est_principal" @checked($est_principal) />
                            <span class="text-sm font-medium text-ink-950">Magasin principal</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">{{ $editingId ? 'Enregistrer' : 'Créer' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
