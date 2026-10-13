<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Inquiry triage shared by the customer support and property owner desks
 * (SRS Inquiry System statuses).
 */
class InquiryController extends Controller
{
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $user = auth()->user();

        $ownsInquiry = $user && $user->agentProfile && $inquiry->agent_id === $user->agentProfile->id;

        $canTriage = $user && (
            $user->isAdmin()
            || ($user->role === 'customer_support' && $user->hasPermission('tickets.manage'))
            || ($user->role === 'property_owner' && $user->hasPermission('inquiries.update'))
            || ($ownsInquiry && $user->hasPermission('leads.manage'))
        );

        abort_unless($canTriage, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,viewing,negotiating,offer,closed,lost'],
        ]);

        $inquiry->update([
            'status' => $validated['status'],
            'read_at' => $inquiry->read_at ?? now(),
        ]);

        return back()->with('success', 'Inquiry status updated to '.ucfirst($validated['status']).'.');
    }
}
