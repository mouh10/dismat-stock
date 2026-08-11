{{-- Interrupteur style iOS. Le wire:model doit être passé comme attribut, ex: <x-toggle wire:model.live="actif" /> --}}
<label class="relative inline-flex items-center cursor-pointer shrink-0">
    <input type="checkbox" {{ $attributes->merge(['class' => 'sr-only peer']) }}>
    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-ink-950 transition-colors
                after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full
                after:h-5 after:w-5 after:transition-transform after:shadow-sm peer-checked:after:translate-x-5"></div>
</label>
