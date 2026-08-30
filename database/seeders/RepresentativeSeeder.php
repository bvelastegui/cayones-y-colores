<?php

namespace Database\Seeders;

use App\Models\Representative;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RepresentativeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Representative::factory()->count(15)->create()->each(function (Representative $representative): void {
            $user = User::factory()->representative()->create([
                'name' => "{$representative->first_name} {$representative->last_name}",
                'email' => $representative->email,
                'identification' => $representative->id_card,
            ]);

            $representative->update(['user_id' => $user->id]);
        });
    }
}
