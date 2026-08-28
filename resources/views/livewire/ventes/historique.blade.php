<div class="space-y-4">
    <x-page-header title="Ventes" :subtitle="$subtitle" />

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

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
            <option value="brouillon">Brouillon</option>
            <option value="annulee">Annulée</option>
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
                        @if ($f->statut !== 'annulee' && (auth()->user()->hasFullAccess() || $f->utilisateur_id === auth()->id()))
                            <button type="button" wire:click="openEdit({{ $f->id }})" title="Modifier" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 hover:text-ink-950 transition">
                                <x-icon name="pencil" class="w-[18px] h-[18px]" />
                            </button>
                        @endif
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

    {{-- Modale de modification --}}
    @if ($showEditModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showEditModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">Modifier la facture</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Corrige une erreur de quantité, de prix ou de client.</p>
                    </div>
                    <button type="button" wire:click="$set('showEditModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Informations</h4>
                        </div>

                        <div class="relative" x-data x-on:click.outside="$wire.set('editClientDropdownOpen', false)">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Client</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.200ms="editClientSearch" wire:focus="$set('editClientDropdownOpen', true)"
                                       placeholder="Client comptoir" autocomplete="off" class="field pr-9">
                                @if ($editClientId)
                                    <button type="button" wire:click="selectClientEdit(null)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm">✕</button>
                                @endif
                            </div>
                            @if ($editClientDropdownOpen)
                                <div class="absolute z-40 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg max-h-48 overflow-y-auto">
                                    <button type="button" wire:click="selectClientEdit(null)" class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50">Client comptoir</button>
                                    @forelse ($clientsFiltres as $c)
                                        <button type="button" wire:click="selectClientEdit({{ $c->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50 border-t border-slate-50">
                                            {{ $c->nom }} {{ $c->prenom }}
                                        </button>
                                    @empty
                                        <p class="px-3 py-3 text-sm text-slate-400 text-center">Aucun client trouvé.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                                <input type="date" wire:model="editDate" class="field">
                                @error('editDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Montant payé (F)</label>
                                <input type="number" step="0.01" wire:model="editMontantPaye" class="field">
                                @error('editMontantPaye') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea wire:model="editNotes" rows="2" class="field resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">TVA</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="$set('editInclureTva', false)"
                                        class="p-2.5 rounded-lg border-2 text-sm font-medium text-center transition
                                               {{ ! $editInclureTva ? 'border-ink-950 bg-ink-950 text-white' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                    Sans TVA
                                </button>
                                <button type="button" wire:click="$set('editInclureTva', true)"
                                        class="p-2.5 rounded-lg border-2 text-sm font-medium text-center transition
                                               {{ $editInclureTva ? 'border-ink-950 bg-ink-950 text-white' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                    TVA {{ rtrim(rtrim(number_format(auth()->user()->tenant->tva_defaut ?? 18, 2), '0'), '.') }}%
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card p-5 space-y-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="package" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Produits</h4>
                        </div>

                        <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1" x-data x-on:click.outside="$wire.set('editProduitDropdownOpen', false)">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
                                    <input type="text" wire:model.live.debounce.200ms="editProduitSearch" wire:focus="$set('editProduitDropdownOpen', true)"
                                           placeholder="Rechercher un produit par nom..." autocomplete="off" class="field pl-9">
                                    @if ($editProduitDropdownOpen && $editProduitSearch)
                                        <div class="absolute z-40 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg max-h-48 overflow-y-auto">
                                            @forelse ($produitsFiltres as $p)
                                                <button type="button" wire:click="selectProduitEdit({{ $p->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50 border-t border-slate-50 first:border-t-0">
                                                    {{ $p->designation }}
                                                    <span class="text-xs text-slate-400 block">Prix de vente : {{ number_format($p->prix_vente, 0, ',', ' ') }} F</span>
                                                </button>
                                            @empty
                                                <p class="px-3 py-3 text-sm text-slate-400 text-center">Aucun produit trouvé.</p>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <input type="number" step="0.01" wire:model="editLignePrix" placeholder="Prix unit." class="field sm:w-32">
                                <input type="number" step="0.01" wire:model="editLigneQte" class="field sm:w-20">
                                <button type="button" wire:click="ajouterLigneEdit" class="btn-primary whitespace-nowrap shrink-0">+ Ajouter</button>
                            </div>
                            @error('editLigneProduitId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if (count($editLignes))
                            <div class="divide-y divide-slate-100">
                                @foreach ($editLignes as $i => $l)
                                    <div class="flex items-center justify-between py-2.5" wire:key="editligne-{{ $i }}">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-ink-950 truncate">{{ $l['designation'] }}</p>
                                            <p class="text-xs text-slate-400">{{ $l['qte'] }} × {{ number_format($l['prix_unitaire'], 0, ',', ' ') }} F</p>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-sm font-semibold text-ink-950">{{ number_format($l['total_ht'], 0, ',', ' ') }} F</span>
                                            <button type="button" wire:click="retirerLigneEdit({{ $i }})" class="text-red-500 hover:text-red-700">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-3 border-t border-slate-100 space-y-1 text-sm">
                                <div class="flex justify-between text-slate-500">
                                    <span>Sous-total</span>
                                    <span>{{ number_format($this->editSousTotal, 0, ',', ' ') }} F</span>
                                </div>
                                @if ($editInclureTva)
                                    <div class="flex justify-between text-slate-500">
                                        <span>TVA</span>
                                        <span>{{ number_format($this->editTvaMontant, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-display font-bold text-ink-950 text-base pt-1">
                                    <span>Total</span>
                                    <span>{{ number_format($this->editTotal, 0, ',', ' ') }} F CFA</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-10 text-slate-400">
                                <p class="text-4xl mb-2">📦</p>
                                <p class="text-sm">Aucun produit dans cette facture</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="btn-secondary">Annuler</button>
                        <button type="button" wire:click="saveEdit" class="btn-primary">
                            <x-icon name="save" class="w-4 h-4" /> Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
