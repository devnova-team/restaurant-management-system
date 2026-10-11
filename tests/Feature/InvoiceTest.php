<?php

use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->restaurant = Restaurant::create([
        'name' => 'Test Restaurant',
        'owner_phone' => '1234567890',
    ]);

    $this->cashier = Staff::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Cashier Staff',
        'email' => 'cashier@test.com',
        'phone' => '1234567890',
        'password_hash' => bcrypt('password'),
        'role' => 'cashier',
    ]);
});

test('cashier can create an invoice for an order', function () {
    $this->actingAs($this->cashier);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'received',
        'channel' => 'dine_in',
    ]);

    $menuItem = MenuItem::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Burger',
        'category' => 'Food',
        'price' => 50,
        'is_available' => true,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 2,
        'unit_price' => 50,
    ]);

    $response = $this->postJson('/api/invoices', [
        'order_id' => $order->id,
        'payment_method' => 'cash',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'total_amount' => '100.00',
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
            ],
        ]);
});

test('cashier can view an invoice', function () {
    $this->actingAs($this->cashier);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'received',
        'channel' => 'dine_in',
    ]);

    $invoice = Invoice::create([
        'order_id' => $order->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
    ]);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'order_id' => $order->id,
            ],
        ]);
});

test('non-cashier staff cannot access invoices', function () {
    $kitchenStaff = Staff::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Kitchen Staff',
        'email' => 'kitchen@test.com',
        'phone' => '1234567891',
        'password_hash' => bcrypt('password'),
        'role' => 'kitchen',
    ]);

    $this->actingAs($kitchenStaff);

    $response = $this->getJson('/api/invoices/1');

    $response->assertStatus(403);
});

test('cashier can pay an invoice with exact amount', function () {
    $this->actingAs($this->cashier);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'received',
        'channel' => 'dine_in',
    ]);

    $invoice = Invoice::create([
        'order_id' => $order->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
    ]);

    $response = $this->postJson("/api/invoices/{$invoice->id}/pay", [
        'amount' => 100.0,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Invoice paid successfully.',
            'data' => [
                'id' => $invoice->id,
                'payment_status' => 'paid',
            ],
        ]);
});

test('cashier cannot pay an invoice with incorrect amount', function () {
    $this->actingAs($this->cashier);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'received',
        'channel' => 'dine_in',
    ]);

    $invoice = Invoice::create([
        'order_id' => $order->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
    ]);

    $response = $this->postJson("/api/invoices/{$invoice->id}/pay", [
        'amount' => 90.0,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('cashier cannot pay an already paid invoice', function () {
    $this->actingAs($this->cashier);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'received',
        'channel' => 'dine_in',
    ]);

    $invoice = Invoice::create([
        'order_id' => $order->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
    ]);

    $response = $this->postJson("/api/invoices/{$invoice->id}/pay", [
        'amount' => 100.0,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});
