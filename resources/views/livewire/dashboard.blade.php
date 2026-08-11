<div class="space-y-6">

    {{-- En-tête de bienvenue --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl font-bold text-ink-950 truncate">Bonjour, {{ auth()->user()->nom }}</h1>
            <p class="text-slate-500 truncate">
                {{ auth()->user()->tenant?->nom }} — {{ ucfirst(now()->translatedFormat('l j F Y')) }}
            </p>
            @if ($mesVentes)
                <span class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-medium">
                    <x-icon name="eye" class="w-3.5 h-3.5" /> Vos statistiques personnelles uniquement
                </span>
            @endif
        </div>
        <div class="flex flex-wrap sm:flex-nowrap gap-2 w-full sm:w-auto">
            <a href="{{ route('caisse.index') }}" class="btn-primary flex-1 sm:flex-initial justify-center">
                <span>+</span> Nouvelle vente
            </a>
            <a href="{{ route('caisse.index') }}" class="btn-secondary flex-1 sm:flex-initial justify-center">
                <span>💼</span> Ouvrir caisse
            </a>
        </div>
    </div>

    {{-- Cartes de statistiques --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Ventes du jour</p>
                <span class="text-slate-400 text-base">🧾</span>
            </div>
            <p class="text-2xl font-display font-bold text-ink-950 truncate">{{ number_format($ventesJour, 0, ',', ' ') }} F</p>
            <p class="text-xs text-slate-500 flex items-center gap-1 mt-1.5">
                <span class="{{ $varJour['up'] ? 'text-emerald-500' : 'text-red-500' }}">{{ $varJour['up'] ? '↗' : '↘' }}</span>
                <span class="font-medium {{ $varJour['up'] ? 'text-emerald-500' : 'text-red-500' }}">{{ $varJour['up'] ? '+' : '-' }}{{ number_format($varJour['pct'], 1) }}%</span>
                <span>vs hier · {{ $nbFacturesJour }} ventes</span>
            </p>
        </div>

        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Ventes du mois</p>
                <span class="text-slate-400 text-base">📊</span>
            </div>
            <p class="text-2xl font-display font-bold text-ink-950 truncate">{{ number_format($ventesMois, 0, ',', ' ') }} F</p>
            <p class="text-xs text-slate-500 flex items-center gap-1 mt-1.5">
                <span class="{{ $varMois['up'] ? 'text-emerald-500' : 'text-red-500' }}">{{ $varMois['up'] ? '↗' : '↘' }}</span>
                <span class="font-medium {{ $varMois['up'] ? 'text-emerald-500' : 'text-red-500' }}">{{ $varMois['up'] ? '+' : '-' }}{{ number_format($varMois['pct'], 1) }}%</span>
                <span>vs mois dernier</span>
            </p>
        </div>

        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Créances clients</p>
                <span class="text-slate-400 text-base">💳</span>
            </div>
            <p class="text-2xl font-display font-bold text-ink-950 truncate">{{ number_format($totalCreances, 0, ',', ' ') }} F</p>
            <p class="text-xs text-slate-500 mt-1.5">{{ $nbClients }} clients enregistrés</p>
        </div>

        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Dettes fournisseurs</p>
                <span class="text-slate-400 text-base">💰</span>
            </div>
            <p class="text-2xl font-display font-bold text-ink-950 truncate">{{ number_format($totalDettes, 0, ',', ' ') }} F</p>
            <p class="text-xs text-slate-500 mt-1.5">En cours</p>
        </div>
    </div>

    {{-- Graphique + Top produits --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h3 class="font-display font-semibold text-ink-950">Évolution des ventes</h3>
            <p class="text-sm text-slate-500 mb-4">7 derniers jours (factures payées)</p>
            <div style="height: 280px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="font-display font-semibold text-ink-950">Top produits</h3>
            <p class="text-sm text-slate-500 mb-4">Les plus vendus ce mois (par revenu)</p>

            @forelse ($topProduits as $index => $p)
                <div class="flex items-center justify-between gap-3 py-2.5 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700 shrink-0">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-medium text-sm text-ink-950 truncate">{{ $p->designation }}</p>
                            <p class="text-xs text-slate-400">{{ (int) $p->total_vendu }} vendus</p>
                        </div>
                    </div>
                    <p class="font-semibold text-sm text-ink-950 shrink-0">{{ number_format($p->revenue, 0, ',', ' ') }} F</p>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400">
                    <p class="text-3xl mb-2">📦</p>
                    <p class="text-sm">Aucune vente enregistrée ce mois</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Alertes stock / Actions rapides / Résumé --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-display font-semibold text-ink-950">Alertes stock</h3>
                    <p class="text-sm text-slate-500">Produits à reconstituer</p>
                </div>
                @if ($produitsEnAlerte->count())
                    <span class="badge bg-red-600 text-white">{{ $produitsEnAlerte->count() }}</span>
                @endif
            </div>

            @if ($produitsEnAlerte->count())
                <div class="space-y-2">
                    @foreach ($produitsEnAlerte as $p)
                        <div class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-red-50/60 border border-red-100">
                            <span class="text-sm font-medium text-ink-950 truncate flex items-center gap-2">
                                <span class="text-red-500">⚠️</span> {{ $p->designation }}
                            </span>
                            <span class="badge bg-red-100 text-red-700 shrink-0">{{ (int) ($p->stock_total ?? 0) }} / {{ $p->stock_min }}</span>
                        </div>
                    @endforeach
                    <a href="{{ route('stocks.index') }}" class="btn-secondary w-full mt-2">Voir tout →</a>
                </div>
            @else
                <div class="text-center py-8 text-slate-400">
                    <p class="text-3xl mb-2">📦</p>
                    <p class="text-sm">Tous les stocks sont OK</p>
                </div>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="font-display font-semibold text-ink-950 mb-4">Actions rapides</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('caisse.index') }}" class="flex flex-col items-center justify-center h-20 gap-2 rounded-lg border border-slate-200 text-sm font-medium transition hover:bg-brand-50 hover:border-brand-200 active:scale-95">
                    <span class="text-xl">🛒</span>
                    <span class="text-xs text-ink-950">Nouvelle vente</span>
                </a>
                <a href="{{ route('produits.index') }}" class="flex flex-col items-center justify-center h-20 gap-2 rounded-lg border border-slate-200 text-sm font-medium transition hover:bg-brand-50 hover:border-brand-200 active:scale-95">
                    <span class="text-xl">📦</span>
                    <span class="text-xs text-ink-950">Ajouter produit</span>
                </a>
                <a href="{{ route('clients.index') }}" class="flex flex-col items-center justify-center h-20 gap-2 rounded-lg border border-slate-200 text-sm font-medium transition hover:bg-brand-50 hover:border-brand-200 active:scale-95">
                    <span class="text-xl">👥</span>
                    <span class="text-xs text-ink-950">Clients</span>
                </a>
                <a href="{{ route('achats.index') }}" class="flex flex-col items-center justify-center h-20 gap-2 rounded-lg border border-slate-200 text-sm font-medium transition hover:bg-brand-50 hover:border-brand-200 active:scale-95">
                    <span class="text-xl">🧾</span>
                    <span class="text-xs text-ink-950">Nouvel achat</span>
                </a>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="font-display font-semibold text-ink-950 mb-4">Résumé</h3>
            <div class="space-y-3.5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-slate-500"><span>📦</span> Produits actifs</span>
                    <span class="font-semibold text-ink-950">{{ $nbProduits }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-slate-500"><span>👥</span> Clients</span>
                    <span class="font-semibold text-ink-950">{{ $nbClients }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-slate-500"><span>💵</span> Marge estimée jour</span>
                    <span class="font-semibold {{ $margeJour >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($margeJour, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-slate-500"><span>🕒</span> Dernière vente</span>
                    <span class="font-semibold text-ink-950">{{ $derniereVente?->created_at?->diffForHumans() ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function renderSalesChart() {
                const ctx = document.getElementById('salesChart');
                if (!ctx || typeof Chart === 'undefined') return;
                if (ctx._chartInstance) { ctx._chartInstance.destroy(); }
                ctx._chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Ventes (F CFA)',
                            data: @json($chartData),
                            borderColor: '#0EA5EA',
                            backgroundColor: 'rgba(14, 165, 234, 0.12)',
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3,
                            pointBackgroundColor: '#0EA5EA',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR').format(v) } },
                        },
                    },
                });
            }
            renderSalesChart();
        })();
    </script>
</div>
