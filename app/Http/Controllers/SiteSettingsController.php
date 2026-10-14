<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_logo' => $request->hasFile('site_logo') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'site_logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_address' => ['required', 'string', 'max:255'],
            'happy_buyers_base_count' => ['nullable', 'integer', 'min:0'],
            // Currency rates
            'currency_rate_usd' => ['nullable', 'numeric', 'min:0.0001', 'max:100000'],
            'currency_rate_eur' => ['nullable', 'numeric', 'min:0.0001', 'max:100000'],
            'currency_rate_gbp' => ['nullable', 'numeric', 'min:0.0001', 'max:100000'],
            'currency_rate_aed' => ['nullable', 'numeric', 'min:0.0001', 'max:100000'],
            // Luxury Theme Colors & Preset
            'theme_preset' => ['nullable', 'string', 'max:50'],
            'theme_primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_dark' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_light' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_site_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_card_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_text_dark' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_text_muted' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            // Static Landing Page Images (Files or URLs)
            'hero_image' => $request->hasFile('hero_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'hero_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'panorama_image' => $request->hasFile('panorama_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'panorama_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_1_image' => $request->hasFile('project_1_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_1_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_2_image' => $request->hasFile('project_2_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_2_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'project_3_image' => $request->hasFile('project_3_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'project_3_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'about_image' => $request->hasFile('about_image') ? ['file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'] : ['nullable', 'string', 'max:1000'],
            'about_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            // Hero & Search
            'hero_badge' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1500'],
            'hero_primary_button_text' => ['nullable', 'string', 'max:100'],
            'hero_secondary_button_text' => ['nullable', 'string', 'max:100'],
            'hero_careers_link_text' => ['nullable', 'string', 'max:100'],
            'hero_featured_badge' => ['nullable', 'string', 'max:100'],
            'hero_stat_1_label' => ['nullable', 'string', 'max:100'],
            'hero_stat_2_label' => ['nullable', 'string', 'max:100'],
            'hero_stat_3_value' => ['nullable', 'string', 'max:100'],
            'hero_stat_3_label' => ['nullable', 'string', 'max:100'],
            'filter_category_label' => ['nullable', 'string', 'max:100'],
            'filter_type_label' => ['nullable', 'string', 'max:100'],
            'filter_location_label' => ['nullable', 'string', 'max:100'],
            'filter_button_text' => ['nullable', 'string', 'max:100'],
            // Counter
            'counter_1_value' => ['nullable', 'string', 'max:100'],
            'counter_1_label' => ['nullable', 'string', 'max:100'],
            'counter_2_label' => ['nullable', 'string', 'max:100'],
            'counter_3_label' => ['nullable', 'string', 'max:100'],
            'counter_4_value' => ['nullable', 'string', 'max:100'],
            'counter_4_label' => ['nullable', 'string', 'max:100'],
            // Amenities
            'amenities_badge' => ['nullable', 'string', 'max:100'],
            'amenities_title' => ['nullable', 'string', 'max:255'],
            'amenities_description' => ['nullable', 'string', 'max:1500'],
            'amenity_1_title' => ['nullable', 'string', 'max:255'],
            'amenity_1_description' => ['nullable', 'string', 'max:1000'],
            'amenity_2_title' => ['nullable', 'string', 'max:255'],
            'amenity_2_description' => ['nullable', 'string', 'max:1000'],
            'amenity_3_title' => ['nullable', 'string', 'max:255'],
            'amenity_3_description' => ['nullable', 'string', 'max:1000'],
            'amenity_4_title' => ['nullable', 'string', 'max:255'],
            'amenity_4_description' => ['nullable', 'string', 'max:1000'],
            'amenity_5_title' => ['nullable', 'string', 'max:255'],
            'amenity_5_description' => ['nullable', 'string', 'max:1000'],
            'amenity_6_title' => ['nullable', 'string', 'max:255'],
            'amenity_6_description' => ['nullable', 'string', 'max:1000'],
            'amenity_7_title' => ['nullable', 'string', 'max:255'],
            'amenity_7_description' => ['nullable', 'string', 'max:1000'],
            'amenity_8_title' => ['nullable', 'string', 'max:255'],
            'amenity_8_description' => ['nullable', 'string', 'max:1000'],
            // Featured Properties
            'featured_properties_badge' => ['nullable', 'string', 'max:100'],
            'featured_properties_title' => ['nullable', 'string', 'max:255'],
            'featured_properties_link_text' => ['nullable', 'string', 'max:100'],
            // Panorama
            'panorama_badge' => ['nullable', 'string', 'max:100'],
            'panorama_title' => ['nullable', 'string', 'max:255'],
            'panorama_description' => ['nullable', 'string', 'max:1500'],
            'panorama_primary_button_text' => ['nullable', 'string', 'max:100'],
            'panorama_secondary_button_text' => ['nullable', 'string', 'max:100'],
            // Developments
            'developments_badge' => ['nullable', 'string', 'max:100'],
            'developments_title' => ['nullable', 'string', 'max:255'],
            'developments_link_text' => ['nullable', 'string', 'max:100'],
            'developments_link_url' => ['nullable', 'string', 'max:255'],
            'project_1_tag' => ['nullable', 'string', 'max:100'],
            'project_1_title' => ['nullable', 'string', 'max:255'],
            'project_1_description' => ['nullable', 'string', 'max:1000'],
            'project_2_tag' => ['nullable', 'string', 'max:100'],
            'project_2_title' => ['nullable', 'string', 'max:255'],
            'project_2_description' => ['nullable', 'string', 'max:1000'],
            'project_3_tag' => ['nullable', 'string', 'max:100'],
            'project_3_title' => ['nullable', 'string', 'max:255'],
            'project_3_description' => ['nullable', 'string', 'max:1000'],
            // Latest Properties
            'latest_properties_badge' => ['nullable', 'string', 'max:100'],
            'latest_properties_title' => ['nullable', 'string', 'max:255'],
            'latest_properties_link_text' => ['nullable', 'string', 'max:100'],
            // About
            'about_badge' => ['nullable', 'string', 'max:100'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_description' => ['nullable', 'string', 'max:2000'],
            'about_pillar_1_title' => ['nullable', 'string', 'max:100'],
            'about_pillar_1_desc' => ['nullable', 'string', 'max:100'],
            'about_pillar_2_title' => ['nullable', 'string', 'max:100'],
            'about_pillar_2_desc' => ['nullable', 'string', 'max:100'],
            'about_image_subtitle' => ['nullable', 'string', 'max:100'],
            'about_image_title' => ['nullable', 'string', 'max:255'],
            // Blog / News
            'news_badge' => ['nullable', 'string', 'max:100'],
            'news_title' => ['nullable', 'string', 'max:255'],
            'news_link_text' => ['nullable', 'string', 'max:100'],
            // Testimonials
            'testimonials_badge' => ['nullable', 'string', 'max:100'],
            'testimonials_title' => ['nullable', 'string', 'max:255'],
            // Agents
            'agents_badge' => ['nullable', 'string', 'max:100'],
            'agents_title' => ['nullable', 'string', 'max:255'],
            'agents_link_text' => ['nullable', 'string', 'max:100'],
            // Contact
            'contact_badge' => ['nullable', 'string', 'max:100'],
            'contact_title' => ['nullable', 'string', 'max:255'],
            'contact_description' => ['nullable', 'string', 'max:1500'],
            'contact_box_title' => ['nullable', 'string', 'max:255'],
            'contact_box_description' => ['nullable', 'string', 'max:1500'],
            'contact_benefit_1' => ['nullable', 'string', 'max:255'],
            'contact_benefit_2' => ['nullable', 'string', 'max:255'],
            'contact_benefit_3' => ['nullable', 'string', 'max:255'],
            'contact_submit_text' => ['nullable', 'string', 'max:100'],
            // Newsletter
            'newsletter_badge' => ['nullable', 'string', 'max:100'],
            'newsletter_title' => ['nullable', 'string', 'max:255'],
            'newsletter_description' => ['nullable', 'string', 'max:1500'],
            'newsletter_placeholder' => ['nullable', 'string', 'max:100'],
            'newsletter_button_text' => ['nullable', 'string', 'max:100'],
            // Concierge
            'concierge_button_text' => ['nullable', 'string', 'max:100'],
        ]);

        // Persist basic site settings
        SiteSetting::set('site_name', $validated['site_name']);
        SiteSetting::set('contact_email', $validated['contact_email']);
        SiteSetting::set('contact_phone', $validated['contact_phone']);
        SiteSetting::set('contact_address', $validated['contact_address']);

        if ($request->has('happy_buyers_base_count')) {
            SiteSetting::set('happy_buyers_base_count', (int) ($validated['happy_buyers_base_count'] ?? 0));
        }

        // Persist all landing page content controlled from the dashboard
        $allConfigurableKeys = [
            'hero_badge', 'hero_title', 'hero_description', 'hero_primary_button_text', 'hero_secondary_button_text',
            'hero_careers_link_text', 'hero_featured_badge', 'hero_stat_1_label', 'hero_stat_2_label',
            'hero_stat_3_value', 'hero_stat_3_label', 'filter_category_label', 'filter_type_label',
            'filter_location_label', 'filter_button_text',
            'counter_1_value', 'counter_1_label', 'counter_2_label', 'counter_3_label',
            'counter_4_value', 'counter_4_label',
            'amenities_badge', 'amenities_title', 'amenities_description',
            'amenity_1_title', 'amenity_1_description', 'amenity_2_title', 'amenity_2_description',
            'amenity_3_title', 'amenity_3_description', 'amenity_4_title', 'amenity_4_description',
            'amenity_5_title', 'amenity_5_description', 'amenity_6_title', 'amenity_6_description',
            'amenity_7_title', 'amenity_7_description', 'amenity_8_title', 'amenity_8_description',
            'featured_properties_badge', 'featured_properties_title', 'featured_properties_link_text',
            'panorama_badge', 'panorama_title', 'panorama_description',
            'panorama_primary_button_text', 'panorama_secondary_button_text',
            'developments_badge', 'developments_title', 'developments_link_text', 'developments_link_url',
            'project_1_tag', 'project_1_title', 'project_1_description',
            'project_2_tag', 'project_2_title', 'project_2_description',
            'project_3_tag', 'project_3_title', 'project_3_description',
            'latest_properties_badge', 'latest_properties_title', 'latest_properties_link_text',
            'about_badge', 'about_title', 'about_description',
            'about_pillar_1_title', 'about_pillar_1_desc', 'about_pillar_2_title', 'about_pillar_2_desc',
            'about_image_subtitle', 'about_image_title',
            'news_badge', 'news_title', 'news_link_text',
            'testimonials_badge', 'testimonials_title',
            'agents_badge', 'agents_title', 'agents_link_text',
            'contact_badge', 'contact_title', 'contact_description',
            'contact_box_title', 'contact_box_description',
            'contact_benefit_1', 'contact_benefit_2', 'contact_benefit_3', 'contact_submit_text',
            'newsletter_badge', 'newsletter_title', 'newsletter_description',
            'newsletter_placeholder', 'newsletter_button_text',
            'concierge_button_text',
            'currency_rate_usd', 'currency_rate_eur', 'currency_rate_gbp', 'currency_rate_aed',
            'theme_preset', 'theme_primary_color', 'theme_accent_color', 'theme_accent_dark',
            'theme_accent_light', 'theme_site_bg', 'theme_card_bg', 'theme_text_dark', 'theme_text_muted',
        ];

        foreach ($allConfigurableKeys as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $validated[$key] ?? null);
            }
        }

        // Handle logo upload or URL
        $logoFile = $request->file('site_logo_file') ?? $request->file('site_logo');
        if ($logoFile) {
            $previousLogo = SiteSetting::get('site_logo');

            // Delete the old logo if it exists in the images directory
            if ($previousLogo && str_starts_with($previousLogo, 'images/') && Storage::disk('public')->exists($previousLogo)) {
                Storage::disk('public')->delete($previousLogo);
            }

            // Store the new logo in the images folder on the public disk
            $newPath = $logoFile->store('images', 'public');
            SiteSetting::set('site_logo', $newPath);
        } elseif ($request->has('site_logo') && ! $request->hasFile('site_logo')) {
            $stringLogo = $request->input('site_logo');
            if (filled($stringLogo)) {
                SiteSetting::set('site_logo', trim($stringLogo));
            }
        }

        // Handle static landing page images (uploaded file or custom URL)
        $staticImageKeys = [
            'hero_image',
            'panorama_image',
            'project_1_image',
            'project_2_image',
            'project_3_image',
            'about_image',
        ];

        foreach ($staticImageKeys as $imgKey) {
            $fileInputKey = $imgKey.'_file';
            $uploaded = $request->file($fileInputKey) ?? $request->file($imgKey);

            if ($uploaded) {
                $previousImage = SiteSetting::get($imgKey);
                if ($previousImage && str_starts_with($previousImage, 'site_images/') && Storage::disk('public')->exists($previousImage)) {
                    Storage::disk('public')->delete($previousImage);
                }

                $newPath = $uploaded->store('site_images', 'public');
                SiteSetting::set($imgKey, $newPath);
            } elseif ($request->has($imgKey) && ! $request->hasFile($imgKey)) {
                $stringValue = $request->input($imgKey);
                SiteSetting::set($imgKey, filled($stringValue) ? trim($stringValue) : null);
            }
        }

        $message = 'Site settings, content, and images updated successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'settings',
            ]);
        }

        return redirect()->to(route('dashboard', ['section' => 'settings']))->with('success', $message);
    }
}
