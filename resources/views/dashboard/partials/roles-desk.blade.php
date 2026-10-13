        {{-- ═══════════════════ ROLES & PERMISSIONS (Access Control) ═══════════════════ --}}
        <section id="roles-permissions" data-section="roles-permissions" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-[#e7ddca] pb-6">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Access Control &amp; RBAC</p>
                    <h2 class="mt-2 text-2xl md:text-3xl font-black text-[#1d3c34]">Roles &amp; Permissions Matrix</h2>
                    <p class="mt-2 max-w-3xl text-sm text-slate-500">
                        Create and edit roles, assign granular resource.action permissions, and control access across all 17 target actors. Every permission change is recorded in the immutable audit log with actor, timestamp, IP, diff, and reason.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('create-permission-modal').classList.toggle('hidden')" class="rounded-full bg-[#d4af37] px-5 py-2.5 text-xs font-bold text-[#1d3c34] transition hover:bg-[#e4c76f] shadow-sm cursor-pointer">
                        + Add Custom Permission
                    </button>
                    <button type="button" onclick="document.getElementById('create-role-modal').classList.toggle('hidden')" class="rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer">
                        + Create Custom Role
                    </button>
                </div>
            </div>

            {{-- Create Permission Form (Modal) --}}
            <div id="create-permission-modal" class="hidden mt-6 rounded-[1.5rem] border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                    <div>
                        <h3 class="text-lg font-black text-[#1d3c34]">Add Granular Permission</h3>
                        <p class="text-xs text-slate-500">Define a new permission in the database without code changes using the standard resource.action format.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('create-permission-modal').classList.add('hidden')" class="text-sm font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
                </div>
                <form method="POST" action="{{ route('dashboard.permissions.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Permission Key * <span class="text-slate-400 font-normal font-mono">(resource.action)</span></label>
                        <input type="text" name="key" required placeholder="e.g. property.create, booking.view, payment.refund" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Display Label *</label>
                        <input type="text" name="label" required placeholder="e.g. Refund customer payment transactions" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Resource / Group</label>
                        <input type="text" name="group" placeholder="e.g. payment (defaults to prefix)" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Description</label>
                        <input type="text" name="description" placeholder="Capability scope details" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                            Save Permission to Database
                        </button>
                    </div>
                </form>
            </div>

            {{-- Create Role Form (Collapsible/Modal) --}}
            <div id="create-role-modal" class="hidden mt-6 rounded-[1.5rem] border border-[#d4af37] bg-[#fbf8f3] p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                    <h3 class="text-lg font-black text-[#1d3c34]">Create New Role</h3>
                    <button type="button" onclick="document.getElementById('create-role-modal').classList.add('hidden')" class="text-sm font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
                </div>
                <form method="POST" action="{{ route('dashboard.roles.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Role Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Regional Inspector" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Description</label>
                        <input type="text" name="description" placeholder="Brief responsibility overview" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                            Create Role &amp; Initialize Permissions
                        </button>
                    </div>
                </form>
            </div>

            {{-- Custom Roles Section --}}
            @if (isset($roles) && $roles->where('is_system', false)->count() > 0)
                <div class="mt-8">
                    <h3 class="text-lg font-black text-[#1d3c34] mb-4">Custom Roles</h3>
                    <div class="grid gap-6">
                        @foreach ($roles->where('is_system', false) as $customRole)
                            @php
                                $roleGrantIds = $customRole->permissions->pluck('id')->all();
                            @endphp
                            <div class="rounded-[1.5rem] border border-[#d4af37]/60 bg-[#fffdfa] p-5 md:p-6 shadow-xs">
                                <form method="POST" action="{{ route('dashboard.roles.permissions.update') }}">
                                    @csrf
                                    <input type="hidden" name="persona" value="{{ $customRole->slug }}">

                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between border-b border-[#e7ddca] pb-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-lg font-black text-[#1d3c34]">{{ $customRole->name }}</h4>
                                                <span class="rounded-full bg-[#d4af37]/20 text-[#9b6c17] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">Custom Role</span>
                                            </div>
                                            <p class="mt-1 text-sm text-slate-500">{{ $customRole->description ?? 'Custom administrative role' }}</p>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2 md:text-right">
                                            <button type="button" onclick="document.getElementById('edit-role-modal-{{ $customRole->id }}').classList.toggle('hidden')" class="rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 text-xs font-bold transition cursor-pointer">
                                                Edit
                                            </button>
                                            <button type="button" onclick="if(confirm('Are you sure you want to delete role {{ $customRole->name }}?')) { document.getElementById('delete-role-form-{{ $customRole->id }}').submit(); }" class="rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-1 text-xs font-bold transition cursor-pointer">
                                                Delete
                                            </button>
                                            <span class="inline-block rounded-full bg-[#1d3c34] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white">
                                                {{ count($roleGrantIds) }} {{ Str::plural('grant', count($roleGrantIds)) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-2 md:grid-cols-2 lg:grid-cols-3 max-h-60 overflow-y-auto pr-2">
                                        @foreach ($allPermissions as $permission)
                                            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-[#e7ddca] bg-white px-3 py-2 transition hover:border-[#1d3c34]">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, $roleGrantIds, true) ? 'checked' : '' }} class="mt-0.5 h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                                <span class="text-xs font-semibold leading-snug text-slate-700">{{ $permission->label }} <span class="text-[10px] text-slate-400 font-mono">({{ $permission->key }})</span></span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                                        <input type="text" name="reason" placeholder="Reason for permission change (logged to audit trail)" class="w-full sm:w-2/3 rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                        <button type="submit" class="w-full sm:w-auto rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43]">
                                            Save Permissions
                                        </button>
                                    </div>
                                </form>

                                {{-- Edit Role Modal --}}
                                <div id="edit-role-modal-{{ $customRole->id }}" class="hidden mt-4 rounded-2xl border border-[#d9cab3] bg-white p-4 shadow-sm">
                                    <h5 class="text-sm font-black text-[#1d3c34] mb-2">Edit Role Details</h5>
                                    <form method="POST" action="{{ route('dashboard.roles.update', $customRole) }}" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">Role Name</label>
                                            <input type="text" name="name" value="{{ $customRole->name }}" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs focus:ring-2 focus:ring-[#1d3c34]">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">Description</label>
                                            <input type="text" name="description" value="{{ $customRole->description }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs focus:ring-2 focus:ring-[#1d3c34]">
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" onclick="document.getElementById('edit-role-modal-{{ $customRole->id }}').classList.add('hidden')" class="px-3 py-1 text-xs font-semibold text-slate-600">Cancel</button>
                                            <button type="submit" class="rounded-full bg-[#1d3c34] text-white px-4 py-1 text-xs font-bold transition hover:bg-[#254d43]">Update Role</button>
                                        </div>
                                    </form>
                                </div>

                                {{-- Hidden Delete Role Form --}}
                                <form id="delete-role-form-{{ $customRole->id }}" method="POST" action="{{ route('dashboard.roles.destroy', $customRole) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 17 System Personas & Roles Section --}}
            <div class="mt-8">
                <h3 class="text-lg font-black text-[#1d3c34] mb-4">Core Actor Presets &amp; Roles</h3>
                <div class="grid gap-6">
                    @foreach ($personas as $persona)
                        @continue(! ($persona['role'] ?? null) || ($persona['role'] ?? '') === 'campaign_officer' || ($persona['key'] ?? '') === 'campaign_officer')
                        @php
                            $grantedIds = $roleGrantsByPersona[$persona['role']] ?? [];
                            $personaPermissions = $allPermissions->filter(
                                fn ($permission) => $permission->group === $persona['key'] || in_array($permission->id, $grantedIds, true)
                            );
                        @endphp
                        <div class="rounded-[1.5rem] border border-[#e7ddca] bg-[#fbf8f3] p-5 md:p-6">
                            <form method="POST" action="{{ route('dashboard.roles.permissions.update') }}">
                                @csrf
                                <input type="hidden" name="persona" value="{{ $persona['key'] }}">

                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between border-b border-[#e7ddca] pb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-lg font-black text-[#1d3c34]">{{ $persona['label'] }}</h4>
                                            <span class="rounded-full bg-slate-200 text-slate-700 px-2 py-0.5 text-[10px] font-mono">role: {{ $persona['role'] }}</span>
                                        </div>
                                        <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ $persona['description'] }}</p>
                                    </div>
                                    <div class="shrink-0 md:text-right">
                                        <span class="inline-block rounded-full bg-[#1d3c34] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white">
                                            {{ count($grantedIds) }} {{ Str::plural('grant', count($grantedIds)) }}
                                        </span>
                                        @php
                                            $dedicatedRoute = match($persona['role'] ?? '') {
                                                'property_manager' => 'property-manager.dashboard',
                                                'cleaner' => 'cleaner.dashboard',
                                                'investor' => 'investor.dashboard',
                                                'moderator' => 'moderator.dashboard',
                                                'verification_officer' => 'verification-officer.dashboard',
                                                'customer_support' => 'customer-support.dashboard',
                                                'service_provider' => 'professional.dashboard',
                                                'landlord', 'property_owner' => 'seller.dashboard',
                                                default => null,
                                            };
                                        @endphp
                                        @if ($dedicatedRoute && Route::has($dedicatedRoute))
                                            <p class="mt-2 text-[11px] font-bold text-[#1d3c34]">
                                                Control Center: <a href="{{ route($dedicatedRoute) }}" target="_blank" class="underline hover:text-[#254d43]">{{ route($dedicatedRoute) }}</a>
                                            </p>
                                        @endif
                                        @if ($persona['portal'] && Route::has($persona['portal']) && $persona['portal'] !== $dedicatedRoute)
                                            <p class="mt-1 text-[11px] font-semibold text-slate-400">
                                                RBAC Desk: <a href="{{ route($persona['portal']) }}" target="_blank" class="underline hover:text-slate-600">{{ route($persona['portal']) }}</a>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2 md:grid-cols-2 lg:grid-cols-3 max-h-72 overflow-y-auto pr-2">
                                    @foreach ($personaPermissions as $permission)
                                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-[#e7ddca] bg-white px-3 py-2.5 transition hover:border-[#1d3c34]">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, $grantedIds, true) ? 'checked' : '' }} class="mt-0.5 h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                            <span class="text-xs font-semibold leading-snug text-slate-700">{{ $permission->label }} <span class="text-[10px] text-slate-400 font-mono">({{ $permission->key }})</span></span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <input type="text" name="reason" placeholder="Audit reason for update (e.g., quarterly policy review)" class="w-full sm:w-2/3 rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                    <button type="submit" class="w-full sm:w-auto rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-xs cursor-pointer">
                                        Save {{ $persona['label'] }} Permissions
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach

                    @php $guestPersona = collect($personas)->firstWhere('key', 'guest'); @endphp
                    @if ($guestPersona)
                        <div class="rounded-[1.5rem] border border-dashed border-[#d9cab3] bg-white p-5 md:p-6">
                            <h4 class="text-lg font-black text-[#1d3c34]">{{ $guestPersona['label'] }} <span class="ml-1 align-middle text-[10px] font-black uppercase tracking-wider text-[#9b6c17]">Public — no account</span></h4>
                            <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ $guestPersona['description'] }}</p>
                            <div class="mt-4 grid gap-2 md:grid-cols-3">
                                @foreach ($allPermissions->where('group', 'guest') as $permission)
                                    <span class="rounded-xl border border-[#e7ddca] bg-[#fbf8f3] px-3 py-2.5 text-xs font-semibold text-slate-600">{{ $permission->label }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
