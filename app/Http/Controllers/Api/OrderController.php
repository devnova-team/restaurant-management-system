<?php
// app/Http/Controllers/Api/OrderController.php
namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateOrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
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

    public function show(Request $request, int $id): OrderResource
    {
        return new OrderResource(
            $this->orderService->show($id, $request->user())
        );
    }

    public function update(UpdateOrderRequest $request, int $id): OrderResource
    {
        return new OrderResource(
            $this->orderService->update($id, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->orderService->delete($id, $request->user());

        // return response()->json(null, 204);
        return response()->json([
            'success' => true,
            'message' => 'تم حذف الطلب بنجاح'
        ], 200);
    }
}
