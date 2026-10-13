<?php

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\FinancialLedger;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\MaintenanceTicket;
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
use Database\Seeders\ActorSeedDataSeeder;

beforeEach(function () {
    $this->seed();
});

function seededPersona(string $role): User
{
    return User::query()->where('role', $role)->firstOrFail();
}

test('seeds marketplace activity for the buyer persona', function () {
    $buyer = seededPersona('buyer');

    expect(PropertyLike::query()->where('user_id', $buyer->id)->exists())->toBeTrue()
        ->and(PropertyViewing::query()->where('user_id', $buyer->id)->exists())->toBeTrue()
        ->and(PropertyOffer::query()->where('user_id', $buyer->id)->exists())->toBeTrue()
        ->and(Inquiry::query()->where('email', $buyer->email)->exists())->toBeTrue();
});

test('seeds lease, rent schedule and maintenance data for the tenant persona', function () {
    $tenant = seededPersona('tenant');

    $lease = Lease::query()->where('tenant_user_id', $tenant->id)->firstOrFail();

    expect(RentSchedule::query()->where('lease_id', $lease->id)->where('status', 'paid')->exists())->toBeTrue()
        ->and(RentSchedule::query()->where('lease_id', $lease->id)->where('status', 'pending')->exists())->toBeTrue()
        ->and(MaintenanceTicket::query()->where('user_id', $tenant->id)->exists())->toBeTrue()
        ->and(PropertyDocument::query()->where('user_id', $tenant->id)->exists())->toBeTrue();
});

test('seeds bookings, reviews and payments for the traveler persona', function () {
    $traveler = seededPersona('traveler');

    $completed = Booking::query()->where('user_id', $traveler->id)->where('status', 'completed')->firstOrFail();

    expect(PropertyReview::query()->where('user_id', $traveler->id)->where('booking_id', $completed->id)->exists())->toBeTrue()
        ->and(Payment::query()->where('user_id', $traveler->id)->where('payable_id', $completed->id)->exists())->toBeTrue()
        ->and(Booking::query()->where('user_id', $traveler->id)->where('status', 'confirmed')->exists())->toBeTrue();
});

test('seeds owned property with verified documents for the property owner persona', function () {
    $owner = seededPersona('property_owner');

    $document = PropertyDocument::query()->where('user_id', $owner->id)->firstOrFail();

    expect($document->property)->not->toBeNull()
        ->and(PropertyDocument::query()->where('user_id', $owner->id)->where('status', 'verified')->exists())->toBeTrue()
        ->and(Booking::query()->where('user_id', $owner->id)->exists())->toBeTrue()
        ->and(PropertyOffer::query()->where('user_id', $owner->id)->exists())->toBeTrue();
});

test('seeds short-stay property, bookings and housekeeping for the host persona', function () {
    $host = seededPersona('host');

    $stayProperty = Property::query()->where('host_id', $host->id)->firstOrFail();

    expect(Listing::query()->where('property_id', $stayProperty->id)->where('listing_type', 'stay')->exists())->toBeTrue()
        ->and(Booking::query()->where('property_id', $stayProperty->id)->where('status', 'checked_in')->exists())->toBeTrue()
        ->and(GuestServiceRequest::query()->where('property_id', $stayProperty->id)->where('status', 'pending')->exists())->toBeTrue()
        ->and(PropertyReview::query()->where('property_id', $stayProperty->id)->whereNotNull('host_reply')->exists())->toBeTrue();
});

test('seeds rental property, lease and rent desk data for the landlord persona', function () {
    $landlord = seededPersona('landlord');

    $lease = Lease::query()->where('landlord_user_id', $landlord->id)->firstOrFail();

    expect($lease->property)->not->toBeNull()
        ->and(RentSchedule::query()->where('lease_id', $lease->id)->count())->toBeGreaterThan(0)
        ->and(MaintenanceTicket::query()->where('property_id', $lease->property_id)->where('priority', 'emergency')->exists())->toBeTrue();
});

