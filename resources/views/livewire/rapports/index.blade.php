<div class="space-y-4">
    <x-page-header title="Rapports" subtitle="Chiffre d'affaires, marge et performance">
        <x-slot:actions>
            <select wire:model.live="periode" class="field sm:w-44">
                <option value="jour">Aujourd'hui</option>
                <option value="semaine">Cette semaine</option>
                <option value="mois">Ce mois</option>
                <option value="annee">Cette année</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-xs text-slate-500">Chiffre d'affaires</p>
            <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format($ventes, 0, ',', ' ') }} F</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-slate-500">Achats</p>
            <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format($achats, 0, ',', ' ') }} F</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-slate-500">Marge estimée</p>
            <p class="text-xl font-bold {{ $margeEstimee >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ number_format($margeEstimee, 0, ',', ' ') }} F</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-slate-500">Nb ventes</p>
            <p class="text-xl font-bold text-slate-800 mt-1">{{ $nbVentes }}</p>
        </div>
    </div>

    <div wire:key="charts-{{ $periode }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card p-4 lg:col-span-2">
                <h3 class="font-display font-semibold text-ink-950 mb-3">Évolution du chiffre d'affaires</h3>
                <div style="height: 260px;">
                    <canvas id="caChart"></canvas>
                </div>
            </div>
            <div class="card p-4">
                <h3 class="font-display font-semibold text-ink-950 mb-3">Modes de paiement</h3>
                <div style="height: 260px;">
                    <canvas id="paiementsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-6">
            <h3 class="font-display font-semibold text-ink-950 mb-3">Meilleures ventes (par quantité)</h3>
            <div style="height: 280px;">
                <canvas id="topProduitsChart"></canvas>
            </div>
        </div>

        <script>
            (function () {
                const caCtx = document.getElementById('caChart');
                if (caCtx && typeof Chart !== 'undefined') {
                    new Chart(caCtx, {
                        type: 'line',
                        data: {
                            labels: @json($evolutionLabels),
                            datasets: [{
                                label: "Chiffre d'affaires",
                                data: @json($evolutionData),
                                borderColor: '#0EA5EA',
                                backgroundColor: 'rgba(14, 165, 234, 0.12)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: true,
                                pointRadius: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR').format(v) } } },
                        },
                    });
                }

                const payCtx = document.getElementById('paiementsChart');
                if (payCtx && typeof Chart !== 'undefined') {
                    new Chart(payCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($repartitionPaiements->pluck('mode_paiement')->map(fn ($m) => ucfirst(str_replace('_', ' ', $m)))),
                            datasets: [{
                                data: @json($repartitionPaiements->pluck('total')),
                                backgroundColor: ['#01225A', '#0EA5EA', '#2DB3FF', '#0B4A72', '#6FC5FF'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                        },
                    });
                }

                const topCtx = document.getElementById('topProduitsChart');
                if (topCtx && typeof Chart !== 'undefined') {
                    new Chart(topCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($topProduits->pluck('designation')),
                            datasets: [{
                                label: 'Quantité vendue',
                                data: @json($topProduits->pluck('qte_totale')),
                                backgroundColor: '#01225A',
                                borderRadius: 4,
                                maxBarThickness: 34,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { x: { beginAtZero: true } },
                        },
                    });
                }
            })();
        </script>
    </div>

    <div class="card p-4">
        <h3 class="font-display font-semibold text-ink-950 mb-3">Détail meilleures ventes</h3>
        <table class="w-full text-sm table-modern">
            <thead class="text-slate-500 text-left">
                <tr><th class="py-2">Produit</th><th class="py-2">Qté vendue</th><th class="py-2">Montant</th></tr>
            </thead>
            <tbody>
                @forelse ($topProduits as $tp)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $tp->designation }}</td>
                        <td class="py-2">{{ (int) $tp->qte_totale }}</td>
                        <td class="py-2">{{ number_format($tp->montant_total, 0, ',', ' ') }} F</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-slate-400">Pas de données pour cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
