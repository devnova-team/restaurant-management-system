<?php

namespace App\Services;

use App\Interfaces\OrderInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderInterface $orderRepository
    ) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];

            unset($data['items']);

            $order = $this->orderRepository->create($data);

            foreach ($items as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $order->load([
                'items.menuItem',
            ]);
        });
    }
}
