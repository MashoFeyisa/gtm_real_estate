@php
    $href = $href ?? '#';
    $targetSection = $targetSection ?? null;
    $icon = $icon ?? null;
    $label = $label ?? '';
    $count = $count ?? null;
    $badge = $badge ?? null;
    $badgeClass = $badgeClass ?? null;
    $badgeId = $badgeId ?? null;
    $alert = $alert ?? false;
    $active = $active ?? false;
    $isSub = $isSub ?? false;
    $resolvedTarget = $targetSection ?? (is_string($href) && str_starts_with($href, '#') ? ltrim($href, '#') : null);
    $isSection = !empty($resolvedTarget) && is_string($href) && str_starts_with($href, '#');
    $activeState = filter_var($active, FILTER_VALIDATE_BOOLEAN);
    $isSubItem = filter_var($isSub, FILTER_VALIDATE_BOOLEAN);
    $isAlert = filter_var($alert, FILTER_VALIDATE_BOOLEAN);
@endphp

<a
    href="{{ $href }}"
    @if($isSection) data-target-section="{{ $resolvedTarget }}" @endif
    class="token-nav-item token-nav-link{{ $isSection ? ' nav-section-link' : '' }}{{ $activeState ? ' token-nav-link-active' : '' }}{{ $isSubItem ? ' token-nav-sub-item' : ' token-nav-top-item' }}"
>
    <span class="token-nav-lead">
        @if($icon)
            <span class="token-nav-icon" aria-hidden="true">{{ $icon }}</span>
        @endif
        <span class="token-nav-label">{{ $label }}</span>
    </span>

    @if($badge !== null && $badge !== '')
        <span @if($badgeId) id="{{ $badgeId }}" @endif class="{{ $badgeClass ?? ($isAlert ? 'token-nav-count-alert' : 'token-nav-badge-new') }}">{{ $badge }}</span>
    @elseif($count !== null && $count !== '')
        <span @if($badgeId) id="{{ $badgeId }}" @endif class="{{ $badgeClass ?? ($isAlert ? 'token-nav-count-alert' : 'token-nav-count') }}">{{ $count }}</span>
    @endif
</a>
