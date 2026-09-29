<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'total_amount', 'payment_method', 'payment_status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
