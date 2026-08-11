<div class="space-y-4">
    <x-page-header title="Catégories" subtitle="Gérez vos catégories de produits">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary">+ Nouvelle catégorie</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher une catégorie..."
               class="field sm:w-72">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($categories as $cat)
            @php $couleur = $cat->couleur ?: '#64748b'; @endphp
            <div wire:key="cat-{{ $cat->id }}" class="card p-5 border-l-4 flex flex-col" style="border-left-color: {{ $couleur }}">
                <div class="flex items-start justify-between mb-3">
                    <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                          style="background-color: {{ $couleur }}1A; color: {{ $couleur }};">
                        <x-icon name="package" class="w-5 h-5" />
                    </span>
                    <div class="flex items-center gap-1">
                        <button wire:click="edit({{ $cat->id }})" title="Modifier"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-ink-950 transition">
                            <x-icon name="pencil" class="w-4 h-4" />
                        </button>
                        <button wire:click="confirmDelete({{ $cat->id }})" title="Supprimer"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-600 transition">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <h3 class="font-display font-semibold text-lg text-ink-950">{{ $cat->nom }}</h3>
                <p class="text-sm text-slate-500 mt-1 line-clamp-2 flex-1">
                    {{ $cat->description ?: 'Aucune description' }}
                </p>

                @unless ($cat->active)
                    <span class="badge bg-slate-100 text-slate-500 self-start mt-3">Inactive</span>
                @endunless

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                    <span class="badge bg-slate-100 text-slate-600">{{ $cat->fields_count }} champ(s)</span>
                    <a href="{{ route('produits.index', ['categorie' => $cat->id]) }}" class="text-sm font-medium text-ink-950 hover:text-brand-700 transition">
                        Voir les produits ({{ $cat->produits_count }}) →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-400">
                <p class="text-3xl mb-2">🏷️</p>
                <p class="text-sm">Aucune catégorie ne correspond à cette recherche.</p>
            </div>
        @endforelse
    </div>

    @if ($categories->hasPages())
        <div class="card p-3">{{ $categories->links() }}</div>
    @endif

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-md p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-9 h-9 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                        <x-icon name="tag" class="w-4 h-4" />
                    </span>
                    <h3 class="font-display font-semibold text-lg text-ink-950">{{ $editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h3>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" wire:model="nom" placeholder="Ex: Téléphones" class="field">
                        @error('nom') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Courte description de la catégorie..." class="field resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Couleur</label>
                        <div class="flex flex-wrap items-center gap-2.5">
                            @foreach (['#0EA5EA', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#64748B', '#0F172A'] as $swatch)
                                <button type="button" wire:click="$set('couleur', '{{ $swatch }}')"
                                        class="w-8 h-8 rounded-full transition ring-2 ring-offset-2 {{ $couleur === $swatch ? 'ring-ink-950 scale-110' : 'ring-transparent hover:scale-110' }}"
                                        style="background-color: {{ $swatch }}"></button>
                            @endforeach
                            <label class="w-8 h-8 rounded-full border-2 border-dashed border-slate-300 flex items-center justify-center cursor-pointer relative overflow-hidden hover:border-slate-400 transition shrink-0">
                                <input type="color" wire:model="couleur" class="absolute inset-0 opacity-0 cursor-pointer">
                                <span class="text-slate-400 text-sm leading-none">+</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <p class="text-sm font-medium text-ink-950">Catégorie active</p>
                            <p class="text-xs text-slate-500">Visible dans le catalogue produits</p>
                        </div>
                        <x-toggle wire:model="active" @checked($active) />
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.confirm-delete')
</div>
