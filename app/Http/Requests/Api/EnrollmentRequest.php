<?php

namespace App\Http\Requests\Api;

use App\Enums\EnrollmentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollmentRequest extends FormRequest
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
            'student_id' => [$this->required(), 'exists:students,id'],
            'course_id' => [$this->required(), 'exists:courses,id'],
            'enrollment_date' => [$this->required(), 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(EnrollmentStatus::class)],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
