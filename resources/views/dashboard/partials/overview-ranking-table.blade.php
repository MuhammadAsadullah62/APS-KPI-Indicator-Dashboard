@php
    use App\Enums\StaffStatusEnum;
    $rows = $rows ?? collect();
    $viewer = $viewer ?? auth()->user();
    $showStatusColumn = (bool) ($showStatusColumn ?? false);
    $colspan = $showStatusColumn ? 9 : 8;
@endphp
<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h3 class="text-xl font-black text-slate-800">{{ $title }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[720px]">
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
                        <td class="px-8 py-5 text-sm font-semibold text-slate-700 max-w-[14rem]">
                            <span class="line-clamp-3">{{ $row['user']->departmentsLabelForDisplay() }}</span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <span class="text-lg font-black text-emerald-600">{{ round($row['avg_score']) }}%</span>
                        </td>
                        @if ($showStatusColumn)
                            <td class="px-8 py-5">
                                <x-dashboard.performance-status-chip :status="$rowStatus" size="sm" />
                            </td>
                        @endif
                        <td class="px-8 py-5 text-right text-sm font-bold text-slate-600">{{ (int) $row['observation_count'] }}</td>
                        <td class="px-8 py-5 text-right">
                            @if ($viewer->canOpenObservationsPortalForObservee($row['user']))
                                @php($obsUrl = route('observations', ['observee' => $row['user']->id]))
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ $obsUrl }}" class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">View</a>
                                    <a href="{{ $obsUrl }}" class="inline-flex items-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-aps-green transition-colors">Edit</a>
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
