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
</head>
<body class="min-h-screen bg-ink-950">
    <div class="min-h-screen lg:grid lg:grid-cols-2">

        {{-- Panneau de marque --}}
        <div class="relative hidden lg:flex flex-col justify-between bg-ink-950 text-white p-12 overflow-hidden">
            <div class="absolute inset-0 blueprint-grid"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-brand-400/10 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <x-brand-icon size="w-10 h-10" iconSize="w-5 h-5" />
                <span class="font-display font-semibold text-xl tracking-tight">DISMAT</span>
            </div>

            <div class="relative">
                <p class="text-brand-300 text-sm font-medium tracking-wide uppercase mb-3">Gestion de stock &amp; caisse</p>
                <h1 class="font-display text-4xl xl:text-5xl font-semibold leading-tight tracking-tight text-white">
                    Votre boutique,<br>maîtrisée du stock<br>à la caisse.
                </h1>
                <p class="mt-5 text-brand-100/70 text-base max-w-sm">
                    Ventes, achats, stock, créances et trésorerie réunis dans un seul outil, pensé pour DISMAT.
                </p>
            </div>

            <div class="relative flex items-center gap-6 text-xs text-brand-100/50">
                <span>© {{ date('Y') }} DISMAT</span>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="flex items-center justify-center p-6 sm:p-12 bg-slate-50 min-h-screen lg:min-h-0">
            <div class="w-full max-w-sm">
                <div class="lg:hidden flex items-center gap-2.5 mb-8">
                    <x-brand-icon size="w-9 h-9" iconSize="w-5 h-5" rounded="rounded-xl" />
                    <span class="font-display font-semibold text-lg text-ink-950">DISMAT</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
