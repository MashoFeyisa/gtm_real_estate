<?php

namespace App\Services;

use App\Models\Property;

class DuplicateDetector
{
    /**
     * Check if a property is a suspected duplicate of an existing active listing.
     *
     * @return array{is_duplicate: bool, risk_score: int, matching_property_ids: array, reasons: array}
     */
    public function detectDuplicates(Property|array $candidate): array
    {
        $title = is_array($candidate) ? ($candidate['title'] ?? '') : $candidate->title;
        $city = is_array($candidate) ? ($candidate['city'] ?? '') : $candidate->city;
        $subcity = is_array($candidate) ? ($candidate['subcity'] ?? '') : $candidate->subcity;
        $price = (float) (is_array($candidate) ? ($candidate['price'] ?? 0) : $candidate->price);
        $candidateId = is_array($candidate) ? ($candidate['id'] ?? null) : $candidate->id;

        $potentialMatches = Property::query()
            ->when($candidateId, fn ($q) => $q->where('id', '!=', $candidateId))
            ->where('city', $city)
            ->where('subcity', $subcity)
            ->where('status', 'published')
            ->get();

        $matchingIds = [];
        $reasons = [];
        $maxRiskScore = 0;

        foreach ($potentialMatches as $match) {
            $currentRisk = 0;

            // 1. Title text similarity
            similar_text(mb_strtolower($title), mb_strtolower($match->title), $titlePercent);
            if ($titlePercent >= 85) {
                $currentRisk += 50;
                $reasons[] = "High title similarity ({$titlePercent}%) with listing #{$match->id}";
            }

            // 2. Price proximity (within 5% margin)
            if ($match->price > 0 && $price > 0) {
                $priceRatio = abs($price - $match->price) / $match->price;
                if ($priceRatio <= 0.05) {
                    $currentRisk += 30;
                    $reasons[] = "Identical or near-identical price range with listing #{$match->id}";
                }
            }

            if ($currentRisk >= 60) {
                $matchingIds[] = $match->id;
                $maxRiskScore = max($maxRiskScore, $currentRisk);
            }
        }

        return [
            'is_duplicate' => $maxRiskScore >= 60,
            'risk_score' => min(100, $maxRiskScore),
            'matching_property_ids' => array_unique($matchingIds),
            'reasons' => array_unique($reasons),
        ];
    }
}
