<?php

namespace App\Http\Requests\Api;

use App\Enums\TeacherType;
use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
            'id_card' => [$this->required(), 'string', 'max:50', Rule::unique('teachers')->ignore($this->teacher())],
            'first_name' => [$this->required(), 'string', 'max:100'],
            'last_name' => [$this->required(), 'string', 'max:100'],
            'email' => [
                $this->required(),
                'email',
                'max:255',
                Rule::unique('teachers')->ignore($this->teacher()),
                Rule::unique('users')->ignore($this->teacher()?->user_id),
            ],
            'teacher_type' => [$this->required(), 'string', Rule::enum(TeacherType::class)],
        ];
    }

    private function teacher(): ?Teacher
    {
        $teacher = $this->route('teacher');

        return $teacher instanceof Teacher ? $teacher : null;
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
