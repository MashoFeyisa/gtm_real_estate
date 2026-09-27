<?php

use App\Mail\InquiryReceived;
use App\Models\Agent;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('admin can log in with the seeded credentials', function () {
    User::query()->create([
        'name' => 'Admin User',
        'email' => 'admin@realestate.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin@realestate.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs(User::where('email', 'admin@realestate.com')->first());
});

test('user can submit a home page contact form and notify the admin', function () {
    Mail::fake();

    $response = $this->post('/contact', [
        'name' => 'Amanuel Bekele',
        'email' => 'amanuel@example.com',
        'phone' => '+251912345678',
        'subject' => 'House inquiry',
        'message' => 'I want to schedule a visit for the Greenview Residence property.',
    ]);

    $response->assertRedirect('/app#contact')
        ->assertSessionHas('success', 'Your message has been sent successfully.');

    $inquiry = Inquiry::query()->where('email', 'amanuel@example.com')->first();
    expect($inquiry)->not->toBeNull();
    expect($inquiry->subject)->toBe('House inquiry');

    Mail::assertSent(InquiryReceived::class, function ($mail) use ($inquiry) {
        return $mail->inquiry->id === $inquiry->id;
    });
});

test('admin can create and manage property listings with category types', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'property-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/properties', [
            'title' => 'Cedar Heights Villa',
            'description' => 'A modern villa with a landscaped garden and pool.',
            'price' => 480000,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area' => 220,
            'type' => 'sale',
            'category' => 'villa',
            'status' => 'draft',
            'city' => 'Addis Ababa',
            'address' => 'Bole, Addis Ababa',
            'featured' => true,
        ])
        ->assertRedirect('/dashboard');

    $property = Property::query()->where('title', 'Cedar Heights Villa')->first();
    expect($property)->not->toBeNull();
    expect($property->status)->toBe('draft');
    expect($property->category)->toBe('villa');

    $this->actingAs($admin)
        ->put('/dashboard/properties/'.$property->id, [
            'title' => 'Cedar Heights Villa',
            'description' => 'Updated villa description.',
            'price' => 510000,
            'bedrooms' => 5,
            'bathrooms' => 4,
            'area' => 260,
            'type' => 'sale',
            'category' => 'home',
            'status' => 'published',
            'city' => 'Addis Ababa',
            'address' => 'Bole, Addis Ababa',
            'featured' => true,
        ])
        ->assertRedirect('/dashboard');

    $property->refresh();
    expect($property->category)->toBe('home');
    expect($property->price)->toBe(510000.0);

    $this->actingAs($admin)
        ->post('/dashboard/properties/'.$property->id.'/toggle-publish')
        ->assertRedirect('/dashboard');

    $property->refresh();
    expect($property->status)->toBe('draft');
    expect($property->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->post('/dashboard/properties/'.$property->id.'/archive')
        ->assertRedirect('/dashboard');

    $property->refresh();
    expect($property->status)->toBe('archived');
    expect($property->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->delete('/dashboard/properties/'.$property->id)
        ->assertRedirect('/dashboard');

    $this->assertDatabaseMissing('properties', ['id' => $property->id]);
});

test('admin can upload a property image and it displays on the property detail page', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'property-image-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    Storage::fake('public');

    $this->actingAs($admin)
        ->post('/dashboard/properties', [
            'title' => 'Azwa Residence',
            'description' => 'A contemporary residence with a private courtyard and premium finishes.',
            'price' => 540000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 210,
            'type' => 'sale',
            'status' => 'published',
            'city' => 'Harar',
            'address' => 'Harar, Ethiopia',
            'featured' => true,
            'image' => UploadedFile::fake()->image('azwa-residence.jpg'),
        ])
        ->assertRedirect('/dashboard');

    $property = Property::query()->where('title', 'Azwa Residence')->first();
    expect($property)->not->toBeNull();
    expect($property->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($property->image_path);

    $this->get('/properties/'.$property->slug)
        ->assertOk()
        ->assertSee('Azwa Residence')
        ->assertSee('storage/');
});

test('admin can edit and delete a user from the user manager board', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'user-manager-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $user = User::factory()->create([
        'name' => 'Regular User',
        'email' => 'regular-user@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'user',
    ]);

    $this->actingAs($admin)
        ->put('/dashboard/users/'.$user->id, [
            'name' => 'Updated User',
            'email' => 'updated-user@example.com',
            'role' => 'admin',
        ])
        ->assertRedirect('/dashboard');

    $user->refresh();
    expect($user->name)->toBe('Updated User');
    expect($user->email)->toBe('updated-user@example.com');
    expect($user->role)->toBe('admin');

    $this->actingAs($admin)
        ->delete('/dashboard/users/'.$user->id)
        ->assertRedirect('/dashboard');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('workers attendance is removed completely from the application', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'attendance-nav-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->get('/')->assertOk()->assertDontSee('Attendance');
    $this->actingAs($admin)->get('/dashboard')->assertOk()->assertDontSee('Attendance');
    $this->actingAs($admin)->get('/dashboard')->assertOk()->assertDontSee('Workers Management');
    $this->get('/attendance')->assertNotFound();
});

