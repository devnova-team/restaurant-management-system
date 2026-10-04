<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Restaurant
        $restaurantId = DB::table('restaurants')->insertGetId([
            'name' => 'Dashboard Test Restaurant',
            'owner_phone' => '01000000000',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Menu items
        $burgerId = DB::table('menu_items')->insertGetId([
            'restaurant_id' => $restaurantId,
            'category' => 'Main',
            'name' => 'Burger',
            'price' => 150,
            'is_available' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pizzaId = DB::table('menu_items')->insertGetId([
            'restaurant_id' => $restaurantId,
            'category' => 'Main',
            'name' => 'Pizza',
            'price' => 200,
            'is_available' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Order 1
        $order1 = DB::table('orders')->insertGetId([
            'restaurant_id' => $restaurantId,
            'channel' => 'dine_in',
            'status' => 'served',
            'customer_name' => 'Ahmed',
            'customer_phone' => '01011111111',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_items')->insert([
            'order_id' => $order1,
            'menu_item_id' => $burgerId,
            'quantity' => 2,
            'notes' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('invoices')->insert([
            'order_id' => $order1,
            'total_amount' => 300,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Order 2
        $order2 = DB::table('orders')->insertGetId([
            'restaurant_id' => $restaurantId,
            'channel' => 'delivery',
            'status' => 'delivered',
            'customer_name' => 'Mohamed',
            'customer_phone' => '01022222222',
            'delivery_address' => 'Tanta',
            'tracking_token' => 'TEST-ORDER-2',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_items')->insert([
            'order_id' => $order2,
            'menu_item_id' => $pizzaId,
            'quantity' => 3,
            'notes' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('invoices')->insert([
            'order_id' => $order2,
            'total_amount' => 600,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Order 3
        $order3 = DB::table('orders')->insertGetId([
            'restaurant_id' => $restaurantId,
            'channel' => 'delivery',
            'status' => 'preparing',
            'customer_name' => 'Omar',
            'customer_phone' => '01033333333',
            'delivery_address' => 'Tanta',
            'tracking_token' => 'TEST-ORDER-3',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_items')->insert([
            'order_id' => $order3,
            'menu_item_id' => $burgerId,
            'quantity' => 1,
            'notes' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('invoices')->insert([
            'order_id' => $order3,
            'total_amount' => 150,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}