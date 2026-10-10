<?php
namespace App\Repositories\Interfaces;
Interface BillingStatsRepositoryInterface{
    public function totalRevenue():float;
    public function revenueByPaymentStatus(): array;

    public function averageInvoiceValue(): float;
}