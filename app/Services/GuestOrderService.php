<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Repositories\PublicOrderRepository;
use Illuminate\Support\Facades\DB;

class GuestOrderService
{
    //     public function __construct(private readonly OrderRepository $orderRepository) {}

    //     public function createGuestOrder(Restaurant $restaurant, array $data): Order
    //     {
    //         return DB::transaction(function () use ($restaurant, $data) {

    //             // foreach ($data['items'] as $item) {
    //             //     $order->orderItems()->create([
    //             //         'menu_item_id' => $item['menu_item_id'],
    //             //         'quantity' => $item['quantity'],
    //             //         'notes' => $item['notes'] ?? null,
    //             //     ]);
    //             // }
    //             $order = $this->orderRepository->createGuestOrder($restaurant, $data);
    //                   $this->orderRepository->createItems($order, $data['items']);

    //             return $order;
    //         });
    //     }

    public function __construct(
        private readonly PublicOrderRepository $orderRepository,
        // private readonly InventoryService $inventoryService, // Moaz: الاسم والتوقيع يتأكدوا منه
    ) {}

    public function createGuestOrder(Restaurant $restaurant, array $data): Order
    {
        return DB::transaction(function () use ($restaurant, $data): Order {
            $order = $this->orderRepository->createGuestOrder($restaurant, $data);
            $this->orderRepository->createItems($order, $data['items']);
            //  لد مايخلص معاذ
            // $order->load('items');
            // $this->inventoryService->deductForOrder($order);

            return $order;
        });
    }
}
