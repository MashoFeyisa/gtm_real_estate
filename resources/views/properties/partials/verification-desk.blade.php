{{-- Legal Verification & Document Audit Desk --}}
@php
    $allPropertiesForAudit = \App\Models\Property::with(['agent', 'documents'])->latest()->get();
    $allDocumentsForAudit = \App\Models\PropertyDocument::with(['property', 'uploader'])->latest()->take(12)->get();
    $level4And5Count = $allPropertiesForAudit->where('verification_level', '>=', 4)->count();
    $level3Count = $allPropertiesForAudit->where('verification_level', 3)->count();
    $pendingDocsCount = $allDocumentsForAudit->where('status', 'pending')->count();
@endphp

<section id="verifications" data-section="verifications" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-[#102b25] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider text-[#d4af37]">Trust &amp; Safety</span>
                <h2 class="text-2xl font-black text-[#1d3c34]">Legal Verification &amp; Document Audit Desk</h2>
            </div>
            <p class="mt-1 text-xs text-slate-500">Audit Ethiopian land title deeds (Carta), site plans (Mapa), verify property authenticity, and grant certified badges.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fffdfa] p-4">
            <span class="text-xs font-bold uppercase tracking-wider text-[#587165]">Total Properties</span>
            <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ $allPropertiesForAudit->count() }}</p>
            <span class="text-[11px] text-slate-500">National Master Registry</span>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Certified Properties</span>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ $level4And5Count }}</p>
            <span class="text-[11px] text-emerald-600">Level 4 &amp; Level 5 Verified</span>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Docs Submitted</span>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ $level3Count }}</p>
            <span class="text-[11px] text-amber-600">Awaiting Professional Review</span>
        </div>
        <div class="rounded-2xl border border-[#d9cab3] bg-[#fdfaf5] p-4">
            <span class="text-xs font-bold uppercase tracking-wider text-[#9b6c17]">Pending Documents</span>
            <p class="mt-2 text-2xl font-black text-[#9b6c17]">{{ $pendingDocsCount }}</p>
            <span class="text-[11px] text-slate-500">Document Queue To Inspect</span>
        </div>
    </div>

    {{-- Document Queue for Review --}}
    @if ($allDocumentsForAudit->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-sm font-black uppercase tracking-wider text-[#1d3c34] mb-3">
                Submitted Legal Documents Review Queue ({{ $allDocumentsForAudit->count() }})
            </h3>
            <div class="overflow-x-auto rounded-2xl border border-[#d9cab3]/80">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#102b25] text-white">
                        <tr>
                            <th class="px-4 py-3 font-bold uppercase">Document</th>
                            <th class="px-4 py-3 font-bold uppercase">Type</th>
                            <th class="px-4 py-3 font-bold uppercase">Property</th>
                            <th class="px-4 py-3 font-bold uppercase">Access Privacy</th>
                            <th class="px-4 py-3 font-bold uppercase">Status</th>
                            <th class="px-4 py-3 font-bold uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                        @foreach ($allDocumentsForAudit as $doc)
                            <tr class="hover:bg-[#fffdfa]">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-[#1d3c34]">{{ $doc->title }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $doc->file_name }} ({{ $doc->formatted_size }})</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-[#f0e4cf] px-2 py-0.5 font-bold text-[#1d3c34]">
                                        {{ $doc->formatted_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($doc->property)
                                        <a href="{{ route('properties.show', $doc->property->slug) }}" class="font-bold text-[#1d3c34] hover:underline" target="_blank">
                                            {{ $doc->property->title }}
                                        </a>
                                        <div class="font-mono text-[10px] text-slate-400">{{ $doc->property->property_code }}</div>
                                    @else
                                        <span class="text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 capitalize text-slate-600">
                                    {{ str_replace('_', ' ', $doc->access_level) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($doc->status === 'verified')
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-black uppercase text-emerald-800 text-[10px]">Verified</span>
                                    @elseif ($doc->status === 'rejected')
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 font-black uppercase text-red-800 text-[10px]">Rejected</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 font-black uppercase text-amber-800 text-[10px]">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($doc->property)
                                            <a href="{{ route('properties.documents.download', [$doc->property, $doc]) }}" class="rounded-xl border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-bold text-[#1d3c34] hover:bg-[#f5ecdd] transition">
                                                Download
                                            </a>
                                            @if ($doc->status !== 'verified')
                                                <form method="POST" action="{{ route('properties.documents.update-status', [$doc->property, $doc]) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="verified">
                                                    <button type="submit" class="rounded-xl bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($doc->status !== 'rejected')
                                                <form method="POST" action="{{ route('properties.documents.update-status', [$doc->property, $doc]) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 transition cursor-pointer">
                                                        Reject
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Properties Verification Certification Register --}}
    <div class="mt-8">
        <h3 class="text-sm font-black uppercase tracking-wider text-[#1d3c34] mb-3">
            Property Registry Verification Status
        </h3>
        <div class="overflow-x-auto rounded-2xl border border-[#d9cab3]/80">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#102b25] text-white">
                    <tr>
                        <th class="px-4 py-3 font-bold uppercase">Asset Code</th>
                        <th class="px-4 py-3 font-bold uppercase">Property</th>
                        <th class="px-4 py-3 font-bold uppercase">Location</th>
                        <th class="px-4 py-3 font-bold uppercase">Verification Level</th>
                        <th class="px-4 py-3 font-bold uppercase">Title Deed (Carta)</th>
                        <th class="px-4 py-3 font-bold uppercase">Docs Count</th>
                        <th class="px-4 py-3 font-bold uppercase text-right">Certification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0e4cf] bg-white text-slate-700">
                    @foreach ($allPropertiesForAudit as $prop)
                        <tr class="hover:bg-[#fffdfa]">
                            <td class="px-4 py-3 font-mono font-bold text-[#1d3c34] whitespace-nowrap">
                                {{ $prop->property_code }}
                            </td>
                            <td class="px-4 py-3 font-bold text-[#1d3c34]">
                                <a href="{{ route('properties.show', $prop->slug) }}" class="hover:underline">
                                    {{ $prop->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $prop->address ?? $prop->city }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="rounded-full px-2.5 py-0.5 font-bold text-[10px] {{ $prop->verification_level >= 4 ? 'bg-emerald-100 text-emerald-800' : ($prop->verification_level >= 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $prop->verification_level_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-600">
                                {{ $prop->title_deed_number ?? 'Pending' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-[#f0e4cf] px-2 py-0.5 font-bold text-[#1d3c34]">
                                    {{ $prop->documents->count() }} docs
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" onclick="openVerificationModal({{ json_encode($prop) }})" class="rounded-xl bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-[#d4af37] hover:bg-[#254d43] transition cursor-pointer">
                                    Certify / Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('properties.partials.property-verification-modal')
</section>
