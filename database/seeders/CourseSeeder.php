<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = Level::all();

        foreach ($levels as $level) {
            Course::factory()->create([
                'level_id' => $level->id,
                'parallel' => 'A',
            ]);

            Course::factory()->create([
                'level_id' => $level->id,
                'parallel' => 'B',
            ]);
        }
    }
}
