<div class="max-w-3xl space-y-4">
    <x-page-header title="Paramètres" subtitle="Configurez votre boutique et vos préférences" />

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Informations boutique --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-1">
                <x-icon name="building" class="w-5 h-5 text-ink-950" />
                <h3 class="font-display font-semibold text-lg text-ink-950">Informations boutique</h3>
            </div>
            <p class="text-sm text-slate-500 mb-5">Informations générales de votre boutique</p>

            <div class="flex flex-col sm:flex-row items-start gap-4 mb-6 pb-6 border-b border-slate-100">
                <div class="w-28 h-28 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center shrink-0 overflow-hidden bg-slate-50">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Aperçu du logo">
                    @elseif ($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" class="w-full h-full object-cover" alt="Logo boutique">
                    @else
                        <x-icon name="image" class="w-8 h-8 text-slate-300" />
                    @endif
                </div>
                <div>
                    <p class="font-medium text-ink-950">Logo de la boutique</p>
                    <p class="text-sm text-slate-500 mb-3">Format recommandé : PNG ou JPG (max. 1 Mo, idéalement carré)</p>
                    <label class="btn-secondary cursor-pointer inline-flex">
                        Choisir une image
                        <input type="file" wire:model="logo" accept="image/*" class="hidden">
                    </label>
                    @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="logo" class="text-xs text-slate-400 mt-1">Chargement...</div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom de la boutique *</label>
                    <input type="text" wire:model="nom" class="field">
                    @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="text" wire:model="telephone" class="field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" wire:model="email" class="field">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                    <input type="text" wire:model="adresse" class="field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ville</label>
                    <input type="text" wire:model="ville" class="field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pays</label>
                    <input type="text" wire:model="pays" class="field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NINEA</label>
                    <input type="text" wire:model="ninea" placeholder="NINEA" class="field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RCCM</label>
                    <input type="text" wire:model="rccm" placeholder="RCCM" class="field">
                </div>
            </div>
        </div>

        {{-- Paramètres facture --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-1">
                <x-icon name="receipt" class="w-5 h-5 text-ink-950" />
                <h3 class="font-display font-semibold text-lg text-ink-950">Paramètres facture</h3>
            </div>
            <p class="text-sm text-slate-500 mb-5">Personnalisez vos factures et tickets</p>

            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">TVA par défaut</p>
                        <p class="text-sm text-slate-500">Appliquer un taux de TVA sur les ventes</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <input type="number" step="0.01" wire:model="tva_defaut" class="field w-24 text-right">
                        <span class="text-sm text-slate-500">%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Format numérotation</p>
                        <p class="text-sm text-slate-500">Format des numéros de facture (Ex: FAC-{YYYY}{MM}{DD}-{NNN})</p>
                    </div>
                    <input type="text" wire:model="format_num_facture" class="field w-56 shrink-0">
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Mentions légales</p>
                        <p class="text-sm text-slate-500">Textes affichés en bas des factures</p>
                    </div>
                    <input type="text" wire:model="mentions_legales" placeholder="TVA: X% - Siège: ..." class="field w-56 shrink-0">
                </div>
            </div>
        </div>

        {{-- Modes de paiement --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <x-icon name="credit-card" class="w-5 h-5 text-ink-950" />
                    <h3 class="font-display font-semibold text-lg text-ink-950">Modes de paiement</h3>
                </div>
                <button type="button" wire:click="openPaymentModal" class="btn-secondary text-sm">+ Ajouter</button>
            </div>
            <p class="text-sm text-slate-500 mb-5">Gérez les modes de paiement dynamiques</p>

            <div class="divide-y divide-slate-100">
                @forelse ($paymentMethods as $pm)
                    @php
                        $pmIcon = match(true) {
                            str_contains($pm->code, 'espece') => 'banknote',
                            str_contains($pm->code, 'wave') => 'wifi',
                            str_contains($pm->code, 'om') || str_contains($pm->code, 'orange') => 'smartphone',
                            default => 'credit-card',
                        };
                    @endphp
                    <div class="flex items-center justify-between gap-3 py-3.5" wire:key="pm-{{ $pm->id }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-600">
                                <x-icon :name="$pmIcon" class="w-4 h-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium text-ink-950 truncate">{{ $pm->nom }}</p>
                                <p class="text-xs text-slate-400">{{ $pm->code }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-toggle wire:click="togglePaymentActif({{ $pm->id }})" @checked($pm->actif) />
                            <button type="button" wire:click="openPaymentModal({{ $pm->id }})" title="Modifier" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $pm->id }})" title="Supprimer" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-600 transition">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Aucun mode de paiement configuré.</p>
                @endforelse
            </div>
        </div>

        {{-- Notifications --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-1">
                <x-icon name="bell" class="w-5 h-5 text-ink-950" />
                <h3 class="font-display font-semibold text-lg text-ink-950">Notifications</h3>
            </div>
            <p class="text-sm text-slate-500 mb-5">Configurez les alertes et notifications</p>

            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Alertes stock bas</p>
                        <p class="text-sm text-slate-500">Être notifié quand un produit est en rupture</p>
                    </div>
                    <x-toggle wire:model="alerte_stock_bas" @checked($alerte_stock_bas) />
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Créances échéance</p>
                        <p class="text-sm text-slate-500">Notifier avant l'échéance des créances</p>
                    </div>
                    <x-toggle wire:model="creances_echeance" @checked($creances_echeance) />
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Rapports quotidiens</p>
                        <p class="text-sm text-slate-500">Recevoir un résumé des ventes chaque jour</p>
                    </div>
                    <x-toggle wire:model="rapports_quotidiens" @checked($rapports_quotidiens) />
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <p class="font-medium text-ink-950 text-sm">Mode Multi-magasin</p>
                        <p class="text-sm text-slate-500">Activer la gestion de plusieurs boutiques/entrepôts et les transferts de stock</p>
                    </div>
                    <x-toggle wire:model="activer_multi_magasin" @checked($activer_multi_magasin) />
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <x-icon name="save" class="w-4 h-4" /> Enregistrer les modifications
            </button>
        </div>
    </form>

    {{-- Modale mode de paiement --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showPaymentModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-sm p-6">
                <h3 class="font-display font-semibold text-lg text-ink-950 mb-4">{{ $editingPaymentId ? 'Modifier le mode de paiement' : 'Nouveau mode de paiement' }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" wire:model="pm_nom" placeholder="Ex: Wave" class="field">
                        @error('pm_nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                        <input type="text" wire:model="pm_code" placeholder="Ex: wave" class="field">
                        @error('pm_code') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="btn-secondary">Annuler</button>
                    <button type="button" wire:click="savePayment" class="btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
