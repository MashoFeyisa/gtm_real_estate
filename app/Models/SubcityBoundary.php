<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcityBoundary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'city',
        'name',
        'amharic_name',
        'om_name',
        'center_lat',
        'center_lng',
        'coordinates',
        'properties_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'center_lat' => 'float',
            'center_lng' => 'float',
            'coordinates' => 'array',
            'properties_count' => 'integer',
        ];
    }

    /**
     * Scope query to a specific city.
     */
    public function scopeByCity(Builder $query, string $city = 'Addis Ababa'): Builder
    {
        return $query->where('city', $city);
    }

    /**
     * Test whether a point (lat, lng) lies inside the polygon boundary via ray-casting.
     */
    public function containsPoint(float $lat, float $lng): bool
    {
        $points = $this->coordinates;
        if (! is_array($points) || count($points) < 3) {
            return false;
        }

        $inside = false;
        $count = count($points);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) ($points[$i][1] ?? $points[$i]['lng'] ?? 0); // lng
            $yi = (float) ($points[$i][0] ?? $points[$i]['lat'] ?? 0); // lat
            $xj = (float) ($points[$j][1] ?? $points[$j]['lng'] ?? 0); // lng
            $yj = (float) ($points[$j][0] ?? $points[$j]['lat'] ?? 0); // lat

            $intersect = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Convert model instance to GeoJSON Feature format.
     *
     * @return array<string, mixed>
     */
    public function toGeoJsonFeature(): array
    {
        $coords = array_map(function ($point) {
            $lat = (float) ($point[0] ?? $point['lat'] ?? 0);
            $lng = (float) ($point[1] ?? $point['lng'] ?? 0);

            return [$lng, $lat]; // GeoJSON expects [longitude, latitude]
        }, (array) $this->coordinates);

        if (! empty($coords) && ($coords[0] !== $coords[count($coords) - 1])) {
            $coords[] = $coords[0];
        }

        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $this->id,
                'city' => $this->city,
                'name' => $this->name,
                'amharic_name' => $this->amharic_name,
                'om_name' => $this->om_name,
                'center' => [$this->center_lat, $this->center_lng],
                'properties_count' => $this->properties_count,
            ],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$coords],
            ],
        ];
    }
}
