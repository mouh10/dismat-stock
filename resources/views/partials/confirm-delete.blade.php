@if ($confirmingDeleteId)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 backdrop-enter" wire:click.self="cancelDelete">
        <div class="bg-white rounded-2xl shadow-2xl modal-enter w-full max-w-sm p-6 text-center">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600 text-2xl mb-4">🗑️</span>
            <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">{{ $confirmTitle ?? 'Confirmer la suppression' }}</h3>
            <p class="text-sm text-slate-500 mb-6">{{ $confirmMessage ?? 'Cette action est irréversible.' }}</p>
            <div class="flex gap-3">
                <button type="button" wire:click="cancelDelete" class="btn-secondary flex-1">Annuler</button>
                <button type="button" wire:click="delete" wire:loading.attr="disabled"
                        class="flex-1 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 transition shadow-sm hover:shadow-md active:scale-[0.98] disabled:opacity-50">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
@endif
