<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->role === 'admin'
            && $this->is_active;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'rider_id');
    }

    public function deliveredOrders(): HasMany
    {
        return $this->assignedOrders()->where('order_status', 'delivered');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
