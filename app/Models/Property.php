<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    protected $guarded = [];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
    ];

    public function getCategoryAttribute(): ?string
    {
        return $this->attributes['property_category'] ?? $this->attributes['category'] ?? null;
    }

    public function setCategoryAttribute($value): void
    {
        $this->attributes['property_category'] = $value;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
