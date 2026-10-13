{{--
    Signed-in user block shown at the bottom of every dashboard sidebar:
    avatar/initials, name, role label, and a logout button.

    Optional variables:
        $roleLabel  string  Role label shown under the name. Defaults to the
                            persona label resolved from config('personas.personas')
                            by the user's role (or its legacy roles).
        $profileUrl string  When set, the avatar/name row links to this URL
                            (e.g. "#profile" on the client portal).
        $variant    string  "dark" (default) for dark sidebars, "light" for
                            light pages (renders a dark-green box instead).
--}}
@php
    $signedInUser = auth()->user();

    $signedInPersona = collect(config('personas.personas'))
        ->first(fn (array $persona) => ($persona['role'] ?? null) === $signedInUser->role
            || in_array($signedInUser->role ?? '', $persona['legacy_roles'] ?? [], true));

    $resolvedRoleLabel = $roleLabel
        ?? ($signedInPersona['label'] ?? ucfirst(str_replace('_', ' ', $signedInUser->role ?? 'Member')));

    $signedInBoxClasses = ($variant ?? 'dark') === 'light'
        ? 'mt-8 rounded-[1.25rem] border border-[#102b25]/10 bg-[#1d3c34] p-4'
        : 'mt-8 rounded-[1.25rem] border border-white/10 bg-white/5 p-4';
@endphp
<div class="{{ $signedInBoxClasses }}">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Signed in</p>

    @if (! empty($profileUrl))
        <a href="{{ $profileUrl }}"
            @if (str_starts_with($profileUrl, '#')) data-target-section="{{ substr($profileUrl, 1) }}" @endif
            class="group mt-3 flex items-center gap-3 cursor-pointer {{ str_starts_with($profileUrl, '#') ? 'nav-section-link' : '' }}"
            title="Edit Profile">
    @else
        <div class="mt-3 flex items-center gap-3">
    @endif
            @if ($signedInUser->photo_url)
                <img src="{{ $signedInUser->photo_url }}" alt="{{ $signedInUser->name }}" class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-[#d9cab3]">
            @else
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#d9cab3]/20 text-sm font-bold text-white">
                    {{ $signedInUser->initials ?: 'GT' }}
                </span>
            @endif
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white group-hover:text-[#e4c76f] transition">{{ $signedInUser->name }}</p>
                <p class="truncate text-xs text-[#dfeee4]">{{ $resolvedRoleLabel }}</p>
            </div>
    @if (! empty($profileUrl))
        </a>
    @else
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full rounded-xl bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34] transition hover:bg-white cursor-pointer">
            Logout
        </button>
    </form>
</div>