test('admin can create a blog post with an image', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'content-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    Storage::fake('public');

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'New Neighborhood Investment Guide',
            'type' => 'news',
            'content' => 'This is a detailed article about neighborhood investment opportunities and long-term growth.',
            'image' => UploadedFile::fake()->image('neighborhood.jpg'),
        ])
        ->assertRedirect('/dashboard');

    $post = BlogPost::query()->where('title', 'New Neighborhood Investment Guide')->first();
    expect($post)->not->toBeNull();
    expect($post->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);
});

test('admin can create a scheduled editorial post with seo metadata', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'editor-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    Storage::fake('public');

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'Harar Property Market Update',
            'slug' => 'harar-property-market-update',
            'type' => 'news',
            'category' => 'Market Trends',
            'tags' => 'harar,property,market',
            'author_name' => 'Admin User',
            'status' => 'scheduled',
            'published_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'seo_title' => 'Harar Property Market Update',
            'seo_description' => 'Read the latest property trends in Harar.',
            'content' => '<p>Harar remains one of the fastest-growing property markets in the region.</p>',
            'image' => UploadedFile::fake()->image('harar.jpg'),
            'social_image' => UploadedFile::fake()->image('harar-social.jpg'),
            'related_posts' => '',
        ])
        ->assertRedirect('/dashboard');

    $post = BlogPost::query()->where('title', 'Harar Property Market Update')->first();
    expect($post)->not->toBeNull();
    expect($post->status)->toBe('scheduled');
    expect($post->category)->toBe('Market Trends');
    expect($post->seo_title)->toBe('Harar Property Market Update');
    expect($post->seo_description)->toBe('Read the latest property trends in Harar.');
    expect($post->image_path)->not->toBeNull();
    expect($post->social_image_path)->not->toBeNull();
});

test('dashboard sidebar links target the management sections', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'dashboard-sidebar@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('data-target-section')
        ->assertSee('data-section')
        ->assertSee('overview')
        ->assertSee('properties')
        ->assertSee('blog-posts')
        ->assertSee('agents')
        ->assertSee('users')
        ->assertSee('DOMContentLoaded');
});

test('admin can publish a blog post and it appears on the public blog/news section', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin-post@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'New property market insights',
            'type' => 'news',
            'content' => 'This is an admin news post for the real estate portal.',
        ])
        ->assertSessionHas('success', 'Your news was posted successfully.');

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'New property market insights',
        'type' => 'news',
    ]);

    $response = $this->get('/app');
    $response->assertOk();
    $response->assertSee('New property market insights');
    $response->assertDontSee('value="student"');
});

test('the home page includes the required real estate sections', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Real Estate')
        ->assertSee('Featured Properties')
        ->assertSee('Latest Properties')
        ->assertSee('Featured Projects')
        ->assertSee('About GTP')
        ->assertSee('Testimonials')
        ->assertSee('Newsletter');
});

