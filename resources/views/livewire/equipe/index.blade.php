<div class="space-y-4">
    <x-page-header title="Gestion de l'équipe" subtitle="Gérez les accès et assignez vos employés aux points de vente" icon="users">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary">+ Nouvel Employé</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        <select wire:model.live="filterRole" class="field sm:w-44">
            <option value="">Tous les rôles</option>
            <option value="admin">Admin</option>
            <option value="gestionnaire">Gestionnaire</option>
            <option value="caissier">Caissier</option>
        </select>

        <select wire:model.live="filterActif" class="field sm:w-40">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
        </select>

        @if ($filterRole || $filterActif)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-4 py-3">Employé</th>
                    <th class="px-4 py-3">Rôle</th>
                    <th class="px-4 py-3">Point de vente</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($membres as $m)
                    <tr wire:key="user-{{ $m->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink-950">{{ $m->nom }} {{ $m->prenom }}</p>
                            <p class="text-xs text-slate-400">{{ $m->email }}</p>
                        </td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $m->role) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $m->magasin?->nom ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $m->actif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $m->actif ? 'Actif' : 'Inactif' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="edit({{ $m->id }})" class="text-brand-700 hover:underline font-medium">Modifier</button>
                            @if ($m->role !== 'super_admin')
                                <button wire:click="confirmDelete({{ $m->id }})" class="text-red-600 hover:underline">Retirer</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-300">
                                <x-icon name="users" class="w-12 h-12" />
                                <p class="text-slate-400 text-sm">Aucun membre trouvé dans l'équipe.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-lg max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">{{ $editingId ? "Modifier l'employé" : 'Créer un nouvel employé' }}</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $editingId ? "Modifiez les informations et l'accès de cet employé." : "Remplissez ces informations pour créer un accès sécurisé à votre employé." }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="shield" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Identité &amp; accès</h4>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                                <input type="text" wire:model="prenom" placeholder="Prénom" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                                <input type="text" wire:model="nom" placeholder="Nom" class="field">
                                @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse Email *</label>
                            <input type="email" wire:model="email" placeholder="email@boutique.com" class="field">
                            @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            <p class="text-xs text-slate-400 mt-1">Sera utilisé pour la connexion à l'application.</p>
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Mot de passe {{ $editingId ? '' : '*' }}
                                @if ($editingId)
                                    <span class="text-slate-400 font-normal">(laisser vide pour ne pas changer)</span>
                                @endif
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model="password" placeholder="Mot de passe initial" class="field pr-10">
                                <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ink-950 transition">
                                    <x-icon name="eye" x-show="!show" class="w-4 h-4" />
                                    <x-icon name="eye-off" x-show="show" class="w-4 h-4" x-cloak />
                                </button>
                            </div>
                            @error('password') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Rôle *</label>
                                <select wire:model="role" class="field">
                                    <option value="admin">Admin</option>
                                    <option value="gestionnaire">Gestionnaire</option>
                                    <option value="caissier">Caissier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Point de vente</label>
                                <select wire:model="magasin_id" class="field">
                                    <option value="">Aucun / Tous</option>
                                    @foreach ($magasins as $mg)
                                        <option value="{{ $mg->id }}">{{ $mg->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="actif" class="rounded border border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer"> Compte actif
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">{{ $editingId ? 'Enregistrer' : "Créer l'accès" }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
