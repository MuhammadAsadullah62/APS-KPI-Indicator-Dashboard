@php
    $remarks = $observationRemarks ?? ['session_blocks' => [], 'overall_blocks' => []];
    $sessionBlocks = $remarks['session_blocks'] ?? [];
    $overallBlocks = $remarks['overall_blocks'] ?? [];
    $hasVisits = count($overallBlocks) > 0 || count($sessionBlocks) > 0;
@endphp

<section class="mt-12 scroll-mt-24" aria-label="Observation remarks">
    <div class="mb-6">
        <h3 class="text-lg font-black text-slate-800">Observation remarks</h3>
        <p class="text-sm font-semibold text-slate-500 mt-1">Per-session and overall notes from your received observations (ordered by visit date).</p>
    </div>

    @if (! $hasVisits)
        <p class="text-sm font-semibold text-slate-500 rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-8 text-center">No observation visits yet, so there are no remarks to show.</p>
    @else
        <div class="space-y-6">
            @if (count($sessionBlocks) > 0)
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">By session</h4>
                    <div class="space-y-5">
                        @foreach ($sessionBlocks as $block)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                                <p class="text-xs font-black text-aps-green uppercase tracking-widest mb-2">Observation session {{ (int) $block['n'] }} remarks</p>
                                <p class="text-[10px] font-semibold text-slate-400 mb-2">
                                    @if (! empty($block['observed_at']) && $block['observed_at'] instanceof \Illuminate\Support\Carbon)
                                        {{ $block['observed_at']->timezone(config('app.timezone'))->format('M j, Y') }}
                                    @endif
                                    @if (! empty($block['observer_name']))
                                        <span class="text-slate-500">· {{ $block['observer_name'] }}</span>
                                    @endif
                                </p>
                                <p class="text-sm font-semibold text-slate-700 whitespace-pre-wrap">{{ filled($block['text'] ?? null) ? $block['text'] : '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-[2rem] border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/30 p-6 md:p-8 shadow-sm">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Overall observation remarks</h4>
                <div class="space-y-5">
                    @foreach ($overallBlocks as $idx => $ob)
                        <div class="rounded-2xl border border-emerald-100/80 bg-white/90 px-5 py-4">
                            <p class="text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Observation visit {{ $idx + 1 }}</p>
                            <p class="text-[10px] font-semibold text-slate-400 mb-2">
                                @if (! empty($ob['observed_at']) && $ob['observed_at'] instanceof \Illuminate\Support\Carbon)
                                    {{ $ob['observed_at']->timezone(config('app.timezone'))->format('M j, Y g:i a') }}
                                @endif
                                @if (! empty($ob['observer_name']))
                                    <span class="text-slate-500">· {{ $ob['observer_name'] }}</span>
                                @endif
                            </p>
                            <p class="text-sm font-semibold text-slate-800 whitespace-pre-wrap">{{ filled($ob['text'] ?? null) ? $ob['text'] : '—' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
