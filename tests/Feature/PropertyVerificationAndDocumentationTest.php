<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->adminUser = User::create([
        'name' => 'Admin Verifier',
        'email' => 'admin.verify@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Chala',
        'email' => 'agent.chala@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Chala Tadesse',
        'email' => 'chala@example.com',
        'phone' => '+251 91 199 8877',
        'is_active' => true,
    ]);

    $this->otherUser = User::create([
        'name' => 'Random User',
        'email' => 'random@example.com',
        'password' => Hash::make('password123'),
        'role' => 'buyer',
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-AA-BOLE-00000099',
        'title' => 'Bole Atlas High-End Penthouse',
        'slug' => 'bole-atlas-high-end-penthouse',
        'price' => 25000000.00,
        'bedrooms' => 3,
        'bathrooms' => 3,
        'area' => 240,
        'building_area' => 240,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Atlas Hotel Area, Addis Ababa',
        'agent_id' => $this->agent->id,
        'verification_level' => 1,
        'verification_status' => 'unverified',
    ]);
});

test('agent can upload title deed carta for their property and auto-advance to level 3', function () {
    $file = UploadedFile::fake()->create('title_deed_carta.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->agentUser)->post(route('properties.documents.upload', $this->property), [
        'document_type' => 'carta',
        'title' => 'Addis Ababa Bole Title Deed Carta',
        'document_number' => 'CARTA-AA-2026-9901',
        'access_level' => 'confidential',
        'notes' => 'Authentic land title deed issued by Addis Ababa Land Administration.',
        'document_file' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('property_documents', [
        'property_id' => $this->property->id,
        'document_type' => 'title_deed_carta',
        'document_number' => 'CARTA-AA-2026-9901',
        'access_level' => 'confidential',
        'status' => 'pending',
    ]);

    $this->property->refresh();
    expect($this->property->verification_level)->toBe(3);
    expect($this->property->verification_status)->toBe('pending');
});

test('unauthorized buyer cannot download confidential property document', function () {
    $file = UploadedFile::fake()->create('confidential_plan.pdf', 300, 'application/pdf');
    $path = $file->store('property_documents/'.$this->property->id, 'public');

    $doc = PropertyDocument::create([
        'property_id' => $this->property->id,
        'uploaded_by' => $this->agentUser->id,
        'document_type' => 'site_plan',
        'title' => 'Cadastral Mapa Blueprint',
        'document_number' => 'MAPA-7788',
        'file_path' => $path,
        'file_name' => 'confidential_plan.pdf',
        'file_size' => 300 * 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'confidential',
        'status' => 'verified',
    ]);

    // Unauthenticated access
    $guestResponse = $this->get(route('properties.documents.download', [$this->property, $doc]));
    $guestResponse->assertRedirect(route('login'));

    // Regular buyer without offer or viewing
    $buyerResponse = $this->actingAs($this->otherUser)->get(route('properties.documents.download', [$this->property, $doc]));
    $buyerResponse->assertStatus(403);
});

test('agent owner and admin can download confidential documents', function () {
    $file = UploadedFile::fake()->create('title_carta.pdf', 400, 'application/pdf');
    $path = $file->store('property_documents/'.$this->property->id, 'public');

    $doc = PropertyDocument::create([
        'property_id' => $this->property->id,
        'uploaded_by' => $this->agentUser->id,
        'document_type' => 'carta',
        'title' => 'Official Carta',
        'file_path' => $path,
        'file_name' => 'title_carta.pdf',
        'file_size' => 400 * 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'confidential',
        'status' => 'pending',
    ]);

    // Listing agent download
    $agentDownload = $this->actingAs($this->agentUser)->get(route('properties.documents.download', [$this->property, $doc]));
    $agentDownload->assertOk();

    // Admin download
    $adminDownload = $this->actingAs($this->adminUser)->get(route('properties.documents.download', [$this->property, $doc]));
    $adminDownload->assertOk();
});

test('admin can update property verification level and award badges', function () {
    $response = $this->actingAs($this->adminUser)->put(route('properties.verification.update', $this->property), [
        'verification_level' => 5,
        'verification_status' => 'verified',
        'title_deed_number' => 'CARTA-ETH-AA-999',
        'cadastre_code' => 'CAD-BOLE-03-888',
        'badges' => ['carta_verified', 'site_plan_approved', 'tax_clearance_verified'],
        'verification_notes' => 'Municipal title registry verified at Bole Subcity Cadastre Desk.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->property->refresh();
    expect($this->property->verification_level)->toBe(5);
    expect($this->property->verification_status)->toBe('verified');
    expect($this->property->title_deed_number)->toBe('CARTA-ETH-AA-999');
    expect($this->property->cadastre_code)->toBe('CAD-BOLE-03-888');
    expect($this->property->hasVerificationBadge('carta_verified'))->toBeTrue();
    expect($this->property->hasVerificationBadge('site_plan_approved'))->toBeTrue();
    expect($this->property->hasVerificationBadge('tax_clearance_verified'))->toBeTrue();
    expect($this->property->hasVerificationBadge('ownership_attested'))->toBeFalse();
    expect($this->property->verified_by)->toBe($this->adminUser->id);
    expect($this->property->verified_at)->not->toBeNull();
});

test('admin can approve or reject property documents', function () {
    $file = UploadedFile::fake()->create('tax_clearance.pdf', 200, 'application/pdf');
    $path = $file->store('property_documents/'.$this->property->id, 'public');

    $doc = PropertyDocument::create([
        'property_id' => $this->property->id,
        'uploaded_by' => $this->agentUser->id,
        'document_type' => 'tax_clearance',
        'title' => 'City Revenue Tax Clearance',
        'file_path' => $path,
        'file_name' => 'tax_clearance.pdf',
        'file_size' => 200 * 1024,
        'mime_type' => 'application/pdf',
        'access_level' => 'verification_only',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->patch(route('properties.documents.update-status', [$this->property, $doc]), [
        'status' => 'verified',
        'rejection_reason' => null,
    ]);

    $response->assertRedirect();
    $doc->refresh();
    expect($doc->status)->toBe('verified');
    expect($doc->verified_by)->toBe($this->adminUser->id);
    expect($doc->verified_at)->not->toBeNull();
});

test('property show page displays verification shield and trust details', function () {
    $this->property->update([
        'verification_level' => 5,
        'verification_status' => 'verified',
        'title_deed_number' => 'CARTA-BOLE-2026-X1',
        'verified_badges' => ['carta_verified', 'tax_clearance_verified'],
    ]);

    $response = $this->get(route('properties.show', $this->property->slug));

    $response->assertOk();
    $response->assertSee('Legal Verification &amp; Documentation Shield', false);
    $response->assertSee('Transaction Ready');
    $response->assertSee('5/5 Completed');
    $response->assertSee('CARTA-BOLE-2026-X1');
    $response->assertSee('Title Deed Verified');
    $response->assertSee('ካርታ የተረጋገጠ');
});
