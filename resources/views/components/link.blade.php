@props([
    'href' => null,
    'targetSection' => null,
])

@php
    $finalHref = $href;
    if (empty($finalHref) || $finalHref === '#' || str_starts_with($finalHref, '#')) {
        $finalHref = 'javascript:void(0);';
    }
@endphp

<a {{ $attributes->merge([
    'href' => $finalHref,
    'data-target-section' => $targetSection,
    'class' => 'transition cursor-pointer select-none',
]) }}>
    {{ $slot }}
</a>
