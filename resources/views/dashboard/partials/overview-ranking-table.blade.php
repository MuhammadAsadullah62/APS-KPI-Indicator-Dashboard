@php
    use App\Enums\StaffStatusEnum;
    $rows = $rows ?? collect();
    $viewer = $viewer ?? auth()->user();
    $showStatusColumn = (bool) ($showStatusColumn ?? false);
    $colspan = $showStatusColumn ? 9 : 8;
@endphp
<div class="bg-white rounded-4xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h3 class="text-xl font-black text-slate-800">{{ $title }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-180">
            <thead>
                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-4">Rank</th>
                    <th class="px-8 py-4">Staff</th>
                    <th class="px-8 py-4">Role</th>
                    <th class="px-8 py-4">Wing</th>
                    <th class="px-8 py-4">Dept.</th>
                    <th class="px-8 py-4 text-right">Avg. score</th>
                    @if ($showStatusColumn)
                        <th class="px-8 py-4">Status</th>
                    @endif
                    <th class="px-8 py-4 text-right">Visits</th>
                    <th class="px-8 py-4 text-right">Observations</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($rows as $row)
                    @php
                        $rowStatus = $showStatusColumn
                            ? StaffStatusEnum::fromAveragePercent(isset($row['avg_score']) ? (float) $row['avg_score'] : null)
                            : null;
                    @endphp
                    <tr class="group hover:bg-emerald-50/30 transition-colors">
                        <td class="px-8 py-5">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-800 font-black text-xs border border-amber-200">#{{ $row['rank'] }}</div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-avatar :user="$row['user']" box="h-10 w-10 rounded-xl" :px="40"
                                    img-class="border border-slate-100 shrink-0"
                                    fallback-class="bg-emerald-500 text-white text-sm shrink-0" />
                                <div class="min-w-0">
                                    <p class="font-black text-slate-800 truncate">{{ $row['user']->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $row['user']->employee_id ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $row['user']->roleLabel() }}</span>
                        </td>
                        <td class="px-8 py-5 text-sm font-bold text-slate-600">{{ $row['user']->wing?->label() ?? '—' }}</td>
                        <td class="px-8 py-5 text-sm font-semibold text-slate-700 max-w-56">
                            <span class="line-clamp-3">{{ $row['user']->departmentsLabelForDisplay() }}</span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <span class="text-lg font-black text-emerald-600">{{ round($row['avg_score']) }}%</span>
                        </td>
                        @if ($showStatusColumn)
                            <td class="px-8 py-5 whitespace-nowrap">
                                <x-dashboard.performance-status-chip :status="$rowStatus" size="sm" />
                            </td>
                        @endif
                        <td class="px-8 py-5 text-right text-sm font-bold text-slate-600">{{ (int) $row['observation_count'] }}</td>
                        <td class="px-8 py-5 text-right">
                            @if ($viewer->canOpenObservationsPortalForObservee($row['user']))
                                @php($obsUrl = route('observations', ['observee' => $row['user']->id]))
                                <div class="relative inline-flex justify-end" data-row-actions>
                                    <button
                                        type="button"
                                        class="js-row-actions-btn inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors"
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        aria-label="Observation actions"
                                    >
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                    <div
                                        class="js-row-actions-menu hidden absolute right-0 top-full z-40 mt-2 w-44 origin-top-right rounded-2xl border border-slate-200 bg-white py-2 shadow-xl"
                                        role="menu"
                                    >
                                        <a href="{{ $obsUrl }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-aps-green transition-colors" role="menuitem">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            View
                                        </a>
                                        @if ($viewer->isAdmin() || $viewer->isPrincipal())
                                            <a href="{{ $obsUrl }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-aps-green transition-colors" role="menuitem">
                                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colspan }}" class="px-8 py-12 text-center text-slate-400 font-semibold">No observation data yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('.js-row-actions-btn');
                    var openMenus = document.querySelectorAll('.js-row-actions-menu:not(.hidden)');

                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        var wrap = btn.closest('[data-row-actions]');
                        var menu = wrap ? wrap.querySelector('.js-row-actions-menu') : null;
                        openMenus.forEach(function (m) {
                            if (m !== menu) {
                                m.classList.add('hidden');
                                var otherWrap = m.closest('[data-row-actions]');
                                var otherBtn = otherWrap ? otherWrap.querySelector('.js-row-actions-btn') : null;
                                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
                            }
                        });
                        if (menu) {
                            var willOpen = menu.classList.contains('hidden');
                            menu.classList.toggle('hidden');
                            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        }
                        return;
                    }

                    if (!e.target.closest('[data-row-actions]')) {
                        openMenus.forEach(function (m) {
                            m.classList.add('hidden');
                            var w = m.closest('[data-row-actions]');
                            var b = w ? w.querySelector('.js-row-actions-btn') : null;
                            if (b) b.setAttribute('aria-expanded', 'false');
                        });
                    }
                });
            });
        </script>
    @endpush
@endonce
