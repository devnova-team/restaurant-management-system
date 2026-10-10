<?php

namespace App\Models;

use App\Enums\StaffRole;
use App\Models\Scopes\TenantScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['restaurant_id', 'name', 'role', 'phone', 'email', 'password_hash', 'is_active'];

    protected $hidden = ['password_hash'];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_active' => 'boolean',
            'role' => StaffRole::class,
        ];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    // Scope to ensure that queries are filtered by the restaurant_id of the authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
