<div class="space-y-4">
    <x-page-header title="Ventes" :subtitle="$subtitle" />

    <div class="flex flex-col sm:flex-row gap-2">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher par numéro ou client..."
                   class="field pl-9">
        </div>

        <select wire:model.live="filterStatut" class="field sm:w-48">
            <option value="">Tous les statuts</option>
            <option value="payee">Payée</option>
            <option value="partiel">Partiellement payée</option>
            <option value="annulee">Annulée</option>
            <option value="brouillon">Brouillon</option>
        </select>

        <select wire:model.live="filterPeriode" class="field sm:w-48">
            <option value="">Toute la période</option>
            <option value="jour">Aujourd'hui</option>
            <option value="semaine">Cette semaine</option>
            <option value="mois">Ce mois</option>
            <option value="annee">Cette année</option>
        </select>
    </div>

    @if ($search || $filterStatut || $filterPeriode)
        <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser les filtres</button>
    @endif

    <div class="space-y-3">
        @forelse ($factures as $f)
            @php
                $badge = match($f->statut) {
                    'payee' => 'bg-emerald-50 text-emerald-700',
                    'partiel' => 'bg-amber-50 text-amber-700',
                    'annulee' => 'bg-red-50 text-red-700',
                    default => 'bg-slate-100 text-slate-500',
                };
                $labels = ['payee' => 'Payée', 'partiel' => 'Partiellement payée', 'annulee' => 'Annulée', 'brouillon' => 'Brouillon', 'envoyee' => 'Envoyée'];
            @endphp
            <div wire:key="fact-{{ $f->id }}" class="card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <span class="w-12 h-12 rounded-xl bg-slate-100 text-ink-950 flex items-center justify-center shrink-0">
                    <x-icon name="receipt" class="w-5 h-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-display font-semibold text-ink-950">{{ $f->num_facture }}</p>
                        <span class="badge {{ $badge }}">{{ $labels[$f->statut] ?? $f->statut }}</span>
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-sm text-slate-500 flex-wrap">
                        <span class="flex items-center gap-1.5"><x-icon name="calendar" class="w-3.5 h-3.5" /> {{ $f->date_facture->translatedFormat('d F Y') }}</span>
                        @if ($f->client)
                            <span class="flex items-center gap-1.5"><x-icon name="user" class="w-3.5 h-3.5" /> {{ $f->client->nom }} {{ $f->client->prenom }}@if($f->client->telephone) - {{ $f->client->telephone }}@endif</span>
                        @else
                            <span class="flex items-center gap-1.5"><x-icon name="user" class="w-3.5 h-3.5" /> Client comptoir</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:gap-5 shrink-0 sm:ml-auto">
                    <div class="text-right">
                        <p class="font-display font-bold text-lg text-ink-950">{{ number_format($f->montant_ttc, 0, ',', ' ') }} F CFA</p>
                        @if ($f->statut === 'partiel')
                            <p class="text-xs text-amber-600">Payé : {{ number_format($f->montant_paye, 0, ',', ' ') }} F · Reste : {{ number_format($f->resteAPayer(), 0, ',', ' ') }} F</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 text-slate-400">
                        <a href="{{ route('factures.pdf', $f) }}" target="_blank" title="Voir le PDF" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="eye" class="w-[18px] h-[18px]" />
                        </a>
                        <a href="{{ route('factures.pdf', $f) }}?download=1" title="Télécharger" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="download" class="w-[18px] h-[18px]" />
                        </a>
                        <button type="button" title="Partager"
                                onclick="navigator.share ? navigator.share({title: '{{ $f->num_facture }}', url: '{{ route('factures.pdf', $f) }}'}) : (navigator.clipboard.writeText('{{ route('factures.pdf', $f) }}'), alert('Lien copié !'))"
                                class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="share" class="w-[18px] h-[18px]" />
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">🧾</p>
                <p class="text-sm">Aucune vente ne correspond à ces filtres.</p>
            </div>
        @endforelse
    </div>

    @if ($factures->hasPages())
        <div class="card p-3">{{ $factures->links() }}</div>
    @endif
</div>
