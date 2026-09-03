<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tuition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TuitionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentIds = Student::pluck('id')->toArray();

        foreach ($studentIds as $studentId) {
            $tuitionCount = fake()->numberBetween(1, 3);

            for ($monthsAgo = 0; $monthsAgo < $tuitionCount; $monthsAgo++) {
                $billingPeriod = now()->startOfMonth()->subMonths($monthsAgo);
                $generationDate = $billingPeriod->copy()->addDays(fake()->numberBetween(0, 8));

                Tuition::factory()->create([
                    'student_id' => $studentId,
                    'generation_date' => $generationDate->toDateString(),
                    'billing_period' => $billingPeriod->toDateString(),
                    'due_date' => $billingPeriod->copy()->setDay(10)->toDateString(),
                ]);
            }
        }
    }
}
