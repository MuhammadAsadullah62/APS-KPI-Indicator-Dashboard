@php
    $deptChips = $row->departmentChipLabels();
@endphp

<x-directory.row-card
    :row="$row"
    :wing-label="$row->wing?->label()"
    :dept-chips="$deptChips"
    :manage="auth()->user()->can('faculty.manage')"
    read-only-label="View only"
    handler-prefix="openFaculty"
    avatar-fallback-class="bg-emerald-500 text-white text-base shadow-sm"
    :view-payload="[
        'name' => $row->name,
        'employee_id' => $row->employee_id,
        'email' => $row->email,
        'departments_display' => $row->departmentsLabelForDisplay(),
        'wing_label' => $row->wing?->label(),
        'avatar' => $row->avatarUrl(),
        'initials' => $row->initials(),
    ]"
    :edit-payload="[
        'updateUrl' => route('faculty.update', $row),
        'name' => $row->name,
        'employee_id' => $row->employee_id,
        'email' => $row->email,
        'wing' => $row->wing?->value,
        'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),
        'other_department_label' => $row->other_department_label,
        'title' => $row->title,
        'avatar' => $row->avatarUrl(),
    ]"
    :destroy-payload="['destroyUrl' => route('faculty.destroy', $row), 'name' => $row->name]"
/>
