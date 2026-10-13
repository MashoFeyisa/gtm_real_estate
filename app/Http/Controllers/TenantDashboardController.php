<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\PropertyDocument;
use App\Models\RentSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasTenantPerm = $user?->hasAnyPermission(['agreements.view', 'rent.view', 'leases.manage', 'leases.terminate', 'leases.view', 'rent.manage', 'rent.track']);

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['tenant', 'renter'], true) && ! $user?->isAdmin() && ! $hasTenantPerm)) {
            abort(403, 'Permission denied: Tenant agreement access required.');
        }

        $leases = Lease::with(['property.images', 'landlord', 'agent.user', 'schedules' => fn ($q) => $q->orderBy('installment_number')])
            ->where(function ($q) use ($user) {
                $q->where('tenant_user_id', $user->id)
                    ->orWhere('tenant_email', $user->email);
            })
            ->latest()
            ->get();

        $leaseIds = $leases->pluck('id')->all();
        $propertyIds = $leases->pluck('property_id')->filter()->all();

        $rentSchedules = RentSchedule::with('lease.property')
            ->whereIn('lease_id', $leaseIds)
            ->orderBy('due_date')
            ->get();

        $maintenanceTickets = MaintenanceTicket::with('property')
            ->where(function ($q) use ($user, $propertyIds) {
                $q->where('user_id', $user->id)
                    ->orWhereIn('property_id', $propertyIds);
            })
            ->latest()
            ->get();

        $documents = PropertyDocument::where(function ($q) use ($user, $propertyIds) {
            $q->where('user_id', $user->id)
                ->orWhereIn('property_id', $propertyIds);
        })->latest()->get();

        return view('dashboards.tenant', compact(
            'leases',
            'rentSchedules',
            'maintenanceTickets',
            'documents'
        ));
    }

    public function submitMaintenance(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,urgent,emergency'],
        ]);

        $ticket = MaintenanceTicket::create([
            'property_id' => $validated['property_id'],
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        AuditLog::log(
            action: 'tenant.maintenance_ticket_submitted',
            entityType: 'MaintenanceTicket',
            entityId: $ticket->id,
            newValue: $ticket->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Maintenance request '{$ticket->title}' submitted.");
    }
}
