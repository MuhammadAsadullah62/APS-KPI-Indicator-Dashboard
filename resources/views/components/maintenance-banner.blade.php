@php
    use Illuminate\Support\Carbon;

    $cfg = config('maintenance.banner', []);
    $tz = $cfg['timezone'] ?? config('app.timezone');

    $start = null;
    $end = null;
    try {
        $start = filled($cfg['starts_at'] ?? null) ? Carbon::parse($cfg['starts_at'], $tz) : null;
        $end = filled($cfg['ends_at'] ?? null) ? Carbon::parse($cfg['ends_at'], $tz) : null;
    } catch (\Throwable) {
        $start = $end = null;
    }

    // Show it right up until the window closes, then it removes itself.
    $show = ($cfg['enabled'] ?? false) && $end !== null && Carbon::now()->lt($end);

    $fmt = fn (?Carbon $d) => $d?->timezone($tz)->isoFormat('ddd D MMM, h:mm A');
@endphp

@if ($show)
    {{-- Push page content down by the banner height on every layout. Vanishes with the banner. --}}
    <style>body{padding-top:2.75rem!important}@media (min-width:640px){body{padding-top:2.5rem!important}}</style>

    <div role="alert"
        class="fixed inset-x-0 top-0 z-[9999] bg-amber-500 text-amber-950 shadow-md ring-1 ring-amber-600/30">
        <div class="mx-auto flex max-w-6xl items-start gap-2.5 px-4 py-2 text-[13px] font-semibold leading-snug sm:items-center sm:py-1.5">
            <svg class="mt-0.5 h-4 w-4 shrink-0 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <p class="min-w-0">
                <span class="font-black uppercase tracking-wide">Scheduled maintenance</span>
                — the site will be unavailable from
                <span class="whitespace-nowrap font-black">{{ $fmt($start) }}</span>
                to
                <span class="whitespace-nowrap font-black">{{ $fmt($end) }}</span>
                while we upgrade the system. We&rsquo;re sorry for the inconvenience.
            </p>
        </div>
    </div>
@endif
