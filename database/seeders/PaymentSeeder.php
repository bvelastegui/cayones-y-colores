<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Tuition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tuitionIds = Tuition::pluck('id')->toArray();

        foreach ($tuitionIds as $tuitionId) {
            if (fake()->boolean(70)) {
                Payment::factory()->count(fake()->numberBetween(1, 2))->create([
                    'tuition_id' => $tuitionId,
                ]);
            }
        }
    }
}
