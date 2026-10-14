{{-- Profile Management Modal --}}
<div id="profile-management-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-xs transition-opacity duration-200" aria-labelledby="profile-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-6">
        <div class="relative w-full max-w-3xl transform overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white p-6 text-left shadow-2xl transition-all dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-[#e7ddca] pb-4 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#1d3c34] text-lg text-[#d4af37] shadow-sm">
                        👤
                    </span>
                    <div>
                        <h3 id="profile-modal-title" class="text-xl font-black text-[#1d3c34] dark:text-white">Profile Management</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Account settings, credentials, security, and notification preferences</p>
                    </div>
                </div>
                <button type="button" onclick="closeProfileModal()" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 cursor-pointer" aria-label="Close modal">
                    ✕
                </button>
            </div>

            {{-- Tabs Navigation --}}
            <div class="mt-4 flex flex-wrap gap-1.5 border-b border-[#e7ddca] pb-3 text-xs font-bold dark:border-slate-800" role="tablist">
                <button type="button" onclick="switchProfileTab('info')" id="tab-btn-info" class="profile-tab-btn rounded-xl px-3 py-1.5 bg-[#1d3c34] text-white cursor-pointer transition">
                    👤 Information
                </button>
                <button type="button" onclick="switchProfileTab('photo')" id="tab-btn-photo" class="profile-tab-btn rounded-xl px-3 py-1.5 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer transition">
                    📸 Photo
                </button>
                <button type="button" onclick="switchProfileTab('security')" id="tab-btn-security" class="profile-tab-btn rounded-xl px-3 py-1.5 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer transition">
                    🔐 Password &amp; 2FA
                </button>
                <button type="button" onclick="switchProfileTab('preferences')" id="tab-btn-preferences" class="profile-tab-btn rounded-xl px-3 py-1.5 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer transition">
                    ⚙️ Preferences
                </button>
                <button type="button" onclick="switchProfileTab('history')" id="tab-btn-history" class="profile-tab-btn rounded-xl px-3 py-1.5 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer transition">
                    📜 Login History
                </button>
                <button type="button" onclick="switchProfileTab('deactivate')" id="tab-btn-deactivate" class="profile-tab-btn rounded-xl px-3 py-1.5 text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30 cursor-pointer transition">
                    ⚠️ Deactivate
                </button>
            </div>

            {{-- Tab 1: Profile Information --}}
            <div id="profile-tab-info" class="profile-tab-content mt-5 space-y-4">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4" onsubmit="handleProfileFormSubmit(event, this)">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Full Name *</label>
                            <input type="text" name="name" value="{{ auth()->user()->name }}" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-[#1d3c34] focus:ring-1 focus:ring-[#1d3c34] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Phone Number</label>
                            <input type="text" name="phone" value="{{ auth()->user()->phone ?? '' }}" placeholder="+251 91 123 4567" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-[#1d3c34] focus:ring-1 focus:ring-[#1d3c34] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Email Address (Login Identity)</label>
                        <div class="mt-1 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 dark:border-slate-800 dark:bg-slate-800/50">
                            <span class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ auth()->user()->email }}</span>
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-300">Managed by Admin</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Assigned System Role</label>
                        <div class="mt-1 inline-flex items-center gap-2 rounded-xl bg-[#f8f3eb] px-3 py-1.5 border border-[#d9cab3] dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-xs font-black uppercase text-[#9b6c17] dark:text-[#f8e7b9]">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Member')) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab 2: Photo --}}
            <div id="profile-tab-photo" class="profile-tab-content mt-5 hidden space-y-4">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4" onsubmit="handleProfileFormSubmit(event, this)">
                    @csrf
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <div class="flex items-center gap-5">
                        <div class="relative shrink-0">
                            @if (auth()->user()->photo_url)
                                <img id="profile-avatar-preview" src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-[#d9cab3]">
                            @else
                                <span id="profile-avatar-preview-fallback" class="flex h-20 w-20 items-center justify-center rounded-full bg-[#1d3c34] text-xl font-black text-[#d4af37] ring-4 ring-[#d9cab3]">
                                    {{ auth()->user()->initials ?: 'GT' }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Upload New Photo</label>
                            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-[#1d3c34] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#254d43]">
                            <p class="text-[11px] text-slate-400">PNG, JPG or WebP up to 2MB. Recommended 400x400 square.</p>
                        </div>
                    </div>
                    @if (auth()->user()->photo_path)
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-rose-600 hover:text-rose-800 cursor-pointer">
                                <input type="checkbox" name="remove_photo" value="1" class="rounded border-rose-300 text-rose-600">
                                <span>Remove current profile photo and revert to initials</span>
                            </label>
                        </div>
                    @endif
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer shadow-sm">
                            Update Photo
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab 3: Security & Password + 2FA --}}
            <div id="profile-tab-security" class="profile-tab-content mt-5 hidden space-y-5">
                <form method="POST" action="{{ route('profile.security') }}" class="space-y-4" onsubmit="handleProfileFormSubmit(event, this)">
                    @csrf
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-[#d4af37]">Change Password</h4>
                        <p class="text-[11px] text-slate-500">Ensure your password has at least 8 characters with letters, numbers, and symbols.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Current Password</label>
                        <input type="password" name="current_password" placeholder="••••••••" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">New Password</label>
                            <input type="password" name="password" placeholder="••••••••" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Confirm New Password</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="border-t border-[#e7ddca] pt-4 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-[#d4af37]">Two-Factor Authentication (2FA / OTP)</h4>
                                <p class="text-[11px] text-slate-500">Require an additional one-time passcode for critical operations and logins.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="two_factor_enabled" value="0">
                                <input type="checkbox" name="two_factor_enabled" value="1" {{ auth()->user()->two_factor_enabled ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 dark:bg-slate-700"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer shadow-sm">
                            Save Security Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab 4: Preferences (Notifications & Language) --}}
            <div id="profile-tab-preferences" class="profile-tab-content mt-5 hidden space-y-5">
                <form method="POST" action="{{ route('profile.preferences') }}" class="space-y-4" onsubmit="handleProfileFormSubmit(event, this)">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Preferred Language</label>
                        <select name="language" class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <option value="en" {{ (auth()->user()->language ?? app()->getLocale()) === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                            <option value="am" {{ (auth()->user()->language ?? app()->getLocale()) === 'am' ? 'selected' : '' }}>🇪🇹 አማርኛ (Amharic)</option>
                            <option value="om" {{ (auth()->user()->language ?? app()->getLocale()) === 'om' ? 'selected' : '' }}>🇪🇹 Afaan Oromoo</option>
                        </select>
                    </div>

                    @php
                        $notifPrefs = auth()->user()->notification_preferences ?? ['email' => true, 'in_app' => true, 'sms' => false];
                    @endphp
                    <div class="border-t border-[#e7ddca] pt-4 dark:border-slate-800">
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-[#d4af37]">Notification Channels</h4>
                        <div class="mt-3 space-y-2.5 text-xs">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[email]" value="1" {{ !empty($notifPrefs['email']) ? 'checked' : '' }} class="rounded border-[#d9cab3] text-[#1d3c34]">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">Email Notifications (Offers, Inquiries, Bookings)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[in_app]" value="1" {{ !empty($notifPrefs['in_app']) ? 'checked' : '' }} class="rounded border-[#d9cab3] text-[#1d3c34]">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">In-App Live Alerts &amp; Bell Notifications</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[sms]" value="1" {{ !empty($notifPrefs['sms']) ? 'checked' : '' }} class="rounded border-[#d9cab3] text-[#1d3c34]">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">SMS Alerts for Instant Booking Confirmations</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#254d43] transition cursor-pointer shadow-sm">
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab 5: Login History --}}
            <div id="profile-tab-history" class="profile-tab-content mt-5 hidden space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#1d3c34] dark:text-[#d4af37]">Recent Authentication History</h4>
                        <p class="text-[11px] text-slate-500">Security audit trail of recent sign-ins from your devices.</p>
                    </div>
                    <button type="button" onclick="loadLoginHistory()" class="text-xs font-bold text-[#1d3c34] hover:underline dark:text-[#d4af37]">
                        🔄 Refresh
                    </button>
                </div>
                <div class="max-h-60 overflow-y-auto rounded-2xl border border-[#e7ddca] dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#fbf8f3] dark:bg-slate-800 font-bold text-slate-600 dark:text-slate-300 border-b border-[#e7ddca] dark:border-slate-700">
                            <tr>
                                <th class="p-3">Event</th>
                                <th class="p-3">IP Address</th>
                                <th class="p-3">Device / User Agent</th>
                                <th class="p-3 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody id="login-history-tbody" class="divide-y divide-[#f0e8d8] dark:divide-slate-800 font-medium">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-400">Loading audit history...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 6: Deactivate / Delete Account --}}
            <div id="profile-tab-deactivate" class="profile-tab-content mt-5 hidden space-y-4">
                <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 text-xs dark:border-rose-900/50 dark:bg-rose-950/20">
                    <h4 class="font-black text-rose-900 dark:text-rose-200">Account Deactivation or Deletion</h4>
                    <p class="mt-1 text-rose-800/80 dark:text-rose-300/80">
                        Deactivating suspends your public activity and hides your listings until you sign in again. Deletion will flag your records for permanent administrative review and scrub personal details.
                    </p>
                </div>
                <form method="POST" action="{{ route('profile.deactivate') }}" class="space-y-4" data-deactivate-account-form>
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Action Type *</label>
                        <select name="action_type" required class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <option value="deactivate">Temporary Deactivation</option>
                            <option value="delete">Permanent Account Deletion Request</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Reason for leaving *</label>
                        <textarea name="reason" rows="3" required placeholder="Please let us know why you are deactivating or requesting deletion..." class="mt-1 w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-full bg-rose-700 px-6 py-2.5 text-xs font-bold text-white hover:bg-rose-800 transition cursor-pointer shadow-sm">
                            Submit Request &amp; Sign Out
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openProfileModal(tab = 'info') {
        const modal = document.getElementById('profile-management-modal');
        if (modal) {
            modal.classList.remove('hidden');
            switchProfileTab(tab);
        }
    }

    function closeProfileModal() {
        const modal = document.getElementById('profile-management-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function switchProfileTab(tab) {
        document.querySelectorAll('.profile-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#1d3c34]', 'text-white');
            btn.classList.add('text-slate-600');
        });

        const activeContent = document.getElementById('profile-tab-' + tab);
        const activeBtn = document.getElementById('tab-btn-' + tab);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600');
            activeBtn.classList.add('bg-[#1d3c34]', 'text-white');
        }

        if (tab === 'history') {
            loadLoginHistory();
        }
    }

    async function loadLoginHistory() {
        const tbody = document.getElementById('login-history-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-400">Loading audit history...</td></tr>';

        try {
            const res = await fetch('{{ route('profile.history') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.history && data.history.length > 0) {
                tbody.innerHTML = data.history.map(row => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-3 font-bold text-[#1d3c34] dark:text-[#d4af37]">${row.action}</td>
                        <td class="p-3 font-mono text-slate-600 dark:text-slate-300">${row.ip_address}</td>
                        <td class="p-3 text-slate-500 truncate max-w-xs" title="${row.device}">${row.device}</td>
                        <td class="p-3 text-right text-slate-400">${row.timestamp}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-400">No login history recorded yet.</td></tr>';
            }
        } catch (_) {
            tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-rose-500">Failed to load login history.</td></tr>';
        }
    }

    async function handleProfileFormSubmit(e, form) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerText : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();

            if (res.ok && data.success) {
                if (typeof showDashboardToast === 'function') {
                    showDashboardToast(data.message || 'Updated successfully.', 'success');
                } else {
                    alert(data.message || 'Updated successfully.');
                }
                setTimeout(() => window.location.reload(), 700);
            } else {
                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error updating profile.');
                if (typeof showDashboardToast === 'function') {
                    showDashboardToast(errMsg, 'error');
                } else {
                    alert(errMsg);
                }
            }
        } catch (err) {
            if (typeof showDashboardToast === 'function') {
                showDashboardToast('Network error while saving.', 'error');
            } else {
                alert('Network error while saving.');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = originalText;
            }
        }
    }

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.matches('[data-deactivate-account-form]') && !confirm('Are you sure you want to proceed with this account action? You will be signed out.')) {
            e.preventDefault();
        }
    });
</script>
