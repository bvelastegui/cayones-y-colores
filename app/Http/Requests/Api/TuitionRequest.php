<?php

namespace App\Http\Requests\Api;

use App\Enums\TuitionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TuitionRequest extends FormRequest
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
            'amount' => [$this->required(), 'numeric', 'min:0'],
            'generation_date' => [$this->required(), 'date'],
            'due_date' => [$this->required(), 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(TuitionStatus::class)],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
