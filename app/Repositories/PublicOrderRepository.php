<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Support\Str;

class PublicOrderRepository
{
    public function createGuestOrder(Restaurant $restaurant, array $data): Order
    {
        return Order::create([
            'restaurant_id' => $restaurant->id,
            'channel' => 'delivery',
            'status' => 'received',
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'delivery_address' => $data['delivery_address'],
            'created_by_staff_id' => null,
            'tracking_token' => (string) Str::uuid(),
        ]);
    }

    public function createItems(Order $order, array $items): void
    {
        $now = now();

        OrderItem::insert(array_map(fn (array $item): array => [
            'order_id' => $order->id,
            'menu_item_id' => $item['menu_item_id'],
            'quantity' => $item['quantity'],
            'notes' => $item['notes'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $items));
    }
}
