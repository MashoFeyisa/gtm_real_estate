<?php

use App\Mail\InquiryReceived;
use App\Models\Agent;
use App\Models\AttendanceRecord;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\Property;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('admin can log in with the seeded credentials', function () {
    User::query()->create([
        'name' => 'Admin User',
        'email' => 'admin@realestate.com',
        'password' => Hash::make('secret123'),
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

test('attendance link is removed from the home and admin dashboard navigation', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'attendance-nav-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->get('/')->assertOk()->assertDontSee('Attendance');
    $this->actingAs($admin)->get('/dashboard')->assertOk()->assertDontSee('Attendance');
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

test('dashboard loads workers with their latest attendance record', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'dashboard-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $worker = Worker::create([
        'name' => 'Selam Bekele',
        'email' => 'selam@example.com',
        'department' => 'Operations',
        'phone' => '+251900000001',
    ]);

    AttendanceRecord::create([
        'worker_id' => $worker->id,
        'status' => 'late',
        'recorded_at' => now('Africa/Addis_Ababa')->subHour(),
        'check_in_time' => now('Africa/Addis_Ababa')->subHour()->format('H:i:s'),
    ]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk();
});

test('attendance status is calculated using ethiopia time', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin-attendance@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    $worker = Worker::create([
        'name' => 'Selam Bekele',
        'email' => 'selam@example.com',
        'department' => 'Operations',
        'phone' => '+251900000001',
    ]);

    $this->travelTo(now('Africa/Addis_Ababa')->setTime(14, 15));
    $this->post('/dashboard/workers/'.$worker->id.'/record-attendance');
    expect(AttendanceRecord::latest()->first()->status)->toBe('present');

    $this->travelTo(now('Africa/Addis_Ababa')->setTime(15, 45));
    $this->post('/dashboard/workers/'.$worker->id.'/record-attendance');
    expect(AttendanceRecord::latest()->first()->status)->toBe('late');

    $this->travelTo(now('Africa/Addis_Ababa')->setTime(16, 31));
    $this->post('/dashboard/workers/'.$worker->id.'/record-attendance');
    expect(AttendanceRecord::latest()->first()->status)->toBe('absent');
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
        ->assertSee('workers')
        ->assertSee('users')
        ->assertSee('DOMContentLoaded');
});

test('admin can add a worker and they appear in the attendance list automatically', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'worker-admin@example.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/dashboard/workers', [
            'name' => 'Hana Tadesse',
            'email' => 'hana@example.com',
            'department' => 'Sales',
            'phone' => '+251911223344',
        ])
        ->assertRedirect('/dashboard')
        ->assertSessionHas('success', 'Worker added successfully.');

    $this->assertDatabaseHas('workers', ['email' => 'hana@example.com', 'department' => 'Sales']);

    $response = $this->get('/attendance');
    $response->assertOk();
    $response->assertSee('Hana Tadesse');
    $response->assertSee('Pending');
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

test('public website pages render for key routes', function () {
    $this->get('/about')->assertOk();
    $this->get('/services')->assertOk();
    $this->get('/contact')->assertOk();
    $this->get('/privacy-policy')->assertOk();
    $this->get('/terms')->assertOk();
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
