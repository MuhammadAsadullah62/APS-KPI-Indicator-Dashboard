<div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-2xl transition-all group/row font-semibold">
    <div class="flex items-center gap-3 min-w-0">
        <x-avatar :user="$row" box="h-8 w-8 rounded-lg" :px="32"
            img-class="shadow-sm shrink-0 border border-slate-100"
            fallback-class="bg-aps-green text-white text-xs shadow-sm shrink-0" />
        <div class="leading-none min-w-0">
            <p class="text-xs font-black text-slate-800 truncate">{{ $row->name }}</p>
            <p class="text-[9px] text-slate-400 uppercase mt-1 truncate">{{ $row->departmentsLabelForDisplay() }}</p>
        </div>
    </div>
    <div class="flex items-center gap-1 shrink-0">
        <x-directory.view-button variant="compact" handler="openFacultyView" :payload="[
            'name' => $row->name,
            'employee_id' => $row->employee_id,
            'email' => $row->email,
            'departments_display' => $row->departmentsLabelForDisplay(),
            'wing_label' => $row->wing?->label(),
            'avatar' => $row->avatarUrl(),
            'initials' => $row->initials(),
        ]" />
        @unless($readOnly ?? false)
        <button type="button" onclick="openFacultyEdit(@js(['updateUrl' => route('faculty.update', $row),'name' => $row->name,'employee_id' => $row->employee_id,'email' => $row->email,'wing' => $row->wing?->value,'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),'other_department_label' => $row->other_department_label,'title' => $row->title,'avatar' => $row->avatarUrl()]))" class="p-1.5 text-slate-300 hover:text-aps-green transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
        <button type="button" onclick="openFacultyDelete(@js(['destroyUrl' => route('faculty.destroy', $row),'name' => $row->name]))" class="p-1.5 text-slate-300 hover:text-red-500 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
        @endunless
    </div>
</div>
