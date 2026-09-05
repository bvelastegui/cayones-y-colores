<?php

namespace App\Http\Requests\Api;

use App\Enums\TuitionStatus;
use App\Models\Tuition;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $routeTuition = $this->route('tuition');
            $tuition = $routeTuition instanceof Tuition ? $routeTuition : null;
            $studentId = $this->input('student_id', $tuition?->student_id);
            $generationDate = $this->input(
                'generation_date',
                $tuition?->generation_date->toDateString(),
            );

            if ($studentId === null || $generationDate === null) {
                return;
            }

            $tuitionQuery = Tuition::query()
                ->where('student_id', $studentId)
                ->whereDate('billing_period', Carbon::parse($generationDate)->startOfMonth());

            if ($tuition !== null) {
                $tuitionQuery->whereKeyNot($tuition->getKey());
            }

            $duplicate = $tuitionQuery->exists();

            if ($duplicate) {
                $validator->errors()->add(
                    'generation_date',
                    'El estudiante ya tiene una pensión generada para ese mes.',
                );
            }
        }];
    }

    private function required(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
