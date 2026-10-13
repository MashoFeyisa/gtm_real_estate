<?php

namespace App\Http\Controllers;

use App\Models\GuestServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Housekeeping queue for the property manager desk: guest service requests
 * move through the pending -> in_progress -> completed / cancelled flow.
 */
class HousekeepingController extends Controller
{
    public function updateStatus(Request $request, GuestServiceRequest $guestServiceRequest): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'response_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $guestServiceRequest->update([
            'status' => $validated['status'],
            'response_notes' => $validated['response_notes'] ?? $guestServiceRequest->response_notes,
            'handled_by' => auth()->id(),
        ]);

        return back()->with('success', 'Service request status updated to '.ucwords(str_replace('_', ' ', $validated['status'])).'.');
    }

    private function authorizeManager(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->isAdmin()
                || ($user->role === 'property_manager' && $user->hasPermission('housekeeping.manage'))
                || ($user->role === 'service_provider' && $user->hasPermission('services.jobs_receive'))
            ),
            403
        );
    }
}
