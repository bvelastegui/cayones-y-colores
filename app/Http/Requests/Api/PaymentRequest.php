<?php

namespace App\Http\Requests\Api;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
            'tuition_id' => [$this->required(), 'exists:tuitions,id'],
            'payment_method' => [$this->required(), 'string', Rule::enum(PaymentMethod::class)],
            'amount_paid' => [$this->required(), 'numeric', 'min:0'],
            'payment_date' => [$this->required(), 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
