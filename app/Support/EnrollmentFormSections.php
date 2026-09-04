<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class EnrollmentFormSections
{
    /** @var list<string> */
    public const ORDER = [
        'student',
        'health',
        'conditions',
        'allergies',
        'medications',
        'address',
        'legal_representative',
        'billing',
        'emergency_contacts',
        'insurance',
    ];

    /** @return array<string, list<mixed>> */
    public static function rulesFor(string $section): array
    {
        return match ($section) {
            'student' => [
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'birth_date' => ['required', 'date', 'before:today'],
                'preferred_name' => ['nullable', 'string', 'max:100'],
                'gender' => ['nullable', 'string', 'max:50'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'birth_place' => ['nullable', 'string', 'max:150'],
                'previous_institution' => ['nullable', 'string', 'max:150'],
                'previous_level' => ['nullable', 'string', 'max:100'],
                'academic_background' => ['nullable', 'string', 'max:5000'],
                'educational_needs' => ['nullable', 'string', 'max:5000'],
                'educational_supports' => ['nullable', 'string', 'max:5000'],
                'languages' => ['nullable', 'string', 'max:250'],
            ],
            'health' => [
                'blood_type' => ['nullable', 'string', 'max:10'],
                'pediatrician_name' => ['nullable', 'string', 'max:150'],
                'pediatrician_phone' => ['nullable', 'string', 'max:30'],
                'developmental_notes' => ['nullable', 'string', 'max:5000'],
                'care_instructions' => ['nullable', 'string', 'max:5000'],
                'medical_observations' => ['nullable', 'string', 'max:5000'],
                'additional_notes' => ['nullable', 'string', 'max:5000'],
            ],
            'conditions' => [
                'none' => ['required', 'boolean'],
                'items' => ['present', 'array', 'max:20'],
                'items.*.name' => ['required', 'string', 'max:150'],
                'items.*.details' => ['nullable', 'string', 'max:3000'],
                'items.*.care_instructions' => ['nullable', 'string', 'max:3000'],
            ],
            'allergies' => [
                'none' => ['required', 'boolean'],
                'items' => ['present', 'array', 'max:20'],
                'items.*.allergen' => ['required', 'string', 'max:150'],
                'items.*.severity' => ['required', Rule::in(['mild', 'moderate', 'severe'])],
                'items.*.reaction' => ['nullable', 'string', 'max:3000'],
                'items.*.response_instructions' => ['nullable', 'string', 'max:3000'],
            ],
            'medications' => [
                'none' => ['required', 'boolean'],
                'items' => ['present', 'array', 'max:20'],
                'items.*.name' => ['required', 'string', 'max:150'],
                'items.*.dose' => ['nullable', 'string', 'max:100'],
                'items.*.schedule' => ['nullable', 'string', 'max:150'],
                'items.*.prescriber' => ['nullable', 'string', 'max:150'],
                'items.*.instructions' => ['nullable', 'string', 'max:3000'],
            ],
            'address' => [
                'country' => ['required', 'string', 'max:100'],
                'province' => ['required', 'string', 'max:100'],
                'city' => ['required', 'string', 'max:100'],
                'parish' => ['nullable', 'string', 'max:100'],
                'main_street' => ['required', 'string', 'max:150'],
                'secondary_street' => ['nullable', 'string', 'max:150'],
                'house_number' => ['nullable', 'string', 'max:50'],
                'reference' => ['nullable', 'string', 'max:250'],
                'residence_type' => ['nullable', 'string', 'max:100'],
                'housing_relationship' => ['nullable', 'string', 'max:100'],
            ],
            'legal_representative' => [
                'relationship' => ['required', 'string', 'max:50'],
                'id_type' => ['required', Rule::in(['cedula', 'ruc', 'passport'])],
                'id_number' => ['required', 'string', 'max:30'],
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'birth_date' => ['nullable', 'date', 'before:today'],
                'marital_status' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
                'occupation' => ['nullable', 'string', 'max:100'],
                'workplace' => ['nullable', 'string', 'max:150'],
                'work_phone' => ['nullable', 'string', 'max:30'],
                'address' => ['nullable', 'string', 'max:250'],
            ],
            'billing' => [
                'person_type' => ['required', Rule::in(['natural', 'company'])],
                'tax_id_type' => ['required', Rule::in(['cedula', 'ruc', 'passport'])],
                'tax_id' => ['required', 'string', 'max:30'],
                'business_name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'address' => ['required', 'string', 'max:250'],
            ],
            'emergency_contacts' => [
                'items' => ['required', 'array', 'min:1', 'max:3'],
                'items.*.full_name' => ['required', 'string', 'max:150'],
                'items.*.relationship' => ['required', 'string', 'max:50'],
                'items.*.phone' => ['required', 'string', 'max:30'],
                'items.*.alternate_phone' => ['nullable', 'string', 'max:30'],
                'items.*.address' => ['nullable', 'string', 'max:250'],
                'items.*.authorized_pickup' => ['required', 'boolean'],
            ],
            'insurance' => [
                'has_insurance' => ['required', 'boolean'],
                'provider' => ['nullable', 'required_if:has_insurance,true', 'string', 'max:150'],
                'policy_number' => ['nullable', 'required_if:has_insurance,true', 'string', 'max:100'],
                'plan_name' => ['nullable', 'string', 'max:100'],
                'policy_holder' => ['nullable', 'required_if:has_insurance,true', 'string', 'max:150'],
                'emergency_phone' => ['nullable', 'string', 'max:30'],
                'expires_on' => ['nullable', 'date'],
            ],
            default => ['section' => ['prohibited']],
        };
    }

    public static function next(string $section): string
    {
        $position = array_search($section, self::ORDER, true);

        if ($position === false || ! isset(self::ORDER[$position + 1])) {
            return 'review';
        }

        return self::ORDER[$position + 1];
    }

    /** @param array<string, mixed> $data */
    public static function hasExplicitMedicalListChoice(string $section, array $data): bool
    {
        if (! in_array($section, ['conditions', 'allergies', 'medications'], true)) {
            return true;
        }

        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $declaresNone = ($data['none'] ?? null) === true;

        return $declaresNone ? $items === [] : $items !== [];
    }
}
