<?php

namespace App\Http\Requests\Api;

use App\Enums\AcademicPeriodStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicPeriodRequest extends FormRequest
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
            'name' => [
                $this->required(),
                'string',
                'max:150',
                Rule::unique('academic_periods')->ignore($this->route('academic_period')),
            ],
            'starts_on' => [$this->required(), 'date'],
            'ends_on' => [$this->required(), 'date', 'after:starts_on'],
            'enrollment_opens_at' => [$this->required(), 'date', 'before:enrollment_closes_at'],
            'enrollment_closes_at' => [$this->required(), 'date', 'before_or_equal:starts_on'],
            'status' => [$this->required(), Rule::enum(AcademicPeriodStatus::class)],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
