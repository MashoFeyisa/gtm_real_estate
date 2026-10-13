<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyApiController extends Controller
{
    /**
     * Search and filter properties for mobile applications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query()
            ->with(['images', 'agent.branch'])
            ->where('is_active', true)
            ->where('status', 'published');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('subcity')) {
            $query->where('subcity', $request->input('subcity'));
        }

        if ($request->filled('category')) {
            $query->where('property_category', $request->input('category'));
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', (int) $request->input('bedrooms'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        $perPage = min((int) ($request->input('per_page', 15)), 50);
        $properties = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }

    /**
     * Show full property details.
     */
    public function show(Property $property): JsonResponse
    {
        $property->load(['images', 'agent.branch', 'documents' => function ($q) {
            $q->where('access_level', 'public');
        }]);

        return response()->json([
            'success' => true,
            'data' => $property,
        ]);
    }
}
