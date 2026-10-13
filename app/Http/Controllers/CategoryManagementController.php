<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Database-configurable category management for the platform:
 * property categories (grouped by segment) and listing types (mapped to the
 * five marketplace modes). Administrators manage them from the dashboard.
 */
class CategoryManagementController extends Controller
{
    private function categoryRedirect(Request $request, string $message, ?string $error = null, array $extra = []): Response
    {
        if ($request->expectsJson()) {
            if ($error) {
                return response()->json(['success' => false, 'error' => $error], 422);
            }

            return response()->json(array_merge([
                'success' => true,
                'message' => $message,
                'section' => 'categories',
                'redirect_url' => route('dashboard').'#categories',
            ], $extra));
        }

        if ($error) {
            return redirect()->to(route('dashboard').'#categories')->withErrors(['category' => $error]);
        }

        return redirect()->to(route('dashboard').'#categories')->with('success', $message);
    }

    public function storeCategory(Request $request): Response
    {
        $this->guardAdmin();

        $validated = $request->validate([
            'group' => ['required', 'in:'.implode(',', PropertyCategory::GROUPS)],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $cat = PropertyCategory::query()->create([
            'group' => $validated['group'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'icon' => $validated['icon'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->categoryRedirect($request, "Category '{$validated['name']}' created.", null, ['category' => $cat]);
    }

    public function updateCategory(Request $request, PropertyCategory $propertyCategory): Response
    {
        $this->guardAdmin();

        $validated = $request->validate([
            'group' => ['required', 'in:'.implode(',', PropertyCategory::GROUPS)],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = Str::slug($validated['name']);
        $previousSlug = $propertyCategory->slug;

        $propertyCategory->update([
            'group' => $validated['group'],
            'name' => $validated['name'],
            'slug' => $slug === $previousSlug ? $slug : $this->uniqueSlug($validated['name'], $propertyCategory->id),
            'icon' => $validated['icon'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->categoryRedirect($request, "Category '{$validated['name']}' updated.", null, ['category' => $propertyCategory]);
    }

    public function destroyCategory(Request $request, PropertyCategory $propertyCategory): Response
    {
        $this->guardAdmin();

        if (Property::query()->where('property_category', $propertyCategory->slug)->exists()) {
            return $this->categoryRedirect($request, '', "Category '{$propertyCategory->name}' is still used by properties and cannot be deleted.");
        }

        $name = $propertyCategory->name;
        $propertyCategory->delete();

        return $this->categoryRedirect($request, "Category '{$name}' deleted.");
    }

    public function storeListingType(Request $request): Response
    {
        $this->guardAdmin();

        $validated = $request->validate([
            'mode' => ['required', 'in:'.implode(',', array_keys(ListingType::MODES))],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $type = ListingType::query()->create([
            'mode' => $validated['mode'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->categoryRedirect($request, "Listing type '{$validated['name']}' created.", null, ['listing_type' => $type]);
    }

    public function updateListingType(Request $request, ListingType $listingType): Response
    {
        $this->guardAdmin();

        $validated = $request->validate([
            'mode' => ['required', 'in:'.implode(',', array_keys(ListingType::MODES))],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = Str::slug($validated['name']);
        $previousSlug = $listingType->slug;

        $listingType->update([
            'mode' => $validated['mode'],
            'name' => $validated['name'],
            'slug' => $slug === $previousSlug ? $slug : $this->uniqueSlug($validated['name'], $listingType->id),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->categoryRedirect($request, "Listing type '{$validated['name']}' updated.", null, ['listing_type' => $listingType]);
    }

    public function destroyListingType(Request $request, ListingType $listingType): Response
    {
        $this->guardAdmin();

        if (Listing::query()->where('listing_type', $listingType->slug)->exists()) {
            return $this->categoryRedirect($request, '', "Listing type '{$listingType->name}' is still used by listings and cannot be deleted.");
        }

        $name = $listingType->name;
        $listingType->delete();

        return $this->categoryRedirect($request, "Listing type '{$name}' deleted.");
    }

    private function guardAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (PropertyCategory::query()->where('slug', $slug)->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))->exists()
            || ListingType::query()->where('slug', $slug)->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
