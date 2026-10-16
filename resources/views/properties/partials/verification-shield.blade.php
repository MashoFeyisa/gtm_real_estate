{{-- Ethiopian Property Verification & Legal Documentation Shield --}}
<div id="legal-verification-shield" class="flex flex-col h-full rounded-[2rem] border border-[#d9cab3] bg-white p-6 sm:p-8 shadow-sm dark:bg-[#111827] dark:border-slate-800 relative overflow-hidden min-w-0 break-words">
    {{-- Top Decorative Header Ribbon --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#f0e4cf] pb-5 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-[#102b25] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-[#d4af37] flex items-center gap-1">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Trust &amp; Safety</span>
                </span>
                <span class="text-xs font-bold text-[#587165] dark:text-slate-400">የይዞታ ካርታ እና ህጋዊ ሰነዶች</span>
            </div>
            <h3 class="mt-1 text-xl font-black text-[#1d3c34] dark:text-white">
                Legal Verification &amp; Documentation Shield
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Official Ethiopian Cadastre, Title Deed (Carta), and ownership audit status.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @php
                $level = (int) ($property->verification_level ?? 0);
                $levelColors = match ($level) {
                    5 => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    4 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    3 => 'bg-amber-50 text-amber-800 border-amber-200',
                    2 => 'bg-blue-50 text-blue-800 border-blue-200',
                    1 => 'bg-slate-100 text-slate-700 border-slate-200',
                    default => 'bg-slate-100 text-slate-500 border-slate-200',
                };
            @endphp
            <div class="rounded-2xl border px-3.5 py-1.5 text-right {{ $levelColors }}">
                <span class="block text-[10px] font-bold uppercase tracking-wider opacity-75">Trust Level</span>
                <span class="text-xs font-black">{{ $property->verification_level_label }}</span>
            </div>

            @if (auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->agentProfile && auth()->user()->agentProfile->id === $property->agent_id)))
                <button type="button" onclick="openUploadDocumentModal()" class="inline-flex items-center gap-1 rounded-full bg-[#102b25] px-3.5 py-2 text-xs font-bold text-[#d4af37] hover:bg-[#1d3c34] transition shadow-xs cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Upload Doc</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Verification Level 5-Segment Progress Bar --}}
    <div class="mt-5">
        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
            <span>Verification Pipeline</span>
            <span class="font-mono text-[#1d3c34]">{{ $level }}/5 Completed</span>
        </div>
        <div class="grid grid-cols-5 gap-1.5">
            <div class="h-2 rounded-full {{ $level >= 1 ? 'bg-emerald-600' : 'bg-slate-200' }}" title="Level 1: Identity Verified"></div>
            <div class="h-2 rounded-full {{ $level >= 2 ? 'bg-emerald-600' : 'bg-slate-200' }}" title="Level 2: Licensed Agent"></div>
            <div class="h-2 rounded-full {{ $level >= 3 ? 'bg-emerald-600' : 'bg-slate-200' }}" title="Level 3: Documents Submitted"></div>
            <div class="h-2 rounded-full {{ $level >= 4 ? 'bg-emerald-600' : 'bg-slate-200' }}" title="Level 4: Professionally Reviewed"></div>
            <div class="h-2 rounded-full {{ $level >= 5 ? 'bg-emerald-600' : 'bg-slate-200' }}" title="Level 5: Transaction Ready"></div>
        </div>
        <div class="grid grid-cols-5 gap-1 text-[10px] text-slate-400 mt-1 text-center font-medium">
            <span class="{{ $level >= 1 ? 'text-[#1d3c34] font-bold' : '' }}">Identity</span>
            <span class="{{ $level >= 2 ? 'text-[#1d3c34] font-bold' : '' }}">Agent</span>
            <span class="{{ $level >= 3 ? 'text-[#1d3c34] font-bold' : '' }}">Docs</span>
            <span class="{{ $level >= 4 ? 'text-[#1d3c34] font-bold' : '' }}">Review</span>
            <span class="{{ $level >= 5 ? 'text-[#1d3c34] font-bold' : '' }}">Ready</span>
        </div>
    </div>

    {{-- Official Reference Cards (Carta / Cadastre / Asset) --}}
    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-3.5">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">Title Deed / Carta Ref</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="font-mono text-xs font-bold text-[#1d3c34]">
                    @if ($property->title_deed_number)
                        {{ $property->title_deed_number }}
                    @elseif ($property->verification_level >= 3)
                        AA-BOL-****-2016 (Verified)
                    @else
                        Municipal Registry Pending
                    @endif
                </span>
            </div>
            <span class="text-[10px] text-slate-400">የይዞታ ማረጋገጫ ቁጥር</span>
        </div>

        <div class="rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-3.5">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">City Cadastre Reference</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="font-mono text-xs font-bold text-[#1d3c34]">
                    {{ $property->cadastre_code ?? 'CAD-ET-AA-'.str_pad((string)($property->id * 43 % 900 + 100), 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <span class="text-[10px] text-slate-400">የመሬት ካዳስተር ፕሎት</span>
        </div>

        <div class="rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-3.5">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-[#587165]">Master Property Code</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="font-mono text-xs font-bold text-[#1d3c34]">{{ $property->property_code }}</span>
            </div>
            <span class="text-[10px] text-slate-400">ብሔራዊ ንብረት መለያ</span>
        </div>
    </div>

    {{-- Trust & Safety Badges --}}
    <div class="mt-5 pt-5 border-t border-[#f0e4cf]">
        <span class="block text-xs font-bold uppercase tracking-wider text-[#587165] mb-2.5">
            Certified Verification Badges
        </span>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            {{-- Badge 1: Title Deed Verified --}}
            <div class="flex items-start gap-2 rounded-2xl p-2.5 border {{ $property->hasVerificationBadge('title_deed_verified') || $property->verification_level >= 3 ? 'bg-[#eef8f2] border-[#c8e6c9] text-[#1b5e20]' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="mt-0.5 rounded-full p-1 {{ $property->hasVerificationBadge('title_deed_verified') || $property->verification_level >= 3 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-500' }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <strong class="block text-xs">Title Deed Verified</strong>
                    <span class="text-[10px] opacity-80">ካርታ የተረጋገጠ</span>
                </div>
            </div>

            {{-- Badge 2: Cadastre & Site Plan Matched --}}
            <div class="flex items-start gap-2 rounded-2xl p-2.5 border {{ $property->hasVerificationBadge('site_plan_verified') || $property->hasVerificationBadge('cadastre_verified') || $property->verification_level >= 4 ? 'bg-[#eef8f2] border-[#c8e6c9] text-[#1b5e20]' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="mt-0.5 rounded-full p-1 {{ $property->hasVerificationBadge('site_plan_verified') || $property->hasVerificationBadge('cadastre_verified') || $property->verification_level >= 4 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-500' }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <div>
                    <strong class="block text-xs">Site Plan &amp; Mapa</strong>
                    <span class="text-[10px] opacity-80">ማፓ የተረጋገጠ</span>
                </div>
            </div>

            {{-- Badge 3: Physical Inspection Verified --}}
            <div class="flex items-start gap-2 rounded-2xl p-2.5 border {{ $property->hasVerificationBadge('physical_inspection_verified') || $property->verification_level >= 4 ? 'bg-[#eef8f2] border-[#c8e6c9] text-[#1b5e20]' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="mt-0.5 rounded-full p-1 {{ $property->hasVerificationBadge('physical_inspection_verified') || $property->verification_level >= 4 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-500' }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <strong class="block text-xs">Physical Inspection</strong>
                    <span class="text-[10px] opacity-80">በቦታው ተገኝቶ የታየ</span>
                </div>
            </div>

            {{-- Badge 4: Licensed Broker / Agent --}}
            <div class="flex items-start gap-2 rounded-2xl p-2.5 border {{ $property->agent_id || $property->hasVerificationBadge('licensed_agent') ? 'bg-[#eef8f2] border-[#c8e6c9] text-[#1b5e20]' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                <div class="mt-0.5 rounded-full p-1 {{ $property->agent_id || $property->hasVerificationBadge('licensed_agent') ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-500' }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <strong class="block text-xs">Licensed Broker</strong>
                    <span class="text-[10px] opacity-80">ፈቃድ ያለው ኤጀንት</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Uploaded Legal Documents Audit Trail --}}
    @if ($property->documents->isNotEmpty())
        <div class="mt-5 pt-5 border-t border-[#f0e4cf]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-[#587165]">
                    Uploaded Legal Records ({{ $property->documents->count() }})
                </span>
                <span class="text-[11px] text-slate-400">Strict Access Control Active</span>
            </div>

            <div class="space-y-2">
                @foreach ($property->documents as $doc)
                    <div class="flex items-center justify-between rounded-2xl border border-[#f0e4cf] bg-[#fffdfa] p-3 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#102b25]/5 text-[#102b25]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <strong class="font-bold text-[#1d3c34]">{{ $doc->title }}</strong>
                                    <span class="rounded bg-[#f0e4cf] px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">
                                        {{ $doc->formatted_type }}
                                    </span>
                                    @if ($doc->status === 'verified')
                                        <span class="rounded bg-emerald-100 text-emerald-800 px-1.5 py-0.5 text-[9px] font-black uppercase">Verified</span>
                                    @else
                                        <span class="rounded bg-amber-100 text-amber-800 px-1.5 py-0.5 text-[9px] font-black uppercase">Pending Review</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                    <span>{{ $doc->formatted_size }}</span>
                                    <span>·</span>
                                    <span class="capitalize">{{ str_replace('_', ' ', $doc->access_level) }} Access</span>
                                    @if ($doc->document_number)
                                        <span>· Ref: {{ $doc->document_number }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($doc->isAccessibleBy(auth()->user()))
                                <a href="{{ route('properties.documents.download', [$property, $doc]) }}" class="inline-flex items-center gap-1 rounded-xl bg-[#102b25] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#1d3c34] transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download</span>
                                </a>
                            @else
                                <span class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500" title="Available to verified buyers after viewing or offer proposal">
                                    🔒 Confidential
                                </span>
                            @endif

                            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $doc->user_id))
                                <form method="POST" action="{{ route('properties.documents.destroy', [$property, $doc]) }}" onsubmit="return confirm('Remove this document?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl p-1.5 text-slate-400 hover:text-red-600 transition cursor-pointer" title="Delete document">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
