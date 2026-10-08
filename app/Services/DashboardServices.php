<?php
namespace   App\Services;

use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;

class DashboardServices{
    public function __construct(protected BillingStatsRepositoryInterface $billingState,protected OrderStatsRepositoryInterface $orderState){}
    public function getStats(string $from, string $to):array{

        return [
            'orders'=>[
                'total'=>$this->orderState->countOrders($from,$to),
                'by_status'=>$this->orderState->countOrdersByStatus($from,$to),
                'by_channel'=>$this->orderState->countOrdersByChannel($from,$to),
                'top_selling'=>$this->orderState->topSellingItems($from,$to),
            ],
            'revenue'=>[
                'total'=>$this->billingState->totalRevenue($from,$to),
                'by_payment_status'=>$this->billingState->revenueByPaymentStatus($from,$to),
                'average_invoice'=>$this->billingState->averageInvoiceValue($from,$to),
            ],
        ];
    }


}