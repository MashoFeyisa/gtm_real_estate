@extends('app')

@section('content')
<div class="min-h-screen bg-slate-100 py-12 px-4 sm:px-6 lg:px-8 print:bg-white print:py-0">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 print:border-none print:shadow-none">
        
        <!-- Statement Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-8 border-b border-slate-200 gap-4">
            <div>
                <span class="text-xs uppercase tracking-wider font-semibold text-blue-600 block mb-1">Commercial Property Management</span>
                <h1 class="text-2xl font-bold text-slate-900">Consolidated Billing Statement</h1>
                <p class="text-sm text-slate-500 mt-1">Invoice: <strong>{{ $invoiceNumber }}</strong></p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-xs text-slate-400 block">Statement Date</span>
                <span class="text-sm font-semibold text-slate-800">{{ $statementDate }}</span>
                <button onclick="window.print()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-medium text-slate-700 transition print:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Statement
                </button>
            </div>
        </div>

        <!-- Billed Entity Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Billed To (Corporate Tenant)</span>
                <h3 class="text-lg font-bold text-slate-900">{{ $tenant->company_name }}</h3>
                <p class="text-sm text-slate-600 mt-1">TIN / Tax ID: {{ $tenant->tax_id ?? 'N/A' }}</p>
                <p class="text-sm text-slate-600">{{ $tenant->billing_address ?? $tenant->city }}</p>
                <p class="text-sm text-slate-600">Attn: {{ $tenant->contact_person }} ({{ $tenant->contact_email }})</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Remittance Instructions</span>
                <p class="text-sm text-slate-700 font-medium">Commercial Bank of Ethiopia (CBE)</p>
                <p class="text-sm text-slate-600">Account: 1000293847561</p>
                <p class="text-sm text-slate-600">Telebirr Merchant: 884729</p>
                <p class="text-xs text-slate-500 mt-2">All payments subject to Ethiopian VAT declaration.</p>
            </div>
        </div>

        <!-- Itemized Occupant Lease Table -->
        <div class="py-8">
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Itemized Unit Allocations</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-800 text-xs font-semibold uppercase tracking-wider border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Unit / Residence</th>
                            <th class="py-3 px-4">Occupant Employee</th>
                            <th class="py-3 px-4">Staff ID</th>
                            <th class="py-3 px-4 text-right">Monthly Rent (ETB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $item)
                            <tr>
                                <td class="py-3.5 px-4 font-medium text-slate-900">{{ $item->unit_identifier }}</td>
                                <td class="py-3.5 px-4">{{ $item->employee->full_name }}</td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $item->employee->employee_id_number }}</td>
                                <td class="py-3.5 px-4 text-right font-medium text-slate-900">{{ number_format($item->monthly_rent_etb, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">No active lease assignments.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="border-t border-slate-200 pt-6 flex justify-end">
            <div class="w-full sm:w-72 space-y-3">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-medium text-slate-900">{{ number_format($subtotal, 2) }} ETB</span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Ethiopian VAT ({{ (float) $vatRate }}%):</span>
                    <span class="font-medium text-slate-900">{{ number_format($vat, 2) }} ETB</span>
                </div>
                <div class="border-t border-slate-200 pt-3 flex justify-between text-base font-bold text-slate-900">
                    <span>Total Amount Due:</span>
                    <span class="text-blue-600">{{ number_format($total, 2) }} ETB</span>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="mt-12 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            Official receipt generated by GTM Real Estate Property Management System &bull; Addis Ababa, Ethiopia
        </div>

    </div>
</div>
@endsection
