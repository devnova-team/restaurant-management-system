<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderStatsRepository implements OrderStatsRepositoryInterface
{
    public function countOrders(): int
    {
        return Order::query()
            ->whereDate('created_at', today())
            ->count();
    }

    public function countOrdersByStatus(): array
    {
        return Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', today())
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->toArray();
    }

    public function countOrdersByChannel(): array
    {
        return Order::query()
            ->select('channel', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', today())
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->map(fn ($total) => (int) $total)
            ->toArray();
    }

    public function topSellingItems(int $limit = 5): array
    {
        $restaurantId = Auth::user()->restaurant_id;

        $items = DB::table('order_items')
            ->join(
                'menu_items',
                'menu_items.id',
                '=',
                'order_items.menu_item_id'
            )
            ->where('menu_items.restaurant_id', $restaurantId)
            ->select(
                'order_items.order_id',
                'order_items.quantity',
                'menu_items.id as menu_item_id',
                'menu_items.name'
            );

        return Order::query()
            ->joinSub(
                $items,
                'items',
                'items.order_id',
                '=',
                'orders.id'
            )
            ->where('orders.restaurant_id', $restaurantId)
            ->whereDate('orders.created_at', today())
            ->select(
                'items.menu_item_id',
                'items.name',
                DB::raw('SUM(items.quantity) as total_quantity')
            )
            ->groupBy('items.menu_item_id', 'items.name')
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

    public function countActiveOrders(): int
    {
        return Order::query()
            ->whereIn('status', [
                'received',
                'preparing',
                'ready',
                'out_for_delivery',
            ])
            ->count();
    }

    public function countActiveOrdersByStatus(): array
    {
        return Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereIn('status', [
                'received',
                'preparing',
                'ready',
                'out_for_delivery',
            ])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->toArray();
    }
}