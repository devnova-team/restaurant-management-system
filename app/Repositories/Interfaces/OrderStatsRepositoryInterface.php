<?php
namespace App\Repositories\Interfaces;

Interface  OrderStatsRepositoryInterface{
    public function countOrders( ):int;
    public function countOrdersByStatus():array;
    public function countOrdersByChannel():array;
    public function topSellingItems(int $limit=5):array;

    public function countActiveOrders():int;
    public function countActiveOrdersByStatus():array;
}   