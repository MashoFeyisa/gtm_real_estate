<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request, ?string $category = null)
    {
        $category = strtolower((string) ($category ?? $request->query('category', 'all')));

        $propertiesQuery = Property::query()->publicVisible();

        if (in_array($category, ['villa', 'home', 'apartment'], true)) {
            $matches = match ($category) {
                'villa' => ['villa'],
                'home' => ['home', 'house', 'residence'],
                'apartment' => ['apartment', 'flat'],
                default => [$category],
            };

            $propertiesQuery->whereIn('property_category', $matches);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $propertiesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && in_array($request->query('type'), ['sale', 'rent'], true)) {
            $propertiesQuery->where('type', $request->query('type'));
        }

        if ($request->filled('location')) {
            $location = trim((string) $request->query('location'));
            $propertiesQuery->where(function ($query) use ($location) {
                $query->where('city', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%");
            });
        }

        $properties = $propertiesQuery->latest()->get();

        return view('properties.index', [
            'properties' => $properties,
            'selectedCategory' => $category,
            'searchQuery' => $request->query('search', ''),
            'selectedType' => $request->query('type', ''),
            'selectedLocation' => $request->query('location', ''),
        ]);
    }

    public function show(string $slug)
    {
        $property = Property::query()
            ->with('agent')
            ->where('slug', $slug)
            ->publicVisible()
            ->firstOrFail();

        return view('properties.show', compact('property'));
    }

    public function store(PropertyFormRequest $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validationData();

        $slug = $this->uniqueSlug($validated['title']);

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage(),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? 'home',
            'status' => $validated['status'],
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'featured' => (bool) ($validated['featured'] ?? false),
            'agent_id' => $validated['agent_id'] ?? null,
            'is_active' => in_array($validated['status'], ['published', 'available', 'sold', 'rented'], true),
        ]);

        $request->syncPropertyImages($property);

        return redirect()->route('dashboard')->with('success', 'Property "'.$property->title.'" created successfully.');
    }

    public function update(PropertyFormRequest $request, Property $property)
    {
        $this->authorizeAdmin();

        $validated = $request->validationData();

        $slug = $this->uniqueSlug($validated['title'], $property->id);

        $property->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage($property->image_path),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? $property->property_category ?? 'home',
            'status' => $validated['status'],
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'featured' => (bool) ($validated['featured'] ?? false),
            'agent_id' => $validated['agent_id'] ?? null,
            'is_active' => in_array($validated['status'], ['published', 'available', 'sold', 'rented'], true),
        ]);

        $request->syncPropertyImages($property);

        return redirect()->route('dashboard')->with('success', 'Property updated successfully.');
    }

    public function togglePublish(Property $property)
    {
        $this->authorizeAdmin();

        if ($property->status === 'archived') {
            $property->status = 'published';
            $property->is_active = true;
        } else {
            $property->status = $property->is_active ? 'draft' : 'published';
            $property->is_active = ! $property->is_active;
        }

        $property->save();

        return redirect()->route('dashboard')->with('success', 'Property publishing status updated.');
    }

    public function archive(Property $property)
    {
        $this->authorizeAdmin();

        $property->status = 'archived';
        $property->is_active = false;
        $property->save();

        return redirect()->route('dashboard')->with('success', 'Property archived successfully.');
    }

    public function toggleSold(Property $property)
    {
        $this->authorizeAdmin();

        $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';

        $property->status = $property->status === $soldStatus ? 'available' : $soldStatus;
        $property->is_active = in_array($property->status, ['published', 'available', 'sold', 'rented'], true);
        $property->save();

        $label = $soldStatus === 'rented' ? 'rental' : 'sale';

        return redirect()->route('dashboard')->with('success', 'Property '.$label.' status updated successfully.');
    }

    public function destroy(Property $property)
    {
        $this->authorizeAdmin();

        foreach ($property->images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        if ($property->image_path && Storage::disk('public')->exists($property->image_path)) {
            Storage::disk('public')->delete($property->image_path);
        }

        $property->delete();

        return redirect()->route('dashboard')->with('success', 'Property deleted successfully.');
    }

    public function deleteImage(Property $property, PropertyImage $image)
    {
        $this->authorizeAdmin();

        abort_unless($image->property_id === $property->id, 404);

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        if ($property->image_path === $image->image_path) {
            $next = $property->images()->first();
            $property->update(['image_path' => $next?->image_path]);
        }

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Image removed successfully.');
    }

    public function setCoverImage(Request $request, Property $property, PropertyImage $image)
    {
        $this->authorizeAdmin();

        abort_unless($image->property_id === $property->id, 404);

        $property->update(['image_path' => $image->image_path]);
        $image->update(['sort_order' => 1]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'message' => 'Cover image updated successfully.',
                'image_id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => $image->image_url,
            ]);
        }

        return back()->with('success', 'Cover image updated successfully.');
    }

    public function setCoverPreset(Request $request, Property $property)
    {
        $this->authorizeAdmin();

        $preset = $request->validate([
            'preset' => ['required', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
        ])['preset'];

        $path = 'images/luxury/'.$preset.'.jpg';
        $property->update(['image_path' => $path]);

        $existing = $property->images()->where('image_path', $path)->first();
        if (! $existing) {
            $property->images()->create([
                'image_path' => $path,
                'sort_order' => 1,
            ]);
        } else {
            $existing->update(['sort_order' => 1]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'preset' => $preset,
                'message' => 'Luxury background preset set as cover.',
                'image_path' => $path,
                'image_url' => asset($path),
            ]);
        }

        return back()->with('success', 'Luxury background preset set as cover.');
    }

    protected function categoryKeywords(string $category): ?array
    {
        return match ($category) {
            'villa', 'villas' => ['villa', 'villas'],
            'home', 'homes', 'house', 'houses' => ['home', 'homes', 'house', 'houses', 'residence', 'residences'],
            'apartment', 'apartments', 'apartment-suite', 'apartment suite' => ['apartment', 'apartments', 'flat', 'flats', 'suite'],
            default => null,
        };
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;
        $counter = 1;

        $query = Property::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        while ($query->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;

            $query = Property::query()->where('slug', $slug);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }
        }

        return $slug;
    }

    protected function storePropertyImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('properties', 'public');
    }

    protected function authorizeAdmin(): void
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }
}
