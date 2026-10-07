<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['restaurant_id', 'customer_name', 'customer_phone', 'status', 'channel', 'delivery_address', 'tracking_token', 'created_by_staff_id'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // Scope to ensure that queries are filtered by the restaurant_id of the authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
