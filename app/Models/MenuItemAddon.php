<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemAddon extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemAddonFactory> */
    use HasFactory;

    public const TYPES = [
        'topping' => 'Topping',
        'extra' => 'Extra',
        'dip' => 'Dip / Sauce',
    ];

    protected $fillable = [
        'menu_item_id',
        'name',
        'type',
        'price',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
