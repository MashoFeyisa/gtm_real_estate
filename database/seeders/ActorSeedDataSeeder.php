<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\InvestorPortfolio;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\MaintenanceTicket;
use App\Models\OfferNegotiation;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyLike;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\UnitReservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds representative data for every persona desk / portal (17 actors),
 * so each role's queues render populated screens out of the box.
 *
 * All blocks are guarded with existence checks and remain idempotent on re-run.
 */
class ActorSeedDataSeeder extends Seeder
{
    /**
     * Persona user emails as created by DatabaseSeeder.
     *
     * @var array<string, string>
     */
    private array $personaEmails = [
        'admin' => 'admin@realestate.com',
        'buyer' => 'buyer@realestate.com',
        'tenant' => 'tenant@realestate.com',
        'traveler' => 'traveler@realestate.com',
        'property_owner' => 'owner@realestate.com',
        'host' => 'host@realestate.com',
        'landlord' => 'landlord@realestate.com',
        'agent' => 'agent@realestate.com',
        'company' => 'company@realestate.com',
        'developer' => 'developer@realestate.com',
        'property_manager' => 'propertymanager@realestate.com',
        'service_provider' => 'service@realestate.com',
        'moderator' => 'moderator@realestate.com',
        'verification_officer' => 'verification@realestate.com',
        'verification_company' => 'verifycompany@realestate.com',
        'finance_officer' => 'finance@realestate.com',
        'customer_support' => 'support@realestate.com',
        'cleaner' => 'cleaner@realestate.com',
        'investor' => 'investor@realestate.com',
    ];

    public function run(): void
    {
        $users = $this->resolvePersonaUsers();

        if ($users->isEmpty()) {
            $this->command?->warn('ActorSeedDataSeeder: no persona users found — run DatabaseSeeder first.');

            return;
        }

        $this->seedBuyerData($users);
        $this->seedTenantData($users);
        $this->seedTravelerData($users);
        $this->seedPropertyOwnerData($users);
        $this->seedHostData($users);
        $this->seedLandlordData($users);
        $this->seedAgentData($users);
        $this->seedCompanyData($users);
        $this->seedDeveloperData($users);
        $this->seedPropertyManagerData($users);
        $this->seedServiceProviderData($users);
        $this->seedModeratorData($users);
        $this->seedVerificationOfficerData($users);
        $this->seedVerificationCompanyData($users);
        $this->seedFinanceOfficerData($users);
        $this->seedCustomerSupportData($users);
        $this->seedCleanerData($users);
        $this->seedInvestorData($users);
    }

    /**
     * Map persona keys to existing User models (skips missing users).
     *
     * @return Collection<string, User>
     */
    private function resolvePersonaUsers(): Collection
    {
        return collect($this->personaEmails)
            ->mapWithKeys(function (string $email, string $persona) {
                $user = User::query()->where('email', $email)->first();

                return $user ? [$persona => $user] : [];
            });
    }

    /**
     * Buyer (client portal): favorites, viewings, offers + negotiations, inquiries.
     */
    private function seedBuyerData(Collection $users): void
    {
        $buyer = $users->get('buyer');

        if (! $buyer || PropertyLike::query()->where('user_id', $buyer->id)->exists()) {
            return;
        }

        $saleProperties = Property::query()->where('type', 'sale')->take(3)->get();

        if ($saleProperties->isEmpty()) {
            $saleProperties = Property::factory()->count(3)->create(['type' => 'sale', 'status' => 'published']);
        }

        foreach ($saleProperties as $property) {
            PropertyLike::query()->firstOrCreate([
                'user_id' => $buyer->id,
                'property_id' => $property->id,
            ]);
        }

        PropertyViewing::factory()->count(2)->confirmed()->create([
            'property_id' => $saleProperties->first()->id,
            'user_id' => $buyer->id,
            'buyer_name' => $buyer->name,
            'buyer_email' => $buyer->email,
        ]);

        $offer = PropertyOffer::factory()->create([
            'property_id' => $saleProperties->first()->id,
            'user_id' => $buyer->id,
            'buyer_name' => $buyer->name,
            'buyer_email' => $buyer->email,
        ]);

        OfferNegotiation::factory()->count(2)->create(['property_offer_id' => $offer->id]);

        PropertyOffer::factory()->count(2)->create([
            'user_id' => $buyer->id,
            'buyer_name' => $buyer->name,
            'buyer_email' => $buyer->email,
        ]);

        Inquiry::factory()->count(2)->create([
            'name' => $buyer->name,
            'email' => $buyer->email,
        ]);
    }

