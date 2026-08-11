<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès refusé — DISMAT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="text-center max-w-sm">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 text-brand-700 text-2xl mb-5">🔒</span>
        <h1 class="font-display text-2xl font-semibold text-ink-950 mb-2">Accès refusé</h1>
        <p class="text-slate-500 text-sm mb-6">
            {{ $exception->getMessage() ?: "Vous n'avez pas les droits nécessaires pour accéder à cette page." }}
        </p>
        <a href="{{ route('dashboard') }}" class="inline-block px-5 py-2.5 rounded-lg bg-ink-950 hover:bg-ink-900 text-white text-sm font-medium transition">
            Retour au tableau de bord
        </a>
    </div>
</body>
</html>
