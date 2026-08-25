<div class="space-y-4">
    <x-page-header title="Gestion des Stocks"
        subtitle="{{ $magasinLocked ? 'Stock de votre magasin uniquement' : 'Suivez et gérez vos stocks en temps réel' }}" />

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Valeur totale du stock</p>
                <x-icon name="archive" class="w-4 h-4 text-slate-400" />
            </div>
            <p class="text-2xl font-display font-bold text-ink-950">{{ number_format($valeurTotale, 0, ',', ' ') }} F CFA</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Articles en stock</p>
                <x-icon name="package" class="w-4 h-4 text-slate-400" />
            </div>
            <p class="text-2xl font-display font-bold text-ink-950">{{ $nbArticles }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Stock bas</p>
                <x-icon name="trending-down" class="w-4 h-4 text-amber-500" />
            </div>
            <p class="text-2xl font-display font-bold {{ $nbStockBas > 0 ? 'text-amber-600' : 'text-ink-950' }}">{{ $nbStockBas }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Rupture de stock</p>
                <x-icon name="alert-triangle" class="w-4 h-4 text-red-500" />
            </div>
            <p class="text-2xl font-display font-bold {{ $nbRupture > 0 ? 'text-red-600' : 'text-ink-950' }}">{{ $nbRupture }}</p>
        </div>
    </div>

    <div class="inline-flex items-center gap-1 p-1 rounded-lg bg-slate-100">
        <button wire:click="setTab('etat')"
                class="px-3.5 py-1.5 rounded-md text-sm font-medium transition {{ $activeTab === 'etat' ? 'bg-white text-ink-950 shadow-sm' : 'text-slate-500 hover:text-ink-950' }}">
            État des stocks
        </button>
        <button wire:click="setTab('mouvements')"
                class="px-3.5 py-1.5 rounded-md text-sm font-medium transition {{ $activeTab === 'mouvements' ? 'bg-white text-ink-950 shadow-sm' : 'text-slate-500 hover:text-ink-950' }}">
            Mouvements
        </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <div class="relative flex-1 sm:max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un produit..." class="field pl-9">
        </div>

        @if ($magasinLocked)
            <span class="flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-600 shrink-0">
                <x-icon name="store" class="w-4 h-4 text-slate-400" />
                {{ $magasins->firstWhere('id', $magasin_id)?->nom ?? 'Votre magasin' }}
            </span>
        @else
            <select wire:model.live="magasin_id" class="field sm:w-52">
                @foreach ($magasins as $m)
                    <option value="{{ $m->id }}">{{ $m->nom }}</option>
                @endforeach
            </select>
        @endif

        <button wire:click="$set('filterStockBas', {{ $filterStockBas ? 'false' : 'true' }})"
                class="flex items-center gap-2 px-3.5 py-2 rounded-lg border text-sm font-medium transition shrink-0
                       {{ $filterStockBas ? 'bg-amber-50 border-amber-300 text-amber-700' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 shadow-sm' }}">
            <x-icon name="alert-triangle" class="w-4 h-4" />
            Stock bas uniquement
        </button>

        @if ($search || $filterStockBas)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    @if ($activeTab === 'etat')
        <div class="card overflow-x-auto">
            <table class="w-full text-sm table-modern">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Produit</th>
                        <th class="px-4 py-3">Catégorie</th>
                        <th class="px-4 py-3">Quantité</th>
                        <th class="px-4 py-3">Stock min</th>
                        <th class="px-4 py-3">Valeur</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($produits as $p)
                        @php
                            $qte = (float) ($p->stocks->first()->quantite ?? 0);
                            $valeur = $qte * (float) $p->prix_achat;
                            $statut = $qte <= 0 ? 'rupture' : ($qte <= $p->stock_min ? 'bas' : 'ok');
                        @endphp
                        <tr wire:key="stock-{{ $p->id }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-ink-950">{{ $p->designation }}</p>
                                @if ($p->code_barres)
                                    <p class="text-xs text-slate-400">{{ $p->code_barres }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->category?->nom ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium {{ $statut !== 'ok' ? 'text-red-600' : 'text-ink-950' }}">{{ (int) $qte }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->stock_min }}</td>
                            <td class="px-4 py-3 font-medium text-ink-950">{{ number_format($valeur, 0, ',', ' ') }} F CFA</td>
                            <td class="px-4 py-3">
                                @if ($statut === 'rupture')
                                    <span class="badge border border-red-200 text-red-700">Rupture</span>
                                @elseif ($statut === 'bas')
                                    <span class="badge border border-amber-200 text-amber-700">Bas</span>
                                @else
                                    <span class="badge border border-emerald-200 text-emerald-700">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <button wire:click="openAdjust({{ $p->id }}, 'entree')" class="text-brand-700 hover:underline font-medium">+ Entrée</button>
                                <button wire:click="openAdjust({{ $p->id }}, 'sortie')" class="text-red-600 hover:underline">- Sortie</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Aucun produit ne correspond à ces filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($produits->hasPages())
                <div class="p-3">{{ $produits->links() }}</div>
            @endif
        </div>
    @else
        <div class="card overflow-x-auto">
            <table class="w-full text-sm table-modern">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Produit</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Quantité</th>
                        <th class="px-4 py-3">Motif</th>
                        <th class="px-4 py-3">Par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mouvements as $m)
                        <tr wire:key="mvt-{{ $m->id }}">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-ink-950">{{ $m->produit?->designation }}</td>
                            <td class="px-4 py-3">
                                @if (in_array($m->type, ['entree', 'inventaire']))
                                    <span class="badge border border-emerald-200 text-emerald-700">Entrée</span>
                                @else
                                    <span class="badge border border-red-200 text-red-700">Sortie</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium">{{ (int) $m->quantite }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $m->motif ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $m->utilisateur?->nom ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Aucun mouvement pour ce magasin.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($mouvements->hasPages())
                <div class="p-3">{{ $mouvements->links() }}</div>
            @endif
        </div>
    @endif

    @if ($showAdjustModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showAdjustModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-sm p-5">
                <h3 class="font-display font-semibold text-lg text-ink-950 mb-4">
                    {{ $adjustType === 'entree' ? 'Entrée de stock' : 'Sortie de stock' }}
                </h3>
                <form wire:submit="saveAdjust" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantité</label>
                        <input type="number" step="0.01" wire:model="adjustQte" class="field">
                        @error('adjustQte') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Motif</label>
                        <input type="text" wire:model="adjustMotif" class="field">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showAdjustModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
