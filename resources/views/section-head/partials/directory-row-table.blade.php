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
<tr class="group transition-colors hover:bg-emerald-50/30">
    <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        <div class="flex min-w-0 items-center gap-4">
            <x-avatar :user="$row" box="h-12 w-12 rounded-2xl" :px="48"
                img-class="border border-slate-100 shadow-sm"
                fallback-class="bg-aps-green text-white text-lg shadow-sm" />
            <div>
                <p class="font-black leading-none text-slate-800">{{ $row->name }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase text-slate-400">{{ $row->employee_id }}</p>
            </div>
        </div>
    </td>
    <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        @if($row->wing)
            <span class="rounded-lg bg-slate-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-600">{{ $row->wing->label() }}</span>
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="align-top px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        @if($deptLabelsForRow->isNotEmpty())
            <div class="flex max-w-md flex-wrap gap-2">
                @foreach ($deptLabelsForRow as $lbl)
                    <span class="rounded-lg border border-emerald-100/80 bg-emerald-50 px-3 py-1 text-[10px] font-black text-aps-green">{{ $lbl }}</span>
                @endforeach
            </div>
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="px-3 py-4 text-right sm:px-5 sm:py-5 md:px-10 md:py-6">
        <div class="flex justify-end gap-3 opacity-60 transition-opacity group-hover:opacity-100">
            <button type="button" onclick="openSecHeadView(@js(['name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing_label' => $row->wing?->label(),'departments_display' => $deptLabelsForRow->implode(', ') ?: '—','avatar' => $row->avatarUrl(),'initials' => $row->initials()]))" class="rounded-lg border border-transparent p-2 text-slate-400 shadow-sm hover:border-slate-100 hover:bg-white hover:text-aps-green"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
            @can('sectionheads.manage')
                <button type="button" onclick="openSecHeadEdit(@js(['updateUrl' => route('section-heads.update', $row),'name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing' => $row->wing?->value,'title' => $row->title,'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),'other_department_label' => $row->other_department_label,'allowedWings' => $allowedWingsForRow,'avatar' => $row->avatarUrl()]))" class="rounded-lg border border-transparent p-2 text-slate-400 shadow-sm hover:border-slate-100 hover:bg-white hover:text-aps-green"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button type="button" onclick="openSecHeadDelete(@js(['destroyUrl' => route('section-heads.destroy', $row),'name' => $row->name]))" class="rounded-lg border border-transparent p-2 text-slate-400 shadow-sm hover:border-slate-100 hover:bg-white hover:text-red-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            @endcan
        </div>
    </td>
</tr>
