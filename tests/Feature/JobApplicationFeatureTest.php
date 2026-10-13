<?php

use App\Models\BlogPost;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('guest must sign in before applying for a job', function () {
    $response = $this->post(route('job-applications.store'));

    $response->assertRedirect(route('register'))
        ->assertSessionHas('signup_prompt', 'Create a client account or sign in before submitting a job application.');
});

test('candidate can submit job application with a valid PDF resume', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $job = BlogPost::create([
        'user_id' => $admin->id,
        'title' => 'Senior Property Consultant',
        'slug' => 'senior-property-consultant-'.uniqid(),
        'content' => 'Join our sales team to advise premium real estate clients.',
        'type' => 'job',
        'status' => 'published',
        'published_at' => now(),
        'job_type' => 'Full-time',
        'job_location' => 'Addis Ababa',
    ]);

    $pdfFile = UploadedFile::fake()->create('my_cv.pdf', 500, 'application/pdf');

    $response = $this->actingAs(User::factory()->create(['role' => 'client']))
        ->post(route('job-applications.store'), [
            'job_id' => $job->id,
            'job_title' => $job->title,
            'full_name' => 'Abebe Kebede',
            'email' => 'abebe@example.com',
            'phone' => '+251 91 123 4567',
            'current_location' => 'Addis Ababa, Bole',
            'experience_years' => '3-5 years',
            'education_level' => "Bachelor's Degree",
            'portfolio_url' => 'https://linkedin.com/in/abebekebede',
            'expected_salary' => 'ETB 35,000 / month',
            'available_start_date' => 'Within 2 weeks',
            'cover_letter' => 'I have 4 years of experience selling luxury residential properties.',
            'resume' => $pdfFile,
        ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('job_applications', [
        'blog_post_id' => $job->id,
        'job_title' => 'Senior Property Consultant',
        'full_name' => 'Abebe Kebede',
        'email' => 'abebe@example.com',
        'phone' => '+251 91 123 4567',
        'current_location' => 'Addis Ababa, Bole',
        'status' => 'pending',
    ]);

    $application = JobApplication::first();
    expect($application)->not->toBeNull();
    expect($application->is_pdf)->toBeTrue();
    expect($application->is_docx)->toBeFalse();
    Storage::disk('public')->assertExists($application->resume_path);
});

test('candidate can submit job application with a valid DOCX resume via AJAX', function () {
    $docxFile = UploadedFile::fake()->create('resume.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $response = $this->actingAs(User::factory()->create(['role' => 'client']))
        ->postJson(route('job-applications.store'), [
            'job_title' => 'General Talent Pool',
            'full_name' => 'Sara Haile',
            'email' => 'sara@example.com',
            'phone' => '+251 92 987 6543',
            'current_location' => 'Hawassa',
            'experience_years' => '1-3 years',
            'education_level' => "Master's Degree",
            'cover_letter' => 'Interested in upcoming marketing and administrative positions.',
            'resume' => $docxFile,
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'candidate_name' => 'Sara Haile',
        ]);

    $application = JobApplication::where('email', 'sara@example.com')->first();
    expect($application)->not->toBeNull();
    expect($application->is_docx)->toBeTrue();
    expect($application->is_pdf)->toBeFalse();
    Storage::disk('public')->assertExists($application->resume_path);
});

test('candidate submission fails with invalid resume format', function () {
    $invalidFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

    $response = $this->actingAs(User::factory()->create(['role' => 'client']))
        ->post(route('job-applications.store'), [
            'job_title' => 'Civil Engineer',
            'full_name' => 'Dawit Mengistu',
            'email' => 'dawit@example.com',
            'phone' => '+251 93 456 7890',
            'resume' => $invalidFile,
        ]);

    $response->assertSessionHasErrors(['resume']);
    expect(JobApplication::count())->toBe(0);
});

test('candidate submission fails without resume', function () {
    $response = $this->actingAs(User::factory()->create(['role' => 'client']))
        ->post(route('job-applications.store'), [
            'job_title' => 'Civil Engineer',
            'full_name' => 'Dawit Mengistu',
            'email' => 'dawit@example.com',
            'phone' => '+251 93 456 7890',
        ]);

    $response->assertSessionHasErrors(['resume']);
});

test('admin can view applications in dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $application = JobApplication::factory()->create([
        'full_name' => 'Selamawit Tadesse',
        'job_title' => 'Lead Architect',
        'email' => 'selam@example.com',
        'status' => 'pending',
    ]);

    // View main dashboard
    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Selamawit Tadesse');
    $response->assertSee('Lead Architect');
    $response->assertSee('Job Applications');
    $response->assertSee('Recent Job Applications');

    // View with section query
    $sectionResponse = $this->actingAs($admin)->get(route('dashboard', ['section' => 'job-applications']));
    $sectionResponse->assertOk();
    $sectionResponse->assertSee('Selamawit Tadesse');
    $sectionResponse->assertSee('Job Applications &amp; Candidate Resumes', false);
    $sectionResponse->assertSee(route('dashboard.job-applications.download', $application));
});

test('admin can download candidate resume', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $file = UploadedFile::fake()->create('selam_cv.pdf', 300, 'application/pdf');
    $path = $file->store('resumes', 'public');

    $application = JobApplication::factory()->create([
        'resume_path' => $path,
        'resume_original_name' => 'selam_cv.pdf',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.job-applications.download', $application));

    $response->assertOk();
});

test('unauthenticated user cannot download candidate resume', function () {
    $application = JobApplication::factory()->create([
        'resume_path' => 'resumes/fake.pdf',
        'resume_original_name' => 'fake.pdf',
    ]);

    $response = $this->get(route('dashboard.job-applications.download', $application));

    $response->assertRedirect(route('login'));
});

test('admin can update job application status', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $application = JobApplication::factory()->create([
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->patch(route('dashboard.job-applications.status', $application), [
        'status' => 'shortlisted',
        'admin_notes' => 'Great portfolio, invite for initial interview.',
    ]);

    $response->assertRedirect(route('dashboard', ['section' => 'job-applications']));

    expect($application->fresh()->status)->toBe('shortlisted');
    expect($application->fresh()->admin_notes)->toBe('Great portfolio, invite for initial interview.');
});

test('admin can delete job application and its resume file is deleted', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $file = UploadedFile::fake()->create('delete_me.pdf', 200, 'application/pdf');
    $path = $file->store('resumes', 'public');

    $application = JobApplication::factory()->create([
        'resume_path' => $path,
    ]);

    Storage::disk('public')->assertExists($path);

    $response = $this->actingAs($admin)->delete(route('dashboard.job-applications.destroy', $application));

    $response->assertRedirect(route('dashboard', ['section' => 'job-applications']));

    expect(JobApplication::find($application->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('careers page displays open positions with apply trigger and modal component', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $job = BlogPost::create([
        'user_id' => $admin->id,
        'title' => 'Luxury Villa Consultant',
        'slug' => 'luxury-villa-consultant-'.uniqid(),
        'content' => 'High profile consulting for VIP clients.',
        'type' => 'job',
        'status' => 'published',
        'published_at' => now(),
        'job_location' => 'Addis Ababa',
    ]);

    $response = $this->get(route('careers'));

    $response->assertOk();
    $response->assertSee('Luxury Villa Consultant');
    $response->assertSee('Apply Now');
    $response->assertSee('job-application-modal');
    $response->assertSee('PDF');
    $response->assertSee('DOCX');
});
