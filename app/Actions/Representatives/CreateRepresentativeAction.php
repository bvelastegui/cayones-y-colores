<?php

namespace App\Actions\Representatives;

use App\Models\Representative;
use App\Services\UserAccountService;
use Illuminate\Support\Facades\DB;

class CreateRepresentativeAction
{
    public function __construct(private UserAccountService $userAccountService) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Representative
    {
        return DB::transaction(function () use ($data): Representative {
            $representative = Representative::create($data);
            $this->userAccountService->createForRepresentative($representative);

            return $representative->load('user');
        });
    }
}