test('seeds CRM pipeline data for the agent persona', function () {
    $agent = Agent::query()->where('email', 'agent@realestate.com')->firstOrFail();

    expect(Commission::query()->where('agent_id', $agent->id)->where('status', 'pending')->exists())->toBeTrue()
        ->and(Commission::query()->where('agent_id', $agent->id)->where('status', 'paid')->exists())->toBeTrue()
        ->and(Inquiry::query()->where('agent_id', $agent->id)->exists())->toBeTrue()
        ->and(PropertyViewing::query()->where('agent_id', $agent->id)->exists())->toBeTrue()
        ->and(Property::query()->where('agent_id', $agent->id)->exists())->toBeTrue();
});

test('seeds branches for the real estate company persona', function () {
    seededPersona('company');

    expect(Branch::query()->where('is_headquarters', true)->exists())->toBeTrue()
        ->and(Branch::query()->count())->toBeGreaterThanOrEqual(3);
});

test('seeds projects, units and reservations for the developer persona', function () {
    seededPersona('developer');

    $project = Project::query()->where('slug', 'bole-atlas-luxury-heights')->firstOrFail();

    expect($project->buildings()->count())->toBeGreaterThan(0)
        ->and($project->units()->count())->toBeGreaterThan(0)
        ->and(UnitReservation::query()->where('project_id', $project->id)->where('status', 'confirmed')->exists())->toBeTrue();
});

test('seeds housekeeping queue for the property manager persona', function () {
    seededPersona('property_manager');

    expect(GuestServiceRequest::query()->exists())->toBeTrue()
        ->and(MaintenanceTicket::query()->exists())->toBeTrue();
});

test('seeds documents and payouts for the service provider persona', function () {
    $provider = seededPersona('service_provider');

    expect(PropertyDocument::query()->where('user_id', $provider->id)->where('status', 'verified')->exists())->toBeTrue()
        ->and(Payment::query()->where('user_id', $provider->id)->exists())->toBeTrue();
});

test('seeds moderation queues for the moderator persona', function () {
    seededPersona('moderator');

    expect(Listing::query()->where('status', 'pending_review')->exists())->toBeTrue()
        ->and(Listing::query()->where('status', 'paused')->exists())->toBeTrue()
        ->and(PropertyReport::query()->where('status', 'pending')->exists())->toBeTrue()
        ->and(PropertyReport::query()->where('status', 'under_investigation')->exists())->toBeTrue()
        ->and(PropertyReview::query()->where('status', 'pending')->exists())->toBeTrue();
});

test('seeds verification queues for the verification officer persona', function () {
    seededPersona('verification_officer');

    expect(PropertyDocument::query()->where('status', 'pending')->exists())->toBeTrue()
        ->and(PropertyDocument::query()->where('status', 'rejected')->exists())->toBeTrue()
        ->and(Agent::query()->where('license_verified', false)->exists())->toBeTrue()
        ->and(Project::query()->where('developer_verified', false)->exists())->toBeTrue();
});

test('seeds pending jobs for the verification company persona', function () {
    seededPersona('verification_company');

    expect(GuestServiceRequest::query()->where('status', 'pending')->exists())->toBeTrue();
});

test('seeds payment audit queue and ledgers for the finance officer persona', function () {
    seededPersona('finance_officer');

    expect(Payment::query()->where('status', 'pending')->exists())->toBeTrue()
        ->and(FinancialLedger::query()->exists())->toBeTrue()
        ->and(Commission::query()->where('status', 'pending')->exists())->toBeTrue();
});

test('seeds inbound support queue for the customer support persona', function () {
    seededPersona('customer_support');

    expect(Inquiry::query()->where('status', 'new')->exists())->toBeTrue();
});

test('actor seed data seeder is idempotent across repeated runs', function () {
    $countsBefore = [
        Property::query()->count(),
        Lease::query()->count(),
        Booking::query()->count(),
        PropertyOffer::query()->count(),
        PropertyDocument::query()->count(),
        GuestServiceRequest::query()->count(),
        Branch::query()->count(),
        Project::query()->count(),
        Commission::query()->count(),
    ];

    $this->seed(ActorSeedDataSeeder::class);

    expect([
        Property::query()->count(),
        Lease::query()->count(),
        Booking::query()->count(),
        PropertyOffer::query()->count(),
        PropertyDocument::query()->count(),
        GuestServiceRequest::query()->count(),
        Branch::query()->count(),
        Project::query()->count(),
        Commission::query()->count(),
    ])->toBe($countsBefore);
});
