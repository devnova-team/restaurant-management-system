<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Resources\DashboardStatsResource;
use App\Http\Traits\ApiResponse;
use App\Services\DashboardServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use  ApiResponse;
    public function __construct(protected DashboardServices $services){}

    public function stats(DashboardRequest $request): JsonResponse
{
    $data = $this->services->getStats(
        $request->validated('from'),
        $request->validated('to')
    );

    return $this->success(new DashboardStatsResource($data));
}


}
