@extends('dashboards.layout')

@section('title', 'Admin Dashboard')
@section('portal_name', 'Admin Control Panel')
@section('dashboard-title', 'Admin Dashboard')
@section('dashboard-subtitle', 'Platform oversight: users, listings, transactions, revenue, governance, and every operations desk in one control panel.')

@section('sidebar-nav')
    <x-sidebar-item href="#users" icon="👥" label="Users" :count="$users->total()" />
    <x-sidebar-item href="#properties" icon="🏠" label="Properties" :count="$properties->total()" />
    <x-sidebar-item href="#orders" icon="🧾" label="Orders" />
    <x-sidebar-item href="#listings" icon="📋" label="Listings" :count="($listings ?? collect())->total()" />
    <x-sidebar-item href="#hosts" icon="🏡" label="Hosts" />
    <x-sidebar-item href="#companies" icon="🏢" label="Companies" />
    <x-sidebar-item href="#developers" icon="🏗️" label="Developers" />
    <x-sidebar-item href="#projects" icon="🏗️" label="Projects" :count="($projects ?? collect())->count()" />
    <x-sidebar-item href="#stay-bookings" icon="📅" label="Bookings" :count="($bookings ?? collect())->count()" />
    <x-sidebar-item href="#transactions" icon="💱" label="Transactions" />
    <x-sidebar-item href="#revenue" icon="💰" label="Revenue" />
    <x-sidebar-item href="#reports" icon="🚨" label="Reports" :count="($pendingReportsCount ?? 0) > 0 ? $pendingReportsCount : null" :alert="($pendingReportsCount ?? 0) > 0" />
    <x-sidebar-item href="#job-postings" icon="💼" label="Job Posting" />
    <x-sidebar-item href="#job-applications" icon="📮" label="Job Applications" :count="($pendingApplicationsCount ?? 0) > 0 ? $pendingApplicationsCount : null" />
    <x-sidebar-item href="#blog-posts" icon="📰" label="Blogs and News" />
    <x-sidebar-item href="#leases" icon="🔑" label="Lease and Rental" :badge="($overdueRentCount ?? 0) > 0 ? ($overdueRentCount . ' overdue') : null" :alert="($overdueRentCount ?? 0) > 0" />
    <x-sidebar-item href="#offers-negotiations" icon="🤝" label="Offers" />
    <x-sidebar-item href="#inquiries" icon="💬" :badge="(isset($inquiries) && $inquiries->whereNull('read_at')->isNotEmpty()) ? ($inquiries->whereNull('read_at')->count() . ' new') : null" badgeId="sidebar-inquiries-badge">
        <span>Message</span>
    </x-sidebar-item>
    <x-sidebar-item href="#verifications" icon="✅" label="Verification Requests" />
    <x-sidebar-item href="#roles-permissions" icon="🔐" label="Roles and Permissions" />
    <x-sidebar-item href="#settings" icon="⚙️" label="Site Settings" />

    <div class="pt-4">
        <p class="px-3 token-sidebar-heading">More Desks</p>
    </div>

    <x-sidebar-item href="#viewings" icon="👁️" label="Property Viewings" />
    <x-sidebar-item href="#testimonials" icon="💬" label="Client Testimonials" />
    <x-sidebar-item href="#categories" icon="🗂️" label="Categories and Listing Types" />
    <x-sidebar-item href="#activity-logs" icon="📜" label="Activity Logs" />
@endsection

