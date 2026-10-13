<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationController extends Controller
{
    /**
     * Store a newly submitted job application.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! auth()->check()) {
            return $this->redirectGuestToSignup(
                $request,
                route('careers'),
                'Create a client account or sign in before submitting a job application.'
            );
        }

        $validated = $request->validate([
            'job_id' => ['nullable', 'integer', 'exists:blog_posts,id'],
            'job_title' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'string', 'max:100'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'portfolio_url' => ['nullable', 'string', 'max:500'],
            'expected_salary' => ['nullable', 'string', 'max:100'],
            'available_start_date' => ['nullable', 'string', 'max:100'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'resume' => ['required', 'file', 'mimes:pdf,docx,doc', 'max:10240'],
        ], [
            'resume.required' => 'Please upload your resume / CV document.',
            'resume.mimes' => 'The resume must be a PDF or Word document (.pdf or .docx).',
            'resume.max' => 'The resume file may not be greater than 10MB in size.',
        ]);

        $resumeFile = $request->file('resume');
        $resumeOriginalName = $resumeFile->getClientOriginalName();
        $resumeMimeType = $resumeFile->getClientMimeType() ?: $resumeFile->getMimeType();
        $resumeSize = $resumeFile->getSize();
        $resumePath = $resumeFile->store('resumes', 'public');

        $jobTitle = $validated['job_title'];
        if (! empty($validated['job_id'])) {
            $post = BlogPost::find($validated['job_id']);
            if ($post && $post->type === 'job') {
                $jobTitle = $post->title;
            }
        }

        $application = JobApplication::create([
            'blog_post_id' => $validated['job_id'] ?? null,
            'job_title' => $jobTitle,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'current_location' => $validated['current_location'] ?? null,
            'experience_years' => $validated['experience_years'] ?? null,
            'education_level' => $validated['education_level'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'expected_salary' => $validated['expected_salary'] ?? null,
            'available_start_date' => $validated['available_start_date'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'resume_path' => $resumePath,
            'resume_original_name' => $resumeOriginalName,
            'resume_mime_type' => $resumeMimeType,
            'resume_size' => $resumeSize,
            'status' => 'pending',
        ]);

        $successMessage = 'Your application for "'.$application->job_title.'" has been submitted successfully! Our recruitment team will review your qualifications.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'application_id' => $application->id,
                'candidate_name' => $application->full_name,
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Download the applicant resume file (Admin only).
     */
    public function download(JobApplication $application): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required to download candidate resumes.');
        }

        if (! Storage::disk('public')->exists($application->resume_path)) {
            return back()->with('error', 'The requested resume file was not found in storage.');
        }

        return Storage::disk('public')->download(
            $application->resume_path,
            $application->resume_original_name
        );
    }

    /**
     * Update application status and optional notes (Admin only).
     */
    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,shortlisted,rejected,hired'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $application->admin_notes,
        ]);

        return redirect()->to(route('dashboard', ['section' => 'job-applications']))
            ->with('success', 'Application for "'.$application->full_name.'" status updated to '.ucfirst($validated['status']).'.');
    }

    /**
     * Delete a job application (Admin only).
     */
    public function destroy(JobApplication $application): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isFinanceStaff()) {
            abort(403, 'Admin access required.');
        }

        if ($application->resume_path && Storage::disk('public')->exists($application->resume_path)) {
            Storage::disk('public')->delete($application->resume_path);
        }

        $candidateName = $application->full_name;
        $application->delete();

        return redirect()->to(route('dashboard', ['section' => 'job-applications']))
            ->with('success', 'Job application from "'.$candidateName.'" was removed successfully.');
    }
}
