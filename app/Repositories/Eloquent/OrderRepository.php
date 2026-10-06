<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function addItems(Order $order, array $items): void
    {
        $order->items()->createMany($items);
    }

    public function findWithRelations(int $id): Order
    {
        return Order::with('items.menuItem')->findOrFail($id);
    }

    public function paginateForRestaurant(int $restaurantId, array $filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->where('restaurant_id', $restaurantId)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['channel'] ?? null, fn ($q, $channel) => $q->where('channel', $channel))
            ->with('items.menuItem')
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForRestaurant(int $id, int $restaurantId): Order
    {
        return Order::with('items.menuItem')
            ->where('restaurant_id', $restaurantId)
            ->findOrFail($id);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function deleteItems(Order $order): void
    {
        $order->items()->delete();
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}
