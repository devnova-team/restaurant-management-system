<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderStatsRepository implements OrderStatsRepositoryInterface
{
    public function countOrders(string $from, string $to): int
    {
        return Order::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->count();
    }

    public function countOrdersByStatus( string $from, string $to): array
    {
        return Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->toArray();
    }

    public function countOrdersByChannel( string $from, string $to): array
    {
        return Order::query()
            ->select('channel', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->map(fn ($total) => (int) $total)
            ->toArray();
    }

    public function topSellingItems(
        string $from,
        string $to,
        int $limit = 5
    ): array {
        return Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->select(
                'menu_items.id as menu_item_id',
                'menu_items.name',
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'menu_item_id' => (int) $item->menu_item_id,
                'name' => $item->name,
                'total_quantity' => (int) $item->total_quantity,
            ])
            ->toArray();
    }
}