<x-layouts.guest title="Connexion — DISMAT">
    <h2 class="font-display text-2xl font-semibold text-ink-950 mb-1">Content de vous revoir</h2>
    <p class="text-slate-500 text-sm mb-6">Connectez-vous pour accéder à votre espace DISMAT.</p>

    @if ($errors->any())
        <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-100 text-red-700 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
            <input type="password" name="password" required
                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:outline-none shadow-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-500/30 focus:outline-none cursor-pointer">
            Se souvenir de moi
        </label>
        <button type="submit" class="w-full py-2.5 rounded-lg bg-ink-950 hover:bg-ink-900 text-white font-medium text-sm transition shadow-sm hover:shadow-md active:scale-[0.98]">
            Se connecter
        </button>
    </form>
</x-layouts.guest>
