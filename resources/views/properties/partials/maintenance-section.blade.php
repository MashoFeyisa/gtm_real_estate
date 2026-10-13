<div id="property-maintenance-section" class="scroll-mt-24 mt-8 rounded-[2rem] border border-[#d9cab3] bg-white p-6 md:p-8 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f0e4cf] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-blue-900 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-blue-200">Property Management</span>
                <h3 class="text-2xl font-black text-[#1d3c34]">Maintenance & Housekeeping Desk</h3>
            </div>
            <p class="mt-1 text-xs text-slate-500">Report facility issues, schedule preventative repairs, or request housekeeping assistance.</p>
        </div>
        <button type="button" data-auth-gate onclick="document.getElementById('maintenance-form-wrapper').classList.toggle('hidden')" class="rounded-xl border border-[#102b25] bg-[#102b25] px-4 py-2 text-xs font-bold text-[#d4af37] shadow-xs hover:bg-[#1d3c34] transition">
            + New Service Ticket
        </button>
    </div>

    {{-- User's existing maintenance requests for this property --}}
    @auth
        @php
            $myTickets = $property->maintenanceTickets()->where('user_id', auth()->id())->latest()->get();
        @endphp
        @if ($myTickets->isNotEmpty())
            <div class="mt-6 space-y-3 border-b border-[#f0e4cf] pb-6">
                <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34]">Your Submitted Service Requests</h4>
                @foreach ($myTickets as $ticket)
                    <div class="rounded-xl border border-[#ecd9be] bg-[#fffaf2] p-3.5 text-xs flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-[#102b25]">{{ $ticket->ticket_number }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $ticket->priority_badge_class }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                <strong class="font-bold text-[#1d3c34]">{{ $ticket->title }}</strong>
                            </div>
                            <p class="mt-1 text-slate-600 text-[11px]">{{ Str::limit($ticket->description, 100) }}</p>
                            @if ($ticket->location_notes)
                                <span class="text-[10px] text-slate-400">Location: {{ $ticket->location_notes }}</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $ticket->status_badge_class }}">
                                {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-1">{{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endauth

    {{-- Ticket Submission Form --}}
    <div id="maintenance-form-wrapper" class="mt-6 {{ auth()->check() && isset($myTickets) && $myTickets->isNotEmpty() ? 'hidden' : '' }}">
        @auth
            <form method="POST" action="{{ route('properties.maintenance.store', $property) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Issue Category <span class="text-red-500">*</span></label>
                        <select name="category" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34]">
                            <option value="plumbing">🚰 Plumbing (Water leaks, drainage, pump)</option>
                            <option value="electrical">⚡ Electrical (Power, breaker, switches, sockets)</option>
                            <option value="internet">📶 Internet & Telecom Connectivity</option>
                            <option value="appliance">🍳 Appliance (Stove, water heater, boiler)</option>
                            <option value="structural">🏗️ Structural / Locks / Windows / Doors</option>
                            <option value="cleaning">🧹 Housekeeping & Cleaning Request</option>
                            <option value="other">🔧 Other Maintenance Need</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Urgency Priority <span class="text-red-500">*</span></label>
                        <select name="priority" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-bold text-[#1d3c34]">
                            <option value="low">Low (Standard routine maintenance)</option>
                            <option value="medium" selected>Medium (Impacting comfort within 24h)</option>
                            <option value="high">High (Urgent - Requires technician today)</option>
                            <option value="emergency">🚨 Emergency (Immediate hazard / water leak / electrical)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Issue Summary <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required placeholder="e.g. Master bathroom water heater not functioning" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Specific Location Notes</label>
                        <input type="text" name="location_notes" placeholder="e.g. 2nd floor, guest bedroom on the left" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-semibold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#587165]">Detailed Description of Problem <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" required placeholder="Describe what occurred, any warning signs, and the preferred time window for technician inspection..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs font-medium text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#102b25]"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
                    <span class="text-[11px] text-slate-500">🛠️ Dispatches notification to property management and assigned building technician</span>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-[#102b25] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#d4af37] hover:bg-[#1d3c34] transition">
                        Dispatch Maintenance Ticket
                    </button>
                </div>
            </form>
        @else
            <div class="rounded-2xl bg-[#f0f4f8] p-4 text-center border border-blue-200">
                <p class="text-xs font-bold text-[#1d3c34]">Active tenant or staying guest?</p>
                <a href="{{ route('login') }}" class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-blue-900 px-4 py-2 text-xs font-bold text-white hover:bg-blue-800 transition">
                    Sign in to dispatch maintenance tickets
                </a>
            </div>
        @endauth
    </div>
</div>
