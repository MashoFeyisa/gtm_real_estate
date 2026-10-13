<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyComparisonController extends Controller
{
    /**
     * Display comparison matrix for selected properties.
     */
    public function index(Request $request): View
    {
        $sessionIds = array_map('intval', session()->get('compared_property_ids', []));
        $queryIds = $request->query('ids') ? explode(',', (string) $request->query('ids')) : [];

        $allIds = array_slice(array_unique(array_filter(array_map('intval', array_merge($sessionIds, $queryIds)))), 0, 4);
        $isSample = false;

        // If no properties selected yet, provide top 2 available properties as sample preview
        if (empty($allIds)) {
            $sampleIds = Property::query()
                ->where('status', 'available')
                ->latest()
                ->take(2)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($sampleIds)) {
                $allIds = $sampleIds;
                $isSample = true;
            }
        }

        if (! empty($allIds) && ! $isSample) {
            session()->put('compared_property_ids', $allIds);
            session()->save();
        }

        $properties = Property::query()
            ->with(['agent', 'images', 'documents'])
            ->whereIn('id', $allIds)
            ->get()
            ->sortBy(fn ($p) => array_search((int) $p->id, $allIds, true))
            ->values();

        // Additional properties for quick "+ Add to Compare" selector
        $availableToAdd = Property::query()
            ->whereNotIn('id', $allIds)
            ->where('status', 'available')
            ->select(['id', 'title', 'price', 'type', 'city', 'subcity'])
            ->latest()
            ->take(12)
            ->get();

        return view('properties.compare', [
            'properties' => $properties,
            'maxCompare' => 4,
            'isSample' => $isSample,
            'availableToAdd' => $availableToAdd,
        ]);
    }

    /**
     * Add or remove a property from user's comparison basket.
     */
    public function toggle(Request $request, Property $property): JsonResponse|RedirectResponse
    {
        $compared = array_values(array_unique(array_map('intval', session()->get('compared_property_ids', []))));
        $propId = (int) $property->id;

        if (in_array($propId, $compared, true)) {
            $compared = array_values(array_diff($compared, [$propId]));
            $action = 'removed';
            $message = 'Removed from comparison.';
        } else {
            if (count($compared) >= 4) {
                array_shift($compared); // maintain max 4
            }
            $compared[] = $propId;
            $action = 'added';
            $message = 'Added to comparison.';
        }

        session()->put('compared_property_ids', $compared);
        session()->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $message,
                'count' => count($compared),
                'ids' => $compared,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Clear all compared properties.
     */
    public function clear(Request $request): RedirectResponse
    {
        session()->forget('compared_property_ids');
        session()->save();

        return redirect()->route('properties.compare')->with('success', 'Comparison list cleared.');
    }
}