test('admin can publish listing and featured project content for homepage sections', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'homepage-content-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'Harar Townhouse Listing',
            'type' => 'listing',
            'status' => 'published',
            'content' => 'A premium townhouse in Harar with private parking and a landscaped courtyard.',
        ])
        ->assertSessionHas('success', 'Your listing was posted successfully.');

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'Emerald Heights Development',
            'type' => 'project',
            'status' => 'published',
            'content' => 'A future-focused project with smart homes and modern shared amenities.',
        ])
        ->assertSessionHas('success', 'Your project was posted successfully.');

    $this->assertDatabaseHas('blog_posts', ['title' => 'Harar Townhouse Listing', 'type' => 'listing']);
    $this->assertDatabaseHas('blog_posts', ['title' => 'Emerald Heights Development', 'type' => 'project']);

    $response = $this->get('/');
    $response->assertOk();
    $response->assertSee('Harar Townhouse Listing');
    $response->assertSee('Emerald Heights Development');
});

test('homepage shows live admin-created properties and project content', function () {
    $category = Category::create([
        'name' => 'Villa',
        'slug' => 'villa',
    ]);

    $location = Location::create([
        'name' => 'harar',
        'slug' => 'harar',
    ]);

    $agent = Agent::create([
        'name' => 'Alemu Bekele',
        'email' => 'alemu@example.com',
        'phone' => '+251911000111',
        'bio' => 'Regional property advisor',
    ]);

    Property::create([
        'title' => 'Harar Courtyard Villa',
        'slug' => 'harar-courtyard-villa',
        'description' => 'A premium villa with a courtyard and modern finishings.',
        'price' => 620000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 2600,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'address' => 'Harar, Ethiopia',
        'featured' => true,
        'category_id' => $category->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
        'is_active' => true,
    ]);

    BlogPost::create([
        'title' => 'Harar Townhouse Listing',
        'slug' => 'harar-townhouse-listing',
        'type' => 'listing',
        'status' => 'published',
        'content' => 'A premium townhouse in Harar with private parking and a landscaped courtyard.',
        'user_id' => User::factory()->create()->id,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Emerald Heights Development',
        'slug' => 'emerald-heights-development',
        'type' => 'project',
        'status' => 'published',
        'content' => 'A future-focused project with smart homes and modern shared amenities.',
        'user_id' => User::factory()->create()->id,
        'published_at' => now(),
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Harar Courtyard Villa');
    $response->assertSee('Harar Townhouse Listing');
    $response->assertSee('Emerald Heights Development');
});

test('sold properties are marked and counted on the home page', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'sold-property-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)->post('/dashboard/properties', [
        'title' => 'Sold Residence',
        'description' => 'A sold residence with a successful close.',
        'price' => 650000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 240,
        'type' => 'sale',
        'category' => 'home',
        'status' => 'sold',
        'city' => 'Addis Ababa',
        'address' => 'Bole, Addis Ababa',
        'featured' => true,
    ]);

    $this->get('/')->assertOk()->assertSee('Sold')->assertSee('properties sold');
});

test('admin can publish organization job openings on the careers page', function () {
    $admin = User::factory()->create([
        'name' => 'HR Manager',
        'email' => 'hr@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'Senior Property Consultant',
            'type' => 'job',
            'category' => 'Sales',
            'status' => 'published',
            'content' => 'We are hiring a senior property consultant to drive sales and client growth across our team.',
        ])
        ->assertRedirect('/dashboard');

    $job = BlogPost::query()->where('title', 'Senior Property Consultant')->first();
    expect($job)->not->toBeNull();
    expect($job->type)->toBe('job');

    $this->get('/careers')->assertOk()->assertSee('Senior Property Consultant');
});

