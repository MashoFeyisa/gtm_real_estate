<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Persona / permission catalog (role defaults for every actor)
        $this->call(RolePermissionSeeder::class);

        // Database-configurable property categories & listing types
        $this->call(PropertyTaxonomySeeder::class);

        // Subcity polygon boundaries & GIS mapping
        $this->call(SubcityBoundarySeeder::class);

        // ── Shared password for all fabricated test users ────────────
        $sharedPassword = Hash::make('secret123');

        // ── 1. Administrator ────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'admin@realestate.com'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => $sharedPassword,
            ]
        );

        // ── 2. Buyer ────────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'buyer@realestate.com'],
            [
                'name' => 'Abebe Desta (Buyer)',
                'role' => 'buyer',
                'password' => $sharedPassword,
            ]
        );

        // ── 3. Tenant ───────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'tenant@realestate.com'],
            [
                'name' => 'Sara Mulugeta (Tenant)',
                'role' => 'tenant',
                'password' => $sharedPassword,
            ]
        );

        // ── 4. Guest / Traveler ─────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'traveler@realestate.com'],
            [
                'name' => 'Dawit Tadesse (Traveler)',
                'role' => 'traveler',
                'password' => $sharedPassword,
            ]
        );

        // ── 5. Property Owner ───────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'owner@realestate.com'],
            [
                'name' => 'Tigist Haile (Property Owner)',
                'role' => 'property_owner',
                'password' => $sharedPassword,
            ]
        );

        // ── 6. Host ─────────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'host@realestate.com'],
            [
                'name' => 'Girma Assefa (Host)',
                'role' => 'host',
                'password' => $sharedPassword,
            ]
        );

        // ── 7. Landlord ─────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'landlord@realestate.com'],
            [
                'name' => 'Meseret Kebede (Landlord)',
                'role' => 'landlord',
                'password' => $sharedPassword,
            ]
        );

        // ── 8. Agent / Broker ───────────────────────────────────────
        $agentUser = User::query()->updateOrCreate(
            ['email' => 'agent@realestate.com'],
            [
                'name' => 'Tadesse Bekele (Agent)',
                'role' => 'agent',
                'password' => $sharedPassword,
            ]
        );

        Agent::query()->updateOrCreate(
            ['email' => 'agent@realestate.com'],
            [
                'name' => 'Tadesse Bekele',
                'phone' => '+251911000001',
                'bio' => 'Senior property consultant with 10+ years in Addis Ababa real estate.',
                'password' => 'secret123',
                'user_id' => $agentUser->id,
            ]
        );

        // ── 9. Real Estate Company ──────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'company@realestate.com'],
            [
                'name' => 'Ethio Prime Properties (Company)',
                'role' => 'company',
                'password' => $sharedPassword,
            ]
        );

        // ── 10. Developer ───────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'developer@realestate.com'],
            [
                'name' => 'Nuradin Dalu (Developer)',
                'role' => 'developer',
                'password' => $sharedPassword,
            ]
        );

        // ── 11. Property Manager ────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'propertymanager@realestate.com'],
            [
                'name' => 'Bekele Wondimu (Property Manager)',
                'role' => 'property_manager',
                'password' => $sharedPassword,
            ]
        );

        // ── 12. Professional Service Provider ───────────────────────
        User::query()->updateOrCreate(
            ['email' => 'service@realestate.com'],
            [
                'name' => 'Mekonnen Gebre (Service Provider)',
                'role' => 'service_provider',
                'password' => $sharedPassword,
            ]
        );

        // ── 13. Moderator ───────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'moderator@realestate.com'],
            [
                'name' => 'Hana Tekle (Moderator)',
                'role' => 'moderator',
                'password' => $sharedPassword,
            ]
        );

        // ── 14. Verification Officer ────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'verification@realestate.com'],
            [
                'name' => 'Selam Ayalew (Verification Officer)',
                'role' => 'verification_officer',
                'password' => $sharedPassword,
            ]
        );

        // ── 15. Verification Company ────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'verifycompany@realestate.com'],
            [
                'name' => 'Ethio Verify Solutions (Verification Company)',
                'role' => 'verification_company',
                'password' => $sharedPassword,
            ]
        );

        // ── 16. Finance Officer ─────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'finance@realestate.com'],
            [
                'name' => 'Rahel Demisse (Finance Officer)',
                'role' => 'finance_officer',
                'password' => $sharedPassword,
            ]
        );

        // ── 17. Customer Support ────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'support@realestate.com'],
            [
                'name' => 'Yonas Tesfaye (Customer Support)',
                'role' => 'customer_support',
                'password' => $sharedPassword,
            ]
        );

        // ── 18. Cleaner / Housekeeping ──────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'cleaner@realestate.com'],
            [
                'name' => 'Almaz Hailu (Housekeeping & Cleaning)',
                'role' => 'cleaner',
                'password' => $sharedPassword,
            ]
        );

        // ── 19. Investor ────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'investor@realestate.com'],
            [
                'name' => 'Kassahun Worku (Portfolio Investor)',
                'role' => 'investor',
                'password' => $sharedPassword,
            ]
        );

        // ── Bonus roles ─────────────────────────────────────────────

        // Regular Client / User (fallback role)
        User::query()->updateOrCreate(
            ['email' => 'user@realestate.com'],
            [
                'name' => 'Regular User',
                'role' => 'user',
                'password' => $sharedPassword,
            ]
        );

        // Receptionist (legacy → customer_support)
        User::query()->updateOrCreate(
            ['email' => 'receptionist@realestate.com'],
            [
                'name' => 'Hiwot Alemayehu (Front Desk Receptionist)',
                'role' => 'receptionist',
                'password' => $sharedPassword,
            ]
        );

        // Campaign Officer (Promotions & Marketing Review Desk)
        User::query()->updateOrCreate(
            ['email' => 'campaign@realestate.com'],
            [
                'name' => 'Campaign Officer (Promotions & Marketing Desk)',
                'role' => 'campaign_officer',
                'password' => $sharedPassword,
            ]
        );

        // Additional sample agents for realistic CRM / pipeline testing
        $sampleAgents = [
            ['name' => 'Mekdes Ali', 'email' => 'mekdes.agent@example.com', 'phone' => '+251922000002', 'bio' => 'Investment advisor'],
            ['name' => 'Yohannes Gebre', 'email' => 'yohannes.agent@example.com', 'phone' => '+251933000003', 'bio' => 'Sales manager'],
        ];

        foreach ($sampleAgents as $agentData) {
            $agent = Agent::query()->updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'phone' => $agentData['phone'],
                    'bio' => $agentData['bio'],
                    'password' => 'secret123',
                ]
            );

            $account = User::query()->updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'role' => 'agent',
                    'password' => $sharedPassword,
                ]
            );

            $agent->forceFill(['user_id' => $account->id])->save();
        }

        // Persona-specific demo data for all 17 actor desks/portals
        $this->call(ActorSeedDataSeeder::class);

        $this->call(ShortStayHostSeeder::class);
        $this->call(CampaignSeeder::class);
    }
}
