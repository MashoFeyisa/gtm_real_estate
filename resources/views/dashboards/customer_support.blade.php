@extends('dashboards.layout')

@section('portal_name', 'Customer Support Desk')
@section('dashboard-title', 'Customer Support & Helpdesk')
@section('dashboard-subtitle', 'User support tickets, dispute resolution workflow (Dispute -> Evidence -> Review -> Resolution -> Closed), and customer assistance.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['tickets.manage', 'tickets.create', 'tickets.close', 'tickets.view']))
        <a href="#tickets" data-target-section="tickets" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🎫</span>
                <span>User Tickets</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['open_tickets'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['disputes.handle', 'disputes.view', 'disputes.resolve']))
        <a href="#disputes" data-target-section="disputes" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>⚖️</span>
                <span>Dispute Resolution</span>
            </span>
            <span class="rounded-full bg-amber-500/30 text-amber-200 px-2 py-0.5 text-[10px] font-black uppercase">{{ $stats['active_disputes'] }}</span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['users.assist', 'users.lookup', 'users.view']))
        <a href="#user-lookup" data-target-section="user-lookup" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔍</span>
                <span>User Assist</span>
            </span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Open Tickets</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['open_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">In Progress</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['in_progress_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Escalated (Urgent)</span>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['escalated_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Resolved / Closed</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['resolved_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Active Disputes</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['active_disputes'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: User Tickets Desk -->
    <div id="tickets" data-section="tickets" class="section-panel hidden scroll-mt-6 space-y-6">
        <!-- Create Ticket Form -->
        @if (auth()->user()->hasAnyPermission(['tickets.create', 'tickets.manage']) || auth()->user()->isAdmin())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">🎫 Open New Customer Support Ticket</h3>
                <p class="text-xs text-slate-500 mb-4">Log customer complaints, payment inquiries, maintenance emergencies, or general requests.</p>
                
                <form method="POST" action="{{ route('customer-support.tickets.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Subject / Issue Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Telebirr payment callback failure #891"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Category *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <option value="payment">Payment Issue</option>
                            <option value="technical">Technical Bug / Access</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="dispute">User Dispute</option>
                            <option value="general">General Support</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Priority *</label>
                        <select name="priority" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent (Escalated)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Assign To Agent (Optional)</label>
                        <select name="assigned_to" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <option value="">-- Unassigned (Triage Pool) --</option>
                            @foreach ($supportAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} ({{ strtoupper($agent->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Associated Property (Optional)</label>
                        <select name="property_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <option value="">-- None / Platform-wide --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Description & Customer Details *</label>
                        <textarea name="description" rows="3" required placeholder="Describe the customer issue, transaction reference, user contact phone, etc."
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900"></textarea>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-[#1d3c34] hover:bg-[#254d43] text-white font-bold text-xs rounded-xl transition">
                            Create &amp; Dispatch Ticket
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Tickets List -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Active Support Queue</h3>
            <div class="space-y-4">
                @forelse($tickets as $ticket)
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div>
                                <span class="font-bold text-sm text-slate-900 dark:text-white">Ticket #{{ $ticket->ticket_number }} - {{ $ticket->title }}</span>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    <span>From: <strong>{{ $ticket->user->name ?? 'Guest/Anonymous' }}</strong></span>
                                    <span class="mx-1">•</span>
                                    <span>Category: <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ $ticket->category }}</span></span>
                                    @if ($ticket->property)
                                        <span class="mx-1">•</span>
                                        <span>Property: {{ $ticket->property->title }}</span>
                                    @endif
                                    <span class="mx-1">•</span>
                                    <span>Assigned: <strong>{{ $ticket->assignedAgent->name ?? 'Unassigned' }}</strong></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    @if($ticket->priority === 'urgent') bg-rose-100 text-rose-800 border border-rose-300
                                    @elseif($ticket->priority === 'high') bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $ticket->priority }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    @if($ticket->status === 'resolved' || $ticket->status === 'closed') bg-emerald-100 text-emerald-800
                                    @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($ticket->status === 'assigned') bg-purple-100 text-purple-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $ticket->description }}</p>

                        @if ($ticket->resolution_notes)
                            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs text-emerald-900 dark:text-emerald-200">
                                <strong>Resolution Notes:</strong> {{ $ticket->resolution_notes }}
                            </div>
                        @endif

                        @if ($ticket->location_notes)
                            <div class="p-2.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-[11px] text-slate-500 font-mono">
                                {{ $ticket->location_notes }}
                            </div>
                        @endif

                        <!-- Workflow Actions Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <!-- Update Status & Reply -->
                            <form method="POST" action="{{ route('customer-support.tickets.reply', $ticket) }}" class="flex flex-wrap items-center gap-2 flex-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="assigned" {{ $ticket->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <input type="text" name="response" placeholder="Reply note to customer..."
                                    class="flex-1 min-w-[200px] text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition">
                                    Update
                                </button>
                            </form>

                            <div class="flex items-center gap-2">
                                <!-- Reassign Agent -->
                                @if (auth()->user()->hasPermission('tickets.manage') || auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('customer-support.tickets.assign', $ticket) }}" class="flex items-center gap-1">
                                        @csrf
                                        <select name="assigned_to" class="text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                            @foreach ($supportAgents as $agent)
                                                <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                    Assign: {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-xl text-xs font-semibold">
                                            Assign
                                        </button>
                                    </form>
                                @endif

                                <!-- Escalate -->
                                @if ($ticket->priority !== 'urgent' && (auth()->user()->hasAnyPermission(['tickets.manage', 'technical_support.escalate', 'payment_issues.escalate']) || auth()->user()->isAdmin()))
                                    <form method="POST" action="{{ route('customer-support.tickets.escalate', $ticket) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition">
                                            ⚡ Escalate
                                        </button>
                                    </form>
                                @endif

                                <!-- Close Ticket -->
                                @if ($ticket->status !== 'closed' && (auth()->user()->hasAnyPermission(['tickets.close', 'tickets.manage']) || auth()->user()->isAdmin()))
                                    <form method="POST" action="{{ route('customer-support.tickets.close', $ticket) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Close Ticket #{{ $ticket->ticket_number }}?');"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition">
                                            ✓ Close
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-slate-400 text-xs py-8 text-center">No customer tickets currently open.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section 2: Dispute Resolution Pipeline -->
    <div id="disputes" data-section="disputes" class="section-panel hidden scroll-mt-6 space-y-6">
        <!-- Pipeline Overview Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">⚖️ Dispute Resolution State Machine (SRS Section 5)</h3>
            <p class="text-xs text-slate-500 mb-4">Every user dispute progresses sequentially through 5 auditable stages:</p>
            <div class="grid grid-cols-5 gap-2 text-center text-xs font-bold">
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                    <span class="block text-base">1️⃣</span>
                    <span>Dispute</span>
                    <span class="block text-[10px] font-normal text-amber-700 mt-1">Case Filed</span>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900">
                    <span class="block text-base">2️⃣</span>
                    <span>Evidence</span>
                    <span class="block text-[10px] font-normal text-blue-700 mt-1">Docs &amp; Logs</span>
                </div>
                <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900">
                    <span class="block text-base">3️⃣</span>
                    <span>Review</span>
                    <span class="block text-[10px] font-normal text-indigo-700 mt-1">Officer Assessment</span>
                </div>
                <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900">
                    <span class="block text-base">4️⃣</span>
                    <span>Resolution</span>
                    <span class="block text-[10px] font-normal text-purple-700 mt-1">Ruling Made</span>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                    <span class="block text-base">5️⃣</span>
                    <span>Closed</span>
                    <span class="block text-[10px] font-normal text-emerald-700 mt-1">Settled &amp; Archived</span>
                </div>
            </div>
        </div>

        <!-- Initiate Dispute Form -->
        @if (auth()->user()->hasAnyPermission(['disputes.create', 'disputes.handle']) || auth()->user()->isAdmin())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Initiate User Dispute Case</h3>
                <p class="text-xs text-slate-500 mb-4">Open an official dispute docket between platform parties (e.g. Host vs Guest, Landlord vs Tenant).</p>

                <form method="POST" action="{{ route('customer-support.disputes.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Dispute Title / Claim *</label>
                        <input type="text" name="title" required placeholder="e.g. Security deposit withholding disagreement for Bole Apartment"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Property Involved (Optional)</label>
                        <select name="property_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <option value="">-- None --</option>
                            @foreach ($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Description of Dispute & Claimed Amount *</label>
                        <textarea name="description" rows="3" required placeholder="Detailed account of what transpired, communications held, and settlement sought..."
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900"></textarea>
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-[#1d3c34] hover:bg-[#254d43] text-white font-bold text-xs rounded-xl transition">
                            Open Dispute Docket
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Dispute Docket Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Dispute Dockets</h3>
            <div class="space-y-4">
                @forelse($disputes as $dispute)
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div>
                                <span class="font-bold text-sm text-slate-900 dark:text-white">Case #{{ $dispute->dispute_code }} - {{ $dispute->title }}</span>
                                <div class="text-xs text-slate-400 mt-0.5">
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
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase
                                @if($dispute->stage === 'closed') bg-slate-200 text-slate-700
                                @elseif($dispute->stage === 'resolution') bg-emerald-100 text-emerald-800
                                @elseif($dispute->stage === 'review') bg-indigo-100 text-indigo-800
                                @elseif($dispute->stage === 'evidence') bg-blue-100 text-blue-800
                                @else bg-amber-100 text-amber-800 @endif">
                                Stage: {{ $dispute->stage }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $dispute->description }}</p>

                        @if ($dispute->evidence_notes)
                            <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/40 rounded-xl text-xs text-blue-950 dark:text-blue-200">
                                <strong>Evidence &amp; Statements:</strong> {{ $dispute->evidence_notes }}
                            </div>
                        @endif

                        @if ($dispute->resolution_notes)
                            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs text-emerald-950 dark:text-emerald-200">
                                <strong>Official Ruling / Resolution:</strong> {{ $dispute->resolution_notes }}
                                <span class="block text-[10px] text-emerald-600 mt-1">Signed by {{ $dispute->resolver->name ?? 'Officer' }} at {{ $dispute->resolved_at?->format('M d, Y H:i') }}</span>
                            </div>
                        @endif

                        <!-- Dispute Stage Progression Form -->
                        @if ($dispute->stage !== 'closed')
                            <form method="POST" action="{{ route('customer-support.disputes.stage', $dispute) }}" class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-3">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Advance Stage To:</label>
                                        <select name="stage" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                            <option value="evidence" {{ $dispute->stage === 'evidence' ? 'selected' : '' }}>2️⃣ Evidence Gathering</option>
                                            <option value="review" {{ $dispute->stage === 'review' ? 'selected' : '' }}>3️⃣ Officer Review</option>
                                            @if (auth()->user()->hasPermission('disputes.resolve') || auth()->user()->isAdmin())
                                                <option value="resolution" {{ $dispute->stage === 'resolution' ? 'selected' : '' }}>4️⃣ Official Resolution</option>
                                                <option value="closed" {{ $dispute->stage === 'closed' ? 'selected' : '' }}>5️⃣ Close Docket</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Evidence Notes (If any):</label>
                                        <input type="text" name="evidence_notes" placeholder="Receipts verified, call logs..."
                                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Resolution Ruling (For Resolution/Closed):</label>
                                        <input type="text" name="resolution_notes" placeholder="E.g. Full refund authorized to tenant..."
                                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-1.5 bg-[#1d3c34] hover:bg-[#254d43] text-white text-xs font-bold rounded-xl transition">
                                        Update Dispute State
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-slate-400 text-xs py-8 text-center">No dispute dockets on file.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Section 3: Non-sensitive User Lookup & Assistance Tool -->
    <div id="user-lookup" data-section="user-lookup" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8 space-y-6">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">🔍 User Lookup &amp; Assist Desk</h3>
            <p class="text-xs text-slate-500">Quickly check customer account status, verified contact phone, and provide customer support assistance (Password Reset, Email Verification, Account Unsuspend).</p>
        </div>

        <form method="GET" action="{{ route('customer-support.dashboard') }}" class="flex items-center gap-3">
            <input type="text" name="q" placeholder="Search customer by name, email, or Ethiopian phone number..." value="{{ $searchQuery }}"
                class="flex-1 text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
            <button type="submit" class="px-5 py-2.5 bg-[#1d3c34] hover:bg-[#254d43] text-white font-semibold text-xs rounded-xl transition">
                Search Customer
            </button>
        </form>

        @if($searchedUsers->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Verified</th>
                            <th class="p-3 text-right">Assist Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($searchedUsers as $customer)
                            <tr>
                                <td class="p-3 font-bold text-slate-900 dark:text-white">
                                    {{ $customer->name }}
                                    @if ($customer->is_warned)
                                        <span class="ml-1 px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 text-[9px] font-black uppercase">Warned</span>
                                    @endif
                                </td>
                                <td class="p-3">{{ $customer->email }}</td>
                                <td class="p-3">{{ $customer->phone ?? 'N/A' }}</td>
                                <td class="p-3 capitalize">{{ $customer->role }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $customer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ strtoupper($customer->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if ($customer->email_verified_at)
                                        <span class="text-emerald-600 font-bold">✓ Yes</span>
                                    @else
                                        <span class="text-amber-500 font-bold">Pending</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    @if (auth()->user()->hasPermission('users.assist') || auth()->user()->isAdmin())
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Password Reset Link -->
                                            <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="reset_password">
                                                <button type="submit" title="Generate Password Reset Link" class="px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg text-[10px] font-bold">
                                                    🔑 Reset Pwd
                                                </button>
                                            </form>

                                            <!-- Email Verification -->
                                            @if (! $customer->email_verified_at)
                                                <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="verify_email">
                                                    <button type="submit" title="Manually Verify Email" class="px-2 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-lg text-[10px] font-bold">
                                                        ✉️ Verify
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Clear Warning -->
                                            @if ($customer->is_warned)
                                                <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="clear_warning">
                                                    <button type="submit" title="Clear Warning" class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded-lg text-[10px] font-bold">
                                                        🛡️ Unwarn
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Toggle Status -->
                                            <form method="POST" action="{{ route('customer-support.users.assist', $customer) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="toggle_status">
                                                <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $customer->status === 'active' ? 'bg-rose-100 hover:bg-rose-200 text-rose-800' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800' }}">
                                                    {{ $customer->status === 'active' ? 'Suspend' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($searchQuery)
            <div class="text-xs text-slate-400 py-4 text-center">No matching accounts found for '{{ $searchQuery }}'.</div>
        @endif
    </div>
@endsection
