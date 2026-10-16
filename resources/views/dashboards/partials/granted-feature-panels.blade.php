@php
    $user = auth()->user();
    if (! $user) {
        return;
    }
    $role = $user->role ?? '';

    // Check permissions
    $canLeases = $user->hasAnyPermission(['leases.view', 'lease.view', 'rent.pay']);
    $canMaintenance = $user->hasAnyPermission(['maintenance.view', 'maintenance.create']);
    $canBookings = $user->hasAnyPermission(['bookings.view', 'booking.view', 'stays.view']);

    // Avoid duplicate panels for roles that already render these panels natively in their view
    $hasNativeLeases = in_array($role, ['tenant', 'agent', 'finance_officer', 'seller', 'landlord', 'property_owner'], true);
    $hasNativeMaintenance = in_array($role, ['tenant', 'seller', 'landlord', 'property_owner', 'property_manager'], true);
    $hasNativeBookings = in_array($role, ['host', 'guest', 'traveler'], true);
@endphp

{{-- 1. Leases & Rent Panel --}}
@if ($canLeases && ! $hasNativeLeases)
    @php
        $panelLeases = \App\Models\Lease::query()
            ->where('tenant_user_id', $user->id)
            ->orWhere('landlord_user_id', $user->id)
            ->with('property')
            ->latest()
            ->get();
    @endphp
    <div id="leases" data-section="leases" class="token-chart-card section-panel hidden scroll-mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="token-badge token-badge-success uppercase tracking-wider">Living</span>
                    <h2 class="text-xl font-black">Leases &amp; Rent</h2>
                </div>
                <p class="mt-1 text-xs token-muted">Review your lease contracts, active terms, and rental payment installment schedules.</p>
            </div>
            <span class="token-badge token-badge-neutral">
                {{ $panelLeases->count() }} {{ \Illuminate\Support\Str::plural('Agreement', $panelLeases->count()) }}
            </span>
        </div>

        @if ($panelLeases->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl block mb-2">🔑</span>
                <h3 class="text-base font-bold">No active leases on file</h3>
                <p class="mt-1 text-xs token-muted">You currently have no active lease agreements or rent schedules on record.</p>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Term Period</th>
                            <th>Monthly Rent</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($panelLeases as $lease)
                            <tr>
                                <td class="font-semibold">
                                    {{ $lease->property->title ?? 'Lease #'.$lease->id }}
                                </td>
                                <td class="token-muted">
                                    {{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') : '-' }} &rarr;
                                    {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') : '-' }}
                                </td>
                                <td class="font-mono font-bold">
                                    ETB {{ number_format((float) ($lease->rent_amount ?? 0), 2) }}
                                </td>
                                <td class="text-right">
                                    <span class="token-badge token-badge-success">
                                        {{ ucfirst($lease->status ?? 'Active') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif

{{-- 2. Maintenance Requests Panel --}}
@if ($canMaintenance && ! $hasNativeMaintenance)
    @php
        $panelTickets = \App\Models\MaintenanceTicket::query()
            ->where('user_id', $user->id)
            ->with('property')
            ->latest()
            ->get();
    @endphp
    <div id="maintenance" data-section="maintenance" class="token-chart-card section-panel hidden scroll-mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="token-badge token-badge-success uppercase tracking-wider">Living</span>
                    <h2 class="text-xl font-black">Maintenance Requests</h2>
                </div>
                <p class="mt-1 text-xs token-muted">Submit and track resolution on property repairs, service tickets, and issues.</p>
            </div>
            <span class="token-badge token-badge-neutral">
                {{ $panelTickets->count() }} {{ \Illuminate\Support\Str::plural('Ticket', $panelTickets->count()) }}
            </span>
        </div>

        @if ($panelTickets->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl block mb-2">🛠️</span>
                <h3 class="text-base font-bold">No maintenance tickets found</h3>
                <p class="mt-1 text-xs token-muted">You currently have no open or resolved maintenance service tickets.</p>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Title / Issue</th>
                            <th>Property</th>
                            <th>Priority</th>
                            <th>Submitted</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($panelTickets as $ticket)
                            <tr>
                                <td class="font-semibold">
                                    {{ $ticket->title ?? $ticket->description }}
                                </td>
                                <td class="token-muted">
                                    {{ $ticket->property->title ?? 'General' }}
                                </td>
                                <td class="font-medium">
                                    {{ ucfirst($ticket->priority ?? 'Normal') }}
                                </td>
                                <td class="token-muted">
                                    {{ $ticket->created_at?->diffForHumans() ?? 'Recently' }}
                                </td>
                                <td class="text-right">
                                    <span class="token-badge token-badge-warning">
                                        {{ ucfirst($ticket->status ?? 'Submitted') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif

{{-- 3. Bookings Panel --}}
@if ($canBookings && ! $hasNativeBookings)
    @php
        $panelBookings = \App\Models\Booking::query()
            ->where('user_id', $user->id)
            ->with('property')
            ->latest()
            ->get();
    @endphp
    <div id="bookings" data-section="bookings" class="token-chart-card section-panel hidden scroll-mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--token-border-subtle)] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="token-badge token-badge-info uppercase tracking-wider">Stays</span>
                    <h2 class="text-xl font-black">My Bookings</h2>
                </div>
                <p class="mt-1 text-xs token-muted">Track and review all your property, short stay, and travel reservations.</p>
            </div>
            <span class="token-badge token-badge-neutral">
                {{ $panelBookings->count() }} {{ \Illuminate\Support\Str::plural('Booking', $panelBookings->count()) }}
            </span>
        </div>

        @if ($panelBookings->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl block mb-2">🧳</span>
                <h3 class="text-base font-bold">No bookings found</h3>
                <p class="mt-1 text-xs token-muted">You currently have no stay or property reservations.</p>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="token-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Dates</th>
                            <th>Guests</th>
                            <th>Total (ETB)</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($panelBookings as $b)
                            <tr>
                                <td class="font-semibold">
                                    {{ $b->property->title ?? 'Reserved Property #'.$b->property_id }}
                                </td>
                                <td class="token-muted">
                                    {{ $b->check_in ? \Carbon\Carbon::parse($b->check_in)->format('M d, Y') : '-' }} &rarr;
                                    {{ $b->check_out ? \Carbon\Carbon::parse($b->check_out)->format('M d, Y') : '-' }}
                                </td>
                                <td>
                                    {{ $b->guests_count ?? 1 }}
                                </td>
                                <td class="font-mono font-bold">
                                    ETB {{ number_format((float) ($b->total_price ?? 0), 2) }}
                                </td>
                                <td class="text-right">
                                    <span class="token-badge token-badge-success">
                                        {{ ucfirst($b->status ?? 'Confirmed') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
