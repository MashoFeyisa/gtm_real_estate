<?php

namespace Database\Factories;

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_title' => fake()->jobTitle(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+251 91 1'.fake()->numerify('######'),
            'current_location' => fake()->city(),
            'experience_years' => '3-5 years',
            'education_level' => "Bachelor's Degree",
            'portfolio_url' => fake()->url(),
            'expected_salary' => 'ETB 30,000 - 45,000',
            'available_start_date' => 'Within 2 weeks',
            'cover_letter' => fake()->paragraphs(2, true),
            'resume_path' => 'resumes/sample_resume.pdf',
            'resume_original_name' => 'Resume_'.fake()->lastName().'.pdf',
            'resume_mime_type' => 'application/pdf',
            'resume_size' => 1024 * 512,
            'status' => 'pending',
            'admin_notes' => null,
        ];
    }
}
