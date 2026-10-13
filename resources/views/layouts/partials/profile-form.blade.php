{{--
    Shared self-service profile form.
    Every signed-in user edits their own profile; email (the login identity) is
    displayed read-only and only an administrator can change it.
    Expects: $user, $profileAction, $profileHeading, $profileSubheading, $profileRoleLabel
--}}
<div class="flex items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-[#1d3c34]">{{ $profileHeading }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ $profileSubheading }}</p>
    </div>
    <span class="rounded-full bg-[#dfeee4] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $profileRoleLabel }}</span>
</div>

<form method="POST" action="{{ $profileAction }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-[1.25rem] border border-[#e7ddca] bg-[#fdfaf5] p-6">
    @csrf

    <div class="flex items-center gap-4">
        @if ($user->photo_url)
            <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-[#d9cab3]">
        @else
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-[#1d3c34] text-[#e4c76f] ring-2 ring-[#d9cab3]">
                <svg class="h-10 w-10 text-[#d4af37]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                </svg>
            </span>
        @endif
        <div class="flex-1">
            <label class="mb-1 block text-sm font-semibold text-slate-700">Profile Photo</label>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WebP up to 2 MB. Your current photo is replaced when you upload a new one.</p>
            @error('photo')
                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
        @error('name')
            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">Phone Number (Ethiopian)</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+251 9... / 09..." class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
        @error('phone')
            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">Email Address</label>
        <input type="email" value="{{ $user->email }}" readonly disabled class="w-full cursor-not-allowed rounded-xl border border-[#d9cab3] bg-[#f4efe4] px-3 py-2.5 text-sm text-slate-500">
        <p class="mt-1 text-xs text-slate-500">Your email signs you in, so only an administrator can change it.</p>
    </div>

    <div>
        <div class="mb-1 flex items-center justify-between">
            <label class="block text-sm font-semibold text-slate-700">New Password (optional)</label>
            <button type="button" onclick="suggestProfilePassword('profile-form-password')" class="text-xs font-semibold text-[#1d3c34] hover:underline">
                Suggest Strong Password
            </button>
        </div>
        <input id="profile-form-password" type="password" name="password" minlength="8" placeholder="Leave blank to keep your current password" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
        <p class="mt-1 text-xs text-slate-500">If changing, must be at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
        @error('password')
            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($showPortalBg ?? false)
        <div class="border-t border-[#e7ddca] pt-5">
            <label class="mb-1 block text-sm font-semibold text-slate-700">Portal Banner Background Image</label>
            <p class="mb-3 text-xs text-slate-500">Customize the hero banner backdrop of your client portal. Upload a custom photo or choose a luxury theme preset.</p>
            
            <div class="relative mb-3 h-32 w-full overflow-hidden rounded-xl border border-[#d9cab3] bg-[#102b25] shadow-xs">
                <img id="portal-bg-preview" src="{{ $user->portal_bg_url ?? asset('images/luxury/hero-skyline.jpg') }}" alt="Banner Background Preview" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-black/20 flex items-end p-2.5">
                    <span class="rounded-md bg-black/60 px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider backdrop-blur-xs">Current Banner Preview</span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Upload Custom Banner</label>
                    <input type="file" id="portal-bg-input" name="portal_bg" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    <p class="mt-1 text-[11px] text-slate-500">JPG, PNG, or WebP up to 6 MB.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Or Choose a Luxury Theme Preset</label>
                    <input type="hidden" name="portal_bg_preset" id="portal-bg-preset-input" value="">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @php
                            $presets = [
                                ['name' => 'Addis Skyline', 'file' => 'images/luxury/hero-skyline.jpg'],
                                ['name' => 'Luxury Towers', 'file' => 'images/luxury/luxury-towers.jpg'],
                                ['name' => 'Central Plaza', 'file' => 'images/luxury/central-plaza.jpg'],
                                ['name' => 'Panoramic Park', 'file' => 'images/luxury/panoramic-park.jpg'],
                                ['name' => 'Retail Boulevard', 'file' => 'images/luxury/retail-boulevard.jpg'],
                            ];
                        @endphp
                        @foreach ($presets as $preset)
                            <button type="button" onclick="selectPortalBgPreset('{{ $preset['file'] }}')" class="portal-bg-preset-btn group relative overflow-hidden rounded-xl border border-[#d9cab3] text-left transition hover:border-[#1d3c34] focus:outline-none cursor-pointer">
                                <img src="{{ asset($preset['file']) }}" alt="{{ $preset['name'] }}" class="h-14 w-full object-cover transition group-hover:scale-105">
                                <div class="bg-white/95 px-2 py-1 text-center">
                                    <span class="block truncate text-[10px] font-bold text-[#1d3c34]">{{ $preset['name'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($user->portal_bg_path)
                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remove_portal_bg" value="1" class="rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#dfeee4]">
                            <span class="text-xs text-slate-600">Reset to default luxury skyline banner</span>
                        </label>
                    </div>
                @endif
            </div>
            @error('portal_bg')
                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="pt-2">
        <button type="submit" class="rounded-full bg-[#1d3c34] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#254d43] cursor-pointer">
            Update Profile
        </button>
    </div>
</form>

<script>
    if (typeof window.suggestProfilePassword !== 'function') {
        window.suggestProfilePassword = function (inputId) {
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lower = 'abcdefghjkmnpqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%&*?+';

            let pwd = upper[Math.floor(Math.random() * upper.length)]
                + lower[Math.floor(Math.random() * lower.length)]
                + numbers[Math.floor(Math.random() * numbers.length)]
                + symbols[Math.floor(Math.random() * symbols.length)];

            const all = upper + lower + numbers + symbols;
            for (let i = 4; i < 16; i++) {
                pwd += all[Math.floor(Math.random() * all.length)];
            }

            const input = document.getElementById(inputId);
            if (input) {
                input.value = pwd.split('').sort(() => 0.5 - Math.random()).join('');
                input.type = 'text';
            }
        };
    }

    if (typeof window.selectPortalBgPreset !== 'function') {
        window.selectPortalBgPreset = function (presetUrl) {
            const preview = document.getElementById('portal-bg-preview');
            const presetInput = document.getElementById('portal-bg-preset-input');
            const fileInput = document.getElementById('portal-bg-input');
            if (preview) preview.src = '/' + presetUrl;
            if (presetInput) presetInput.value = presetUrl;
            if (fileInput) fileInput.value = '';
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const portalBgInput = document.getElementById('portal-bg-input');
        if (portalBgInput) {
            portalBgInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (file) {
                    const preview = document.getElementById('portal-bg-preview');
                    const presetInput = document.getElementById('portal-bg-preset-input');
                    if (presetInput) presetInput.value = '';
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        if (preview) preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