test('admin can create a job posting with requirements and an apply link shown on the careers page', function () {
    $admin = User::factory()->create([
        'name' => 'HR Manager',
        'email' => 'hr-manager@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/posts', [
            'title' => 'Real Estate Sales Agent',
            'type' => 'job',
            'category' => 'Sales',
            'status' => 'published',
            'content' => 'Join our sales team to connect clients with premium properties across Ethiopia.',
            'job_location' => 'Addis Ababa, Ethiopia',
            'job_type' => 'Full-time',
            'salary_range' => 'ETB 15,000 - 25,000',
            'experience_level' => '2+ years',
            'education_level' => "Bachelor's Degree in Marketing or related field",
            'apply_link' => 'https://example.com/apply/sales-agent',
            'application_deadline' => now()->addMonth()->format('Y-m-d'),
            'requirements' => "2+ years of sales experience\nStrong communication skills\nValid real estate license preferred",
        ])
        ->assertRedirect('/dashboard');

    $job = BlogPost::query()->where('title', 'Real Estate Sales Agent')->first();
    expect($job)->not->toBeNull();
    expect($job->type)->toBe('job');
    expect($job->job_location)->toBe('Addis Ababa, Ethiopia');
    expect($job->job_type)->toBe('Full-time');
    expect($job->experience_level)->toBe('2+ years');
    expect($job->education_level)->toBe("Bachelor's Degree in Marketing or related field");
    expect($job->apply_link)->toBe('https://example.com/apply/sales-agent');
    expect($job->application_deadline->format('Y-m-d'))->toBe(now()->addMonth()->format('Y-m-d'));

    $this->get('/careers')
        ->assertOk()
        ->assertSee('Real Estate Sales Agent')
        ->assertSee('Addis Ababa, Ethiopia')
        ->assertSee('Full-time')
        ->assertSee('ETB 15,000 - 25,000')
        ->assertSee('2+ years')
        ->assertSee("Bachelor's Degree in Marketing or related field")
        ->assertSee('Strong communication skills')
        ->assertSee('https://example.com/apply/sales-agent')
        ->assertSee('Apply Now');
});

test('admin dashboard has a job postings sidebar section with create and manage forms', function () {
    $admin = User::factory()->create([
        'name' => 'HR Manager',
        'email' => 'hr-sidebar@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Job Postings')
        ->assertSee('job-postings')
        ->assertSee('Apply Link')
        ->assertSee('Experience Required')
        ->assertSee('Education Required')
        ->assertSee('Job Description');
});

test('admin can edit an existing job posting through the job postings section', function () {
    $admin = User::factory()->create([
        'name' => 'HR Manager',
        'email' => 'hr-edit@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $job = BlogPost::create([
        'title' => 'Property Manager',
        'slug' => 'property-manager',
        'type' => 'job',
        'status' => 'published',
        'content' => 'Oversee day-to-day building operations and tenant relations.',
        'user_id' => $admin->id,
        'job_location' => 'Hawassa, Ethiopia',
        'experience_level' => '3+ years',
        'published_at' => now(),
    ]);

    $this->actingAs($admin)
        ->put('/dashboard/posts/'.$job->id, [
            'title' => 'Senior Property Manager',
            'type' => 'job',
            'status' => 'published',
            'content' => 'Oversee day-to-day building operations and tenant relations for a growing portfolio.',
            'job_location' => 'Addis Ababa, Ethiopia',
            'experience_level' => '5+ years',
            'education_level' => "Bachelor's Degree",
            'apply_link' => 'mailto:careers@realestate.com',
        ])
        ->assertRedirect('/dashboard');

    $job->refresh();
    expect($job->title)->toBe('Senior Property Manager');
    expect($job->job_location)->toBe('Addis Ababa, Ethiopia');
    expect($job->experience_level)->toBe('5+ years');
    expect($job->apply_link)->toBe('mailto:careers@realestate.com');

    $this->get('/careers')->assertOk()->assertSee('Senior Property Manager');
});

test('public website pages render for key routes', function () {
    $this->get('/about')->assertOk();
    $this->get('/services')->assertOk();
    $this->get('/contact')->assertOk();
    $this->get('/privacy-policy')->assertOk();
    $this->get('/terms')->assertOk();
});

test('admin can create agents and they appear on the home page agents section', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agent-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/agents', [
            'name' => 'Selam Alemu',
            'email' => 'selam.agent@example.com',
            'phone' => '+251911555000',
            'bio' => 'Luxury villa specialist',
        ])
        ->assertRedirect('/dashboard?section=agents')
        ->assertSessionHas('success', 'Agent created successfully.');

    $this->assertDatabaseHas('agents', [
        'email' => 'selam.agent@example.com',
        'bio' => 'Luxury villa specialist',
    ]);

    $response = $this->get('/');
    $response->assertOk()
        ->assertSee('Selam Alemu')
        ->assertSee('Luxury villa specialist')
        ->assertSee('Contact Selam Alemu');
});

