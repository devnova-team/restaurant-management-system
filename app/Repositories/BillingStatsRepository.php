<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BillingStatsRepository implements BillingStatsRepositoryInterface
{
    public function totalRevenue(): float
    {
        return (float) Order::query()
            ->join('invoices', 'invoices.order_id', '=', 'orders.id')
            ->where('invoices.payment_status', 'paid')
            ->whereDate('orders.created_at', today())
            ->sum('invoices.total_amount');
    }

    public function revenueByPaymentStatus(): array
    {
        return Order::query()
            ->join('invoices', 'invoices.order_id', '=', 'orders.id')
            ->select(
                'invoices.payment_status',
                DB::raw('SUM(invoices.total_amount) as total')
            )
            ->whereDate('orders.created_at', today())
            ->groupBy('invoices.payment_status')
            ->pluck('total', 'payment_status')
            ->map(fn ($total) => (float) $total)
            ->toArray();
    }

    public function averageInvoiceValue(): float
    {
        return (float) (
            Order::query()
                ->join(
                    'invoices',
                    'invoices.order_id',
                    '=',
                    'orders.id'
                )
                ->where('invoices.payment_status', 'paid')
                ->whereDate('orders.created_at', today())
                ->avg('invoices.total_amount') ?? 0
        );
    }
}