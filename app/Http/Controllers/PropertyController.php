<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\EthiopianGeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request, ?string $category = null)
    {
        $category = strtolower((string) ($category ?? $request->query('category', 'all')));

        $propertiesQuery = Property::query()->publicVisible();

        if (in_array($category, ['villa', 'home', 'apartment', 'guest_house', 'condominium', 'hotel', 'office', 'guesthouse', 'condo', 'hotels', 'offices'], true)) {
            $matches = match ($category) {
                'villa' => ['villa'],
                'home' => ['home', 'house', 'residence'],
                'apartment' => ['apartment', 'flat'],
                'guest_house' => ['guest_house', 'guesthouse', 'guest house'],
                'condominium' => ['condominium', 'condo', 'condos'],
                'hotel' => ['hotel', 'hotels', 'guest hotel'],
                'office' => ['office', 'offices', 'commercial office'],
                default => [$category],
            };

            $propertiesQuery->whereIn('property_category', $matches);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            // Natural Language Query Processing (§15, §131): e.g. "4-bedroom villa in Bole under 30 million"
            $parsed = $this->parseNaturalLanguageQuery($search);
            $hasStructuredFilters = false;

            if (! empty($parsed['bedrooms'])) {
                $propertiesQuery->where('bedrooms', '>=', $parsed['bedrooms']);
                $hasStructuredFilters = true;
            }
            if (! empty($parsed['max_price'])) {
                $propertiesQuery->where('price', '<=', $parsed['max_price']);
                $hasStructuredFilters = true;
            }
            if (! empty($parsed['type']) && ! $request->filled('type')) {
                $propertiesQuery->where('type', $parsed['type']);
                $hasStructuredFilters = true;
            }
            if (! empty($parsed['category']) && $category === 'all') {
                $propertiesQuery->whereIn('property_category', $parsed['category']);
                $hasStructuredFilters = true;
            }
            if (! empty($parsed['location']) && ! $request->filled('city') && ! $request->filled('subcity')) {
                $loc = $parsed['location'];
                $propertiesQuery->where(function ($q) use ($loc) {
                    $q->where('city', 'like', "%{$loc}%")
                        ->orWhere('subcity', 'like', "%{$loc}%")
                        ->orWhere('neighborhood', 'like', "%{$loc}%")
                        ->orWhere('address', 'like', "%{$loc}%");
                });
                $hasStructuredFilters = true;
            }

            // Keyword text search fallback when query is not pure natural language structure
            if (! $hasStructuredFilters) {
                $propertiesQuery->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('subcity', 'like', "%{$search}%")
                        ->orWhere('woreda', 'like', "%{$search}%")
                        ->orWhere('neighborhood', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }
        }

        if ($request->filled('type') && in_array($request->query('type'), ['sale', 'rent', 'stay'], true)) {
            $propertiesQuery->where('type', $request->query('type'));
        }

        if ($request->filled('city')) {
            $city = trim((string) $request->query('city'));
            $citiesList = app(EthiopianGeoService::class)->getCities();
            if (isset($citiesList[$city])) {
                $aliases = $citiesList[$city]['aliases'] ?? [$city];
                $propertiesQuery->where(function ($query) use ($aliases) {
                    foreach ($aliases as $alias) {
                        $query->orWhere('city', 'like', "%{$alias}%");
                    }
                });
            } else {
                $propertiesQuery->where('city', 'like', "%{$city}%");
            }
        }

        if ($request->filled('subcity')) {
            $subcity = trim((string) $request->query('subcity'));
            $propertiesQuery->where('subcity', 'like', "%{$subcity}%");
        }

        if ($request->filled('woreda')) {
            $woreda = trim((string) $request->query('woreda'));
            $propertiesQuery->where('woreda', 'like', "%{$woreda}%");
        }

        if ($request->filled('location')) {
            $location = trim((string) $request->query('location'));
            $propertiesQuery->where(function ($query) use ($location) {
                $query->where('city', 'like', "%{$location}%")
                    ->orWhere('subcity', 'like', "%{$location}%")
                    ->orWhere('neighborhood', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%");
            });
        }

        $mapBounds = $this->parseMapBounds($request->query('map_bounds'));

        if ($mapBounds) {
            [$swLat, $swLng, $neLat, $neLng] = $mapBounds;

            // Only listings with a real map pin can sit inside a drawn area.
            $propertiesQuery->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereBetween('latitude', [$swLat, $neLat])
                ->whereBetween('longitude', [$swLng, $neLng]);
        }

        $properties = $propertiesQuery->with(['agent', 'images'])->latest()->get();
        $subcities = app(EthiopianGeoService::class)->getSubcities();
        $cities = app(EthiopianGeoService::class)->getCities();

        $selectedCity = $request->query('city', '');
        $defaultCenter = null;
        if ($selectedCity && isset($cities[$selectedCity])) {
            $defaultCenter = $cities[$selectedCity]['lat'].','.$cities[$selectedCity]['lng'];
        }

        return view('properties.index', [
            'properties' => $properties,
            'mapProperties' => $properties->map->map_data->values(),
            'selectedCategory' => $category,
            'searchQuery' => $request->query('search', ''),
            'selectedType' => $request->query('type', ''),
            'selectedLocation' => $request->query('location', ''),
            'selectedCity' => $selectedCity,
            'selectedSubcity' => $request->query('subcity', ''),
            'cities' => $cities,
            'subcities' => $subcities,
            'mapBounds' => $mapBounds,
            'mapCenter' => $this->parseMapCenter($request->query('map_center')) ?? $defaultCenter,
            'mapZoom' => $this->parseMapZoom($request->query('map_zoom')),
        ]);
    }

    /**
     * Parse the "search this area" box drawn on the public map.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}|null south-west and north-east corners
     */
    private function parseMapBounds(mixed $raw): ?array
    {
        $parts = is_string($raw) ? array_map('trim', explode(',', $raw)) : [];

        if (count($parts) !== 4 || collect($parts)->contains(fn (string $part) => $part === '' || ! is_numeric($part))) {
            return null;
        }

        [$swLat, $swLng, $neLat, $neLng] = array_map('floatval', $parts);

        if ($swLat < -90 || $neLat > 90 || $swLng < -180 || $neLng > 180 || $swLat > $neLat || $swLng > $neLng) {
            return null;
        }

        return [$swLat, $swLng, $neLat, $neLng];
    }

    /**
     * Parse the map centre so the map reopens where the visitor left it.
     */
    private function parseMapCenter(mixed $raw): ?string
    {
        $parts = is_string($raw) ? array_map('trim', explode(',', $raw)) : [];

        if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1])) {
            return null;
        }

        return number_format((float) $parts[0], 7, '.', '').','.number_format((float) $parts[1], 7, '.', '');
    }

    private function parseMapZoom(mixed $raw): ?int
    {
        if (! is_numeric($raw)) {
            return null;
        }

        $zoom = (int) $raw;

        return $zoom >= 1 && $zoom <= 19 ? $zoom : null;
    }

    public function show(string $slug)
    {
        $query = Property::query()->with(['agent.user', 'images', 'campaigns']);

        $query->where(function ($q) use ($slug) {
            $q->where('slug', $slug);
            if (is_numeric($slug)) {
                $q->orWhere('id', (int) $slug);
            }
            $q->orWhere('property_code', $slug);
        });

        $user = auth()->user();
        $isPrivileged = $user && (
            $user->isAdmin() ||
            $user->isCompany() ||
            $user->isCampaignOfficer() ||
            $user->isFinanceOfficer() ||
            $user->isReceptionist()
        );

        if (! $isPrivileged) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'archived')
                    ->where(function ($sub) {
                        $sub->where('is_active', true)
                            ->orWhereHas('campaigns', fn ($cq) => $cq->whereIn('status', ['published', 'approved']));
                    });

                if ($user && $user->agentProfile) {
                    $q->orWhere('agent_id', $user->agentProfile->id);
                }
            });
        }

        $property = $query->firstOrFail();

        return view('properties.show', compact('property'));
    }

    public function store(PropertyFormRequest $request)
    {
        $this->authorize('create', Property::class);

        $validated = $request->validated();

        $slug = $this->uniqueSlug($validated['title']);

        $property = Property::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage(),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'building_area' => $validated['building_area'] ?? $validated['area'],
            'floors' => $validated['floors'] ?? 1,
            'parking_spaces' => $validated['parking_spaces'] ?? 1,
            'balconies' => $validated['balconies'] ?? 0,
            'year_built' => $validated['year_built'] ?? null,
            'condition' => $validated['condition'] ?? 'excellent',
            'has_electricity' => (bool) $request->input('has_electricity', false),
            'has_water' => (bool) $request->input('has_water', false),
            'has_generator' => (bool) $request->input('has_generator', false),
            'has_water_tank' => (bool) $request->input('has_water_tank', false),
            'has_solar' => (bool) $request->input('has_solar', false),
            'has_security' => (bool) $request->input('has_security', false),
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? 'home',
            'status' => $validated['status'],
            'city' => $validated['city'] ?? null,
            'subcity' => $validated['subcity'] ?? null,
            'woreda' => $validated['woreda'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'walk_score' => ! empty($validated['walk_score']) ? (int) $validated['walk_score'] : null,
            'transit_score' => ! empty($validated['transit_score']) ? (int) $validated['transit_score'] : null,
            'featured' => (bool) ($validated['featured'] ?? false),
            'agent_id' => $validated['agent_id'] ?? null,
            'is_active' => in_array($validated['status'], ['published', 'available', 'sold', 'rented'], true),
        ]);

        $request->syncPropertyImages($property);

        return $this->propertyRedirect('Property "'.$property->title.'" created successfully.');
    }

    public function update(PropertyFormRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validated();

        $slug = $this->uniqueSlug($validated['title'], $property->id);
        $oldPrice = (float) $property->price;
        $newPrice = (float) $validated['price'];

        $property->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage($property->image_path),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'building_area' => $validated['building_area'] ?? ($property->building_area ?: $validated['area']),
            'floors' => $validated['floors'] ?? ($property->floors ?: 1),
            'parking_spaces' => $validated['parking_spaces'] ?? ($property->parking_spaces ?: 1),
            'balconies' => $validated['balconies'] ?? ($property->balconies ?: 0),
            'year_built' => $validated['year_built'] ?? $property->year_built,
            'condition' => $validated['condition'] ?? ($property->condition ?: 'excellent'),
            'has_electricity' => (bool) $request->input('has_electricity', false),
            'has_water' => (bool) $request->input('has_water', false),
            'has_generator' => (bool) $request->input('has_generator', false),
            'has_water_tank' => (bool) $request->input('has_water_tank', false),
            'has_solar' => (bool) $request->input('has_solar', false),
            'has_security' => (bool) $request->input('has_security', false),
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? $property->property_category ?? 'home',
            'status' => $validated['status'],
            'city' => $validated['city'] ?? $property->city,
            'subcity' => $validated['subcity'] ?? $property->subcity,
            'woreda' => $validated['woreda'] ?? $property->woreda,
            'neighborhood' => $validated['neighborhood'] ?? $property->neighborhood,
            'address' => $validated['address'] ?? $property->address,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : $property->latitude,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : $property->longitude,
            'walk_score' => ! empty($validated['walk_score']) ? (int) $validated['walk_score'] : $property->walk_score,
            'transit_score' => ! empty($validated['transit_score']) ? (int) $validated['transit_score'] : $property->transit_score,
            'featured' => (bool) ($validated['featured'] ?? false),
            'agent_id' => $validated['agent_id'] ?? null,
            'is_active' => in_array($validated['status'], ['published', 'available', 'sold', 'rented'], true),
        ]);

        if ($oldPrice !== $newPrice) {
            foreach ($property->listings as $listing) {
                $listing->update(['price' => $newPrice]);
            }
        }

        $request->syncPropertyImages($property);

        return $this->propertyRedirect('Property updated successfully.');
    }

    public function togglePublish(Property $property)
    {
        $this->authorize('publish', $property);

        if ($property->status === 'archived') {
            $property->status = 'published';
            $property->is_active = true;
        } else {
            $property->status = $property->is_active ? 'draft' : 'published';
            $property->is_active = ! $property->is_active;
        }

        $property->save();

        return $this->propertyRedirect('Property publishing status updated.');
    }

    public function archive(Property $property)
    {
        $this->authorize('update', $property);

        $property->status = 'archived';
        $property->is_active = false;
        $property->save();

        return $this->propertyRedirect('Property archived successfully.');
    }

    public function toggleSold(Property $property)
    {
        $this->authorize('update', $property);

        $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';

        $property->status = $property->status === $soldStatus ? 'available' : $soldStatus;
        $property->is_active = in_array($property->status, ['published', 'available', 'sold', 'rented'], true);
        $property->save();

        $label = $soldStatus === 'rented' ? 'rental' : 'sale';

        return $this->propertyRedirect('Property '.$label.' status updated successfully.');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        foreach ($property->images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        if ($property->image_path && Storage::disk('public')->exists($property->image_path)) {
            Storage::disk('public')->delete($property->image_path);
        }

        $property->delete();

        return $this->propertyRedirect('Property deleted successfully.');
    }

    protected function propertyRedirect(string $message): RedirectResponse
    {
        $user = auth()->user();
        $target = route('dashboard');

        if ($user) {
            if ($user->isCompany()) {
                $target = route('company.dashboard', ['section' => 'properties']);
            } elseif (in_array($user->role, ['seller', 'landlord', 'property_owner'], true)) {
                $target = route('seller.dashboard');
            } elseif ($user->role === 'host') {
                $target = route('host.dashboard');
            } elseif ($user->agentProfile || $user->role === 'agent') {
                $target = route('agent.portal');
            } elseif ($user->role === 'developer') {
                $target = route('developer.portal');
            } else {
                $target = $user->dashboard_url;
            }
        }

        return redirect()->to($target)->with('success', $message);
    }

    public function deleteImage(Property $property, PropertyImage $image)
    {
        $this->authorize('update', $property);

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
        $this->authorize('update', $property);

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
        $this->authorize('update', $property);

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
            'guest_house', 'guest_houses', 'guesthouse', 'guesthouses', 'guest house', 'guest houses' => ['guest_house', 'guesthouse', 'guest house', 'guesthouses', 'guest houses'],
            'condominium', 'condominiums', 'condo', 'condos' => ['condominium', 'condominiums', 'condo', 'condos'],
            'hotel', 'hotels' => ['hotel', 'hotels', 'guest hotel'],
            'office', 'offices' => ['office', 'offices', 'commercial office'],
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
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isCompany())) {
            abort(403, 'Admin or Company access required.');
        }
    }

    /**
     * Parse natural language queries into structured search criteria (§15, §131).
     * e.g., "4-bedroom villa in Bole under 30 million"
     */
    protected function parseNaturalLanguageQuery(string $raw): array
    {
        $filters = [];

        // 1. Bedrooms: "4 bed", "3-bedroom", "5 bdrm"
        if (preg_match('/(\d+)\s*(?:-| )?(?:bed|bedroom|bdrm)s?/i', $raw, $m)) {
            $filters['bedrooms'] = (int) $m[1];
        }

        // 2. Maximum Price: "under 30 million", "below 25m", "under 80000"
        if (preg_match('/(?:under|below|less than|max)\s*([\d\.,]+)\s*(million|m|k|thousand)?/i', $raw, $m)) {
            $val = (float) str_replace(',', '', $m[1]);
            $unit = strtolower($m[2] ?? '');
            if (in_array($unit, ['million', 'm'], true)) {
                $val *= 1000000;
            } elseif (in_array($unit, ['k', 'thousand'], true)) {
                $val *= 1000;
            }
            $filters['max_price'] = $val;
        }

        // 3. Transaction mode: "for rent", "for sale", "short stay"
        if (preg_match('/\b(?:rent|for rent|rental|monthly)\b/i', $raw)) {
            $filters['type'] = 'rent';
        } elseif (preg_match('/\b(?:sale|for sale|buy|purchase)\b/i', $raw)) {
            $filters['type'] = 'sale';
        } elseif (preg_match('/\b(?:stay|short stay|vacation|nightly)\b/i', $raw)) {
            $filters['type'] = 'stay';
        }

        // 4. Property Category
        if (preg_match('/\bvillas?\b/i', $raw)) {
            $filters['category'] = ['villa', 'villas'];
        } elseif (preg_match('/\bapartments?\b/i', $raw)) {
            $filters['category'] = ['apartment', 'flat'];
        } elseif (preg_match('/\bcondominiums?|condos?\b/i', $raw)) {
            $filters['category'] = ['condominium', 'condo', 'condos'];
        } elseif (preg_match('/\bguest\s*houses?\b/i', $raw)) {
            $filters['category'] = ['guest_house', 'guesthouse', 'guest house'];
        } elseif (preg_match('/\bhotels?\b/i', $raw)) {
            $filters['category'] = ['hotel', 'hotels'];
        } elseif (preg_match('/\bhomes?|houses?\b/i', $raw)) {
            $filters['category'] = ['home', 'house', 'residence'];
        }

        // 5. Locations in Ethiopia: "in Bole", "in Harar", "in Hawassa", etc.
        if (preg_match('/\bin\s+([A-Za-z\s\-]+)/i', $raw, $m)) {
            $cleanLoc = trim(explode(' ', trim($m[1]))[0]);
            if (strlen($cleanLoc) >= 3) {
                $filters['location'] = $cleanLoc;
            }
        }

        return $filters;
    }
}
