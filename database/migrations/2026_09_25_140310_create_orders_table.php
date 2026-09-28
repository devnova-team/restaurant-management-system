<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();

            $table->enum('channel', [
                'dine_in',
                'delivery',
            ]);

            $table->enum('status', [
                'received',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
            ])->default('received');

            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->text('delivery_address')->nullable();

            $table->foreignId('created_by_staff_id')
                ->nullable()
                ->constrained('staff')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
