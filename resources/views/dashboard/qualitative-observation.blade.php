@extends('layouts.app')

@section('title', 'APSACS Khanewal | Qualitative Observations')

@section('header')
    <x-dashboard.page-header variant="session" title="Qualitative Results" subtitle="Behavioral Indicators" chip="Session: 2023-24" />
@endsection

@section('content')
            @php
                use App\Support\ObservationAnalytics;
                $qual = $qualitative ?? [];
                $qv = fn (string $name) => isset($qual[$name]) && is_numeric($qual[$name]) ? (float) $qual[$name] : null;
                $qualPct = fn (?float $s) => $s === null ? null : min(100, max(0, ($s / 5) * 100));
                $lAvg = $qualAveragePercent ?? null;
                if ($lAvg === null && isset($aggregateQualitativePercent) && $aggregateQualitativePercent !== null) {
                    $lAvg = (float) $aggregateQualitativePercent;
                }
                $lBar = $qualBarClass ?? ($lAvg !== null ? ObservationAnalytics::kpiTierBarBgClass($lAvg) : 'bg-slate-300');
            @endphp
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-200 shadow-sm p-4 sm:p-6 md:p-10 mb-6 sm:mb-8 relative overflow-hidden min-w-0">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Your qualitative observation average</p>
                <h3 class="text-4xl sm:text-5xl md:text-6xl font-black text-slate-900 leading-none">{{ $lAvg !== null ? number_format($lAvg, 1) : '—' }}<span class="text-xl sm:text-2xl font-bold text-slate-400">%</span></h3>
                <div class="mt-6 w-full h-4 rounded-full bg-slate-100 overflow-hidden">
                    @if ($lAvg !== null)
                        <div class="h-full rounded-full {{ $lBar }} transition-all" style="width: {{ min(100, max(0, $lAvg)) }}%"></div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:gap-8 lg:grid-cols-2 min-w-0">
                <div class="bg-white p-5 sm:p-8 md:p-10 rounded-2xl sm:rounded-[2.5rem] border border-slate-200 shadow-sm space-y-8 sm:space-y-10 font-semibold min-w-0">
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Student-Centricity</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Addresses individual learning needs</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Student-Centricity') !== null ? number_format($qv('Student-Centricity'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Student-Centricity'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Professional Ethics</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Integrity & APS compliance</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Professional Ethics') !== null ? number_format($qv('Professional Ethics'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Professional Ethics'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Classroom Culture</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Positive learning environment</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Classroom Culture') !== null ? number_format($qv('Classroom Culture'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Classroom Culture'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 sm:p-8 md:p-10 rounded-2xl sm:rounded-[2.5rem] border border-slate-200 shadow-sm space-y-8 sm:space-y-10 font-semibold min-w-0">
                    <div class="space-y-4">
                        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Communication</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Parent & student communication</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Communication') !== null ? number_format($qv('Communication'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Communication'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Collaboration</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Team collaboration</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Collaboration') !== null ? number_format($qv('Collaboration'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Collaboration'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-lg font-black text-slate-800">Innovation</h4>
                                <p class="text-xs text-slate-400 font-medium italic">Active & experiential learning</p>
                            </div>
                            <span class="text-xl font-black text-aps-green">{{ $qv('Innovation') !== null ? number_format($qv('Innovation'), 2) : '—' }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                            @php($s = $qv('Innovation'))
                            @if ($s !== null)
                                <div class="h-full rounded-full transition-all {{ $s !== null && $qualPct($s) !== null ? ObservationAnalytics::kpiTierBarBgClass((float) $qualPct($s)) : '' }}" style="width: {{ $qualPct($s) }}%"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-8 shrink-0"></div>
@endsection
