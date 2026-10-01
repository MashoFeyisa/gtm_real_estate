<?php

use App\Models\Agent;
use App\Models\BlogPost;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function createPortalAgent(string $email): array
{
    $agent = Agent::create([
        'name' => 'Portal Agent',
        'email' => $email,
        'phone' => '+251900111222',
        'bio' => 'Portal agent',
    ]);

    $account = User::factory()->create([
        'name' => 'Portal Agent',
        'email' => $email,
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    return [$agent, $account];
}

function portalListingData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Agent Posted Villa',
        'description' => 'A villa posted by an agent from the portal.',
        'price' => 250000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 210,
        'type' => 'sale',
        'category' => 'villa',
        'status' => 'published',
        'city' => 'Harar',
        'address' => 'Kezira, Harar',
    ], $overrides);
}

test('agent can post a property listing from the portal', function () {
    Storage::fake('public');

    [, $account] = createPortalAgent('listing.agent@example.com');

    $this->actingAs($account)
        ->post('/agent-portal/properties', portalListingData([
            'image' => UploadedFile::fake()->image('villa.jpg'),
        ]))
        ->assertRedirect();

    $property = Property::query()->where('title', 'Agent Posted Villa')->first();

    expect($property)->not->toBeNull();
    expect($property->agent_id)->not->toBeNull();
    expect($property->status)->toBe('published');
    expect($property->is_active)->toBeTrue();
    expect($property->image_path)->not->toBeNull();
    expect($property->featured)->toBeFalse();
});

test('agent can save a listing as an unpublished draft', function () {
    [, $account] = createPortalAgent('draft.agent@example.com');

    $this->actingAs($account)
        ->post('/agent-portal/properties', portalListingData(['status' => 'draft']))
        ->assertRedirect();

    $property = Property::query()->where('title', 'Agent Posted Villa')->first();

    expect($property->status)->toBe('draft');
    expect($property->is_active)->toBeFalse();
});

