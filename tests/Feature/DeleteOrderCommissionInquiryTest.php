<?php

use App\Models\Agent;
use App\Models\Commission;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function createTestAgent(?User $user = null): Agent
{
    $random = Str::random(6);

    return Agent::create([
        'name' => 'Agent '.$random,
        'email' => 'agent.'.$random.'@example.com',
        'phone' => '+251911'.rand(100000, 999999),
        'bio' => 'Licensed real estate agent',
        'user_id' => $user?->id,
    ]);
}

function createTestProperty(Agent $agent, string $type = 'buy'): Property
{
    $random = Str::random(6);

    return Property::create([
        'title' => 'Property '.$random,
        'slug' => 'property-'.$random,
        'description' => 'Test property description.',
        'price' => 150000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 150,
        'type' => $type === 'rent' ? 'rent' : 'sale',
        'property_category' => 'home',
        'status' => 'published',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);
}

test('admin can delete an order and its agreement file', function () {
    Storage::fake('local');

    $admin = User::factory()->create(['role' => 'admin']);
    $agent = createTestAgent();
    $property = createTestProperty($agent);

    $agreementPath = 'agreements/test_order_agreement.pdf';
    Storage::disk('local')->put($agreementPath, '%PDF-fake-content');

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Almaz Kebede',
        'email' => 'almaz@example.com',
        'phone' => '+251911000111',
        'offer_amount' => 5000000,
        'type' => 'buy',
        'status' => 'accepted',
        'agreement_path' => $agreementPath,
    ]);

    expect(Storage::disk('local')->exists($agreementPath))->toBeTrue();

    $response = $this->actingAs($admin)
        ->delete(route('dashboard.orders.destroy', $order));

    $response->assertRedirect(route('dashboard', ['#orders']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    expect(Storage::disk('local')->exists($agreementPath))->toBeFalse();
});

test('admin can delete a commission record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $agent = createTestAgent();
    $property = createTestProperty($agent);
    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Dawit Girma',
        'email' => 'dawit@example.com',
        'phone' => '+251911222333',
        'offer_amount' => 3000000,
        'type' => 'buy',
        'status' => 'accepted',
    ]);

    $commission = Commission::query()->create([
        'agent_id' => $agent->id,
        'order_id' => $order->id,
        'property_id' => $property->id,
        'property_price' => 3000000,
        'commission_rate' => 5.0,
        'commission_amount' => 150000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('dashboard.commissions.destroy', $commission));

    $response->assertRedirect(route('dashboard', ['#commissions']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('commissions', ['id' => $commission->id]);
    $this->assertDatabaseHas('orders', ['id' => $order->id]);
});

test('admin can delete a client message inquiry', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $inquiry = Inquiry::query()->create([
        'name' => 'Helen Tadesse',
        'email' => 'helen@example.com',
        'phone' => '+251933445566',
        'subject' => 'Question regarding property in Bole',
        'message' => 'Is this property still available for viewing?',
        'status' => 'new',
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('dashboard.inquiries.destroy', $inquiry));

    $response->assertRedirect(route('dashboard', ['#inquiries']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
});

test('agent can delete their own buy or rent request', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = createTestAgent($user);
    $property = createTestProperty($agent, 'rent');

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Blen Bekele',
        'email' => 'blen@example.com',
        'phone' => '+251944556677',
        'offer_amount' => 45000,
        'type' => 'rent',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('agent.orders.destroy', $order));

    $response->assertRedirect(route('agent.portal', ['#buy-requests']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
});

test('agent cannot delete another agents order', function () {
    $user1 = User::factory()->create(['role' => 'agent']);
    $agent1 = createTestAgent($user1);

    $user2 = User::factory()->create(['role' => 'agent']);
    $agent2 = createTestAgent($user2);
    $property = createTestProperty($agent2);

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent2->id,
        'name' => 'Client for Agent 2',
        'email' => 'client2@example.com',
        'type' => 'buy',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('agent.orders.destroy', $order));

    $response->assertForbidden();
    $this->assertDatabaseHas('orders', ['id' => $order->id]);
});

test('agent can delete their own client inquiry message', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = createTestAgent($user);

    $inquiry = Inquiry::query()->create([
        'agent_id' => $agent->id,
        'name' => 'Yared Negash',
        'email' => 'yared@example.com',
        'phone' => '+251955667788',
        'subject' => 'Villa inquiry',
        'message' => 'Hello, I want to see the villa this weekend.',
        'status' => 'new',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('agent.inquiries.destroy', $inquiry));

    $response->assertRedirect(route('agent.portal', ['#messages']))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
});

test('agent cannot delete another agents inquiry message', function () {
    $user1 = User::factory()->create(['role' => 'agent']);
    $agent1 = createTestAgent($user1);

    $user2 = User::factory()->create(['role' => 'agent']);
    $agent2 = createTestAgent($user2);

    $inquiry = Inquiry::query()->create([
        'agent_id' => $agent2->id,
        'name' => 'Target Inquiry',
        'email' => 'target@example.com',
        'subject' => 'Message to Agent 2',
        'message' => 'Agent 2 private inquiry.',
        'status' => 'new',
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('agent.inquiries.destroy', $inquiry));

    $response->assertForbidden();
    $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id]);
});

test('admin dashboard renders delete buttons for orders commissions and inquiries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $agent = createTestAgent();
    $property = createTestProperty($agent);

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Test Client Order',
        'email' => 'order@example.com',
        'type' => 'buy',
        'status' => 'pending',
    ]);

    $commission = Commission::query()->create([
        'agent_id' => $agent->id,
        'order_id' => $order->id,
        'property_id' => $property->id,
        'property_price' => 100000,
        'commission_rate' => 5,
        'commission_amount' => 5000,
        'status' => 'pending',
    ]);

    $inquiry = Inquiry::query()->create([
        'name' => 'Test Inquirer',
        'email' => 'inquirer@example.com',
        'subject' => 'General Question',
        'message' => 'Hello there',
        'status' => 'new',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee(route('dashboard.orders.destroy', $order), false)
        ->assertSee(route('dashboard.commissions.destroy', $commission), false)
        ->assertSee(route('dashboard.inquiries.destroy', $inquiry), false);
});

