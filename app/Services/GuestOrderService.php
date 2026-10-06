<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class GuestOrderService
{
    public function __construct(private readonly OrderRepository $orderRepository) {}

    public function createGuestOrder(Restaurant $restaurant, array $data): Order
    {
        return DB::transaction(function () use ($restaurant, $data) {
            $order = $this->orderRepository->createGuestOrder($restaurant, $data);

            foreach ($data['items'] as $item) {
                $order->orderItems()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $order;
        });
    }
}
