@php
    $viewer = auth()->user();
    $avgAggregate = $avgAggregate ?? null;
    $wingRankRow = $wingRankRow ?? null;
    $staffRankRow = $staffRankRow ?? null;
    $staffRankedCount = (int) ($staffRankedCount ?? 0);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · All staff', 'row' => $topStaff ?? null])
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · Section heads', 'row' => $topSectionHead ?? null])
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · Teachers (your wing)', 'row' => $topWingTeacher ?? null])
</div>

<div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden mb-8">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Your position</p>
    <div class="flex flex-col lg:flex-row lg:items-start gap-8">
        <div class="flex items-center gap-4 shrink-0">
            <x-avatar :user="$viewer" box="h-16 w-16 rounded-2xl" :px="64"
                img-class="border border-slate-100 shadow-sm shrink-0"
                fallback-class="bg-aps-green text-white text-xl shrink-0" />
            <div class="min-w-0">
                <p class="font-black text-slate-800 truncate leading-tight text-lg">{{ $viewer->name }}</p>
                @if ($avgAggregate !== null)
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ round($avgAggregate) }}<span class="text-sm text-slate-400 font-bold">%</span> <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">obs. avg</span></p>
                @else
                    <p class="text-sm font-semibold text-slate-400 mt-1">No observation average yet</p>
                @endif
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ (int) ($observationCount ?? 0) }} leadership {{ (int) ($observationCount ?? 0) === 1 ? 'visit' : 'visits' }}</p>
            </div>
        </div>
        <div class="flex-1 space-y-5 pt-2 lg:pt-0 lg:border-t-0 border-t border-slate-100 lg:border-l lg:pl-8 lg:border-t-0">
            @include('dashboard.partials.overview-rank-position-bar', [
                'title' => 'All staff (heads + teachers)',
                'rank' => $staffRankRow['rank'] ?? null,
                'total' => $staffRankedCount,
            ])
            @if ($viewer->wing)
                @include('dashboard.partials.overview-rank-position-bar', [
                    'title' => 'Teachers · '.$viewer->wing->label().' (your wing)',
                    'rank' => $wingRankRow['rank'] ?? null,
                    'total' => (int) ($wingRankedCount ?? 0),
                ])
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-4">
                    <p class="text-xs font-bold text-slate-500">Assign a wing on your profile to see your rank among teachers in your wing.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="your-ranking" class="mb-10 rounded-[2rem] border border-emerald-100 bg-gradient-to-br from-emerald-50/90 to-white p-6 md:p-8 shadow-sm scroll-mt-24">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-black text-slate-800">Your ranking · quick view</h3>
            <p class="text-sm font-semibold text-slate-500 mt-1">Scroll down for overall staff and your wing teacher tables.</p>
        </div>
        <a href="#faculty-leaderboards" class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-aps-green transition-colors shrink-0">Jump to tables</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @include('dashboard.partials.overview-rank-position-bar', [
            'title' => 'All staff',
            'rank' => $staffRankRow['rank'] ?? null,
            'total' => $staffRankedCount,
        ])
        @if ($viewer->wing)
            @include('dashboard.partials.overview-rank-position-bar', [
                'title' => 'Teachers · '.$viewer->wing->label(),
                'rank' => $wingRankRow['rank'] ?? null,
                'total' => (int) ($wingRankedCount ?? 0),
            ])
        @else
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-4 flex items-center justify-center text-center">
                <p class="text-xs font-bold text-slate-500">Assign a wing on your profile to see your wing teacher ranking.</p>
            </div>
        @endif
    </div>
</div>

<div id="faculty-leaderboards" class="space-y-10 scroll-mt-24">
    @include('dashboard.partials.overview-ranking-table', [
        'title' => 'Overall staff rankings',
        'rows' => $rankStaff ?? collect(),
        'viewer' => $viewer,
    ])

    @if ($viewer->wing)
        @php($ownWingRows = ($rankFacultyByWing ?? collect())->get($viewer->wing->value) ?? collect())
        @include('dashboard.partials.overview-ranking-table', [
            'title' => 'Teachers · '.$viewer->wing->label().' (your wing)',
            'rows' => $ownWingRows,
            'viewer' => $viewer,
        ])
    @else
        <div class="rounded-[2rem] border border-dashed border-slate-200 bg-slate-50 px-8 py-10 text-center">
            <p class="text-sm font-semibold text-slate-500">Assign a wing on your profile to view the teacher leaderboard for your wing.</p>
        </div>
    @endif
</div>

<div class="mt-12 space-y-6">
    @include('dashboard.partials.overview-observations-section-heading')
    @include('dashboard.partials.overview-metrics-panels', [
        'metrics' => $observeeMetrics ?? ['quantitative' => [], 'qualitative' => []],
        'aggregatedSessions' => $rubricAggregatedSessions ?? 0,
        'kpiQuantAveragePercent' => $kpiQuantAveragePercent ?? null,
        'kpiQualAveragePercent' => $kpiQualAveragePercent ?? null,
    ])
    @include('dashboard.partials.overview-observation-remarks', ['observationRemarks' => $observationRemarks ?? null])
</div>
