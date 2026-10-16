<?php

use App\Models\AuditLog;
use App\Models\FinancialLedger;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserDispute;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
});

// ══════════════════════════════════════════════════════════════════════════════
// 1. MODERATOR ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('moderator can approve, reject with notes, and pause a property listing', function () {
    $moderator = User::factory()->create(['role' => 'moderator']);
    $seller = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create([
        'user_id' => $seller->id,
        'status' => 'pending',
        'is_active' => false,
    ]);

    $listing = Listing::create([
        'property_id' => $property->id,
        'user_id' => $seller->id,
        'title' => 'Luxury Penthouse in Bole',
        'listing_type' => 'sale',
        'price' => 5000000,
        'status' => 'pending',
    ]);

    // 1. Approve listing
    $approveRes = $this->actingAs($moderator)->patch(route('moderator.listings.decision', $listing), [
        'status' => 'approved',
        'moderation_notes' => 'Meets listing standards and clean title deed.',
    ]);
    $approveRes->assertRedirect();
    $listing->refresh();
    expect($listing->status)->toBe('approved');
    expect($listing->property->is_active)->toBeTrue();

    // 2. Reject listing
    $rejectRes = $this->actingAs($moderator)->patch(route('moderator.listings.decision', $listing), [
        'status' => 'rejected',
        'moderation_notes' => 'Inaccurate location coordinates provided.',
    ]);
    $rejectRes->assertRedirect();
    $listing->refresh();
    expect($listing->status)->toBe('rejected');
    expect($listing->property->is_active)->toBeFalse();

    // 3. Pause listing
    $pauseRes = $this->actingAs($moderator)->patch(route('moderator.listings.decision', $listing), [
        'status' => 'paused',
        'moderation_notes' => 'Paused pending owner clarification.',
    ]);
    $pauseRes->assertRedirect();
    $listing->refresh();
    expect($listing->status)->toBe('paused');

    // Verify AuditLog
    expect(AuditLog::where('action', 'listing.moderation_decision')->count())->toBeGreaterThanOrEqual(3);
});

test('moderator can triage fraud report: unpublish listing, warn user, and flag fraud', function () {
    $moderator = User::factory()->create(['role' => 'moderator']);
    $seller = User::factory()->create(['role' => 'seller', 'is_warned' => false]);
    $property = Property::factory()->create([
        'user_id' => $seller->id,
        'status' => 'published',
        'is_active' => true,
    ]);

    $report = PropertyReport::create([
        'property_id' => $property->id,
        'user_id' => $moderator->id,
        'subject' => 'Suspicious deposit demand',
        'reason' => 'scam',
        'description' => 'Seller asked for cash deposit before inspection',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($moderator)->patch(route('moderator.reports.decision', $report), [
        'status' => 'resolved',
        'action_type' => 'warn_user',
        'resolution_notes' => 'Account issued formal warning for off-platform deposit demand.',
    ]);
    $response->assertRedirect();

    $report->refresh();
    $seller->refresh();
    expect($report->status)->toBe('resolved');
    expect($report->action_taken)->toBe('user_warned');
    expect((bool) $seller->is_warned)->toBeTrue();
});

test('moderator can moderate reviews visibility and delete inappropriate content', function () {
    $moderator = User::factory()->create(['role' => 'moderator']);
    $seller = User::factory()->create(['role' => 'seller']);
    $property = Property::factory()->create(['user_id' => $seller->id]);

    $review = PropertyReview::create([
        'property_id' => $property->id,
        'user_id' => $seller->id,
        'rating' => 1,
        'review_title' => 'Inappropriate content',
        'review_body' => 'Contains profanity and abusive language',
        'status' => 'published',
    ]);

    // 1. Hide review
    $hideRes = $this->actingAs($moderator)->patch(route('moderator.reviews.decision', $review), [
        'status' => 'hidden',
    ]);
    $hideRes->assertRedirect();
    $review->refresh();
    expect($review->status)->toBe('hidden');

    // 2. Permanently delete review
    $deleteRes = $this->actingAs($moderator)->delete(route('moderator.reviews.destroy', $review));
    $deleteRes->assertRedirect();
    expect(PropertyReview::find($review->id))->toBeNull();
});

test('moderator can merge duplicate listings using properties.merge permission', function () {
    $moderator = User::factory()->create(['role' => 'moderator']);
    $seller = User::factory()->create(['role' => 'seller']);

    $primary = Property::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Master Luxury Villa in Bole',
    ]);
    $duplicate = Property::factory()->create([
        'user_id' => $seller->id,
        'title' => 'Luxury Villa in Bole (Duplicate Copy)',
    ]);

    $mergeRes = $this->actingAs($moderator)->post(route('moderator.listings.merge'), [
        'primary_property_id' => $primary->id,
        'duplicate_property_id' => $duplicate->id,
    ]);
    $mergeRes->assertRedirect();

    $duplicate->refresh();
    expect($duplicate->status)->toBe('archived');
    expect((bool) $duplicate->is_active)->toBeFalse();
    expect($duplicate->title)->toContain('[Merged into #'.$primary->id.']');

    expect(AuditLog::where('action', 'property.moderator_merged')->exists())->toBeTrue();
});

