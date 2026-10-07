<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestOrderRequest;
use App\Http\Resources\GuestOrderCreatedResource;
use App\Models\Restaurant;
use App\Services\GuestOrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicOrderController extends Controller
{
    public function __construct(private readonly GuestOrderService $service) {}

    public function store(StoreGuestOrderRequest $request, Restaurant $restaurant): JsonResponse
    {
        $order = $this->service->createGuestOrder($restaurant, $request->validated());

        return ApiResponse::success(
            new GuestOrderCreatedResource($order),
            'تم استلام طلبك بنجاح',
            201
        );
    }
}
