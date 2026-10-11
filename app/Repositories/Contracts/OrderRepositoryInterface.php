<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function addItems(Order $order, array $items): void;
    public function findWithRelations(int $id): Order;
    public function paginateForRestaurant(int $restaurantId, array $filters = []): LengthAwarePaginator;
    public function findForRestaurant(int $id, int $restaurantId): Order;
    public function update(Order $order, array $data): Order;
    public function deleteItems(Order $order): void;
    public function delete(Order $order): void;
}
