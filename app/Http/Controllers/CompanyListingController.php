<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Company listing management (SRS: create, edit, approve, reject, suspend,
 * archive, feature, expire, merge duplicate records).
 */
class CompanyListingController extends Controller
{
    public function store(PropertyFormRequest $request): RedirectResponse
    {
        $this->authorizeCompanyDesk();

        $validated = $request->validated();

        $status = in_array($validated['status'], ['published', 'draft', 'available', 'sold', 'rented', 'archived'], true)
            ? $validated['status']
            : 'published';

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
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
            'status' => $status,
            'city' => $validated['city'] ?? null,
            'subcity' => $validated['subcity'] ?? null,
            'woreda' => $validated['woreda'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'featured' => $request->boolean('featured'),
            'agent_id' => null,
            'is_active' => $status === 'published' || $status === 'available',
        ]);

        return redirect()
            ->route('company.dashboard', ['section' => 'properties'])
            ->with('success', 'Listing "'.$property->title.'" created successfully.');
    }

    public function editForm(Property $property): RedirectResponse
    {
        $this->authorizeCompanyDesk();

        return redirect()
            ->route('company.dashboard', ['section' => 'properties', 'edit_property' => $property->id]);
    }

    public function update(PropertyFormRequest $request, Property $property): RedirectResponse
    {
        $this->authorizeCompanyDesk();

        $validated = $request->validated();

        $status = in_array($validated['status'], ['published', 'draft', 'available', 'sold', 'rented', 'archived'], true)
            ? $validated['status']
            : $property->status;

        $property->update([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title'], $property->id),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? $property->property_category,
            'status' => $status,
            'city' => $validated['city'] ?? null,
            'subcity' => $validated['subcity'] ?? null,
            'address' => $validated['address'] ?? null,
            'featured' => $request->boolean('featured'),
            'is_active' => in_array($status, ['published', 'available'], true),
        ]);

        return redirect()
            ->route('company.dashboard', ['section' => 'properties'])
            ->with('success', 'Listing "'.$property->title.'" updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse|JsonResponse
    {
        $this->authorizeCompanyDesk();

        $title = $property->title;
        $property->delete();

        $message = 'Listing "'.$title.'" deleted successfully.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'listings',
            ]);
        }

        return redirect()
            ->to(route('company.dashboard', ['section' => 'properties']).'#listings')
            ->with('success', $message);
    }

    public function togglePublish(Property $property): RedirectResponse|JsonResponse
    {
        $this->authorizeCompanyDesk();

        $property->update([
            'is_active' => ! $property->is_active,
            'status' => $property->is_active ? 'archived' : 'published',
        ]);

        $message = $property->is_active
            ? "Listing '{$property->title}' unpublished."
            : "Listing '{$property->title}' published.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'listings',
            ]);
        }

        return redirect()->to(route('dashboard').'#listings')->with('success', $message);
    }

    public function setStatus(Property $property, string $status): RedirectResponse|JsonResponse
    {
        $this->authorizeCompanyDesk();

        $allowed = ['available', 'sold', 'rented', 'draft', 'archived', 'published'];

        abort_unless(in_array($status, $allowed, true), 422);

        $property->update([
            'status' => $status,
            'is_active' => in_array($status, ['published', 'available'], true),
        ]);

        $message = "Listing '{$property->title}' marked as ".$status.'.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'listings',
            ]);
        }

        return redirect()->to(route('dashboard').'#listings')->with('success', $message);
    }

    private function authorizeCompanyDesk(): void
    {
        abort_unless(auth()->user()?->canAccessCompanyDashboard(), 403);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Property::query()->where('slug', $slug)->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
