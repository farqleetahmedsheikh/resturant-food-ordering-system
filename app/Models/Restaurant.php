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
        'email',
        'phone',
        'address',
        'formatted_address',
        'short_description',
        'opening_time',
        'closing_time',
        'timezone',
        'latitude',
        'longitude',
        'delivery_fee',
        'minimum_order_amount',
        'logo',
        'cover_image',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_open' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return self::query()->oldest('id')->first();
    }

    public function getInitialsAttribute(): string
    {
        $words = collect(preg_split('/\s+/', trim((string) $this->name)) ?: [])
            ->map(fn (string $word): string => trim($word))
            ->filter(fn (string $word): bool => $word !== '')
            ->reject(fn (string $word): bool => in_array(mb_strtolower($word), ['the', 'and', '&'], true))
            ->take(2)
            ->map(fn (string $word): string => mb_substr($word, 0, 1));

        $initials = $words->implode('');

        return mb_strtoupper($initials ?: 'AK');
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
