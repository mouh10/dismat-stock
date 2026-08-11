<div class="relative" x-data x-on:click.outside="$wire.open = false">
    <button wire:click="toggle" type="button" class="relative w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center transition">
        <span class="text-slate-600"><x-icon name="bell" class="w-5 h-5" /></span>
        @if ($total > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">
                {{ $total > 9 ? '9+' : $total }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-slate-200 shadow-lg z-50 max-h-96 overflow-y-auto">
            <div class="p-3 border-b border-slate-100 font-semibold text-sm text-ink-950">Alertes</div>

            @if ($total === 0)
                <p class="p-4 text-sm text-slate-400 text-center">Aucune alerte pour le moment 🎉</p>
            @else
                @if ($produitsStockBas->count())
                    <div class="px-3 pt-3 pb-1 text-xs font-medium text-slate-500 uppercase">Stock bas</div>
                    @foreach ($produitsStockBas as $p)
                        <a href="{{ route('stocks.index') }}" class="flex justify-between items-center px-3 py-2 text-sm hover:bg-slate-50">
                            <span class="truncate">{{ $p->designation }}</span>
                            <span class="text-red-600 font-medium text-xs ml-2 shrink-0">{{ (int) ($p->stock_total ?? 0) }} / min {{ $p->stock_min }}</span>
                        </a>
                    @endforeach
                @endif

                @if ($creancesEnRetard->count())
                    <div class="px-3 pt-3 pb-1 text-xs font-medium text-slate-500 uppercase">Créances en retard</div>
                    @foreach ($creancesEnRetard as $c)
                        <a href="{{ route('creances.index') }}" class="flex justify-between items-center px-3 py-2 text-sm hover:bg-slate-50">
                            <span class="truncate">{{ $c->client?->nom }}</span>
                            <span class="text-amber-600 font-medium text-xs ml-2 shrink-0">{{ number_format($c->montant_restant, 0, ',', ' ') }} F</span>
                        </a>
                    @endforeach
                @endif
            @endif
        </div>
    @endif
</div>
