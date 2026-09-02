<?php

namespace App\Http\Requests\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'name' => [$this->required(), 'string', 'max:255'],
            'email' => [$this->required(), 'email', 'max:255', Rule::unique('users')->ignore($this->userModel())],
            'password' => [$this->required(), 'string', 'min:8'],
            'identification' => [$this->required(), 'string', 'max:50', Rule::unique('users')->ignore($this->userModel())],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'role' => [$this->required(), 'string', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
        ];
    }

    private function userModel(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
