{{-- GTM Real Estate Company - Client CRM Desk --}}
<section id="clients" data-section="clients" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#f0e4cf] pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#1d3c34] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37]">Client CRM Hub</span>
                <span class="text-xs font-bold text-[#587165]">የደንበኞች አስተዳደር</span>
            </div>
            <h2 class="mt-1 text-2xl md:text-3xl font-black text-[#1d3c34]">GTM Real Estate Client Portfolio</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Manage registered buyers, long-term tenants, diaspora investors, and short-stay guests across all branches.
            </p>
        </div>

        @php
            $allClients = isset($clients) ? $clients : collect();
            $buyersCount = $allClients->where('role', 'buyer')->count();
            $tenantsCount = $allClients->where('role', 'tenant')->count();
            $generalClientsCount = $allClients->whereIn('role', ['client', 'user'])->count();
            $totalDealsClosed = isset($orders) ? $orders->count() : 0;
        @endphp

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openAddClientModal()" class="inline-flex items-center gap-2 rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#254d43] cursor-pointer">
                <svg class="h-4 w-4 text-[#d4af37]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>+ Register New Client</span>
            </button>
        </div>
    </div>

    {{-- KPI Cards Ribbon --}}
    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Registered Clients</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-black text-[#1d3c34]">{{ $allClients->count() }}</span>
                <span class="text-xs font-semibold text-emerald-700">In CRM</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Buyers, tenants &amp; investors</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Active Buyers</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-black text-[#102b25]">{{ $buyersCount }}</span>
                <span class="text-xs font-semibold text-amber-700">Property Seekers</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Pursuing sales acquisitions</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Active Tenants</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-black text-[#1d3c34]">{{ $tenantsCount }}</span>
                <span class="text-xs font-semibold text-blue-700">Leaseholders</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Residential &amp; commercial rentals</p>
        </div>

        <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf8f3] p-4 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Deals Closed</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-black text-[#9b6c17]">{{ $totalDealsClosed }}</span>
                <span class="text-xs font-semibold text-[#1d3c34]">Signed Orders</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Lifetime transactions</p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#fdfaf5] p-3.5 rounded-2xl border border-[#ebdcc9]">
        <div class="relative flex-1 max-w-md">
            <input type="text" id="dashboard-clients-search" oninput="filterClientsTable()" placeholder="Search clients by name, email, role..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-3 text-xs text-[#102b25] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-1.5" id="clients-role-filters">
            <button type="button" onclick="filterClientsByRole('all', this)" class="client-filter-btn active rounded-xl px-3 py-1.5 text-xs font-bold transition bg-[#1d3c34] text-white">
                All ({{ $allClients->count() }})
            </button>
            <button type="button" onclick="filterClientsByRole('buyer', this)" class="client-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Buyers ({{ $buyersCount }})
            </button>
            <button type="button" onclick="filterClientsByRole('tenant', this)" class="client-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                Tenants ({{ $tenantsCount }})
            </button>
            <button type="button" onclick="filterClientsByRole('client', this)" class="client-filter-btn rounded-xl px-3 py-1.5 text-xs font-bold transition bg-white border border-[#d9cab3] text-slate-700 hover:bg-[#fbf6ed]">
                General ({{ $generalClientsCount }})
            </button>
        </div>
    </div>

    {{-- Clients Table --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-[#d9cab3] bg-white shadow-xs">
        @if ($allClients->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs text-slate-700">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Client Profile</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">CRM Role</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Contact Email</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Deals / Orders</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-center">Saved Items</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px]">Registered</th>
                            <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[11px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf]" id="clients-table-body">
                        @foreach ($allClients as $client)
                            <tr class="client-table-row transition hover:bg-[#fbf8f3]" data-client-name="{{ strtolower($client->name) }}" data-client-email="{{ strtolower($client->email) }}" data-client-role="{{ strtolower($client->role ?? 'client') }}">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if ($client->photo_url)
                                            <img src="{{ $client->photo_url }}" alt="{{ $client->name }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                        @else
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-white">
                                                {{ $client->initials ?? strtoupper(substr($client->name, 0, 2)) }}
                                            </span>
                                        @endif
                                        <div>
                                            <p class="font-bold text-[#1d3c34]">{{ $client->name }}</p>
                                            <p class="text-[10px] text-slate-400">Client ID #{{ $client->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $roleStyles = match($client->role) {
                                            'buyer' => 'bg-amber-100 text-amber-900 border-amber-300',
                                            'tenant' => 'bg-blue-100 text-blue-900 border-blue-300',
                                            'client' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $roleStyles }}">
                                        {{ ucfirst($client->role ?? 'Client') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-medium text-slate-600">{{ $client->email }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full bg-[#f4ebe1] px-2.5 py-0.5 font-bold text-[#1d3c34]">
                                        {{ $client->orders_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 text-slate-500 font-semibold">
                                        <span>❤️ {{ $client->property_likes_count ?? 0 }}</span>
                                        <span class="text-slate-300">|</span>
                                        <span>⭐ {{ $client->property_favorites_count ?? 0 }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ $client->created_at ? $client->created_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="mailto:{{ $client->email }}" class="rounded-lg border border-[#d9cab3] bg-white p-1.5 text-slate-600 transition hover:bg-[#f1e4cf] hover:text-[#1d3c34]" title="Send Email">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </a>
                                        <button type="button" onclick="openEditClientModal({{ json_encode($client) }})" class="rounded-lg border border-[#d9cab3] bg-white p-1.5 text-slate-600 transition hover:bg-[#f1e4cf] hover:text-[#1d3c34]" title="Edit Client">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ request()->routeIs('company.*') ? route('company.clients.destroy', $client) : route('dashboard.users.delete', $client) }}" onsubmit="return confirm('Are you sure you want to remove this client?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 p-1.5 text-rose-600 transition hover:bg-rose-100" title="Delete Client">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $clients->fragment('clients')->links() }}
                </div>
            @endif
        @else
            <div class="py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-3 text-sm font-semibold text-slate-500">No registered clients yet.</p>
                <button type="button" onclick="openAddClientModal()" class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white hover:bg-[#254d43]">
                    + Register First Client
                </button>
            </div>
        @endif
    </div>
</section>

{{-- Add/Edit Client Modal --}}
<div id="client-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 p-4 backdrop-blur-xs flex items-center justify-center">
    <div class="relative w-full max-w-lg rounded-3xl border border-[#d9cab3] bg-white p-6 shadow-2xl md:p-8">
        <div class="flex items-center justify-between border-b border-[#f0e4cf] pb-4">
            <div>
                <span class="rounded bg-[#1d3c34] px-2 py-0.5 text-[10px] font-black uppercase text-[#d4af37]">GTM CRM</span>
                <h3 id="client-modal-title" class="mt-1 text-xl font-black text-[#1d3c34]">Register New Client</h3>
            </div>
            <button type="button" onclick="closeClientModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="client-form" method="POST" action="{{ route('dashboard.users.store') }}" class="mt-6 space-y-4">
            @csrf
            <div id="client-form-method"></div>

            <div>
                <label for="client_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Full Name *</label>
                <input type="text" id="client_name" name="name" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]" placeholder="e.g. Abebe Balcha">
            </div>

            <div>
                <label for="client_email" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Email Address *</label>
                <input type="email" id="client_email" name="email" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]" placeholder="e.g. abebe@example.com">
            </div>

            <div>
                <label for="client_role" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Client CRM Role *</label>
                <select id="client_role" name="role" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    <option value="client">General Client / Diaspora Investor</option>
                    <option value="buyer">Active Property Buyer</option>
                    <option value="tenant">Rental Tenant</option>
                    <option value="user">Platform User</option>
                </select>
            </div>

            <div id="client-password-field">
                <label for="client_password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Temporary Password *</label>
                <input type="password" id="client_password" name="password" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs text-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]" placeholder="Min. 8 chars, mixed case, number &amp; symbol">
                <p id="client-password-help" class="mt-1 text-[11px] text-slate-400">Default requirement: at least 8 characters with letters, numbers, and symbols.</p>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#f0e4cf] pt-4">
                <button type="button" onclick="closeClientModal()" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2 text-xs font-bold text-slate-700 hover:bg-[#fbf6ed]">Cancel</button>
                <button type="submit" id="client-form-submit-btn" class="rounded-full bg-[#1d3c34] px-6 py-2 text-xs font-bold text-white hover:bg-[#254d43]">Save Client</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddClientModal() {
        const modal = document.getElementById('client-modal');
        const form = document.getElementById('client-form');
        const title = document.getElementById('client-modal-title');
        const methodDiv = document.getElementById('client-form-method');
        const passwordInput = document.getElementById('client_password');
        const passwordHelp = document.getElementById('client-password-help');

        form.action = window.location.pathname.startsWith('/company') ? "{{ route('company.clients.store') }}" : "{{ route('dashboard.users.store') }}";
        methodDiv.innerHTML = '';
        title.textContent = 'Register New Client';
        form.reset();
        passwordInput.required = true;
        passwordHelp.textContent = 'Min. 8 characters with mixed case, number and symbol.';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditClientModal(client) {
        const modal = document.getElementById('client-modal');
        const form = document.getElementById('client-form');
        const title = document.getElementById('client-modal-title');
        const methodDiv = document.getElementById('client-form-method');
        const passwordInput = document.getElementById('client_password');
        const passwordHelp = document.getElementById('client-password-help');

        form.action = window.location.pathname.startsWith('/company') ? `/company/clients/${client.id}` : `/dashboard/users/${client.id}`;
        methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        title.textContent = `Edit Client: ${client.name}`;

        document.getElementById('client_name').value = client.name || '';
        document.getElementById('client_email').value = client.email || '';
        document.getElementById('client_role').value = client.role || 'client';

        passwordInput.required = false;
        passwordHelp.textContent = 'Leave blank to keep existing password.';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeClientModal() {
        const modal = document.getElementById('client-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function filterClientsTable() {
        const query = document.getElementById('dashboard-clients-search').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.client-table-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-client-name') || '';
            const email = row.getAttribute('data-client-email') || '';
            const role = row.getAttribute('data-client-role') || '';
            const matches = name.includes(query) || email.includes(query) || role.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    }

    function filterClientsByRole(role, btnEl) {
        const buttons = document.querySelectorAll('.client-filter-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-[#1d3c34]', 'text-white', 'active');
            btn.classList.add('bg-white', 'text-slate-700');
        });
        const activeBtn = btnEl || event?.currentTarget;
        if (activeBtn) {
            activeBtn.classList.add('bg-[#1d3c34]', 'text-white', 'active');
            activeBtn.classList.remove('bg-white', 'text-slate-700');
        }

        const rows = document.querySelectorAll('.client-table-row');
        rows.forEach(row => {
            const rowRole = row.getAttribute('data-client-role') || '';
            if (role === 'all') {
                row.style.display = '';
            } else if (role === 'client') {
                row.style.display = (rowRole === 'client' || rowRole === 'user') ? '' : 'none';
            } else {
                row.style.display = rowRole === role ? '' : 'none';
            }
        });
    }
</script>
