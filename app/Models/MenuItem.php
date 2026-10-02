<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['name', 'category', 'price', 'restaurant_id', 'is_available'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    // Define the relationship with Ingredient through the pivot table
    // quantity_required is to specify how much of each ingredient is needed for this menu item
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_item_ingredients')->withPivot('quantity_required');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scope to ensure that queries are filtered by the restaurant_id of the authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
