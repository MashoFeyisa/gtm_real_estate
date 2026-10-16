<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use App\Support\DemoData;
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

        // Baseline dated finance-rate book (SRS §171)
        $this->call(FinanceRateSeeder::class);

        // Database-configurable property categories & listing types
        $this->call(PropertyTaxonomySeeder::class);

        // Subcity polygon boundaries & GIS mapping
        $this->call(SubcityBoundarySeeder::class);

        // Demo accounts with a shared, well-known password are fabricated data.
        // They may only exist on a developer machine: production seeding must
        // refuse rather than create accounts anyone can log into.
        if (! DemoData::allowed()) {
            $this->command?->warn(
                'Skipping demo account seeding: APP_ENV is "'.app()->environment().'". '
                .'Set APP_ENV=local to seed the fabricated demo accounts.'
            );

            return;
        }

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

        // ── 2. Guest ──────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'guest@realestate.com'],
            [
                'name' => 'Guest Visitor (Guest)',
                'role' => 'guest',
                'password' => $sharedPassword,
            ]
        );

        // ── 3. Buyer ────────────────────────────────────────────────
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
                // The demo agent must be able to sign in: AuthController blocks
                // logins when the linked agent profile is inactive.
                'is_active' => true,
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

        // NOTE: the former "bonus" personas (user@realestate.com as role `user`
        // and receptionist@realestate.com as role `receptionist`) were removed:
        // those roles merged into `buyer` and `customer_support`, which already
        // have their own persona account. Exactly one account per surviving
        // role is seeded. See docs/USER_CLEANUP_PLAN.md.

        // ── Campaign Officer (Promotions & Marketing Review Desk) ────────
        User::query()->updateOrCreate(
            ['email' => 'campaign@realestate.com'],
            [
                'name' => 'Campaign Officer (Promotions & Marketing Desk)',
                'role' => 'campaign_officer',
                'password' => $sharedPassword,
            ]
        );

        // NOTE: exactly one account per role is seeded on purpose. The extra
        // "sample agents" block that used to live here created second agent
        // accounts (mekdes.agent@example.com, yohannes.agent@example.com) on
        // every seed and broke the one-account-per-role rule.
        // See docs/USER_CLEANUP_PLAN.md.

        // Persona-specific demo data for all 17 actor desks/portals
        $this->call(ActorSeedDataSeeder::class);

        $this->call(ShortStayHostSeeder::class);
        $this->call(ExperienceSeeder::class);
        $this->call(CorporateTenancySeeder::class);
        $this->call(CampaignSeeder::class);
    }
}
