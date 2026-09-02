<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcademicReportRequest extends FormRequest
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
            'teacher_id' => [$this->required(), 'exists:teachers,id'],
            'development_area' => [$this->required(), 'string', 'max:100'],
            'evaluated_skill' => [$this->required(), 'string', 'max:100'],
            'achievement_level' => [$this->required(), 'string', 'max:100'],
            'observations' => ['nullable', 'string'],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
