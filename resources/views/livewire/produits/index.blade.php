<div class="space-y-4">
    <x-page-header title="Produits"
        subtitle="{{ $nbProduitsTotal }} produit(s) - <span class='text-emerald-600 font-semibold'>{{ number_format($valeurStock, 0, ',', ' ') }} F CFA</span> en stock">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary whitespace-nowrap">+ Nouveau produit</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un produit ou code-barres..."
               class="field sm:w-80">
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <select wire:model.live="filterCategory" class="field sm:w-48">
            <option value="">Toutes les catégories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatut" class="field sm:w-40">
            <option value="actifs">Actifs</option>
            <option value="inactifs">Inactifs</option>
            <option value="tous">Tous les statuts</option>
        </select>

        <select wire:model.live="filterStock" class="field sm:w-44">
            <option value="">Tous les stocks</option>
            <option value="alerte">Stock bas</option>
            <option value="rupture">Rupture de stock</option>
        </select>

        @if ($search || $filterCategory || $filterStatut !== 'actifs' || $filterStock)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-4 py-3">Désignation</th>
                    <th class="px-4 py-3">Catégorie</th>
                    <th class="px-4 py-3">Prix achat</th>
                    <th class="px-4 py-3">Prix vente</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($produits as $p)
                    @php $stock = (float) ($p->stock_total ?? 0); @endphp
                    <tr wire:key="prod-{{ $p->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $p->designation }}</p>
                            @if ($p->sku)
                                <p class="text-xs text-slate-400">{{ $p->sku }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $p->category?->nom ?? '—' }}</td>
                        <td class="px-4 py-3">{{ number_format($p->prix_achat, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3">{{ number_format($p->prix_vente, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3">
                            @if ($p->est_stockable)
                                <span class="{{ $stock <= $p->stock_min ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                                    {{ (int) $stock }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $p->actif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $p->actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="edit({{ $p->id }})" class="text-brand-700 hover:underline font-medium">Modifier</button>
                            <button wire:click="confirmDelete({{ $p->id }})" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Aucun produit.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($produits->hasPages())
            <div class="p-3">{{ $produits->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">{{ $editingId ? 'Modifier le produit' : 'Nouveau Produit' }}</h3>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $editingId ? 'Modifiez les informations du produit' : 'Ajoutez un nouveau produit à votre catalogue' }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="package" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Informations produit</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Designation</label>
                            <input type="text" wire:model="designation" placeholder="HP OMNIBOOK 14" class="field">
                            @error('designation') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="3" placeholder="Description détaillée du produit..." class="field resize-none"></textarea>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                                <div class="flex gap-2">
                                    <input type="text" wire:model="sku" placeholder="SKU-XXXXXXXX" class="field">
                                    <button type="button" wire:click="generateSku" class="btn-secondary whitespace-nowrap shrink-0">Générer</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Code-barres</label>
                                <input type="text" wire:model="code_barres" class="field">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Catégorie</label>
                                <select wire:model="category_id" class="field">
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Marque</label>
                                <input type="text" wire:model="marque" placeholder="Apple" class="field">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Unité de mesure</label>
                                <select wire:model="unite" class="field">
                                    @foreach ($unites as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Type de produit</label>
                                <select wire:model="type_produit" class="field">
                                    @foreach ($typesProduit as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="dollar-sign" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Prix &amp; stock</h4>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prix achat</label>
                                <input type="number" step="0.01" wire:model="prix_achat" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prix vente</label>
                                <input type="number" step="0.01" wire:model="prix_vente" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prix gros</label>
                                <input type="number" step="0.01" wire:model="prix_vente_gros" class="field">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Stock min.</label>
                                <input type="number" wire:model="stock_min" class="field">
                            </div>
                            @if (! $editingId)
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Stock initial</label>
                                    <input type="number" step="0.01" wire:model="stock_initial" class="field">
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-6 pt-1">
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model="actif" class="rounded border border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer"> Actif
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model="est_stockable" class="rounded border border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer"> Suivi de stock
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