test('user without moderator permissions cannot perform moderation actions', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $property = Property::factory()->create();
    $listing = Listing::create([
        'property_id' => $property->id,
        'user_id' => $buyer->id,
        'title' => 'Test Listing',
        'listing_type' => 'sale',
        'price' => 1000000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($buyer)->patch(route('moderator.listings.decision', $listing), [
        'status' => 'approved',
    ]);
    $response->assertStatus(403);
});

// ══════════════════════════════════════════════════════════════════════════════
// 2. VERIFICATION OFFICER ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('verification officer can verify KYC identity: approve, reject, or request clearer photo', function () {
    $officer = User::factory()->create(['role' => 'verification_officer']);
    $targetUser = User::factory()->create([
        'id_verification_status' => 'pending',
    ]);

    // 1. Request clearer photo
    $photoRes = $this->actingAs($officer)->patch(route('verification-officer.identities.decision', $targetUser), [
        'decision' => 'request_photo',
        'rejection_reason' => 'Edges of passport are cut off and unreadable.',
    ]);
    $photoRes->assertRedirect();
    $targetUser->refresh();
    expect($targetUser->id_verification_status)->toBe('pending');
    expect($targetUser->id_rejection_reason)->toContain('Clearer photo requested');

    // 2. Approve identity
    $approveRes = $this->actingAs($officer)->patch(route('verification-officer.identities.decision', $targetUser), [
        'decision' => 'approve',
    ]);
    $approveRes->assertRedirect();
    $targetUser->refresh();
    expect($targetUser->id_verification_status)->toBe('approved');
    expect($targetUser->id_verified_at)->not->toBeNull();

    // 3. Reject identity
    $rejectRes = $this->actingAs($officer)->patch(route('verification-officer.identities.decision', $targetUser), [
        'decision' => 'reject',
        'rejection_reason' => 'Identity card expired in 2021.',
    ]);
    $rejectRes->assertRedirect();
    $targetUser->refresh();
    expect($targetUser->id_verification_status)->toBe('rejected');
    expect($targetUser->id_rejection_reason)->toContain('Identity card expired');
});

test('verification officer can assign trust level 0-5 and verification badges to property', function () {
    $officer = User::factory()->create(['role' => 'verification_officer']);
    $property = Property::factory()->create([
        'verification_level' => 0,
        'verification_status' => 'unverified',
    ]);

    $response = $this->actingAs($officer)->patch(route('verification-officer.properties.trust-level', $property), [
        'verification_level' => 4,
        'verification_status' => 'verified',
        'verified_badges' => ['Title Deed Verified', 'Cadastre Confirmed'],
        'title_deed_number' => 'TD-AA-2026-9912',
        'cadastre_code' => 'CAD-BOLE-04-12',
        'verification_notes' => 'Addis Ababa City Land Management Bureau verified in person.',
    ]);
    $response->assertRedirect();

    $property->refresh();
    expect($property->verification_level)->toBe(4);
    expect($property->verification_status)->toBe('verified');
    expect($property->title_deed_number)->toBe('TD-AA-2026-9912');
    expect($property->verified_badges)->toContain('Title Deed Verified');
    expect($property->verified_by)->toBe($officer->id);
    expect($property->verified_at)->not->toBeNull();
});

