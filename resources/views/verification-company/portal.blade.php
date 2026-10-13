<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verification Company | {{ config('app.name', 'GTM Real Estate') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    @include('layouts.partials.impersonation-banner')
    <div class="flex min-h-screen">
        <aside id="verification-company-sidebar" class="dashboard-sidebar sidebar-scroll-area w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb] hidden lg:flex flex-col justify-between overflow-y-auto" style="position: sticky; top: 0; height: 100dvh; overflow-y: auto;">
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
                    <p class="text-sm font-black text-white truncate">Verification Company</p>
                    <p class="text-[11px] text-[#dfeee4]/80">👤 {{ auth()->user()->name }}</p>
                </div>

                <nav class="mt-6 space-y-1.5 min-h-[420px]" aria-label="Verification company navigation">
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
                    @if ($canManageJobs || $canVerifyDocuments)
                        <a href="#jobs" data-target-section="jobs" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>🧾</span>
                                <span>Verification Jobs</span>
                            </span>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['open_jobs'] }}</span>
                        </a>
                    @endif
                    @if ($canManageJobs)
                        <a href="#certifications" data-target-section="certifications" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>✅</span>
                                <span>Property Certifications</span>
                            </span>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['pending_certifications'] }}</span>
                        </a>
                    @endif
                    @if ($canSubmitReports)
                        <a href="#reports" data-target-section="reports" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>📄</span>
                                <span>Submitted Reports</span>
                            </span>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['reports_submitted'] }}</span>
                        </a>
                    @endif
                    @if ($canReceivePayments)
                        <a href="#payments" data-target-section="payments" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
                            <span class="flex items-center gap-2.5">
                                <span>💰</span>
                                <span>Verification Payments</span>
                            </span>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['payments'] }}</span>
                        </a>
                    @endif
                </nav>
            </div>

            @include('dashboard.partials.signed-in-user', ['roleLabel' => 'Verification Company'])
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
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-[#d4af37]">Verification Company</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-white">Welcome back, {{ auth()->user()->name }}.</h1>
                    <p class="mt-2 max-w-2xl text-sm text-[#dfeee4]">Property document verification jobs, ownership certifications, submitted reports, and verification billing — everything your firm is licensed to handle.</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $stats['open_jobs'] }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Open Jobs</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $stats['pending_certifications'] }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Pending Certifications</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $stats['reports_submitted'] }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Reports Submitted</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                            <p class="text-2xl font-black text-white">{{ $stats['payments'] }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#dfeee4]">Payments Received</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Verification Jobs --}}
            @if ($canManageJobs || $canVerifyDocuments)
                <section id="jobs" data-section="jobs" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
                        <div>
                            <h2 class="text-xl font-black text-[#1d3c34]">🧾 Verification Jobs</h2>
                            <p class="text-sm text-[#587165]">Property documents submitted for verification — verify or reject each one.</p>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#9b6c17]">{{ $stats['open_jobs'] }} open</span>
                    </div>

                    @if ($documents->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                            <span class="text-3xl">🧾</span>
                            <p class="mt-2 text-sm font-semibold text-[#587165]">No document submissions yet.</p>
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Document</th>
                                        <th class="p-3">Property</th>
                                        <th class="p-3">Submitted</th>
                                        <th class="p-3">Status</th>
                                        @if ($canVerifyDocuments)
                                            <th class="p-3">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8]">
                                    @foreach ($documents as $document)
                                        <tr class="transition hover:bg-[#fbf8f3]">
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ $document->title }}</td>
                                            <td class="p-3 text-[#587165]">{{ $document->property->title ?? '—' }}</td>
                                            <td class="p-3 text-[#587165]">{{ $document->created_at?->format('M d, Y') }}</td>
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ ucfirst($document->status) }}</td>
                                            @if ($canVerifyDocuments)
                                                <td class="p-3">
                                                    @if ($document->property)
                                                        <form action="{{ route('properties.documents.update-status', [$document->property, $document]) }}" method="POST" class="flex gap-1">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" name="status" value="verified" class="rounded-full bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-emerald-700 cursor-pointer">Verify</button>
                                                            <button type="submit" name="status" value="rejected" class="rounded-full bg-rose-600 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-rose-700 cursor-pointer">Reject</button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-slate-400">Property unavailable</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Property Certifications --}}
            @if ($canManageJobs)
                <section id="certifications" data-section="certifications" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
                        <div>
                            <h2 class="text-xl font-black text-[#1d3c34]">✅ Property Certifications</h2>
                            <p class="text-sm text-[#587165]">Certify property verification levels (0–5) after reviewing title, ownership, and boundary records.</p>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#9b6c17]">{{ $stats['pending_certifications'] }} pending</span>
                    </div>

                    @if ($pendingProperties->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                            <span class="text-3xl">✅</span>
                            <p class="mt-2 text-sm font-semibold text-[#587165]">No properties awaiting certification.</p>
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Property</th>
                                        <th class="p-3">City</th>
                                        <th class="p-3">Level</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Certify</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8]">
                                    @foreach ($pendingProperties as $property)
                                        <tr class="transition hover:bg-[#fbf8f3]">
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ $property->title }}</td>
                                            <td class="p-3 text-[#587165]">{{ $property->city ?? '—' }}</td>
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ $property->verification_level }}</td>
                                            <td class="p-3 text-[#587165]">{{ ucfirst($property->verification_status) }}</td>
                                            <td class="p-3">
                                                <form action="{{ route('properties.verification.update', $property) }}" method="POST" class="flex items-center gap-1">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="verification_level" class="rounded-lg border border-[#d9cab3] bg-white px-2 py-1 text-[10px]">
                                                        @for ($level = 0; $level <= 5; $level++)
                                                            <option value="{{ $level }}" {{ $property->verification_level === $level ? 'selected' : '' }}>L{{ $level }}</option>
                                                        @endfor
                                                    </select>
                                                    <button type="submit" name="verification_status" value="verified" class="rounded-full bg-[#1d3c34] px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-[#254d43] cursor-pointer">Certify</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Submitted Reports --}}
            @if ($canSubmitReports)
                <section id="reports" data-section="reports" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
                        <div>
                            <h2 class="text-xl font-black text-[#1d3c34]">📄 Submitted Reports</h2>
                            <p class="text-sm text-[#587165]">Verification reports your firm has submitted.</p>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#9b6c17]">{{ $stats['reports_submitted'] }} reports</span>
                    </div>

                    @if ($submittedReports->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                            <span class="text-3xl">📄</span>
                            <p class="mt-2 text-sm font-semibold text-[#587165]">No reports submitted yet.</p>
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Document</th>
                                        <th class="p-3">Property</th>
                                        <th class="p-3">Verified At</th>
                                        <th class="p-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8]">
                                    @foreach ($submittedReports as $report)
                                        <tr class="transition hover:bg-[#fbf8f3]">
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ $report->title }}</td>
                                            <td class="p-3 text-[#587165]">{{ $report->property->title ?? '—' }}</td>
                                            <td class="p-3 text-[#587165]">{{ $report->verified_at?->format('M d, Y') ?? '—' }}</td>
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ ucfirst($report->status) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Verification Payments --}}
            @if ($canReceivePayments)
                <section id="payments" data-section="payments" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e7ddca] pb-5">
                        <div>
                            <h2 class="text-xl font-black text-[#1d3c34]">💰 Verification Payments</h2>
                            <p class="text-sm text-[#587165]">Service payments recorded for verification work.</p>
                        </div>
                        <span class="rounded-full bg-[#f9ecd0] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#9b6c17]">{{ $stats['payments'] }} received</span>
                    </div>

                    @if ($payments->isEmpty())
                        <div class="mt-6 rounded-2xl border border-dashed border-[#d9cab3] bg-[#fbf8f3] py-12 text-center">
                            <span class="text-3xl">💰</span>
                            <p class="mt-2 text-sm font-semibold text-[#587165]">No verification payments recorded yet.</p>
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-[#e7ddca]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#f9f4ed] text-[10px] font-bold uppercase tracking-wider text-[#1d3c34]">
                                    <tr>
                                        <th class="p-3">Transaction</th>
                                        <th class="p-3">Payer</th>
                                        <th class="p-3">Amount</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e8d8]">
                                    @foreach ($payments as $payment)
                                        <tr class="transition hover:bg-[#fbf8f3]">
                                            <td class="p-3 font-mono text-[11px] font-bold text-[#587165]">{{ $payment->transaction_number }}</td>
                                            <td class="p-3 text-[#587165]">{{ $payment->payer_name }}</td>
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ number_format((float) $payment->amount) }} {{ strtoupper((string) ($payment->currency ?? 'ETB')) }}</td>
                                            <td class="p-3 font-bold text-[#1d3c34]">{{ ucfirst($payment->status) }}</td>
                                            <td class="p-3 text-[#587165]">{{ $payment->created_at?->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
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
