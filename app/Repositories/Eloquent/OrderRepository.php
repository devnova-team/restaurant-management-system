<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function addItems(Order $order, array $items): void
    {
        $now = now();

        $rows = collect($items)->map(fn ($item) => array_merge($item, [
            'order_id'   => $order->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]))->all();

        OrderItem::insert($rows);
    }

    public function findWithRelations(int $id): Order
    {
        return $this->withDailyNumber(Order::with('orderItems.menuItem'))->findOrFail($id);
    }

    public function paginateForRestaurant(int $restaurantId, array $filters = []): LengthAwarePaginator
    {
        return $this->withDailyNumber(Order::query())
            ->where('restaurant_id', $restaurantId)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['channel'] ?? null, fn ($q, $channel) => $q->where('channel', $channel))
            ->with('orderItems.menuItem')
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function activeForRestaurant(int $restaurantId, array $statuses): Collection
    {
        return $this->withDailyNumber(Order::query())
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', $statuses)
            ->with('orderItems.menuItem')
            ->oldest()
            ->get();
    }

    public function findForRestaurant(int $id, int $restaurantId): Order
    {
        return $this->withDailyNumber(Order::with('orderItems.menuItem'))
            ->where('restaurant_id', $restaurantId)
            ->findOrFail($id);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function transitionStatus(Order $order, string $from, string $to): bool
    {
        return Order::where('id', $order->id)
            ->where('status', $from)
            ->update(['status' => $to]) === 1;
    }

    public function deleteItems(Order $order): void
    {
        OrderItem::where('order_id', $order->id)->delete();
    }

    public function updateInvoiceTotal(Order $order, string $total): void
    {
        $order->invoice?->forceFill(['total_amount' => $total])->save();
    }


    private function withDailyNumber(Builder $query): Builder
    {
        $position = DB::table('orders as same_day')
            ->selectRaw('count(*)')
            ->whereColumn('same_day.restaurant_id', 'orders.restaurant_id')
            ->whereColumn('same_day.id', '<=', 'orders.id')
            ->whereRaw('DATE(same_day.created_at) = DATE(orders.created_at)');

        return $query->select('orders.*')->addSelect(['daily_number' => $position]);
    }
}
