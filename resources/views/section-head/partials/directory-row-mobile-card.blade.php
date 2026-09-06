@php
    use App\Enums\Wing;
    $takenWingValuesByOthers = $sectionHeads->where('id', '!=', $row->id)->pluck('wing')->filter()->map(fn ($w) => $w->value)->all();
    $allowedWingsForRow = collect(Wing::cases())->filter(function (Wing $w) use ($takenWingValuesByOthers, $row) {
        if (in_array($w->value, $takenWingValuesByOthers, true)) {
            return $row->wing && $row->wing->value === $w->value;
        }
        return true;
    })->values()->map(fn (Wing $w) => ['value' => $w->value, 'label' => $w->label()])->all();
    $deptChips = $row->departmentChipLabels();
@endphp

<x-directory.row-card
    :row="$row"
    :wing-label="$row->wing?->label()"
    :dept-chips="$deptChips"
    :manage="auth()->user()->can('sectionheads.manage')"
    handler-prefix="openSecHead"
    :view-payload="[
        'name' => $row->name,
        'employee_id' => $row->employee_id,
        'email' => $row->email,
        'wing_label' => $row->wing?->label(),
        'departments_display' => $deptChips->implode(', ') ?: '—',
        'avatar' => $row->avatarUrl(),
        'initials' => $row->initials(),
    ]"
    :edit-payload="[
        'updateUrl' => route('section-heads.update', $row),
        'name' => $row->name,
        'employee_id' => $row->employee_id,
        'email' => $row->email,
        'wing' => $row->wing?->value,
        'title' => $row->title,
        'departments' => array_values(array_filter(array_map(fn ($v) => is_string($v) ? $v : null, $row->departments ?? []))),
        'other_department_label' => $row->other_department_label,
        'allowedWings' => $allowedWingsForRow,
        'avatar' => $row->avatarUrl(),
    ]"
    :destroy-payload="['destroyUrl' => route('section-heads.destroy', $row), 'name' => $row->name]"
/>
