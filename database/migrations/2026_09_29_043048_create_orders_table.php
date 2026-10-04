<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained();
            $table->enum('channel', ['dine_in', 'delivery']);
            $table->enum('status', ['received', 'preparing', 'ready', 'served', 'out_for_delivery', 'delivered']);
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('tracking_token')->nullable()->unique();
            $table->foreignId('created_by_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
