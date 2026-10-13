@props(['url', 'filename', 'title' => 'Partager le PDF', 'label' => null])

{{--
    Partage le FICHIER PDF (et non un lien) via la feuille de partage du téléphone/ordinateur
    (WhatsApp, e-mail, Drive…). Si le navigateur ne sait pas partager un fichier, le PDF est
    simplement téléchargé pour être joint à la main.
--}}
<div class="relative inline-flex" x-data="{
        url: @js($url),
        filename: @js($filename),
        blob: null,
        loading: false,
        notice: '',
        async load() {
            if (this.blob) return this.blob;
            const res = await fetch(this.url, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('http ' + res.status);
            this.blob = await res.blob();
            return this.blob;
        },
        prefetch() { this.load().catch(() => {}); },
        say(msg) { this.notice = msg; setTimeout(() => this.notice = '', 4000); },
        download(blob) {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = this.filename;
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 5000);
        },
        async share() {
            if (this.loading) return;
            this.loading = true;
            try {
                const blob = await this.load();
                const file = new File([blob], this.filename, { type: 'application/pdf' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], title: this.filename });
                } else {
                    this.download(blob);
                    this.say('PDF téléchargé — joins-le à ton message.');
                }
            } catch (e) {
                if (e && e.name === 'AbortError') { /* partage annulé */ }
                else if (this.blob) { this.download(this.blob); this.say('PDF téléchargé — joins-le à ton message.'); }
                else this.say('Impossible de récupérer le PDF.');
            } finally { this.loading = false; }
        }
    }"
    x-on:mouseenter.once="prefetch()" x-on:touchstart.once.passive="prefetch()">
    <button type="button" x-on:click="share()" title="{{ $title }}" x-bind:disabled="loading"
            {{ $attributes->merge(['class' => 'w-8 h-8 rounded-lg flex items-center justify-center hover:bg-slate-100 hover:text-ink-950 transition disabled:opacity-50']) }}>
        <span x-show="!loading" class="inline-flex items-center gap-1.5"><x-icon name="share" class="w-[18px] h-[18px]" />@if ($label)<span>{{ $label }}</span>@endif</span>
        <span x-show="loading" x-cloak class="w-4 h-4 border-2 border-slate-300 border-t-ink-950 rounded-full animate-spin"></span>
    </button>
    <div x-show="notice" x-cloak x-transition
         class="absolute right-0 top-full mt-2 z-50 w-56 text-xs text-white bg-ink-950 rounded-lg px-3 py-2 shadow-lg" x-text="notice"></div>
</div>
