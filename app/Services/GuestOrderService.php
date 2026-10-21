<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Repositories\PublicOrderRepository;
use Illuminate\Support\Facades\DB;

class GuestOrderService
{
    public function __construct(
        private readonly PublicOrderRepository $orderRepository,
        private readonly StockOrderDeductionAndCostSnapshotService $stockDeduction,
    ) {}

    public function createGuestOrder(Restaurant $restaurant, array $data): Order
    {
        return DB::transaction(function () use ($restaurant, $data): Order {
            $order = $this->orderRepository->createGuestOrder($restaurant, $data);
            $this->orderRepository->createItems($order, $data['items']);
            $this->stockDeduction->deductForOrder($order);

            return $order;
        });
    }
}
