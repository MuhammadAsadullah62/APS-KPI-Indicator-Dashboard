@php($faDeptLine = $row->departmentsLabelForDisplay())
<tr class="group transition-colors hover:bg-emerald-50/30">
    <td class="px-3 py-4 sm:px-6 sm:py-5 md:px-10 md:py-6">
        <div class="flex min-w-0 items-center gap-3 sm:gap-4">
            @if($row->avatarUrl())
                <img src="{{ $row->avatarUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-2xl border border-slate-100 object-cover shadow-sm">
            @else
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-lg font-black text-white shadow-sm">{{ $row->initials() }}</div>
            @endif
            <div>
                <p class="font-black leading-none text-slate-800">{{ $row->name }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase text-slate-400">{{ $row->employee_id }}</p>
            </div>
        </div>
    </td>
    <td class="min-w-0 px-3 py-4 sm:px-6 sm:py-5 md:px-10 md:py-6">
        @if($faDeptLine !== '—')
            <span class="inline-block max-w-xs rounded-lg bg-slate-100 px-4 py-1.5 text-[10px] font-black uppercase leading-relaxed tracking-widest text-slate-600">{{ $faDeptLine }}</span>
            @if($row->wing)
                <p class="mt-1 text-[10px] font-bold uppercase text-slate-400">{{ $row->wing->label() }}</p>
            @endif
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="min-w-0 px-3 py-4 text-right sm:px-6 sm:py-5 md:px-10 md:py-6">
        @can('faculty.manage')
            <div class="flex justify-end gap-3 opacity-60 transition-opacity group-hover:opacity-100">
                <button type="button" onclick="openFacultyView(@js(['name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'departments_display' => $row->departmentsLabelForDisplay(),'wing_label' => $row->wing?->label(),'avatar' => $row->avatarUrl(),'initials' => $row->initials()]))" class="rounded-xl border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-aps-green"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                <button type="button" onclick="openFacultyEdit(@js(['updateUrl' => route('faculty.update', $row),'name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing' => $row->wing?->value,'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),'other_department_label' => $row->other_department_label,'title' => $row->title]))" class="rounded-xl border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-aps-green"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button type="button" onclick="openFacultyDelete(@js(['destroyUrl' => route('faculty.destroy', $row),'name' => $row->name]))" class="rounded-xl border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-red-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </div>
        @else
            <span class="text-[10px] font-bold uppercase text-slate-300">View only</span>
        @endcan
    </td>
</tr>
