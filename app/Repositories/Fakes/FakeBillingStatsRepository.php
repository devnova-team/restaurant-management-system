<?php

namespace App\Repositories\Fakes;

use App\Repositories\Interfaces\BillingStatsRepositoryInterface;

class FakeBillingStatsRepository implements BillingStatsRepositoryInterface
{
    public function totalRevenue(int $restaurantId, string $from, string $to): float
    {
        return 15750.50;
    }

    public function revenueByPaymentStatus(int $restaurantId, string $from, string $to): array
    {
        return [
            'paid'     => 14200.00,
            'pending'  => 1350.50,
            'refunded' => 200.00,
        ];
    }

    public function averageInvoiceValue(int $restaurantId, string $from, string $to): float
    {
        return 187.50;
    }
}