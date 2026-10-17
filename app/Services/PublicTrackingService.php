<?php

namespace App\Services;

use App\Enums\DeliveryStep;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicTrackingService
{
    public function getTrackableOrder(Order $order): Order
    {
        if ($order->channel !== 'delivery' || DeliveryStep::tryFrom($order->status) === null) {
            throw (new ModelNotFoundException)->setModel(Order::class);
        }

        return $order->load('orderItems.menuItem');
    }
}
