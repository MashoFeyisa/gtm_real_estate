<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $persona['label'] }} Dashboard | {{ config('app.name', 'GTM Real Estate') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    @php
        $deskUser = auth()->user();

        $formatCell = function (string $columnKey, mixed $value): string {
            if ($value === null || $value === '') {
                return '—';
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            if (str_contains($columnKey, '_at') || str_contains($columnKey, 'date')) {
                $timestamp = strtotime((string) $value);

                return $timestamp ? date('M d, Y', $timestamp) : (string) $value;
            }

            if (in_array($columnKey, ['price', 'amount', 'amount_due', 'amount_paid', 'monthly_rent', 'offered_price', 'total_amount', 'security_deposit'], true)) {
                return number_format((float) $value).' ETB';
            }

            if (str_contains($columnKey, 'status') || str_contains($columnKey, 'priority') || str_contains($columnKey, 'type')) {
                return ucwords(str_replace('_', ' ', (string) $value));
            }

            return (string) $value;
        };
    @endphp
    <div class="flex min-h-screen">
        <aside id="persona-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] hidden lg:flex flex-col justify-between overflow-y-auto" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
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

                <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#d4af37]">Active Desk</p>
                    <p class="text-sm font-black text-white truncate">{{ $persona['label'] }} Dashboard</p>
                    <p class="text-[11px] text-[#dfeee4]/80">👤 {{ $deskUser->name }}</p>
                </div>

                <nav class="mt-6 space-y-1.5 min-h-[420px]" aria-label="{{ $persona['label'] }} dashboard navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                        <span>🏠</span>
                        <span>Home</span>
                    </a>
                    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center justify-between rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
                        <span class="flex items-center gap-2.5">
                            <span>📊</span>
                            <span>Overview</span>
                        </span>
                    </a>
                    @foreach ($sections as $section)
                        <a href="#section-{{ $section['key'] }}" data-target-section="section-{{ $section['key'] }}" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>{{ $section['icon'] }}</span>
                                <span>{{ $section['label'] }}</span>
                            </span>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $sectionData[$section['key']]['count'] ?? 0 }}</span>
                        </a>
                    @endforeach

                    @include('layouts.partials.authorized-sidebar-links')
                </nav>
            </div>

            @include('dashboard.partials.signed-in-user')
        </aside>

        <main class="flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            @if (session('success'))
                <div class="mb-6 flex items-center gap-2.5 rounded-2xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3.5 text-sm font-medium text-[#214f3a] shadow-xs">
                    <span class="text-base">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm font-medium text-rose-800 shadow-xs">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Overview --}}
            <section id="overview" data-section="overview" class="section-panel scroll-mt-24">
                <div class="rounded-[1.75rem] border border-[#d9cab3] bg-gradient-to-br from-[#1d3c34] to-[#254d43] p-6 text-white shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-[#d4af37]">{{ $persona['label'] }} Dashboard</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-white">Welcome back, {{ $deskUser->name }}.</h1>
                    <p class="mt-2 max-w-2xl text-sm text-[#dfeee4]">{{ $persona['description'] }}</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $sections->count() }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Desk Sections</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $grantedCount }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Granted Capabilities</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $openQueueCount }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Open Items</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Role Sections --}}
            @foreach ($sections as $section)
                @php
                    $data = $sectionData[$section['key']] ?? ['count' => 0, 'columns' => [], 'rows' => collect()];
                    $grantedLabels = collect($section['permissions'] ?? [])
                        ->filter(fn (string $permission) => $deskUser->hasPermission($permission))
                        ->map(fn (string $permission) => $persona['permissions'][$permission] ?? $permission)
                        ->values();
                @endphp
                <section id="section-{{ $section['key'] }}" data-section="section-{{ $section['key'] }}" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-[#1d3c34]">{{ $section['icon'] }} {{ $section['label'] }}</h2>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($grantedLabels as $label)
                                    <span class="rounded-full bg-[#edf9ee] px-2.5 py-0.5 text-[11px] font-bold text-[#214f3a]">✓ {{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#9b6c17]">{{ $data['count'] }} items</span>
                    </div>

                    @if (! empty($data['createForm']))
                        <form action="{{ $data['createForm']['route'] }}" method="POST" class="mt-5 grid gap-2 rounded-2xl border border-[#e7ddca] bg-[#fbf8f3] p-4 sm:grid-cols-2 lg:grid-cols-6">
                            @csrf
                            <select name="property_id" required class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                @foreach ($data['propertyOptions'] ?? [] as $propertyOption)
                                    <option value="{{ $propertyOption->id }}">{{ $propertyOption->title }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="title" required maxlength="255" placeholder="Issue title" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                            <select name="category" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                @foreach (['plumbing', 'electrical', 'internet', 'appliance', 'structural', 'cleaning', 'other'] as $ticketCategory)
                                    <option value="{{ $ticketCategory }}">{{ ucfirst($ticketCategory) }}</option>
                                @endforeach
                            </select>
                            <select name="priority" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs">
                                @foreach (['low', 'medium', 'high', 'emergency'] as $ticketPriority)
                                    <option value="{{ $ticketPriority }}">{{ ucfirst($ticketPriority) }}</option>
                                @endforeach
                            </select>
                            <textarea name="description" required maxlength="3000" placeholder="Describe the issue" class="rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs lg:col-span-2"></textarea>
                            <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#254d43] cursor-pointer">{{ $data['createForm']['label'] }}</button>
                        </form>
                    @endif
                    @if ($data['rows']->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                            <span class="text-3xl">{{ $section['icon'] }}</span>
                            <p class="mt-2 text-sm font-semibold text-[#587165]">Nothing here yet — this queue is empty.</p>
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                    <tr>
                                        @foreach ($data['columns'] as $column)
                                            <th class="p-3">{{ $column['label'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8]">
                                    @foreach ($data['rows'] as $row)
                                        <tr class="transition hover:bg-[#fbf8f3]">
                                            @foreach ($data['columns'] as $column)
                                                <td class="p-3 {{ str_contains($column['key'], 'status') || str_contains($column['key'], 'priority') ? 'font-bold' : '' }} {{ str_contains($column['key'], 'status') ? 'text-[#1d3c34]' : 'text-[#587165]' }}">
                                                    {{ $formatCell($column['key'], data_get($row, $column['key'])) }}
                                                </td>
                                            @endforeach
                                            @if (! empty($data['actions']['listing_moderation']))
                                                <td class="p-3">
                                                    <form action="{{ route('moderation.listings.decision', $row) }}" method="POST" class="flex flex-wrap gap-1">
                                                        @csrf
                                                        <button type="submit" name="decision" value="approve" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Approve</button>
                                                        <button type="submit" name="decision" value="publish" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Publish</button>
                                                        <button type="submit" name="decision" value="reject" class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-rose-700 cursor-pointer">Reject</button>
                                                        <button type="submit" name="decision" value="suspend" class="rounded-full bg-slate-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-slate-600 cursor-pointer">Suspend</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['resolve_report']))
                                                <td class="p-3">
                                                    <form action="{{ route('reports.update', $row) }}" method="POST" class="flex flex-col gap-1.5">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="resolution_notes" maxlength="2000" placeholder="Resolution notes" class="w-44 rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                        <div class="flex gap-1">
                                                            <button type="submit" name="status" value="resolved" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Resolve</button>
                                                            <button type="submit" name="status" value="dismissed" class="rounded-full bg-slate-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-slate-600 cursor-pointer">Dismiss</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['review_moderation']))
                                                <td class="p-3">
                                                    <form action="{{ route('moderation.reviews.decision', $row) }}" method="POST" class="flex gap-1">
                                                        @csrf
                                                        <button type="submit" name="decision" value="approve" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Keep</button>
                                                        <button type="submit" name="decision" value="hide" class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-rose-700 cursor-pointer">Hide</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['document_decision']))
                                                <td class="p-3">
                                                    @if ($row->property)
                                                        <form action="{{ route('properties.documents.update-status', [$row->property, $row]) }}" method="POST" class="flex gap-1">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" name="status" value="verified" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Verify</button>
                                                            <button type="submit" name="status" value="rejected" class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-rose-700 cursor-pointer">Reject</button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-slate-400">Property unavailable</span>
                                                    @endif
                                                </td>
                                            @elseif (! empty($data['actions']['company_verification']))
                                                <td class="p-3">
                                                    <form action="{{ route('verification.companies.decision', $row) }}" method="POST" class="flex gap-1">
                                                        @csrf
                                                        <button type="submit" name="decision" value="verify" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Verify</button>
                                                        <button type="submit" name="decision" value="revoke" class="rounded-full bg-slate-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-slate-600 cursor-pointer">Revoke</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['developer_verification']))
                                                <td class="p-3">
                                                    <form action="{{ route('verification.developers.decision', $row) }}" method="POST" class="flex gap-1">
                                                        @csrf
                                                        <button type="submit" name="decision" value="verify" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Verify</button>
                                                        <button type="submit" name="decision" value="revoke" class="rounded-full bg-slate-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-slate-600 cursor-pointer">Revoke</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['maintenance_manage']))
                                                <td class="p-3">
                                                    <form action="{{ route('maintenance.update-status', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['open', 'assigned', 'in_progress', 'completed', 'closed'] as $ticketStatus)
                                                                <option value="{{ $ticketStatus }}" {{ $row->status === $ticketStatus ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $ticketStatus)) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['housekeeping_manage']))
                                                <td class="p-3">
                                                    <form action="{{ route('housekeeping-requests.update-status', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $serviceStatus)
                                                                <option value="{{ $serviceStatus }}" {{ $row->status === $serviceStatus ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $serviceStatus)) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['rent_payment']))
                                                <td class="p-3">
                                                    <form action="{{ route('rent-schedules.record-payment', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        <input type="number" name="amount" min="0" step="0.01" value="{{ max(0, (float) $row->amount_due - (float) $row->amount_paid) }}" class="w-24 rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                        <select name="payment_method" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['telebirr', 'cbe_birr', 'bank_transfer', 'chapa', 'cash'] as $paymentMethod)
                                                                <option value="{{ $paymentMethod }}">{{ ucwords(str_replace('_', ' ', $paymentMethod)) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Record</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['service_request_response']))
                                                <td class="p-3">
                                                    <form action="{{ route('housekeeping-requests.update-status', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $serviceStatus)
                                                                <option value="{{ $serviceStatus }}" {{ $row->status === $serviceStatus ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $serviceStatus)) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['ticket_triage']))
                                                <td class="p-3">
                                                    <form action="{{ route('inquiries.update-status', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['new', 'contacted', 'viewing', 'negotiating', 'offer', 'closed', 'lost'] as $triageStatus)
                                                                <option value="{{ $triageStatus }}" {{ $row->status === $triageStatus ? 'selected' : '' }}>{{ ucfirst($triageStatus) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                                                    </form>
                                                </td>
                                            @elseif (! empty($data['actions']['booking_status']))
                                                <td class="p-3">
                                                    <form action="{{ route('bookings.update-status', $row) }}" method="POST" class="flex items-center gap-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                            @foreach (['pending', 'confirmed', 'checked_in', 'checked_out', 'completed', 'cancelled'] as $bookingStatus)
                                                                <option value="{{ $bookingStatus }}" {{ $row->status === $bookingStatus ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $bookingStatus)) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Save</button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach
        </main>
    </div>

    <script>
        const navLinks = document.querySelectorAll('.nav-section-link');
        const panels = document.querySelectorAll('.section-panel');

        function activateSection(sectionName) {
            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.section !== sectionName);
            });

            navLinks.forEach(link => {
                const active = link.dataset.targetSection === sectionName;
                link.classList.toggle('bg-white/10', active);
                link.classList.toggle('text-white', active);
                link.classList.toggle('text-[#dfeee4]', !active);
            });
        }

        navLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                const target = this.dataset.targetSection;
                if (!target) {
                    return;
                }
                event.preventDefault();
                activateSection(target);
                history.replaceState(null, '', '#' + target);
            });
        });

        const initialHash = window.location.hash.replace('#', '');
        if (initialHash && document.querySelector('[data-section="' + initialHash + '"]')) {
            activateSection(initialHash);
        }
    </script>
    @include('layouts.partials.sidebar-scroll-preserver')
</body>
</html>
