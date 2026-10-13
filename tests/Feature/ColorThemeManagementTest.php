<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Theme Admin',
        'email' => 'theme-admin@example.com',
        'password' => Hash::make('Secret123!@#'),
        'role' => 'admin',
    ]);

    $this->regularUser = User::create([
        'name' => 'Regular User',
        'email' => 'regular-user@example.com',
        'password' => Hash::make('Secret123!@#'),
        'role' => 'agent',
    ]);
});

test('guest cannot update color theme or site settings', function () {
    $response = $this->post(route('dashboard.settings.update'), [
        'site_name' => 'GTP Real Estate',
        'contact_email' => 'gtmrealstate@gmail.com',
        'contact_phone' => '+251 993722346',
        'contact_address' => 'Harar, Ethiopia',
        'theme_preset' => 'sapphire',
        'theme_primary_color' => '#1e3a5f',
    ]);

    $response->assertRedirect(route('login'));
});

test('non-admin user cannot update site settings', function () {
    $response = $this->actingAs($this->regularUser)
        ->post(route('dashboard.settings.update'), [
            'site_name' => 'GTP Real Estate',
            'contact_email' => 'gtmrealstate@gmail.com',
            'contact_phone' => '+251 993722346',
            'contact_address' => 'Harar, Ethiopia',
            'theme_preset' => 'sapphire',
            'theme_primary_color' => '#1e3a5f',
        ]);

    $response->assertForbidden();
});

test('admin can view color theme studio in dashboard site settings', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('dashboard', ['section' => 'settings']));

    $response->assertOk()
        ->assertSee('Color Theme &amp; Palette Studio', false)
        ->assertSee('Curated Luxury Presets')
        ->assertSee('Emerald &amp; Warm Sand', false)
        ->assertSee('Sapphire &amp; Platinum', false)
        ->assertSee('Obsidian &amp; Champagne Gold', false)
        ->assertSee('Fine-Tune Individual Colors')
        ->assertSee('Live Interactive Preview')
        ->assertSee('name="theme_primary_color"', false);
});

test('admin can successfully update color theme settings with a preset and custom colors', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('dashboard.settings.update'), [
            'site_name' => 'GTP Luxury Estates',
            'contact_email' => 'luxury@example.com',
            'contact_phone' => '+251 993722346',
            'contact_address' => 'Harar, Ethiopia',
            'theme_preset' => 'sapphire',
            'theme_primary_color' => '#1e3a5f',
            'theme_primary_hover' => '#2b5282',
            'theme_primary_dark' => '#0f1f33',
            'theme_primary_light' => '#dbeafe',
            'theme_secondary_color' => '#cbd5e1',
            'theme_secondary_light' => '#e2e8f0',
            'theme_accent_color' => '#0284c7',
            'theme_accent_dark' => '#0369a1',
            'theme_accent_light' => '#e0f2fe',
            'theme_site_bg' => '#f8fafc',
            'theme_card_bg' => '#ffffff',
            'theme_text_dark' => '#0f172a',
            'theme_text_muted' => '#64748b',
        ]);

    $response->assertRedirect(route('dashboard', ['section' => 'settings']))
        ->assertSessionHas('success');

    expect(SiteSetting::get('theme_preset'))->toBe('sapphire')
        ->and(SiteSetting::get('theme_primary_color'))->toBe('#1e3a5f')
        ->and(SiteSetting::get('theme_accent_color'))->toBe('#0284c7')
        ->and(SiteSetting::get('theme_site_bg'))->toBe('#f8fafc');

    $currentTheme = SiteSetting::currentTheme();
    expect($currentTheme['primary'])->toBe('#1e3a5f')
        ->and($currentTheme['primary_rgb'])->toBe('30, 58, 95')
        ->and($currentTheme['preset'])->toBe('sapphire');
});

test('public pages render dynamic theme stylesheet with configured colors', function () {
    SiteSetting::set('theme_preset', 'obsidian');
    SiteSetting::set('theme_primary_color', '#18181b');
    SiteSetting::set('theme_accent_color', '#d97706');
    SiteSetting::set('theme_site_bg', '#fafaf9');

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('id="site-theme-styles"', false)
        ->assertSee('--theme-primary: #18181b;', false)
        ->assertSee('--theme-accent: #d97706;', false)
        ->assertSee('--theme-site-bg: #fafaf9;', false)
        ->assertSee('--theme-primary-rgb: 24, 24, 27;', false);
});

test('theme color inputs validate hex format strictly', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('dashboard.settings.update'), [
            'site_name' => 'GTP Real Estate',
            'contact_email' => 'gtmrealstate@gmail.com',
            'contact_phone' => '+251 993722346',
            'contact_address' => 'Harar, Ethiopia',
            'theme_primary_color' => 'not-a-color',
        ]);

    $response->assertSessionHasErrors(['theme_primary_color']);
});

test('themePresets method provides all 9 curated luxury palettes', function () {
    $presets = SiteSetting::themePresets();

    expect(array_keys($presets))->toEqualCanonicalizing([
        'emerald',
        'sapphire',
        'obsidian',
        'burgundy',
        'olive',
        'navy',
        'terracotta',
        'oak',
        'cream',
    ]);

    foreach ($presets as $key => $preset) {
        expect($preset)->toHaveKeys(['name', 'description', 'colors'])
            ->and($preset['colors'])->toHaveKeys([
                'primary',
                'primary_hover',
                'primary_dark',
                'primary_light',
                'secondary',
                'secondary_light',
                'accent',
                'accent_dark',
                'accent_light',
                'site_bg',
                'card_bg',
                'text_dark',
                'text_muted',
            ]);
    }
});
