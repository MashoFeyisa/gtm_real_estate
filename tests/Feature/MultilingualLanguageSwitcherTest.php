<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can switch application language to Amharic', function () {
    $response = $this->get(route('locale.switch', 'am'));

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'am');
    $response->assertCookie('app_locale', 'am');
});

test('user can switch application language to Afan Oromo', function () {
    $response = $this->get(route('locale.switch', 'om'));

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'om');
    $response->assertCookie('app_locale', 'om');
});

test('user can switch application language to English', function () {
    $response = $this->get(route('locale.switch', 'en'));

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('app_locale', 'en');
});

test('switching with json request returns json response', function () {
    $response = $this->getJson(route('locale.switch', 'am'));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'locale' => 'am',
        ]);
});

test('invalid locale switches are rejected and fallback to default', function () {
    $response = $this->get(route('locale.switch', 'fr'));

    $response->assertSessionMissing('locale');
});

test('SetLocale middleware sets application locale from session', function () {
    $this->withSession(['locale' => 'am'])->get(route('home'));

    expect(app()->getLocale())->toBe('am');

    $this->withSession(['locale' => 'om'])->get(route('home'));

    expect(app()->getLocale())->toBe('om');

    $this->withSession(['locale' => 'en'])->get(route('home'));

    expect(app()->getLocale())->toBe('en');
});

test('home page header renders language dropdown near currency with English, Amharic, and Afan Oromo', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    // Currency toggle is present
    $response->assertSee('currency-dropdown-toggle');

    // Language toggle and options are present
    $response->assertSee('language-dropdown-toggle');
    $response->assertSee('data-set-language="en"', false);
    $response->assertSee('data-set-language="am"', false);
    $response->assertSee('data-set-language="om"', false);
    $response->assertSee('አማርኛ (Amharic)');
    $response->assertSee('Afaan Oromoo');
    $response->assertSee('English');

    // Google Translate element and script integration
    $response->assertSee('google_translate_element');
    $response->assertSee('mobile_google_translate_element');
    $response->assertSee('translate.google.com/translate_a/element.js', false);
});
