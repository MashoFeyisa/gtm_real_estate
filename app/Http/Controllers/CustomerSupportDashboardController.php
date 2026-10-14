<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\User;
use App\Models\UserDispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class CustomerSupportDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasSupportPerm = $user?->hasAnyPermission([
            'tickets.manage',
            'tickets.close',
            'tickets.view',
            'disputes.view',
            'disputes.handle',
            'users.assist',
        ]);

        if (! $user || (! in_array($user->role, ['customer_support', 'receptionist'], true) && ! $user->isAdmin() && ! $hasSupportPerm)) {
            abort(403, 'Permission denied: Customer Support desk access required.');
        }

        // 1. Support & maintenance tickets
        $tickets = MaintenanceTicket::query()
            ->with(['property', 'user', 'assignedAgent'])
            ->latest()
            ->get();

        // 2. User Disputes (Full 5-stage pipeline)
        $disputes = UserDispute::query()
            ->with(['user', 'respondent', 'property', 'booking', 'resolver'])
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
                ->select(['id', 'name', 'email', 'phone', 'role', 'status', 'email_verified_at', 'is_warned', 'created_at'])
                ->limit(10)
                ->get();
        }

        // Support agents for ticket assignment
        $supportAgents = User::query()
            ->whereIn('role', ['customer_support', 'admin', 'receptionist', 'property_manager'])
            ->select(['id', 'name', 'role'])
            ->get();

        $properties = Property::query()->select(['id', 'title'])->limit(50)->get();

        $stats = [
            'open_tickets' => $tickets->whereIn('status', ['open', 'pending'])->count(),
            'in_progress_tickets' => $tickets->where('status', 'in_progress')->count(),
            'resolved_tickets' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
            'active_disputes' => $disputes->whereNotIn('stage', ['closed'])->count(),
            'escalated_tickets' => $tickets->where('priority', 'urgent')->count(),
        ];

        return view('dashboards.customer_support', [
            'user' => $user,
            'stats' => $stats,
            'tickets' => $tickets,
            'disputes' => $disputes,
            'searchedUsers' => $searchedUsers,
            'searchQuery' => $searchQuery,
            'supportAgents' => $supportAgents,
            'properties' => $properties,
        ]);
    }

    public function storeTicket(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyPermission(['tickets.create', 'tickets.manage']) && ! $user->isAdmin())) {
            abort(403, 'Permission denied: tickets.create or tickets.manage required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:maintenance,payment,technical,dispute,general'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'description' => ['required', 'string', 'max:5000'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $ticket = MaintenanceTicket::create([
            'user_id' => $user->id,
            'property_id' => $validated['property_id'] ?? null,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => ! empty($validated['assigned_to']) ? 'assigned' : 'open',
            'description' => $validated['description'],
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        AuditLog::log(
            user: $user,
            action: 'support.ticket_create',
            entity: $ticket,
            oldValue: null,
            newValue: $ticket->toArray(),
            reason: 'Customer support agent opened new ticket #'.$ticket->ticket_number,
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} created successfully.");
    }

    public function ticketReply(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyPermission(['tickets.update', 'tickets.manage']) && ! $user->isAdmin())) {
            abort(403, 'Permission denied: tickets.update or tickets.manage required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,assigned,in_progress,resolved,cancelled,closed'],
            'response' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => $validated['status'],
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null,
            'resolution_notes' => $validated['response'] ?? $ticket->resolution_notes,
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

        return back()->with('success', "Ticket #{$ticket->ticket_number} updated to ".strtoupper($ticket->status).'.');
    }

    public function assignTicket(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasPermission('tickets.manage') && ! $user->isAdmin())) {
            abort(403, 'Permission denied: tickets.manage required to assign tickets.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $oldAssigned = $ticket->assigned_to;
        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $ticket->status === 'open' ? 'assigned' : $ticket->status,
        ]);

        $assignedUser = User::find($validated['assigned_to']);

        AuditLog::log(
            user: $user,
            action: 'support.ticket_assign',
            entity: $ticket,
            oldValue: ['assigned_to' => $oldAssigned],
            newValue: ['assigned_to' => $ticket->assigned_to],
            reason: "Ticket reassigned to {$assignedUser?->name}",
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} assigned to {$assignedUser?->name}.");
    }

    public function escalateTicket(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyPermission(['tickets.manage', 'technical_support.escalate', 'payment_issues.escalate']) && ! $user->isAdmin())) {
            abort(403, 'Permission denied: escalation privileges required.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldPriority = $ticket->priority;
        $ticket->update([
            'priority' => 'urgent',
            'location_notes' => trim(($ticket->location_notes ? $ticket->location_notes."\n" : '').'[ESCALATED]: '.($validated['notes'] ?? 'Priority raised to Urgent by Support Desk')),
        ]);

        AuditLog::log(
            user: $user,
            action: 'support.ticket_escalate',
            entity: $ticket,
            oldValue: ['priority' => $oldPriority],
            newValue: ['priority' => 'urgent', 'notes' => $validated['notes'] ?? null],
            reason: 'Ticket escalated to Urgent priority',
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} has been escalated to URGENT priority.");
    }

    public function closeTicket(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyPermission(['tickets.close', 'tickets.manage']) && ! $user->isAdmin())) {
            abort(403, 'Permission denied: tickets.close required.');
        }

        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => 'closed',
            'resolved_at' => now(),
            'resolution_notes' => $validated['resolution_notes'] ?? $ticket->resolution_notes,
        ]);

        AuditLog::log(
            user: $user,
            action: 'support.ticket_close',
            entity: $ticket,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'closed', 'resolved_at' => $ticket->resolved_at],
            reason: 'Ticket marked CLOSED by support officer',
            request: $request
        );

        return back()->with('success', "Ticket #{$ticket->ticket_number} closed successfully.");
    }

    public function storeDispute(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasAnyPermission(['disputes.create', 'disputes.handle']) && ! $user->isAdmin())) {
            abort(403, 'Permission denied: disputes.create required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'respondent_id' => ['nullable', 'exists:users,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $dispute = UserDispute::create([
            'user_id' => $user->id,
            'respondent_id' => $validated['respondent_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'stage' => 'dispute',
        ]);

        AuditLog::log(
            user: $user,
            action: 'support.dispute_create',
            entity: $dispute,
            oldValue: null,
            newValue: $dispute->toArray(),
            reason: "User dispute #{$dispute->dispute_code} filed",
            request: $request
        );

        return back()->with('success', "Dispute case #{$dispute->dispute_code} initiated.");
    }

    public function updateDisputeStage(Request $request, UserDispute $dispute): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'stage' => ['required', 'string', 'in:dispute,evidence,review,resolution,closed'],
            'evidence_notes' => ['nullable', 'string', 'max:3000'],
            'resolution_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $targetStage = $validated['stage'];

        // Enforce resolution permission when advancing to resolution or closed
        if (in_array($targetStage, ['resolution', 'closed'], true)) {
            if (! $user || (! $user->hasPermission('disputes.resolve') && ! $user->isAdmin())) {
                abort(403, 'Permission denied: disputes.resolve required to resolve or close disputes.');
            }
        } else {
            if (! $user || (! $user->hasAnyPermission(['disputes.update', 'disputes.handle']) && ! $user->isAdmin())) {
                abort(403, 'Permission denied: disputes.handle required to update dispute stages.');
            }
        }

        $oldStage = $dispute->stage;
        $updates = ['stage' => $targetStage];

        if (! empty($validated['evidence_notes'])) {
            $updates['evidence_notes'] = $validated['evidence_notes'];
        }

        if (! empty($validated['resolution_notes'])) {
            $updates['resolution_notes'] = $validated['resolution_notes'];
        }

        if (in_array($targetStage, ['resolution', 'closed'], true)) {
            $updates['resolved_by'] = $user->id;
            $updates['resolved_at'] = now();
        }

        $dispute->update($updates);

        AuditLog::log(
            user: $user,
            action: 'support.dispute_stage_update',
            entity: $dispute,
            oldValue: ['stage' => $oldStage],
            newValue: ['stage' => $targetStage, 'resolved_by' => $updates['resolved_by'] ?? null],
            reason: "Dispute stage moved from {$oldStage} to {$targetStage}",
            request: $request
        );

        return back()->with('success', "Dispute #{$dispute->dispute_code} advanced to stage: ".strtoupper($targetStage).'.');
    }

    public function assistUser(Request $request, User $targetUser): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->hasPermission('users.assist') && ! $user->isAdmin())) {
            abort(403, 'Permission denied: users.assist required.');
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:verify_email,clear_warning,toggle_status,reset_password'],
        ]);

        $action = $validated['action'];
        $details = '';

        switch ($action) {
            case 'verify_email':
                $targetUser->forceFill(['email_verified_at' => now()])->save();
                $details = 'Email manually marked as verified';
                break;

            case 'clear_warning':
                $targetUser->update(['is_warned' => false, 'warning_notes' => null]);
                $details = 'Account warning flag cleared';
                break;

            case 'toggle_status':
                $newStatus = $targetUser->status === 'active' ? 'suspended' : 'active';
                $targetUser->update(['status' => $newStatus]);
                $details = "Account status changed to {$newStatus}";
                break;

            case 'reset_password':
                $token = Password::broker()->createToken($targetUser);
                $details = "Password reset token generated: {$token}";
                break;
        }

        AuditLog::log(
            user: $user,
            action: 'support.user_assist_'.$action,
            entity: $targetUser,
            oldValue: null,
            newValue: ['action' => $action, 'details' => $details],
            reason: "Customer support assisted user {$targetUser->name} ({$targetUser->email}): {$details}",
            request: $request
        );

        return back()->with('success', "Assistance action completed for {$targetUser->name}: {$details}.");
    }
}
