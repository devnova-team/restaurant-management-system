<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemIngredientUsage;
use App\Models\Restaurant;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(Restaurant::where('name','Analytics demo A')->exists()){
            $this->command?->warn('AnalyticsSeeder already ran. Use migrate:fresh to reset');
            return ;
        }
        DB::transaction(function(){
            $this->seedRestaurantA();
            $this->seedRestaurantB();
        });
    }

    private function staff(Restaurant $r, string $role, string $email, string $phone): Staff
    {
        return Staff::create([
            'restaurant_id' => $r->id,
            'name'          => ucfirst($role) . ' ' . $r->name,
            'role'          => $role,
            'phone'         => $phone,
            'email'         => $email,
            'password_hash' => 'password',   
            'is_active'     => true,
        ]);
    }
    public function seedRestaurantA():void{
        $r=Restaurant::create(['name'=>'Analytics Demo A', 'owner_phone' => '01000000001']);
        $this->staff($r,'owner','owner.a@analytics.test','01000000011');
        $cashier=$this->staff($r,'cashier','cashier.a@analytics.test', '01000000012');
        
        $cheese = Ingredient::create([
            'restaurant_id' => $r->id, 'name' => 'Cheese', 'quantity' => 50,
            'unit' => 'kg', 'low_stock_threshold' => 5
        ]);
        $dough = Ingredient::create([
            'restaurant_id' => $r->id, 'name' => 'Dough', 'quantity' => 50,
            'unit' => 'kg', 'low_stock_threshold' => 5
        ]);

        $margherita = MenuItem::create([
            'restaurant_id' => $r->id, 'name' => 'Margherita', 'category' => 'Pizza',
            'price' => 100, 'is_available' => true,
        ]);
        $pepperoni = MenuItem::create([
            'restaurant_id' => $r->id, 'name' => 'Pepperoni', 'category' => 'Pizza',
            'price' => 150, 'is_available' => true,
        ]);

        $margheritaRecipe = [[$cheese, 0.20, 100], [$dough, 0.30, 20]];   
        $pepperoniRecipe  = [[$cheese, 0.25, 100], [$dough, 0.30, 20]];   

        $now = Carbon::now();
        $this->makeOrder($r, $cashier, 'dine_in', 'served', $now->copy()->subDays(3),
            [[$margherita, 2, $margheritaRecipe]], 200);

        $this->makeOrder($r, null, 'delivery', 'delivered', $now->copy()->subDays(2),
            [[$pepperoni, 1, $pepperoniRecipe], [$margherita, 1, $margheritaRecipe]], 250);

        $this->makeOrder($r, $cashier, 'dine_in', 'received', $now->copy()->subDays(1),
            [[$margherita, 5, $margheritaRecipe]], null);

        $this->makeOrder($r, null, 'delivery', 'preparing', $now->copy()->subDays(1),
            [[$pepperoni, 3, $pepperoniRecipe]], null);

        $this->makeOrder($r, $cashier, 'dine_in', 'served', $now->copy()->subDays(40),
            [[$margherita, 1, $margheritaRecipe]], 100);
    }

    private function seedRestaurantB(): void
    {
        $r = Restaurant::create(['name' => 'Analytics Demo B', 'owner_phone' => '01000000002']);
        $this->staff($r, 'owner', 'owner.b@analytics.test', '01000000021');
        $cashier = $this->staff($r, 'cashier', 'cashier.b@analytics.test', '01000000022');

        $beef = Ingredient::create([
            'restaurant_id' => $r->id, 'name' => 'Beef', 'quantity' => 50,
            'unit' => 'kg', 'low_stock_threshold' => 5
        ]);
        $burger = MenuItem::create([
            'restaurant_id' => $r->id, 'name' => 'Burger', 'category' => 'Burger',
            'price' => 80, 'is_available' => true,
        ]);

        $this->makeOrder($r, $cashier, 'dine_in', 'served', Carbon::now()->subDays(2),
            [[$burger, 10, [[$beef, 0.25, 40]]]], 800);   
    }

    private function makeOrder(
        Restaurant $r, ?Staff $cashier, string $channel, string $status,
        Carbon $when, array $lines, ?float $invoiceTotal
    ): void {
        $order = Order::create([
            'restaurant_id'       => $r->id,
            'channel'             => $channel,
            'status'              => $status,
            'tracking_token'      => $channel === 'delivery' ? Str::random(32) : null,
            'customer_name'       => $channel === 'delivery' ? 'Test Customer' : null,
            'customer_phone'      => $channel === 'delivery' ? '01012345678' : null,
            'delivery_address'    => $channel === 'delivery' ? 'Test Address' : null,
            'created_by_staff_id' => $channel === 'dine_in' ? $cashier?->id : null,
        ]);

        Order::withoutGlobalScopes()->whereKey($order->id)
            ->update(['created_at' => $when, 'updated_at' => $when]);

        foreach ($lines as [$menuItem, $qty, $recipe]) {
            $item = OrderItem::create([
                'order_id' => $order->id, 'menu_item_id' => $menuItem->id,
                'quantity' => $qty, 'notes' => null,
            ]);

            foreach ($recipe as [$ingredient, $perUnit, $snapshotCost]) {
                OrderItemIngredientUsage::create([
                    'order_item_id'       => $item->id,
                    'ingredient_id'       => $ingredient->id,
                    'quantity_used'       => $perUnit * $qty,
                    'cost_at_time_of_use' => $snapshotCost,   
                ]);
            }
        }

        if ($invoiceTotal !== null) {
            Invoice::create([
                'order_id'       => $order->id,
                'total_amount'   => $invoiceTotal,
                'payment_method' => $channel === 'delivery' ? 'cod' : 'cash',
                'payment_status' => 'paid',   
            ]);
        }
    }
    private function ingredient(array $data, float $currentPrice): Ingredient
{
    if (Schema::hasColumn('ingredients', 'unit_price')) {
        $data['unit_price'] = $currentPrice;
    }

    return Ingredient::create($data);
}
}
