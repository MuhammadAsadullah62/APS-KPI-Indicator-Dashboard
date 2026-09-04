@php
    use App\Enums\Department;
    use App\Enums\Wing;
    $takenWingValuesByOthers = $sectionHeads->where('id', '!=', $row->id)->pluck('wing')->filter()->map(fn ($w) => $w->value)->all();
    $allowedWingsForRow = collect(Wing::cases())->filter(function (Wing $w) use ($takenWingValuesByOthers, $row) {
        if (in_array($w->value, $takenWingValuesByOthers, true)) {
            return $row->wing && $row->wing->value === $w->value;
        }
        return true;
    })->values()->map(fn (Wing $w) => ['value' => $w->value, 'label' => $w->label()])->all();
    $deptLabelsForRow = collect($row->departments ?? [])->map(function ($v) use ($row) {
        if (! is_string($v)) {
            return null;
        }
        if ($v === Department::Other->value) {
            return filled($row->other_department_label)
                ? 'Other ('.$row->other_department_label.')'
                : 'Other';
        }
        return Department::tryFrom($v)?->label();
    })->filter()->values();
@endphp
<article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm min-w-0">
    <div class="flex gap-3 min-w-0">
        <x-avatar :user="$row" box="h-12 w-12 rounded-xl" :px="48"
            img-class="shrink-0 shadow-sm ring-1 ring-slate-100"
            fallback-class="shrink-0 bg-aps-green text-white text-base shadow-sm" />
        <div class="min-w-0 flex-1">
            <p class="font-black leading-tight text-slate-900 [overflow-wrap:anywhere]">{{ $row->name }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase text-slate-400">{{ $row->employee_id }}</p>
        </div>
    </div>
    <div class="mt-4 space-y-3 text-sm">
        <div>
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Wing</p>
            @if($row->wing)
                <span class="mt-0.5 inline-block rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $row->wing->label() }}</span>
            @else
                <p class="text-slate-400">—</p>
            @endif
        </div>
        <div>
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Departments</p>
            @if($deptLabelsForRow->isNotEmpty())
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    @foreach ($deptLabelsForRow as $lbl)
                        <span class="inline-block rounded-md border border-emerald-100/80 bg-emerald-50 px-2 py-0.5 text-[10px] font-black text-aps-green">{{ $lbl }}</span>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400">—</p>
            @endif
        </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
        <button type="button" onclick="openSecHeadView(@js(['name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing_label' => $row->wing?->label(),'departments_display' => $deptLabelsForRow->implode(', ') ?: '—','avatar' => $row->avatarUrl(),'initials' => $row->initials()]))" class="inline-flex flex-1 min-w-[5rem] items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-600 active:bg-slate-100 sm:py-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            View
        </button>
        @can('sectionheads.manage')
            <button type="button" onclick="openSecHeadEdit(@js(['updateUrl' => route('section-heads.update', $row),'name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing' => $row->wing?->value,'title' => $row->title,'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),'other_department_label' => $row->other_department_label,'allowedWings' => $allowedWingsForRow]))" class="inline-flex flex-1 min-w-[5rem] items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-[10px] font-black uppercase tracking-widest text-aps-green active:bg-emerald-50 sm:py-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </button>
            <button type="button" onclick="openSecHeadDelete(@js(['destroyUrl' => route('section-heads.destroy', $row),'name' => $row->name]))" class="inline-flex flex-1 min-w-[5rem] items-center justify-center gap-1.5 rounded-xl border border-red-100 bg-red-50/80 py-2.5 text-[10px] font-black uppercase tracking-widest text-red-600 active:bg-red-100 sm:py-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
        @endcan
    </div>
</article>
