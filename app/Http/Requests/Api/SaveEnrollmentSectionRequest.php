<?php

namespace App\Http\Requests\Api;

use App\Models\Enrollment;
use App\Support\EnrollmentFormSections;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveEnrollmentSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return $enrollment instanceof Enrollment
            && $enrollment->student->representative->user_id === $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return EnrollmentFormSections::rulesFor((string) $this->route('section'));
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! EnrollmentFormSections::hasExplicitMedicalListChoice(
                (string) $this->route('section'),
                $this->all(),
            )) {
                $validator->errors()->add('items', 'Declara la ausencia o registra al menos un elemento.');
            }
        }];
    }
}
