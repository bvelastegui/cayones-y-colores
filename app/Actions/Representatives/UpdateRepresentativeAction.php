<?php

namespace App\Actions\Representatives;

use App\Models\Representative;
use Illuminate\Support\Facades\DB;

class UpdateRepresentativeAction
{
    /** @param array<string, mixed> $data */
    public function execute(Representative $representative, array $data): Representative
    {
        return DB::transaction(function () use ($representative, $data): Representative {
            $representative->update($data);
            $representative->user?->update([
                'name' => "{$representative->first_name} {$representative->last_name}",
                'email' => $representative->email,
                'identification' => $representative->id_card,
                'phone' => $representative->phone,
            ]);

            return $representative->load('user');
        });
    }
}
