<?php

namespace App\Http\Requests\Concerns;

use App\Enums\Department;

/**
 * Shared "departments[]" handling for the faculty / section-head form requests.
 */
trait NormalizesDepartmentInput
{
    /**
     * De-duplicated list of submitted department values (strings only).
     *
     * @return list<string>
     */
    public function normalizedDepartments(): array
    {
        return collect($this->input('departments', []))
            ->map(fn ($v) => is_string($v) ? $v : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasOtherDepartment(): bool
    {
        return in_array(Department::Other->value, $this->normalizedDepartments(), true);
    }
}