test('users can send a message directly to a specific agent from the contact form', function () {
    Mail::fake();

    $agent = Agent::create([
        'name' => 'Mekdes Ali',
        'email' => 'mekdes.agent@example.com',
        'phone' => '+251922000002',
        'bio' => 'Investment advisor',
    ]);

    $response = $this->get('/app?agent='.$agent->id);
    $response->assertOk()->assertSee('<option value="'.$agent->id.'" selected', false);

    $this->post('/contact', [
        'name' => 'Daniel Girma',
        'email' => 'daniel@example.com',
        'phone' => '+251933444555',
        'agent_id' => $agent->id,
        'subject' => 'Villa viewing request',
        'message' => 'I would like to arrange a viewing with Mekdes for the Bole villa listing.',
    ])->assertRedirect('/agents/'.$agent->id)
        ->assertSessionHas('success', 'Your message has been sent to Mekdes Ali successfully.');

    $inquiry = Inquiry::query()->where('email', 'daniel@example.com')->first();
    expect($inquiry)->not->toBeNull();
    expect($inquiry->agent_id)->toBe($agent->id);

    Mail::assertSent(InquiryReceived::class, 2);
});

test('contact form still works without choosing an agent', function () {
    Mail::fake();

    $this->post('/contact', [
        'name' => 'General Visitor',
        'email' => 'general@example.com',
        'subject' => 'General question',
        'message' => 'I have a general question about listings in Harar.',
    ])->assertRedirect('/app#contact')
        ->assertSessionHas('success', 'Your message has been sent successfully.');

    $inquiry = Inquiry::query()->where('email', 'general@example.com')->first();
    expect($inquiry)->not->toBeNull();
    expect($inquiry->agent_id)->toBeNull();

    Mail::assertSent(InquiryReceived::class, 1);
});

test('agents page lists all agents with photos and call buttons', function () {
    $agent = Agent::create([
        'name' => 'Bekele Tadesse',
        'email' => 'bekele@example.com',
        'phone' => '+251911777888',
        'bio' => 'Commercial property specialist',
    ]);

    $this->get('/agents')
        ->assertOk()
        ->assertSee('Bekele Tadesse')
        ->assertSee('Commercial property specialist')
        ->assertSee('tel:+251911777888', false)
        ->assertSee('View Profile');
});

test('agent profile page shows photo, call link, contact form, and feedback section', function () {
    $agent = Agent::create([
        'name' => 'Hanna Girma',
        'email' => 'hanna@example.com',
        'phone' => '+251922333444',
        'bio' => 'Rental market expert',
    ]);

    $this->get('/agents/'.$agent->id)
        ->assertOk()
        ->assertSee('Hanna Girma')
        ->assertSee('tel:+251922333444', false)
        ->assertSee(route('contact.store'), false)
        ->assertSee('Give Feedback');
});

test('clients can leave feedback for an agent and it shows after approval', function () {
    $agent = Agent::create([
        'name' => 'Dawit Haile',
        'email' => 'dawit@example.com',
        'phone' => '+251933222111',
        'bio' => 'Land investment advisor',
    ]);

    $this->post('/agents/'.$agent->id.'/feedback', [
        'name' => 'Sara Tesfaye',
        'email' => 'sara@example.com',
        'rating' => 5,
        'message' => 'Dawit helped us find the perfect plot with great patience and honest advice.',
    ])->assertRedirect('/agents/'.$agent->id)
        ->assertSessionHas('feedback_success', 'Thank you! Your feedback was submitted and will appear once reviewed.');

    $this->assertDatabaseHas('agent_feedbacks', [
        'agent_id' => $agent->id,
        'email' => 'sara@example.com',
        'rating' => 5,
        'is_approved' => false,
    ]);

    // Pending feedback must not be publicly visible.
    $this->get('/agents/'.$agent->id)
        ->assertOk()
        ->assertDontSee('Dawit helped us find the perfect plot');

    $feedback = $agent->feedbacks()->first();
    $feedback->update(['is_approved' => true]);

    $this->get('/agents/'.$agent->id)
        ->assertOk()
        ->assertSee('Sara Tesfaye')
        ->assertSee('Dawit helped us find the perfect plot');

    expect($agent->fresh()->feedback_count)->toBe(1);
    expect($agent->fresh()->average_rating)->toBe(5.0);
});

