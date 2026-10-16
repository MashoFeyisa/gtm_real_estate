<?php

use App\Models\ConstructionMilestone;
use App\Models\DeveloperEscrowDisbursement;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * P0 security: developer milestone escrow.
 *
 * SRS §38/§137 used to let any signed-in user request or approve a disbursement.
 * The DeveloperEscrowDisbursementPolicy now restricts requesting to the project's
 * own developer, and approval to finance/admin (never the requester).
 */
function escrowProject(array $attributes = []): Project
{
    return Project::create(array_merge([
        'project_code' => 'DEV-ESC-'.uniqid(),
        'name' => 'Escrow Authorisation Project',
        'slug' => 'escrow-'.strtolower(Str::random(8)),
        'developer_name' => 'Sunshine Construction',
        'city' => 'Addis Ababa',
        'subcity' => 'Kirkos',
        'project_type' => 'residential',
        'construction_status' => 'under_construction',
        'status' => 'published',
    ], $attributes));
}

function escrowMilestone(Project $project): ConstructionMilestone
{
    return ConstructionMilestone::create([
        'project_id' => $project->id,
        'milestone_name' => 'Foundation Pouring',
        'percentage' => 100,
        'target_date' => '2026-04-01',
        'status' => 'completed',
        'order_index' => 1,
    ]);
}

function escrowUser(string $role, string $email): User
{
    return User::create([
        'name' => ucfirst($role).' Test',
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => $role,
    ]);
}

test("the project's own developer can request escrow disbursement", function () {
    $developer = escrowUser('developer', 'own.developer@example.com');
    $project = escrowProject(['developer_user_id' => $developer->id]);
    $milestone = escrowMilestone($project);

    $response = $this->actingAs($developer)->post(route('developer.escrow.request', $project), [
        'milestone_id' => $milestone->id,
        'amount_etb' => 1800000.00,
        'bank_reference' => 'CBE-ESC-1',
        'inspection_notes' => 'City engineer signed off.',
    ]);

    $response->assertRedirect();

    $disbursement = DeveloperEscrowDisbursement::where('project_id', $project->id)->firstOrFail();
    expect($disbursement->status)->toBe('pending')
        ->and($disbursement->requested_by)->toBe($developer->id);
});

test("another developer cannot request another project's escrow disbursement", function () {
    $owner = escrowUser('developer', 'project.owner@example.com');
    $stranger = escrowUser('developer', 'stranger.developer@example.com');
    $project = escrowProject(['developer_user_id' => $owner->id]);
    $milestone = escrowMilestone($project);

    $response = $this->actingAs($stranger)->post(route('developer.escrow.request', $project), [
        'milestone_id' => $milestone->id,
        'amount_etb' => 1800000.00,
    ]);

    $response->assertForbidden();
    expect(DeveloperEscrowDisbursement::where('project_id', $project->id)->count())->toBe(0);
});

test('a buyer cannot request an escrow disbursement', function () {
    $owner = escrowUser('developer', 'another.owner@example.com');
    $buyer = escrowUser('buyer', 'nosy.buyer@example.com');
    $project = escrowProject(['developer_user_id' => $owner->id]);
    $milestone = escrowMilestone($project);

    $this->actingAs($buyer)->post(route('developer.escrow.request', $project), [
        'milestone_id' => $milestone->id,
        'amount_etb' => 5000.00,
    ])->assertForbidden();

    expect(DeveloperEscrowDisbursement::count())->toBe(0);
});

test('finance can approve a pending escrow request', function () {
    $developer = escrowUser('developer', 'approver.dev@example.com');
    $project = escrowProject(['developer_user_id' => $developer->id]);
    $milestone = escrowMilestone($project);
    $disbursement = DeveloperEscrowDisbursement::create([
        'project_id' => $project->id,
        'milestone_id' => $milestone->id,
        'requested_by' => $developer->id,
        'amount_etb' => 900000.00,
        'status' => 'pending',
    ]);

    $finance = escrowUser('finance_officer', 'escrow.finance@example.com');

    $this->actingAs($finance)->post(route('developer.escrow.approve', $disbursement))
        ->assertRedirect();

    $disbursement->refresh();
    expect($disbursement->status)->toBe('disbursed')
        ->and($disbursement->verified_by)->toBe($finance->id);
});

test('a non-finance user cannot approve an escrow request', function () {
    $developer = escrowUser('developer', 'notfinance.dev@example.com');
    $project = escrowProject(['developer_user_id' => $developer->id]);
    $milestone = escrowMilestone($project);
    $disbursement = DeveloperEscrowDisbursement::create([
        'project_id' => $project->id,
        'milestone_id' => $milestone->id,
        'requested_by' => $developer->id,
        'amount_etb' => 900000.00,
        'status' => 'pending',
    ]);

    $this->actingAs($developer)->post(route('developer.escrow.approve', $disbursement))
        ->assertForbidden();

    $this->actingAs(escrowUser('buyer', 'approve.buyer@example.com'))
        ->post(route('developer.escrow.approve', $disbursement))
        ->assertForbidden();

    expect($disbursement->fresh()->status)->toBe('pending');
});

test('the requester of an escrow request cannot approve their own request even if finance staff', function () {
    $developer = escrowUser('developer', 'selfapprove.dev@example.com');
    $project = escrowProject(['developer_user_id' => $developer->id]);
    $milestone = escrowMilestone($project);
    $disbursement = DeveloperEscrowDisbursement::create([
        'project_id' => $project->id,
        'milestone_id' => $milestone->id,
        'requested_by' => $developer->id,
        'amount_etb' => 900000.00,
        'status' => 'pending',
    ]);

    $this->actingAs($developer)->post(route('developer.escrow.approve', $disbursement))
        ->assertForbidden();

    expect($disbursement->fresh()->status)->toBe('pending');
});

test('an already disbursed escrow request cannot be approved twice', function () {
    $developer = escrowUser('developer', 'twice.dev@example.com');
    $project = escrowProject(['developer_user_id' => $developer->id]);
    $milestone = escrowMilestone($project);
    $disbursement = DeveloperEscrowDisbursement::create([
        'project_id' => $project->id,
        'milestone_id' => $milestone->id,
        'requested_by' => $developer->id,
        'amount_etb' => 900000.00,
        'status' => 'disbursed',
        'disbursed_at' => now(),
    ]);

    $this->actingAs(escrowUser('finance_officer', 'twice.finance@example.com'))
        ->post(route('developer.escrow.approve', $disbursement))
        ->assertStatus(422);

    expect($disbursement->fresh()->status)->toBe('disbursed');
});
