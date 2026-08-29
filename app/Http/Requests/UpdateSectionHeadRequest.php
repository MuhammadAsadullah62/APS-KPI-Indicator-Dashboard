<?php

namespace App\Http\Requests;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Enums\Wing;
use App\Http\Requests\Concerns\ValidatesSectionHeadOtherDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSectionHeadRequest extends FormRequest
{
    use ValidatesSectionHeadOtherDepartment;

    public function authorize(): bool
    {
        return $this->user()?->can('sectionheads.manage') ?? false;
    }

    public function rules(): array
    {

        $subject = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['prohibited'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subject->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'wing' => [
                'required',
                Rule::enum(Wing::class),
                Rule::unique('users', 'wing')
                    ->ignore($subject->id)
                    ->where(fn ($query) => $query->where('role', UserRole::SectionHead->value)),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => [Rule::enum(Department::class)],
            'other_department_label' => ['nullable', 'string', 'max:120'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {

        $subject = $this->route('user');

        $this->validateSectionHeadOtherDepartment($validator, $subject?->id);
    }
}
