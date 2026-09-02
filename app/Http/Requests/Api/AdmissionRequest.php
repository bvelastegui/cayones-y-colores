<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdmissionRequest extends FormRequest
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
            'level_id' => [$this->required(), 'exists:levels,id'],
            'applicant_first_name' => [$this->required(), 'string', 'max:100'],
            'applicant_last_name' => [$this->required(), 'string', 'max:100'],
            'applicant_birth_date' => [$this->required(), 'date'],
            'representative_names' => [$this->required(), 'string', 'max:255'],
            'contact_email' => [$this->required(), 'email', 'max:255'],
            'contact_phone' => [$this->required(), 'string', 'max:50'],
            'application_date' => [$this->required(), 'date'],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
