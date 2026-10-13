<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyManagerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        // Managed properties
        $properties = Property::with(['images', 'leases', 'bookings'])->latest()->take(20)->get();

        $totalUnits = $properties->count();
        $occupiedUnits = $properties->filter(fn ($p) => $p->status === 'rented' || $p->leases->where('status', 'active')->isNotEmpty())->count();
        $vacantUnits = max(0, $totalUnits - $occupiedUnits);
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        $tenants = Lease::with(['property', 'tenant'])->where('status', 'active')->latest()->get();
        $guests = Booking::with('property')->whereIn('status', ['confirmed', 'checked_in'])->latest()->get();
        $rentSchedules = RentSchedule::with('lease.property')->latest()->take(15)->get();
        $maintenanceTickets = MaintenanceTicket::with('property')->latest()->get();
        $cleaningTasks = CleaningTask::with(['property', 'cleaner'])->latest()->get();
        $cleaners = User::whereIn('role', ['cleaner', 'receptionist', 'service_provider'])->get();

        return view('dashboards.property_manager', compact(
            'properties',
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'occupancyRate',
            'tenants',
            'guests',
            'rentSchedules',
            'maintenanceTickets',
            'cleaningTasks',
            'cleaners'
        ));
    }

    public function dispatchCleaning(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'cleaner_user_id' => ['nullable', 'exists:users,id'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'priority' => ['required', 'in:low,normal,urgent'],
            'scheduled_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $task = CleaningTask::create([
            'property_id' => $validated['property_id'],
            'cleaner_user_id' => $validated['cleaner_user_id'] ?? null,
            'task_code' => 'CLN-'.strtoupper(Str::random(6)),
            'room_number' => $validated['room_number'] ?? 'Unit 101',
            'status' => 'assigned',
            'priority' => $validated['priority'],
            'scheduled_date' => $validated['scheduled_date'] ?? now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
            'checklist' => [
                ['item' => 'Linen change & bed preparation', 'done' => false],
                ['item' => 'Bathroom sanitation & restock', 'done' => false],
                ['item' => 'Kitchen cleaning & trash disposal', 'done' => false],
                ['item' => 'Dusting, floor vacuum & mopping', 'done' => false],
            ],
        ]);

        AuditLog::log(
            action: 'property_manager.cleaning_dispatched',
            entityType: 'CleaningTask',
            entityId: $task->id,
            newValue: $task->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Cleaning task '{$task->task_code}' dispatched.");
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'property_category' => ['required', 'string', 'in:apartment,condominium,villa,home,office,guest_house,hotel'],
            'type' => ['required', 'string', 'in:rent,sale,stay'],
            'price' => ['required', 'numeric', 'min:0'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('properties', 'public');
        }

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
            'price' => $validated['price'],
            'type' => $validated['type'],
            'property_category' => $validated['property_category'],
            'city' => $validated['city'],
            'subcity' => $validated['subcity'] ?? 'Bole',
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 75,
            'status' => $validated['status'] === 'rented' ? 'rented' : ($validated['status'] === 'maintenance' ? 'maintenance' : 'available'),
            'image_path' => $imagePath,
            'is_active' => true,
            'condition' => 'excellent',
            'has_electricity' => true,
            'has_water' => true,
            'agent_id' => $user->agentProfile?->id,
        ]);

        if ($imagePath) {
            $property->images()->create([
                'image_path' => $imagePath,
                'sort_order' => 1,
            ]);
        }

        AuditLog::log(
            action: 'property_manager.unit_created',
            entityType: 'Property',
            entityId: $property->id,
            newValue: $property->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Unit '{$property->title}' added to managed portfolio successfully.");
    }

    public function updateUnit(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance,draft,archived'],
            'property_category' => ['nullable', 'string', 'in:apartment,condominium,villa,home,office,guest_house,hotel'],
            'type' => ['nullable', 'string', 'in:rent,sale,stay'],
            'city' => ['nullable', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
        ]);

        $updateData = [
            'title' => $validated['title'],
            'price' => $validated['price'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['property_category'])) {
            $updateData['property_category'] = $validated['property_category'];
        }
        if (! empty($validated['type'])) {
            $updateData['type'] = $validated['type'];
        }
        if (! empty($validated['city'])) {
            $updateData['city'] = $validated['city'];
        }
        if (isset($validated['subcity'])) {
            $updateData['subcity'] = $validated['subcity'];
        }
        if (isset($validated['bedrooms'])) {
            $updateData['bedrooms'] = $validated['bedrooms'];
        }
        if (isset($validated['bathrooms'])) {
            $updateData['bathrooms'] = $validated['bathrooms'];
        }
        if (isset($validated['area'])) {
            $updateData['area'] = $validated['area'];
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('properties', 'public');
            $updateData['image_path'] = $path;
            $property->images()->create([
                'image_path' => $path,
                'sort_order' => 1,
            ]);
        }

        $property->update($updateData);

        AuditLog::log(
            action: 'property_manager.unit_updated',
            entityType: 'Property',
            entityId: $property->id,
            newValue: $property->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Unit '{$property->title}' updated successfully.");
    }
}
