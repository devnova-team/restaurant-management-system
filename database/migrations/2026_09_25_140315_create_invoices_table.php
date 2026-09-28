<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->decimal('total_amount', 10, 2);

            $table->enum('payment_method', [
                'cash',
                'card',
                'cod',
                'stripe',
                'paypal',
                'paymob',
            ])->nullable();

            $table->string('transaction_id')->nullable();

            $table->enum('payment_status', [
                'pending',
                'paid',
                'refunded',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
