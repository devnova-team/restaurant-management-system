<?php

namespace App\Repositories\Fakes;

use App\Repositories\Interfaces\OrderStatsRepositoryInterface;

class FakeOrderStatsRepository implements OrderStatsRepositoryInterface
{
    public function countOrders(int $restaurantId, string $from, string $to): int
    {
        return 42;
    }

    public function countOrdersByStatus(int $restaurantId, string $from, string $to): array
    {
        return [
            'received'         => 5,
            'preparing'        => 3,
            'ready'            => 2,
            'out_for_delivery' => 4,
            'delivered'        => 28,
        ];
    }

    public function countOrdersByChannel(int $restaurantId, string $from, string $to): array
    {
        return [
            'dine_in'  => 25,
            'delivery' => 17,
        ];
    }

    public function topSellingItems(int $restaurantId, string $from, string $to, int $limit = 5): array
    {
        return [
            ['menu_item_id' => 1, 'name' => 'Grilled Chicken', 'total_quantity' => 34],
            ['menu_item_id' => 2, 'name' => 'Beef Burger', 'total_quantity' => 28],
        ];
    }
}