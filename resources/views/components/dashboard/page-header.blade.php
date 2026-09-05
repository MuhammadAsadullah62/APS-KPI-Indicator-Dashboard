@props([
    'variant' => 'simple',
    'title',
    'subtitle' => null,
    'chip' => null,
    'dateRange' => null,
    'showDateRange' => true,
    'padding' => 'px-4 py-4 sm:px-8 sm:py-6',
    'actions' => null,
    'staffStatus' => null,
    'showStaffStatus' => false,
])
@php
    $resolvedDateChip = $dateRange ?? now()->startOfMonth()->format('M j') . ' – ' . now()->endOfMonth()->format('M j, Y');
@endphp
<header class="bg-white {{ $padding }} border-b border-slate-200 shrink-0 min-w-0">
    @if($variant === 'profile')
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <div class="flex min-w-0 items-center gap-1 sm:gap-2">
                <x-dashboard.mobile-menu-open-button />
                <h2 class="min-w-0 flex-1 truncate text-xl font-black tracking-tight text-slate-800 sm:text-2xl">{{ $title }}</h2>
            </div>
            <div class="flex shrink-0 items-center justify-end gap-2 sm:gap-4 md:gap-5">
                @if($showStaffStatus)
                    <x-dashboard.performance-status-chip :status="$staffStatus" class="shrink-0" />
                @endif
                @if($showDateRange)
                    <div class="hidden md:flex items-center gap-3 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-600 font-bold text-xs select-none">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ $resolvedDateChip }}
                    </div>
                @endif
                <x-dashboard.user-menu />
            </div>
        </div>
    @elseif($variant === 'session')
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-1 sm:items-center sm:gap-2">
                <x-dashboard.mobile-menu-open-button class="self-center sm:self-start" />
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-black uppercase leading-none tracking-tight text-slate-800 sm:text-2xl">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-500 sm:mt-2 sm:text-xs">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-3 sm:gap-6">
                @if($chip)
                    <div class="hidden min-w-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 sm:flex sm:gap-3 sm:px-4 sm:py-2">
                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="truncate">{{ $chip }}</span>
                    </div>
                @endif
                <x-dashboard.user-menu />
            </div>
        </div>
    @elseif($variant === 'bare')
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-1">
                <x-dashboard.mobile-menu-open-button class="mt-0.5 self-start sm:mt-1" />
                <div class="min-w-0">
                    <h2 class="text-2xl font-black uppercase leading-none tracking-tight text-slate-800 sm:text-3xl">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400 sm:mt-3 sm:text-sm">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-4">
                {{ $actions ?? '' }}
                <x-dashboard.user-menu />
            </div>
        </div>
    @else
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-1 sm:items-center sm:gap-2">
                <x-dashboard.mobile-menu-open-button class="self-center" />
                <div class="min-w-0">
                    <h2 class="text-lg font-black uppercase text-slate-800 sm:text-2xl">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 sm:text-xs">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-4">
                @isset($actions)
                    <div class="max-w-full min-w-0 flex flex-wrap justify-end gap-2">
                        {{ $actions }}
                    </div>
                @endisset
                <x-dashboard.user-menu />
            </div>
        </div>
    @endif
</header>
