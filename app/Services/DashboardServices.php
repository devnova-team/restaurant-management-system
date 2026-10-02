<?php
namespace   App\Services;

use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;

class DashboardServices{
    public function __construct(protected BillingStatsRepositoryInterface $billingState,protected OrderStatsRepositoryInterface $orderState){}
    public function getStats(int $restaurantId ,string $from, string $to):array{

        return [
            'orders'=>[
                'total'=>$this->orderState->countOrders($restaurantId,$from,$to),
                'by_status'=>$this->orderState->countOrdersByStatus($restaurantId,$from,$to),
                'by_channel'=>$this->orderState->countOrdersByChannel($restaurantId,$from,$to),
                'top_selling'=>$this->orderState->topSellingItems($restaurantId,$from,$to),
            ],
            'revenue'=>[
                'total'=>$this->billingState->totalRevenue($restaurantId,$from,$to),
                'by_payment_status'=>$this->billingState->revenueByPaymentStatus($restaurantId,$from,$to),
                'average_invoice'=>$this->billingState->averageInvoiceValue($restaurantId,$from,$to),
            ],
        ];
    }


}