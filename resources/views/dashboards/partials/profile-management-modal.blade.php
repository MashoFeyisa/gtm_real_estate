{{-- Profile Management Modal --}}
<div id="profile-management-modal" class="fixed inset-0 z-50 hidden overflow-y-auto token-modal-overlay backdrop-blur-xs transition-opacity duration-200" aria-labelledby="profile-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-6">
        <div class="token-modal relative w-full max-w-3xl transform overflow-hidden rounded-[2rem] p-6 text-left shadow-2xl transition-all sm:p-8">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-[var(--token-border-subtle)] pb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[var(--token-primary)] text-lg text-[var(--token-accent-light)] shadow-sm">
                        👤
                    </span>
                    <div>
                        <h3 id="profile-modal-title" class="text-xl font-black">Profile Management</h3>
                        <p class="token-muted text-xs">Account settings, credentials, security, and notification preferences</p>
                    </div>
                </div>
                <button type="button" onclick="closeProfileModal()" class="token-btn token-btn-secondary !rounded-full !p-0 h-9 w-9 cursor-pointer" aria-label="Close modal">
                    ✕
                </button>
            </div>

            {{-- Tabs Navigation --}}
            <div class="mt-4 flex flex-wrap gap-1.5 border-b border-[var(--token-border-subtle)] pb-3 text-xs font-bold" role="tablist">
                <button type="button" onclick="switchProfileTab('info')" id="tab-btn-info" class="profile-tab-btn token-btn token-btn-primary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    👤 Information
                </button>
                <button type="button" onclick="switchProfileTab('photo')" id="tab-btn-photo" class="profile-tab-btn token-btn token-btn-secondary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    📸 Photo
                </button>
                <button type="button" onclick="switchProfileTab('security')" id="tab-btn-security" class="profile-tab-btn token-btn token-btn-secondary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    🔐 Password &amp; 2FA
                </button>
                <button type="button" onclick="switchProfileTab('preferences')" id="tab-btn-preferences" class="profile-tab-btn token-btn token-btn-secondary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    ⚙️ Preferences
                </button>
                <button type="button" onclick="switchProfileTab('history')" id="tab-btn-history" class="profile-tab-btn token-btn token-btn-secondary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    📜 Login History
                </button>
                <button type="button" onclick="switchProfileTab('deactivate')" id="tab-btn-deactivate" class="profile-tab-btn token-btn token-btn-secondary cursor-pointer !rounded-xl !px-3 !py-1.5 !text-xs">
                    ⚠️ Deactivate
                </button>
            </div>

            {{-- Tab 1: Profile Information --}}
            <div id="profile-tab-info" class="profile-tab-content mt-5 space-y-4">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4" onsubmit="handleProfileFormSubmit(event, this)">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="token-label uppercase tracking-wider">Full Name *</label>
                            <input type="text" name="name" value="{{ auth()->user()->name }}" required class="token-field mt-1 !text-xs font-semibold">
                        </div>
                        <div>
                            <label class="token-label uppercase tracking-wider">Phone Number</label>
                            <input type="text" name="phone" value="{{ auth()->user()->phone ?? '' }}" placeholder="+251 91 123 4567" class="token-field mt-1 !text-xs font-semibold">
                        </div>
                    </div>
                    <div>
                        <label class="token-label uppercase tracking-wider">Email Address (Login Identity)</label>
                        <div class="mt-1 flex items-center justify-between rounded-xl border border-[var(--token-border)] bg-[var(--token-surface-subtle)] px-3.5 py-2.5">
                            <span class="token-muted text-xs font-mono">{{ auth()->user()->email }}</span>
                            <span class="token-badge token-badge-neutral !text-[10px]">Managed by Admin</span>
                        </div>
                    </div>
                    <div>
                        <label class="token-label uppercase tracking-wider">Assigned System Role</label>
                        <div class="mt-1">
                            <span class="token-badge token-badge-accent uppercase">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'Member')) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="token-btn token-btn-primary rounded-full !px-6 cursor-pointer shadow-sm">
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
                                <img id="profile-avatar-preview" src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-[var(--token-border)]">
                            @else
                                <span id="profile-avatar-preview-fallback" class="flex h-20 w-20 items-center justify-center rounded-full bg-[var(--token-primary)] text-xl font-black text-[var(--token-accent-light)] ring-4 ring-[var(--token-border)]">
                                    {{ auth()->user()->initials ?: 'GT' }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="token-label uppercase tracking-wider">Upload New Photo</label>
                            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="block w-full token-muted text-xs file:mr-3 file:rounded-full file:border-0 file:bg-[var(--token-primary)] file:px-4 file:py-2 file:text-xs file:font-bold file:text-[var(--token-text-on-color)] hover:file:bg-[var(--token-primary-hover)]">
                            <p class="token-muted text-[11px]">PNG, JPG or WebP up to 2MB. Recommended 400x400 square.</p>
                        </div>
                    </div>
                    @if (auth()->user()->photo_path)
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-[var(--token-danger)] hover:opacity-80 cursor-pointer">
                                <input type="checkbox" name="remove_photo" value="1" class="rounded border-[var(--token-border)] accent-[var(--token-danger)]">
                                <span>Remove current profile photo and revert to initials</span>
                            </label>
                        </div>
                    @endif
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="token-btn token-btn-primary rounded-full !px-6 cursor-pointer shadow-sm">
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
                        <h4 class="token-section-heading">Change Password</h4>
                        <p class="token-muted text-[11px]">Ensure your password has at least 8 characters with letters, numbers, and symbols.</p>
                    </div>
                    <div>
                        <label class="token-label uppercase tracking-wider">Current Password</label>
                        <input type="password" name="current_password" placeholder="••••••••" class="token-field mt-1 !text-xs font-semibold">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="token-label uppercase tracking-wider">New Password</label>
                            <input type="password" name="password" placeholder="••••••••" class="token-field mt-1 !text-xs font-semibold">
                        </div>
                        <div>
                            <label class="token-label uppercase tracking-wider">Confirm New Password</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••" class="token-field mt-1 !text-xs font-semibold">
                        </div>
                    </div>

                    <div class="border-t border-[var(--token-border-subtle)] pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="token-section-heading">Two-Factor Authentication (2FA / OTP)</h4>
                                <p class="token-muted text-[11px]">Require an additional one-time passcode for critical operations and logins.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="two_factor_enabled" value="0">
                                <input type="checkbox" name="two_factor_enabled" value="1" {{ auth()->user()->two_factor_enabled ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-[var(--token-surface-hover)] peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[var(--token-text-on-color)] after:border after:border-[var(--token-border)] after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--token-success)]"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="token-btn token-btn-primary rounded-full !px-6 cursor-pointer shadow-sm">
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
                        <label class="token-label uppercase tracking-wider">Preferred Language</label>
                        <select name="language" class="token-field mt-1 !text-xs font-semibold">
                            <option value="en" {{ (auth()->user()->language ?? app()->getLocale()) === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                            <option value="am" {{ (auth()->user()->language ?? app()->getLocale()) === 'am' ? 'selected' : '' }}>🇪🇹 አማርኛ (Amharic)</option>
                            <option value="om" {{ (auth()->user()->language ?? app()->getLocale()) === 'om' ? 'selected' : '' }}>🇪🇹 Afaan Oromoo</option>
                        </select>
                    </div>

                    @php
                        $notifPrefs = auth()->user()->notification_preferences ?? ['email' => true, 'in_app' => true, 'sms' => false];
                    @endphp
                    <div class="border-t border-[var(--token-border-subtle)] pt-4">
                        <h4 class="token-section-heading">Notification Channels</h4>
                        <div class="mt-3 space-y-2.5 text-xs">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[email]" value="1" {{ !empty($notifPrefs['email']) ? 'checked' : '' }} class="rounded border-[var(--token-border)] accent-[var(--token-primary)]">
                                <span class="font-semibold">Email Notifications (Offers, Inquiries, Bookings)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[in_app]" value="1" {{ !empty($notifPrefs['in_app']) ? 'checked' : '' }} class="rounded border-[var(--token-border)] accent-[var(--token-primary)]">
                                <span class="font-semibold">In-App Live Alerts &amp; Bell Notifications</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="notification_preferences[sms]" value="1" {{ !empty($notifPrefs['sms']) ? 'checked' : '' }} class="rounded border-[var(--token-border)] accent-[var(--token-primary)]">
                                <span class="font-semibold">SMS Alerts for Instant Booking Confirmations</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="token-btn token-btn-primary rounded-full !px-6 cursor-pointer shadow-sm">
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tab 5: Login History --}}
            <div id="profile-tab-history" class="profile-tab-content mt-5 hidden space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="token-section-heading">Recent Authentication History</h4>
                        <p class="token-muted text-[11px]">Security audit trail of recent sign-ins from your devices.</p>
                    </div>
                    <button type="button" onclick="loadLoginHistory()" class="text-xs font-bold text-[var(--token-primary)] hover:underline cursor-pointer">
                        🔄 Refresh
                    </button>
                </div>
                <div class="max-h-60 overflow-y-auto rounded-2xl border border-[var(--token-border-subtle)]">
                    <table class="token-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>IP Address</th>
                                <th>Device / User Agent</th>
                                <th class="text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody id="login-history-tbody" class="font-medium">
                            <tr>
                                <td colspan="4" class="text-center token-muted">Loading audit history...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 6: Deactivate / Delete Account --}}
            <div id="profile-tab-deactivate" class="profile-tab-content mt-5 hidden space-y-4">
                <div class="token-alert token-alert-danger !p-4 !text-xs">
                    <h4 class="font-black">Account Deactivation or Deletion</h4>
                    <p class="mt-1 opacity-90">
                        Deactivating suspends your public activity and hides your listings until you sign in again. Deletion will flag your records for permanent administrative review and scrub personal details.
                    </p>
                </div>
                <form method="POST" action="{{ route('profile.deactivate') }}" class="space-y-4" data-deactivate-account-form>
                    @csrf
                    <div>
                        <label class="token-label uppercase tracking-wider">Action Type *</label>
                        <select name="action_type" required class="token-field mt-1 !text-xs font-semibold">
                            <option value="deactivate">Temporary Deactivation</option>
                            <option value="delete">Permanent Account Deletion Request</option>
                        </select>
                    </div>
                    <div>
                        <label class="token-label uppercase tracking-wider">Reason for leaving *</label>
                        <textarea name="reason" rows="3" required placeholder="Please let us know why you are deactivating or requesting deletion..." class="token-field mt-1 !text-xs font-semibold"></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="token-btn token-btn-danger rounded-full !px-6 cursor-pointer shadow-sm">
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
            btn.classList.remove('token-btn-primary', 'token-btn-secondary', 'token-btn-danger');
            btn.classList.add('token-btn-secondary');
        });

        const activeContent = document.getElementById('profile-tab-' + tab);
        const activeBtn = document.getElementById('tab-btn-' + tab);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('token-btn-secondary');
            activeBtn.classList.add(tab === 'deactivate' ? 'token-btn-danger' : 'token-btn-primary');
        }

        if (tab === 'history') {
            loadLoginHistory();
        }
    }

    async function loadLoginHistory() {
        const tbody = document.getElementById('login-history-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="text-center token-muted">Loading audit history...</td></tr>';

        try {
            const res = await fetch('{{ route('profile.history') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.history && data.history.length > 0) {
                tbody.innerHTML = data.history.map(row => `
                    <tr>
                        <td class="font-bold">${row.action}</td>
                        <td class="font-mono">${row.ip_address}</td>
                        <td class="max-w-xs" title="${row.device}">${row.device}</td>
                        <td class="text-right token-muted">${row.timestamp}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center token-muted">No login history recorded yet.</td></tr>';
            }
        } catch (_) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-[var(--token-danger)]">Failed to load login history.</td></tr>';
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
