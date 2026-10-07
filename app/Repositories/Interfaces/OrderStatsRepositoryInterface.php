<?php
namespace App\Repositories\Interfaces;

Interface  OrderStatsRepositoryInterface{
    public function countOrders( string $from ,string $to):int;
    public function countOrdersByStatus( string $from ,string $to):array;
    public function countOrdersByChannel( string $from ,string $to):array;
    public function topSellingItems(string $from ,string $to,int $limit=5):array;

    public function countActiveOrders(string $from ,string $to):int;
    public function countActiveOrdersByStatus(string $from ,string $to):array;
}   