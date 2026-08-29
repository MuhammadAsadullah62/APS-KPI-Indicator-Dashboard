@php
    $row = $row ?? null;
@endphp
<div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ $label }}</p>
    @if ($row)
        <div class="flex items-center gap-4 mt-2">
            <x-avatar :user="$row['user']" box="h-14 w-14 rounded-2xl" :px="56"
                img-class="border border-slate-100 shadow-sm shrink-0"
                fallback-class="bg-aps-green text-white text-lg shrink-0" />
            <div class="min-w-0 flex-1">
                <p class="font-black text-slate-800 truncate leading-tight">{{ $row['user']->name }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Rank #{{ $row['rank'] }} · {{ (int) $row['observation_count'] }} {{ (int) $row['observation_count'] === 1 ? 'visit' : 'visits' }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-2">{{ round($row['avg_score']) }}<span class="text-sm text-slate-400 font-bold">%</span></p>
            </div>
        </div>
    @else
        <p class="text-sm font-semibold text-slate-400 mt-4">No observation data yet.</p>
    @endif
</div>