test('unauthorized user cannot assign property trust level', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    $property = Property::factory()->create();

    $response = $this->actingAs($tenant)->patch(route('verification-officer.properties.trust-level', $property), [
        'verification_level' => 5,
        'verification_status' => 'verified',
    ]);
    $response->assertStatus(403);
});

// ══════════════════════════════════════════════════════════════════════════════
// 3. FINANCE OFFICER ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('finance officer can issue refund as immutable ledger adjustment without overwriting records', function () {
    $finance = User::factory()->create(['role' => 'finance_officer']);
    $payer = User::factory()->create(['role' => 'buyer']);

    $payment = Payment::create([
        'user_id' => $payer->id,
        'payer_name' => $payer->name,
        'payer_email' => $payer->email,
        'payer_phone' => '+251911223344',
        'payment_method' => 'telebirr',
        'amount' => 10000,
        'currency' => 'ETB',
        'status' => 'completed',
        'payment_type' => 'booking_fee',
    ]);

    // Initial ledger credit entry
    FinancialLedger::create([
        'ledger_code' => 'LED-INIT-100',
        'payment_id' => $payment->id,
        'entry_type' => 'credit',
        'category' => 'booking_fee',
        'amount' => 10000,
        'currency' => 'ETB',
        'description' => 'Initial payment deposit',
    ]);

    // Issue partial refund of 4000 ETB
    $refundRes = $this->actingAs($finance)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 4000,
        'reason' => 'Guest cancelled with 48h notice per platform policy',
    ]);
    $refundRes->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('partially_refunded');
    expect((float) $payment->refunded_amount)->toBe(4000.0);
    expect($payment->refund_reason)->toContain('Guest cancelled');

    // Immutable ledger check: original credit remains untouched, new debit created!
    $adjustment = FinancialLedger::where('payment_id', $payment->id)
        ->where('entry_type', 'debit')
        ->where('category', 'refund_adjustment')
        ->first();

    expect($adjustment)->not->toBeNull();
    expect((float) $adjustment->amount)->toBe(4000.0);
    expect($adjustment->description)->toContain('Refund adjustment');

    expect(AuditLog::where('action', 'payment.refund_issued')->exists())->toBeTrue();
});

test('finance officer can generate tax compliant invoice with 15% VAT and 2% Withholding Tax', function () {
    $finance = User::factory()->create(['role' => 'finance_officer']);
    $client = User::factory()->create(['role' => 'seller']);

    $response = $this->actingAs($finance)->post(route('finance.invoices.store'), [
        'title' => 'Annual Brokerage & Premium Placement Fee',
        'user_id' => $client->id,
        'subtotal' => 50000,
        'due_date' => now()->addDays(30)->toDateString(),
        'notes' => 'Net payable includes 15% VAT minus 2% Ethiopian WHT.',
    ]);
    $response->assertRedirect();

    $invoice = Invoice::where('title', 'Annual Brokerage & Premium Placement Fee')->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->subtotal)->toBe(50000.0);
    expect((float) $invoice->vat_rate)->toBe(15.0);
    expect((float) $invoice->vat_amount)->toBe(7500.0); // 50,000 * 0.15
    expect((float) $invoice->withholding_tax_rate)->toBe(2.0);
    expect((float) $invoice->withholding_tax_amount)->toBe(1000.0); // 50,000 * 0.02
    expect((float) $invoice->total_amount)->toBe(56500.0); // 50,000 + 7,500 - 1,000
    expect($invoice->status)->toBe('unpaid');
});

