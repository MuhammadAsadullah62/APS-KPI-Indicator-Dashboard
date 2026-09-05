@props([
    'variant' => 'icon',
    'handler',
    'payload' => [],
])

@php
    $onclick = $handler.'('.\Illuminate\Support\Js::from($payload).')';

    $button = match ($variant) {
        'pill' => 'inline-flex flex-1 min-w-[5rem] items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-600 active:bg-slate-100 sm:py-2',
        'compact' => 'p-1.5 text-slate-300 transition-all hover:text-aps-green',
        default => 'rounded-lg border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-aps-green',
    };

    $iconClass = $variant === 'icon' ? 'h-5 w-5' : 'h-4 w-4';
    $strokeWidth = $variant === 'compact' ? 2.5 : 2;
@endphp

<button type="button" onclick="{{ $onclick }}" class="{{ $button }}">
    <x-icon.eye :class="$iconClass" :stroke-width="$strokeWidth" />
    @if ($variant === 'pill') View @endif
</button>