@section('dashboard-content')
    @if (session('success'))
        <div class="mt-6 token-alert token-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 token-alert token-alert-danger">
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
            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 token-alert token-alert-warning">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--token-accent)_14%,transparent)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-black">Agent Agreement Requests Pending Admin Review ({{ $unreadAdminRequests->count() }} new)</h2>
                        <p class="text-xs opacity-90">Agents have reached agreement with clients and requested admin confirmation and agreement review.</p>
                    </div>
                </div>
                <a href="#orders" data-target-section="orders" class="nav-section-link token-btn token-btn-primary">
                    Review Requests
                </a>
            </div>
        @endif

        @if (isset($pendingApplicationsCount) && $pendingApplicationsCount > 0)
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4 token-alert token-alert-accent">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl token-btn-primary shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-black">New Candidate Job Applications Awaiting Review ({{ $pendingApplicationsCount }} pending)</h2>
                        <p class="text-xs opacity-90">Candidates have submitted online applications with attached PDF or DOCX resumes.</p>
                    </div>
                </div>
                <a href="#job-applications" data-target-section="job-applications" class="nav-section-link token-btn token-btn-primary">
                    <span>Review All Applications &rarr;</span>
                </a>
            </div>
        @endif

        <div class="mt-8 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
            <div class="token-stat-card">
                <p class="token-stat-title">Total Properties</p>
                <p class="token-stat-value token-stat-primary">{{ $overviewStats['totalProperties'] }}</p>
            </div>
            <div class="token-stat-card">
                <p class="token-stat-title">Client Reports</p>
                <p class="token-stat-value">{{ isset($reports) ? $reports->count() : 0 }}</p>
            </div>
            <div class="token-stat-card">
                <p class="token-stat-title">Active Listings</p>
                <p class="token-stat-value token-stat-success">{{ $overviewStats['activeListings'] }}</p>
            </div>
            <div class="token-stat-card">
                <p class="token-stat-title">Master Developments</p>
                <p class="token-stat-value token-stat-primary">{{ $projects->count() }}</p>
            </div>
            <div class="token-stat-card">
                <p class="token-stat-title">Pending Reviews</p>
                <p class="token-stat-value token-stat-warning">{{ $overviewStats['pendingReviews'] }}</p>
            </div>
            <a href="#job-applications" data-target-section="job-applications" class="nav-section-link token-stat-card block cursor-pointer">
                <p class="token-stat-title">Job Applicants</p>
                <p class="token-stat-value token-stat-info">{{ $jobApplications->count() }}</p>
            </a>
        </div>

        {{-- Admin Overview Interactive Analytics & Visualizations --}}
        <div class="mt-8 token-chart-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-[var(--token-border-subtle)] gap-2">
                <div>
                    <h2 class="token-chart-title text-xl">Executive Intelligence &amp; Performance Overview</h2>
                    <p class="token-chart-sub text-xs mt-0.5">Real-time database analytics tracking transaction velocity, inventory distribution, and broker productivity.</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Monthly Trends -->
                <div class="token-chart-tile">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="token-chart-title text-xs uppercase tracking-wider">Sales, Rentals &amp; Revenue Trends</h3>
                        <span class="token-chart-sub text-[10px]">Past 12 Months</span>
                    </div>
                    <div id="admin-overview-monthly-chart" class="min-h-[280px]"></div>
                </div>

                <!-- Chart 2: Property Status Donut -->
                <div class="token-chart-tile">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="token-chart-title text-xs uppercase tracking-wider">Property Inventory Distribution</h3>
                        <span class="token-chart-sub text-[10px]">Available vs Rented vs Sold</span>
                    </div>
                    <div id="admin-overview-status-chart" class="min-h-[280px]"></div>
                </div>

                <!-- Chart 3: Agent Performance -->
                <div class="token-chart-tile">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="token-chart-title text-xs uppercase tracking-wider">Broker Transaction Velocity</h3>
                        <span class="token-chart-sub text-[10px]">Closed Sales &amp; Leases</span>
                    </div>
                    <div id="admin-overview-agent-chart" class="min-h-[280px]"></div>
                </div>

                <!-- Chart 4: Location Concentration -->
                <div class="token-chart-tile">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="token-chart-title text-xs uppercase tracking-wider">Geographic Market Concentration</h3>
                        <span class="token-chart-sub text-[10px]">Properties by City</span>
                    </div>
                    <div id="admin-overview-location-chart" class="min-h-[280px]"></div>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            <section class="token-card">
                <h2 class="token-section-title !border-b-0 !pb-0 text-2xl">Recent Properties</h2>
                <ul class="mt-5 space-y-4">
                    @forelse ($recentProperties as $recent)
                        <li class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-3 last:border-b-0 last:pb-0">
                            <span class="font-medium">{{ $recent->title }}</span>
                            @php
                                $badge = match ($recent->status) {
                                    'published' => ['Published', 'token-badge-success'],
                                    'sold' => ['Sold', 'token-badge-info'],
                                    'archived' => ['Archived', 'token-badge-neutral'],
                                    'available' => ['Available', 'token-badge-success'],
                                    default => ['Draft', 'token-badge-warning'],
                                };
                            @endphp
                            <span class="token-badge {{ $badge[1] }}">{{ $badge[0] }}</span>
                        </li>
                    @empty
                        <li class="token-muted">No properties yet. Create your first listing in the Properties section.</li>
                    @endforelse
                </ul>
            </section>

            <section class="token-card flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="token-section-title !border-b-0 !pb-0 text-2xl">Recent Job Applications</h2>
                            <p class="token-muted text-xs mt-0.5">Inspect incoming candidate profiles and resumes directly.</p>
                        </div>
                        @if ($pendingApplicationsCount > 0)
                            <span class="token-badge token-badge-warning shrink-0">
                                {{ $pendingApplicationsCount }} pending review
                            </span>
                        @endif
                    </div>
                    <ul class="mt-5 space-y-3">
                        @forelse ($jobApplications->take(4) as $recentApp)
                            <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 token-chart-tile">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-extrabold text-sm truncate">{{ $recentApp->full_name }}</p>
                                        @php
                                            $appBadge = match ($recentApp->status) {
                                                'pending' => ['Pending', 'token-badge-warning'],
                                                'shortlisted' => ['Shortlisted', 'token-badge-info'],
                                                'reviewed' => ['Reviewed', 'token-badge-neutral'],
                                                'rejected' => ['Rejected', 'token-badge-danger'],
                                                'hired' => ['Hired', 'token-badge-success'],
                                                default => ['Pending', 'token-badge-neutral'],
                                            };
                                        @endphp
                                        <span class="token-badge {{ $appBadge[1] }} shrink-0">{{ $appBadge[0] }}</span>
                                    </div>
                                    <p class="token-muted mt-0.5 truncate">
                                        <span class="font-semibold">{{ $recentApp->job_title }}</span>
                                        @if ($recentApp->current_location)
                                            <span>• {{ $recentApp->current_location }}</span>
                                        @endif
                                        <span>• {{ $recentApp->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($recentApp->resume_path)
                                        <a href="{{ route('dashboard.job-applications.download', $recentApp) }}" class="token-btn token-btn-secondary !px-2.5 !py-1.5 !text-xs" title="Download CV ({{ $recentApp->formatted_file_size }})">
                                            @if ($recentApp->is_pdf)
                                                <span class="token-badge token-badge-danger !px-1 !py-0 !text-[9px]">PDF</span>
                                            @else
                                                <span class="token-badge token-badge-info !px-1 !py-0 !text-[9px]">DOCX</span>
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
                                    ]) }})" class="token-btn token-btn-primary cursor-pointer">
                                        View
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="token-muted py-4 text-center">No applications received yet.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="mt-5 pt-3 border-t border-[var(--token-border-subtle)] text-right">
                    <a href="#job-applications" data-target-section="job-applications" class="nav-section-link token-muted text-xs font-extrabold hover:underline inline-flex items-center gap-1">
                        <span>Open Full Job Applications Studio ({{ $jobApplications->count() }}) &rarr;</span>
                    </a>
                </div>
            </section>
        </div>

        {{-- Page-level alerts & messages widget --}}
        <div class="mt-8 flex justify-end">
            <div class="relative" id="dashboard-messages-icon-wrapper">
                @php
                    $unreadInquiriesCount = isset($inquiries) ? $inquiries->whereNull('read_at')->count() : 0;
                    $pendingReportsTotal = isset($pendingReportsCount) ? $pendingReportsCount : (isset($reports) ? $reports->where('status', 'pending')->count() : 0);
                    $overdueRentTotal = isset($overdueRentCount) ? $overdueRentCount : 0;
                    $totalAdminAlerts = $unreadInquiriesCount + $pendingReportsTotal + $overdueRentTotal;
                @endphp
                <button type="button" id="dashboard-messages-icon" class="token-btn token-btn-secondary relative" title="Notifications &amp; Reports" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if ($totalAdminAlerts > 0)
                        <span id="dashboard-messages-icon-badge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full token-badge-danger px-1 !text-[10px]">{{ $totalAdminAlerts }}</span>
                    @endif
                </button>
                <div id="dashboard-messages-dropdown" class="hidden absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 overflow-hidden rounded-2xl token-dropdown">
                    <div class="border-b border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] px-3.5 py-2.5 flex items-center justify-between">
                        <p class="token-muted text-[10px] font-black uppercase tracking-wider">Admin Alerts &amp; Notifications</p>
                        @if ($totalAdminAlerts > 0)
                            <span class="token-badge token-badge-danger">{{ $totalAdminAlerts }} new</span>
                        @endif
                    </div>

                    {{-- Section: Overdue Rent Installments Alert --}}
                    @if ($overdueRentTotal > 0)
                        <div class="border-b border-[var(--token-border-subtle)] bg-[var(--token-danger-surface)] p-3">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5">
                                    <span>⚠️</span>
                                    <span>{{ $overdueRentTotal }} Overdue Rent Installment(s) Pending Collection</span>
                                </span>
                                <span class="token-badge token-badge-danger">Action Needed</span>
                            </div>
                            <p class="text-[11px] mt-1 leading-snug">
                                Lease tenants have past-due installments. Follow up and record payment or issue notice.
                            </p>
                            <a href="#leases" data-target-section="leases" class="nav-section-link inline-flex items-center gap-1 text-[11px] font-bold underline mt-1.5 cursor-pointer">
                                <span>Review Overdue Leases &rarr;</span>
                            </a>
                        </div>
                    @endif

                    {{-- Section: Direct Client Incident & Misleading Reports --}}
                    @if ($pendingReportsTotal > 0)
                        <div class="border-b border-[var(--token-border-subtle)] bg-[var(--token-danger-surface)] p-3">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                    <span>🚨</span>
                                    <span>Client Safety Reports &bull; {{ $pendingReportsTotal }} Client Incident &amp; Misleading Report(s) Awaiting Review</span>
                                </span>
                                <span class="token-badge token-badge-danger">{{ $pendingReportsTotal }} pending</span>
                            </div>
                            <p class="text-[11px] mt-1 leading-snug">
                                Clients have submitted safety reports regarding potential fraud, fake listings, inaccurate prices, or broker misconduct.
                            </p>
                            @if (isset($reports) && $reports->where('status', 'pending')->isNotEmpty())
                                <div class="space-y-1 mt-2 max-h-36 overflow-y-auto">
                                    @foreach ($reports->where('status', 'pending')->take(3) as $pendingRep)
                                        <a href="#reports" data-target-section="reports" class="nav-section-link block rounded-xl bg-[var(--token-surface)] p-2 border border-[var(--token-border-subtle)] text-left transition hover:bg-[var(--token-surface-hover)] shadow-2xs">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-bold text-xs truncate">#{{ $pendingRep->report_number }}: {{ $pendingRep->reason_label }}</span>
                                                <span class="token-muted text-[9px] shrink-0">{{ $pendingRep->created_at?->diffForHumans() }}</span>
                                            </div>
                                            <p class="token-muted text-[10px] truncate mt-0.5">
                                                From: {{ $pendingRep->user?->name ?: ($pendingRep->contact_email ?: 'Client') }}
                                                @if ($pendingRep->property) — {{ $pendingRep->property->title }} @endif
                                            </p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <a href="#reports" data-target-section="reports" class="nav-section-link inline-flex items-center gap-1 text-[11px] font-bold underline mt-2 cursor-pointer">
                                <span>Investigate Reports &rarr;</span>
                            </a>
                        </div>
                    @endif

                    {{-- Section: Latest Client Messages --}}
                    <div class="p-2">
                        <p class="px-2 py-1 token-muted text-[10px] font-black uppercase tracking-wider">Client Inquiries &amp; Messages</p>
                        @php $recentInquiries = isset($inquiries) ? $inquiries->take(3) : collect(); @endphp
                        <div class="max-h-52 overflow-y-auto space-y-1">
                            @forelse ($recentInquiries as $recentInquiry)
                                <button type="button" class="dashboard-messages-nav-item block w-full rounded-xl px-2.5 py-2 text-left transition hover:bg-[var(--token-surface-hover)]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-xs font-black">{{ $recentInquiry->subject }}</span>
                                        <span class="shrink-0 token-muted text-[10px]">{{ $recentInquiry->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <span class="token-muted mt-0.5 block truncate text-[11px] font-semibold">{{ $recentInquiry->name }}</span>
                                    <span class="token-muted mt-0.5 line-clamp-1 block text-[11px]">{{ $recentInquiry->message }}</span>
                                </button>
                            @empty
                                <p class="px-2 py-2 token-muted">No messages yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-3 border-t border-[var(--token-border-subtle)] bg-[var(--token-surface-subtle)] text-center text-[10px] font-bold uppercase tracking-wider">
                        <a href="#leases" data-target-section="leases" class="nav-section-link py-2.5 hover:bg-[var(--token-surface-hover)] border-r border-[var(--token-border-subtle)]">Overdue Leases →</a>
                        <a href="#reports" data-target-section="reports" class="nav-section-link py-2.5 hover:bg-[var(--token-surface-hover)] border-r border-[var(--token-border-subtle)]">Client Reports →</a>
                        <button type="button" class="dashboard-messages-nav-item py-2.5 hover:bg-[var(--token-surface-hover)]">All Messages →</button>
                    </div>
                </div>
            </div>
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
    @include('dashboard.partials.bank-accounts-desk')
    @include('dashboard.partials.careers-blog-desk')
    @include('dashboard.partials.activity-logs-desk')
@endsection

@section('scripts')
    <script>
        window.GOOGLE_MAPS_API_KEY = @json(config('services.google.maps_api_key') ?: '');
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @include('dashboard.partials.scripts')
    @include('dashboard.partials.modals')
    @include('properties.partials.payment-modal')
    @include('layouts.partials.confirm-modal')
@endsection
