<?php

namespace App\Http\Requests\Concerns;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\DepartmentLabelNormalizer;
use Illuminate\Validation\Validator;

trait ValidatesSectionHeadOtherDepartment
{
    protected function validateSectionHeadOtherDepartment(Validator $validator, ?int $ignoreUserId): void
    {
        $validator->after(function (Validator $validator) use ($ignoreUserId): void {
            $departments = collect($this->input('departments', []))
                ->filter(fn ($v) => is_string($v))
                ->values()
                ->all();

            $hasOther = in_array(Department::Other->value, $departments, true);
            $rawLabel = trim((string) $this->input('other_department_label', ''));

            if (! $hasOther && $rawLabel !== '') {
                $validator->errors()->add(
                    'other_department_label',
                    'Remove the custom department name or select Other.'
                );

                return;
            }

            if (! $hasOther) {
                return;
            }

            if ($rawLabel === '') {
                $validator->errors()->add(
                    'other_department_label',
                    'Enter the custom department name when Other is selected.'
                );

                return;
            }

            if (mb_strlen($rawLabel) < 2) {
                $validator->errors()->add(
                    'other_department_label',
                    'The custom department name must be at least 2 characters.'
                );

                return;
            }

            $norm = DepartmentLabelNormalizer::normalize($rawLabel);

            foreach (Department::cases() as $case) {
                if ($case === Department::Other) {
                    continue;
                }
                if ($norm === DepartmentLabelNormalizer::normalize($case->label())) {
                    $validator->errors()->add(
                        'other_department_label',
                        'This name matches a built-in department — pick it from the list instead of Other.'
                    );

                    return;
                }
            }

            $query = User::role([UserRole::SectionHead->value, UserRole::Faculty->value])
                ->whereNotNull('other_department_label');

            if ($ignoreUserId !== null) {
                $query->where('id', '!=', $ignoreUserId);
            }

            foreach ($query->cursor() as $existing) {
                if (DepartmentLabelNormalizer::areConflicting($rawLabel, $existing->other_department_label)) {
                    $validator->errors()->add(
                        'other_department_label',
                        'This department name is already used or too similar to another custom department.'
                    );

                    return;
                }
            }
        });
    }
}
