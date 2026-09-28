<?php

namespace App\Interfaces;

use App\Models\Order;

interface OrderInterface
{
    public function create(array $data): Order;

    public function findById(int $id): ?Order;

    public function update(Order $order, array $data): Order;
}
