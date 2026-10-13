@php
    $theme = $customTheme ?? $themeOverride ?? $siteTheme ?? \App\Models\SiteSetting::currentTheme();
@endphp
<style id="site-theme-styles">
:root {
    --theme-primary: {{ $theme['primary'] ?? '#1d3c34' }};
    --theme-primary-hover: {{ $theme['primary_hover'] ?? '#254d43' }};
    --theme-primary-dark: {{ $theme['primary_dark'] ?? '#102b25' }};
    --theme-primary-light: {{ $theme['primary_light'] ?? '#dfeee4' }};
    --theme-secondary: {{ $theme['secondary'] ?? '#d9cab3' }};
    --theme-secondary-light: {{ $theme['secondary_light'] ?? '#e7ddca' }};
    --theme-accent: {{ $theme['accent'] ?? '#c5a059' }};
    --theme-accent-dark: {{ $theme['accent_dark'] ?? '#9b6c17' }};
    --theme-accent-light: {{ $theme['accent_light'] ?? '#f9ecd0' }};
    --theme-site-bg: {{ $theme['site_bg'] ?? '#f6f1e8' }};
    --theme-card-bg: {{ $theme['card_bg'] ?? '#fdfaf5' }};
    --theme-text-dark: {{ $theme['text_dark'] ?? '#1d3c34' }};
    --theme-text-muted: {{ $theme['text_muted'] ?? '#587165' }};

    --theme-primary-rgb: {{ $theme['primary_rgb'] ?? '29, 60, 52' }};
    --theme-primary-dark-rgb: {{ $theme['primary_dark_rgb'] ?? '16, 43, 37' }};
    --theme-primary-light-rgb: {{ $theme['primary_light_rgb'] ?? '223, 238, 228' }};
    --theme-secondary-rgb: {{ $theme['secondary_rgb'] ?? '217, 202, 179' }};
    --theme-accent-rgb: {{ $theme['accent_rgb'] ?? '197, 160, 89' }};
    --theme-accent-light-rgb: {{ $theme['accent_light_rgb'] ?? '249, 236, 208' }};
}

/* ========================================================
   DYNAMIC COLOR THEME ENGINE OVERRIDES
   ======================================================== */

