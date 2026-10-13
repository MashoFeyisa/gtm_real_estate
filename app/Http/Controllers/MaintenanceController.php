<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Submit a new maintenance or housekeeping ticket for a property.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:plumbing,electrical,internet,appliance,structural,cleaning,other'],
            'priority' => ['required', 'string', 'in:low,medium,high,emergency'],
            'description' => ['required', 'string', 'max:3000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $ticket = $property->maintenanceTickets()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'description' => $validated['description'],
            'location_notes' => $validated['location_notes'] ?? null,
            'scheduled_at' => ! empty($validated['scheduled_at']) ? $validated['scheduled_at'] : null,
        ]);

        return back()->with('success', 'Maintenance request #'.$ticket->ticket_number.' submitted successfully.');
    }

    /**
     * Submit a maintenance ticket from user dashboard.
     * Landlords and property managers holding maintenance.manage may file
     * tickets too; landlords only for properties tied to their leases.
     */
    public function storeGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'category' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ]);

        $propertyId = $validated['property_id'] ?? Property::value('id');

        $this->validatePropertyOwnership($propertyId);

        $ticket = MaintenanceTicket::create([
            'user_id' => auth()->id(),
            'property_id' => $propertyId,
            'title' => $validated['title'],
            'category' => $validated['category'] ?? 'plumbing',
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Maintenance request #'.$ticket->ticket_number.' submitted successfully.');
    }

    /**
     * Update maintenance ticket status and assignment.
     */
    public function updateStatus(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $this->authorizeManager($ticket);

        $validated = $request->validate([
            'status' => ['required', 'in:open,assigned,in_progress,completed,closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $resolvedAt = in_array($validated['status'], ['completed', 'closed'], true) ? ($ticket->resolved_at ?: now()) : null;

        $ticket->update([
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? $ticket->assigned_to,
            'scheduled_at' => ! empty($validated['scheduled_at']) ? $validated['scheduled_at'] : $ticket->scheduled_at,
            'resolution_notes' => $validated['resolution_notes'] ?? $ticket->resolution_notes,
            'resolved_at' => $resolvedAt,
        ]);

        return back()->with('success', 'Maintenance ticket #'.$ticket->ticket_number.' updated successfully.');
    }

    protected function authorizeManager(?MaintenanceTicket $ticket = null): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->isAdmin() || $user->agentProfile) {
            return;
        }

        if (in_array($user->role, ['landlord', 'property_manager'], true) && $user->hasPermission('maintenance.manage')) {
            if ($user->role === 'property_manager') {
                return;
            }

            // Landlords manage maintenance only on properties tied to their leases.
            abort_unless(
                $ticket && Lease::query()
                    ->where('landlord_user_id', $user->id)
                    ->where('property_id', $ticket->property_id)
                    ->exists(),
                403
            );

            return;
        }

        abort(403, 'Unauthorized access to maintenance desk.');
    }

    /**
     * Landlords may only file or manage tickets on properties from their leases.
     */
    protected function validatePropertyOwnership(?int $propertyId): void
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin() || $user->agentProfile || $user->role === 'property_manager') {
            return;
        }

        if ($user->role === 'landlord' && $user->hasPermission('maintenance.manage')) {
            abort_unless(
                $propertyId && Lease::query()
                    ->where('landlord_user_id', $user->id)
                    ->where('property_id', $propertyId)
                    ->exists(),
                403
            );
        }
    }
}