test('agent portal renders delete buttons for orders and messages', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = createTestAgent($user);
    $property = createTestProperty($agent);

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Agent Order Client',
        'email' => 'agentorder@example.com',
        'type' => 'buy',
        'status' => 'pending',
    ]);

    $inquiry = Inquiry::query()->create([
        'agent_id' => $agent->id,
        'name' => 'Agent Message Client',
        'email' => 'agentmsg@example.com',
        'subject' => 'Agent inquiry subject',
        'message' => 'Agent inquiry body',
        'status' => 'new',
    ]);

    $response = $this->actingAs($user)->get('/agent-portal');

    $response->assertOk()
        ->assertSee(route('agent.orders.destroy', $order), false)
        ->assertSee(route('agent.inquiries.destroy', $inquiry), false)
        ->assertSee('Delete Request')
        ->assertSee('Delete Message');
});

test('dashboard uses custom confirmation modal and data-confirm for requests without browser alert', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $agent = createTestAgent();
    $property = createTestProperty($agent);

    $order = Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Modal Client',
        'email' => 'modal@example.com',
        'type' => 'buy',
        'status' => 'pending',
        'submitted_to_admin_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee('id="custom-confirm-modal"', false)
        ->assertSee('data-confirm="Are you sure you want to delete this request?"', false)
        ->assertDontSee("onsubmit=\"return confirm('Are you sure you want to delete this request?')\"", false);
});

test('agent portal uses custom confirmation modal and data-confirm for buy and rent requests', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $agent = createTestAgent($user);
    $property = createTestProperty($agent);

    Order::query()->create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Agent Modal Client',
        'email' => 'agentmodal@example.com',
        'type' => 'buy',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->get('/agent-portal');

    $response->assertOk()
        ->assertSee('id="custom-confirm-modal"', false)
        ->assertSee('data-confirm="Are you sure you want to delete this buy/rent request?"', false)
        ->assertDontSee('onsubmit="return confirm', false);
});

test('admin can mark an inquiry message as read via ajax json', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $inquiry = Inquiry::query()->create([
        'name' => 'Sara Daniel',
        'email' => 'sara@example.com',
        'subject' => 'Apartment price question',
        'message' => 'Can you give me more details about payment schedule?',
        'status' => 'new',
        'read_at' => null,
    ]);

    expect($inquiry->read_at)->toBeNull();

    $response = $this->actingAs($admin)
        ->postJson(route('dashboard.inquiries.read', $inquiry));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'unread_count' => 0,
        ]);

    $inquiry->refresh();
    expect($inquiry->read_at)->not->toBeNull();
});

test('non-admin cannot mark an inquiry message as read on admin route', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $inquiry = Inquiry::query()->create([
        'name' => 'Sara Daniel',
        'email' => 'sara@example.com',
        'subject' => 'Apartment price question',
        'message' => 'Can you give me more details about payment schedule?',
        'status' => 'new',
        'read_at' => null,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('dashboard.inquiries.read', $inquiry));

    $response->assertForbidden();
    expect($inquiry->fresh()->read_at)->toBeNull();
});

test('admin dashboard renders Message navigation item, reduced message width, view button, and modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $inquiry = Inquiry::query()->create([
        'name' => 'Abebe Test',
        'email' => 'abebe@example.com',
        'subject' => 'Test Subject',
        'message' => 'This is a long test client message to verify reduced width and modal functionality.',
        'status' => 'new',
        'read_at' => null,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee('<span>Message</span>', false)
        ->assertSee('id="sidebar-inquiries-badge"', false)
        ->assertSee('1 new', false)
        ->assertSee('id="admin-inquiry-modal"', false)
        ->assertSee('view-inquiry-btn', false)
        ->assertSee('w-44 max-w-[170px]', false)
        ->assertSee(route('dashboard.inquiries.read', $inquiry), false);
});