test('agent can edit their own listing', function () {
    [$agent, $account] = createPortalAgent('edit.agent@example.com');

    $property = Property::create([
        'title' => 'Editable Listing',
        'slug' => 'editable-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->actingAs($account)
        ->put('/agent-portal/properties/'.$property->id, portalListingData([
            'title' => 'Renovated Listing',
            'price' => 180000,
        ]))
        ->assertRedirect();

    $property->refresh();

    expect($property->title)->toBe('Renovated Listing');
    expect((float) $property->price)->toBe(180000.0);
});

test('agent can publish and unpublish their own listing', function () {
    [$agent, $account] = createPortalAgent('publish.agent@example.com');

    $property = Property::create([
        'title' => 'Publish Toggle Listing',
        'slug' => 'publish-toggle-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->actingAs($account)
        ->post('/agent-portal/properties/'.$property->id.'/toggle-publish')
        ->assertRedirect();

    $property->refresh();
    expect($property->status)->toBe('draft');
    expect($property->is_active)->toBeFalse();

    $this->actingAs($account)
        ->post('/agent-portal/properties/'.$property->id.'/toggle-publish')
        ->assertRedirect();

    $property->refresh();
    expect($property->status)->toBe('published');
    expect($property->is_active)->toBeTrue();
});

test('agent marks sale listings as sold and rent listings as rented', function (string $type, string $soldStatus) {
    [$agent, $account] = createPortalAgent('sold.agent@example.com');

    $property = Property::create([
        'title' => 'Sold Status Listing',
        'slug' => 'sold-status-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => $type,
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->actingAs($account)
        ->post('/agent-portal/properties/'.$property->id.'/toggle-sold')
        ->assertRedirect();

    $property->refresh();
    expect($property->status)->toBe($soldStatus);
    expect($property->is_active)->toBeTrue();

    $this->actingAs($account)
        ->post('/agent-portal/properties/'.$property->id.'/toggle-sold')
        ->assertRedirect();

    $property->refresh();
    expect($property->status)->toBe('available');
})->with([
    ['sale', 'sold'],
    ['rent', 'rented'],
]);

test('agent can archive and delete their own listing', function () {
    [$agent, $account] = createPortalAgent('archive.agent@example.com');

    $property = Property::create([
        'title' => 'Archive Listing',
        'slug' => 'archive-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->actingAs($account)
        ->post('/agent-portal/properties/'.$property->id.'/archive')
        ->assertRedirect();

    $property->refresh();
    expect($property->status)->toBe('archived');
    expect($property->is_active)->toBeFalse();

    $this->actingAs($account)
        ->delete('/agent-portal/properties/'.$property->id)
        ->assertRedirect();

    expect(Property::query()->whereKey($property->id)->exists())->toBeFalse();
});

test('agent cannot manage listings owned by another agent', function (string $action, string $method, array $payload) {
    [$ownerAgent] = createPortalAgent('owner.agent@example.com');
    [$intruderAgent, $intruderAccount] = createPortalAgent('intruder.agent@example.com');

    $property = Property::create([
        'title' => 'Protected Listing',
        'slug' => 'protected-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $ownerAgent->id,
    ]);

    $url = match ($action) {
        'update' => '/agent-portal/properties/'.$property->id,
        'destroy' => '/agent-portal/properties/'.$property->id,
        default => '/agent-portal/properties/'.$property->id.'/'.$action,
    };

    $response = $this->actingAs($intruderAccount)->call($method, $url, $payload);

    expect($response->getStatusCode())->toBe(403);

    $property->refresh();
    expect($property->status)->toBe('published');
    expect($property->agent_id)->toBe($ownerAgent->id);
})->with([
    ['toggle-publish', 'POST', []],
    ['toggle-sold', 'POST', []],
    ['archive', 'POST', []],
    ['destroy', 'DELETE', []],
    ['update', 'PUT', [
        'title' => 'Hijacked',
        'price' => 1,
        'bedrooms' => 0,
        'bathrooms' => 0,
        'area' => 0,
        'type' => 'sale',
        'status' => 'published',
    ]],
]);

test('agent portal shows the add-property button and the full listing list', function () {
    [$agent, $account] = createPortalAgent('listings.ui.agent@example.com');

    Property::create([
        'title' => 'Card Display Villa',
        'slug' => 'card-display-villa',
        'price' => 123000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->actingAs($account)
        ->get('/agent-portal');

    $response->assertOk()
        ->assertSee('+ Add Property')
        ->assertSee('Card Display Villa')
        ->assertSee('ETB 123,000')
        ->assertSee('Unpublish')
        ->assertSee('Mark Sold')
        ->assertSee('Archive')
        ->assertSee('Delete');

    $html = $response->getContent();
    $createPanelPos = strpos($html, 'id="agent-listing-create-panel"');
    $createPanelEnd = strpos($html, '</form>', $createPanelPos);
    $tablePos = strpos($html, '<table', $createPanelEnd);
    expect($tablePos)->toBeGreaterThan($createPanelEnd);
});

test('agent can load edit form in portal with pre-filled values and save changes', function () {
    [$agent, $account] = createPortalAgent('edit.ui.agent@example.com');

    $property = Property::create([
        'title' => 'Initial Luxury Villa',
        'slug' => 'initial-luxury-villa',
        'price' => 350000,
        'bedrooms' => 5,
        'bathrooms' => 4,
        'area' => 320,
        'type' => 'sale',
        'property_category' => 'villa',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->actingAs($account)
        ->get('/agent-portal?section=listings&edit_property='.$property->id);

    $response->assertOk()
        ->assertSee('Edit: Initial Luxury Villa')
        ->assertSee('value="Initial Luxury Villa"', false)
        ->assertSee('value="350000"', false)
        ->assertSee('Save Changes');

    $this->actingAs($account)
        ->put('/agent-portal/properties/'.$property->id, portalListingData([
            'title' => 'Updated Luxury Villa',
            'price' => 390000,
            'category' => 'villa',
            'status' => 'archived',
        ]))
        ->assertRedirect(route('agent.portal', ['section' => 'listings']))
        ->assertSessionHas('success');

    $property->refresh();
    expect($property->title)->toBe('Updated Luxury Villa');
    expect((float) $property->price)->toBe(390000.0);
    expect($property->status)->toBe('archived');
    expect($property->is_active)->toBeFalse();
});

test('agent sees only their own listings in the portal', function () {
    [$agentA, $accountA] = createPortalAgent('agent.a@example.com');
    [$agentB] = createPortalAgent('agent.b@example.com');

    Property::create([
        'title' => 'Agent A House',
        'slug' => 'agent-a-house',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agentA->id,
    ]);

    Property::create([
        'title' => 'Agent B Secret Villa',
        'slug' => 'agent-b-secret-villa',
        'price' => 200000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 140,
        'type' => 'sale',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agentB->id,
    ]);

    $this->actingAs($accountA)
        ->get('/agent-portal')
        ->assertOk()
        ->assertSee('Agent A House')
        ->assertDontSee('Agent B Secret Villa');
});

test('published agent listings appear on the landing and home pages', function () {
    [$agent] = createPortalAgent('visible.agent@example.com');

    Property::create([
        'title' => 'Sunset View Condo',
        'slug' => 'sunset-view-condo',
        'price' => 95000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 88,
        'type' => 'sale',
        'property_category' => 'apartment',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    foreach (['/', '/app'] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('Sunset View Condo');
    }

    $this->get('/properties/apartments')
        ->assertOk()
        ->assertSee('Sunset View Condo');

    $this->get('/properties/sunset-view-condo')
        ->assertOk()
        ->assertSee('Sunset View Condo');
});

test('agent marks a client message as read when opening it', function () {
    [$agent, $account] = createPortalAgent('reader.agent@example.com');

    $inquiry = Inquiry::create([
        'name' => 'Curious Client',
        'email' => 'curious.client@example.com',
        'subject' => 'Viewing request',
        'message' => 'Can I visit the property this weekend?',
        'status' => 'new',
        'agent_id' => $agent->id,
    ]);

    $this->actingAs($account)
        ->post('/agent-portal/inquiries/'.$inquiry->id.'/read')
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect($inquiry->fresh()->read_at)->not->toBeNull();
});

test('agent cannot mark another agent message as read', function () {
    [$otherAgent] = createPortalAgent('other.inbox.agent@example.com');
    [, $account] = createPortalAgent('sneaky.agent@example.com');

    $inquiry = Inquiry::create([
        'name' => 'Private Client',
        'email' => 'private.client@example.com',
        'subject' => 'Private message',
        'message' => 'This message is not for you.',
        'status' => 'new',
        'agent_id' => $otherAgent->id,
    ]);

    $response = $this->actingAs($account)
        ->post('/agent-portal/inquiries/'.$inquiry->id.'/read');

    expect($response->getStatusCode())->toBe(403);
    expect($inquiry->fresh()->read_at)->toBeNull();
});

test('unpublished and sold listings never appear on the public site', function () {
    [$agent] = createPortalAgent('hidden.agent@example.com');

    Property::create([
        'title' => 'Hidden Draft Listing',
        'slug' => 'hidden-draft-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'draft',
        'is_active' => false,
        'agent_id' => $agent->id,
    ]);

    Property::create([
        'title' => 'Hidden Sold Listing',
        'slug' => 'hidden-sold-listing',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 90,
        'type' => 'sale',
        'status' => 'sold',
        'is_active' => false,
        'agent_id' => $agent->id,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Hidden Draft Listing')
        ->assertDontSee('Hidden Sold Listing');

    $this->get('/properties/hidden-draft-listing')->assertNotFound();
    $this->get('/properties/hidden-sold-listing')->assertNotFound();
});

test('admin can toggle agent active status permission', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$agent] = createPortalAgent('toggle.agent@example.com');

    expect($agent->is_active)->toBeTrue();

    // Toggle to inactive
    $this->actingAs($admin)
        ->post(route('dashboard.agents.toggleActive', $agent))
        ->assertRedirect(route('dashboard', ['section' => 'agents']));

    expect($agent->fresh()->is_active)->toBeFalse();

    // Toggle back to active
    $this->actingAs($admin)
        ->post(route('dashboard.agents.toggleActive', $agent))
        ->assertRedirect(route('dashboard', ['section' => 'agents']));

    expect($agent->fresh()->is_active)->toBeTrue();
});

test('inactive agent cannot access agent portal and cannot log in', function () {
    [$agent, $account] = createPortalAgent('inactive.agent@example.com');
    $agent->update(['is_active' => false]);

    // Portal access blocked
    $this->actingAs($account)
        ->get('/agent-portal')
        ->assertForbidden();

    // Login blocked
    $this->post('/login', [
        'email' => 'inactive.agent@example.com',
        'password' => 'agentpass1',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('inactive agent does not appear in public directory and returns 404 on profile', function () {
    [$agent] = createPortalAgent('secret.agent@example.com');
    $agent->update(['is_active' => false]);

    $this->get(route('agents'))
        ->assertOk()
        ->assertDontSee('secret.agent@example.com');

    $this->get(route('agents.show', $agent))
        ->assertNotFound();
});

test('property show page has removed request call back button and renders formal request', function () {
    [$agent] = createPortalAgent('view.agent@example.com');
    $property = Property::create([
        'title' => 'Sample Bole Residence',
        'slug' => 'sample-bole-residence',
        'price' => 12000000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'type' => 'sale',
        'category' => 'apartment',
        'status' => 'available',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $response = $this->get('/properties/'.$property->slug);
    $response->assertOk();
    $response->assertDontSee('Request Call Back');
    $response->assertSee('Send Formal Buy Request');
});

test('login page does not display apply job link', function () {
    $author = User::factory()->create();

    BlogPost::create([
        'user_id' => $author->id,
        'title' => 'siner engener',
        'slug' => 'siner-engener',
        'content' => 'this job is published by ethiotelecom',
        'type' => 'job',
        'status' => 'published',
        'published_at' => now(),
        'apply_link' => 'https://gtm.et/jobs/apply',
    ]);

    $response = $this->get('/login');
    $response->assertOk();
    $response->assertDontSee('Now Hiring');
    $response->assertDontSee('siner engener');
    $response->assertDontSee('Apply Now');
    $response->assertDontSee('Looking for job opportunities?');
});

test('job page and home page display apply link with job list', function () {
    $author = User::factory()->create(['name' => 'Admin User']);

    BlogPost::create([
        'user_id' => $author->id,
        'author_name' => 'Admin User',
        'title' => 'siner engener',
        'slug' => 'siner-engener-test',
        'content' => 'this job is published by ethiotelecom',
        'type' => 'job',
        'status' => 'published',
        'published_at' => Carbon::parse('2026-09-29 14:00:00'),
        'category' => 'natural science',
        'apply_link' => 'https://ethiojobs.et/apply/siner-engener',
    ]);

    $response = $this->get(route('careers'));
    $response->assertOk();
    $response->assertSee('natural science');
    $response->assertSee('siner engener');
    $response->assertSee('this job is published by ethiotelecom');
    $response->assertSee('By Admin User');
    $response->assertSee('Sep 29, 2026');
    $response->assertSee('Apply Link');
    $response->assertSee('https://ethiojobs.et/apply/siner-engener');
    $response->assertSee('Apply Now');

    // Also accessible via /jobs and /job
    $this->get('/jobs')->assertOk()->assertSee('https://ethiojobs.et/apply/siner-engener');
    $this->get('/job')->assertOk()->assertSee('https://ethiojobs.et/apply/siner-engener');

    // Also visible with job in home page posts list
    $homeResponse = $this->get('/');
    $homeResponse->assertOk();
    $homeResponse->assertSee('siner engener');
    $homeResponse->assertSee('Apply Link:');
    $homeResponse->assertSee('https://ethiojobs.et/apply/siner-engener');
    $homeResponse->assertSee('Apply Now');
});
