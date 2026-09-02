<?php

namespace App\Http\Requests\Api;

use App\Models\Level;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelRequest extends FormRequest
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
            'name' => [$this->required(), 'string', 'max:100', Rule::unique('levels')->ignore($this->level())],
            'max_capacity' => [$this->required(), 'integer', 'min:1'],
            'student_aux_ratio' => [$this->required(), 'integer', 'min:0'],
            'enrollment_fee' => [$this->required(), 'numeric', 'min:0'],
            'monthly_fee' => [$this->required(), 'numeric', 'min:0'],
        ];
    }

    private function level(): ?Level
    {
        $level = $this->route('level');

        return $level instanceof Level ? $level : null;
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
