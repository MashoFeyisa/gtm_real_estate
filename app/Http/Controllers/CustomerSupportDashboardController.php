<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MaintenanceTicket;
use App\Models\PropertyReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerSupportDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasSupportPerm = $user?->hasAnyPermission(['tickets.manage', 'tickets.close', 'users.assist']);

        if (! $user || (! in_array($user->role, ['customer_support', 'receptionist'], true) && ! $user->isAdmin() && ! $hasSupportPerm)) {
            abort(403, 'Permission denied: Customer Support desk access required.');
        }

        // 1. Support & maintenance tickets
        $tickets = MaintenanceTicket::query()
            ->with(['property', 'user'])
            ->latest()
            ->get();

        // 2. User Disputes & Inquiries
        $disputes = PropertyReport::query()
            ->with('property')
            ->latest()
            ->get();

        // 3. User lookup search (filtered, non-sensitive)
        $searchQuery = $request->input('q');
        $searchedUsers = collect();
        if ($searchQuery) {
            $searchedUsers = User::query()
                ->where('name', 'like', "%{$searchQuery}%")
                ->orWhere('email', 'like', "%{$searchQuery}%")
                ->orWhere('phone', 'like', "%{$searchQuery}%")
                ->select(['id', 'name', 'email', 'phone', 'role', 'status', 'created_at'])
                ->limit(10)
                ->get();
        }

        $stats = [
            'open_tickets' => $tickets->whereIn('status', ['open', 'pending'])->count(),
            'in_progress_tickets' => $tickets->where('status', 'in_progress')->count(),
            'resolved_tickets' => $tickets->where('status', 'resolved')->count(),
            'active_disputes' => $disputes->whereIn('status', ['pending', 'investigating'])->count(),
        ];

        return view('dashboards.customer_support', [
            'user' => $user,
            'stats' => $stats,
            'tickets' => $tickets,
            'disputes' => $disputes,
            'searchedUsers' => $searchedUsers,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function ticketReply(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! in_array($user->role, ['customer_support', 'receptionist'], true) && ! $user->isAdmin() && ! $user->hasPermission('tickets.manage'))) {
            abort(403, 'Permission denied: Customer Support desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,cancelled'],
            'response' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => $validated['status'],
            'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        AuditLog::log(
            user: $user,
            action: 'support.ticket_reply',
            entity: $ticket,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $ticket->status, 'response' => $validated['response'] ?? null],
            reason: 'Customer support agent updated ticket workflow',
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->id} updated to ".strtoupper($ticket->status).'.');
    }
}
