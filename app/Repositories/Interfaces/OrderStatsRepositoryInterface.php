<?php
namespace App\Repositories\Interfaces;

Interface  OrderStatsRepositoryInterface{
    public function countOrders(int $restaurantId , string $from ,string $to):int;
    public function countOrdersByStatus(int $restaurantId , string $from ,string $to):array;
    public function countOrdersByChannel(int $restaurantId , string $from ,string $to):array;
    public function topSellingItems(int $restaurantId , string $from ,string $to,int $limit=5):array;
}