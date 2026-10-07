<?php
// app/Http/Controllers/Api/OrderController.php
namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->list(
            $request->only(['status', 'channel', 'per_page']),
            $request->user()
        );

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->user(),
            $request->validated()
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        return new OrderResource(
            $this->orderService->show($order, $request->user())
        );
    }

    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        return new OrderResource(
            $this->orderService->update($order, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $this->orderService->delete($order, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الطلب بنجاح'
        ], 200);
    }
}
