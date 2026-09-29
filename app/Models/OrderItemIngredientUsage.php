<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemIngredientUsage extends Model
{
    protected $fillable = ['order_item_id', 'ingredient_id', 'quantity_used','cost_at_time_of_use'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
