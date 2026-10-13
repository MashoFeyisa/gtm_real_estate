<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeratorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasModeratorPerm = $user?->hasAnyPermission([
            'listings.review',
            'content.moderate',
            'moderation.manage',
            'reviews.moderate',
            'reports.resolve',
        ]);

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $hasModeratorPerm)) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        // 1. Listings review queue
        $listingsQueue = Listing::query()
            ->with(['property.agent'])
            ->latest()
            ->get();

        // 2. Incident & Fraud Reports queue
        $reportsQueue = PropertyReport::query()
            ->with('property')
            ->latest()
            ->get();

        // 3. User Reviews queue
        $reviewsQueue = PropertyReview::query()
            ->with(['property', 'user'])
            ->latest()
            ->get();

        $stats = [
            'pending_listings' => $listingsQueue->where('status', 'pending_review')->count(),
            'published_listings' => $listingsQueue->where('status', 'published')->count(),
            'rejected_listings' => $listingsQueue->where('status', 'rejected')->count(),
            'pending_reports' => $reportsQueue->where('status', 'pending')->count(),
            'investigating_reports' => $reportsQueue->where('status', 'investigating')->count(),
            'resolved_reports' => $reportsQueue->where('status', 'resolved')->count(),
            'total_reviews' => $reviewsQueue->count(),
            'hidden_reviews' => $reviewsQueue->where('status', 'hidden')->count(),
        ];

        return view('dashboards.moderator', [
            'user' => $user,
            'stats' => $stats,
            'listingsQueue' => $listingsQueue,
            'reportsQueue' => $reportsQueue,
            'reviewsQueue' => $reviewsQueue,
        ]);
    }

    public function listingDecision(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('listings.review') && ! $user->hasPermission('content.moderate'))) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:published,approved,rejected,paused,draft'],
            'moderation_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $listing->status;
        $updates = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'published' && ! $listing->published_at) {
            $updates['published_at'] = now();
        }

        $listing->update($updates);

        AuditLog::log(
            user: $user,
            action: 'listing.moderation_decision',
            entity: $listing,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $listing->status],
            reason: $validated['moderation_notes'] ?? 'Moderator evaluated listing quality & status',
            request: $request
        );

        return back()->with('success', "Listing '{$listing->title}' status set to ".strtoupper($listing->status).'.');
    }

    public function reportDecision(Request $request, PropertyReport $report): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('reports.handle') && ! $user->hasPermission('listings.review'))) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:investigating,resolved,dismissed,pending'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $report->status;
        $report->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? $report->resolution_notes,
            'resolved_at' => in_array($validated['status'], ['resolved', 'dismissed'], true) ? now() : null,
            'resolved_by' => $user->id,
        ]);

        AuditLog::log(
            user: $user,
            action: 'report.moderation_decision',
            entity: $report,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $report->status],
            reason: $validated['resolution_notes'] ?? 'Moderator triaged report',
            request: $request
        );

        return back()->with('success', "Report #{$report->id} updated to ".strtoupper($report->status).'.');
    }

    public function reviewDecision(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('reviews.moderate') && ! $user->hasPermission('content.moderate'))) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:published,hidden,approved,flagged'],
        ]);

        $oldStatus = $review->status;
        $review->update([
            'status' => $validated['status'],
        ]);

        AuditLog::log(
            user: $user,
            action: 'review.moderation_decision',
            entity: $review,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $review->status],
            reason: 'Moderator moderated review visibility',
            request: $request
        );

        return back()->with('success', "Review visibility set to {$review->status}.");
    }
}
