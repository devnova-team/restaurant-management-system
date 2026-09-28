<?php

namespace App\Repositories;

use App\Interfaces\OrderInterface;
use App\Models\Order;

class OrderRepository implements OrderInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function findById(int $id): ?Order
    {
        return Order::with([
            'items.menuItem',
            'invoice',
        ])->find($id);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->fresh([
            'items.menuItem',
            'invoice',
        ]);
    }
}
