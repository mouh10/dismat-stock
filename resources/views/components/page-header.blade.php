@props(['title', 'subtitle' => null, 'icon' => null])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="min-w-0">
        <div class="flex items-center gap-2.5">
            @if ($icon)
                <x-icon :name="$icon" class="w-6 h-6 text-ink-950 shrink-0" />
            @endif
            <h1 class="font-display text-2xl font-bold text-ink-950 truncate">{{ $title }}</h1>
        </div>
        @if ($subtitle)
            <p class="text-slate-500 mt-1 truncate">{!! $subtitle !!}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2 shrink-0">{{ $actions }}</div>
    @endisset
</div>