test('agent feedback requires valid rating and message', function () {
    $agent = Agent::create([
        'name' => 'Lidya Alemu',
        'email' => 'lidya@example.com',
        'phone' => null,
        'bio' => 'First-time buyer consultant',
    ]);

    $this->from('/agents/'.$agent->id)
        ->post('/agents/'.$agent->id.'/feedback', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'rating' => 9,
            'message' => 'short',
        ])
        ->assertSessionHasErrorsIn('feedback', ['rating', 'message']);

    $this->assertDatabaseCount('agent_feedbacks', 0);
});

test('agent photo can be uploaded from the dashboard and displays on the agents page', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agent-photo-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/agents', [
            'name' => 'Photo Agent',
            'email' => 'photo.agent@example.com',
            'phone' => '+251955444333',
            'bio' => 'Luxury apartment specialist',
            'photo' => UploadedFile::fake()->image('agent-photo.jpg'),
        ])
        ->assertRedirect('/dashboard?section=agents')
        ->assertSessionHas('success', 'Agent created successfully.');

    $agent = Agent::query()->where('email', 'photo.agent@example.com')->first();
    expect($agent)->not->toBeNull();
    expect($agent->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($agent->photo_path);

    $this->get('/agents')
        ->assertOk()
        ->assertSee('Photo Agent')
        ->assertSee('storage/'.$agent->photo_path, false);
});

test('contact form submitted from an agent profile redirects back to the profile', function () {
    Mail::fake();

    $agent = Agent::create([
        'name' => 'Yonas Mekonnen',
        'email' => 'yonas@example.com',
        'phone' => '+251944555666',
        'bio' => 'Property legal advisor',
    ]);

    $this->post('/contact', [
        'name' => 'Marta Bekele',
        'email' => 'marta@example.com',
        'phone' => '+251966777888',
        'agent_id' => $agent->id,
        'subject' => 'Viewing request',
        'message' => 'I would like to schedule a viewing for the property listed by Yonas.',
    ])->assertRedirect('/agents/'.$agent->id)
        ->assertSessionHas('success', 'Your message has been sent to Yonas Mekonnen successfully.');
});

test('admin can delete an agent from the agents management section', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agent-delete-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $agent = Agent::create([
        'name' => 'Temporary Agent',
        'email' => 'temp.agent@example.com',
        'phone' => '+251900000999',
        'bio' => 'Trial specialist',
    ]);

    $this->actingAs($admin)
        ->delete('/dashboard/agents/'.$agent->id)
        ->assertRedirect('/dashboard')
        ->assertSessionHas('success', 'Agent deleted successfully.');

    $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
});

test('admin dashboard has an agents sidebar section with create and manage forms', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agents-sidebar@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Agents Management')
        ->assertSee('Add Agent')
        ->assertSee('Title / Specialty');
});

test('admin can edit an agent from the agents management section', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agent-edit-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $agent = Agent::create([
        'name' => 'Editable Agent',
        'email' => 'editable.agent@example.com',
        'phone' => '+251900111222',
        'bio' => 'Junior consultant',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard?edit_agent='.$agent->id.'&section=agents')
        ->assertOk()
        ->assertSee('Edit Agent')
        ->assertSee('Editable Agent');

    $this->actingAs($admin)
        ->put('/dashboard/agents/'.$agent->id, [
            'name' => 'Edited Agent',
            'email' => 'edited.agent@example.com',
            'phone' => '+251900111333',
            'bio' => 'Senior consultant',
            'password' => 'portal123',
        ])
        ->assertRedirect('/dashboard?section=agents')
        ->assertSessionHas('success', 'Agent updated successfully.');

    $agent->refresh();
    expect($agent->name)->toBe('Edited Agent');
    expect($agent->bio)->toBe('Senior consultant');

    $account = $agent->account;
    expect($account)->not->toBeNull();
    expect($account->email)->toBe('edited.agent@example.com');
    expect($account->role)->toBe('agent');
    expect(Hash::check('portal123', $account->password))->toBeTrue();
});

test('creating an agent with a password creates a portal login account', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'agent-account-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/agents', [
            'name' => 'Portal Agent',
            'email' => 'portal.agent@example.com',
            'phone' => '+251955000111',
            'bio' => 'Portal tester',
            'password' => 'agentpass1',
        ])
        ->assertRedirect('/dashboard?section=agents')
        ->assertSessionHas('success', 'Agent created successfully.');

    $agent = Agent::query()->where('email', 'portal.agent@example.com')->first();
    expect($agent->account)->not->toBeNull();
    expect($agent->account->role)->toBe('agent');
    expect(Hash::check('agentpass1', $agent->account->password))->toBeTrue();
});

