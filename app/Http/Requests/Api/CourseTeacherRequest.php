<?php

namespace App\Http\Requests\Api;

use App\Enums\AssignedRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseTeacherRequest extends FormRequest
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
            'course_id' => [$this->required(), 'exists:courses,id'],
            'teacher_id' => [$this->required(), 'exists:teachers,id'],
            'assigned_role' => [$this->required(), 'string', Rule::enum(AssignedRole::class)],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
