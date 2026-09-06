<?php

namespace App\Http\Requests;

use App\Enums\Wing;
use App\Models\User;
use App\Support\ObservationAnalytics;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateObservationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $sessions = $this->input('sessions_payload');
        if (is_string($sessions)) {
            $decoded = json_decode($sessions, true);
            $this->merge([
                'sessions_payload' => is_array($decoded) ? $decoded : [],
            ]);
        }

        foreach (['observation_wing', 'observation_department'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function authorize(): bool
    {

        $observation = $this->route('observation');

        return $observation !== null && ($this->user()?->canEditObservation($observation) ?? false);
    }

    public function rules(): array
    {

        $observation = $this->route('observation');
        $observee = $observation?->observee;
        $needsWingDepartment = $observee !== null && ($observee->isSectionHead() || $observee->isFaculty());

        $allowedDepartments = User::observationDepartmentAllowedValues();
        if ($needsWingDepartment && $observation?->observation_department !== null) {
            $existing = $observation->observation_department->value;
            if (! in_array($existing, $allowedDepartments, true)) {
                $allowedDepartments[] = $existing;
            }
        }

        return [
            'aggregate_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sessions_payload' => ['required', 'array', 'min:1'],
            'notes' => ['nullable', 'string', 'max:50000'],
            'observation_wing' => $needsWingDepartment
                ? ['required', Rule::enum(Wing::class)]
                : ['nullable', Rule::enum(Wing::class)],
            'observation_department' => $needsWingDepartment
                ? ['required', Rule::in($allowedDepartments)]
                : ['nullable', Rule::in(User::observationDepartmentAllowedValues())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {

            $observation = $this->route('observation');
            $observee = $observation->observee;
            if ($observee === null || ! $this->user()->canObserveUser($observee)) {
                $validator->errors()->add('observee_id', 'Invalid observation target.');

                return;
            }
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $sessions = $this->input('sessions_payload', []);
            if (! is_array($sessions)) {
                $validator->errors()->add('sessions_payload', 'Invalid observation sessions data.');

                return;
            }
            if (! ObservationAnalytics::sessionsPayloadRubricIsComplete($sessions)) {
                $validator->errors()->add(
                    'sessions_payload',
                    'Each session must have a 1–5 rating for all quantitative and all qualitative rubric items before saving.',
                );
            } elseif (ObservationAnalytics::computeWeightedAggregateFromSessionsPayload($sessions) === null) {
                $validator->errors()->add('sessions_payload', 'Could not compute a valid observation score. Check all rubric items are set.');
            }
        });
    }
}
