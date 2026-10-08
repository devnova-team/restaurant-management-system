<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Resources\DashboardStatsResource;
use App\Services\DashboardServices;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardServices $services
    ) {}

    public function stats(DashboardRequest $request): JsonResponse
    {
        $data = $this->services->getStats(
            $request->validated('from'),
            $request->validated('to')
        );

        return ApiResponse::success(
            new DashboardStatsResource($data)
        );
    }
}