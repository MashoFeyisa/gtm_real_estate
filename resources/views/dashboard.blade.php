<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    {{-- CDN reference fallback --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.GOOGLE_MAPS_API_KEY = @json(config('services.google.maps_api_key') ?: '');
    </script>
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen">
        <aside id="admin-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] flex flex-col justify-between" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
            <div>
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ $siteBrand['logoUrl'] }}" alt="{{ $siteBrand['name'] }} Logo" class="h-10 w-10 rounded-xl object-contain ring-2 ring-[#d9cab3] bg-white/10">
                <span class="text-xl font-black tracking-tight text-white">{{ $siteBrand['name'] }}</span>
            </a>

            {{-- Sidebar Skeleton placeholder for layout height stability while permissions/badges evaluate --}}
            <div class="sidebar-skeleton hidden pointer-events-none opacity-0 h-0 overflow-hidden" aria-hidden="true">
                <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
                <div class="h-9 w-full rounded-xl bg-white/5 animate-pulse mb-2"></div>
            </div>

            <nav class="mt-8 space-y-2 min-h-[420px]">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span>🏠</span>
                    <span>Home</span>
                </a>
                <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
                    <span>📊 Overview</span>
                </a>
                <a href="#users" data-target-section="users" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>👥</span>
                        <span>Users</span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $users->total() }}</span>
                </a>
                <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏠</span>
                        <span>Properties</span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $properties->total() }}</span>
                </a>
                <a href="#orders" data-target-section="orders" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🧾</span>
                        <span>Orders</span>
                    </span>
                </a>
                <a href="#listings" data-target-section="listings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📋</span>
                        <span>Listings</span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ ($listings ?? collect())->total() }}</span>
                </a>
                <a href="#hosts" data-target-section="hosts" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏡</span>
                        <span>Hosts</span>
                    </span>
                </a>
                <a href="#companies" data-target-section="companies" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏢</span>
                        <span>Companies</span>
                    </span>
                </a>
                <a href="#developers" data-target-section="developers" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏗️</span>
                        <span>Developers</span>
                    </span>
                </a>
                <a href="#projects" data-target-section="projects" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🏗️</span>
                        <span>Projects</span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ ($projects ?? collect())->count() }}</span>
                </a>
                <a href="#stay-bookings" data-target-section="stay-bookings" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📅</span>
                        <span>Bookings</span>
                    </span>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ ($bookings ?? collect())->count() }}</span>
                </a>
                <a href="#transactions" data-target-section="transactions" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💱</span>
                        <span>Transactions</span>
                    </span>
                </a>
                <a href="#revenue" data-target-section="revenue" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💰</span>
                        <span>Revenue</span>
                    </span>
                </a>
                <a href="#reports" data-target-section="reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🚨</span>
                        <span>Reports</span>
                    </span>
                    @if (($pendingReportsCount ?? 0) > 0)
                        <span class="rounded-full bg-rose-500/20 border border-rose-400 px-2 py-0.5 text-[10px] font-bold text-rose-300">{{ $pendingReportsCount }}</span>
                    @endif
                </a>
                <a href="#job-postings" data-target-section="job-postings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💼</span>
                        <span>Job Posting</span>
                    </span>
                </a>
                <a href="#job-applications" data-target-section="job-applications" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📮</span>
                        <span>Job Applications</span>
                    </span>
                    @if (($pendingApplicationsCount ?? 0) > 0)
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $pendingApplicationsCount }}</span>
                    @endif
                </a>
                <a href="#blog-posts" data-target-section="blog-posts" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📰</span>
                        <span>Blogs and News</span>
                    </span>
                </a>
                <a href="#leases" data-target-section="leases" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🔑</span>
                        <span>Lease and Rental</span>
                    </span>
                    @if (($overdueRentCount ?? 0) > 0)
                        <span class="rounded-full bg-rose-500/20 border border-rose-400 px-2 py-0.5 text-[10px] font-bold text-rose-300">{{ $overdueRentCount }} overdue</span>
                    @endif
                </a>
                <a href="#offers-negotiations" data-target-section="offers-negotiations" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🤝</span>
                        <span>Offers</span>
                    </span>
                </a>
                <a href="#inquiries" data-target-section="inquiries" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💬</span>
                        <span>Message</span>
                    </span>
                    @if (isset($inquiries) && $inquiries->whereNull('read_at')->isNotEmpty())
                        <span id="sidebar-inquiries-badge" class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-xs font-bold text-[#9b6c17]">{{ $inquiries->whereNull('read_at')->count() }} new</span>
                    @endif
                </a>
                <a href="#verifications" data-target-section="verifications" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>✅</span>
                        <span>Verification Requests</span>
                    </span>
                </a>
                <a href="#roles-permissions" data-target-section="roles-permissions" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🔐</span>
                        <span>Roles and Permissions</span>
                    </span>
                </a>
                <a href="#settings" data-target-section="settings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>⚙️</span>
                        <span>Site Settings</span>
                    </span>
                </a>

                <div class="pt-4">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#d9cab3]">More Desks</p>
                </div>

                <a href="#viewings" data-target-section="viewings" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>👁️</span>
                        <span>Property Viewings</span>
                    </span>
                </a>
                <a href="#testimonials" data-target-section="testimonials" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>💬</span>
                        <span>Client Testimonials</span>
                    </span>
                </a>
                <a href="#categories" data-target-section="categories" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>🗂️</span>
                        <span>Categories and Listing Types</span>
                    </span>
                </a>
                <a href="#activity-logs" data-target-section="activity-logs" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                    <span class="flex items-center gap-2.5">
                        <span>📜</span>
                        <span>Activity Logs</span>
                    </span>
                </a>
            </nav>
            </div>

            @include('dashboard.partials.signed-in-user', ['roleLabel' => 'Administrator'])
        </aside>

        <main class="flex-1 px-6 py-12">
        <div id="overview" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between scroll-mt-24">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Control Panel</p>
                <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Admin Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative" id="dashboard-messages-icon-wrapper">
                    @php
                        $unreadInquiriesCount = isset($inquiries) ? $inquiries->whereNull('read_at')->count() : 0;
                        $pendingReportsTotal = isset($pendingReportsCount) ? $pendingReportsCount : (isset($reports) ? $reports->where('status', 'pending')->count() : 0);
                        $overdueRentTotal = isset($overdueRentCount) ? $overdueRentCount : 0;
                        $totalAdminAlerts = $unreadInquiriesCount + $pendingReportsTotal + $overdueRentTotal;
                    @endphp
                    <button type="button" id="dashboard-messages-icon" class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#cdbb97] bg-white text-[#1d3c34] transition hover:bg-[#f1e4cf] cursor-pointer" title="Notifications &amp; Reports" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($totalAdminAlerts > 0)
                            <span id="dashboard-messages-icon-badge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-black text-white shadow-sm">{{ $totalAdminAlerts }}</span>
                        @endif
                    </button>
                    <div id="dashboard-messages-dropdown" class="hidden absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xl">
                        <div class="border-b border-[#f0e8d8] bg-[#fbf8f2] px-3.5 py-2.5 flex items-center justify-between">
                            <p class="text-[10px] font-black uppercase tracking-wider text-[#587165]">Admin Alerts &amp; Notifications</p>
                            @if ($totalAdminAlerts > 0)
                                <span class="rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[10px] font-bold">{{ $totalAdminAlerts }} new</span>
                            @endif
                        </div>

                        {{-- Section: Overdue Rent Installments Alert --}}
                        @if ($overdueRentTotal > 0)
                            <div class="border-b border-[#f0e8d8] bg-rose-50/70 p-3">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-950 flex items-center gap-1.5">
                                        <span>⚠️</span>
                                        <span>{{ $overdueRentTotal }} Overdue Rent Installment(s) Pending Collection</span>
                                    </span>
                                    <span class="rounded bg-rose-200 text-rose-950 px-1.5 py-0.5 text-[9px] font-bold">Action Needed</span>
                                </div>
                                <p class="text-[11px] text-rose-900/90 mt-1 leading-snug">
                                    Lease tenants have past-due installments. Follow up and record payment or issue notice.
                                </p>
                                <a href="#leases" data-target-section="leases" class="nav-section-link inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 hover:text-rose-900 hover:underline mt-1.5 cursor-pointer">
                                    <span>Review Overdue Leases &rarr;</span>
                                </a>
                            </div>
                        @endif

                        {{-- Section: Direct Client Incident & Misleading Reports --}}
                        @if ($pendingReportsTotal > 0)
                            <div class="border-b border-[#f0e8d8] bg-rose-50/50 p-3">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-800 flex items-center gap-1">
                                        <span>🚨</span>
                                        <span>Client Safety Reports &bull; {{ $pendingReportsTotal }} Client Incident &amp; Misleading Report(s) Awaiting Review</span>
                                    </span>
                                    <span class="rounded bg-rose-200 text-rose-900 px-1.5 py-0.5 text-[9px] font-bold">{{ $pendingReportsTotal }} pending</span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1 leading-snug">
                                    Clients have submitted safety reports regarding potential fraud, fake listings, inaccurate prices, or broker misconduct.
                                </p>
                                @if (isset($reports) && $reports->where('status', 'pending')->isNotEmpty())
                                    <div class="space-y-1 mt-2 max-h-36 overflow-y-auto">
                                        @foreach ($reports->where('status', 'pending')->take(3) as $pendingRep)
                                            <a href="#reports" data-target-section="reports" class="nav-section-link block rounded-xl bg-white p-2 border border-rose-100 text-left transition hover:bg-rose-50 shadow-2xs">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="font-bold text-xs text-rose-900 truncate">#{{ $pendingRep->report_number }}: {{ $pendingRep->reason_label }}</span>
                                                    <span class="text-[9px] text-slate-400 shrink-0">{{ $pendingRep->created_at?->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-[10px] text-slate-600 truncate mt-0.5">
                                                    From: {{ $pendingRep->user?->name ?: ($pendingRep->contact_email ?: 'Client') }}
                                                    @if ($pendingRep->property) — {{ $pendingRep->property->title }} @endif
                                                </p>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                <a href="#reports" data-target-section="reports" class="nav-section-link inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 hover:text-rose-900 hover:underline mt-2 cursor-pointer">
                                    <span>Investigate Reports &rarr;</span>
                                </a>
                            </div>
                        @endif

                        {{-- Section: Latest Client Messages --}}
                        <div class="p-2">
                            <p class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-[#587165]">Client Inquiries &amp; Messages</p>
                            @php $recentInquiries = isset($inquiries) ? $inquiries->take(3) : collect(); @endphp
                            <div class="max-h-52 overflow-y-auto space-y-1">
                                @forelse ($recentInquiries as $recentInquiry)
                                    <button type="button" class="dashboard-messages-nav-item block w-full rounded-xl px-2.5 py-2 text-left transition hover:bg-[#f3eadc]">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="truncate text-xs font-black text-[#1d3c34]">{{ $recentInquiry->subject }}</span>
                                            <span class="shrink-0 text-[10px] text-slate-400">{{ $recentInquiry->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <span class="mt-0.5 block truncate text-[11px] font-semibold text-slate-600">{{ $recentInquiry->name }}</span>
                                        <span class="mt-0.5 line-clamp-1 block text-[11px] text-slate-600">{{ $recentInquiry->message }}</span>
                                    </button>
                                @empty
                                    <p class="px-2 py-2 text-xs text-slate-500">No messages yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-3 border-t border-[#f0e8d8] bg-[#fbf8f2] text-center text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                            <a href="#leases" data-target-section="leases" class="nav-section-link py-2.5 hover:bg-[#f3eadc] border-r border-[#f0e8d8]">Overdue Leases →</a>
                            <a href="#reports" data-target-section="reports" class="nav-section-link py-2.5 hover:bg-[#f3eadc] border-r border-[#f0e8d8]">Client Reports →</a>
                            <button type="button" class="dashboard-messages-nav-item py-2.5 hover:bg-[#f3eadc]">All Messages →</button>
                        </div>
                    </div>
                </div>


                {{-- Admin Profile Circle Avatar Button: Clicking displays profile edit page --}}
                <a href="#profile" data-target-section="profile" class="nav-section-link group relative inline-flex items-center justify-center rounded-full p-0.5 transition ring-2 ring-[#cdbb97] hover:ring-[#1d3c34] focus:outline-none focus:ring-4 focus:ring-[#1d3c34]/30 shadow-xs cursor-pointer" title="Edit Profile ({{ auth()->user()->name ?? 'Admin' }})">
                    @if (auth()->user()->photo_url)
                        <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover group-hover:brightness-95 transition">
                    @else
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#f8f3eb] group-hover:bg-[#254d43] transition">
                            {{ auth()->user()->initials }}
                        </span>
                    @endif
                    <span class="sr-only">Edit Profile</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-medium text-[#214f3a]">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-[#c86b5c] bg-[#fef3f1] p-4 text-sm font-medium text-[#a24339]">
                <p class="font-bold mb-1">Please fix the following errors:</p>
                <ul class="list-disc pl-5 space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div data-section="overview" class="section-panel">
            @if (isset($unreadAdminRequests) && $unreadAdminRequests->isNotEmpty())
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-[#b7842d]/30 bg-[#fef9ec] p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f9ecd0] text-[#9b6c17]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="font-black text-[#1d3c34]">Agent Agreement Requests Pending Admin Review ({{ $unreadAdminRequests->count() }} new)</h2>
                            <p class="text-xs text-slate-600">Agents have reached agreement with clients and requested admin confirmation and agreement review.</p>
                        </div>
                    </div>
                    <a href="#orders" data-target-section="orders" class="nav-section-link rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43]">
                        Review Requests
                    </a>
                </div>
            @endif

            @if (isset($pendingApplicationsCount) && $pendingApplicationsCount > 0)
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-[#d4af37]/40 bg-gradient-to-r from-[#fefbf6] via-[#fcf6e8] to-[#fbf2da] p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#102b25] text-[#d4af37] shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="font-black text-[#102b25]">New Candidate Job Applications Awaiting Review ({{ $pendingApplicationsCount }} pending)</h2>
                            <p class="text-xs text-slate-600">Candidates have submitted online applications with attached PDF or DOCX resumes.</p>
                        </div>
                    </div>
                    <a href="#job-applications" data-target-section="job-applications" class="nav-section-link inline-flex items-center gap-2 rounded-full bg-[#102b25] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#e7d8b7] hover:bg-[#1a3d34] transition shadow-sm">
                        <span>Review All Applications &rarr;</span>
                    </a>
                </div>
            @endif



            <div class="mt-8 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Properties</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $overviewStats['totalProperties'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Client Reports</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ isset($reports) ? $reports->count() : 0 }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Active Listings</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $overviewStats['activeListings'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Master Developments</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $projects->count() }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f9ebd8] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Pending Reviews</p>
                    <p class="mt-3 text-3xl font-black text-[#b7842d]">{{ $overviewStats['pendingReviews'] }}</p>
                </div>
                <a href="#job-applications" data-target-section="job-applications" class="nav-section-link rounded-[1.5rem] border border-[#d9cab3] bg-[#fefbf6] p-5 shadow-sm transition hover:border-[#1d3c34] block cursor-pointer">
                    <p class="text-sm font-medium text-slate-500">Job Applicants</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $jobApplications->count() }}</p>
                </a>
            </div>



            {{-- Admin Overview Interactive Analytics & Visualizations --}}
            <div class="mt-8 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#102b25]"></span>
                            <h2 class="text-xl font-black text-[#1d3c34]">Executive Intelligence &amp; Performance Overview</h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Real-time database analytics tracking transaction velocity, inventory distribution, and broker productivity.</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart 1: Monthly Trends -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Sales, Rentals &amp; Revenue Trends</h3>
                            <span class="text-[10px] font-bold text-slate-400">Past 12 Months</span>
                        </div>
                        <div id="admin-overview-monthly-chart" class="min-h-[280px]"></div>
                    </div>

                    <!-- Chart 2: Property Status Donut -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Property Inventory Distribution</h3>
                            <span class="text-[10px] font-bold text-slate-400">Available vs Rented vs Sold</span>
                        </div>
                        <div id="admin-overview-status-chart" class="min-h-[280px]"></div>
                    </div>

                    <!-- Chart 3: Agent Performance -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Broker Transaction Velocity</h3>
                            <span class="text-[10px] font-bold text-slate-400">Closed Sales &amp; Leases</span>
                        </div>
                        <div id="admin-overview-agent-chart" class="min-h-[280px]"></div>
                    </div>

                    <!-- Chart 4: Location Concentration -->
                    <div class="rounded-2xl border border-slate-100 bg-[#fbf9f5] p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Geographic Market Concentration</h3>
                            <span class="text-[10px] font-bold text-slate-400">Properties by City</span>
                        </div>
                        <div id="admin-overview-location-chart" class="min-h-[280px]"></div>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black text-[#1d3c34]">Recent Properties</h2>
                    <ul class="mt-5 space-y-4">
                        @forelse ($recentProperties as $recent)
                            <li class="flex items-center justify-between border-b border-[#e7ddca] pb-3 last:border-b-0 last:pb-0">
                                <span class="font-medium text-slate-700">{{ $recent->title }}</span>
                                @php
                                    $badge = match ($recent->status) {
                                        'published' => ['Published', 'bg-[#dfeee4] text-[#1d3c34]'],
                                        'sold' => ['Sold', 'bg-[#e9efe9] text-[#2d5d4d]'],
                                        'archived' => ['Archived', 'bg-[#f3ecdb] text-[#9b6c17]'],
                                        'available' => ['Available', 'bg-[#edf2ee] text-[#2d5d4d]'],
                                        default => ['Draft', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">No properties yet. Create your first listing in the Properties section.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-2xl font-black text-[#1d3c34]">Recent Job Applications</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Inspect incoming candidate profiles and resumes directly.</p>
                            </div>
                            @if ($pendingApplicationsCount > 0)
                                <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-xs font-bold text-[#9b6c17] border border-[#d4af37]/30 shrink-0">
                                    {{ $pendingApplicationsCount }} pending review
                                </span>
                            @endif
                        </div>
                        <ul class="mt-5 space-y-3">
                            @forelse ($jobApplications->take(4) as $recentApp)
                                <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] p-3.5 hover:bg-[#f6f2ea] transition">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-extrabold text-sm text-[#1d3c34] truncate">{{ $recentApp->full_name }}</p>
                                            @php
                                                $appBadge = match ($recentApp->status) {
                                                    'pending' => ['Pending', 'bg-[#f9ecd0] text-[#9b6c17]'],
                                                    'shortlisted' => ['Shortlisted', 'bg-emerald-100 text-emerald-800'],
                                                    'reviewed' => ['Reviewed', 'bg-blue-100 text-blue-800'],
                                                    'rejected' => ['Rejected', 'bg-rose-100 text-rose-800'],
                                                    'hired' => ['Hired', 'bg-purple-100 text-purple-800'],
                                                    default => ['Pending', 'bg-slate-100 text-slate-800'],
                                                };
                                            @endphp
                                            <span class="rounded-full px-2 py-0.2 text-[10px] font-bold shrink-0 {{ $appBadge[1] }}">{{ $appBadge[0] }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-0.5 truncate">
                                            <span class="font-semibold text-[#102b25]">{{ $recentApp->job_title }}</span>
                                            @if ($recentApp->current_location)
                                                <span>• {{ $recentApp->current_location }}</span>
                                            @endif
                                            <span>• {{ $recentApp->created_at->diffForHumans() }}</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if ($recentApp->resume_path)
                                            <a href="{{ route('dashboard.job-applications.download', $recentApp) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-[#102b25] hover:text-white transition shadow-2xs" title="Download CV ({{ $recentApp->formatted_file_size }})">
                                                @if ($recentApp->is_pdf)
                                                    <span class="rounded bg-[#e02424] px-1 py-0.2 text-[9px] font-black text-white">PDF</span>
                                                @else
                                                    <span class="rounded bg-[#2563eb] px-1 py-0.2 text-[9px] font-black text-white">DOCX</span>
                                                @endif
                                                <span>CV</span>
                                            </a>
                                        @endif
                                        <button type="button" onclick="viewCandidateDetails({{ json_encode([
                                            'id' => $recentApp->id,
                                            'name' => $recentApp->full_name,
                                            'job_title' => $recentApp->job_title,
                                            'email' => $recentApp->email,
                                            'phone' => $recentApp->phone,
                                            'location' => $recentApp->current_location,
                                            'experience' => $recentApp->experience_years,
                                            'education' => $recentApp->education_level,
                                            'portfolio' => $recentApp->portfolio_url,
                                            'expected_salary' => $recentApp->expected_salary,
                                            'start_date' => $recentApp->available_start_date,
                                            'cover_letter' => $recentApp->cover_letter,
                                            'resume_download_url' => route('dashboard.job-applications.download', $recentApp),
                                            'resume_name' => $recentApp->resume_original_name,
                                            'resume_size' => $recentApp->formatted_file_size,
                                            'is_pdf' => $recentApp->is_pdf,
                                            'status' => $recentApp->status,
                                            'status_action' => route('dashboard.job-applications.status', $recentApp),
                                            'applied_at' => $recentApp->created_at->format('M d, Y h:i A'),
                                        ]) }})" class="rounded-xl bg-[#1d3c34] px-3.5 py-1.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer">
                                            View
                                        </button>
                                    </div>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500 py-4 text-center">No applications received yet.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="mt-5 pt-3 border-t border-[#f0e8dc] text-right">
                        <a href="#job-applications" data-target-section="job-applications" class="nav-section-link text-xs font-extrabold text-[#1d3c34] hover:text-[#2d5d4d] hover:underline inline-flex items-center gap-1">
                            <span>Open Full Job Applications Studio ({{ $jobApplications->count() }}) &rarr;</span>
                        </a>
                    </div>
                </section>
            </div>
        </div>

        @include('dashboard.partials.users-desk')
        @include('dashboard.partials.roles-desk')
        @include('dashboard.partials.category-desk')
        @include('dashboard.partials.testimonials-desk')
        @include('dashboard.partials.properties-desk')
        @include('dashboard.partials.orders-desk')
        @include('dashboard.partials.listings-desk')
        @include('dashboard.partials.actors-desk')
        @include('dashboard.partials.projects-desk')
        @include('dashboard.partials.transactions-desk')
        @include('dashboard.partials.revenue-desk')
        {{-- ═══════════════════ MODULE 4: NEGOTIATION, LEGAL & LEASE DESKS ═══════════════════ --}}
        @include('properties.partials.viewings-desk')
        @include('properties.partials.offers-desk')
        @include('properties.partials.verification-desk')
        @include('properties.partials.leases-desk')
        @include('properties.partials.payment-desk')

        {{-- ═══════════════════ STAY BOOKINGS ═══════════════════ --}}
        @include('dashboard.partials.stay-bookings-desk')
        {{-- ═══════════════════ MESSAGES / INQUIRIES ═══════════════════ --}}
        @include('dashboard.partials.inquiries-desk')
        {{-- ═══════════════════ TRUST & SAFETY / CLIENT INCIDENT REPORTS ═══════════════════ --}}
        @include('dashboard.partials.reports-desk')

        @include('dashboard.partials.settings-desk')
        @include('dashboard.partials.profile-desk')
        @include('dashboard.partials.careers-blog-desk')
        @include('dashboard.partials.activity-logs-desk')
        </main>
    </div>

    @include('dashboard.partials.scripts')
    @include('dashboard.partials.modals')
    @include('properties.partials.payment-modal')
    @include('layouts.partials.confirm-modal')
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
