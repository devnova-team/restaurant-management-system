<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = ['name', 'owner_phone'];

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

}
