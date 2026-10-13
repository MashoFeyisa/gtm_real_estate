<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Campaign;
use App\Models\CampaignLead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officer = User::query()->where('role', 'campaign_officer')->first();
        $tadesse = Agent::query()->where('email', 'tadesse.agent@example.com')->first() ?? Agent::query()->first();
        $mekdes = Agent::query()->where('email', 'mekdes.agent@example.com')->first() ?? Agent::query()->skip(1)->first() ?? $tadesse;
        $yohannes = Agent::query()->where('email', 'yohannes.agent@example.com')->first() ?? Agent::query()->skip(2)->first() ?? $tadesse;

        $properties = Property::query()->where('status', '!=', 'archived')->take(4)->get();
        $prop1 = $properties->get(0)?->id;
        $prop2 = $properties->get(1)?->id ?? $prop1;
        $prop3 = $properties->get(2)?->id ?? $prop1;

        // 1. Diaspora Promotional Campaign (Awaiting Review)
        $campaign1 = Campaign::query()->updateOrCreate(
            ['slug' => 'grand-diaspora-luxury-homecoming-expo'],
            [
                'agent_id' => $tadesse?->id,
                'user_id' => $tadesse?->user_id,
                'property_id' => $prop1,
                'title' => 'Grand Diaspora Luxury Homecoming Expo',
                'headline' => 'Exclusive 5% Diaspora Incentive & Dedicated Mortgage Advisory',
                'description' => 'A high-impact campaign offering Ethiopian diaspora investors 5% off selected modern apartments, foreign currency escrow facilitation, and turnkey property management.',
                'marketing_copy' => 'Invest in Addis Ababa with complete peace of mind. Full turnkey furnished units, verified title deeds, and dedicated repatriation advisory.',
                'campaign_type' => 'diaspora_promo',
                'target_audience' => 'Diaspora Investors (US, Europe, UAE)',
                'budget' => 75000.00,
                'spent' => 12500.00,
                'start_date' => Carbon::now()->addDays(3)->toDateString(),
                'end_date' => Carbon::now()->addDays(35)->toDateString(),
                'status' => 'under_review',
                'views_count' => 380,
                'clicks_count' => 94,
                'inquiries_count' => 12,
            ]
        );

        // 2. Project Launch Campaign (Awaiting Review)
        $campaign2 = Campaign::query()->updateOrCreate(
            ['slug' => 'diplomatic-quarter-penthouse-villa-showcase'],
            [
                'agent_id' => $mekdes?->id,
                'user_id' => $mekdes?->user_id,
                'property_id' => $prop2,
                'title' => 'Diplomatic Quarter Penthouse & Villa Showcase',
                'headline' => 'Prime Executive Living with Unmatched Security & Skyline Views',
                'description' => 'Curated private showings for embassy delegates, international NGOs, and multinational executives seeking standalone villas with solar backups and private compounds.',
                'marketing_copy' => 'Unrivaled security, ambassadorial specifications, backup generators, and lush landscaped gardens in Addis Ababa’s most secure zone.',
                'campaign_type' => 'project_launch',
                'target_audience' => 'Embassies, International NGOs & Executives',
                'budget' => 50000.00,
                'spent' => 0.00,
                'start_date' => Carbon::now()->addDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(40)->toDateString(),
                'status' => 'under_review',
                'views_count' => 210,
                'clicks_count' => 52,
                'inquiries_count' => 7,
            ]
        );

        // 3. Open House Promotional Campaign (Published & Live)
        $campaign3 = Campaign::query()->updateOrCreate(
            ['slug' => 'spring-villa-open-house-vip-weekend'],
            [
                'agent_id' => $yohannes?->id,
                'user_id' => $yohannes?->user_id,
                'property_id' => $prop3,
                'title' => 'Spring Luxury Villa Open House & VIP Showcase',
                'headline' => 'Tour Ready-to-Move Contemporary Villas this Weekend',
                'description' => 'Two-day exclusive open house event featuring architects, interior designers, and pre-qualification mortgage bank partners on site.',
                'marketing_copy' => 'Walk through ready-to-move homes with smart home automation, custom Italian kitchens, and panoramic city views.',
                'campaign_type' => 'open_house',
                'target_audience' => 'High-Net-Worth Families & Local Investors',
                'budget' => 35000.00,
                'spent' => 28000.00,
                'start_date' => Carbon::now()->subDays(4)->toDateString(),
                'end_date' => Carbon::now()->addDays(16)->toDateString(),
                'status' => 'published',
                'reviewed_by' => $officer?->id,
                'reviewed_at' => Carbon::now()->subDays(5),
                'review_notes' => 'Approved! Campaign is live on client portal with featured status and sponsored banner.',
                'views_count' => 1420,
                'clicks_count' => 328,
                'inquiries_count' => 26,
            ]
        );

        // Add sample customer inquiries / leads to campaign 3
        CampaignLead::query()->firstOrCreate(
            ['email' => 'selamawit.h@example.com', 'campaign_id' => $campaign3->id],
            [
                'name' => 'Dr. Selamawit Haile',
                'phone' => '+251911456789',
                'message' => 'Interested in the 4-bedroom villa layout. Can we arrange a private walkthrough this Saturday morning?',
                'status' => 'contacted',
            ]
        );

        CampaignLead::query()->firstOrCreate(
            ['email' => 'ermias.t@example.com', 'campaign_id' => $campaign3->id],
            [
                'name' => 'Ermias Tilahun',
                'phone' => '+251922889900',
                'message' => 'Looking for financing options and payment schedule for the open house properties.',
                'status' => 'new',
            ]
        );
    }
}
