<?php

use App\Models\Invoice;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Staff;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->restaurant = Restaurant::create([
        'name' => 'Test Restaurant',
        'address' => '123 Test St',
        'phone_number' => '1234567890',
        'subscription_status' => 'active',
        'domain' => 'test',
    ]);

    $this->staff = Staff::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Owner Staff',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);
});

test('it validates payment amount when marking invoice as paid', function () {
    // Act as owner so TenantScope works
    $this->actingAs($this->staff);

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'pending',
        'channel' => 'dine-in',
    ]);

    $menuItem = MenuItem::create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Burger',
        'price' => 50,
        'status' => 'active',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 2,
        'price' => 50,
    ]);

    $service = app(InvoiceService::class);

    $invoice = $service->createInvoice([
        'order_id' => $order->id,
        'payment_method' => 'cash',
    ]);

    expect($invoice->total_amount)->toBe(100.0);

    // Expect ValidationException when amount is less than total
    expect(fn () => $service->updatePaymentStatus($invoice->id, 'paid', 90.0))
        ->toThrow(ValidationException::class);

    // Should succeed with exact amount
    $updatedInvoice = $service->updatePaymentStatus($invoice->id, 'paid', 100.0);
    expect($updatedInvoice->payment_status)->toBe('paid');
});

test('api requires amount when payment status is paid', function () {
    $this->actingAs($this->staff);

    // Create invoice via DB directly or factories if they existed
    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'customer_name' => 'John',
        'status' => 'pending',
        'channel' => 'dine-in',
    ]);

    $invoice = Invoice::create([
        'order_id' => $order->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
    ]);

    // Send request without amount
    $response = $this->patchJson("/api/invoices/{$invoice->id}/payment-status", [
        'payment_status' => 'paid',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});
