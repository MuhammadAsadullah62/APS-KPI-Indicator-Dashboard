@props([
    'size' => 'md',
])
@php
    $logoBox = match ($size) {
        'lg' => 'w-14 h-14 rounded-2xl',
        default => 'w-12 h-12 rounded-xl',
    };
    $titleCls = match ($size) {
        'lg' => 'text-2xl sm:text-3xl',
        default => 'text-xl sm:text-2xl',
    };
    $subtitleCls = match ($size) {
        'lg' => 'text-sm',
        default => 'text-xs',
    };
    $svg = match ($size) {
        'lg' => 'w-8 h-8',
        default => 'w-7 h-7',
    };
@endphp
<div class="{{ $size === 'lg' ? 'flex items-center gap-4 mb-12' : 'flex items-center gap-3 mb-12' }}">
    <div class="w-12 h-12 bg-aps-green rounded-xl flex items-center justify-center text-white shadow-lg">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
    </div>
    <div>
        <h1 class="{{ $titleCls }} font-extrabold tracking-tighter aps-green leading-none">APSACS</h1>
        <p class="{{ $subtitleCls }} font-bold text-slate-400 uppercase tracking-widest">Khanewal</p>
    </div>
</div>