<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UpsellRule extends Model
{
    public const TRIGGERS = [
        'global' => 'Every order',
        'product' => 'Specific product in cart',
        'category' => 'Category in cart',
        'subtotal' => 'Cart subtotal threshold',
        'missing_category' => 'Category missing from cart',
        'product_combo' => 'Product combination in cart',
    ];

    public const PLACEMENTS = [
        'product_detail' => 'Product detail',
        'after_add_to_cart' => 'After add-to-cart',
        'cart' => 'Cart page',
        'before_checkout' => 'Before checkout',
    ];

    protected $fillable = [
        'restaurant_id',
        'name',
        'trigger_type',
        'trigger_config',
        'placement',
        'priority',
        'max_recommendations',
        'allow_duplicates',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'priority' => 'integer',
            'max_recommendations' => 'integer',
            'allow_duplicates' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'upsell_rule_products')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('upsell_rule_products.sort_order');
    }
}
