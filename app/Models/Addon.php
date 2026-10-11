<?php

namespace App\Models;

use App\Support\ImageUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Addon extends Model
{
    public const TYPES = [
        'customization' => 'Customization',
        'upsell' => 'Upsell',
    ];

    protected $fillable = [
        'restaurant_id',
        'name',
        'type',
        'description',
        'price',
        'image',
        'sort_order',
        'is_available',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
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

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AddonGroup::class, 'addon_group_items')
            ->withPivot(['sort_order', 'is_default'])
            ->withTimestamps();
    }
}
