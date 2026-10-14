<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\User;
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
            'verifications.identity',
            'verifications.identity_approve',
            'verifications.company',
            'verifications.company_approve',
            'verifications.developer',
            'verifications.developer_approve',
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

        // 2. Identity Verification (KYC) queue
        $identitiesQueue = User::query()
            ->where(function ($q) {
                $q->whereIn('id_verification_status', ['pending', 'approved', 'rejected'])
                    ->orWhereNotNull('id_card_path');
            })
            ->latest()
            ->get();

        // 3. Properties Trust Levels & Badges (Levels 0-5)
        $propertiesQueue = Property::query()
            ->with(['agent', 'user'])
            ->latest()
            ->take(50)
            ->get();

        // 4. Company / Agency licenses queue
        $companiesQueue = Agent::query()
            ->whereNotNull('license_number')
            ->latest()
            ->get();

        // 5. Developer registrations queue
        $developersQueue = Project::query()
            ->latest()
            ->get();

        $stats = [
            'pending_docs' => $documentsQueue->where('status', 'pending')->count(),
            'verified_docs' => $documentsQueue->where('status', 'verified')->count(),
            'rejected_docs' => $documentsQueue->where('status', 'rejected')->count(),
            'pending_identities' => $identitiesQueue->where('id_verification_status', 'pending')->count(),
            'verified_identities' => $identitiesQueue->where('id_verification_status', 'approved')->count(),
            'pending_companies' => $companiesQueue->where('license_verified', false)->count(),
            'verified_companies' => $companiesQueue->where('license_verified', true)->count(),
            'pending_developers' => $developersQueue->where('developer_verified', false)->count(),
            'verified_developers' => $developersQueue->where('developer_verified', true)->count(),
            'audited_properties' => $propertiesQueue->where('verification_level', '>=', 3)->count(),
        ];

        return view('dashboards.verification_officer', [
            'user' => $user,
            'stats' => $stats,
            'documentsQueue' => $documentsQueue,
            'identitiesQueue' => $identitiesQueue,
            'propertiesQueue' => $propertiesQueue,
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

    public function identityDecision(Request $request, User $targetUser): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'verification_officer' && ! $user->isAdmin() && ! $user->hasPermission('verifications.identity') && ! $user->hasPermission('verifications.identity_approve'))) {
            abort(403, 'Permission denied: verifications.identity permission required.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject,request_photo'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $targetUser->id_verification_status;

        if ($validated['decision'] === 'approve') {
            $targetUser->update([
                'id_verification_status' => 'approved',
                'id_verified_at' => now(),
                'id_rejection_reason' => null,
            ]);
        } elseif ($validated['decision'] === 'reject') {
            $targetUser->update([
                'id_verification_status' => 'rejected',
                'id_rejection_reason' => $validated['rejection_reason'] ?? 'Submitted identity document does not meet official standards.',
            ]);
        } else {
            // request_photo
            $targetUser->update([
                'id_verification_status' => 'pending',
                'id_rejection_reason' => 'Clearer photo requested: '.($validated['rejection_reason'] ?? 'Please re-upload a high-resolution, unblurred photo of your ID.'),
            ]);
        }

        AuditLog::log(
            user: $user,
            action: 'user.identity_verification',
            entity: $targetUser,
            oldValue: ['id_verification_status' => $oldStatus],
            newValue: [
                'id_verification_status' => $targetUser->id_verification_status,
                'id_rejection_reason' => $targetUser->id_rejection_reason,
            ],
            reason: $validated['rejection_reason'] ?? 'Verification officer evaluated user KYC identity',
            request: $request
        );

        return back()->with('success', "Identity verification for {$targetUser->name} updated to {$targetUser->id_verification_status}.");
    }

    public function propertyTrustLevel(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'verification_officer' && ! $user->isAdmin() && ! $user->hasPermission('verifications.manage'))) {
            abort(403, 'Permission denied: verifications.manage permission required.');
        }

        $validated = $request->validate([
            'verification_level' => ['required', 'integer', 'min:0', 'max:5'],
            'verification_status' => ['required', 'string', 'in:unverified,pending,verified,rejected'],
            'verified_badges' => ['nullable', 'array'],
            'verification_notes' => ['nullable', 'string', 'max:2000'],
            'title_deed_number' => ['nullable', 'string', 'max:100'],
            'cadastre_code' => ['nullable', 'string', 'max:100'],
        ]);

        $oldLevel = $property->verification_level;

        $property->update([
            'verification_level' => $validated['verification_level'],
            'verification_status' => $validated['verification_status'],
            'verified_badges' => $validated['verified_badges'] ?? [],
            'verification_notes' => $validated['verification_notes'] ?? $property->verification_notes,
            'title_deed_number' => $validated['title_deed_number'] ?? $property->title_deed_number,
            'cadastre_code' => $validated['cadastre_code'] ?? $property->cadastre_code,
            'verified_at' => $validated['verification_status'] === 'verified' ? now() : null,
            'verified_by' => $user->id,
        ]);

        AuditLog::log(
            user: $user,
            action: 'property.trust_level_assigned',
            entity: $property,
            oldValue: ['verification_level' => $oldLevel],
            newValue: [
                'verification_level' => $property->verification_level,
                'verification_status' => $property->verification_status,
                'verified_badges' => $property->verified_badges,
            ],
            reason: $validated['verification_notes'] ?? 'Verification officer assigned Trust Level & Badges',
            request: $request
        );

        return back()->with('success', "Property #{$property->id} updated to Level {$property->verification_level} ({$property->verification_status}).");
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
