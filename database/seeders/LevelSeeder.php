<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Maternal 1', 'sequence_order' => 1, 'max_capacity' => 12, 'student_aux_ratio' => 3, 'enrollment_fee' => 150.00, 'monthly_fee' => 180.00],
            ['name' => 'Maternal 2', 'sequence_order' => 2, 'max_capacity' => 12, 'student_aux_ratio' => 3, 'enrollment_fee' => 150.00, 'monthly_fee' => 180.00],
            ['name' => 'Inicial 1', 'sequence_order' => 3, 'max_capacity' => 18, 'student_aux_ratio' => 6, 'enrollment_fee' => 170.00, 'monthly_fee' => 200.00],
            ['name' => 'Inicial 2', 'sequence_order' => 4, 'max_capacity' => 18, 'student_aux_ratio' => 6, 'enrollment_fee' => 170.00, 'monthly_fee' => 200.00],
            ['name' => 'Primero EGB', 'sequence_order' => 5, 'max_capacity' => 9, 'student_aux_ratio' => 0, 'enrollment_fee' => 200.00, 'monthly_fee' => 230.00],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['name' => $level['name']], $level);
        }

        $createdLevels = Level::query()->whereNotNull('sequence_order')->orderBy('sequence_order')->get();
        foreach ($createdLevels as $index => $level) {
            $level->update(['next_level_id' => $createdLevels->get($index + 1)?->id]);
        }
    }
}
