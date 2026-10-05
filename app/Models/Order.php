<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'restaurant_id',
        'channel',
        'status',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'tracking_token',
        'created_by_staff_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function createdByStaff(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'created_by_staff_id'
        );
    }

    // Scope to ensure that queries are filtered by the restaurant_id of the authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
