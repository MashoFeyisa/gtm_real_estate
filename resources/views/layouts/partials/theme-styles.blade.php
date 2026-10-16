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

    /* Unified Design Tokens (Light Mode - WCAG AA Contrast >= 4.5:1) */
    --token-bg: #f5f0e7;
    --token-surface: #ffffff;
    --token-surface-subtle: #fbf8f3;
    --token-surface-hover: #f5ede1;
    --token-border: #e2d8c7;
    --token-border-subtle: #eee6d8;
    --token-text: #0f172a;           /* 15.4:1 contrast on white */
    --token-text-muted: #475569;     /* 5.9:1 contrast on white */
    --token-text-on-color: #ffffff;
    --token-primary: #1d3c34;
    --token-primary-surface: #edf4f0;
    --token-primary-text: #142b25;
    --token-success: #047857;        /* 5.1:1 contrast on white */
    --token-success-surface: #ecfdf5;
    --token-success-border: #a7f3d0;
    --token-success-text: #064e3b;   /* 8.5:1 contrast on surface */
    --token-warning: #b45309;        /* 4.8:1 contrast on white */
    --token-warning-surface: #fffbeb;
    --token-warning-border: #fde68a;
    --token-warning-text: #78350f;   /* 9.3:1 contrast on surface */
    --token-danger: #b91c1c;         /* 5.6:1 contrast on white */
    --token-danger-surface: #fef2f2;
    --token-danger-border: #fecaca;
    --token-danger-text: #7f1d1d;    /* 9.8:1 contrast on surface */
    --token-info: #1d4ed8;           /* 5.5:1 contrast on white */
    --token-info-surface: #eff6ff;
    --token-info-border: #bfdbfe;
    --token-info-text: #1e3a8a;      /* 9.6:1 contrast on surface */

    /* Chart Semantic Palette - Light */
    --token-chart-primary: #1d3c34;
    --token-chart-primary-2: #254d43;
    --token-chart-primary-3: #366e60;
    --token-chart-warning: #b45309;
    --token-chart-info: #1d4ed8;
    --token-chart-success: #047857;
    --token-chart-accent: #7c3aed;
    --token-chart-danger: #b91c1c;
    --token-chart-grid: rgba(15, 23, 42, 0.06);
    --token-chart-text: #475569;

    /* Brand accents, hero & sidebar (mode-independent where noted) */
    --token-hero-start: #1d3c34;          /* brand gradient start: white text 12.9:1 */
    --token-hero-end: #2d5d4d;            /* brand gradient end: white text 9.2:1 */
    --token-hero-border: rgba(255, 255, 255, 0.16);
    --token-hero-sub: rgba(255, 255, 255, 0.88);
    --token-hero-stat-bg: rgba(255, 255, 255, 0.10);
    --token-hero-chip-bg: rgba(212, 175, 55, 0.18);
    --token-hero-chip-border: rgba(212, 175, 55, 0.45);
    --token-accent: #b45309;              /* accessible gold-family accent on light */
    --token-accent-light: #f8e7b9;        /* cream text on hero/primary surfaces */
    --token-accent-surface: #faf1d8;
    --token-accent-border: #e3c978;
    --token-accent-text: #7a5410;         /* 6.0:1 on accent-surface */
    --token-accent-stat: #6d28d9;         /* 7.4:1 on white */
    --token-sidebar-bg: #16302a;
    --token-sidebar-text: #dfeee4;        /* 11.8:1 on sidebar-bg */
    --token-sidebar-hover: rgba(255, 255, 255, 0.08);
    --token-sidebar-active: rgba(255, 255, 255, 0.14);

    /* Ethiopian flag tricolor (receipts / national branding) — theme independent */
    --token-flag-green: #009b44;
    --token-flag-yellow: #fcd116;
    --token-flag-red: #eb1c24;
    --token-overlay: rgba(9, 13, 22, 0.6);
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
   DARK MODE OVERRIDES & DESIGN TOKENS
   ======================================================== */
