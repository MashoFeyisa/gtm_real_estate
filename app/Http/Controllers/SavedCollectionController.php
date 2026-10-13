<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SavedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedCollectionController extends Controller
{
    /**
     * Create a new named collection.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ]);

        $collection = $request->user()->savedCollections()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? false),
        ]);

        if (! empty($validated['property_id'])) {
            $collection->properties()->syncWithoutDetaching([$validated['property_id']]);
        }

        return back()->with('success', 'Collection "'.$collection->name.'" created successfully.');
    }

    /**
     * Toggle a property inside a collection.
     */
    public function toggleProperty(Request $request, SavedCollection $collection, Property $property): JsonResponse|RedirectResponse
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($collection->properties()->where('property_id', $property->id)->exists()) {
            $collection->properties()->detach($property->id);
            $action = 'removed';
            $msg = 'Removed from collection "'.$collection->name.'".';
        } else {
            $collection->properties()->attach($property->id, ['notes' => $request->input('notes')]);
            $action = 'added';
            $msg = 'Saved to collection "'.$collection->name.'".';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a collection.
     */
    public function destroy(Request $request, SavedCollection $collection): RedirectResponse
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        $collection->delete();

        return back()->with('success', 'Collection deleted.');
    }
}
