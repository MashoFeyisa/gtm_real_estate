<?php

use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createFeedbackAgent(string $email): array
{
    $agent = Agent::create([
        'name' => 'Feedback Agent',
        'email' => $email,
        'phone' => '+251900111222',
        'bio' => 'Feedback agent',
    ]);

    $account = User::factory()->create([
        'name' => 'Feedback Agent',
        'email' => $email,
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    return [$agent, $account];
}

function createFeedbackAdmin(): User
{
    return User::factory()->create([
        'name' => 'Feedback Admin',
        'email' => 'feedback.admin@example.com',
        'password' => Hash::make('adminpass1'),
        'role' => 'admin',
    ]);
}

test('admin dashboard lists client feedback for every agent', function () {
    $admin = createFeedbackAdmin();

    $agentA = Agent::create(['name' => 'Agent A', 'email' => 'agent.a@example.com']);
    $agentB = Agent::create(['name' => 'Agent B', 'email' => 'agent.b@example.com']);

    AgentFeedback::create([
        'agent_id' => $agentA->id,
        'name' => 'Client One',
        'email' => 'client.one@example.com',
        'rating' => 5,
        'message' => 'Agent A found us a perfect home.',
        'is_approved' => true,
    ]);

    AgentFeedback::create([
        'agent_id' => $agentB->id,
        'name' => 'Client Two',
        'email' => 'client.two@example.com',
        'rating' => 4,
        'message' => 'Agent B handled our rental smoothly.',
        'is_approved' => false,
    ]);

    $this->actingAs($admin)
        ->get('/dashboard?section=testimonials')
        ->assertOk()
        ->assertSee('Agent A')
        ->assertSee('Agent B')
        ->assertSee('Client One')
        ->assertSee('Client Two')
        ->assertSee('Approved')
        ->assertSee('Pending');
});

test('admin cannot create testimonials and the create form is gone', function () {
    $admin = createFeedbackAdmin();

    $this->actingAs($admin)
        ->get('/dashboard?section=testimonials')
        ->assertOk()
        ->assertDontSee('+ Add Testimonial')
        ->assertDontSee('Add Client Testimonial');

    // The dedicated create route is removed, so this posts to no matching route.
    $this->actingAs($admin)
        ->post('/dashboard/testimonials', [
            'name' => 'Fake Client',
            'rating' => 5,
            'message' => 'Admin should not be able to create feedback.',
        ])->assertNotFound();

    $this->assertDatabaseCount('agent_feedbacks', 0);
});

test('admin can edit client feedback including approval', function () {
    $admin = createFeedbackAdmin();

    $agent = Agent::create(['name' => 'Agent Edit', 'email' => 'agent.edit@example.com']);

    $feedback = AgentFeedback::create([
        'agent_id' => $agent->id,
        'name' => 'Original Client',
        'email' => 'original.client@example.com',
        'rating' => 3,
        'message' => 'Initial feedback message.',
        'is_approved' => false,
    ]);

    $this->actingAs($admin)
        ->get('/dashboard?section=testimonials&edit_testimonial='.$feedback->id)
        ->assertOk()
        ->assertSee('Editing Testimonial from: Original Client');

    $this->actingAs($admin)
        ->put('/dashboard/testimonials/'.$feedback->id, [
            'name' => 'Edited Client',
            'email' => 'edited.client@example.com',
            'agent_id' => $agent->id,
            'rating' => 5,
            'message' => 'Edited feedback message.',
            'is_approved' => '1',
        ])
        ->assertRedirect(route('dashboard', ['section' => 'testimonials']));

    $feedback->refresh();

    expect($feedback->name)->toBe('Edited Client')
        ->and($feedback->rating)->toBe(5)
        ->and($feedback->is_approved)->toBeTrue();
});

test('admin can toggle feedback approval to show it on the home page testimonials', function () {
    $admin = createFeedbackAdmin();

    $agent = Agent::create(['name' => 'Agent Approve', 'email' => 'agent.approve@example.com']);

    $feedback = AgentFeedback::create([
        'agent_id' => $agent->id,
        'name' => 'Pending Client',
        'email' => 'pending.client@example.com',
        'rating' => 5,
        'message' => 'Pending feedback that should reach the home page.',
        'is_approved' => false,
    ]);

    // Pending feedback is not shown on the home page.
    $this->get('/')->assertOk()->assertDontSee('Pending feedback that should reach the home page.');

    $this->actingAs($admin)
        ->post('/dashboard/testimonials/'.$feedback->id.'/toggle-approval')
        ->assertRedirect();

    expect($feedback->refresh()->is_approved)->toBeTrue();

    $this->get('/')
        ->assertOk()
        ->assertSee('Pending Client')
        ->assertSee('Pending feedback that should reach the home page.');
});

test('agent sees their own received feedback in the portal', function () {
    [$agent, $account] = createFeedbackAgent('portal.feedback@example.com');

    AgentFeedback::create([
        'agent_id' => $agent->id,
        'name' => 'Happy Client',
        'email' => 'happy.client@example.com',
        'rating' => 5,
        'message' => 'Portal agent was outstanding.',
        'is_approved' => true,
    ]);

    $otherAgent = Agent::create(['name' => 'Other Agent', 'email' => 'other.agent@example.com']);

    AgentFeedback::create([
        'agent_id' => $otherAgent->id,
        'name' => 'Other Client',
        'email' => 'other.client@example.com',
        'rating' => 2,
        'message' => 'Feedback for a different agent must stay hidden.',
        'is_approved' => true,
    ]);

    $this->actingAs($account)
        ->get('/agent-portal?section=feedback')
        ->assertOk()
        ->assertSee('Happy Client')
        ->assertSee('Portal agent was outstanding.')
        ->assertDontSee('Other Client')
        ->assertDontSee('Feedback for a different agent must stay hidden.');
});

test('agent portal shows pending and approved feedback states', function () {
    [$agent, $account] = createFeedbackAgent('states.feedback@example.com');

    AgentFeedback::create([
        'agent_id' => $agent->id,
        'name' => 'Pending Client',
        'email' => 'pending2.client@example.com',
        'rating' => 3,
        'message' => 'Still awaiting admin approval.',
        'is_approved' => false,
    ]);

    $this->actingAs($account)
        ->get('/agent-portal?section=feedback')
        ->assertOk()
        ->assertSee('Pending review')
        ->assertSee('Still awaiting admin approval.');
});

test('admin can control home page hero and about content from site settings', function () {
    $admin = createFeedbackAdmin();

    $this->actingAs($admin)
        ->get('/dashboard?section=settings')
        ->assertOk()
        ->assertSee('Homepage Content')
        ->assertSee('Hero Title');

    $this->actingAs($admin)
        ->post('/dashboard/settings', [
            'site_name' => 'GTM Real Estate',
            'contact_email' => 'info@gtm.com',
            'contact_phone' => '+251 900 000 000',
            'contact_address' => 'Harar, Ethiopia',
            'hero_badge' => 'Luxury homes daily',
            'hero_title' => 'Custom hero title from admin',
            'hero_description' => 'Custom hero description from admin settings.',
            'about_badge' => 'About our story',
            'about_title' => 'Custom about title from admin',
            'about_description' => 'Custom about description from admin settings.',
        ])
        ->assertRedirect(route('dashboard', ['section' => 'settings']));

    $this->get('/')
        ->assertOk()
        ->assertSee('Custom hero title from admin')
        ->assertSee('Custom hero description from admin settings.')
        ->assertSee('Custom about title from admin')
        ->assertSee('Custom about description from admin settings.');

    // The settings form shows the saved values on the next visit.
    $this->actingAs($admin)
        ->get('/dashboard?section=settings')
        ->assertOk()
        ->assertSee('Custom hero title from admin');
});

test('home page falls back to default hero and about content when no settings exist', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Discover homes that feel like your next chapter.')
        ->assertSee('Trusted guidance for every move');
});

test('guests and non-admin users cannot edit testimonials', function () {
    $agent = Agent::create(['name' => 'Agent Guard', 'email' => 'agent.guard@example.com']);

    $feedback = AgentFeedback::create([
        'agent_id' => $agent->id,
        'name' => 'Guarded Client',
        'email' => 'guarded.client@example.com',
        'rating' => 4,
        'message' => 'Feedback used for permission checks.',
        'is_approved' => false,
    ]);

    $this->put('/dashboard/testimonials/'.$feedback->id, [
        'name' => 'Hacked Client',
        'rating' => 1,
        'message' => 'Should never be saved.',
    ])->assertRedirect('/login');

    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->post('/dashboard/testimonials/'.$feedback->id.'/toggle-approval')
        ->assertForbidden();

    expect($feedback->refresh()->name)->toBe('Guarded Client')
        ->and($feedback->is_approved)->toBeFalse();
});

test('admin can configure every landing page section including amenities, panorama, counters, and contact text from site settings', function () {
    $admin = createFeedbackAdmin();

    $payload = [
        'site_name' => 'Addis Luxury Real Estate',
        'contact_email' => 'concierge@addisluxury.et',
        'contact_phone' => '+251 911 888 999',
        'contact_address' => 'Bole Subcity, Addis Ababa',
        'hero_badge' => 'Ultra Luxury 2026',
        'hero_title' => 'Redefining Architectural Masterplans',
        'hero_description' => 'A premier collection of residences built for visionary homeowners.',
        'hero_primary_button_text' => 'View Prime Units',
        'hero_secondary_button_text' => 'Book VIP Walkthrough',
        'counter_1_value' => 'ETB 5.5B+',
        'counter_1_label' => 'Managed Capital',
        'amenities_badge' => 'World Class Living',
        'amenities_title' => 'Unmatched Luxury Masterplan',
        'amenities_description' => 'Every detail curated for the most discerning residents.',
        'amenity_1_title' => 'Private Jogging Circuit',
        'amenity_1_description' => 'Dedicated track around the perimeter of the estate.',
        'panorama_badge' => 'Panoramic Vista',
        'panorama_title' => 'Spectacular Skyline Outlook',
        'panorama_description' => 'Uninterrupted horizon views from your private balcony terrace.',
        'panorama_primary_button_text' => 'Request Private Showing',
        'contact_badge' => 'Private Advisory',
        'contact_title' => 'Schedule Your Confidential Meeting',
        'contact_box_title' => 'Connect With Our Private Concierge',
        'newsletter_badge' => 'Exclusive Updates',
        'newsletter_title' => 'VIP Property Dispatch',
        'newsletter_placeholder' => 'Enter your corporate email',
        'newsletter_button_text' => 'Join Private Circle',
        'concierge_button_text' => 'Direct Concierge',
    ];

    $response = $this->actingAs($admin)
        ->post(route('dashboard.settings.update'), $payload);

    $response->assertRedirect(route('dashboard', ['section' => 'settings']))
        ->assertSessionHas('success');

    $homeResponse = $this->get('/');
    $homeResponse->assertOk()
        ->assertSee('Ultra Luxury 2026')
        ->assertSee('Redefining Architectural Masterplans')
        ->assertSee('A premier collection of residences built for visionary homeowners.')
        ->assertSee('View Prime Units')
        ->assertSee('Book VIP Walkthrough')
        ->assertSee('ETB 5.5B+')
        ->assertSee('Managed Capital')
        ->assertSee('World Class Living')
        ->assertSee('Unmatched Luxury Masterplan')
        ->assertSee('Every detail curated for the most discerning residents.')
        ->assertSee('Private Jogging Circuit')
        ->assertSee('Dedicated track around the perimeter of the estate.')
        ->assertSee('Panoramic Vista')
        ->assertSee('Spectacular Skyline Outlook')
        ->assertSee('Uninterrupted horizon views from your private balcony terrace.')
        ->assertSee('Request Private Showing')
        ->assertSee('Private Advisory')
        ->assertSee('Schedule Your Confidential Meeting')
        ->assertSee('Connect With Our Private Concierge')
        ->assertSee('Exclusive Updates')
        ->assertSee('VIP Property Dispatch')
        ->assertSee('Enter your corporate email')
        ->assertSee('Join Private Circle')
        ->assertSee('Direct Concierge');
});

test('admin settings panel renders dropside navigation and sections', function () {
    $admin = createFeedbackAdmin();

    $response = $this->actingAs($admin)
        ->get('/dashboard?section=settings');

    $response->assertOk()
        ->assertSee('Dropside Navigation')
        ->assertSee('Site Branding')
        ->assertSee('Performance')
        ->assertSee('Lifestyle')
        ->assertSee('Featured Properties')
        ->assertSee('Developments')
        ->assertSee('VIP Contact Suite')
        ->assertSee('Newsletter &amp; Floating Concierge', false);
});
