<div class="space-y-4">
    <x-page-header title="Clients" :subtitle="$subtitle">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary whitespace-nowrap">+ Nouveau Client</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un client..."
               class="field sm:w-72">
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <select wire:model.live="filterType" class="field sm:w-44">
            <option value="">Tous les types</option>
            <option value="particulier">Particulier</option>
            <option value="entreprise">Entreprise</option>
        </select>

        <label class="flex items-center gap-2 text-sm text-slate-600 px-3 py-2 rounded-lg border border-slate-300 bg-white shadow-sm cursor-pointer">
            <input type="checkbox" wire:model.live="filterAvecCreance" class="rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer">
            Avec créance uniquement
        </label>

        @if ($search || $filterType || $filterAvecCreance)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($clients as $c)
            <div wire:key="cli-{{ $c->id }}" class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-full bg-brand-500 text-white flex items-center justify-center font-display font-semibold shrink-0">
                            {{ mb_substr($c->nom, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-display font-semibold text-ink-950 truncate">{{ $c->nom }} {{ $c->prenom }}</h3>
                            <span class="badge border border-slate-200 text-slate-600 mt-1 capitalize">{{ $c->type_client }}</span>
                        </div>
                    </div>
                    <div class="relative shrink-0" x-data="{ open: false }" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = !open" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="more-vertical" class="w-4 h-4" />
                        </button>
                        <div x-show="open" x-transition style="display: none;" class="absolute right-0 mt-1 w-36 bg-white rounded-lg border border-slate-200 shadow-lg py-1 z-20">
                            <button type="button" x-on:click="open = false" wire:click="edit({{ $c->id }})" class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Modifier</button>
                            <button type="button" x-on:click="open = false" wire:click="confirmDelete({{ $c->id }})" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Supprimer</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5 text-sm text-slate-600">
                    @if ($c->telephone)
                        <p class="flex items-center gap-2"><x-icon name="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $c->telephone }}</p>
                    @endif
                    @if ($c->email)
                        <p class="flex items-center gap-2 truncate"><x-icon name="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $c->email }}</p>
                    @endif
                    @if ($c->adresse || $c->ville)
                        <p class="flex items-center gap-2 truncate"><x-icon name="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ $c->adresse }}@if($c->adresse && $c->ville), @endif{{ $c->ville }}</p>
                    @endif
                </div>

                @if ($c->solde_creance > 0)
                    <div class="flex items-center justify-between mt-4 p-3 rounded-lg bg-red-50 border border-red-100">
                        <span class="flex items-center gap-2 text-sm font-medium text-red-700">
                            <x-icon name="credit-card" class="w-4 h-4" /> Créance
                        </span>
                        <span class="font-display font-bold text-red-700">{{ number_format($c->solde_creance, 0, ',', ' ') }} F CFA</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">👥</p>
                <p class="text-sm">Aucun client ne correspond à cette recherche.</p>
            </div>
        @endforelse
    </div>

    @if ($clients->hasPages())
        <div class="card p-3">{{ $clients->links() }}</div>
    @endif

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">{{ $editingId ? 'Modifier le client' : 'Nouveau Client' }}</h3>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $editingId ? 'Modifiez les informations du client' : 'Ajoutez un nouveau client à votre carnet' }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Informations client</h4>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                                <input type="text" wire:model="nom" placeholder="Gueye" class="field">
                                @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                                <input type="text" wire:model="prenom" placeholder="Mouhamed" class="field">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                                <input type="text" wire:model="telephone" placeholder="77 000 00 00" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" wire:model="email" placeholder="client@email.com" class="field">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                            <input type="text" wire:model="adresse" placeholder="Adresse complète" class="field">
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ville</label>
                                <input type="text" wire:model="ville" placeholder="Dakar" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                                <select wire:model="type_client" class="field">
                                    <option value="particulier">Particulier</option>
                                    <option value="entreprise">Entreprise</option>
                                </select>
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
