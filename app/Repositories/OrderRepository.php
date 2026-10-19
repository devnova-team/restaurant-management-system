<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Support\Str;

class OrderRepository
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
            'tracking_token' => Str::uuid(),
        ]);
    }
}