test('agent can log in and see their portal with notifications and buy requests', function () {
    Mail::fake();

    $agent = Agent::create([
        'name' => 'Portal Viewer',
        'email' => 'portal.viewer@example.com',
        'phone' => '+251944000111',
        'bio' => 'Portal viewer',
    ]);

    $account = User::factory()->create([
        'name' => 'Portal Viewer',
        'email' => 'portal.viewer@example.com',
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    $property = Property::create([
        'title' => 'Portal Viewer Villa',
        'slug' => 'portal-viewer-villa',
        'description' => 'A villa listed by the portal viewer.',
        'price' => 320000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => 180,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    Inquiry::create([
        'name' => 'Client One',
        'email' => 'client.one@example.com',
        'subject' => 'Viewing request',
        'message' => 'I would love to view this property on Saturday morning.',
        'status' => 'new',
        'agent_id' => $agent->id,
    ]);

    Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Client Two',
        'email' => 'client.two@example.com',
        'offer_amount' => 300000,
        'message' => 'I can close quickly if the price works.',
        'status' => 'pending',
    ]);

    $response = $this->post('/login', [
        'email' => 'portal.viewer@example.com',
        'password' => 'agentpass1',
    ]);

    $response->assertRedirect('/agent-portal');

    $this->get('/agent-portal')
        ->assertOk()
        ->assertSee('Portal Viewer')
        ->assertSee('Buy Requests')
        ->assertSee('Client Two')
        ->assertSee('$300,000', false)
        ->assertSee('Client Messages')
        ->assertSee('Client One')
        ->assertSee('Viewing request');
});

test('agent can accept and reject buy requests', function () {
    $agent = Agent::create([
        'name' => 'Order Handler',
        'email' => 'order.handler@example.com',
        'phone' => '+251933000999',
        'bio' => 'Order handler',
    ]);

    $account = User::factory()->create([
        'name' => 'Order Handler',
        'email' => 'order.handler@example.com',
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agent->forceFill(['user_id' => $account->id])->save();

    $property = Property::create([
        'title' => 'Order Handler House',
        'slug' => 'order-handler-house',
        'description' => 'A house listed by the order handler.',
        'price' => 210000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 120,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'name' => 'Buyer Person',
        'email' => 'buyer@example.com',
        'status' => 'pending',
    ]);

    $this->actingAs($account)
        ->patch('/agent-portal/orders/'.$order->id.'/status', [
            'status' => 'accepted',
            'agent_note' => 'Congratulations, we accept your offer.',
        ])
        ->assertRedirect('/agent-portal')
        ->assertSessionHas('success', 'The buy request was accepted and the agreement was generated.');

    $order->refresh();
    expect($order->status)->toBe('accepted');
    expect($order->agent_note)->toBe('Congratulations, we accept your offer.');

    $order->update(['status' => 'pending', 'agent_note' => null]);

    $this->actingAs($account)
        ->patch('/agent-portal/orders/'.$order->id.'/status', ['status' => 'rejected'])
        ->assertRedirect('/agent-portal');

    expect($order->refresh()->status)->toBe('rejected');
});

test('agent cannot change the status of another agents order', function () {
    $agentA = Agent::create(['name' => 'Agent A', 'email' => 'agent.a@example.com', 'bio' => 'Agent A']);
    $agentB = Agent::create(['name' => 'Agent B', 'email' => 'agent.b@example.com', 'bio' => 'Agent B']);

    $accountB = User::factory()->create([
        'name' => 'Agent B',
        'email' => 'agent.b.user@example.com',
        'password' => Hash::make('agentpass1'),
        'role' => 'agent',
    ]);

    $agentB->forceFill(['user_id' => $accountB->id])->save();

    $property = Property::create([
        'title' => 'Agent A Property',
        'slug' => 'agent-a-property',
        'description' => 'Property owned by agent A.',
        'price' => 100000,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area' => 100,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => $agentA->id,
    ]);

    $order = Order::create([
        'property_id' => $property->id,
        'agent_id' => $agentA->id,
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'status' => 'pending',
    ]);

    $this->actingAs($accountB)
        ->patch('/agent-portal/orders/'.$order->id.'/status', ['status' => 'accepted'])
        ->assertForbidden();

    expect($order->refresh()->status)->toBe('pending');
});

test('clients can submit a buy request on a property page', function () {
    $agent = Agent::create(['name' => 'Listing Agent', 'email' => 'listing.agent@example.com', 'phone' => '+251977000111', 'bio' => 'Listing agent']);

    $property = Property::create([
        'title' => 'Buyable Villa',
        'slug' => 'buyable-villa',
        'description' => 'A villa clients can request to buy.',
        'price' => 450000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 240,
        'type' => 'sale',
        'status' => 'published',
        'city' => 'Harar',
        'is_active' => true,
        'agent_id' => $agent->id,
    ]);

    $this->post('/properties/'.$property->id.'/orders', [
        'name' => 'Interested Buyer',
        'email' => 'buyer.person@example.com',
        'phone' => '+251988111222',
        'offer_amount' => 430000,
        'message' => 'I am interested in this villa and can visit this weekend.',
    ])->assertRedirect('/properties/buyable-villa')
        ->assertSessionHas('success', 'Your buy request was sent. The agent will contact you shortly.');

    $this->assertDatabaseHas('orders', [
        'property_id' => $property->id,
        'agent_id' => $agent->id,
        'email' => 'buyer.person@example.com',
        'offer_amount' => 430000,
        'status' => 'pending',
    ]);

    $this->get('/properties/buyable-villa')
        ->assertOk()
        ->assertSee('Buy / Request')
        ->assertSee('Listing Agent');
});

test('header shows dashboard and portal links depending on the logged in user', function () {
    $admin = User::factory()->create([
        'name' => 'Header Admin',
        'email' => 'header.admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get('/app')
        ->assertOk()
        ->assertSee('Dashboard', false)
        ->assertSee('Logout', false);

    $agentUser = User::factory()->create([
        'name' => 'Header Agent',
        'email' => 'header.agent@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'agent',
    ]);

    $agent = Agent::create(['name' => 'Header Agent Profile', 'email' => 'header.agent.profile@example.com', 'bio' => 'Agent profile']);
    $agent->forceFill(['user_id' => $agentUser->id])->save();

    $this->actingAs($agentUser)
        ->get('/app')
        ->assertOk()
        ->assertSee('My Portal', false)
        ->assertSee('Logout', false);
});

test('guests see the login button in the header instead of account links', function () {
    $this->get('/app')
        ->assertOk()
        ->assertSee('Login', false)
        ->assertDontSee('Logout', false)
        ->assertDontSee('My Portal', false);
});

test('property listing page loads and shows a property', function () {
    $category = Category::create([
        'name' => 'Apartment',
        'slug' => 'apartment',
    ]);

    $location = Location::create([
        'name' => 'addisababa',
        'slug' => 'addisababa',
    ]);

    $agent = Agent::create([
        'name' => 'Jane Mwangi',
        'email' => 'jane@example.com',
        'phone' => '+254700000001',
        'bio' => 'Property expert',
    ]);

    $property = Property::create([
        'title' => 'Modern City Apartment',
        'slug' => 'modern-city-apartment',
        'description' => 'A bright apartment near the city center.',
        'price' => 250000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 120,
        'type' => 'sale',
        'status' => 'available',
        'city' => 'Nairobi',
        'address' => 'Kilimani, Nairobi',
        'featured' => true,
        'category_id' => $category->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
        'is_active' => true,
    ]);

    $response = $this->get('/properties');

    $response->assertOk();
    $response->assertSee('Modern City Apartment');

    $response = $this->get('/properties/'.$property->slug);

    $response->assertOk();
    $response->assertSee('Modern City Apartment');
});
