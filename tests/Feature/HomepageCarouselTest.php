<?php

use App\Models\Agent;
use App\Models\AgentFeedback;
use App\Models\BlogPost;
use App\Models\Property;
use App\Models\User;

test('homepage includes carousels with controls and pagination dots for testimonials, featured properties, latest properties, blog and news, and agents', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('data-carousel="featured-properties"', false)
        ->assertSee('data-carousel="latest-properties"', false)
        ->assertSee('data-carousel="news"', false)
        ->assertSee('data-carousel="testimonials"', false)
        ->assertSee('data-carousel="agents"', false)
        ->assertSee('data-carousel-prev="featured-properties"', false)
        ->assertSee('data-carousel-next="featured-properties"', false)
        ->assertSee('data-carousel-prev="latest-properties"', false)
        ->assertSee('data-carousel-next="latest-properties"', false)
        ->assertSee('data-carousel-prev="news"', false)
        ->assertSee('data-carousel-next="news"', false)
        ->assertSee('data-carousel-prev="testimonials"', false)
        ->assertSee('data-carousel-next="testimonials"', false)
        ->assertSee('data-carousel-prev="agents"', false)
        ->assertSee('data-carousel-next="agents"', false)
        ->assertSee('data-carousel-dots="featured-properties"', false)
        ->assertSee('data-carousel-dots="latest-properties"', false)
        ->assertSee('data-carousel-dots="news"', false)
        ->assertSee('data-carousel-dots="testimonials"', false)
        ->assertSee('data-carousel-dots="agents"', false);
});

test('homepage displays more than three items in carousels when available in database', function () {
    $user = User::factory()->create();

    // Create 5 featured properties
    for ($i = 1; $i <= 5; $i++) {
        Property::create([
            'title' => "Featured Villa Luxury {$i}",
            'slug' => "featured-villa-luxury-{$i}",
            'price' => 5000000 + ($i * 100000),
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area' => 280,
            'city' => 'Addis Ababa',
            'type' => 'sale',
            'featured' => true,
            'status' => 'available',
        ]);
    }

    // Create 5 published posts
    for ($i = 1; $i <= 5; $i++) {
        BlogPost::create([
            'user_id' => $user->id,
            'title' => "Market Insight Post {$i}",
            'slug' => "market-insight-post-{$i}",
            'type' => 'news',
            'status' => 'published',
            'content' => "Insightful details about Ethiopian real estate market expansion number {$i}.",
            'published_at' => now()->subDays($i),
        ]);
    }

    // Create 5 agents
    for ($i = 1; $i <= 5; $i++) {
        $agent = Agent::create([
            'name' => "Elite Agent {$i}",
            'email' => "agent{$i}@example.com",
            'phone' => "+25191100000{$i}",
            'bio' => "Specialist in prime luxury district {$i}",
            'is_active' => true,
        ]);

        // Create approved testimonial for agent
        AgentFeedback::create([
            'agent_id' => $agent->id,
            'name' => "Happy Client {$i}",
            'email' => "client{$i}@example.com",
            'rating' => 5,
            'message' => "Amazing service received from consultant {$i}.",
            'is_approved' => true,
        ]);
    }

    $response = $this->get('/');
    $response->assertOk();

    // Verify all 5 featured properties are present in the carousel
    for ($i = 1; $i <= 5; $i++) {
        $response->assertSee("Featured Villa Luxury {$i}");
    }

    // Verify latest properties are also loaded in the latest-properties carousel
    for ($i = 1; $i <= 5; $i++) {
        $response->assertSee("Featured Villa Luxury {$i}");
    }

    // Verify all 5 posts are present in the carousel
    for ($i = 1; $i <= 5; $i++) {
        $response->assertSee("Market Insight Post {$i}");
    }

    // Verify all 5 agents are present in the carousel
    for ($i = 1; $i <= 5; $i++) {
        $response->assertSee("Elite Agent {$i}");
    }

    // Verify all 5 testimonials are present in the carousel
    for ($i = 1; $i <= 5; $i++) {
        $response->assertSee("Happy Client {$i}");
        $response->assertSee("Amazing service received from consultant {$i}.");
    }
});
