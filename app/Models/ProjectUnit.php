<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'building_id',
        'unit_number',
        'floor_number',
        'unit_type',
        'bedrooms',
        'bathrooms',
        'area_sqm',
        'price',
        'currency',
        'orientation',
        'parking_spaces',
        'status',
        'floor_plan_image',
        'features',
    ];

    protected $casts = [
        'area_sqm' => 'decimal:2',
        'price' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'parking_spaces' => 'integer',
        'features' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(ProjectBuilding::class, 'building_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(UnitReservation::class, 'unit_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', 'reserved');
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', 'sold');
    }

    public function getFormattedPriceAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format((float) $this->price);
    }

    public function getPricePerSqmAttribute(): float
    {
        if ($this->area_sqm > 0) {
            return round((float) $this->price / (float) $this->area_sqm, 2);
        }

        return 0;
    }

    public function getFormattedPricePerSqmAttribute(): string
    {
        return ($this->currency ?? 'ETB').' '.number_format($this->price_per_sqm).'/sqm';
    }
}
