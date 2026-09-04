<?php

namespace App\Http\Requests;

use App\Enums\Department;
use App\Enums\Wing;
use App\Http\Requests\Concerns\NormalizesDepartmentInput;
use App\Http\Requests\Concerns\ValidatesSectionHeadOtherDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFacultyRequest extends FormRequest
{
    use NormalizesDepartmentInput;
    use ValidatesSectionHeadOtherDepartment;

    public function authorize(): bool
    {
        return $this->user()?->can('faculty.manage') ?? false;
    }

    public function rules(): array
    {
        $subject = $this->route('user');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['prohibited'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subject->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => [Rule::enum(Department::class)],
            'other_department_label' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        $rules['wing'] = $this->user()?->isSectionHead()
            ? ['prohibited']
            : ['required', Rule::enum(Wing::class)];

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $subject = $this->route('user');

        $this->validateSectionHeadOtherDepartment($validator, $subject?->id);
    }
}
