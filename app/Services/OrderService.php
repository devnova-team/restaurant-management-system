<?php

namespace App\Services;


use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Staff;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderService
{
    public function __construct(private OrderRepositoryInterface $orders)
    {
    }

    public function  list(array $filters, Staff $staff): LengthAwarePaginator
    {
        return $this->orders->paginateForRestaurant($staff->restaurant_id, $filters);
    }

    public function show(int $id, Staff $staff): Order
    {
        return $this->orders->findForRestaurant($id, $staff->restaurant_id);
    }

    public function create(Staff $staff, array $data): Order
    {
        return DB::transaction(function () use ($staff, $data) {
            $order = $this->orders->create([
                'restaurant_id' => $staff->restaurant_id,
                'channel' => $data['channel'],
                 'status' => 'received',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'created_by_staff_id' => $staff->id,
            ]);

            $this->orders->addItems($order, $this->buildItems($order, $data['items']));



            return $this->orders->findForRestaurant($order->id, $staff->restaurant_id);
        });
    }

    public function update(int $id, array $data, Staff $staff): Order
    {
        $order = $this->orders->findForRestaurant($id, $staff->restaurant_id);

        $this->assertEditable($order, 'Order can no longer be edited.');

        return DB::transaction(function () use ($order, $staff, $data) {
            $this->orders->update($order, collect($data)->except('items')->all());

            if (isset($data['items'])) {
                $this->orders->deleteItems($order);
                $this->orders->addItems($order, $this->buildItems($order, $data['items']));
                // TODO: inventory re-check
            }

            return $this->orders->findForRestaurant($order->id, $staff->restaurant_id);
        });
    }

    public function delete(int $id, Staff $staff): void
    {
        $order = $this->orders->findForRestaurant($id, $staff->restaurant_id);

        $this->assertEditable($order, 'Only new orders can be deleted.');

        $this->orders->delete($order);
        
    }

    private function assertEditable(Order $order, string $message): void
    {
        if ($order->status !==  'received') {
            throw new UnprocessableEntityHttpException($message);
        }
    }


    private function buildItems(Order $order, array $items): array
    {
        $menuItems = MenuItem::query()
            ->whereIn('id', collect($items)->pluck('menu_item_id'))
            ->where('restaurant_id', $order->restaurant_id)
            ->get()
            ->keyBy('id');

        return collect($items)->map(function (array $row) use ($menuItems) {
            $menuItem = $menuItems[$row['menu_item_id']];

            if (! $menuItem->is_available) {
                throw new UnprocessableEntityHttpException("{$menuItem->name} is not available.");
            }

            return [
                'menu_item_id' => $menuItem->id,
                'quantity' => $row['quantity'],
                'unit_price' => $menuItem->price,
                'notes' => $row['notes'] ?? null,
            ];
        })->all();
    }
}