    /**
     * Tenant (client portal): lease, rent schedule, maintenance tickets, documents.
     */
    private function seedTenantData(Collection $users): void
    {
        $tenant = $users->get('tenant');
        $landlord = $users->get('landlord');

        if (! $tenant || Lease::query()->where('tenant_user_id', $tenant->id)->exists()) {
            return;
        }

        $property = Property::factory()->forRent()->create(['status' => 'published']);

        $lease = Lease::factory()->create([
            'property_id' => $property->id,
            'landlord_user_id' => $landlord?->id,
            'tenant_user_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_email' => $tenant->email,
        ]);

        RentSchedule::factory()->count(3)->paid()->create(['lease_id' => $lease->id]);
        RentSchedule::factory()->count(3)->create(['lease_id' => $lease->id]);

        MaintenanceTicket::factory()->count(2)->create([
            'property_id' => $property->id,
            'user_id' => $tenant->id,
        ]);
        MaintenanceTicket::factory()->resolved()->create([
            'property_id' => $property->id,
            'user_id' => $tenant->id,
        ]);

        PropertyDocument::factory()->titleDeed()->verified()->create([
            'property_id' => $property->id,
            'user_id' => $tenant->id,
        ]);
    }

    /**
     * Traveler (client portal): stay bookings, verified review, payments.
     */
    private function seedTravelerData(Collection $users): void
    {
        $traveler = $users->get('traveler');

        if (! $traveler || Booking::query()->where('user_id', $traveler->id)->exists()) {
            return;
        }

        $completed = Booking::factory()->completed()->create([
            'user_id' => $traveler->id,
            'guest_name' => $traveler->name,
            'guest_email' => $traveler->email,
        ]);

        PropertyReview::factory()->withHostReply()->create([
            'property_id' => $completed->property_id,
            'user_id' => $traveler->id,
            'booking_id' => $completed->id,
        ]);

        Booking::factory()->upcoming()->create([
            'user_id' => $traveler->id,
            'guest_name' => $traveler->name,
            'guest_email' => $traveler->email,
        ]);

        Payment::factory()->forBooking($completed)->create([
            'user_id' => $traveler->id,
            'payer_name' => $traveler->name,
            'payer_email' => $traveler->email,
        ]);
    }

    /**
     * Property owner (role portal): owned property with verified documents,
     * plus bookings/offers/viewings/inquiries scoped to the owner account.
     */
    private function seedPropertyOwnerData(Collection $users): void
    {
        $owner = $users->get('property_owner');

        if (! $owner || PropertyDocument::query()->where('user_id', $owner->id)->exists()) {
            return;
        }

        $property = Property::factory()->verified()->create(['status' => 'published']);

        PropertyDocument::factory()->titleDeed()->verified()->create([
            'property_id' => $property->id,
            'user_id' => $owner->id,
        ]);
        PropertyDocument::factory()->create([
            'property_id' => $property->id,
            'user_id' => $owner->id,
        ]);

        Booking::factory()->create([
            'user_id' => $owner->id,
            'guest_name' => $owner->name,
            'guest_email' => $owner->email,
        ]);

        PropertyOffer::factory()->count(2)->create([
            'user_id' => $owner->id,
            'buyer_name' => $owner->name,
            'buyer_email' => $owner->email,
        ]);

        PropertyViewing::factory()->confirmed()->create([
            'user_id' => $owner->id,
            'buyer_name' => $owner->name,
            'buyer_email' => $owner->email,
        ]);

        Inquiry::factory()->create([
            'name' => $owner->name,
            'email' => $owner->email,
        ]);
    }

