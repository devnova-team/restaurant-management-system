<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'quantity', 'restaurant_id', 'unit', 'low_stock_threshold'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /* Define the relationship with MenuItem through the pivot table
    $pizza->ingredients; // Collection من Ingredients، وكل واحد معاه pivot->quantity_required
    $cheese->menuItems; // كل الأصناف اللي فيها جبنة */
    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_ingredients')->withPivot('quantity_required');
    }

    public function ingredientUsages()
    {
        return $this->hasMany(OrderItemIngredientUsage::class);
    }

    // Scope to ensure that queries are filtered by the restaurant_id of the authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