test('finance officer can disburse payout request and record financial ledger debit', function () {
    $finance = User::factory()->create(['role' => 'finance_officer']);
    $host = User::factory()->create(['role' => 'host']);

    $payout = PayoutRequest::create([
        'payout_code' => 'PAY-REQ-TEST-01',
        'user_id' => $host->id,
        'role' => 'host',
        'gross_amount' => 20000,
        'withholding_tax' => 400, // 2%
        'net_amount' => 19600,
        'payment_method' => 'telebirr',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($finance)->patch(route('finance.payouts.process', $payout), [
        'decision' => 'approve',
    ]);
    $response->assertRedirect();

    $response = $this->actingAs($finance)->patch(route('finance.payouts.process', $payout), [
        'decision' => 'disburse',
        'transaction_reference' => 'TB-DISB-998822',
    ]);
    $response->assertRedirect();

    $payout->refresh();
    expect($payout->status)->toBe('disbursed');
    expect($payout->transaction_reference)->toBe('TB-DISB-998822');
    expect($payout->processed_by)->toBe($finance->id);
    expect($payout->processed_at)->not->toBeNull();

    // Verify the balanced double-entry journal: the gross payout is debited and
    // split into the net cash paid and the withholding tax owed to the authority.
    $ledger = FinancialLedger::where('category', 'payout')
        ->where('entry_type', 'debit')
        ->where('amount', 20000.0)
        ->first();
    expect($ledger)->not->toBeNull();

    $debits = (float) FinancialLedger::where('transaction_group', 'payout:'.$payout->id)->where('entry_type', 'debit')->sum('amount');
    $credits = (float) FinancialLedger::where('transaction_group', 'payout:'.$payout->id)->where('entry_type', 'credit')->sum('amount');
    expect($debits)->toBe($credits)
        ->and($credits)->toBe(20000.0);

    $this->assertDatabaseHas('financial_ledgers', [
        'transaction_group' => 'payout:'.$payout->id,
        'category' => 'tax_withholding',
        'entry_type' => 'credit',
        'amount' => 400.00,
    ]);
});

test('unauthorized user without finance permissions cannot issue refund', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $payment = Payment::create([
        'payer_name' => 'Test Payer',
        'payer_email' => 'payer@example.com',
        'payer_phone' => '+251911223344',
        'payment_method' => 'telebirr',
        'amount' => 5000,
        'currency' => 'ETB',
        'status' => 'completed',
        'payment_type' => 'reservation',
    ]);

    $response = $this->actingAs($buyer)->post(route('finance.payments.refund', $payment), [
        'refund_amount' => 1000,
        'reason' => 'Unauthorized attempt',
    ]);
    $response->assertStatus(403);
});

// ══════════════════════════════════════════════════════════════════════════════
// 4. CUSTOMER SUPPORT ROLE TESTS
// ══════════════════════════════════════════════════════════════════════════════

test('customer support can create, assign, escalate, and close tickets', function () {
    $support = User::factory()->create(['role' => 'customer_support']);
    $agent = User::factory()->create(['role' => 'customer_support']);
    $property = Property::factory()->create();

    // 1. Create ticket
    $createRes = $this->actingAs($support)->post(route('customer-support.tickets.store'), [
        'title' => 'Telebirr SMS callback missing for booking #102',
        'category' => 'payment',
        'priority' => 'medium',
        'description' => 'Customer account was debited 15,000 ETB but booking status shows pending.',
        'property_id' => $property->id,
    ]);
    $createRes->assertRedirect();

    $ticket = MaintenanceTicket::where('title', 'Telebirr SMS callback missing for booking #102')->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->category)->toBe('payment');
    expect($ticket->status)->toBe('open');

    // 2. Assign ticket
    $assignRes = $this->actingAs($support)->post(route('customer-support.tickets.assign', $ticket), [
        'assigned_to' => $agent->id,
    ]);
    $assignRes->assertRedirect();
    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($agent->id);
    expect($ticket->status)->toBe('assigned');

    // 3. Escalate ticket
    $escalateRes = $this->actingAs($support)->post(route('customer-support.tickets.escalate', $ticket), [
        'notes' => 'Customer is at front desk waiting for immediate check-in.',
    ]);
    $escalateRes->assertRedirect();
    $ticket->refresh();
    expect($ticket->priority)->toBe('urgent');
    expect($ticket->location_notes)->toContain('[ESCALATED]');

    // 4. Close ticket
    $closeRes = $this->actingAs($support)->post(route('customer-support.tickets.close', $ticket), [
        'resolution_notes' => 'Telebirr payment manually reconciled and confirmed by finance desk.',
    ]);
    $closeRes->assertRedirect();
    $ticket->refresh();
    expect($ticket->status)->toBe('closed');
    expect($ticket->resolved_at)->not->toBeNull();
    expect($ticket->resolution_notes)->toContain('Telebirr payment manually reconciled');
});

