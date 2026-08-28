<div>
    <x-page-header title="Caisse" subtitle="Point de vente" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    @if (session('success'))
        <div class="lg:col-span-3 p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            @if ($lastFactureId)
                <a href="{{ route('factures.pdf', $lastFactureId) }}" target="_blank" class="text-brand-700 font-medium hover:underline">Voir le reçu PDF</a>
            @endif
        </div>
    @endif
    @if (session('error'))
        <div class="lg:col-span-3 p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="lg:col-span-2 space-y-3">
        @if ($multiMagasins)
            @if ($cartMagasinId)
                <div class="flex items-center justify-between gap-2 p-2.5 rounded-lg bg-brand-50 border border-brand-100 text-sm">
                    <span class="flex items-center gap-1.5 text-brand-800">
                        <x-icon name="store" class="w-4 h-4" />
                        Vente en cours pour <strong>{{ $magasinsDisponibles->firstWhere('id', $cartMagasinId)?->nom }}</strong>
                    </span>
                    <button type="button" wire:click="viderPanier" class="text-brand-700 hover:underline font-medium shrink-0">Changer de magasin</button>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500 flex items-center gap-1.5 shrink-0">
                        <x-icon name="store" class="w-4 h-4" /> Magasin :
                    </span>
                    <select wire:model.live="filterMagasin" class="field sm:w-56">
                        <option value="">Tous mes magasins</option>
                        @foreach ($magasinsDisponibles as $m)
                            <option value="{{ $m->id }}">{{ $m->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endif

        <input type="text" wire:model.live.debounce.200ms="search" placeholder="Rechercher un produit ou scanner un code-barres..."
               class="w-full rounded-lg border border-slate-300 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none shadow-sm">

        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
            @forelse ($produits as $p)
                @php $rupture = $p->stock_disponible !== null && $p->stock_disponible <= 0; @endphp
                <button wire:click="addToCart({{ $p->id }})" type="button" @disabled($rupture)
                        class="relative bg-white border border-slate-200 rounded-xl p-3 text-left transition {{ $rupture ? 'opacity-50 cursor-not-allowed' : 'hover:border-brand-400 hover:shadow-sm' }}">
                    @if ($p->stock_disponible !== null && $p->stock_disponible > 0 && $p->stock_disponible <= $p->stock_min)
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-amber-500" title="Stock bas"></span>
                    @endif
                    <p class="font-medium text-slate-800 text-sm line-clamp-2">{{ $p->designation }}</p>
                    <p class="text-brand-700 font-semibold mt-1">{{ number_format($p->prix_vente, 0, ',', ' ') }} F</p>
                    @if ($rupture)
                        <span class="inline-block mt-1 text-xs font-medium text-red-600">Rupture de stock</span>
                    @elseif ($p->stock_disponible !== null)
                        <span class="inline-block mt-1 text-xs text-slate-400">{{ (int) $p->stock_disponible }} en stock</span>
                    @endif
                    @if ($multiMagasins && ! $filterMagasin && ! $cartMagasinId)
                        <span class="inline-block mt-1 text-xs text-slate-400 truncate w-full">🏬 {{ $p->magasin?->nom }}</span>
                    @endif
                </button>
            @empty
                <p class="col-span-full text-center text-slate-400 py-8">Aucun produit trouvé.</p>
            @endforelse
        </div>
    </div>

    <div class="card p-4 flex flex-col h-fit sticky top-4">
        <h3 class="font-display font-semibold text-ink-950 mb-3">Panier</h3>

        <div class="relative mb-3" x-data x-on:click.outside="$wire.set('clientDropdownOpen', false)">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.200ms="clientSearch"
                       wire:focus="openClientDropdown"
                       x-on:focus="$el.select()"
                       placeholder="Rechercher un client..."
                       autocomplete="off"
                       class="field pr-9 {{ $client_id ? 'text-ink-950 font-medium' : '' }}">
                @if ($client_id)
                    <button type="button" wire:click="selectClient(null)" title="Retirer le client"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm">✕</button>
                @else
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">🔎</span>
                @endif
            </div>

            @if ($clientDropdownOpen)
                <div class="absolute z-40 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg max-h-56 overflow-y-auto modal-enter">
                    <button type="button" wire:click="selectClient(null)"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50 {{ ! $client_id ? 'bg-brand-50 text-brand-800 font-medium' : 'text-slate-700' }}">
                        Client comptoir
                    </button>
                    @forelse ($clientsFiltres as $c)
                        <button type="button" wire:click="selectClient({{ $c->id }})"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-brand-50 border-t border-slate-50 {{ $client_id === $c->id ? 'bg-brand-50 text-brand-800 font-medium' : 'text-slate-700' }}">
                            {{ $c->nom }} {{ $c->prenom }}
                            @if ($c->telephone)
                                <span class="text-xs text-slate-400 block">{{ $c->telephone }}</span>
                            @endif
                        </button>
                    @empty
                        <p class="px-3 py-3 text-sm text-slate-400 text-center">Aucun client trouvé.</p>
                    @endforelse
                </div>
            @endif
        </div>

        <div class="space-y-2 max-h-72 overflow-y-auto mb-3">
            @forelse ($cart as $produitId => $item)
                <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2" wire:key="cart-{{ $produitId }}">
                    <div class="flex-1 min-w-0">
                        <p class="truncate font-medium text-slate-700">{{ $item['designation'] }}</p>
                        <p class="text-xs text-slate-500">{{ number_format($item['prix'], 0, ',', ' ') }} F x {{ $item['qte'] }}</p>
                    </div>
                    <div class="flex items-center gap-1 ml-2">
                        <button wire:click="decrementQte({{ $produitId }})" class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200">-</button>
                        <span class="w-6 text-center">{{ $item['qte'] }}</span>
                        <button wire:click="incrementQte({{ $produitId }})" @disabled(($this->cartDisponible[$produitId] ?? INF) <= $item['qte'])
                                class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed">+</button>
                        <button wire:click="removeFromCart({{ $produitId }})" class="text-red-500 ml-1">✕</button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400 text-center py-6">Panier vide</p>
            @endforelse
        </div>

        <div class="border-t border-slate-200 pt-3 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" wire:click="$set('inclureTva', false)"
                        class="p-2.5 rounded-lg border-2 text-sm font-medium text-center transition
                               {{ ! $inclureTva ? 'border-ink-950 bg-ink-950 text-white' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                    Sans TVA
                </button>
                <button type="button" wire:click="$set('inclureTva', true)"
                        class="p-2.5 rounded-lg border-2 text-sm font-medium text-center transition
                               {{ $inclureTva ? 'border-ink-950 bg-ink-950 text-white' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                    TVA {{ rtrim(rtrim(number_format(auth()->user()->tenant->tva_defaut ?? 18, 2), '0'), '.') }}%
                </button>
            </div>

            <div class="flex justify-between text-sm text-slate-500">
                <span>Sous-total</span>
                <span>{{ number_format($this->sousTotal, 0, ',', ' ') }} F</span>
            </div>
            @if ($inclureTva)
                <div class="flex justify-between text-sm text-slate-500">
                    <span>TVA</span>
                    <span>{{ number_format($this->tvaMontant, 0, ',', ' ') }} F</span>
                </div>
            @endif
            <div class="flex justify-between font-semibold text-slate-800 text-base pt-1 border-t border-slate-100">
                <span>Total</span>
                <span>{{ number_format($this->total, 0, ',', ' ') }} F</span>
            </div>

            <select wire:model="mode_paiement" class="w-full rounded-lg border border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                <option value="especes">Espèces</option>
                <option value="orange_money">Orange Money</option>
                <option value="wave">Wave</option>
                <option value="carte">Carte bancaire</option>
            </select>

            @if ($mode_paiement === 'especes')
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Montant reçu</label>
                    <input type="number" step="0.01" wire:model.live="montant_recu" class="w-full rounded-lg border border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none">
                    @if ($montant_recu > 0)
                        <p class="text-xs text-slate-500 mt-1">Monnaie à rendre : <span class="font-medium">{{ number_format($this->monnaie, 0, ',', ' ') }} F</span></p>
                    @endif
                </div>
            @endif

            <button wire:click="validerVente" wire:loading.attr="disabled"
                    class="w-full py-2.5 rounded-lg bg-ink-950 hover:bg-ink-900 text-white shadow-sm hover:shadow-md font-medium transition active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100">
                Valider la vente
            </button>
        </div>
    </div>
    </div>
</div>