html.dark {
    --theme-site-bg: #090d16;
    --theme-card-bg: #111827;
    --theme-text-dark: #f8fafc;
    --theme-text-muted: #94a3b8;
    color-scheme: dark;

    /* Unified Design Tokens (Dark Mode - WCAG AA Contrast >= 4.5:1) */
    --token-bg: #090d16;
    --token-surface: #111827;
    --token-surface-subtle: #1e293b;
    --token-surface-hover: #1f293d;
    --token-border: #1f2937;
    --token-border-subtle: #2d3748;
    --token-text: #f8fafc;           /* 15.1:1 contrast on #111827 */
    --token-text-muted: #94a3b8;     /* 6.2:1 contrast on #111827 */
    --token-text-on-color: #ffffff;
    --token-primary: #34d399;        /* 8.5:1 contrast on #111827 */
    --token-primary-surface: #062a22;
    --token-primary-text: #a7f3d0;
    --token-success: #34d399;        /* 8.5:1 contrast on #111827 */
    --token-success-surface: #052e22;
    --token-success-border: #065f46;
    --token-success-text: #6ee7b7;   /* 10.2:1 contrast on surface */
    --token-warning: #fbbf24;        /* 9.6:1 contrast on #111827 */
    --token-warning-surface: #381e05;
    --token-warning-border: #78350f;
    --token-warning-text: #fcd34d;   /* 11.2:1 contrast on surface */
    --token-danger: #f87171;         /* 6.8:1 contrast on #111827 */
    --token-danger-surface: #3b0d0c;
    --token-danger-border: #7f1d1d;
    --token-danger-text: #fca5a5;    /* 9.1:1 contrast on surface */
    --token-info: #60a5fa;           /* 7.8:1 contrast on #111827 */
    --token-info-surface: #0c2340;
    --token-info-border: #1e3a8a;
    --token-info-text: #93c5fd;      /* 9.8:1 contrast on surface */

    /* Chart Semantic Palette - Dark */
    --token-chart-primary: #34d399;
    --token-chart-primary-2: #6ee7b7;
    --token-chart-primary-3: #a7f3d0;
    --token-chart-warning: #fbbf24;
    --token-chart-info: #60a5fa;
    --token-chart-success: #34d399;
    --token-chart-accent: #a78bfa;
    --token-chart-danger: #f87171;
    --token-chart-grid: rgba(248, 250, 252, 0.08);
    --token-chart-text: #94a3b8;

    /* Brand accents, hero & sidebar (hero gradient stays brand-dark in both modes) */
    --token-hero-start: #1d3c34;
    --token-hero-end: #2d5d4d;
    --token-hero-border: rgba(255, 255, 255, 0.16);
    --token-hero-sub: rgba(255, 255, 255, 0.88);
    --token-hero-stat-bg: rgba(255, 255, 255, 0.10);
    --token-hero-chip-bg: rgba(226, 193, 112, 0.16);
    --token-hero-chip-border: rgba(226, 193, 112, 0.42);
    --token-accent: #fbbf24;
    --token-accent-light: #f8e7b9;
    --token-accent-surface: #3a2e10;
    --token-accent-border: #7a5f22;
    --token-accent-text: #f2d89b;         /* 9.6:1 on accent-surface */
    --token-accent-stat: #a78bfa;
    --token-sidebar-bg: #0d211c;
    --token-sidebar-text: #dfeee4;        /* 11.4:1 on sidebar-bg */
    --token-sidebar-hover: rgba(255, 255, 255, 0.07);
    --token-sidebar-active: rgba(255, 255, 255, 0.12);
    --token-overlay: rgba(0, 0, 0, 0.6);
}

/* ========================================================
   UNIFIED CARD & COMPONENT STYLING UTILITIES
   ======================================================== */
.token-card {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    border-radius: 1.5rem; /* rounded-3xl */
    padding: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    color: var(--token-text);
}

.token-stat-card {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    border-radius: 1rem; /* rounded-2xl */
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.token-stat-title {
    font-size: 0.75rem; /* 12px */
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--token-text-muted);
}

.token-stat-value {
    font-size: 1.5rem; /* 24px */
    font-weight: 900;
    line-height: 1.2;
    margin-top: 0.25rem;
    color: var(--token-text);
}

.token-stat-primary {
    color: var(--token-primary);
}

.token-stat-success {
    color: var(--token-success);
}

.token-stat-warning {
    color: var(--token-warning);
}

.token-stat-danger {
    color: var(--token-danger);
}

.token-stat-info {
    color: var(--token-info);
}

.token-stat-accent {
    color: var(--token-accent-stat);
}