test('customer support can progress dispute through 5-stage pipeline with resolution permission check', function () {
    $support = User::factory()->create(['role' => 'customer_support']);
    $tenant = User::factory()->create(['role' => 'tenant']);
    $landlord = User::factory()->create(['role' => 'landlord']);

    // 1. Create dispute docket (Stage 1: Dispute)
    $createRes = $this->actingAs($support)->post(route('customer-support.disputes.store'), [
        'title' => 'Deposit withholding dispute for Bole Olympia Penthouse',
        'description' => 'Landlord withheld 60,000 ETB claiming wall damages, tenant claims pre-existing wear.',
        'respondent_id' => $landlord->id,
    ]);
    $createRes->assertRedirect();

    $dispute = UserDispute::where('title', 'Deposit withholding dispute for Bole Olympia Penthouse')->first();
    expect($dispute)->not->toBeNull();
    expect($dispute->stage)->toBe('dispute');

    // 2. Stage 2: Evidence Gathering
    $evidenceRes = $this->actingAs($support)->patch(route('customer-support.disputes.stage', $dispute), [
        'stage' => 'evidence',
        'evidence_notes' => 'Tenant submitted move-in inventory inspection photos dated Jan 2026.',
    ]);
    $evidenceRes->assertRedirect();
    $dispute->refresh();
    expect($dispute->stage)->toBe('evidence');
    expect($dispute->evidence_notes)->toContain('Tenant submitted move-in inventory');

    // 3. Stage 3: Review
    $reviewRes = $this->actingAs($support)->patch(route('customer-support.disputes.stage', $dispute), [
        'stage' => 'review',
    ]);
    $reviewRes->assertRedirect();
    $dispute->refresh();
    expect($dispute->stage)->toBe('review');

    // 4. Test unauthorized user cannot resolve dispute without disputes.resolve permission
    $unauthorizedUser = User::factory()->create(['role' => 'cleaner']);
    $unauthRes = $this->actingAs($unauthorizedUser)->patch(route('customer-support.disputes.stage', $dispute), [
        'stage' => 'resolution',
        'resolution_notes' => 'Arbitrary ruling',
    ]);
    $unauthRes->assertStatus(403);

    // 5. Stage 4: Official Resolution (support officer has disputes.resolve seeded)
    $resolveRes = $this->actingAs($support)->patch(route('customer-support.disputes.stage', $dispute), [
        'stage' => 'resolution',
        'resolution_notes' => 'Ruling: Pre-existing wear confirmed by move-in report. Landlord must refund 45,000 ETB.',
    ]);
    $resolveRes->assertRedirect();
    $dispute->refresh();
    expect($dispute->stage)->toBe('resolution');
    expect($dispute->resolved_by)->toBe($support->id);
    expect($dispute->resolved_at)->not->toBeNull();
    expect($dispute->resolution_notes)->toContain('Landlord must refund 45,000 ETB');

    // 6. Stage 5: Close docket
    $closeRes = $this->actingAs($support)->patch(route('customer-support.disputes.stage', $dispute), [
        'stage' => 'closed',
    ]);
    $closeRes->assertRedirect();
    $dispute->refresh();
    expect($dispute->stage)->toBe('closed');
});

