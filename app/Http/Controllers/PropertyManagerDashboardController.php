<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyInspection;
use App\Models\RentSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyManagerDashboardController extends Controller
{
    /**
     * Display the Property Manager Dashboard with unified shell.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual property manager dashboards.');
        }

        if ($user?->status === 'suspended') {
            abort(403, 'Account suspended.');
        }

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        // Strict Manager Isolation: Only properties owned or managed by this user
        $managedIds = Property::where('user_id', $user->id)
            ->when($user->agentProfile, fn ($q) => $q->orWhere('agent_id', $user->agentProfile->id))
            ->pluck('id')
            ->all();

        $properties = Property::with(['images', 'leases.tenant', 'leases.schedules', 'parentBuilding', 'units', 'inspections'])
            ->where(function ($q) use ($user, $managedIds) {
                $q->where('user_id', $user->id);
                if ($user->agentProfile) {
                    $q->orWhere('agent_id', $user->agentProfile->id);
                }
                if (! empty($managedIds)) {
                    $q->orWhereIn('parent_id', $managedIds);
                }
            })
            ->latest()
            ->get();

        $propertyIds = $properties->pluck('id')->all();

        // Multi-unit breakdown
        $buildings = $properties->whereNull('parent_id');
        $units = $properties->filter(fn ($p) => $p->parent_id !== null || ! empty($p->unit_number) || $p->property_category === 'apartment');

        $totalUnits = $properties->count();
        $occupiedUnits = $properties->filter(fn ($p) => $p->status === 'rented' || $p->leases->where('status', 'active')->isNotEmpty())->count();
        $vacantUnits = max(0, $totalUnits - $occupiedUnits);
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        $tenants = Lease::with(['property', 'tenant'])
            ->whereIn('property_id', $propertyIds)
            ->where('status', 'active')
            ->latest()
            ->get();

        $guests = Booking::with('property')
            ->whereIn('property_id', $propertyIds)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->latest()
            ->get();

        $rentSchedules = RentSchedule::whereHas('lease', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->with(['lease.property', 'lease.tenant'])
            ->latest()
            ->get();

        $maintenanceTickets = MaintenanceTicket::with(['property', 'user'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $cleaningTasks = CleaningTask::with(['property', 'cleaner'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $inspections = PropertyInspection::with(['property', 'lease', 'inspector'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $cleaners = User::whereIn('role', ['cleaner', 'receptionist', 'service_provider'])->get();

        return view('dashboards.property_manager', compact(
            'properties',
            'buildings',
            'units',
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'occupancyRate',
            'tenants',
            'guests',
            'rentSchedules',
            'maintenanceTickets',
            'cleaningTasks',
            'inspections',
            'cleaners'
        ));
    }

    /**
     * Store a multi-unit parent building / complex.
     */
    public function storeBuilding(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'property_category' => ['required', 'string', 'in:apartment,condominium,commercial,office,villa,home'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $building = Property::create([
            'user_id' => $user->id,
            'agent_id' => $user->agentProfile?->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'] ?? null,
            'price' => 0,
            'type' => 'rent',
            'property_category' => $validated['property_category'],
            'city' => $validated['city'],
            'subcity' => $validated['subcity'] ?? 'Bole',
            'address' => $validated['address'] ?? null,
            'floors' => $validated['floors'] ?? 1,
            'status' => 'available',
            'is_active' => true,
        ]);

        AuditLog::log(
            action: 'property_manager.building_created',
            entity: $building,
            reason: "Property Manager registered parent building \"{$building->title}\".",
            user: $user,
            request: $request
        );

        return back()->with('success', "Building '{$building->title}' registered to portfolio.");
    }

    /**
     * Store a managed unit (optionally attached to a parent building).
     */
    public function storeUnit(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessPropertyManagerDashboard()) {
            abort(403, 'Permission denied: Property Manager access required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'unit_number' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:properties,id'],
            'property_category' => ['required', 'string', 'in:apartment,condominium,villa,home,office,guest_house,hotel'],
            'type' => ['required', 'string', 'in:rent,sale,stay'],
            'price' => ['required', 'numeric', 'min:0'],
            'city' => ['required', 'string', 'max:100'],
            'subcity' => ['nullable', 'string', 'max:100'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance,turnover'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ]);

        if (! empty($validated['parent_id'])) {
            $parent = Property::findOrFail($validated['parent_id']);
            $this->authorizeManagedProperty($user, $parent);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('properties', 'public');
        }

        $property = Property::create([
            'user_id' => $user->id,
            'agent_id' => $user->agentProfile?->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'unit_number' => $validated['unit_number'] ?? null,
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
            'status' => $validated['status'],
            'image_path' => $imagePath,
            'is_active' => true,
            'condition' => 'excellent',
            'has_electricity' => true,
            'has_water' => true,
        ]);

        if ($imagePath) {
            $property->images()->create([
                'image_path' => $imagePath,
                'sort_order' => 1,
            ]);
        }

        AuditLog::log(
            action: 'property_manager.unit_created',
            entity: $property,
            reason: "Property Manager registered unit \"{$property->title}\".",
            user: $user,
            request: $request
        );

        return back()->with('success', "Unit '{$property->title}' added to managed portfolio successfully.");
    }

    /**
     * Update unit details.
     */
    public function updateUnit(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeManagedProperty($request->user(), $property);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'unit_number' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance,turnover,draft'],
            'property_category' => ['nullable', 'string', 'in:apartment,condominium,villa,home,office,guest_house,hotel'],
            'type' => ['nullable', 'string', 'in:rent,sale,stay'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ]);

        $updateData = [
            'title' => $validated['title'],
            'unit_number' => $validated['unit_number'] ?? $property->unit_number,
            'price' => $validated['price'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['property_category'])) {
            $updateData['property_category'] = $validated['property_category'];
        }
        if (! empty($validated['type'])) {
            $updateData['type'] = $validated['type'];
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
            entity: $property,
            reason: "Property Manager updated unit \"{$property->title}\".",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Unit '{$property->title}' updated successfully.");
    }

    /**
     * Delete a managed unit.
     */
    public function destroyUnit(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeManagedProperty($request->user(), $property);

        $title = $property->title;

        foreach ($property->images as $img) {
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
            $img->delete();
        }

        $property->delete();

        AuditLog::log(
            action: 'property_manager.unit_deleted',
            reason: "Property Manager deleted unit \"{$title}\".",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Unit '{$title}' removed from portfolio.");
    }

    /**
     * Quick transition of unit occupancy state.
     */
    public function transitionUnitStatus(Request $request, Property $property, string $status): RedirectResponse
    {
        $this->authorizeManagedProperty($request->user(), $property);

        if (! in_array($status, ['available', 'rented', 'maintenance', 'turnover'], true)) {
            abort(422, 'Invalid occupancy status.');
        }

        $property->update(['status' => $status]);

        AuditLog::log(
            action: 'property_manager.unit_status_transition',
            entity: $property,
            reason: "Unit \"{$property->title}\" status transitioned to {$status}.",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Unit status changed to {$status}.");
    }

    /**
     * Adjust tenant ledger / rent schedule balance (credit, discount, adjustment).
     */
    public function adjustRentSchedule(Request $request, RentSchedule $schedule): RedirectResponse
    {
        $lease = $schedule->lease;
        if (! $lease || ! $lease->property) {
            abort(404, 'Lease not found.');
        }

        $this->authorizeManagedProperty($request->user(), $lease->property);

        $validated = $request->validate([
            'new_amount_due' => ['required', 'numeric', 'min:0'],
            'adjustment_reason' => ['required', 'string', 'max:500'],
        ]);

        $oldAmount = (float) $schedule->amount_due;
        $newAmount = (float) $validated['new_amount_due'];
        $diff = $newAmount - $oldAmount;

        $schedule->update([
            'amount_due' => $newAmount,
            'notes' => ($schedule->notes ? $schedule->notes."\n" : '').
                "Balance adjusted by {$request->user()->name}: from ETB {$oldAmount} to ETB {$newAmount}. Reason: {$validated['adjustment_reason']}",
        ]);

        AuditLog::log(
            action: 'property_manager.rent_adjusted',
            entity: $schedule,
            reason: "Adjusted installment #{$schedule->installment_number} due amount by ETB {$diff}. Reason: {$validated['adjustment_reason']}",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Installment #{$schedule->installment_number} balance adjusted to ETB ".number_format($newAmount, 2));
    }

    /**
     * Escalate rent delinquency and dispatch formal notice.
     */
    public function escalateDelinquency(Request $request, RentSchedule $schedule): RedirectResponse
    {
        $lease = $schedule->lease;
        if (! $lease || ! $lease->property) {
            abort(404, 'Lease not found.');
        }

        $this->authorizeManagedProperty($request->user(), $lease->property);

        $validated = $request->validate([
            'notice_type' => ['required', 'string', 'in:friendly_reminder,first_notice,final_notice,legal_demand'],
            'notice_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $schedule->update([
            'status' => 'overdue',
            'notes' => ($schedule->notes ? $schedule->notes."\n" : '').
                'Delinquency notice ['.ucfirst(str_replace('_', ' ', $validated['notice_type'])).'] issued on '.now()->format('M d, Y').'. Note: '.($validated['notice_notes'] ?? 'None'),
        ]);

        AuditLog::log(
            action: 'property_manager.delinquency_escalated',
            entity: $schedule,
            reason: "Delinquency notice [{$validated['notice_type']}] issued to tenant {$lease->tenant_name} for installment #{$schedule->installment_number}.",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', 'Delinquency notice dispatched and logged to tenant ledger.');
    }

    /**
     * Record move-in, move-out, or routine condition inspection.
     */
    public function storeInspection(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeManagedProperty($request->user(), $property);

        $validated = $request->validate([
            'inspection_type' => ['required', 'string', 'in:move_in,move_out,routine,emergency'],
            'overall_condition' => ['required', 'string', 'in:excellent,good,fair,poor,damaged'],
            'inspection_date' => ['required', 'date'],
            'lease_id' => ['nullable', 'exists:leases,id'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'checklist' => ['nullable', 'array'],
        ]);

        $inspection = PropertyInspection::create([
            'property_id' => $property->id,
            'lease_id' => $validated['lease_id'] ?? null,
            'inspector_user_id' => $request->user()->id,
            'inspection_code' => 'INSP-'.strtoupper(Str::random(6)),
            'inspection_type' => $validated['inspection_type'],
            'inspection_date' => $validated['inspection_date'],
            'overall_condition' => $validated['overall_condition'],
            'checklist' => $validated['checklist'] ?? [
                'keys_and_locks' => 'pass',
                'walls_and_paint' => 'pass',
                'plumbing_and_taps' => 'pass',
                'electrical_and_lighting' => 'pass',
                'appliances' => 'pass',
            ],
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed',
        ]);

        AuditLog::log(
            action: 'property_manager.inspection_completed',
            entity: $inspection,
            reason: "Completed {$validated['inspection_type']} inspection for {$property->title}. Condition: {$validated['overall_condition']}.",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Inspection {$inspection->inspection_code} recorded successfully.");
    }

    /**
     * Dispatch maintenance ticket to contractor with quote/cost tracking.
     */
    public function assignMaintenanceContractor(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        if (! $ticket->property) {
            abort(404, 'Property not found.');
        }

        $this->authorizeManagedProperty($request->user(), $ticket->property);

        $validated = $request->validate([
            'assigned_contractor' => ['required', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'location_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $ticket->update([
            'status' => 'in_progress',
            'estimated_cost' => $validated['estimated_cost'] ?? $ticket->estimated_cost,
            'location_notes' => ($ticket->location_notes ? $ticket->location_notes."\n" : '').
                "Contractor: {$validated['assigned_contractor']}".($validated['estimated_cost'] ? " (Quote: ETB {$validated['estimated_cost']})" : ''),
        ]);

        AuditLog::log(
            action: 'property_manager.contractor_assigned',
            entity: $ticket,
            reason: "Assigned contractor {$validated['assigned_contractor']} to maintenance ticket #{$ticket->ticket_number}.",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} assigned to {$validated['assigned_contractor']}.");
    }

    /**
     * Complete and close maintenance ticket with final cost.
     */
    public function closeMaintenance(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        if (! $ticket->property) {
            abort(404, 'Property not found.');
        }

        $this->authorizeManagedProperty($request->user(), $ticket->property);

        $validated = $request->validate([
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        $ticket->update([
            'status' => 'closed',
            'actual_cost' => $validated['actual_cost'] ?? $ticket->actual_cost,
            'resolved_at' => now(),
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        AuditLog::log(
            action: 'property_manager.maintenance_closed',
            entity: $ticket,
            reason: "Closed maintenance ticket #{$ticket->ticket_number}. Final cost: ETB ".($validated['actual_cost'] ?? 0),
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} closed and signed off.");
    }

    /**
     * Dispatch housekeeping or turnover cleaning task.
     */
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

        $property = Property::findOrFail($validated['property_id']);
        $this->authorizeManagedProperty($user, $property);

        $task = CleaningTask::create([
            'property_id' => $property->id,
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
            entity: $task,
            reason: "Dispatched cleaning task {$task->task_code} for {$property->title}.",
            user: $user,
            request: $request
        );

        return back()->with('success', "Cleaning task '{$task->task_code}' dispatched.");
    }

    /**
     * Verify and complete cleaning task inspection.
     */
    public function completeCleaning(Request $request, CleaningTask $task): RedirectResponse
    {
        $property = $task->property;
        if (! $property) {
            abort(404, 'Property not found.');
        }

        $this->authorizeManagedProperty($request->user(), $property);

        $task->update([
            'status' => 'ready',
            'inspected_at' => now(),
            'ready_at' => now(),
            'inspected_by' => $request->user()->id,
            'notes' => ($task->notes ? $task->notes."\n" : '').'Inspected and verified ready for occupancy.',
        ]);

        AuditLog::log(
            action: 'property_manager.cleaning_inspected',
            entity: $task,
            reason: "Cleaning task {$task->task_code} inspected and marked ready.",
            user: $request->user(),
            request: $request
        );

        return back()->with('success', "Task {$task->task_code} inspected and unit verified ready.");
    }

    /**
     * Authorize that the current manager has rights to manage this property or unit.
     */
    protected function authorizeManagedProperty(User $user, Property $property): void
    {
        $isDirectOwner = (int) $property->user_id === (int) $user->id;
        $isAgent = $user->agentProfile && (int) $property->agent_id === (int) $user->agentProfile->id;
        $isParentOwner = $property->parent_id && (int) $property->parentBuilding?->user_id === (int) $user->id;

        if (! $isDirectOwner && ! $isAgent && ! $isParentOwner) {
            abort(403, 'Unauthorized: You do not manage this property.');
        }
    }
}
