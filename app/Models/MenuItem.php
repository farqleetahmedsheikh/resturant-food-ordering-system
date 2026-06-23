<?php

namespace App\Models;

use App\Support\ImageUpload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_at_price',
        'image',
        'preparation_time',
        'calories',
        'is_featured',
        'sort_order',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'preparation_time' => 'integer',
            'calories' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return ImageUpload::url($this->image);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(MenuItemSize::class)->orderBy('sort_order')->orderBy('name');
    }

    public function activeSizes(): HasMany
    {
        return $this->sizes()->where('is_active', true);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(MenuItemAddon::class)->orderBy('type')->orderBy('sort_order')->orderBy('name');
    }

    public function activeAddons(): HasMany
    {
        return $this->addons()->where('is_active', true);
    }
}
