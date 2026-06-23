<?php

namespace App\Models;

use App\Support\ImageUpload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    /** @use HasFactory<\Database\Factories\RestaurantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'short_description',
        'opening_time',
        'closing_time',
        'delivery_fee',
        'minimum_order_amount',
        'logo',
        'cover_image',
        'is_open',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'is_open' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return ImageUpload::url($this->logo);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return ImageUpload::url($this->cover_image);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