/* Semantic Badges */
.token-badge-success {
    background-color: var(--token-success-surface);
    border: 1px solid var(--token-success-border);
    color: var(--token-success-text);
}
.token-badge-warning {
    background-color: var(--token-warning-surface);
    border: 1px solid var(--token-warning-border);
    color: var(--token-warning-text);
}
.token-badge-danger {
    background-color: var(--token-danger-surface);
    border: 1px solid var(--token-danger-border);
    color: var(--token-danger-text);
}
.token-badge-info {
    background-color: var(--token-info-surface);
    border: 1px solid var(--token-info-border);
    color: var(--token-info-text);
}
.token-badge-neutral {
    background-color: var(--token-surface-subtle);
    border: 1px solid var(--token-border);
    color: var(--token-text-muted);
}
.token-badge-accent {
    background-color: var(--token-accent-surface);
    border: 1px solid var(--token-accent-border);
    color: var(--token-accent-text);
}
.token-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 9999px;
    padding: 0.125rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1.5;
    white-space: normal;
}

/* ========================================================
   SHARED DASHBOARD COMPONENTS (one look for every role)
   ======================================================== */

/* Page header (hero) */
.token-hero {
    background: linear-gradient(135deg, var(--token-hero-start), var(--token-hero-end));
    border: 1px solid var(--token-hero-border);
    border-radius: 1.5rem;
    color: var(--token-text-on-color);
    padding: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12);
}
@media (min-width: 768px) {
    .token-hero {
        padding: 2rem;
    }
}
.token-hero-title {
    color: var(--token-text-on-color);
    font-weight: 900;
}
.token-hero-sub {
    color: var(--token-hero-sub);
}
.token-hero-chip {
    background-color: var(--token-hero-chip-bg);
    border: 1px solid var(--token-hero-chip-border);
    color: var(--token-accent-light);
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.token-hero-stat {
    background-color: var(--token-hero-stat-bg);
    border: 1px solid var(--token-hero-border);
    border-radius: 1rem;
    padding: 1rem;
}
.token-hero-stat-label {
    color: var(--token-hero-sub);
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.token-hero-stat-value {
    color: var(--token-accent-light);
    font-weight: 900;
    line-height: 1.25;
}
.token-hero-tag {
    background-color: var(--token-hero-stat-bg);
    border: 1px solid var(--token-hero-border);
    color: var(--token-hero-sub);
    border-radius: 9999px;
    padding: 0.125rem 0.625rem;
    font-size: 0.625rem;
    font-weight: 600;
}

/* Progress / bar fills (semantic, re-theme with the tokens) */
.token-fill-primary {
    background-color: var(--token-chart-primary);
}
.token-fill-success {
    background-color: var(--token-chart-success);
}
.token-fill-warning {
    background-color: var(--token-chart-warning);
}
.token-fill-info {
    background-color: var(--token-chart-info);
}

/* Section heading inside a panel */
.token-section-title {
    color: var(--token-text);
    font-size: 1.25rem;
    font-weight: 900;
    border-bottom: 1px solid var(--token-border-subtle);
    padding-bottom: 0.75rem;
}

/* Period filter (7D / 30D / 90D / 1Y) */
.token-period-bar {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    border: 1px solid var(--token-border);
    background-color: var(--token-surface-subtle);
    border-radius: 9999px;
    padding: 0.25rem;
}
.token-period-btn {
    border-radius: 9999px;
    padding: 0.375rem 0.875rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--token-text-muted);
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.token-period-btn:hover {
    background-color: var(--token-surface-hover);
    color: var(--token-text);
}
.token-period-btn-active,
.token-period-btn[aria-pressed="true"] {
    background-color: var(--token-primary);
    color: var(--token-text-on-color);
}
html.dark .token-period-btn-active,
html.dark .token-period-btn[aria-pressed="true"] {
    background-color: var(--token-success);
    color: #052e22;
}

/* Insight card */
.token-insight-card {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    border-left: 4px solid var(--token-info);
    border-radius: 1rem;
    padding: 1.25rem;
    color: var(--token-text);
}
.token-insight-success {
    border-left-color: var(--token-success);
}
.token-insight-warning {
    border-left-color: var(--token-warning);
}
.token-insight-danger {
    border-left-color: var(--token-danger);
}
.token-insight-accent {
    border-left-color: var(--token-accent);
}
.token-insight-title {
    color: var(--token-text);
    font-weight: 800;
}

/* Chart card */
.token-chart-card {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    border-radius: 1.5rem;
    padding: 1.5rem;
    color: var(--token-text);
}
.token-chart-title {
    color: var(--token-text);
    font-weight: 800;
}
.token-chart-sub {
    color: var(--token-text-muted);
}

/* Data table */
.token-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--token-border);
    border-radius: 1rem;
    background-color: var(--token-surface);
}
.token-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--token-text);
    font-size: 0.875rem;
}
.token-table th {
    background-color: var(--token-surface-subtle);
    color: var(--token-text-muted);
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--token-border);
    white-space: normal;
}
.token-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--token-border-subtle);
    color: var(--token-text);
    white-space: normal;
    vertical-align: top;
}
.token-table tr:last-child td {
    border-bottom: 0;
}
.token-table tbody tr:hover td {
    background-color: var(--token-surface-hover);
}

