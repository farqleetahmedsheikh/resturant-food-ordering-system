<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddonGroup extends Model
{
    public const SELECTION_TYPES = [
        'single' => 'Single select',
        'multi' => 'Multi select',
    ];

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'selection_type',
        'is_required',
        'min_selections',
        'max_selections',
        'quantity_limit',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'min_selections' => 'integer',
            'max_selections' => 'integer',
            'quantity_limit' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'addon_group_items')
            ->withPivot(['sort_order', 'is_default'])
            ->withTimestamps()
            ->orderBy('addon_group_items.sort_order')
            ->orderBy('addons.sort_order')
            ->orderBy('addons.name');
    }

    public function activeAddons(): BelongsToMany
    {
        return $this->addons()
            ->where('addons.is_active', true)
            ->where('addons.is_available', true)
            ->whereNull('addons.archived_at');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'product_addon_groups')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_addon_groups')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'global_addon_groups')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps();
    }
}
