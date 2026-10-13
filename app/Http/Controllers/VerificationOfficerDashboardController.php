<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationOfficerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasVerificationPerm = $user?->hasAnyPermission([
            'documents.review_property',
            'verifications.manage',
            'verifications.identity_approve',
            'verifications.company_approve',
            'verification_jobs.accept',
        ]);

        if (! $user || (! in_array($user->role, ['verification_officer', 'verification_company'], true) && ! $user->isAdmin() && ! $hasVerificationPerm)) {
            abort(403, 'Permission denied: Verification Officer desk access required.');
        }

        // 1. Property Documents verification queue
        $documentsQueue = PropertyDocument::query()
            ->with(['property', 'uploader'])
            ->latest()
            ->get();

        // 2. Company / Agency licenses queue
        $companiesQueue = Agent::query()
            ->whereNotNull('license_number')
            ->latest()
            ->get();

        // 3. Developer registrations queue
        $developersQueue = Project::query()
            ->latest()
            ->get();

        $stats = [
            'pending_docs' => $documentsQueue->where('status', 'pending')->count(),
            'verified_docs' => $documentsQueue->where('status', 'verified')->count(),
            'rejected_docs' => $documentsQueue->where('status', 'rejected')->count(),
            'pending_companies' => $companiesQueue->where('license_verified', false)->count(),
            'verified_companies' => $companiesQueue->where('license_verified', true)->count(),
            'pending_developers' => $developersQueue->where('developer_verified', false)->count(),
            'verified_developers' => $developersQueue->where('developer_verified', true)->count(),
        ];

        return view('dashboards.verification_officer', [
            'user' => $user,
            'stats' => $stats,
            'documentsQueue' => $documentsQueue,
            'companiesQueue' => $companiesQueue,
            'developersQueue' => $developersQueue,
        ]);
    }

    public function documentDecision(Request $request, PropertyDocument $document): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'verification_officer' && ! $user->isAdmin() && ! $user->hasPermission('documents.review_property'))) {
            abort(403, 'Permission denied: Verification Officer desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:verified,rejected,pending'],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $document->status;
        $document->update([
            'status' => $validated['status'],
            'verification_notes' => $validated['verification_notes'] ?? $document->verification_notes,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
            'verified_by' => $user->id,
        ]);

        AuditLog::log(
            user: $user,
            action: 'document.verification_decision',
            entity: $document,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $document->status, 'notes' => $document->verification_notes],
            reason: $validated['verification_notes'] ?? 'Verification officer updated document status',
            request: $request
        );

        return back()->with('success', "Document #{$document->id} ({$document->title}) marked as {$document->status}.");
    }

    public function companyDecision(Request $request, Agent $agent): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'verification_officer' && ! $user->isAdmin() && ! $user->hasPermission('verifications.company'))) {
            abort(403, 'Permission denied: Verification Officer desk access required.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:verify,revoke'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $agent->license_verified;
        $newStatus = $validated['decision'] === 'verify';
        $agent->update([
            'license_verified' => $newStatus,
        ]);

        AuditLog::log(
            user: $user,
            action: 'company.license_verification',
            entity: $agent,
            oldValue: ['license_verified' => $oldStatus],
            newValue: ['license_verified' => $newStatus],
            reason: $validated['notes'] ?? 'Verification officer decided company license verification',
            request: $request
        );

        return back()->with('success', $newStatus ? "Agency {$agent->name} verified." : "Verification revoked for {$agent->name}.");
    }

    public function developerDecision(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'verification_officer' && ! $user->isAdmin() && ! $user->hasPermission('verifications.developer'))) {
            abort(403, 'Permission denied: Verification Officer desk access required.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:verify,revoke'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $project->developer_verified;
        $newStatus = $validated['decision'] === 'verify';
        $project->update([
            'developer_verified' => $newStatus,
        ]);

        AuditLog::log(
            user: $user,
            action: 'developer.project_verification',
            entity: $project,
            oldValue: ['developer_verified' => $oldStatus],
            newValue: ['developer_verified' => $newStatus],
            reason: $validated['notes'] ?? 'Verification officer decided developer project verification',
            request: $request
        );

        return back()->with('success', $newStatus ? "Developer project {$project->name} verified." : "Verification revoked for project {$project->name}.");
    }
}