/* Sidebar navigation & shared menu item */
.token-nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    border-radius: 0.75rem;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--token-sidebar-text);
    transition: background-color 0.15s ease, color 0.15s ease;
    box-sizing: border-box;
    text-decoration: none;
}
.token-nav-link:hover {
    background-color: var(--token-sidebar-hover);
    color: var(--token-text-on-color);
}
.token-nav-link-active {
    background-color: var(--token-sidebar-active);
    color: var(--token-text-on-color);
}
.token-nav-top-item {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}
.token-nav-sub-item {
    padding-left: 2.25rem; /* Only sub-menu items may be indented */
    padding-right: 0.75rem;
    font-size: 0.8125rem;
}
.token-nav-lead {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem; /* Same gap across all menu items */
    min-width: 0;
    flex: 1 1 auto;
}
.token-nav-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;   /* Fixed-width icon box: exactly 24px */
    height: 1.5rem;  /* Fixed-height icon box: exactly 24px */
    min-width: 1.5rem;
    max-width: 1.5rem;
    font-size: 1.125rem;
    line-height: 1;
    text-align: center;
    flex-shrink: 0;
}
.token-nav-label {
    display: inline-block;
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.token-nav-badge-new {
    background-color: var(--token-accent-surface);
    color: var(--token-accent-text);
    border-radius: 9999px;
    padding: 0.125rem 0.5rem;
    font-size: 0.6875rem;
    font-weight: 700;
    flex-shrink: 0;
    margin-left: 0.5rem;
}
.token-nav-count {
    background-color: var(--token-sidebar-hover);
    color: var(--token-sidebar-text);
    border-radius: 9999px;
    padding: 0.125rem 0.5rem;
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    flex-shrink: 0;
    margin-left: 0.5rem;
}
.token-nav-count-alert {
    background-color: var(--token-danger-surface);
    color: var(--token-danger-text);
    border-radius: 9999px;
    padding: 0.125rem 0.5rem;
    font-size: 0.625rem;
    font-weight: 700;
    flex-shrink: 0;
    margin-left: 0.5rem;
}

/* Collapsed sidebar mode */
.dashboard-sidebar.is-collapsed,
.dashboard-sidebar[data-collapsed="true"],
#role-sidebar.is-collapsed,
#role-sidebar[data-collapsed="true"] {
    width: 4.5rem !important;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
    transition: width 0.25s ease, padding 0.25s ease;
}
.dashboard-sidebar.is-collapsed .token-user-chip,
.dashboard-sidebar[data-collapsed="true"] .token-user-chip,
.dashboard-sidebar.is-collapsed .token-sidebar-heading,
.dashboard-sidebar[data-collapsed="true"] .token-sidebar-heading,
.dashboard-sidebar.is-collapsed .sidebar-brand-text,
.dashboard-sidebar[data-collapsed="true"] .sidebar-brand-text {
    display: none !important;
}
.dashboard-sidebar.is-collapsed .token-nav-label,
.dashboard-sidebar[data-collapsed="true"] .token-nav-label,
.dashboard-sidebar.is-collapsed .token-nav-count,
.dashboard-sidebar[data-collapsed="true"] .token-nav-count,
.dashboard-sidebar.is-collapsed .token-nav-badge-new,
.dashboard-sidebar[data-collapsed="true"] .token-nav-badge-new {
    display: none !important;
}
.dashboard-sidebar.is-collapsed .token-nav-link,
.dashboard-sidebar[data-collapsed="true"] .token-nav-link {
    justify-content: center !important;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}
