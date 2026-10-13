@extends('dashboards.layout')

@section('portal_name', 'Customer Support Desk')
@section('dashboard-title', 'Customer Support & Helpdesk')
@section('dashboard-subtitle', 'User support tickets, customer inquiries, dispute mediation assistance, and non-sensitive account lookups.')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['users.assist', 'users.lookup', 'tickets.manage']))
        <a href="#user-lookup" data-target-section="user-lookup" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🔍</span>
                <span>User Lookup</span>
            </span>
        </a>
    @endif
    @if (auth()->user()->hasAnyPermission(['tickets.manage', 'tickets.close']))
        <a href="#tickets" data-target-section="tickets" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🎫</span>
                <span>Support Tickets</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['open_tickets'] }}</span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Open Tickets</span>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['open_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">In Progress</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['in_progress_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Resolved Tickets</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['resolved_tickets'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Active Disputes</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['active_disputes'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Section 1: Non-sensitive User Lookup Tool -->
    <div id="user-lookup" data-section="user-lookup" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">User Lookup (Limited Non-Sensitive Profile View)</h3>
        <p class="text-xs text-slate-500 mb-4">Quickly check customer account status, verified contact phone, and assigned roles when handling calls.</p>
        <form method="GET" action="{{ route('customer-support.dashboard') }}" class="flex items-center gap-3 mb-4">
            <input type="text" name="q" placeholder="Search customer by name, email, or Ethiopian phone number..." value="{{ $searchQuery }}"
                class="flex-1 text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
            <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold text-xs rounded-xl">
                Search User
            </button>
        </form>

        @if($searchedUsers->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-2">Name</th>
                            <th class="p-2">Email</th>
                            <th class="p-2">Phone</th>
                            <th class="p-2">Role</th>
                            <th class="p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($searchedUsers as $customer)
                            <tr>
                                <td class="p-2 font-bold text-slate-900 dark:text-white">{{ $customer->name }}</td>
                                <td class="p-2">{{ $customer->email }}</td>
                                <td class="p-2">{{ $customer->phone ?? 'N/A' }}</td>
                                <td class="p-2 capitalize">{{ $customer->role }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $customer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ strtoupper($customer->status ?? 'active') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($searchQuery)
            <div class="text-xs text-slate-400 py-2">No matching accounts found for '{{ $searchQuery }}'.</div>
        @endif
    </div>

    <!-- Section 2: Ticket Triage Queue -->
    <div id="tickets" data-section="tickets" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Support & Maintenance Tickets Queue</h3>
        <div class="space-y-4">
            @forelse($tickets as $ticket)
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-bold text-sm text-slate-900 dark:text-white">Ticket #{{ $ticket->id }} - {{ $ticket->title }}</span>
                            <span class="text-xs text-slate-400 ml-2">By: {{ $ticket->user->name ?? 'User' }} | Property: {{ $ticket->property->title ?? 'Platform' }}</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                            @if($ticket->status === 'resolved') bg-emerald-100 text-emerald-800
                            @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ strtoupper($ticket->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-4">{{ $ticket->description }}</p>

                    <form method="POST" action="{{ route('customer-support.tickets.reply', $ticket) }}" class="flex flex-col sm:flex-row items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="w-full sm:w-auto text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <input type="text" name="response" placeholder="Response / Note to customer..."
                            class="w-full sm:flex-1 text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white font-medium rounded-xl text-xs hover:bg-blue-700 transition">
                            Update Ticket
                        </button>
                    </form>
                </div>
            @empty
                <div class="text-slate-400 text-xs py-4 text-center">No active support tickets.</div>
            @endforelse
        </div>
    </div>
@endsection
