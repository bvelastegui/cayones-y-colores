<?php

namespace App\Http\Requests\Api;

use App\Models\Representative;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RepresentativeRequest extends FormRequest
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
            'id_card' => [$this->required(), 'string', 'max:50', Rule::unique('representatives')->ignore($this->representative())],
            'first_name' => [$this->required(), 'string', 'max:100'],
            'last_name' => [$this->required(), 'string', 'max:100'],
            'email' => [
                $this->required(),
                'email',
                'max:255',
                Rule::unique('representatives')->ignore($this->representative()),
                Rule::unique('users')->ignore($this->representative()?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function representative(): ?Representative
    {
        $representative = $this->route('representative');

        return $representative instanceof Representative ? $representative : null;
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
