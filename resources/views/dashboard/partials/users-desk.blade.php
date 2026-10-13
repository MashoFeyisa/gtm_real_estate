        @php
            $editingUser = request()->query('edit_user');
            $editingAgentId = request()->query('edit_agent');
            $agentToEdit = $editingAgentId ? ($agents->firstWhere('id', (int) $editingAgentId) ?? \App\Models\Agent::find((int) $editingAgentId)) : null;
            $userToEdit = $editingUser ? ($users->firstWhere('id', (int) $editingUser) ?? \App\Models\User::find((int) $editingUser)) : null;
            if (! $userToEdit && $agentToEdit) {
                $userToEdit = $agentToEdit->account ?? $users->firstWhere('email', $agentToEdit->email) ?? \App\Models\User::where('email', $agentToEdit->email)->first();
            }
        @endphp

        {{-- USER REGISTRATION & UNIFIED DATA TABLE SECTION (Finance Officer, Receptionist, Agent, Admin) --}}
        <section id="users" data-section="users" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-[#e7ddca] pb-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="rounded-full bg-[#102b25] px-3 py-1 text-xs font-black uppercase tracking-wider text-[#d4af37]">Staff &amp; Users</span>
                        <span class="text-xs font-semibold text-slate-500">Company • Finance Officers • Receptionists • Agents • Admins</span>
                    </div>
                    <h2 class="mt-2 text-2xl md:text-3xl font-black text-[#1d3c34]">User Registration &amp; Management</h2>
                    <p class="mt-1 text-xs md:text-sm text-slate-600">Register new staff members and review all active system users at once in one unified dashboard.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="toggle-create-form rounded-full bg-[#1d3c34] px-5 py-2.5 text-xs md:text-sm font-bold text-white transition hover:bg-[#254d43] shadow-sm cursor-pointer" data-target="users-create-panel" data-label-create="+ Register User" data-label-close="Close Registration Form">
                        {{ $userToEdit ? 'Close Form' : '+ Register User' }}
                    </button>
                </div>
            </div>

            {{-- Role Summary Metrics --}}
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div class="rounded-2xl border border-[#d4af37]/40 bg-[#fdfaf3] p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#9b6c17]">Company</span>
                        <span class="rounded-full bg-[#102b25] px-2 py-0.5 text-[10px] font-bold text-[#d4af37]">Enterprise</span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-[#102b25]">{{ \App\Models\User::where('role', 'company')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-[#d9cab3] bg-[#fbf9f5] p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Admins</span>
                        <span class="rounded-full bg-[#1d3c34]/10 px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]">Management</span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-[#1d3c34]">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Finance Officers</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Ledger</span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-emerald-900">{{ \App\Models\User::where('role', 'finance_officer')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#9b6c17]">Receptionists</span>
                        <span class="rounded-full bg-[#f9ecd0] px-2 py-0.5 text-[10px] font-bold text-[#9b6c17]">Front Desk</span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-[#9b6c17]">{{ \App\Models\User::where('role', 'receptionist')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Agents</span>
                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">Sales/Rent</span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-blue-900">{{ \App\Models\User::where('role', 'agent')->count() }}</p>
                </div>
            </div>

            {{-- User Registration / Edit Panel --}}
            <div id="users-create-panel" class="create-form-panel mt-6 rounded-[1.5rem] border border-[#e7ddca] bg-[#f9f5ee] p-6 {{ $userToEdit || $agentToEdit ? '' : 'hidden' }}">
                <div class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                    <h3 class="text-xl font-black text-[#1d3c34]">
                        {{ $agentToEdit ? 'Edit Agent: ' . $agentToEdit->name : ($userToEdit ? 'Edit User: ' . $userToEdit->name : 'Register New User / Staff Member') }}
                    </h3>
                    @if ($userToEdit || $agentToEdit)
                        <a href="{{ route('dashboard', ['section' => 'users']) }}" class="text-xs font-bold text-rose-700 hover:underline">Cancel edit</a>
                    @endif
                </div>

                <form method="POST" action="{{ $userToEdit ? route('dashboard.users.update', $userToEdit) : ($agentToEdit ? route('dashboard.agents.update', $agentToEdit) : route('dashboard.users.store')) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    @if ($userToEdit || $agentToEdit)
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $agentToEdit?->name ?? $userToEdit?->name) }}" required placeholder="e.g. Almaz Kebede" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Email Address (Login Username) *</label>
                            <input type="email" name="email" value="{{ old('email', $agentToEdit?->email ?? $userToEdit?->email) }}" required placeholder="user@gtprealestate.com" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">System Role *</label>
                            @php
                                $selectedRole = old('role', $userToEdit?->role ?? ($agentToEdit ? 'agent' : ''));
                                $roleOptionLabels = [
                                    'receptionist' => 'Receptionist (Front Desk & Inquiries)',
                                ];
                                foreach (config('personas.personas', []) as $personaOption) {
                                    if (filled($personaOption['role'] ?? null) && ($personaOption['role'] ?? '') !== 'campaign_officer' && ! isset($roleOptionLabels[$personaOption['role']])) {
                                        $roleOptionLabels[$personaOption['role']] = $personaOption['label'];
                                    }
                                }
                                $roleOptionGroups = [
                                    'Management & Staff' => ['admin', 'company', 'finance_officer', 'agent', 'receptionist'],
                                    'Members, Buyers & Renters' => ['buyer', 'tenant', 'traveler', 'host', 'investor'],
                                    'Owners, Operations & Oversight' => ['property_owner', 'landlord', 'developer', 'property_manager', 'cleaner', 'service_provider', 'verification_company', 'moderator', 'verification_officer', 'customer_support'],
                                ];
                            @endphp
                            <select name="role" id="user-form-role-select" onchange="onUserFormRoleChanged(this.value)" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-bold text-[#1d3c34] focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                @foreach ($roleOptionGroups as $groupLabel => $roleValues)
                                    <optgroup label="{{ $groupLabel }}">
                                        @foreach ($roleValues as $roleValue)
                                            <option value="{{ $roleValue }}" {{ $selectedRole === $roleValue ? 'selected' : '' }}>{{ $roleOptionLabels[$roleValue] ?? ucfirst(str_replace('_', ' ', $roleValue)) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500">All 16 platform actors are listed here — guests have no dashboard and create accounts by self-registration. Each role receives the default permissions set in Roles &amp; Permissions; fine-tune individual accounts with permission overrides below.</p>
                        </div>

                    </div>

                    {{-- Agent Profile — merged agent management (visible when the role is Agent) --}}
                    @php
                        $agentProfileToEdit = $agentToEdit ?? $userToEdit?->agentProfile;
                        $showAgentProfileFields = (bool) ($agentProfileToEdit || $selectedRole === 'agent' || old('role') === 'agent');
                    @endphp
                    <div id="agent-profile-fields" data-has-agent-profile="{{ $agentProfileToEdit ? '1' : '0' }}" class="{{ $showAgentProfileFields ? '' : 'hidden' }} rounded-[1.25rem] border border-blue-200 bg-blue-50/40 p-5">
                        <div class="border-b border-blue-200 pb-3">
                            <h4 class="text-sm font-black uppercase tracking-wider text-blue-900">Agent Profile</h4>
                            <p class="mt-0.5 text-[11px] text-slate-600">Sales, licensing and portal settings — applied when the System Role above is Agent.</p>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $agentProfileToEdit?->phone) }}" placeholder="+2519 11 22 33 44" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Title / Specialty (bio)</label>
                                <input type="text" name="bio" value="{{ old('bio', $agentProfileToEdit?->bio) }}" placeholder="e.g. Senior property consultant" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Commission Rate (%)</label>
                                <input type="number" name="commission_rate" value="{{ old('commission_rate', $agentProfileToEdit?->commission_rate ?? 5) }}" min="0" max="100" step="0.01" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                <p class="mt-1 text-[11px] text-slate-500">Percentage of deal price the agent owes to admin per completed sale.</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Agent Photo</label>
                                <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                @if ($agentProfileToEdit?->photo_path)
                                    <img src="{{ asset('storage/'.$agentProfileToEdit->photo_path) }}" alt="{{ $agentProfileToEdit->name }}" class="mt-2 h-14 w-14 rounded-full object-cover ring-1 ring-[#d9cab3]">
                                @endif
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Commission Tier</label>
                                <select name="tier" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                    @foreach (['junior' => 'Junior Associate (60% default split)', 'senior' => 'Senior Consultant (70% default split)', 'principal' => 'Principal Partner (80% default split)'] as $tierValue => $tierLabel)
                                        <option value="{{ $tierValue }}" {{ old('tier', $agentProfileToEdit?->tier ?? 'junior') === $tierValue ? 'selected' : '' }}>{{ $tierLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Split Percentage (%)</label>
                                <input type="number" name="split_percentage" value="{{ old('split_percentage', $agentProfileToEdit?->split_percentage) }}" min="0" max="100" step="0.01" placeholder="Tier default" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                                <p class="mt-1 text-[11px] text-slate-500">Leave blank to use the commission tier's default split.</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">License Number</label>
                                <input type="text" name="license_number" value="{{ old('license_number', $agentProfileToEdit?->license_number) }}" placeholder="e.g. ETH-BRK-2026-001" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">License Status</label>
                                <input type="hidden" name="license_verified" value="0">
                                <label class="mt-2 flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" name="license_verified" value="1" {{ old('license_verified', $agentProfileToEdit?->license_verified) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span class="text-sm font-medium text-slate-700">License verified</span>
                                </label>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">Account Permission</label>
                                <input type="hidden" name="is_active" value="0">
                                <label class="mt-2 flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $agentProfileToEdit ? $agentProfileToEdit->is_active : true) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <span class="text-sm font-medium text-slate-700">Active (can login &amp; access Agent Portal)</span>
                                </label>
                                <p class="mt-1 text-[11px] text-slate-500">When unchecked, the agent is deactivated and blocked from portal login.</p>
                            </div>

                            <div class="rounded-xl border border-blue-200 bg-white px-4 py-3 md:col-span-2">
                                <p class="text-xs font-black uppercase tracking-wider text-blue-900">Portal Password</p>
                                <p class="mt-1 text-[11px] text-slate-600">Agents sign in to the Agent Portal with the Account Password field below — leave it blank to keep the current password when editing.</p>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════ USER PERMISSIONS & ACCESS CONTROL (Admin Configurable) ═══════════════════ --}}
                    @php
                        $roleDefaultPermsMap = [];
                        if (isset($roles)) {
                            foreach ($roles as $r) {
                                $roleDefaultPermsMap[$r->slug] = $r->permissions->pluck('id')->all();
                            }
                        }
                        if (isset($roleGrantsByPersona)) {
                            foreach ($roleGrantsByPersona as $pRole => $pIds) {
                                if (! isset($roleDefaultPermsMap[$pRole])) {
                                    $roleDefaultPermsMap[$pRole] = $pIds;
                                }
                            }
                        }

                        $currentUserGrantedIds = [];
                        $isCustomAccount = false;
                        if ($userToEdit) {
                            $hasCustomOverrides = $userToEdit->permissionOverrides->isNotEmpty() || $userToEdit->userPermissions->isNotEmpty();
                            if ($hasCustomOverrides) {
                                $isCustomAccount = true;
                                $currentUserGrantedIds = $userToEdit->permissionOverrides->where('granted', true)->pluck('permission_id')
                                    ->merge($userToEdit->userPermissions->where('granted', true)->pluck('permission_id'))
                                    ->unique()
                                    ->values()
                                    ->all();
                            } else {
                                $currentUserGrantedIds = $roleDefaultPermsMap[$userToEdit->role] ?? [];
                            }
                        } else {
                            $currentUserGrantedIds = $roleDefaultPermsMap[$selectedRole] ?? [];
                        }

                        $initialFormMode = old('permission_mode', $isCustomAccount ? 'custom' : 'role_default');

                        $usersPermissionsMap = [];
                        if (isset($users)) {
                            foreach ($users as $u) {
                                $hasCustom = $u->permissionOverrides->isNotEmpty() || $u->userPermissions->isNotEmpty();
                                if ($hasCustom) {
                                    $grantedIds = $u->permissionOverrides->where('granted', true)->pluck('permission_id')
                                        ->merge($u->userPermissions->where('granted', true)->pluck('permission_id'))
                                        ->unique()->values()->all();
                                    $usersPermissionsMap[$u->id] = [
                                        'mode' => 'custom',
                                        'granted' => $grantedIds,
                                    ];
                                } else {
                                    $usersPermissionsMap[$u->id] = [
                                        'mode' => 'role_default',
                                        'granted' => $roleDefaultPermsMap[$u->role] ?? [],
                                    ];
                                }
                            }
                        }

                        $categoryMetadata = [
                            'administrator' => ['label' => 'System Administration & Security', 'icon' => '🛡️'],
                            'property_owner' => ['label' => 'Property Owner & Listing Sales', 'icon' => '🏡'],
                            'landlord' => ['label' => 'Landlord & Long-Term Rentals', 'icon' => '🔑'],
                            'host' => ['label' => 'Short-Stay Host & Hospitality', 'icon' => '🛏️'],
                            'developer' => ['label' => 'Real Estate Developer & Projects', 'icon' => '🏗️'],
                            'property_manager' => ['label' => 'Property Manager Operations', 'icon' => '🏢'],
                            'agent' => ['label' => 'Broker & Agent CRM', 'icon' => '💼'],
                            'real_estate_company' => ['label' => 'Agency & Corporate Company', 'icon' => '🏛️'],
                            'finance_officer' => ['label' => 'Finance, Escrow & Ledger', 'icon' => '💰'],
                            'buyer' => ['label' => 'Property Buyer & Marketplace', 'icon' => '🛒'],
                            'tenant' => ['label' => 'Tenant & Lease Agreement', 'icon' => '📜'],
                            'traveler' => ['label' => 'Traveler & Guest Stays', 'icon' => '✈️'],
                            'receptionist' => ['label' => 'Front Desk Receptionist', 'icon' => '🛎️'],
                            'cleaner' => ['label' => 'Housekeeping & Turnover', 'icon' => '🧹'],
                            'moderator' => ['label' => 'Content Moderation & Reviews', 'icon' => '⚖️'],
                            'verification_officer' => ['label' => 'Physical Property Verification', 'icon' => '🔍'],
                            'verification_company' => ['label' => 'Verification Firm Audits', 'icon' => '📑'],
                            'customer_support' => ['label' => 'Help Desk & Support Tickets', 'icon' => '🎧'],
                            'service_provider' => ['label' => 'Professional Home Services', 'icon' => '🔧'],
                            'investor' => ['label' => 'Diaspora Investor Portfolios', 'icon' => '📈'],
                            'campaign_officer' => ['label' => 'Marketing & Campaigns', 'icon' => '📣'],
                            'guest' => ['label' => 'Public Visitor & Browse', 'icon' => '🌐'],
                        ];

                        $totalPermsCount = $allPermissions->count();
                    @endphp

                    <div class="rounded-[1.25rem] border border-[#d4af37]/60 bg-[#fffdfa] p-5 md:p-6 shadow-xs">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-[#e7ddca] pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🔑</span>
                                    <h4 class="text-sm font-black uppercase tracking-wider text-[#1d3c34]">User Permissions &amp; Role Overrides</h4>
                                    <span class="rounded-full bg-[#1d3c34]/10 text-[#1d3c34] px-2.5 py-0.5 text-[10px] font-bold">Admin Configurable</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Control exactly what this user can do. Keep role defaults, grant full platform access, or hand-pick permissions by category.
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#1d3c34] px-3 py-1 text-xs font-bold text-white shadow-xs">
                                    <span id="form-selected-count">{{ count($currentUserGrantedIds) }}</span> / {{ $totalPermsCount }} Permissions
                                </span>
                            </div>
                        </div>

                        {{-- Mode Selection Radio Cards --}}
                        <input type="hidden" name="permission_mode" id="form-permission-mode" value="{{ $initialFormMode }}">

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <label onclick="setFormPermissionMode('role_default')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#1d3c34] has-[:checked]:border-[#1d3c34] has-[:checked]:bg-[#f3f7f5] has-[:checked]:ring-1 has-[:checked]:ring-[#1d3c34]">
                                <input type="radio" name="form_mode_radio" value="role_default" {{ $initialFormMode === 'role_default' ? 'checked' : '' }} class="mt-1 text-[#1d3c34] focus:ring-[#1d3c34]">
                                <div>
                                    <p class="text-xs font-bold text-[#1d3c34]">📋 Use Role Defaults</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Inherit standard defaults automatically for the selected System Role.</p>
                                </div>
                            </label>

                            <label onclick="setFormPermissionMode('all')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#d4af37] has-[:checked]:border-[#d4af37] has-[:checked]:bg-[#fcf8ed] has-[:checked]:ring-1 has-[:checked]:ring-[#d4af37]">
                                <input type="radio" name="form_mode_radio" value="all" {{ $initialFormMode === 'all' ? 'checked' : '' }} class="mt-1 text-[#b7842d] focus:ring-[#d4af37]">
                                <div>
                                    <p class="text-xs font-bold text-[#9b6c17]">⚡ Grant Everything</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Superuser: grants all 339 permissions across all platform modules.</p>
                                </div>
                            </label>

                            <label onclick="setFormPermissionMode('custom')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#1d3c34] has-[:checked]:border-[#1d3c34] has-[:checked]:bg-[#f3f7f5] has-[:checked]:ring-1 has-[:checked]:ring-[#1d3c34]">
                                <input type="radio" name="form_mode_radio" value="custom" {{ $initialFormMode === 'custom' ? 'checked' : '' }} class="mt-1 text-[#1d3c34] focus:ring-[#1d3c34]">
                                <div>
                                    <p class="text-xs font-bold text-[#1d3c34]">📂 Custom by Category</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Select categories or fine-tune individual permissions manually.</p>
                                </div>
                            </label>
                        </div>

                        {{-- Granular Permissions Panel --}}
                        <div id="form-permissions-panel" class="mt-5 border-t border-[#e7ddca] pt-4">
                            {{-- Quick Action Toolbar --}}
                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between bg-white p-3 rounded-xl border border-[#e7ddca]">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button type="button" onclick="formSelectAll()" class="rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 text-xs font-bold hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                                        ⚡ Select All
                                    </button>
                                    <button type="button" onclick="formLoadRoleDefaults()" class="rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 text-xs font-bold hover:bg-[#fbf9f5] transition cursor-pointer">
                                        🔄 Role Defaults
                                    </button>
                                    <button type="button" onclick="formClearAll()" class="rounded-lg bg-white border border-rose-200 text-rose-700 px-3 py-1.5 text-xs font-bold hover:bg-rose-50 transition cursor-pointer">
                                        🚫 Clear All
                                    </button>
                                </div>

                                <div class="relative w-full sm:w-64">
                                    <input type="text" id="form-perm-search" oninput="filterFormPermissions(this.value)" placeholder="Search permissions (e.g. create, rent)..." class="w-full rounded-lg border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                                </div>
                            </div>

                            {{-- Categories Accordion Grid --}}
                            <div class="mt-4 grid gap-3 max-h-96 overflow-y-auto pr-1" id="form-categories-container">
                                @foreach ($allPermissions->groupBy('group') as $groupKey => $groupPermissions)
                                    @php
                                        $meta = $categoryMetadata[$groupKey] ?? ['label' => ucfirst(str_replace('_', ' ', $groupKey)), 'icon' => '📁'];
                                        $groupPermIds = $groupPermissions->pluck('id')->all();
                                    @endphp
                                    <div class="form-category-card rounded-xl border border-[#e7ddca] bg-white p-3.5 transition" data-group="{{ $groupKey }}">
                                        <div class="flex items-center justify-between border-b border-[#f0e8dc] pb-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-base">{{ $meta['icon'] }}</span>
                                                <h5 class="text-xs font-bold text-[#1d3c34]">{{ $meta['label'] }}</h5>
                                                <span class="form-category-badge rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600" data-group="{{ $groupKey }}" data-total="{{ count($groupPermIds) }}">
                                                    0 / {{ count($groupPermIds) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" onclick="formSelectCategory('{{ $groupKey }}')" class="rounded-md bg-[#dfeee4] px-2 py-0.5 text-[10px] font-bold text-[#1d3c34] hover:bg-[#1d3c34] hover:text-white transition cursor-pointer">
                                                    + Select All
                                                </button>
                                                <button type="button" onclick="formDeselectCategory('{{ $groupKey }}')" class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 hover:bg-slate-200 transition cursor-pointer">
                                                    - Deselect
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-2.5 grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                                            @foreach ($groupPermissions as $permission)
                                                <label class="form-perm-item flex cursor-pointer items-start gap-2 rounded-lg border border-[#f0e8dc] p-2 hover:bg-[#fbf9f5] transition" data-search="{{ strtolower($permission->label . ' ' . $permission->key) }}">
                                                    <input type="checkbox" name="granted_permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $currentUserGrantedIds, true) ? 'checked' : '' }} onchange="updateFormPermState()" class="form-perm-checkbox mt-0.5 h-3.5 w-3.5 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]" data-group="{{ $groupKey }}">
                                                    <div class="min-w-0">
                                                        <p class="text-[11px] font-semibold text-slate-700 leading-tight">{{ $permission->label }}</p>
                                                        <p class="text-[9px] font-mono text-slate-400 truncate">{{ $permission->key }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                {{ $userToEdit || $agentToEdit ? 'Password (Leave blank to keep current)' : 'Account Password *' }}
                            </label>
                            <button type="button" onclick="generateStrongPassword('#user-reg-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline cursor-pointer">
                                Generate Strong Password
                            </button>
                        </div>
                        <input id="user-reg-password" type="password" name="password" {{ $userToEdit || $agentToEdit ? '' : 'required' }} minlength="8" placeholder="{{ $userToEdit || $agentToEdit ? 'Leave blank to keep unchanged' : 'Min 8 characters with letters, numbers, symbols' }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    </div>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs md:text-sm font-bold text-white transition hover:bg-[#254d43] shadow-xs cursor-pointer">
                            {{ $agentToEdit ? 'Update Agent' : ($userToEdit ? 'Save Changes' : 'Register User') }}
                        </button>
                        @if ($userToEdit || $agentToEdit)
                            <a href="{{ route('dashboard', ['section' => 'users']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-2.5 text-xs md:text-sm font-semibold text-slate-700 hover:bg-[#f5f0e7] transition">
                                Cancel
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Unified Filter Bar & Search --}}
            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fbf8f3] p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="dashboard-users-search" placeholder="Search users by name, email, role, or assigned property..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2 pl-9 pr-8 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-[#1d3c34] focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                        <button type="button" id="dashboard-users-search-clear" class="hidden absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 transition" title="Clear search">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                        <button type="button" onclick="filterUsersByRole('all')" class="user-role-filter-btn rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 transition" data-role="all">All ({{ $users->count() }})</button>
                        <button type="button" onclick="filterUsersByRole('company')" class="user-role-filter-btn rounded-lg bg-white border border-[#d4af37]/60 text-[#102b25] px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-role="company">Company ({{ $users->where('role', 'company')->count() }})</button>
                        <button type="button" onclick="filterUsersByRole('finance_officer')" class="user-role-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-role="finance_officer">Finance ({{ $users->where('role', 'finance_officer')->count() }})</button>
                        <button type="button" onclick="filterUsersByRole('receptionist')" class="user-role-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-role="receptionist">Receptionist ({{ $users->where('role', 'receptionist')->count() }})</button>
                        <button type="button" onclick="filterUsersByRole('agent')" class="user-role-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-role="agent">Agent ({{ $users->where('role', 'agent')->count() }})</button>
                        <button type="button" onclick="filterUsersByRole('admin')" class="user-role-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition" data-role="admin">Admin ({{ $users->where('role', 'admin')->count() }})</button>
                    </div>

                    <span id="dashboard-users-count" class="shrink-0 rounded-full bg-[#1d3c34]/10 px-3 py-1 text-xs font-bold text-[#1d3c34]">{{ $users->count() }} users</span>
                </div>
            </div>

            {{-- Unified Data Table --}}
            <div class="mt-4 overflow-x-auto rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm" id="dashboard-users-table">
                    <thead class="bg-[#1d3c34] text-xs font-bold uppercase tracking-[0.12em] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4">User / Avatar</th>
                            <th class="px-5 py-4">Email</th>
                            <th class="px-5 py-4">System Role</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Access Controls</th>
                            <th class="px-5 py-4">Joined</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca] bg-white">
                        @forelse ($users as $systemUser)
                            @php
                                $roleBadge = match ($systemUser->role) {
                                    'company' => ['Company (GTM)', 'bg-[#102b25] text-[#d4af37] border-[#d4af37]/50 font-black'],
                                    'campaign_officer' => ['Campaign Officer', 'bg-indigo-100 text-indigo-800 border-indigo-300'],
                                    'finance_officer' => ['Finance Officer', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                                    'receptionist' => ['Receptionist', 'bg-[#f9ecd0] text-[#9b6c17] border-[#d4af37]/40'],
                                    'agent' => ['Agent', 'bg-blue-100 text-blue-800 border-blue-300'],
                                    'admin' => ['Admin', 'bg-[#dfeee4] text-[#1d3c34] border-[#2d5d4d]/30'],
                                    default => [ucfirst(str_replace('_', ' ', $systemUser->role ?? 'Client')), 'bg-slate-100 text-slate-700 border-slate-300'],
                                };

                                $statusBadge = match ($systemUser->status ?? 'active') {
                                    'suspended' => ['Suspended', 'bg-rose-100 text-rose-800 border-rose-300'],
                                    'pending' => ['Pending Approval', 'bg-amber-100 text-amber-800 border-amber-300'],
                                    default => ['Active', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                                };
                            @endphp
                            <tr class="dashboard-user-row transition hover:bg-[#fbf9f5]" data-role="{{ $systemUser->role }}" data-search="{{ strtolower($systemUser->name . ' ' . $systemUser->email . ' ' . $systemUser->role . ' ' . ($systemUser->assignedProperty?->title ?? '')) }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($systemUser->photo_url)
                                            <img src="{{ $systemUser->photo_url }}" alt="{{ $systemUser->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-[#d9cab3]/60 shadow-xs">
                                        @else
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1d3c34] text-xs font-black text-[#f8f3eb] shadow-xs">
                                                {{ $systemUser->initials }}
                                            </span>
                                        @endif
                                        <div>
                                            <p class="font-bold text-[#1d3c34] text-sm">{{ $systemUser->name }}</p>
                                            @if ($systemUser->id === auth()->id())
                                                <span class="inline-block text-[10px] font-bold text-[#b7842d]">★ Logged In (You)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs font-medium text-slate-600">
                                    <a href="mailto:{{ $systemUser->email }}" class="hover:text-[#1d3c34] hover:underline">{{ $systemUser->email }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $roleBadge[1] }}">
                                        {{ $roleBadge[0] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $statusBadge[1] }}">
                                        {{ $statusBadge[0] }}
                                    </span>
                                    @if ($systemUser->id !== auth()->id() && auth()->user()->canManageUserStatus())
                                        <div class="mt-1 flex items-center gap-1">
                                            @if (($systemUser->status ?? 'active') !== 'active')
                                                <form method="POST" action="{{ route('dashboard.users.status', $systemUser) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="text-[10px] font-bold text-emerald-700 hover:underline">Approve</button>
                                                </form>
                                            @endif
                                            @if (($systemUser->status ?? 'active') !== 'suspended')
                                                <form method="POST" action="{{ route('dashboard.users.status', $systemUser) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" class="text-[10px] font-bold text-rose-700 hover:underline">Suspend</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="flex flex-col gap-1.5">
                                        @if (auth()->user()->canEditUsers())
                                            <form method="POST" action="{{ route('dashboard.users.toggle-dashboard', $systemUser) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $systemUser->dashboard_access_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    Dashboard: {{ $systemUser->dashboard_access_enabled ? 'Granted' : 'Revoked' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.users.toggle-media', $systemUser) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $systemUser->media_access_enabled ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-700' }}">
                                                    Media: {{ $systemUser->media_access_enabled ? 'Enabled' : 'Disabled' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $systemUser->dashboard_access_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                Dashboard: {{ $systemUser->dashboard_access_enabled ? 'Granted' : 'Revoked' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $systemUser->created_at?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (auth()->user()->canManagePermissions())
                                            <button type="button" onclick="openUserPermissionsModal({{ $systemUser->id }}, '{{ addslashes($systemUser->name) }}', '{{ $systemUser->role }}', '{{ addslashes($systemUser->email) }}')" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#254d43] shadow-xs cursor-pointer inline-flex items-center gap-1" title="Manage permissions for this user">
                                                <svg class="h-3.5 w-3.5 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                Permissions
                                            </button>
                                        @endif

                                        @if (auth()->user()->canEditUsers())
                                            <a href="{{ route('dashboard', ['edit_user' => $systemUser->id, 'section' => 'users']) }}" class="rounded-full bg-[#587165] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#1d3c34]">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($systemUser->id !== auth()->id() && auth()->user()->canDeleteUsers())
                                            <form method="POST" action="{{ route('dashboard.users.delete', $systemUser) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-1.5 text-xs font-bold text-[#a24339] transition hover:bg-[#a24339] hover:text-white" data-confirm="Are you sure you want to delete user {{ addslashes($systemUser->name) }}?">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">No users found in the system.</td>
                            </tr>
                        @endforelse
                        <tr id="dashboard-users-no-results" class="hidden border-t border-[#e7ddca] bg-white">
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">No users found matching your search or role filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $users->fragment('users')->links() }}
                </div>
            @endif

            {{-- ═══════════════════ QUICK USER PERMISSIONS MODAL ═══════════════════ --}}
            <div id="quick-permissions-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="relative w-full max-w-4xl rounded-[1.75rem] border border-[#d4af37] bg-white shadow-2xl overflow-hidden my-8 max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-[#e7ddca] bg-[#1d3c34] px-6 py-4 text-white">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#d4af37] text-white font-black text-sm shadow-xs">🔑</span>
                            <div>
                                <h3 class="text-base font-black text-white" id="modal-user-title">Manage Permissions</h3>
                                <p class="text-xs text-slate-200" id="modal-user-subtitle">Configure granular grants and access rights</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeUserPermissionsModal()" class="rounded-full p-1.5 text-white/70 hover:bg-white/10 hover:text-white transition cursor-pointer" title="Close modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body Form --}}
                    <form id="modal-permissions-form" method="POST" action="" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <input type="hidden" name="permission_mode" id="modal-permission-mode" value="custom">

                        <div class="p-6 overflow-y-auto flex-1 space-y-5 bg-[#fbf8f3]">
                            {{-- Mode Selection Cards --}}
                            <div class="grid gap-3 sm:grid-cols-3">
                                <label onclick="setModalPermissionMode('role_default')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#1d3c34] has-[:checked]:border-[#1d3c34] has-[:checked]:bg-[#f3f7f5] has-[:checked]:ring-1 has-[:checked]:ring-[#1d3c34]">
                                    <input type="radio" name="modal_mode_radio" value="role_default" class="mt-1 text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <div>
                                        <p class="text-xs font-bold text-[#1d3c34]">📋 Role Defaults</p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">Reset to standard permissions for this user's role.</p>
                                    </div>
                                </label>

                                <label onclick="setModalPermissionMode('all')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#d4af37] has-[:checked]:border-[#d4af37] has-[:checked]:bg-[#fcf8ed] has-[:checked]:ring-1 has-[:checked]:ring-[#d4af37]">
                                    <input type="radio" name="modal_mode_radio" value="all" class="mt-1 text-[#b7842d] focus:ring-[#d4af37]">
                                    <div>
                                        <p class="text-xs font-bold text-[#9b6c17]">⚡ Grant Everything</p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">Superuser: grants all 339 permissions across all modules.</p>
                                    </div>
                                </label>

                                <label onclick="setModalPermissionMode('custom')" class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e7ddca] bg-white p-3.5 transition hover:border-[#1d3c34] has-[:checked]:border-[#1d3c34] has-[:checked]:bg-[#f3f7f5] has-[:checked]:ring-1 has-[:checked]:ring-[#1d3c34]">
                                    <input type="radio" name="modal_mode_radio" value="custom" checked class="mt-1 text-[#1d3c34] focus:ring-[#1d3c34]">
                                    <div>
                                        <p class="text-xs font-bold text-[#1d3c34]">📂 Custom by Category</p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">Select categories or customize individual permissions.</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Modal Toolbar --}}
                            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between bg-white p-3 rounded-xl border border-[#e7ddca]">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button type="button" onclick="modalSelectAll()" class="rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 text-xs font-bold hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                                        ⚡ Select All
                                    </button>
                                    <button type="button" onclick="modalLoadRoleDefaults()" class="rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 text-xs font-bold hover:bg-[#fbf9f5] transition cursor-pointer">
                                        🔄 Role Defaults
                                    </button>
                                    <button type="button" onclick="modalClearAll()" class="rounded-lg bg-white border border-rose-200 text-rose-700 px-3 py-1.5 text-xs font-bold hover:bg-rose-50 transition cursor-pointer">
                                        🚫 Clear All
                                    </button>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-[#1d3c34]/10 text-[#1d3c34] px-3 py-1 text-xs font-bold">
                                        <span id="modal-selected-count">0</span> / {{ $totalPermsCount }} Selected
                                    </span>
                                    <input type="text" id="modal-perm-search" oninput="filterModalPermissions(this.value)" placeholder="Search permissions..." class="w-48 rounded-lg border border-[#d9cab3] bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                                </div>
                            </div>

                            {{-- Categories Accordion Grid in Modal --}}
                            <div class="grid gap-3 max-h-72 overflow-y-auto pr-1" id="modal-categories-container">
                                @foreach ($allPermissions->groupBy('group') as $groupKey => $groupPermissions)
                                    @php
                                        $meta = $categoryMetadata[$groupKey] ?? ['label' => ucfirst(str_replace('_', ' ', $groupKey)), 'icon' => '📁'];
                                        $groupPermIds = $groupPermissions->pluck('id')->all();
                                    @endphp
                                    <div class="modal-category-card rounded-xl border border-[#e7ddca] bg-white p-3.5 transition" data-group="{{ $groupKey }}">
                                        <div class="flex items-center justify-between border-b border-[#f0e8dc] pb-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-base">{{ $meta['icon'] }}</span>
                                                <h5 class="text-xs font-bold text-[#1d3c34]">{{ $meta['label'] }}</h5>
                                                <span class="modal-category-badge rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600" data-group="{{ $groupKey }}" data-total="{{ count($groupPermIds) }}">
                                                    0 / {{ count($groupPermIds) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" onclick="modalSelectCategory('{{ $groupKey }}')" class="rounded-md bg-[#dfeee4] px-2 py-0.5 text-[10px] font-bold text-[#1d3c34] hover:bg-[#1d3c34] hover:text-white transition cursor-pointer">
                                                    + Select All
                                                </button>
                                                <button type="button" onclick="modalDeselectCategory('{{ $groupKey }}')" class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 hover:bg-slate-200 transition cursor-pointer">
                                                    - Deselect
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-2.5 grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                                            @foreach ($groupPermissions as $permission)
                                                <label class="modal-perm-item flex cursor-pointer items-start gap-2 rounded-lg border border-[#f0e8dc] p-2 hover:bg-[#fbf9f5] transition" data-search="{{ strtolower($permission->label . ' ' . $permission->key) }}">
                                                    <input type="checkbox" name="granted_permission_ids[]" value="{{ $permission->id }}" onchange="updateModalPermState()" class="modal-perm-checkbox mt-0.5 h-3.5 w-3.5 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#1d3c34]" data-group="{{ $groupKey }}">
                                                    <div class="min-w-0">
                                                        <p class="text-[11px] font-semibold text-slate-700 leading-tight">{{ $permission->label }}</p>
                                                        <p class="text-[9px] font-mono text-slate-400 truncate">{{ $permission->key }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Reason for update (recorded in immutable audit trail)</label>
                                <input type="text" name="reason" placeholder="e.g. Granular role permissions updated by administrator" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-[#1d3c34]">
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="border-t border-[#e7ddca] bg-white px-6 py-3.5 flex items-center justify-end gap-3">
                            <button type="button" onclick="closeUserPermissionsModal()" class="rounded-full border border-[#d9cab3] px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2 text-xs font-bold text-white hover:bg-[#254d43] transition shadow-xs cursor-pointer">
                                Save User Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                (function() {
                    const roleDefaultsMap = @json($roleDefaultPermsMap ?? []);
                    const usersPermissionsMap = @json($usersPermissionsMap ?? []);
                    let modalTargetUserId = null;
                    let modalTargetRole = null;

                    // ── FORM LOGIC ───────────────────────────────────────────────
                    window.setFormPermissionMode = function(mode) {
                        const modeInput = document.getElementById('form-permission-mode');
                        if (modeInput) modeInput.value = mode;

                        const radio = document.querySelector(`input[name="form_mode_radio"][value="${mode}"]`);
                        if (radio) radio.checked = true;

                        if (mode === 'role_default') {
                            formLoadRoleDefaults();
                        } else if (mode === 'all') {
                            formSelectAll();
                        }
                    };

                    window.formSelectAll = function() {
                        document.querySelectorAll('.form-perm-checkbox').forEach(cb => cb.checked = true);
                        updateFormPermState();
                    };

                    window.formClearAll = function() {
                        document.querySelectorAll('.form-perm-checkbox').forEach(cb => cb.checked = false);
                        updateFormPermState();
                    };

                    window.formLoadRoleDefaults = function() {
                        const roleSelect = document.getElementById('user-form-role-select');
                        const role = roleSelect ? roleSelect.value : 'buyer';
                        const defaultIds = roleDefaultsMap[role] || [];
                        const defaultSet = new Set(defaultIds.map(Number));

                        document.querySelectorAll('.form-perm-checkbox').forEach(cb => {
                            cb.checked = defaultSet.has(Number(cb.value));
                        });
                        updateFormPermState();
                    };

                    window.formSelectCategory = function(groupKey) {
                        document.querySelectorAll(`.form-perm-checkbox[data-group="${groupKey}"]`).forEach(cb => cb.checked = true);
                        updateFormPermState();
                    };

                    window.formDeselectCategory = function(groupKey) {
                        document.querySelectorAll(`.form-perm-checkbox[data-group="${groupKey}"]`).forEach(cb => cb.checked = false);
                        updateFormPermState();
                    };

                    window.onUserFormRoleChanged = function(newRole) {
                        const modeInput = document.getElementById('form-permission-mode');
                        if (modeInput && modeInput.value === 'role_default') {
                            formLoadRoleDefaults();
                        }
                    };

                    window.filterFormPermissions = function(query) {
                        const q = query.trim().toLowerCase();
                        document.querySelectorAll('.form-category-card').forEach(card => {
                            let cardHasMatch = false;
                            card.querySelectorAll('.form-perm-item').forEach(item => {
                                const text = item.getAttribute('data-search') || '';
                                if (!q || text.includes(q)) {
                                    item.classList.remove('hidden');
                                    cardHasMatch = true;
                                } else {
                                    item.classList.add('hidden');
                                }
                            });
                            if (!q || cardHasMatch) {
                                card.classList.remove('hidden');
                            } else {
                                card.classList.add('hidden');
                            }
                        });
                    };

                    window.updateFormPermState = function() {
                        const allBoxes = document.querySelectorAll('.form-perm-checkbox');
                        let totalChecked = 0;
                        const groupCheckedCount = {};

                        allBoxes.forEach(cb => {
                            const group = cb.getAttribute('data-group');
                            if (!groupCheckedCount[group]) groupCheckedCount[group] = 0;
                            if (cb.checked) {
                                totalChecked++;
                                groupCheckedCount[group]++;
                            }
                        });

                        const counter = document.getElementById('form-selected-count');
                        if (counter) counter.textContent = totalChecked;

                        document.querySelectorAll('.form-category-badge').forEach(badge => {
                            const group = badge.getAttribute('data-group');
                            const total = badge.getAttribute('data-total');
                            const count = groupCheckedCount[group] || 0;
                            badge.textContent = `${count} / ${total}`;
                            if (count > 0) {
                                badge.className = 'form-category-badge rounded-full bg-[#1d3c34]/15 px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]';
                            } else {
                                badge.className = 'form-category-badge rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500';
                            }
                        });
                    };

                    // ── MODAL LOGIC ──────────────────────────────────────────────
                    window.openUserPermissionsModal = function(userId, userName, userRole, userEmail) {
                        modalTargetUserId = userId;
                        modalTargetRole = userRole;

                        const form = document.getElementById('modal-permissions-form');
                        if (form) form.action = `/dashboard/users/${userId}/permissions`;

                        const title = document.getElementById('modal-user-title');
                        if (title) title.textContent = `Permissions for ${userName}`;

                        const subtitle = document.getElementById('modal-user-subtitle');
                        if (subtitle) subtitle.textContent = `${userEmail} • Role: ${userRole}`;

                        const userData = usersPermissionsMap[userId] || null;
                        const grantedIds = userData ? (userData.granted || []) : (roleDefaultsMap[userRole] || []);
                        const grantedSet = new Set(grantedIds.map(Number));

                        document.querySelectorAll('.modal-perm-checkbox').forEach(cb => {
                            cb.checked = grantedSet.has(Number(cb.value));
                        });

                        const initialMode = userData && userData.mode === 'role_default' ? 'role_default' : 'custom';
                        setModalPermissionMode(initialMode);

                        updateModalPermState();

                        const search = document.getElementById('modal-perm-search');
                        if (search) {
                            search.value = '';
                            filterModalPermissions('');
                        }

                        const modal = document.getElementById('quick-permissions-modal');
                        if (modal) modal.classList.remove('hidden');
                    };

                    window.closeUserPermissionsModal = function() {
                        const modal = document.getElementById('quick-permissions-modal');
                        if (modal) modal.classList.add('hidden');
                    };

                    window.setModalPermissionMode = function(mode) {
                        const modeInput = document.getElementById('modal-permission-mode');
                        if (modeInput) modeInput.value = mode;

                        const radio = document.querySelector(`input[name="modal_mode_radio"][value="${mode}"]`);
                        if (radio) radio.checked = true;

                        if (mode === 'role_default') {
                            modalLoadRoleDefaults();
                        } else if (mode === 'all') {
                            modalSelectAll();
                        }
                    };

                    window.modalSelectAll = function() {
                        document.querySelectorAll('.modal-perm-checkbox').forEach(cb => cb.checked = true);
                        updateModalPermState();
                    };

                    window.modalClearAll = function() {
                        document.querySelectorAll('.modal-perm-checkbox').forEach(cb => cb.checked = false);
                        updateModalPermState();
                    };

                    window.modalLoadRoleDefaults = function() {
                        const defaultIds = roleDefaultsMap[modalTargetRole] || [];
                        const defaultSet = new Set(defaultIds.map(Number));

                        document.querySelectorAll('.modal-perm-checkbox').forEach(cb => {
                            cb.checked = defaultSet.has(Number(cb.value));
                        });
                        updateModalPermState();
                    };

                    window.modalSelectCategory = function(groupKey) {
                        document.querySelectorAll(`.modal-perm-checkbox[data-group="${groupKey}"]`).forEach(cb => cb.checked = true);
                        updateModalPermState();
                    };

                    window.modalDeselectCategory = function(groupKey) {
                        document.querySelectorAll(`.modal-perm-checkbox[data-group="${groupKey}"]`).forEach(cb => cb.checked = false);
                        updateModalPermState();
                    };

                    window.filterModalPermissions = function(query) {
                        const q = query.trim().toLowerCase();
                        document.querySelectorAll('.modal-category-card').forEach(card => {
                            let cardHasMatch = false;
                            card.querySelectorAll('.modal-perm-item').forEach(item => {
                                const text = item.getAttribute('data-search') || '';
                                if (!q || text.includes(q)) {
                                    item.classList.remove('hidden');
                                    cardHasMatch = true;
                                } else {
                                    item.classList.add('hidden');
                                }
                            });
                            if (!q || cardHasMatch) {
                                card.classList.remove('hidden');
                            } else {
                                card.classList.add('hidden');
                            }
                        });
                    };

                    window.updateModalPermState = function() {
                        const allBoxes = document.querySelectorAll('.modal-perm-checkbox');
                        let totalChecked = 0;
                        const groupCheckedCount = {};

                        allBoxes.forEach(cb => {
                            const group = cb.getAttribute('data-group');
                            if (!groupCheckedCount[group]) groupCheckedCount[group] = 0;
                            if (cb.checked) {
                                totalChecked++;
                                groupCheckedCount[group]++;
                            }
                        });

                        const counter = document.getElementById('modal-selected-count');
                        if (counter) counter.textContent = totalChecked;

                        document.querySelectorAll('.modal-category-badge').forEach(badge => {
                            const group = badge.getAttribute('data-group');
                            const total = badge.getAttribute('data-total');
                            const count = groupCheckedCount[group] || 0;
                            badge.textContent = `${count} / ${total}`;
                            if (count > 0) {
                                badge.className = 'modal-category-badge rounded-full bg-[#1d3c34]/15 px-2 py-0.5 text-[10px] font-bold text-[#1d3c34]';
                            } else {
                                badge.className = 'modal-category-badge rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500';
                            }
                        });
                    };

                    // Initial state calculation
                    document.addEventListener('DOMContentLoaded', function() {
                        updateFormPermState();
                    });
                })();
            </script>
        </section>
