@extends('app')

@section('content')
<div class="min-h-screen bg-slate-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 mb-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Corporate Tenancy &amp; Workforce Housing Portal
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                        {{ $tenant->company_name ?? 'Corporate Tenant Portal' }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        TIN: {{ $tenant->tax_id ?? 'N/A' }} &bull; Industry: {{ $tenant->industry ?? 'Enterprise' }} &bull; City: {{ $tenant->city ?? 'Addis Ababa' }}
                    </p>
                </div>
                @if($tenant)
                <div class="flex items-center gap-3">
                    <a href="{{ route('corporate.billing-statement', $tenant) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-800 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Consolidated Statement
                    </a>
                </div>
                @endif
            </div>

            <!-- Stats Ribbon -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
                <div class="bg-slate-50 rounded-xl p-4">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Housing Staff</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $stats['total_employees'] }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Assigned Leases</span>
                    <span class="text-2xl font-bold text-blue-600 mt-1 block">{{ $stats['active_assignments'] }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Monthly Commitment</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ number_format($stats['total_monthly_commitment_etb']) }} ETB</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Credit Facility</span>
                    <span class="text-2xl font-bold text-emerald-600 mt-1 block">{{ number_format($stats['credit_limit_etb']) }} ETB</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Employee Housing Assignments & Roster -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Current Unit Allocations -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-900 text-lg">Active Unit Allocations</h2>
                        <span class="text-xs text-slate-500">{{ $assignments->count() }} active leases</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($assignments as $assignment)
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">{{ $assignment->unit_identifier }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-medium">Occupied</span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1">
                                        Occupant: <strong class="text-slate-800">{{ $assignment->employee->full_name }}</strong> &bull; Dept: {{ $assignment->employee->department ?? 'General' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        From: {{ $assignment->assigned_from->format('M d, Y') }} @if($assignment->assigned_until) to {{ $assignment->assigned_until->format('M d, Y') }} @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold text-slate-900 block">{{ number_format($assignment->monthly_rent_etb) }} ETB</span>
                                    <span class="text-xs text-slate-500">monthly billing</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 text-sm">
                                No employee housing allocations recorded yet. Use the assignment panel to assign units.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Employee Roster -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-900 text-lg">Eligible Staff Roster</h2>
                        <span class="text-xs text-slate-500">{{ $employees->count() }} personnel</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <div class="p-5 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-semibold text-slate-900 text-sm">{{ $emp->full_name }}</h4>
                                    <p class="text-xs text-slate-500">ID: {{ $emp->employee_id_number }} &bull; {{ $emp->designation ?? 'Staff' }} &bull; {{ $emp->phone }}</p>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">
                                    {{ $emp->department ?? 'Operations' }}
                                </span>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 text-sm">
                                No employees registered in the corporate housing roster yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Col: Actions & Enrollment -->
            <div class="space-y-6">
                <!-- Assign Housing Unit Form -->
                @if($tenant)
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="font-bold text-slate-900 text-base mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Assign Unit to Staff
                    </h3>
                    <form action="{{ route('corporate.assign-lease') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="corporate_tenant_id" value="{{ $tenant->id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Select Employee</label>
                            <select name="corporate_employee_id" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Choose Staff Member --</option>
                                @foreach($tenant->employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_id_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Identifier / Apartment</label>
                            <input type="text" name="unit_identifier" placeholder="e.g. Bole Sun Tower Unit #402" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned From</label>
                                <input type="date" name="assigned_from" value="{{ date('Y-m-d') }}" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Until (Optional)</label>
                                <input type="date" name="assigned_until" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Monthly Rent Allocation (ETB)</label>
                            <input type="number" step="0.01" name="monthly_rent_etb" placeholder="e.g. 45000" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition shadow-sm">
                            Confirm Unit Assignment
                        </button>
                    </form>
                </div>

                <!-- Add Employee to Roster Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="font-bold text-slate-900 text-base mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        Enroll New Staff Member
                    </h3>
                    <form action="{{ route('corporate.employees.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="corporate_tenant_id" value="{{ $tenant->id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" placeholder="e.g. Abebe Kebede" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Staff ID</label>
                                <input type="text" name="employee_id_number" placeholder="EMP-2026-09" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Phone</label>
                                <input type="text" name="phone" placeholder="+251 91 123 4567" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" placeholder="staff@company.et" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                                <input type="text" name="department" placeholder="Engineering" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Designation</label>
                                <input type="text" name="designation" placeholder="Lead Architect" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 text-slate-800">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition shadow-sm">
                            Add to Housing Roster
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