/* Body & Main Page Backgrounds */
body,
.bg-\[\#f6f1e8\], [class*="bg-[#f6f1e8]"],
.bg-\[\#f5f0e7\], [class*="bg-[#f5f0e7]"] {
    background-color: var(--theme-site-bg) !important;
}

/* Card & Surface Panels */
.bg-\[\#fdfaf5\], [class*="bg-[#fdfaf5]"],
.bg-\[\#fffaf2\], [class*="bg-[#fffaf2]"],
.bg-\[\#fbf8f3\], [class*="bg-[#fbf8f3]"],
.bg-\[\#f9f4ed\], [class*="bg-[#f9f4ed]"],
.bg-\[\#f9f5ee\], [class*="bg-[#f9f5ee]"],
.bg-\[\#fcfaf7\], [class*="bg-[#fcfaf7]"],
.bg-\[\#f8f3eb\], [class*="bg-[#f8f3eb]"] {
    background-color: var(--theme-card-bg) !important;
}

/* Primary Backgrounds */
.bg-\[\#1d3c34\], [class*="bg-[#1d3c34]"]:not([class*="/"]),
.file\:bg-\[\#1d3c34\]::file-selector-button {
    background-color: var(--theme-primary) !important;
}

/* Primary Hover States */
.hover\:bg-\[\#254d43\]:hover, [class*="hover:bg-[#254d43]"]:hover,
.hover\:bg-\[\#264d41\]:hover, [class*="hover:bg-[#264d41]"]:hover,
.hover\:bg-\[\#244d42\]:hover, [class*="hover:bg-[#244d42]"]:hover,
.hover\:bg-\[\#1d3c34\]:hover, [class*="hover:bg-[#1d3c34]"]:hover,
.hover\:file\:bg-\[\#254d43\]:hover::file-selector-button {
    background-color: var(--theme-primary-hover) !important;
}

/* Primary Dark Backgrounds */
.bg-\[\#102b25\], [class*="bg-[#102b25]"]:not([class*="/"]),
.bg-\[\#0c1f1a\], [class*="bg-[#0c1f1a]"]:not([class*="/"]),
.bg-\[\#0a1714\], [class*="bg-[#0a1714]"]:not([class*="/"]),
.hover\:bg-\[\#102b25\]:hover, [class*="hover:bg-[#102b25]"]:hover,
.group:hover .group-hover\:bg-\[\#102b25\] {
    background-color: var(--theme-primary-dark) !important;
}

/* Primary Light / Tint Backgrounds */
.bg-\[\#dfeee4\], [class*="bg-[#dfeee4]"]:not([class*="/"]),
.bg-\[\#edf2ee\], [class*="bg-[#edf2ee]"]:not([class*="/"]),
.hover\:bg-\[\#dfeee4\]:hover, [class*="hover:bg-[#dfeee4]"]:hover,
.hover\:bg-\[\#edf2ee\]:hover, [class*="hover:bg-[#edf2ee]"]:hover {
    background-color: var(--theme-primary-light) !important;
}

/* Primary Texts */
.text-\[\#1d3c34\]:not(.group:hover *), [class*="text-[#1d3c34]"]:not(.group:hover *) {
    color: var(--theme-primary) !important;
}
.hover\:text-\[\#1d3c34\]:hover, [class*="hover:text-[#1d3c34]"]:hover {
    color: var(--theme-primary) !important;
}
.text-\[\#102b25\]:not(.group:hover *), [class*="text-[#102b25]"]:not(.group:hover *),
.text-\[\#0c1f1a\]:not(.group:hover *), [class*="text-[#0c1f1a]"]:not(.group:hover *) {
    color: var(--theme-primary-dark) !important;
}
.hover\:text-\[\#102b25\]:hover, [class*="hover:text-[#102b25]"]:hover,
.hover\:text-\[\#0c1f1a\]:hover, [class*="hover:text-[#0c1f1a]"]:hover {
    color: var(--theme-primary-dark) !important;
}
.text-\[\#dfeee4\], [class*="text-[#dfeee4]"] {
    color: var(--theme-primary-light) !important;
}
.text-\[\#587165\], [class*="text-[#587165]"],
.text-\[\#2d5d4d\], [class*="text-[#2d5d4d]"] {
    color: var(--theme-text-muted) !important;
}

/* Primary Borders, Rings & Focus */
.border-\[\#1d3c34\], [class*="border-[#1d3c34]"]:not([class*="/"]) {
    border-color: var(--theme-primary) !important;
}
.hover\:border-\[\#1d3c34\]:hover, [class*="hover:border-[#1d3c34]"]:hover {
    border-color: var(--theme-primary-hover) !important;
}
.focus\:border-\[\#1d3c34\]:focus, [class*="focus:border-[#1d3c34]"]:focus {
    border-color: var(--theme-primary) !important;
}
.ring-\[\#1d3c34\], [class*="ring-[#1d3c34]"]:not([class*="/"]),
.focus\:ring-\[\#1d3c34\]:focus, [class*="focus:ring-[#1d3c34]"]:focus {
    --tw-ring-color: var(--theme-primary) !important;
}
.ring-\[\#dfeee4\], [class*="ring-[#dfeee4]"]:not([class*="/"]),
.focus\:ring-\[\#dfeee4\]:focus, [class*="focus:ring-[#dfeee4]"]:focus {
    --tw-ring-color: var(--theme-primary-light) !important;
}

/* Secondary / Neutral Borders & Rings (inactive/unhovered) */
.border-\[\#d9cab3\]:not(:hover), [class*="border-[#d9cab3]"]:not(:hover):not([class*="/"]),
.border-\[\#e7ddca\]:not(:hover), [class*="border-[#e7ddca]"]:not(:hover):not([class*="/"]),
.border-\[\#b9a98b\]:not(:hover), [class*="border-[#b9a98b]"]:not(:hover):not([class*="/"]),
.border-\[\#ebdcc8\]:not(:hover), [class*="border-[#ebdcc8]"]:not(:hover):not([class*="/"]) {
    border-color: var(--theme-secondary) !important;
}
.ring-\[\#d9cab3\], [class*="ring-[#d9cab3]"]:not([class*="/"]),
.ring-\[\#e7ddca\], [class*="ring-[#e7ddca]"]:not([class*="/"]) {
    --tw-ring-color: var(--theme-secondary) !important;
}
.text-\[\#d9cab3\], [class*="text-[#d9cab3]"] {
    color: var(--theme-secondary) !important;
}

/* Luxury Accent Colors */
.bg-\[\#d4af37\], [class*="bg-[#d4af37]"]:not([class*="/"]),
.bg-\[\#c5a059\], [class*="bg-[#c5a059]"]:not([class*="/"]) {
    background-color: var(--theme-accent) !important;
}
.hover\:bg-\[\#d4af37\]:hover, [class*="hover:bg-[#d4af37]"]:hover {
    background-color: var(--theme-accent) !important;
}
.text-\[\#d4af37\], [class*="text-[#d4af37]"],
.text-\[\#c5a059\], [class*="text-[#c5a059]"],
.hover\:text-\[\#d4af37\]:hover, [class*="hover:text-[#d4af37]"]:hover,
.group:hover .group-hover\:text-\[\#d4af37\],
.group:hover [class*="group-hover:text-[#d4af37]"] {
    color: var(--theme-accent) !important;
}
.border-\[\#d4af37\], [class*="border-[#d4af37]"]:not([class*="/"]),
.border-\[\#c5a059\], [class*="border-[#c5a059]"]:not([class*="/"]),
.hover\:border-\[\#d4af37\]:hover, [class*="hover:border-[#d4af37]"]:hover,
.group:hover.hover\:border-\[\#d4af37\], .group:hover[class*="hover:border-[#d4af37]"],
.hover\:border-\[\#c5a059\]:hover, [class*="hover:border-[#c5a059]"]:hover,
.group:hover.hover\:border-\[\#c5a059\], .group:hover[class*="hover:border-[#c5a059]"] {
    border-color: var(--theme-accent) !important;
}
.ring-\[\#d4af37\], [class*="ring-[#d4af37]"]:not([class*="/"]),
.focus\:ring-\[\#d4af37\]:focus, [class*="focus:ring-[#d4af37]"]:focus {
    --tw-ring-color: var(--theme-accent) !important;
}

/* Accent Dark */
.text-\[\#9b6c17\]:not(.group:hover *), [class*="text-[#9b6c17]"]:not(.group:hover *) {
    color: var(--theme-accent-dark) !important;
}
.group:hover .group-hover\:text-\[\#9b6c17\],
.group:hover [class*="group-hover:text-[#9b6c17]"] {
    color: var(--theme-accent-dark) !important;
}
.bg-\[\#9b6c17\], [class*="bg-[#9b6c17]"]:not([class*="/"]) {
    background-color: var(--theme-accent-dark) !important;
}

/* Accent Light */
.bg-\[\#f9ecd0\], [class*="bg-[#f9ecd0]"]:not([class*="/"]),
.hover\:bg-\[\#f9ecd0\]:hover, [class*="hover:bg-[#f9ecd0]"]:hover {
    background-color: var(--theme-accent-light) !important;
}
.text-\[\#e7d8b7\], [class*="text-[#e7d8b7]"],
.hover\:text-\[\#e7d8b7\]:hover, [class*="hover:text-[#e7d8b7]"]:hover,
.group:hover .group-hover\:text-\[\#e7d8b7\],
.group:hover [class*="group-hover:text-[#e7d8b7]"] {
    color: var(--theme-accent-light) !important;
}

/* Opacity & RGBA Variants */
.bg-\[\#1d3c34\]\/10, [class*="bg-[#1d3c34]/10"] {
    background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}
.bg-\[\#1d3c34\]\/90, [class*="bg-[#1d3c34]/90"] {
    background-color: rgba(var(--theme-primary-rgb), 0.9) !important;
}
.border-\[\#1d3c34\]\/25, [class*="border-[#1d3c34]/25"] {
    border-color: rgba(var(--theme-primary-rgb), 0.25) !important;
}
.focus\:ring-\[\#1d3c34\]\/20:focus, [class*="focus:ring-[#1d3c34]/20"]:focus {
    --tw-ring-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.shadow-\[\#1d3c34\]\/20, [class*="shadow-[#1d3c34]/20"] {
    --tw-shadow-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.border-\[\#d4af37\]\/30, [class*="border-[#d4af37]/30"] {
    border-color: rgba(var(--theme-accent-rgb), 0.3) !important;
}
.border-\[\#d4af37\]\/40, [class*="border-[#d4af37]/40"] {
    border-color: rgba(var(--theme-accent-rgb), 0.4) !important;
}
.border-\[\#d4af37\]\/50, [class*="border-[#d4af37]/50"] {
    border-color: rgba(var(--theme-accent-rgb), 0.5) !important;
}
.ring-\[\#d4af37\]\/30, [class*="ring-[#d4af37]/30"] {
    --tw-ring-color: rgba(var(--theme-accent-rgb), 0.3) !important;
}
.bg-\[\#d4af37\]\/15, [class*="bg-[#d4af37]/15"] {
    background-color: rgba(var(--theme-accent-rgb), 0.15) !important;
}
.bg-\[\#d4af37\]\/20, [class*="bg-[#d4af37]/20"] {
    background-color: rgba(var(--theme-accent-rgb), 0.2) !important;
}
.shadow-\[\#d4af37\]\/20, [class*="shadow-[#d4af37]/20"],
.shadow-\[\#d4af37\]\/25, [class*="shadow-[#d4af37]/25"] {
    --tw-shadow-color: rgba(var(--theme-accent-rgb), 0.2) !important;
}
.border-\[\#d9cab3\]\/40, [class*="border-[#d9cab3]/40"],
.border-\[\#d9cab3\]\/20, [class*="border-[#d9cab3]/20"] {
    border-color: rgba(var(--theme-secondary-rgb), 0.4) !important;
}
.bg-\[\#102b25\]\/85, [class*="bg-[#102b25]/85"] {
    background-color: rgba(var(--theme-primary-dark-rgb), 0.85) !important;
}
.bg-\[\#102b25\]\/90, [class*="bg-[#102b25]/90"] {
    background-color: rgba(var(--theme-primary-dark-rgb), 0.9) !important;
}
.bg-\[\#102b25\]\/95, [class*="bg-[#102b25]/95"] {
    background-color: rgba(var(--theme-primary-dark-rgb), 0.95) !important;
}

/* Gradients */
.from-\[\#1d3c34\], [class*="from-[#1d3c34]"] {
    --tw-gradient-from: var(--theme-primary) var(--tw-gradient-from-position) !important;
}
.to-\[\#1d3c34\], [class*="to-[#1d3c34]"] {
    --tw-gradient-to: var(--theme-primary) var(--tw-gradient-to-position) !important;
}
.via-\[\#1d3c34\]\/90, [class*="via-[#1d3c34]/90"] {
    --tw-gradient-stops: var(--tw-gradient-from), rgba(var(--theme-primary-rgb), 0.9) var(--tw-gradient-via-position), var(--tw-gradient-to) !important;
}
.from-\[\#102b25\], [class*="from-[#102b25]"],
.from-\[\#0c1f1a\], [class*="from-[#0c1f1a]"],
.from-\[\#0a1714\], [class*="from-[#0a1714]"] {
    --tw-gradient-from: var(--theme-primary-dark) var(--tw-gradient-from-position) !important;
}
.to-\[\#102b25\], [class*="to-[#102b25]"],
.to-\[\#0a1714\], [class*="to-[#0a1714]"] {
    --tw-gradient-to: var(--theme-primary-dark) var(--tw-gradient-to-position) !important;
}

/* ========================================================
   WHITE HOVER TEXT SAFETY
   Buttons/links that pair a themed text color with
   `hover:text-white` flip to a dark background on hover, but the
   themed text rules above use !important and would otherwise keep
   the text (and any nested labels) dark-on-dark. Force white on
   hover so the label stays readable. Same specificity as the theme
   rules — the later declaration wins the tie.
   ======================================================== */
[class*="text-[#1d3c34]"][class*="hover:text-white"]:hover,
[class*="text-[#102b25]"][class*="hover:text-white"]:hover,
[class*="text-[#0c1f1a]"][class*="hover:text-white"]:hover,
[class*="text-[#9b6c17]"][class*="hover:text-white"]:hover,
[class*="hover:text-white"]:hover [class*="text-[#1d3c34]"],
[class*="hover:text-white"]:hover [class*="text-[#102b25]"],
[class*="hover:text-white"]:hover [class*="text-[#0c1f1a]"],
[class*="hover:text-white"]:hover [class*="text-[#9b6c17]"] {
    color: #ffffff !important;
}

/* ========================================================
   DARK MODE OVERRIDES
   ======================================================== */
html.dark {
    --theme-site-bg: #090d16;
    --theme-card-bg: #111827;
    --theme-text-dark: #f8fafc;
    --theme-text-muted: #94a3b8;
    color-scheme: dark;
}
html.dark body {
    background-color: #090d16 !important;
    color: #f1f5f9 !important;
}
html.dark .bg-\[\#f5f0e7\],
html.dark [class*="bg-[#f5f0e7]"] {
    background-color: #090d16 !important;
}
html.dark .bg-\[\#fdfaf5\],
html.dark [class*="bg-[#fdfaf5]"],
html.dark .bg-\[\#fbf8f3\],
html.dark [class*="bg-[#fbf8f3]"],
html.dark .bg-\[\#f8f3eb\],
html.dark [class*="bg-[#f8f3eb]"] {
    background-color: #111827 !important;
}
html.dark .border-\[\#d9cab3\],
html.dark [class*="border-[#d9cab3]"],
html.dark .border-\[\#e7ddca\],
html.dark [class*="border-[#e7ddca]"] {
    border-color: #1f2937 !important;
}
html.dark .text-slate-800,
html.dark .text-slate-900 {
    color: #f8fafc !important;
}
html.dark .text-slate-600,
html.dark .text-slate-500 {
    color: #94a3b8 !important;
}
</style>
