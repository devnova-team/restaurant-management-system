<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'El-Forsan Restaurant',
                'owner_phone' => '01012345678',
            ]
        );

        $cashier = Staff::firstOrCreate(
            ['email' => 'cashier@test.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Mohamed Cashier',
                'phone' => '01011112222',
                'password_hash' => Hash::make('12345678'),
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        $owner = Staff::firstOrCreate(
            ['email' => 'owner@test.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Ahmed Owner',
                'phone' => '01099990000',
                'password_hash' => Hash::make('12345678'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        $menuItem = MenuItem::firstOrCreate(
            ['name' => 'Burger', 'restaurant_id' => $restaurant->id],
            [
                'category' => 'Food',
                'price' => 50.00,
                'is_available' => true,
            ]
        );

        $order = Order::firstOrCreate(
            ['id' => 1],
            [
                'restaurant_id' => $restaurant->id,
                'customer_name' => 'Ali',
                'customer_phone' => '01055554444',
                'channel' => 'dine_in',
                'status' => 'received',
                'created_by_staff_id' => $cashier->id,
            ]
        );

        OrderItem::firstOrCreate(
            [
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
            ],
            [
                'quantity' => 2,
                'unit_price' => 50.00,
            ]
        );
    }
}
