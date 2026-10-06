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
            $table->increments("id");
            $table->unsignedInteger("restaurant_id");
            $table->foreign("restaurant_id")->references("id")->on("restaurants")->onDelete("cascade");
            $table->enum("channel" , ["dine_in" , "delivery"]);
            $table->enum("status" , ["received", "preparing" , "ready" ,"out_for_delivery" , "delivered"]);
            $table->string("customer_name" , "100")->nullable();
            $table->string("customer_phone" , "20")->nullable();
            $table->text("delivery_address")->nullable();
            $table->unsignedInteger("created_by_staff_id")->nullable();
            $table->foreign("created_by_staff_id")->references("id")->on("staff")->onDelete("set null");
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
