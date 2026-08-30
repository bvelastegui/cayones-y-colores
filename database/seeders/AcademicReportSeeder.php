<?php

namespace Database\Seeders;

use App\Models\AcademicReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicReportSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicReport::factory()->count(30)->create();
    }
}
