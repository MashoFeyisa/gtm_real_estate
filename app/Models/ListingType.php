<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingType extends Model
{
    use HasFactory;

    /** Five primary marketplace modes from the platform specification. */
    public const MODES = [
        'buy' => 'BUY',
        'rent' => 'RENT',
        'lease' => 'LEASE',
        'stay' => 'STAY',
        'new_projects' => 'NEW PROJECTS',
    ];

    protected $fillable = [
        'mode',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForMode(Builder $query, string $mode): void
    {
        $query->where('mode', $mode);
    }

    public static function modeOptions(): array
    {
        return self::MODES;
    }
}
