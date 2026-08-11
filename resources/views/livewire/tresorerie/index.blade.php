<div class="space-y-4">
    <x-page-header title="Trésorerie" subtitle="Dépenses et revenus divers" />

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div class="card p-4">
            <p class="text-xs text-slate-500">Dépenses du mois</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($totalDepenses, 0, ',', ' ') }} F</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-slate-500">Revenus divers du mois</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">{{ number_format($totalRevenus, 0, ',', ' ') }} F</p>
        </div>
    </div>

    <div class="flex gap-2 justify-end">
        <button wire:click="openModal('depense')" class="px-4 py-2 rounded-lg border border-red-300 text-red-600 text-sm font-medium">+ Dépense</button>
        <button wire:click="openModal('revenu')" class="btn-primary">+ Revenu</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-4">
            <h3 class="font-display font-semibold text-ink-950 mb-3">Dépenses récentes</h3>
            @forelse ($depenses as $d)
                <div class="flex justify-between text-sm py-2 border-b border-slate-100 last:border-0">
                    <span>{{ $d->motif }} <span class="text-slate-400">({{ $d->categorie }})</span></span>
                    <span class="text-red-600 font-medium">-{{ number_format($d->montant, 0, ',', ' ') }} F</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune dépense.</p>
            @endforelse
        </div>
        <div class="card p-4">
            <h3 class="font-display font-semibold text-ink-950 mb-3">Revenus divers récents</h3>
            @forelse ($revenus as $r)
                <div class="flex justify-between text-sm py-2 border-b border-slate-100 last:border-0">
                    <span>{{ $r->source }} <span class="text-slate-400">({{ $r->categorie }})</span></span>
                    <span class="text-emerald-600 font-medium">+{{ number_format($r->montant, 0, ',', ' ') }} F</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucun revenu divers.</p>
            @endforelse
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-sm p-5">
                <h3 class="font-display font-semibold text-lg text-ink-950 mb-4">{{ $type === 'depense' ? 'Nouvelle dépense' : 'Nouveau revenu' }}</h3>
                <form wire:submit="save" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Catégorie</label>
                        <input type="text" wire:model="categorie" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                        @error('categorie') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ $type === 'depense' ? 'Motif' : 'Source' }}</label>
                        <input type="text" wire:model="motif_source" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                        @error('motif_source') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Montant</label>
                            <input type="number" step="0.01" wire:model="montant" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                            @error('montant') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" wire:model="date_mouvement" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mode de paiement</label>
                        <select wire:model="mode_paiement" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                            <option value="especes">Espèces</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="wave">Wave</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
