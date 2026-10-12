<?php

namespace App\Http\Controllers;

use App\Models\SubcityBoundary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcityBoundaryController extends Controller
{
    /**
     * Display a GeoJSON feature collection of subcity polygon boundaries.
     */
    public function index(Request $request): JsonResponse
    {
        $city = (string) $request->query('city', 'Addis Ababa');

        $boundaries = SubcityBoundary::query()
            ->byCity($city)
            ->get();

        $features = $boundaries->map(fn (SubcityBoundary $b) => $b->toGeoJsonFeature())->values()->all();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
