<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::create(['name' => 'Pizza Palace']);
        $staff = Staff::create(['name' => 'John Doe']);

        $item1 = MenuItem::create(['name' => 'Margherita Pizza', 'price' => 10.50]);
        $item2 = MenuItem::create(['name' => 'Coca Cola', 'price' => 2.00]);
        $item3 = MenuItem::create(['name' => 'Garlic Bread', 'price' => 4.50]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'channel' => 'dine_in',
            'status' => 'received',
            'customer_name' => 'Test Customer',
            'created_by_staff_id' => $staff->id,
        ]);

        OrderItem::create(['order_id' => $order->id, 'menu_item_id' => $item1->id, 'quantity' => 2]); // $21.00
        OrderItem::create(['order_id' => $order->id, 'menu_item_id' => $item2->id, 'quantity' => 2]); // $4.00
        OrderItem::create(['order_id' => $order->id, 'menu_item_id' => $item3->id, 'quantity' => 1]); // $4.50

        // Total = 29.50
        Invoice::create([
            'order_id' => $order->id,
            'total_amount' => 29.50,
            'payment_status' => 'pending',
        ]);
    }
}
