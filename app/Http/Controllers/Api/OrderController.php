<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateKitchenStatusRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\KitchenOrderResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->list(
            $request->only(['status', 'channel', 'per_page']),
            $request->user()
        );

        return ApiResponse::success([
            'orders' => OrderResource::collection($orders->items())->resolve(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ], 'تم جلب الطلبات بنجاح');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return ApiResponse::success($this->resource($order), 'تم إنشاء الطلب بنجاح', 201);
    }

    public function show(Order $order): JsonResponse
    {
        return ApiResponse::success(
            $this->resource($this->orderService->show($order)),
            'تم جلب الطلب بنجاح'
        );
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->update($order, $request->validated());

        return ApiResponse::success($this->resource($order), 'تم تحديث الطلب بنجاح');
    }

    public function kitchenIndex(Request $request): JsonResponse
    {
        $orders = $this->orderService->kitchenOrders($request->user());

        return ApiResponse::success(
            KitchenOrderResource::collection($orders)->resolve(),
            'تم جلب طلبات المطبخ بنجاح'
        );
    }

    public function updateKitchenStatus(UpdateKitchenStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->changeStatus(
            $order,
            $request->validated()['status'],
            $request->user()
        );

        return ApiResponse::success($this->resource($order), 'تم تحديث حالة الطلب بنجاح');
    }

    public function updateFinalStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->changeStatus(
            $order,
            $request->validated()['status'],
            $request->user()
        );

        return ApiResponse::success($this->resource($order), 'تم تحديث الحالة النهائية للطلب بنجاح');
    }

    private function resource(Order $order): array
    {
        return (new OrderResource($order))->resolve();
    }
}
