@php
    $accent = ['bg-aps-green', 'bg-emerald-500', 'bg-sky-600', 'bg-rose-500', 'bg-indigo-600', 'bg-amber-500'];
    $summaries = $summaries ?? collect();
    $observationsByObservee = $observationsByObservee ?? collect();
@endphp
<div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2 md:gap-8 xl:grid-cols-3 mb-8 sm:mb-12 min-w-0">
    @forelse ($observees as $obsUser)
        @php($bg = $accent[$loop->index % count($accent)])
        @php($summary = $summaries->get($obsUser->id))
        @php($records = $observationsByObservee->get($obsUser->id) ?? collect())
        <div id="observee-card-{{ $obsUser->id }}" class="bg-white p-5 sm:p-6 lg:p-8 xl:p-10 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl transition-all scroll-mt-24 sm:scroll-mt-28 group min-w-0">
            <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                @if($obsUser->avatarUrl())
                    <img src="{{ $obsUser->avatarUrl() }}" alt="" loading="lazy" decoding="async" width="80" height="80" class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl sm:rounded-2xl md:rounded-[2rem] object-cover shadow-lg border border-slate-100 shrink-0">
                @else
                    <div class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 {{ $bg }} rounded-xl sm:rounded-2xl md:rounded-[2rem] flex items-center justify-center text-white text-xl sm:text-2xl md:text-3xl font-black shadow-lg shrink-0">{{ $obsUser->initials() }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $obsUser->role->label() }}</p>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-black text-slate-800 group-hover:text-aps-green transition-colors leading-tight sm:leading-none truncate">{{ $obsUser->name }}</h3>
                    <p class="text-sm text-slate-500 font-bold mt-2 normal-case leading-snug">
                        @if($obsUser->wing)
                            <span class="uppercase tracking-widest text-slate-400">{{ $obsUser->wing->label() }}</span>
                        @else
                            <span class="uppercase tracking-widest text-slate-400">—</span>
                        @endif
                        @php($deptLine = $obsUser->departmentsLabelForDisplay())
                        @if($deptLine !== '—')
                            <span class="block sm:inline sm:before:content-['_•_'] sm:before:text-slate-300 mt-1 sm:mt-0">{{ $deptLine }}</span>
                        @endif
                    </p>
                </div>
            </div>
            @if ($summary && (int) $summary->observation_count > 0)
                <div class="mt-6 grid grid-cols-2 gap-3 sm:mt-8 sm:gap-4">
                    <div class="min-w-0 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4 sm:rounded-[2rem] sm:px-6 sm:py-5">
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 sm:text-[10px] sm:tracking-[0.25em]">Avg. score</p>
                        <p class="mt-1.5 text-2xl font-black text-emerald-700 sm:mt-2 sm:text-3xl">{{ (int) round((float) $summary->avg_score) }}%</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4 sm:rounded-[2rem] sm:px-6 sm:py-5">
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 sm:text-[10px] sm:tracking-[0.25em]">Observations</p>
                        <p class="mt-1.5 text-2xl font-black text-slate-800 sm:mt-2 sm:text-3xl">{{ (int) $summary->observation_count }}</p>
                    </div>
                </div>
            @endif
            @if ($records->isNotEmpty())
                <details class="js-observation-records-details mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/50 sm:mt-8 sm:rounded-[2rem]">
                    <summary class="flex min-h-[3rem] cursor-pointer list-none select-none items-center justify-between gap-3 px-4 py-3.5 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 active:bg-slate-100/60 sm:px-6 sm:py-4 sm:text-xs sm:tracking-[0.2em]">
                        <span class="[overflow-wrap:anywhere]">Observation records</span>
                        <span class="inline-flex shrink-0 items-center gap-2">
                            <span class="inline-flex min-h-[1.75rem] min-w-[1.75rem] items-center justify-center rounded-full bg-slate-200/90 px-2 text-[10px] font-black text-slate-700">{{ $records->count() }}</span>
                            <svg class="observation-records-chevron h-5 w-5 shrink-0 text-slate-400 transition [transition-property:transform]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <ul class="space-y-3 border-t border-slate-100/90 bg-white/60 px-3 pb-4 pt-3 sm:px-6 sm:pb-6 sm:pt-2">
                        @foreach ($records as $rec)
                            <li class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-sm sm:p-5">
                                <div class="space-y-3 sm:space-y-0 sm:flex sm:items-start sm:justify-between sm:gap-4">
                                    <div class="min-w-0 flex-1 space-y-2.5 sm:space-y-2">
                                        <div class="flex items-baseline justify-between gap-2 sm:justify-start sm:gap-3">
                                            <p class="text-sm font-black leading-tight text-slate-800 [overflow-wrap:anywhere]">{{ $rec->created_at->format('M j, Y') }}</p>
                                            <span class="inline-flex shrink-0 items-center rounded-xl bg-emerald-50 px-2.5 py-1.5 text-sm font-black text-emerald-800 ring-1 ring-emerald-100/80 sm:hidden">{{ $rec->aggregate_percent }}%</span>
                                        </div>
                                        <p class="text-xs font-semibold leading-snug text-slate-500 [overflow-wrap:anywhere]">
                                            <span class="text-slate-400">Observer</span>
                                            <span class="text-slate-600">{{ $rec->observer->name ?? '—' }}</span>
                                        </p>
                                        @if ($rec->observation_wing || $rec->observation_department)
                                            <div class="flex flex-wrap gap-1.5 pt-0.5">
                                                @if ($rec->observation_wing)
                                                    <span class="inline-block max-w-full rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black uppercase leading-relaxed tracking-widest text-slate-700 [overflow-wrap:anywhere]">{{ $rec->observation_wing->label() }}</span>
                                                @endif
                                                @if ($rec->observation_department)
                                                    <span class="inline-block max-w-full rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-black uppercase leading-relaxed tracking-widest text-emerald-800 [overflow-wrap:anywhere] ring-1 ring-emerald-100/80">{{ $rec->observation_department->label() }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($rec->notes)
                                            <p class="line-clamp-4 text-xs leading-relaxed text-slate-600 sm:line-clamp-3 [overflow-wrap:anywhere]">{{ $rec->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:shrink-0 sm:items-stretch sm:pl-0">
                                        <span class="hidden min-w-[4.5rem] text-center sm:block rounded-xl bg-emerald-50 px-3 py-2 text-sm font-black text-emerald-800 ring-1 ring-emerald-100/80">{{ $rec->aggregate_percent }}%</span>
                                        @if (auth()->user()?->canEditObservation($rec))
                                            @php($editPayload = [
                                                'id' => $rec->id,
                                                'observee_id' => $rec->observee_id,
                                                'observee_name' => $obsUser->name,
                                                'aggregate_percent' => $rec->aggregate_percent,
                                                'sessions_payload' => $rec->sessions_payload,
                                                'notes' => $rec->notes,
                                                'observer_id' => $rec->observer_id,
                                                'observation_wing' => $rec->observation_wing?->value,
                                                'observation_department' => $rec->observation_department?->value,
                                                'observation_department_label' => $rec->observation_department?->label(),
                                                'observee_is_faculty' => $obsUser->isFaculty(),
                                                'observee_wing_label' => $obsUser->wing?->label(),
                                                'observee_departments_label' => $obsUser->departmentsLabelForDisplay(),
                                            ])
                                            <button type="button" onclick="openEditObservation(@js($editPayload))" class="inline-flex w-full min-h-11 items-center justify-center gap-1.5 rounded-xl border border-emerald-200/90 bg-emerald-50/80 px-4 text-[11px] font-black uppercase tracking-widest text-aps-green transition [touch-action:manipulation] active:bg-emerald-100/80 sm:min-h-0 sm:justify-center sm:gap-1.5 sm:border sm:border-slate-200/90 sm:bg-white sm:px-3.5 sm:py-2 sm:text-[10px] sm:font-black sm:uppercase sm:tracking-widest sm:text-emerald-800 sm:shadow-sm sm:ring-1 sm:ring-emerald-100/80 sm:hover:border-emerald-200 sm:hover:bg-emerald-50/80 sm:hover:text-aps-green sm:hover:shadow sm:active:bg-emerald-100/50">
                                                <svg class="h-4 w-4 shrink-0 sm:h-3.5 sm:w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit observation
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
            <div class="mt-10 pt-8 border-t border-slate-50">
                <button type="button" onclick="openAuditPortal(@js([
                    'id' => $obsUser->id,
                    'name' => $obsUser->name,
                    'observee_is_faculty' => $obsUser->isFaculty(),
                    'wing_label' => $obsUser->wing?->label(),
                    'departments_label' => $obsUser->departmentsLabelForDisplay(),
                ]))" class="w-full py-5 bg-slate-900 text-white rounded-3xl font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-aps-green transition-all">
                    Open Audit Portal
                </button>
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-[2rem] border border-slate-200 bg-white p-12 text-center text-slate-500 font-semibold">
            No staff members are available for observation with your current role.
        </div>
    @endforelse
</div>