.dashboard-sidebar.is-collapsed .token-nav-lead,
.dashboard-sidebar[data-collapsed="true"] .token-nav-lead {
    justify-content: center !important;
    flex: 0 0 auto !important;
}
.dashboard-sidebar.is-collapsed .token-signout span:last-child,
.dashboard-sidebar[data-collapsed="true"] .token-signout span:last-child {
    display: none !important;
}
.token-sidebar-heading {
    color: var(--token-accent-light);
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.2em;
}

/* Sidebar user chip (profile entry point for every role) */
.token-user-chip {
    border-radius: 1rem;
    border: 1px solid var(--token-sidebar-active);
    background-color: var(--token-sidebar-hover);
    padding: 0.75rem;
    transition: background-color 0.15s ease;
}
.token-user-chip:hover {
    background-color: var(--token-sidebar-active);
}
.token-user-chip-label {
    color: var(--token-sidebar-text);
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    opacity: 0.75;
}

/* Sidebar sign-out */
.token-signout {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 0.75rem;
    background-color: var(--token-sidebar-hover);
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--token-sidebar-text);
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.token-signout:hover {
    background-color: var(--token-danger-surface);
    color: var(--token-danger-text);
}

/* Inline alert banners */
.token-alert {
    border-radius: 1rem;
    border: 1px solid var(--token-border);
    background-color: var(--token-surface-subtle);
    padding: 1.25rem;
    color: var(--token-text);
}
.token-alert-success {
    background-color: var(--token-success-surface);
    border-color: var(--token-success-border);
    color: var(--token-success-text);
}
.token-alert-warning {
    background-color: var(--token-warning-surface);
    border-color: var(--token-warning-border);
    color: var(--token-warning-text);
}
.token-alert-danger {
    background-color: var(--token-danger-surface);
    border-color: var(--token-danger-border);
    color: var(--token-danger-text);
}
.token-alert-info {
    background-color: var(--token-info-surface);
    border-color: var(--token-info-border);
    color: var(--token-info-text);
}
.token-alert-accent {
    background-color: var(--token-accent-surface);
    border-color: var(--token-accent-border);
    color: var(--token-accent-text);
}

/* Chart tile inside a chart card */
.token-chart-tile {
    background-color: var(--token-surface-subtle);
    border: 1px solid var(--token-border-subtle);
    border-radius: 1rem;
    padding: 1rem;
}

/* Buttons & fields */
.token-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    border-radius: 0.75rem;
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
.token-btn-primary {
    background-color: var(--token-primary);
    color: var(--token-text-on-color);
}
.token-btn-primary:hover {
    background-color: var(--token-primary-hover);
}
.token-btn-secondary {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    color: var(--token-text);
}
.token-btn-secondary:hover {
    background-color: var(--token-surface-hover);
}
.token-btn-danger {
    background-color: var(--token-danger);
    color: var(--token-text-on-color);
}
.token-btn-ghost {
    color: var(--token-text-muted);
}
.token-btn-ghost:hover {
    background-color: var(--token-surface-hover);
    color: var(--token-text);
}
.token-field {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid var(--token-border);
    background-color: var(--token-surface);
    color: var(--token-text);
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
.token-field::placeholder {
    color: var(--token-text-muted);
}
.token-field:focus {
    outline: 2px solid var(--token-primary);
    outline-offset: 1px;
}
.token-label {
    display: block;
    color: var(--token-text);
    font-size: 0.75rem;
    font-weight: 700;
}
.token-muted {
    color: var(--token-text-muted);
}
.token-dropdown {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    color: var(--token-text);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
}
.token-modal-overlay {
    background-color: var(--token-overlay);
}
.token-modal {
    background-color: var(--token-surface);
    border: 1px solid var(--token-border);
    color: var(--token-text);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Small uppercase sub-section heading inside modals and cards */
.token-section-heading {
    color: var(--token-primary);
    font-size: 0.75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Dark mode surface & contrast enforcement across dashboards */
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
html.dark .text-amber-600 {
    color: #fbbf24 !important;
}
html.dark .text-emerald-600 {
    color: #34d399 !important;
}
html.dark .text-rose-600,
html.dark .text-red-600 {
    color: #f87171 !important;
}
html.dark .text-blue-600 {
    color: #60a5fa !important;
}
html.dark .text-purple-600 {
    color: #c084fc !important;
}
html.dark .text-indigo-600 {
    color: #818cf8 !important;
}
</style>
