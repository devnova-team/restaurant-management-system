<?php
namespace   App\Services;

use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;
use App\Repositories\Interfaces\LowStockStatsRepositoryInterface;
class DashboardServices{
        public function __construct(
            protected BillingStatsRepositoryInterface $billingState,
            protected OrderStatsRepositoryInterface $orderState,
            protected LowStockStatsRepositoryInterface $lowStockState
        ) {}    public function getStats():array{

        return [
            'orders'=>[
                'total'=>$this->orderState->countOrders(),
                'by_status'=>$this->orderState->countOrdersByStatus(),
                'by_channel'=>$this->orderState->countOrdersByChannel(),
                'top_selling'=>$this->orderState->topSellingItems(),
            ],
            'revenue'=>[
                'total'=>$this->billingState->totalRevenue(),
                'by_payment_status'=>$this->billingState->revenueByPaymentStatus(),
                'average_invoice'=>$this->billingState->averageInvoiceValue(),
            ],
            'low_stock' => $this->lowStockState->getLowStockIngredients(),
            'count_of_active_orders' => $this->orderState->countActiveOrders(),

            'count_active_orders_by_status' => $this->orderState->countActiveOrdersByStatus(),
        ];
    }


}