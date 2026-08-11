<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'DISMAT' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="min-h-screen flex" x-data="{ mobileMenuOpen: false }">

    {{-- Fond assombri derrière le menu mobile --}}
    <div x-show="mobileMenuOpen" x-transition.opacity x-on:click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden" style="display: none;"></div>

    {{-- Sidebar : tiroir plein écran sur mobile, fixe sur desktop --}}
    <aside x-cloak
           :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col shrink-0 transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-100">
            <x-brand-icon />
            <div class="min-w-0 flex-1">
                <p class="font-display font-bold text-lg text-ink-950 leading-tight truncate">DISMAT</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->tenant?->nom }}</p>
            </div>
            <button type="button" x-on:click="mobileMenuOpen = false" class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 shrink-0">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5 text-sm">
            @php
                $user = auth()->user();

                $sections = [
                    [
                        'label' => 'Principal',
                        'items' => [
                            ['route' => 'dashboard', 'label' => 'Tableau de bord', 'icon' => 'layout-dashboard', 'roles' => ['admin', 'gestionnaire', 'caissier']],
                            ['route' => 'caisse.index', 'label' => 'Caisse', 'icon' => 'wallet', 'roles' => ['admin', 'gestionnaire', 'caissier'], 'badge' => 'POS'],
                        ],
                    ],
                    [
                        'label' => 'Gestion',
                        'items' => [
                            ['route' => 'ventes.index', 'label' => 'Ventes', 'icon' => 'receipt', 'roles' => ['admin', 'gestionnaire', 'caissier']],
                            ['route' => 'produits.index', 'label' => 'Produits', 'icon' => 'package', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'categories.index', 'label' => 'Catégories', 'icon' => 'tag', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'clients.index', 'label' => 'Clients', 'icon' => 'users', 'roles' => ['admin', 'gestionnaire', 'caissier']],
                            ['route' => 'creances.index', 'label' => 'Créances', 'icon' => 'credit-card', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'fournisseurs.index', 'label' => 'Fournisseurs', 'icon' => 'truck', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'achats.index', 'label' => 'Achats', 'icon' => 'shopping-cart', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'dettes.index', 'label' => 'Dettes', 'icon' => 'dollar-sign', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'tresorerie.index', 'label' => 'Trésorerie', 'icon' => 'trending-down', 'roles' => ['admin', 'gestionnaire']],
                        ],
                    ],
                    [
                        'label' => 'Stock',
                        'items' => [
                            ['route' => 'stocks.index', 'label' => 'Stocks', 'icon' => 'warehouse', 'roles' => ['admin', 'gestionnaire']],
                        ],
                    ],
                    [
                        'label' => 'Boutique',
                        'items' => [
                            ['route' => 'magasins.index', 'label' => 'Magasins', 'icon' => 'store', 'roles' => ['admin']],
                            ['route' => 'equipe.index', 'label' => 'Équipe', 'icon' => 'users', 'roles' => ['admin']],
                            ['route' => 'rapports.index', 'label' => 'Rapports', 'icon' => 'bar-chart-3', 'roles' => ['admin', 'gestionnaire']],
                            ['route' => 'parametres.index', 'label' => 'Paramètres', 'icon' => 'settings', 'roles' => ['admin']],
                        ],
                    ],
                ];
            @endphp

            @foreach ($sections as $section)
                @php $items = array_filter($section['items'], fn ($item) => $user->hasRole(...$item['roles'])); @endphp
                @if (count($items))
                    <div>
                        <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $section['label'] }}</p>
                        <div class="space-y-0.5">
                            @foreach ($items as $item)
                                @php $active = request()->routeIs($item['route']); @endphp
                                <a href="{{ route($item['route']) }}" x-on:click="mobileMenuOpen = false"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                          {{ $active ? 'bg-brand-50 text-brand-800 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-ink-950' }}">
                                    <x-icon :name="$item['icon']" class="w-[18px] h-[18px] shrink-0" />
                                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="p-3 border-t border-slate-100 flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-brand-500 text-white flex items-center justify-center text-sm font-medium font-display shrink-0">
                {{ mb_substr($user->nom, 0, 1) }}{{ mb_substr($user->prenom ?? '', 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-ink-950 truncate">{{ $user->nom }} {{ $user->prenom }}</p>
                <p class="text-xs text-slate-400 capitalize truncate">{{ $user->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Déconnexion" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-600 transition">
                    <span class="text-base">↪</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 sticky top-0 z-30 bg-white/90 backdrop-blur-sm border-b border-slate-200 flex items-center gap-3 px-4 lg:px-6">
            <button type="button" x-on:click="mobileMenuOpen = true" class="lg:hidden w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 shrink-0">
                <x-icon name="menu" class="w-5 h-5" />
            </button>

            <form action="{{ route('produits.index') }}" method="GET" class="flex-1 max-w-md">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input id="global-search" type="text" name="search" placeholder="Rechercher un produit... (Ctrl+K)"
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm
                                  focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none transition">
                </div>
            </form>

            <div class="flex-1"></div>

            <div class="flex items-center gap-2">
                <livewire:alerts.bell />

                <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button type="button" x-on:click="open = !open" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-lg hover:bg-slate-50 transition">
                        <div class="w-8 h-8 rounded-full bg-ink-950 text-white flex items-center justify-center text-xs font-medium font-display">
                            {{ mb_substr($user->nom, 0, 1) }}{{ mb_substr($user->prenom ?? '', 0, 1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-ink-950">{{ $user->nom }} {{ $user->prenom }}</span>
                        <span class="text-slate-400 text-xs">▾</span>
                    </button>
                    <div x-show="open" x-transition style="display: none;"
                         class="absolute right-0 mt-2 w-52 bg-white rounded-xl border border-slate-200 shadow-lg py-1.5 z-50">
                        @if ($user->hasRole('admin'))
                            <a href="{{ route('parametres.index') }}" class="block px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Paramètres</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>

<script>
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            document.getElementById('global-search')?.focus();
        }
    });
</script>

@livewireScripts
</body>
</html>
