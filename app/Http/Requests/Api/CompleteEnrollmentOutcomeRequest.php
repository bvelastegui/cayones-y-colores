<?php

namespace App\Http\Requests\Api;

use App\Enums\EnrollmentOutcome;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteEnrollmentOutcomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'outcome' => ['required', Rule::enum(EnrollmentOutcome::class)],
            'override_level_id' => ['nullable', 'integer', 'exists:levels,id'],
            'reason' => ['nullable', 'required_with:override_level_id', 'string', 'max:500'],
        ];
    }
}
