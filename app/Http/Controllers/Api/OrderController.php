<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\OrderInterface;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderInterface $orderRepository
    ) {}

    public function store(
        StoreOrderRequest $request
    ): JsonResponse {

        $order = $this->orderService->createOrder(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $order,
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
