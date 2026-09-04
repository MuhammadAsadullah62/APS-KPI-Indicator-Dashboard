@php($faDeptLine = $row->departmentsLabelForDisplay())
<article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex min-w-0 gap-3">
        <x-avatar :user="$row" box="h-12 w-12 rounded-xl" :px="48"
            img-class="shrink-0 border border-slate-100 shadow-sm"
            fallback-class="shrink-0 bg-emerald-500 text-white text-base shadow-sm" />
        <div class="min-w-0 flex-1">
            <p class="font-black leading-tight text-slate-900 [overflow-wrap:anywhere]">{{ $row->name }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase text-slate-400">{{ $row->employee_id }}</p>
        </div>
    </div>
    <div class="mt-4 space-y-1.5">
        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Departments &amp; wing</p>
        @if($faDeptLine !== '—')
            <span class="inline-block max-w-full rounded-lg bg-slate-100 px-2.5 py-1.5 text-[10px] font-black uppercase leading-relaxed tracking-widest text-slate-700 [overflow-wrap:anywhere]">{{ $faDeptLine }}</span>
            @if($row->wing)
                <p class="text-[10px] font-bold uppercase text-slate-500">{{ $row->wing->label() }}</p>
            @endif
        @else
            <p class="text-slate-400">—</p>
        @endif
    </div>
    @can('faculty.manage')
        <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
            <button type="button" onclick="openFacultyView(@js(['name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'departments_display' => $row->departmentsLabelForDisplay(),'wing_label' => $row->wing?->label(),'avatar' => $row->avatarUrl(),'initials' => $row->initials()]))" class="inline-flex min-w-[5rem] flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-600 active:bg-slate-100 sm:py-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                View
            </button>
            <button type="button" onclick="openFacultyEdit(@js(['updateUrl' => route('faculty.update', $row),'name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing' => $row->wing?->value,'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),'other_department_label' => $row->other_department_label,'title' => $row->title]))" class="inline-flex min-w-[5rem] flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-[10px] font-black uppercase tracking-widest text-aps-green active:bg-emerald-50 sm:py-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </button>
            <button type="button" onclick="openFacultyDelete(@js(['destroyUrl' => route('faculty.destroy', $row),'name' => $row->name]))" class="inline-flex min-w-[5rem] flex-1 items-center justify-center gap-1.5 rounded-xl border border-red-100 bg-red-50/80 py-2.5 text-[10px] font-black uppercase tracking-widest text-red-600 active:bg-red-100 sm:py-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
        </div>
    @else
        <p class="mt-3 border-t border-slate-100 pt-3 text-center text-[10px] font-bold uppercase text-slate-300">View only</p>
    @endcan
</article>
