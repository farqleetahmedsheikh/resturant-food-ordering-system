<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'menu_item_id',
        'menu_item_size_id',
        'line_hash',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(MenuItemSize::class, 'menu_item_size_id');
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(MenuItemAddon::class, 'cart_item_addons')
            ->withTimestamps()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
