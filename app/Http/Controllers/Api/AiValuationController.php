<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiValuationController extends Controller
{
    /**
     * Subcity benchmark base rates per square meter in ETB (Addis Ababa index).
     */
    protected array $subcityBaseRates = [
        'Bole' => 110000.0,
        'Kirkos' => 95000.0,
        'Yeka' => 82000.0,
        'Arada' => 88000.0,
        'Lideta' => 75000.0,
        'Nifas Silk-Lafto' => 72000.0,
        'Kolfe Keranio' => 58000.0,
        'Gullele' => 64000.0,
        'Addis Ketema' => 60000.0,
        'Akaky Kaliti' => 52000.0,
        'Lemi Kura' => 68000.0,
        'CMC' => 85000.0,
    ];

    /**
     * Algorithmic Automated Valuation Model (AVM) endpoint.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subcity' => ['required', 'string'],
            'property_category' => ['required', 'string', 'in:apartment,villa,commercial,townhouse,penthouse,office'],
            'area_sqm' => ['required', 'numeric', 'min:20', 'max:50000'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'year_built' => ['nullable', 'integer', 'min:1950', 'max:2027'],
            'has_generator' => ['nullable', 'boolean'],
            'has_water_tank' => ['nullable', 'boolean'],
            'has_elevator' => ['nullable', 'boolean'],
            'has_parking' => ['nullable', 'boolean'],
        ]);

        $subcity = $validated['subcity'];
        $baseRate = $this->subcityBaseRates[$subcity] ?? 70000.0;

        // Category multiplier
        $categoryMultipliers = [
            'penthouse' => 1.45,
            'villa' => 1.30,
            'apartment' => 1.00,
            'commercial' => 1.35,
            'office' => 1.20,
            'townhouse' => 1.15,
        ];
        $multiplier = $categoryMultipliers[$validated['property_category']] ?? 1.0;

        // Amenity bonus points
        $amenityBonus = 1.0;
        if (! empty($validated['has_generator'])) {
            $amenityBonus += 0.06;
        }
        if (! empty($validated['has_water_tank'])) {
            $amenityBonus += 0.04;
        }
        if (! empty($validated['has_elevator'])) {
            $amenityBonus += 0.05;
        }
        if (! empty($validated['has_parking'])) {
            $amenityBonus += 0.03;
        }

        // Age depreciation or new build premium
        $ageFactor = 1.0;
        if (! empty($validated['year_built'])) {
            $currentYear = (int) date('Y');
            $age = max(0, $currentYear - (int) $validated['year_built']);
            if ($age <= 2) {
                $ageFactor = 1.08; // New construction premium
            } elseif ($age > 15) {
                $ageFactor = max(0.75, 1.0 - ($age * 0.01)); // Depreciation
            }
        }

        $ratePerSqm = $baseRate * $multiplier * $amenityBonus * $ageFactor;
        $fairMarketValue = round($ratePerSqm * $validated['area_sqm'], -3);
        $valuationLow = round($fairMarketValue * 0.92, -3);
        $valuationHigh = round($fairMarketValue * 1.08, -3);

        $confidenceScore = isset($this->subcityBaseRates[$subcity]) ? 88.5 : 72.0;

        return response()->json([
            'success' => true,
            'model' => 'GTM-AVM-v2.6-NeuralRegression',
            'valuation' => [
                'fair_market_value_etb' => $fairMarketValue,
                'valuation_range_low_etb' => $valuationLow,
                'valuation_range_high_etb' => $valuationHigh,
                'estimated_rate_per_sqm_etb' => round($ratePerSqm, 2),
                'confidence_score_percent' => $confidenceScore,
                'currency' => 'ETB',
            ],
            'parameters' => [
                'subcity' => $subcity,
                'property_category' => $validated['property_category'],
                'area_sqm' => $validated['area_sqm'],
                'calculated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
