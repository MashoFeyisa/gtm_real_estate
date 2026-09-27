<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request, ?string $category = null)
    {
        $category = strtolower((string) ($category ?? $request->query('category', 'all')));

        $propertiesQuery = Property::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived']);

        if (in_array($category, ['villa', 'home', 'apartment'], true)) {
            $matches = match ($category) {
                'villa' => ['villa'],
                'home' => ['home', 'house', 'residence'],
                'apartment' => ['apartment', 'flat'],
                default => [$category],
            };

            $propertiesQuery->whereIn('property_category', $matches);
        }

        $properties = $propertiesQuery->latest()->get();

        return view('properties.index', [
            'properties' => $properties,
            'selectedCategory' => $category,
        ]);
    }

    public function show(string $slug)
    {
        $property = Property::query()
            ->with('agent')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereNotIn('status', ['draft', 'archived'])
            ->firstOrFail();

        $agents = Agent::orderBy('name')->get();

        return view('properties.show', compact('property', 'agents'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'area' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:sale,rent'],
            'category' => ['nullable', 'in:villa,home,apartment'],
            'status' => ['required', 'in:draft,published,archived,available,sold,rented'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = Str::slug($validated['title']);
        $baseSlug = $slug;
        $counter = 1;

        while (Property::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $this->storePropertyImage($request),
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

        return redirect()->route('dashboard')->with('success', 'Property "'.$property->title.'" created successfully.');
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'area' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:sale,rent'],
            'category' => ['nullable', 'in:villa,home,apartment'],
            'status' => ['required', 'in:draft,published,archived,available,sold,rented'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = Str::slug($validated['title']);
        if ($slug !== $property->slug) {
            $baseSlug = $slug;
            $counter = 1;

            while (Property::where('slug', $slug)->whereKeyNot($property->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
        }

        $imagePath = $property->image_path;

        if ($request->hasFile('image')) {
            if ($property->image_path && Storage::disk('public')->exists($property->image_path)) {
                Storage::disk('public')->delete($property->image_path);
            }

            $imagePath = $this->storePropertyImage($request);
        }

        $property->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
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

        $property->delete();

        return redirect()->route('dashboard')->with('success', 'Property deleted successfully.');
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
