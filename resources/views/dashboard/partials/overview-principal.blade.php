@php
    use App\Enums\Wing;
    $viewer = auth()->user();
@endphp

@if ($topStaff ?? null)
    <div class="mb-10 rounded-[2rem] border border-amber-100 bg-gradient-to-br from-amber-50/90 via-white to-emerald-50/40 p-8 md:p-10 shadow-sm overflow-hidden relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center gap-8">
            <div class="shrink-0 flex justify-center lg:justify-start">
                <x-avatar :user="$topStaff['user']" box="h-36 w-36 md:h-44 md:w-44 rounded-[2rem]" :px="176"
                    img-class="border-4 border-white shadow-xl shadow-slate-200"
                    fallback-class="bg-aps-green text-white text-5xl border-4 border-white shadow-xl shadow-slate-200" />
            </div>
            <div class="flex-1 min-w-0 text-center lg:text-left">
                <p class="text-[11px] font-black text-amber-800 uppercase tracking-[0.2em] mb-3">Total staff rankings · 1st place</p>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight leading-tight">{{ $topStaff['user']->name }}</h2>
                <div class="mt-4 flex flex-wrap items-center justify-center lg:justify-start gap-3">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/90 ring-1 ring-slate-200 text-sm font-black text-emerald-700 shadow-sm">#{{ $topStaff['rank'] }} overall</span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-900 text-white text-sm font-black">{{ round($topStaff['avg_score']) }}% avg.</span>
                    <span class="text-xs font-bold text-slate-500">{{ (int) $topStaff['observation_count'] }} {{ (int) $topStaff['observation_count'] === 1 ? 'visit' : 'visits' }}</span>
                </div>
                <p class="mt-5 text-sm font-semibold text-slate-500 max-w-xl lg:mx-0 mx-auto">Principal overview is based on institutional rankings only — observation metrics appear on section head and teacher dashboards.</p>
            </div>
        </div>
    </div>
@else
    <div class="mb-10 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-8 py-10 text-center">
        <p class="text-sm font-semibold text-slate-500">Total staff leaderboard will appear once observations are recorded.</p>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · All staff', 'row' => $topStaff ?? null])
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · Section heads', 'row' => $topSectionHead ?? null])
    @include('dashboard.partials.overview-top-card', ['label' => 'Top ranking · Teachers', 'row' => $topTeacher ?? null])
</div>

<div class="space-y-10">
    @include('dashboard.partials.overview-ranking-table', [
        'title' => 'Overall staff rankings',
        'rows' => $rankStaff ?? collect(),
        'viewer' => $viewer,
    ])

    @include('dashboard.partials.overview-ranking-table', [
        'title' => 'Section head rankings',
        'rows' => $rankSectionHeads ?? collect(),
        'viewer' => $viewer,
    ])

    @include('dashboard.partials.overview-ranking-table', [
        'title' => 'Teacher rankings (all wings)',
        'rows' => $rankFaculty ?? collect(),
        'viewer' => $viewer,
    ])

    @foreach (Wing::cases() as $wing)
        @php($wingRows = ($rankFacultyByWing ?? collect())->get($wing->value) ?? collect())
        @include('dashboard.partials.overview-ranking-table', [
            'title' => 'Teachers · '.$wing->label(),
            'rows' => $wingRows,
            'viewer' => $viewer,
        ])
    @endforeach
</div>
