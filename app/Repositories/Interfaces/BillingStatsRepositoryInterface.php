<?php
namespace App\Repositories\Interfaces;
Interface BillingStatsRepositoryInterface{
    public function totalRevenue(string $from,string $to):float;
    public function revenueByPaymentStatus(string $from, string $to): array;

    public function averageInvoiceValue(string $from, string $to): float;
}