test('customer support can assist users with password reset, email verification, and un-warning', function () {
    $support = User::factory()->create(['role' => 'customer_support']);
    $customer = User::factory()->create([
        'role' => 'buyer',
        'email_verified_at' => null,
        'is_warned' => true,
        'status' => 'suspended',
    ]);

    // 1. Verify email manually
    $verifyRes = $this->actingAs($support)->post(route('customer-support.users.assist', $customer), [
        'action' => 'verify_email',
    ]);
    $verifyRes->assertRedirect();
    $customer->refresh();
    expect($customer->email_verified_at)->not->toBeNull();

    // 2. Clear warning flag
    $unwarnRes = $this->actingAs($support)->post(route('customer-support.users.assist', $customer), [
        'action' => 'clear_warning',
    ]);
    $unwarnRes->assertRedirect();
    $customer->refresh();
    expect((bool) $customer->is_warned)->toBeFalse();

    // 3. Toggle account status (suspended -> active)
    $statusRes = $this->actingAs($support)->post(route('customer-support.users.assist', $customer), [
        'action' => 'toggle_status',
    ]);
    $statusRes->assertRedirect();
    $customer->refresh();
    expect($customer->status)->toBe('active');

    // 4. Reset password token generation
    $pwdRes = $this->actingAs($support)->post(route('customer-support.users.assist', $customer), [
        'action' => 'reset_password',
    ]);
    $pwdRes->assertRedirect();

    expect(AuditLog::where('action', 'like', 'support.user_assist_%')->count())->toBeGreaterThanOrEqual(4);
});

// ══════════════════════════════════════════════════════════════════════════════
// 5. ADMINISTRATOR ISOLATION & DYNAMIC PERMISSIONS PROOF
// ══════════════════════════════════════════════════════════════════════════════

test('administrator cannot open or act inside individual client dashboards (Admin boundary returns 403)', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Admin attempts to open individual buyer dashboard -> 403 Forbidden
    $buyerResponse = $this->actingAs($admin)->get(route('buyer.dashboard'));
    $buyerResponse->assertStatus(403);

    // Admin attempts to open individual tenant dashboard -> 403 Forbidden
    $tenantResponse = $this->actingAs($admin)->get(route('tenant.dashboard'));
    $tenantResponse->assertStatus(403);

    // Admin attempts to open individual guest dashboard -> 403 Forbidden
    $guestResponse = $this->actingAs($admin)->get(route('guest.dashboard'));
    $guestResponse->assertStatus(403);
});

test('administrator can configure permissions per role and per user dynamically with audit logging', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $targetUser = User::factory()->create(['role' => 'buyer']);

    $viewingsCreatePerm = Permission::where('key', 'viewings.create')->first();
    $propertiesBrowsePerm = Permission::where('key', 'properties.browse')->first();

    // 1. Update Role Permissions (Dynamic grant/revoke per role)
    $roleRes = $this->actingAs($admin)->post(route('dashboard.roles.permissions.update'), [
        'persona' => 'buyer',
        'permission_ids' => [$propertiesBrowsePerm->id], // Revoke viewings.create, keep only browse
        'reason' => 'Restricting default buyer permissions for security audit',
    ]);
    $roleRes->assertRedirect();

    $buyerHasViewings = RolePermission::where('role', 'buyer')
        ->where('permission_id', $viewingsCreatePerm->id)
        ->exists();
    expect($buyerHasViewings)->toBeFalse();

    // Verify audit log for role permission modification
    expect(AuditLog::where('action', 'role.permissions_updated')->exists())->toBeTrue();

    // 2. Update User Permissions (Dynamic grant/revoke per user override)
    $userRes = $this->actingAs($admin)->post(route('dashboard.users.permissions.update', $targetUser), [
        'permission_mode' => 'custom',
        'granted_permissions' => [$viewingsCreatePerm->id], // Explicitly grant back to this individual user
        'reason' => 'VIP buyer grant',
    ]);
    $userRes->assertRedirect();

    $targetUser->refresh();
    expect($targetUser->hasPermission('viewings.create'))->toBeTrue();

    // Verify audit log for user permission assignment
    expect(AuditLog::where('action', 'user.permissions_assigned')->exists())->toBeTrue();
});
