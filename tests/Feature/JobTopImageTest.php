<?php

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('admin can post a job opening with an uploaded top image', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'name' => 'HR Director',
        'email' => 'hr.director@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post('/dashboard/posts', [
        'title' => 'Executive Property Manager',
        'type' => 'job',
        'category' => 'Operations',
        'status' => 'published',
        'job_location' => 'Bole, Addis Ababa',
        'job_type' => 'Full-time',
        'salary_range' => 'ETB 30,000 - 45,000',
        'experience_level' => '3+ years',
        'education_level' => "Bachelor's Degree",
        'apply_link' => 'https://example.com/apply/exec-manager',
        'application_deadline' => now()->addMonth()->format('Y-m-d'),
        'content' => 'We are seeking an experienced Executive Property Manager to oversee luxury properties.',
        'requirements' => "Strong leadership\nClient relationship management",
        'image' => UploadedFile::fake()->image('office-header.jpg', 1200, 600),
    ]);

    $response->assertRedirect('/dashboard');

    $job = BlogPost::query()->where('title', 'Executive Property Manager')->first();
    expect($job)->not->toBeNull();
    expect($job->type)->toBe('job');
    expect($job->image_path)->not->toBeNull();
    expect($job->image_url)->toContain('storage/'.$job->image_path);

    Storage::disk('public')->assertExists($job->image_path);

    // Verify rendered on careers page with top image
    $careersResponse = $this->get('/careers');
    $careersResponse->assertOk()
        ->assertSee('Executive Property Manager')
        ->assertSee($job->image_url, false)
        ->assertSee('Now Hiring');
});

test('admin can post a job opening with a luxury preset top image', function () {
    $admin = User::factory()->create([
        'name' => 'Admin Recruiter',
        'email' => 'recruiter@example.com',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post('/dashboard/posts', [
        'title' => 'Commercial Leasing Specialist',
        'type' => 'job',
        'category' => 'Commercial',
        'status' => 'published',
        'job_location' => 'Kazanchis, Addis Ababa',
        'job_type' => 'Full-time',
        'content' => 'Join our prime commercial leasing division in downtown Addis Ababa.',
        'image_preset' => 'luxury-towers',
    ]);

    $response->assertRedirect('/dashboard');

    $job = BlogPost::query()->where('title', 'Commercial Leasing Specialist')->first();
    expect($job)->not->toBeNull();
    expect($job->image_path)->toBe('images/luxury/luxury-towers.jpg');
    expect($job->image_url)->toContain('images/luxury/luxury-towers.jpg');

    $this->get('/careers')
        ->assertOk()
        ->assertSee('Commercial Leasing Specialist')
        ->assertSee('images/luxury/luxury-towers.jpg', false);
});

test('admin can update a job opening with a new top image or remove it', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $job = BlogPost::create([
        'user_id' => $admin->id,
        'title' => 'Site Supervisor',
        'slug' => 'site-supervisor',
        'type' => 'job',
        'status' => 'published',
        'content' => 'Responsible for daily site management and safety compliance.',
        'image_path' => 'images/luxury/central-plaza.jpg',
    ]);

    expect($job->image_url)->toContain('images/luxury/central-plaza.jpg');

    // Update with custom uploaded image
    $response = $this->actingAs($admin)->put("/dashboard/posts/{$job->id}", [
        'title' => 'Senior Site Supervisor',
        'type' => 'job',
        'status' => 'published',
        'content' => 'Responsible for high-level site management and safety compliance across all construction projects.',
        'image' => UploadedFile::fake()->image('new-banner.jpg'),
    ]);

    $response->assertRedirect('/dashboard');
    $job->refresh();

    expect($job->title)->toBe('Senior Site Supervisor');
    expect($job->image_path)->not->toBe('images/luxury/central-plaza.jpg');
    expect($job->image_url)->toContain('storage/'.$job->image_path);
    Storage::disk('public')->assertExists($job->image_path);

    // Update with remove_image
    $removeResponse = $this->actingAs($admin)->put("/dashboard/posts/{$job->id}", [
        'title' => 'Senior Site Supervisor',
        'type' => 'job',
        'status' => 'published',
        'content' => 'Responsible for high-level site management and safety compliance across all construction projects.',
        'remove_image' => 1,
    ]);

    $removeResponse->assertRedirect('/dashboard');
    $job->refresh();

    expect($job->image_path)->toBeNull();
    expect($job->image_url)->toBeNull();
});

test('guest or agent cannot post job openings', function () {
    $guestResponse = $this->post('/dashboard/posts', [
        'title' => 'Unauthorized Job',
        'type' => 'job',
        'content' => 'This should be blocked.',
    ]);
    $guestResponse->assertRedirect('/login');

    $agentUser = User::factory()->create(['role' => 'agent']);
    $agentResponse = $this->actingAs($agentUser)->post('/dashboard/posts', [
        'title' => 'Agent Job',
        'type' => 'job',
        'content' => 'Agents do not have create content permission.',
    ]);
    $agentResponse->assertForbidden();
});

test('home page renders professional decorated hiring job card for job posts', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $job = BlogPost::create([
        'user_id' => $admin->id,
        'title' => 'Lead Architectural Designer',
        'slug' => 'lead-architectural-designer',
        'type' => 'job',
        'category' => 'Architecture',
        'status' => 'published',
        'job_location' => 'Bole Atlas, Addis Ababa',
        'job_type' => 'Full-time',
        'salary_range' => 'ETB 40,000 - 60,000',
        'experience_level' => '4+ years',
        'application_deadline' => now()->addDays(20),
        'apply_link' => 'https://example.com/apply/architect',
        'published_at' => now()->subHour(),
        'content' => 'Join our elite luxury design team shaping the skyline of modern Ethiopia.',
        'image_path' => 'images/luxury/hero-skyline.jpg',
    ]);

    $response = $this->get('/');
    $response->assertOk()
        ->assertSee('Lead Architectural Designer')
        ->assertSee('We Are')
        ->assertSee('Architecture')
        ->assertSee('Full-time')
        ->assertSee('ETB 40,000 - 60,000')
        ->assertSee('4+ years')
        ->assertSee('Bole Atlas, Addis Ababa')
        ->assertSee('Apply Now')
        ->assertSee('View All Careers')
        ->assertSee('images/luxury/hero-skyline.jpg', false);
});
