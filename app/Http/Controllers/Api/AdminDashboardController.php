<?php

namespace App\Http\Controllers\Api;

use App\Actions\Dashboard\GetAdminDashboardAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __invoke(GetAdminDashboardAction $getDashboard): JsonResponse
    {
        return response()->json($getDashboard->execute());
    }
}
