<?php

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\User;
use App\Notifications\ClientReportSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'System Admin',
        'email' => 'admin@realestate.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->client = User::create([
        'name' => 'Abebe Client',
        'email' => 'abebe@example.com',
        'password' => Hash::make('password123'),
        'role' => 'client',
    ]);

    $this->agentUser = User::create([
        'name' => 'Agent Broker',
        'email' => 'broker@example.com',
        'password' => Hash::make('password123'),
        'role' => 'agent',
    ]);

    $this->agent = Agent::create([
        'user_id' => $this->agentUser->id,
        'name' => 'Addis Realty',
        'email' => 'addis@realty.com',
        'phone' => '+251911000000',
        'is_active' => true,
    ]);

    $this->property = Property::create([
        'property_code' => 'ETH-TEST-001',
        'title' => 'Test Luxury Villa',
        'slug' => 'test-luxury-villa',
        'price' => 12000000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 350,
        'type' => 'sale',
        'property_category' => 'house',
        'status' => 'published',
        'is_active' => true,
        'city' => 'Addis Ababa',
        'address' => 'Bole Subcity',
        'agent_id' => $this->agent->id,
    ]);
});

test('client can submit direct report to admin and admin receives database notification', function () {
    Notification::fake();

    $response = $this->actingAs($this->client)->post(route('reports.store-direct'), [
        'subject' => 'Unprofessional Agent conduct during tour',
        'reason' => 'agent_misconduct',
        'description' => 'The broker demanded an extra undisclosed cash deposit before showing the house.',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('property_reports', [
        'user_id' => $this->client->id,
        'property_id' => null,
        'subject' => 'Unprofessional Agent conduct during tour',
        'reason' => 'agent_misconduct',
        'status' => 'pending',
    ]);

    Notification::assertSentTo(
        [$this->admin],
        ClientReportSubmittedNotification::class,
        function ($notification) {
            $data = $notification->toArray($this->admin);

            return str_contains($data['title'], 'Client Report')
                && str_contains($data['message'], 'Unprofessional Agent conduct');
        }
    );
});

test('client can submit property listing report and admin is notified', function () {
    Notification::fake();

    $response = $this->actingAs($this->client)->post(route('properties.report', $this->property), [
        'reason' => 'fake_property',
        'description' => 'This property pictures belong to another private owner and price is fake.',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('property_reports', [
        'user_id' => $this->client->id,
        'property_id' => $this->property->id,
        'reason' => 'fake_property',
        'status' => 'pending',
    ]);

    Notification::assertSentTo(
        [$this->admin],
        ClientReportSubmittedNotification::class
    );
});

test('admin dashboard renders reports desk and notification badge', function () {
    // Create a direct report
    $report = PropertyReport::create([
        'user_id' => $this->client->id,
        'property_id' => null,
        'subject' => 'Fraudulent broker payment request',
        'reason' => 'agent_misconduct',
        'description' => 'Agent requested off-platform wire transfer.',
        'status' => 'pending',
    ]);

    // Send notification to admin
    $this->admin->notify(new ClientReportSubmittedNotification($report));

    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Client Reports & Incidents');
    $response->assertSee('Fraudulent broker payment request');
    // Notification dropdown contains report title and number
    $response->assertSee('Client Safety Reports');
    $response->assertSee($report->report_number);
});

test('admin can update report status and add resolution notes', function () {
    $report = PropertyReport::create([
        'user_id' => $this->client->id,
        'property_id' => $this->property->id,
        'reason' => 'wrong_price',
        'description' => 'Price is listed as 12M but agent verbally said 15M.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->patch(route('reports.update', $report), [
        'status' => 'resolved',
        'resolution_notes' => 'Contacted broker and verified price is accurate 12M on contract.',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('property_reports', [
        'id' => $report->id,
        'status' => 'resolved',
        'resolution_notes' => 'Contacted broker and verified price is accurate 12M on contract.',
    ]);
});

test('admin can suspend a reported property while updating report', function () {
    $report = PropertyReport::create([
        'user_id' => $this->client->id,
        'property_id' => $this->property->id,
        'reason' => 'fake_property',
        'description' => 'Fake title deed verified by Land Administration.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->patch(route('reports.update', $report), [
        'status' => 'under_investigation',
        'resolution_notes' => 'Suspended listing pending title verification with Addis Land Bureau.',
        'suspend_property' => 1,
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('property_reports', [
        'id' => $report->id,
        'status' => 'under_investigation',
    ]);

    $this->property->refresh();
    expect($this->property->is_active)->toBeFalse();
    expect($this->property->status)->toBe('archived');
});

test('admin can delete a report', function () {
    $report = PropertyReport::create([
        'user_id' => $this->client->id,
        'property_id' => null,
        'subject' => 'Spam test ticket',
        'reason' => 'service_complaint',
        'description' => 'Test spam complaint.',
        'status' => 'dismissed',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('reports.destroy', $report));

    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('property_reports', [
        'id' => $report->id,
    ]);
});

test('non-admin cannot update or delete reports', function () {
    $report = PropertyReport::create([
        'user_id' => $this->client->id,
        'property_id' => null,
        'reason' => 'service_complaint',
        'description' => 'Complaint details.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->client)->patch(route('reports.update', $report), [
        'status' => 'resolved',
    ]);
    $response->assertForbidden();

    $deleteResponse = $this->actingAs($this->client)->delete(route('reports.destroy', $report));
    $deleteResponse->assertForbidden();
});

test('client submitting report notifies company users and appears on company dashboard', function () {
    Notification::fake();

    $companyUser = User::create([
        'name' => 'Seyid Executive',
        'email' => 'seyid.exec@gtmrealestate.com',
        'password' => Hash::make('password123'),
        'role' => 'company',
    ]);

    $this->actingAs($this->client)->post(route('reports.store-direct'), [
        'subject' => 'Fraudulent listing claim',
        'reason' => 'scam',
        'description' => 'The listed apartment has duplicate claims from another agency.',
    ])->assertSessionHas('success');

    Notification::assertSentTo([$companyUser], ClientReportSubmittedNotification::class);

    $response = $this->actingAs($companyUser)->get(route('company.dashboard'));
    $response->assertOk()
        ->assertDontSee('Direct Client Incident Alert')
        ->assertSee('Fraudulent listing claim')
        ->assertSee('1 Issues');
});
