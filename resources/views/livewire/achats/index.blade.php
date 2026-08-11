<div class="space-y-4">
    <x-page-header title="Achats" subtitle="{{ auth()->user()->hasFullAccess() ? 'Gérez vos achats fournisseurs' : 'Vos achats fournisseurs uniquement' }}">
        <x-slot:actions>
            <button wire:click="openModal" class="btn-primary">+ Nouvel achat</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Cartes stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Total achats</p>
                <x-icon name="shopping-cart" class="w-4 h-4 text-slate-400" />
            </div>
            <p class="text-2xl font-display font-bold text-ink-950">{{ number_format($totalAchats, 0, ',', ' ') }} F CFA</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Non réglé</p>
                <x-icon name="dollar-sign" class="w-4 h-4 text-amber-500" />
            </div>
            <p class="text-2xl font-display font-bold {{ $nonRegle > 0 ? 'text-amber-600' : 'text-ink-950' }}">{{ number_format($nonRegle, 0, ',', ' ') }} F CFA</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-slate-500">Nombre d'achats</p>
                <x-icon name="truck" class="w-4 h-4 text-slate-400" />
            </div>
            <p class="text-2xl font-display font-bold text-ink-950">{{ $nbTotal }}</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un achat..." class="field pl-9">
        </div>

        <select wire:model.live="filterFournisseur" class="field sm:w-52">
            <option value="">Tous les fournisseurs</option>
            @foreach ($fournisseurs as $f)
                <option value="{{ $f->id }}">{{ $f->nom }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterPeriode" class="field sm:w-44">
            <option value="">Toutes les périodes</option>
            <option value="jour">Aujourd'hui</option>
            <option value="semaine">Cette semaine</option>
            <option value="mois">Ce mois</option>
            <option value="annee">Cette année</option>
        </select>

        <select wire:model.live="filterStatut" class="field sm:w-44">
            <option value="">Tous les statuts</option>
            <option value="regle">Réglé</option>
            <option value="partiel">Partiel</option>
            <option value="non_regle">Non réglé</option>
        </select>

        @if ($search || $filterStatut || $filterFournisseur || $filterPeriode)
            <button wire:click="resetFilters" class="text-sm text-slate-500 hover:text-red-600 underline">Réinitialiser</button>
        @endif
    </div>

    {{-- Liste --}}
    <div class="space-y-3">
        @forelse ($achats as $a)
            @php
                $badge = match($a->statut_paiement) {
                    'regle' => 'bg-emerald-50 text-emerald-700',
                    'partiel' => 'bg-amber-50 text-amber-700',
                    default => 'bg-red-50 text-red-700',
                };
                $label = match($a->statut_paiement) {
                    'regle' => 'Réglé',
                    'partiel' => 'Partiel',
                    default => 'Non réglé',
                };
                $reste = (float) $a->montant_ttc - (float) $a->montant_paye;
            @endphp
            <div wire:key="ach-{{ $a->id }}" class="card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <span class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <x-icon name="truck" class="w-5 h-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-display font-semibold text-ink-950">{{ $a->num_achat }}</p>
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-sm text-slate-500 flex-wrap">
                        <span class="flex items-center gap-1.5"><x-icon name="truck" class="w-3.5 h-3.5" /> {{ $a->fournisseur?->nom }}</span>
                        <span class="flex items-center gap-1.5"><x-icon name="calendar" class="w-3.5 h-3.5" /> {{ $a->date_achat->translatedFormat('d F Y') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 shrink-0 sm:ml-auto">
                    <div class="text-right">
                        <p class="font-display font-bold text-lg text-ink-950">{{ number_format($a->montant_ttc, 0, ',', ' ') }} F CFA</p>
                        @if ($reste > 0)
                            <p class="text-xs text-amber-600">Restant : {{ number_format($reste, 0, ',', ' ') }} F CFA</p>
                        @endif
                    </div>
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400">
                        <x-icon name="eye" class="w-[18px] h-[18px]" />
                    </span>
                </div>
            </div>
        @empty
            <div class="card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">🛒</p>
                <p class="text-sm">Aucun achat ne correspond à ces filtres.</p>
            </div>
        @endforelse
    </div>

    @if ($achats->hasPages())
        <div class="card p-3">{{ $achats->links() }}</div>
    @endif

    {{-- Modale nouvel achat --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <h3 class="font-display font-bold text-xl text-ink-950">Nouvel Achat</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Enregistrez un nouvel achat fournisseur</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition shrink-0">
                        <span class="text-lg leading-none">✕</span>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="truck" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Informations fournisseur</h4>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fournisseur *</label>
                                <select wire:model="fournisseur_id" class="field">
                                    <option value="">Sélectionner un fournisseur</option>
                                    @foreach ($fournisseurs as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom }}</option>
                                    @endforeach
                                </select>
                                @error('fournisseur_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Référence</label>
                                <input type="text" wire:model="reference" placeholder="FAC-{{ now()->year }}-001" class="field">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                                <input type="date" wire:model="date_achat" class="field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Mode de paiement</label>
                                <select wire:model="mode_paiement" class="field">
                                    <option value="especes">Comptant</option>
                                    <option value="orange_money">Orange Money</option>
                                    <option value="wave">Wave</option>
                                    <option value="virement">Virement</option>
                                    <option value="cheque">Chèque</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Montant payé (F)</label>
                            <input type="number" step="0.01" wire:model="montant_paye" placeholder="0" class="field">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea wire:model="notes" rows="2" placeholder="Notes ou commentaires..." class="field resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-lg bg-slate-50 border border-slate-100">
                            <div>
                                <p class="text-sm font-medium text-ink-950">Inclure la TVA ({{ rtrim(rtrim(number_format(auth()->user()->tenant->tva_defaut ?? 18, 2), '0'), '.') }}%)</p>
                                <p class="text-xs text-slate-500">Appliquer la TVA sur le montant total</p>
                            </div>
                            <x-toggle wire:model.live="inclureTva" @checked($inclureTva) />
                        </div>
                    </div>

                    <div class="card p-5 space-y-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="package" class="w-5 h-5 text-ink-950" />
                            <h4 class="font-display font-semibold text-ink-950">Produits</h4>
                        </div>

                        <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1" x-data x-on:click.outside="$wire.set('produitDropdownOpen', false)">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
                                    <input type="text" wire:model.live.debounce.200ms="produitSearch" wire:focus="$set('produitDropdownOpen', true)"
                                           placeholder="Rechercher un produit par nom..." autocomplete="off" class="field pl-9">
                                    @if ($produitDropdownOpen && $produitSearch)
                                        <div class="absolute z-40 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg max-h-48 overflow-y-auto">
                                            @forelse ($produitsFiltres as $p)
                                                <button type="button" wire:click="selectProduit({{ $p->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50 border-t border-slate-50 first:border-t-0">
                                                    {{ $p->designation }}
                                                    <span class="text-xs text-slate-400 block">Prix d'achat habituel : {{ number_format($p->prix_achat, 0, ',', ' ') }} F</span>
                                                </button>
                                            @empty
                                                <p class="px-3 py-3 text-sm text-slate-400 text-center">Aucun produit trouvé.</p>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <input type="number" step="0.01" wire:model="ligne_prix" placeholder="Prix unit." class="field sm:w-32">
                                <input type="number" step="0.01" wire:model="ligne_qte" class="field sm:w-20">
                                <button type="button" wire:click="ajouterLigne" class="btn-primary whitespace-nowrap shrink-0">+ Ajouter</button>
                            </div>
                            @error('ligne_produit_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if (count($lignes))
                            <div class="divide-y divide-slate-100">
                                @foreach ($lignes as $i => $l)
                                    <div class="flex items-center justify-between py-2.5" wire:key="ligne-{{ $i }}">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-ink-950 truncate">{{ $l['designation'] }}</p>
                                            <p class="text-xs text-slate-400">{{ $l['qte'] }} × {{ number_format($l['prix_unitaire'], 0, ',', ' ') }} F</p>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-sm font-semibold text-ink-950">{{ number_format($l['total_ht'], 0, ',', ' ') }} F</span>
                                            <button type="button" wire:click="retirerLigne({{ $i }})" class="text-red-500 hover:text-red-700">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-3 border-t border-slate-100 space-y-1 text-sm">
                                <div class="flex justify-between text-slate-500">
                                    <span>Sous-total</span>
                                    <span>{{ number_format($this->sousTotal, 0, ',', ' ') }} F</span>
                                </div>
                                @if ($inclureTva)
                                    <div class="flex justify-between text-slate-500">
                                        <span>TVA</span>
                                        <span>{{ number_format($this->tvaMontant, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-display font-bold text-ink-950 text-base pt-1">
                                    <span>Total</span>
                                    <span>{{ number_format($this->totalAchat, 0, ',', ' ') }} F CFA</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-10 text-slate-400">
                                <p class="text-4xl mb-2">📦</p>
                                <p class="text-sm">Aucun produit ajouté</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="button" wire:click="enregistrer" class="btn-primary">
                            <x-icon name="save" class="w-4 h-4" /> Enregistrer l'achat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
