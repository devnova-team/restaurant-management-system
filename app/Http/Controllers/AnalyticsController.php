<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\AnalyticsRangeRequest;
use App\Http\Resources\FoodCostResource;
use App\Http\Resources\MenuProfitabilityResource;
use App\Services\AnalyticsService;
use app\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analytics){}
    public function foodCost(AnalyticsRangeRequest $request): JsonResponse
    {
        $data = $this->analytics->foodCost($request->from(), $request->to());

        return ApiResponse::success(
            (
                new FoodCostResource($data)),
            'تمت العملية بنجاح'
        );
    }

    public function menuProfitability(AnalyticsRangeRequest $request): JsonResponse
    {
        $items = $this->analytics->menuProfitability($request->from(), $request->to());

        return ApiResponse::success(
            MenuProfitabilityResource::collection($items),
            'تمت العملية بنجاح'
        );
    }
}