    /**
     * Host (client portal): short-stay property + listing, live bookings,
     * housekeeping requests, and a guest review with host reply.
     */
    private function seedHostData(Collection $users): void
    {
        $host = $users->get('host');

        if (! $host || Property::query()->where('host_id', $host->id)->exists()) {
            return;
        }

        $stayProperty = Property::factory()->forStay()->create([
            'host_id' => $host->id,
            'status' => 'published',
        ]);

        Listing::factory()->stay()->create([
            'property_id' => $stayProperty->id,
            'status' => 'published',
        ]);

        $booking = Booking::factory()->checkedIn()->create([
            'property_id' => $stayProperty->id,
        ]);

        GuestServiceRequest::factory()->count(2)->create([
            'property_id' => $stayProperty->id,
            'booking_id' => $booking->id,
        ]);

        GuestServiceRequest::factory()->completed()->create([
            'property_id' => $stayProperty->id,
            'booking_id' => $booking->id,
        ]);

        PropertyReview::factory()->withHostReply()->create([
            'property_id' => $stayProperty->id,
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Landlord (role portal): rental property, tenant lease, rent schedule,
     * and maintenance tickets on the landlord's property.
     */
    private function seedLandlordData(Collection $users): void
    {
        $landlord = $users->get('landlord');

        if (! $landlord) {
            return;
        }

        // The tenant block may already link a lease to this landlord — reuse it.
        $lease = Lease::query()->where('landlord_user_id', $landlord->id)->first();

        if (! $lease) {
            $property = Property::factory()->forRent()->create(['status' => 'published']);

            $lease = Lease::factory()->create([
                'property_id' => $property->id,
                'landlord_user_id' => $landlord->id,
            ]);
        }

        if (! RentSchedule::query()->where('lease_id', $lease->id)->exists()) {
            RentSchedule::factory()->count(3)->paid()->create(['lease_id' => $lease->id]);
            RentSchedule::factory()->count(3)->create(['lease_id' => $lease->id]);
        }

        if (! MaintenanceTicket::query()->where('property_id', $lease->property_id)->where('priority', 'emergency')->exists()) {
            MaintenanceTicket::factory()->count(2)->create(['property_id' => $lease->property_id]);
            MaintenanceTicket::factory()->emergency()->create(['property_id' => $lease->property_id]);
        }
    }

    /**
     * Agent (agent portal): managed properties + listings, commissions,
     * inbound client inquiries and scheduled viewings.
     */
    private function seedAgentData(Collection $users): void
    {
        $agentUser = $users->get('agent');
        $agentProfile = $agentUser
            ? Agent::query()->where('user_id', $agentUser->id)->first()
                ?? Agent::query()->where('email', $this->personaEmails['agent'])->first()
            : null;

        if (! $agentProfile || Commission::query()->where('agent_id', $agentProfile->id)->exists()) {
            return;
        }

        $properties = Property::factory()->count(2)->create(['agent_id' => $agentProfile->id]);

        Listing::factory()->count(2)->create([
            'property_id' => $properties->first()->id,
            'status' => 'published',
        ]);

        Commission::factory()->count(2)->create(['agent_id' => $agentProfile->id]);
        Commission::factory()->paid()->create(['agent_id' => $agentProfile->id]);

        Inquiry::factory()->count(3)->create(['agent_id' => $agentProfile->id]);

        PropertyViewing::factory()->count(2)->confirmed()->create([
            'agent_id' => $agentProfile->id,
            'property_id' => $properties->first()->id,
        ]);
    }

    /**
     * Real estate company (company dashboard): branches + branch agents.
     */
    private function seedCompanyData(Collection $users): void
    {
        $company = $users->get('company');

        if (! $company) {
            return;
        }

        $this->call(BranchSeeder::class);
    }

    /**
     * Developer (developer portal): projects, buildings, units, milestones,
     * payment plans, and buyer unit reservations.
     */
    private function seedDeveloperData(Collection $users): void
    {
        $developer = $users->get('developer');
        $buyer = $users->get('buyer');

        if (! $developer) {
            return;
        }

        $this->call(DeveloperProjectSeeder::class);

        $project = Project::query()->where('slug', 'bole-atlas-luxury-heights')->first();

        if (! $project || UnitReservation::query()->where('project_id', $project->id)->exists()) {
            return;
        }

        $unit = $project->units()->where('status', 'available')->first()
            ?? $project->units()->first();

        if ($unit) {
            UnitReservation::factory()->count(2)->confirmed()->create([
                'project_id' => $project->id,
                'unit_id' => $unit->id,
                'user_id' => $buyer?->id,
            ]);
        }
    }

    /**
     * Property manager (role portal): platform-wide guests, maintenance and
     * housekeeping queues.
     */
    private function seedPropertyManagerData(Collection $users): void
    {
        $manager = $users->get('property_manager');

        if (! $manager || GuestServiceRequest::query()->exists()) {
            return;
        }

        GuestServiceRequest::factory()->count(3)->create();
        MaintenanceTicket::factory()->count(2)->create();
    }

    /**
     * Service provider (role portal): professional documents + service payouts.
     */
    private function seedServiceProviderData(Collection $users): void
    {
        $provider = $users->get('service_provider');

        if (! $provider || Payment::query()->where('user_id', $provider->id)->exists()) {
            return;
        }

        PropertyDocument::factory()->count(2)->verified()->create([
            'user_id' => $provider->id,
        ]);

        Payment::factory()->count(2)->create([
            'user_id' => $provider->id,
            'payer_name' => $provider->name,
            'payer_email' => $provider->email,
            'payment_type' => 'earnest_deposit',
        ]);
    }

    /**
     * Moderator (moderation desk): pending-review listings, reported listings,
     * pending content reviews.
     */
    private function seedModeratorData(Collection $users): void
    {
        $moderator = $users->get('moderator');

        if (! $moderator || Listing::query()->where('status', 'pending_review')->exists()) {
            return;
        }

        Listing::factory()->count(2)->pendingReview()->create();
        Listing::factory()->count(2)->create(['status' => 'paused']);

        PropertyReport::factory()->count(2)->create();
        PropertyReport::factory()->underInvestigation()->create();

        PropertyReview::factory()->pending()->count(2)->create();
    }

    /**
     * Verification officer (verification desk): pending property documents,
     * agents awaiting license verification, projects awaiting verification.
     */
    private function seedVerificationOfficerData(Collection $users): void
    {
        $officer = $users->get('verification_officer');

        if (! $officer) {
            return;
        }

        if (! PropertyDocument::query()->where('status', 'rejected')->exists()) {
            PropertyDocument::factory()->count(3)->create();
            PropertyDocument::factory()->titleDeed()->create();
            PropertyDocument::factory()->rejected()->create();
        }

        if (! Agent::query()->where('license_verified', false)->exists()) {
            Agent::factory()->pendingLicense()->count(2)->create();
        }

        if (! Project::query()->where('developer_verified', false)->exists()) {
            Project::factory()->create(['developer_verified' => false]);
        }
    }

    /**
     * Verification company (verification portal): pending verification jobs
     * and service payments.
     */
    private function seedVerificationCompanyData(Collection $users): void
    {
        $verifyCompany = $users->get('verification_company');

        if (! $verifyCompany || GuestServiceRequest::query()->where('status', 'pending')->exists()) {
            return;
        }

        GuestServiceRequest::factory()->count(2)->create();
    }

    /**
     * Finance officer (finance dashboard): payments awaiting confirmation,
     * ledger entries, commissions pending payout.
     */
    private function seedFinanceOfficerData(Collection $users): void
    {
        $finance = $users->get('finance_officer');

        if (! $finance || Payment::query()->where('status', 'pending')->exists()) {
            return;
        }

        Payment::factory()->count(2)->awaitingConfirmation()->create();

        FinancialLedger::factory()->count(4)->create();

        $pendingCommissionAgent = Agent::query()->first();

        if ($pendingCommissionAgent) {
            Commission::factory()->create(['agent_id' => $pendingCommissionAgent->id]);
        }
    }

    /**
     * Customer support (support desk): unread inbound inquiries in the queue.
     */
    private function seedCustomerSupportData(Collection $users): void
    {
        $support = $users->get('customer_support');

        if (! $support || Inquiry::query()->where('status', 'new')->exists()) {
            return;
        }

        Inquiry::factory()->count(2)->unread()->create();
    }

    /**
     * Cleaner / Housekeeping: assigned turnaround tasks and checklist workflows.
     */
    private function seedCleanerData(Collection $users): void
    {
        $cleaner = $users->get('cleaner');

        if (! $cleaner || CleaningTask::query()->where('cleaner_user_id', $cleaner->id)->exists()) {
            return;
        }

        $property = Property::query()->first();
        if (! $property) {
            return;
        }

        CleaningTask::create([
            'property_id' => $property->id,
            'cleaner_user_id' => $cleaner->id,
            'task_code' => 'CLN-BOL-104',
            'room_number' => 'Villa Suite B-104 (Bole)',
            'status' => 'assigned',
            'scheduled_date' => now()->toDateString(),
            'notes' => 'Full turnaround after guest checkout. Restock Harar coffee beans.',
            'checklist' => [
                ['item' => 'Dust all surfaces and vacuum carpets', 'done' => false],
                ['item' => 'Change bed linens and sanitize towels', 'done' => false],
                ['item' => 'Scrub master bathroom and restock toiletries', 'done' => false],
                ['item' => 'Clean kitchen sink and sanitize refrigerator', 'done' => false],
                ['item' => 'Restock coffee, tea, and welcome amenities', 'done' => false],
            ],
        ]);

        CleaningTask::create([
            'property_id' => $property->id,
            'cleaner_user_id' => $cleaner->id,
            'task_code' => 'CLN-KAZ-701',
            'room_number' => 'Penthouse 7A (Kazanchis)',
            'status' => 'in_progress',
            'scheduled_date' => now()->toDateString(),
            'started_at' => now()->subHours(1),
            'notes' => 'Deep cleaning in progress before VIP arrival.',
            'checklist' => [
                ['item' => 'Wash balcony windows', 'done' => true],
                ['item' => 'Sanitize all kitchen appliances', 'done' => true],
                ['item' => 'Replace master bed linens', 'done' => false],
            ],
        ]);

        CleaningTask::create([
            'property_id' => $property->id,
            'cleaner_user_id' => $cleaner->id,
            'task_code' => 'CLN-CMC-202',
            'room_number' => 'Studio 2B (CMC)',
            'status' => 'ready',
            'scheduled_date' => now()->subDay()->toDateString(),
            'started_at' => now()->subHours(5),
            'inspected_at' => now()->subHours(2),
            'ready_at' => now()->subHour(),
            'notes' => 'Turnaround completed and inspected. Keys placed in master lockbox.',
            'checklist' => [
                ['item' => 'Clean bathroom', 'done' => true],
                ['item' => 'Change linens', 'done' => true],
                ['item' => 'Trash removal', 'done' => true],
            ],
        ]);
    }

    /**
     * Investor: portfolios, properties, purchase price and yield estimations.
     */
    private function seedInvestorData(Collection $users): void
    {
        $investor = $users->get('investor');

        if (! $investor || InvestorPortfolio::query()->where('user_id', $investor->id)->exists()) {
            return;
        }

        $properties = Property::query()->take(3)->get();
        if ($properties->isEmpty()) {
            return;
        }

        foreach ($properties as $idx => $property) {
            $basePrice = 8500000 + ($idx * 3000000);
            $monthlyRent = ($basePrice * 0.085) / 12;

            InvestorPortfolio::create([
                'user_id' => $investor->id,
                'property_id' => $property->id,
                'asset_name' => 'Addis Asset #'.($idx + 1).' ('.($property->title ?? 'Prime Property').')',
                'asset_type' => 'residential',
                'location' => ($property->city ?? 'Addis Ababa').', '.($property->subcity ?? 'Bole'),
                'purchase_price' => $basePrice,
                'purchase_date' => now()->subMonths(6 + ($idx * 4))->toDateString(),
                'current_valuation' => $basePrice * 1.15,
                'monthly_rental_income' => $monthlyRent,
                'monthly_expenses' => $monthlyRent * 0.15,
                'occupancy_rate' => 95.0,
                'notes' => 'Prime location in '.($property->city ?? 'Addis Ababa').' with high occupancy.',
            ]);
        }
    }
}
