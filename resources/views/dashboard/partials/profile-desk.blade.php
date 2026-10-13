        <section id="profile" data-section="profile" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-5">
                <div>
                    <h2 class="text-2xl font-black text-[#1d3c34]">My Admin Profile</h2>
                    <p class="mt-1 text-sm text-slate-600">Update your profile picture, account details, email address, and login password.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#dfeee4] px-3.5 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">
                    <span class="h-2 w-2 rounded-full bg-[#2d5d4d]"></span> Administrator
                </span>
            </div>

            <form method="POST" action="{{ route('dashboard.profile.update') }}" enctype="multipart/form-data" class="mt-6 max-w-2xl space-y-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#fdfaf5] p-6 sm:p-8 shadow-xs">
                @csrf

                {{-- Profile Picture Uploader & Live Preview --}}
                <div class="rounded-2xl border border-[#d9cab3] bg-white p-5 shadow-xs">
                    <label class="block text-sm font-bold text-[#1d3c34] mb-2">Profile Picture</label>
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <div class="relative shrink-0">
                            @if (auth()->user()->photo_url)
                                <img id="admin-profile-avatar-preview" src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->name }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-[#1d3c34]/20 shadow-md">
                            @else
                                <div id="admin-profile-avatar-preview" class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-[#1d3c34] to-[#2d5d4d] text-2xl font-black text-[#f8f3eb] ring-4 ring-[#d9cab3]/40 shadow-md">
                                    {{ auth()->user()->initials }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2 text-center sm:text-left">
                            <input type="file" name="photo" id="admin-profile-photo-input" accept="image/png,image/jpeg,image/webp,image/avif" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#1d3c34] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-[#254d43] focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <p class="text-[11px] text-slate-500">Upload a square photo (JPG, PNG, WebP up to 2MB). This picture is displayed at the top next to the logout button.</p>
                            @error('photo')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    @error('name')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    @error('email')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label class="block text-sm font-semibold text-slate-700">New Password (optional)</label>
                        <button type="button" onclick="generateStrongPassword('#profile-form-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                            Suggest Strong Password
                        </button>
                    </div>
                    <input id="profile-form-password" type="password" name="password" minlength="8" placeholder="Leave blank to keep your current password" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1d3c34]">
                    <p class="mt-1 text-xs text-slate-500">If changing, must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="rounded-full bg-[#1d3c34] px-7 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#254d43]">
                        Update Profile
                    </button>
                </div>
            </form>
        </section>

