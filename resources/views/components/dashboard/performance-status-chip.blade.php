@props([
    'status' => null,
    /** @var 'sm'|'md'|'pill' */
    'size' => 'md',
])
@php
    /** @var \App\Enums\StaffStatusEnum|null $status */

    $sizeClasses = match ($size) {
        'sm' => 'gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider whitespace-nowrap shrink-0',
        'pill' => 'gap-2 px-4 py-1.5 rounded-full text-sm font-black whitespace-nowrap shrink-0',
        default => 'gap-2 px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-widest whitespace-nowrap shrink-0',
    };

    $dotSize = $size === 'sm' ? 'h-1.5 w-1.5' : 'h-2 w-2';

    $toneClasses = $status
        ? $status->chipClasses()
        : 'border-slate-200 bg-slate-50 text-slate-500';

    $dotClass = match ($status) {
        \App\Enums\StaffStatusEnum::OnTrack => 'bg-emerald-500',
        \App\Enums\StaffStatusEnum::OffTrack => 'bg-amber-500',
        \App\Enums\StaffStatusEnum::AtRisk => 'bg-rose-500',
        default => 'bg-slate-300',
    };

    $label = $status?->label() ?? 'No status';
@endphp
<div {{ $attributes->merge([
    'class' => 'inline-flex items-center border select-none '.$sizeClasses.' '.$toneClasses,
]) }} title="{{ $status ? 'Performance status' : 'No observation average yet' }}">
    <span class="{{ $dotSize }} rounded-full shrink-0 {{ $dotClass }}"></span>
    {{ $label }}
</div>
