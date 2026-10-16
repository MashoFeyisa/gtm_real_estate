@extends('dashboards.layout')

@section('portal_name', 'Customer Support Desk')
@section('dashboard-title', 'Customer Support & Helpdesk')
@section('dashboard-subtitle', 'User support tickets, dispute resolution workflow (Dispute -> Evidence -> Review -> Resolution -> Closed), and customer assistance.')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['tickets.manage', 'tickets.create', 'tickets.close', 'tickets.view']))
        <x-sidebar-item href="#tickets" icon="🎫" label="User Tickets" :count="$stats['open_tickets']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['disputes.handle', 'disputes.view', 'disputes.resolve']))
        <x-sidebar-item href="#disputes" icon="⚖️" label="Dispute Resolution" :badge="$stats['active_disputes']" />
    @endif
    @if (auth()->user()->hasAnyPermission(['users.assist', 'users.lookup', 'users.view']))
        <x-sidebar-item href="#user-lookup" icon="🔍" label="User Assist" />
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Open Tickets</span>
                <div class="token-stat-value token-stat-danger">{{ $stats['open_tickets'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">In Progress</span>
                <div class="token-stat-value token-stat-info">{{ $stats['in_progress_tickets'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Escalated (Urgent)</span>
                <div class="token-stat-value token-stat-accent">{{ $stats['escalated_tickets'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Resolved / Closed</span>
                <div class="token-stat-value token-stat-success">{{ $stats['resolved_tickets'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Active Disputes</span>
                <div class="token-stat-value token-stat-warning">{{ $stats['active_disputes'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: User Tickets Desk -->
    <div id="tickets" data-section="tickets" class="section-panel hidden scroll-mt-6 space-y-6">
        <!-- Create Ticket Form -->
        @if (auth()->user()->hasAnyPermission(['tickets.create', 'tickets.manage']) || auth()->user()->isAdmin())
            <div class="token-card">
                <h3 class="text-base font-bold mb-1">🎫 Open New Customer Support Ticket</h3>
                <p class="text-xs token-muted mb-4">Log customer complaints, payment inquiries, maintenance emergencies, or general requests.</p>

                <form method="POST" action="{{ route('customer-support.tickets.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="token-label mb-1">Subject / Issue Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Telebirr payment callback failure ref: 891"
                            class="token-field">
                    </div>
                    <div>
                        <label class="token-label mb-1">Category *</label>
                        <select name="category" required class="token-field">
                            <option value="payment">Payment Issue</option>
                            <option value="technical">Technical Bug / Access</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="dispute">User Dispute</option>
                            <option value="general">General Support</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label mb-1">Priority *</label>
                        <select name="priority" required class="token-field">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent (Escalated)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="token-label mb-1">Assign To Agent (Optional)</label>
                        <select name="assigned_to" class="token-field">
                            <option value="">-- Unassigned (Triage Pool) --</option>
                            @foreach ($supportAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} ({{ strtoupper($agent->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="token-label mb-1">Associated Property (Optional)</label>
                        <select name="property_id" class="token-field">
                            <option value="">-- None / Platform-wide --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label class="token-label mb-1">Description & Customer Details *</label>
                        <textarea name="description" rows="3" required placeholder="Describe the customer issue, transaction reference, user contact phone, etc."
                            class="token-field"></textarea>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="token-btn token-btn-primary">
                            Create &amp; Dispatch Ticket
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Tickets List -->
        <div class="token-card">
            <h3 class="text-base font-bold mb-4">Active Support Queue</h3>
            <div class="space-y-4">
                @forelse($tickets as $ticket)
                    <div class="token-chart-tile space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--token-border-subtle)] pb-3">
                            <div>
                                <span class="font-bold text-sm">Ticket #{{ $ticket->ticket_number }} - {{ $ticket->title }}</span>
                                <div class="text-xs token-muted mt-0.5">
                                    <span>From: <strong>{{ $ticket->user->name ?? 'Guest/Anonymous' }}</strong></span>
                                    <span class="mx-1">•</span>
                                    <span>Category: <span class="capitalize font-semibold">{{ $ticket->category }}</span></span>
                                    @if ($ticket->property)
                                        <span class="mx-1">•</span>
                                        <span>Property: {{ $ticket->property->title }}</span>
                                    @endif
                                    <span class="mx-1">•</span>
                                    <span>Assigned: <strong>{{ $ticket->assignedAgent->name ?? 'Unassigned' }}</strong></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="token-badge uppercase
                                    @if($ticket->priority === 'urgent') token-badge-danger
                                    @elseif($ticket->priority === 'high') token-badge-warning
                                    @else token-badge-neutral @endif">
                                    {{ $ticket->priority }}
                                </span>
                                <span class="token-badge uppercase
                                    @if($ticket->status === 'resolved' || $ticket->status === 'closed') token-badge-success
                                    @elseif($ticket->status === 'in_progress') token-badge-info
                                    @elseif($ticket->status === 'assigned') token-badge-accent
                                    @else token-badge-warning @endif">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                        </div>

                        <p class="text-xs whitespace-pre-line">{{ $ticket->description }}</p>

                        @if ($ticket->resolution_notes)
                            <div class="token-alert token-alert-success text-xs">
                                <strong>Resolution Notes:</strong> {{ $ticket->resolution_notes }}
                            </div>
                        @endif

                        @if ($ticket->location_notes)
                            <div class="token-chart-tile token-muted text-[11px] font-mono">
                                {{ $ticket->location_notes }}
                            </div>
                        @endif

                        <!-- Workflow Actions Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <!-- Update Status & Reply -->
                            <form method="POST" action="{{ route('customer-support.tickets.reply', $ticket) }}" class="flex flex-wrap items-center gap-2 flex-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="token-field">
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="assigned" {{ $ticket->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <input type="text" name="response" placeholder="Reply note to customer..."
                                    class="flex-1 min-w-[200px] token-field">
                                <button type="submit" class="token-btn token-btn-primary">
                                    Update
                                </button>
                            </form>

                            <div class="flex items-center gap-2">
                                <!-- Reassign Agent -->
                                @if (auth()->user()->hasPermission('tickets.manage') || auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('customer-support.tickets.assign', $ticket) }}" class="flex items-center gap-1">
                                        @csrf
                                        <select name="assigned_to" class="token-field">
                                            @foreach ($supportAgents as $agent)
                                                <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                    Assign: {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="token-btn token-btn-secondary">
                                            Assign
                                        </button>
                                    </form>
                                @endif

                                <!-- Escalate -->
                                @if ($ticket->priority !== 'urgent' && (auth()->user()->hasAnyPermission(['tickets.manage', 'technical_support.escalate', 'payment_issues.escalate']) || auth()->user()->isAdmin()))
                                    <form method="POST" action="{{ route('customer-support.tickets.escalate', $ticket) }}">
                                        @csrf
                                        <button type="submit" class="token-btn token-btn-primary">
                                            ⚡ Escalate
                                        </button>
                                    </form>
                                @endif

                                <!-- Close Ticket -->
                                @if ($ticket->status !== 'closed' && (auth()->user()->hasAnyPermission(['tickets.close', 'tickets.manage']) || auth()->user()->isAdmin()))
                                    <form method="POST" action="{{ route('customer-support.tickets.close', $ticket) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Close Ticket #{{ $ticket->ticket_number }}?');"
                                            class="token-btn token-btn-primary">
                                            ✓ Close
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="token-muted text-xs py-8 text-center">No customer tickets currently open.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section 2: Dispute Resolution Pipeline -->
    <div id="disputes" data-section="disputes" class="section-panel hidden scroll-mt-6 space-y-6">
        <!-- Pipeline Overview Card -->
        <div class="token-card">
            <h3 class="text-base font-bold mb-2">⚖️ Dispute Resolution State Machine (SRS Section 5)</h3>
            <p class="text-xs token-muted mb-4">Every user dispute progresses sequentially through 5 auditable stages:</p>
            <div class="grid grid-cols-5 gap-2 text-center text-xs font-bold">
                <div class="token-chart-tile">
                    <span class="block text-base">1️⃣</span>
                    <span>Dispute</span>
                    <span class="block text-[10px] font-normal text-[var(--token-warning)] mt-1">Case Filed</span>
                </div>
                <div class="token-chart-tile">
                    <span class="block text-base">2️⃣</span>
                    <span>Evidence</span>
                    <span class="block text-[10px] font-normal text-[var(--token-info)] mt-1">Docs &amp; Logs</span>
                </div>
                <div class="token-chart-tile">
                    <span class="block text-base">3️⃣</span>
                    <span>Review</span>
                    <span class="block text-[10px] font-normal text-[var(--token-accent-stat)] mt-1">Officer Assessment</span>
                </div>
                <div class="token-chart-tile">
                    <span class="block text-base">4️⃣</span>
                    <span>Resolution</span>
                    <span class="block text-[10px] font-normal text-[var(--token-accent-stat)] mt-1">Ruling Made</span>
                </div>
                <div class="token-chart-tile">
                    <span class="block text-base">5️⃣</span>
                    <span>Closed</span>
                    <span class="block text-[10px] font-normal text-[var(--token-success)] mt-1">Settled &amp; Archived</span>
                </div>
            </div>
        </div>

        <!-- Initiate Dispute Form -->
        @if (auth()->user()->hasAnyPermission(['disputes.create', 'disputes.handle']) || auth()->user()->isAdmin())
            <div class="token-card">
                <h3 class="text-base font-bold mb-1">Initiate User Dispute Case</h3>
                <p class="text-xs token-muted mb-4">Open an official dispute docket between platform parties (e.g. Host vs Guest, Landlord vs Tenant).</p>

                <form method="POST" action="{{ route('customer-support.disputes.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="token-label mb-1">Dispute Title / Claim *</label>
                        <input type="text" name="title" required placeholder="e.g. Security deposit withholding disagreement for Bole Apartment"
                            class="token-field">
                    </div>
                    <div>
                        <label class="token-label mb-1">Property Involved (Optional)</label>
                        <select name="property_id" class="token-field">
                            <option value="">-- None --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="token-label mb-1">Description of Dispute & Claimed Amount *</label>
                        <textarea name="description" rows="3" required placeholder="Detailed account of what transpired, communications held, and settlement sought..."
                            class="token-field"></textarea>
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="token-btn token-btn-primary">
                            Open Dispute Docket
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Dispute Docket Table -->
        <div class="token-card">
            <h3 class="text-base font-bold mb-4">Dispute Dockets</h3>
            <div class="space-y-4">
                @forelse($disputes as $dispute)
                    <div class="token-chart-tile space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--token-border-subtle)] pb-3">
                            <div>
                                <span class="font-bold text-sm">Case #{{ $dispute->dispute_code }} - {{ $dispute->title }}</span>
                                <div class="text-xs token-muted mt-0.5">
                                    <span>Complainant: <strong>{{ $dispute->user->name ?? 'User' }}</strong></span>
                                    @if ($dispute->respondent)
                                        <span class="mx-1">•</span>
                                        <span>Respondent: <strong>{{ $dispute->respondent->name }}</strong></span>
                                    @endif
                                    @if ($dispute->property)
                                        <span class="mx-1">•</span>
                                        <span>Property: {{ $dispute->property->title }}</span>
                                    @endif
                                    @if ($dispute->resolver)
                                        <span class="mx-1">•</span>
                                        <span>Resolved By: <strong>{{ $dispute->resolver->name }}</strong></span>
                                    @endif
                                </div>
                            </div>
                            <span class="token-badge uppercase
                                @if($dispute->stage === 'closed') token-badge-neutral
                                @elseif($dispute->stage === 'resolution') token-badge-success
                                @elseif($dispute->stage === 'review') token-badge-accent
                                @elseif($dispute->stage === 'evidence') token-badge-info
                                @else token-badge-warning @endif">
                                Stage: {{ $dispute->stage }}
                            </span>
                        </div>

                        <p class="text-xs whitespace-pre-line">{{ $dispute->description }}</p>

                        @if ($dispute->evidence_notes)
                            <div class="token-alert token-alert-info text-xs">
                                <strong>Evidence &amp; Statements:</strong> {{ $dispute->evidence_notes }}
                            </div>
                        @endif

                        @if ($dispute->resolution_notes)
                            <div class="token-alert token-alert-success text-xs">
                                <strong>Official Ruling / Resolution:</strong> {{ $dispute->resolution_notes }}
                                <span class="block text-[10px] text-[var(--token-success)] mt-1">Signed by {{ $dispute->resolver->name ?? 'Officer' }} at {{ $dispute->resolved_at?->format('M d, Y H:i') }}</span>
                            </div>
                        @endif

                        <!-- Dispute Stage Progression Form -->
                        @if ($dispute->stage !== 'closed')
                            <form method="POST" action="{{ route('customer-support.disputes.stage', $dispute) }}" class="pt-2 border-t border-[var(--token-border-subtle)] space-y-3">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="token-label">Advance Stage To:</label>
                                        <select name="stage" class="token-field">
                                            <option value="evidence" {{ $dispute->stage === 'evidence' ? 'selected' : '' }}>2️⃣ Evidence Gathering</option>
                                            <option value="review" {{ $dispute->stage === 'review' ? 'selected' : '' }}>3️⃣ Officer Review</option>
                                            @if (auth()->user()->hasPermission('disputes.resolve') || auth()->user()->isAdmin())
                                                <option value="resolution" {{ $dispute->stage === 'resolution' ? 'selected' : '' }}>4️⃣ Official Resolution</option>
                                                <option value="closed" {{ $dispute->stage === 'closed' ? 'selected' : '' }}>5️⃣ Close Docket</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="token-label">Evidence Notes (If any):</label>
                                        <input type="text" name="evidence_notes" placeholder="Receipts verified, call logs..."
                                            class="token-field">
                                    </div>
                                    <div>
                                        <label class="token-label">Resolution Ruling (For Resolution/Closed):</label>
                                        <input type="text" name="resolution_notes" placeholder="E.g. Full refund authorized to tenant..."
                                            class="token-field">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="token-btn token-btn-primary">
                                        Update Dispute State
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="token-muted text-xs py-8 text-center">No dispute dockets on file.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section 3: Non-sensitive User Lookup & Assistance Tool -->
    <div id="user-lookup" data-section="user-lookup" class="section-panel hidden scroll-mt-6 token-card mb-8 space-y-6">
        <div>
            <h3 class="text-base font-bold mb-1">🔍 User Lookup &amp; Assist Desk</h3>
            <p class="text-xs token-muted">Quickly check customer account status, verified contact phone, and provide customer support assistance (Password Reset, Email Verification, Account Unsuspend).</p>
        </div>

        <form method="GET" action="{{ route('customer-support.dashboard') }}" class="flex items-center gap-3">
            <input type="text" name="q" placeholder="Search customer by name, email, or Ethiopian phone number..." value="{{ $searchQuery }}"
                class="flex-1 token-field">
            <button type="submit" class="token-btn token-btn-primary">
                Search Customer
            </button>
        </form>

        @if($searchedUsers->isNotEmpty())
            <div class="token-table-wrap">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th class="text-right">Assist Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($searchedUsers as $customer)
                            <tr>
                                <td class="font-bold">
                                    {{ $customer->name }}
                                    @if ($customer->is_warned)
                                        <span class="token-badge token-badge-danger !text-[9px] uppercase ml-1">Warned</span>
                                    @endif
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                                <td class="capitalize">{{ $customer->role }}</td>
                                <td>
                                    <span class="token-badge {{ $customer->status === 'active' ? 'token-badge-success' : 'token-badge-danger' }}">
                                        {{ strtoupper($customer->status ?? 'active') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($customer->email_verified_at)
                                        <span class="font-bold text-[var(--token-success)]">✓ Yes</span>
                                    @else
                                        <span class="font-bold text-[var(--token-warning)]">Pending</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if (auth()->user()->hasPermission('users.assist') || auth()->user()->isAdmin())
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Password Reset Link -->
                                            <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="reset_password">
                                                <button type="submit" title="Generate Password Reset Link" class="token-btn token-btn-secondary">
                                                    🔑 Reset Pwd
                                                </button>
                                            </form>

                                            <!-- Email Verification -->
                                            @if (! $customer->email_verified_at)
                                                <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="verify_email">
                                                    <button type="submit" title="Manually Verify Email" class="token-btn token-btn-primary">
                                                        ✉️ Verify
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Clear Warning -->
                                            @if ($customer->is_warned)
                                                <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="clear_warning">
                                                    <button type="submit" title="Clear Warning" class="token-btn token-btn-secondary">
                                                        🛡️ Unwarn
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Toggle Status -->
                                            <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="toggle_status">
                                                <button type="submit" class="{{ $customer->status === 'active' ? 'token-btn token-btn-danger' : 'token-btn token-btn-primary' }}">
                                                    {{ $customer->status === 'active' ? 'Suspend' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="token-muted text-[10px]">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($searchQuery)
            <div class="text-xs token-muted py-4 text-center">No matching accounts found for '{{ $searchQuery }}'.</div>
        @endif
    </div>
@endsection
