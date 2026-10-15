<?php
// app/Http/Controllers/Api/OrderController.php
namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Support\ApiResponse;


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

        return ApiResponse::success(
            OrderResource::collection($orders),
            'تم جلب الطلبات بنجاح'
        );
    }
   public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new OrderResource($order),
            'تم إنشاء الطلب بنجاح',
            201
        );
    }
    public function show(Request $request, Order $order): JsonResponse
    {
        $orderData = $this->orderService->show($order, $request->user());

        return ApiResponse::success(
            new OrderResource($orderData),
            'تم جلب الطلب بنجاح'
        );
    }
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $orderData = $this->orderService->update($order, $request->validated(), $request->user());

        return ApiResponse::success(
            new OrderResource($orderData),
            'تم تحديث الطلب بنجاح'
        );
    }

    // public function destroy(Request $request, Order $order): JsonResponse
    // {
    //     $this->orderService->delete($order, $request->user());

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'تم حذف الطلب بنجاح'
    //     ], 200);
    // }



    public function updateKitchenStatus(Request $request, Order $order): JsonResponse
    {
        $order = $this->orderService->changeKitchenStatus(
            $order,
            $request->input('status'),
            $request->user()
        );

        return ApiResponse::success(
            (new OrderResource($order))->resolve(),
            'تم تحديث حالة الطلب بنجاح'
        );
    }


    public function updateFinalStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->changeStatus(
            $order,
            $request->validated()['status'],
            $request->user()
        );

        return ApiResponse::success(
            (new OrderResource($order))->resolve(),
            'تم تحديث الحالة النهائية للطلب بنجاح'
        );
    }





}
