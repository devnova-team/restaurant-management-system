<?php
namespace App\Repositories\Interfaces;
Interface BillingStatsRepositoryInterface{
    public function totalRevenue(int $restaurantId,string $from,string $to):float;
    public function revenueByPaymentStatus(int $restaurantId, string $from, string $to): array;

    public function averageInvoiceValue(int $restaurantId, string $from, string $to): float;
}