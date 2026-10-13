{{--
    Role-driven capability panel.

    Shows the signed-in user's role and the capabilities their role currently
    grants (role defaults + per-user overrides, admins exempt). Rendered from
    config/personas.php so labels always match the role catalog.

    Optional inputs:
      - $panelSectionLinks: array of permission key => in-page section id
        (renders the card as an in-page jump link on sectioned dashboards).
--}}
@php
    $panelRole = auth()->user()->role ?? '';
    $panelPersonas = collect(config('personas.personas', []));
    $panelPersona = $panelPersonas->firstWhere('role', $panelRole)
        ?? $panelPersonas->first(fn (array $persona) => in_array($panelRole, $persona['legacy_roles'] ?? [], true));
    $panelCapabilities = collect($panelPersona['permissions'] ?? [])
        ->filter(fn ($capabilityLabel, $capabilityKey) => auth()->user()->hasPermission($capabilityKey));
    $panelSectionLinks = $panelSectionLinks ?? [];
    $panelPortalLinks = config('personas.portal_links', []);
    $panelRoleLabel = $panelPersona['label'] ?? ucfirst(str_replace('_', ' ', $panelRole));
@endphp

@if ($panelCapabilities->isNotEmpty())
    <div class="mb-6 rounded-[1.75rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e7ddca] pb-3">
            <div>
                <h2 class="text-lg font-black text-[#1d3c34]">Your Capabilities</h2>
                <p class="mt-0.5 text-xs text-[#587165]">You are signed in as {{ $panelRoleLabel }} &mdash; this desk shows only what your role &amp; permissions allow.</p>
            </div>
            <span class="rounded-full bg-[#102b25] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#d4af37]">{{ $panelRoleLabel }}</span>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($panelCapabilities as $capabilityKey => $capabilityLabel)
                @php
                    $capabilitySection = $panelSectionLinks[$capabilityKey] ?? null;
                    $capabilityPortalLink = $panelPortalLinks[$capabilityKey] ?? null;
                    $capabilityLinked = $capabilitySection !== null || ($capabilityPortalLink !== null && Route::has($capabilityPortalLink['route']));
                @endphp
                @if ($capabilitySection)
                    <a href="#{{ $capabilitySection }}" data-target-section="{{ $capabilitySection }}" class="nav-section-link group flex items-center justify-between gap-2 rounded-2xl border border-[#dfeee4] bg-white px-4 py-3.5 shadow-xs transition hover:border-[#1d3c34] hover:bg-[#f7fbf9]">
                @elseif ($capabilityLinked)
                    <a href="{{ route($capabilityPortalLink['route']) }}" class="group flex items-center justify-between gap-2 rounded-2xl border border-[#dfeee4] bg-white px-4 py-3.5 shadow-xs transition hover:border-[#1d3c34] hover:bg-[#f7fbf9]">
                @else
                    <div class="flex items-center justify-between gap-2 rounded-2xl border border-[#e7ddca] bg-[#fbf9f5] px-4 py-3.5">
                @endif
                    <span class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#1d3c34] text-[11px] font-black text-[#d4af37]">✓</span>
                        <span class="text-sm font-bold text-[#1d3c34]">{{ $capabilityLabel }}</span>
                    </span>
                    @if ($capabilityLinked)
                        <span class="text-xs font-black text-[#1d3c34] transition group-hover:translate-x-0.5">&rarr;</span>
                    @endif
                @if ($capabilityLinked)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
