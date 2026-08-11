<div class="space-y-4">
    <x-page-header title="Créances clients"
        subtitle="{{ $nbTotal }} créance(s) en cours - <span class='font-semibold text-amber-600'>{{ number_format($totalRestant, 0, ',', ' ') }} F CFA</span> restant" />

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un client..."
               class="field sm:w-64">

        <label class="flex items-center gap-2 text-sm text-slate-600 px-3 py-2 rounded-lg border border-slate-300 bg-white shadow-sm cursor-pointer">
            <input type="checkbox" wire:model.live="filterEnRetard" class="rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer">
            En retard uniquement
        </label>

        @if ($search || $filterEnRetard)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm table-modern">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Montant initial</th>
                    <th class="px-4 py-3">Restant</th>
                    <th class="px-4 py-3">Échéance</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($creances as $c)
                    @php $enRetard = $c->date_echeance && $c->date_echeance->isPast(); @endphp
                    <tr wire:key="cre-{{ $c->id }}" class="{{ $enRetard ? 'bg-red-50/40' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $c->client?->nom }}</td>
                        <td class="px-4 py-3">{{ number_format($c->montant_initial, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3 text-amber-600 font-medium">{{ number_format($c->montant_restant, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3 {{ $enRetard ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $c->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($enRetard)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-700">En retard</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700 capitalize">{{ $c->statut }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openPay({{ $c->id }})" class="text-brand-700 hover:underline font-medium">Encaisser</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Aucune créance en cours.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($creances->hasPages())
            <div class="p-3">{{ $creances->links() }}</div>
        @endif
    </div>

    @if ($showPayModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showPayModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-sm p-5">
                <h3 class="font-display font-semibold text-lg text-ink-950 mb-4">Encaisser un paiement</h3>
                <form wire:submit="enregistrerPaiement" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Montant</label>
                        <input type="number" step="0.01" wire:model="montant" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                        @error('montant') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mode de paiement</label>
                        <select wire:model="mode_paiement" class="w-full rounded-lg border border-slate-300 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                            <option value="especes">Espèces</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="wave">Wave</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showPayModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
