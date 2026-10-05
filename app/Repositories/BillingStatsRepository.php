<?php
namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Override;

class BillingStatsRepository implements BillingStatsRepositoryInterface{
    public function totalRevenue( string $from, string $to): float
    {
        return 
        (float) Order::query()
        ->join('invoices','invoices.order_id','=','orders.id')
        ->whereDate('orders.created_at','>=',$from)
        ->whereDate('orders.created_at','<=',$to)
        ->sum('invoices.total_amount')
        ;
    }

    public function revenueByPaymentStatus(string $from ,string $to):array{
        return 
        Order::query()
        ->join('invoices','invoices.order_id','=','orders.id')
        ->select('invoices.payment_status',DB::raw('SUM(invoices.total_amount) as total'))
        ->whereDate('orders.created_at', '>=', $from)
        ->whereDate('orders.created_at', '<=', $to)
        ->groupBy('invoices.payment_status')
        ->pluck('total', 'payment_status')
        ->map(fn ($total) => (float) $total)
        ->toArray();
        ;
    }

    public function averageInvoiceValue(string $from, string $to): float
    {
        return (float) (
            Order::query()
                ->join('invoices', 'invoices.order_id', '=', 'orders.id')
                ->whereDate('orders.created_at', '>=', $from)
                ->whereDate('orders.created_at', '<=', $to)
                ->avg('invoices.total_amount') ?? 0
        );
    }
}