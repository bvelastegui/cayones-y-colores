<?php

namespace App\Http\Requests\Api;

use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
            'representative_id' => [$this->required(), 'exists:representatives,id'],
            'id_card' => [$this->required(), 'string', 'max:50', Rule::unique('students')->ignore($this->student())],
            'first_name' => [$this->required(), 'string', 'max:100'],
            'last_name' => [$this->required(), 'string', 'max:100'],
            'birth_date' => [$this->required(), 'date'],
        ];
    }

    private function student(): ?Student
    {
        $student = $this->route('student');

        return $student instanceof Student ? $student : null;
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
