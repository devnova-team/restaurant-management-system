<?php

namespace App\Enums;

enum DeliveryStep: string
{
    case Received = 'received';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'تم استلام طلبك',
            self::Preparing => 'جاري تحضير طلبك',
            self::Ready => 'طلبك جاهز',
            self::OutForDelivery => 'طلبك في الطريق إليك',
            self::Delivered => 'تم تسليم طلبك',
        };
    }

    public function stepNumber(): int
    {
        return match ($this) {
            self::Received => 1,
            self::Preparing => 2,
            self::Ready => 3,
            self::OutForDelivery => 4,
            self::Delivered => 5,
        };
    }

    public static function total(): int
    {
        return count(self::cases());
    }
}
