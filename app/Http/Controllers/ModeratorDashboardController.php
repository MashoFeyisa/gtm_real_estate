<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'reports.handle',
            'properties.merge',
        ]);

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $hasModeratorPerm)) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        // 1. Listings review queue
        $listingsQueue = Listing::query()
            ->with(['property.agent', 'property.user'])
            ->latest()
            ->get();

        // 2. Incident & Fraud Reports queue
        $reportsQueue = PropertyReport::query()
            ->with(['property.user', 'property.agent', 'user'])
            ->latest()
            ->get();

        // 3. User Reviews queue
        $reviewsQueue = PropertyReview::query()
            ->with(['property', 'user'])
            ->latest()
            ->get();

        // 4. Duplicate listing candidates (properties sharing title or city/subcity & price)
        $duplicateCandidates = Property::query()
            ->select('title', 'city', DB::raw('count(*) as count'), DB::raw('GROUP_CONCAT(id) as property_ids'))
            ->groupBy('title', 'city')
            ->having('count', '>', 1)
            ->take(15)
            ->get();

        $allProperties = Property::query()->latest()->take(50)->get(['id', 'title', 'price', 'city', 'status']);

        $stats = [
            'pending_listings' => $listingsQueue->where('status', 'pending_review')->count(),
            'published_listings' => $listingsQueue->where('status', 'published')->count(),
            'rejected_listings' => $listingsQueue->where('status', 'rejected')->count(),
            'pending_reports' => $reportsQueue->where('status', 'pending')->count(),
            'investigating_reports' => $reportsQueue->where('status', 'investigating')->count(),
            'resolved_reports' => $reportsQueue->where('status', 'resolved')->count(),
            'fraud_reports' => $reportsQueue->where('is_fraud_flagged', true)->count(),
            'total_reviews' => $reviewsQueue->count(),
            'hidden_reviews' => $reviewsQueue->where('status', 'hidden')->count(),
            'duplicate_clusters' => $duplicateCandidates->count(),
        ];

        return view('dashboards.moderator', [
            'user' => $user,
            'stats' => $stats,
            'listingsQueue' => $listingsQueue,
            'reportsQueue' => $reportsQueue,
            'reviewsQueue' => $reviewsQueue,
            'duplicateCandidates' => $duplicateCandidates,
            'allProperties' => $allProperties,
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

        if ($listing->property) {
            $listing->property->update([
                'status' => $validated['status'],
                'is_active' => in_array($validated['status'], ['published', 'approved'], true),
            ]);
        }

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

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('reports.handle') && ! $user->hasPermission('listings.review') && ! $user->hasPermission('reports.resolve'))) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:investigating,resolved,dismissed,pending'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
            'action_type' => ['nullable', 'string', 'in:unpublish_listing,warn_user,flag_fraud,none'],
        ]);

        $oldStatus = $report->status;
        $actionType = $validated['action_type'] ?? 'none';

        if ($actionType === 'unpublish_listing' && $report->property) {
            $report->property->update([
                'status' => 'paused',
                'is_active' => false,
            ]);
            Listing::where('property_id', $report->property->id)->update(['status' => 'paused']);
            $report->action_taken = 'listing_unpublished';
        } elseif ($actionType === 'warn_user') {
            $targetUser = $report->property?->user ?? $report->property?->agent?->user;
            if ($targetUser) {
                $targetUser->update([
                    'is_warned' => true,
                    'warning_notes' => 'Warned on report #'.$report->id.': '.($validated['resolution_notes'] ?? 'Violation of marketplace content rules.'),
                ]);
            }
            $report->action_taken = 'user_warned';
        } elseif ($actionType === 'flag_fraud') {
            $report->is_fraud_flagged = true;
            $report->action_taken = 'fraud_flagged';
            if ($report->property) {
                $report->property->update([
                    'status' => 'paused',
                    'is_active' => false,
                ]);
            }
        }

        $report->status = $validated['status'];
        if ($validated['resolution_notes'] ?? null) {
            $report->resolution_notes = $validated['resolution_notes'];
        }
        if (in_array($validated['status'], ['resolved', 'dismissed'], true)) {
            $report->resolved_by = $user->id;
        }
        $report->save();

        AuditLog::log(
            user: $user,
            action: 'report.moderation_decision',
            entity: $report,
            oldValue: ['status' => $oldStatus],
            newValue: [
                'status' => $report->status,
                'action_taken' => $report->action_taken,
                'is_fraud_flagged' => $report->is_fraud_flagged,
            ],
            reason: $validated['resolution_notes'] ?? 'Moderator triaged report with action '.$actionType,
            request: $request
        );

        return back()->with('success', "Report #{$report->id} updated to ".strtoupper($report->status).($actionType !== 'none' ? " with action [{$actionType}]" : '').'.');
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

    public function deleteReview(Request $request, PropertyReview $review): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('content.moderate'))) {
            abort(403, 'Permission denied: Content Moderation desk access required.');
        }

        $reviewId = $review->id;
        $review->delete();

        AuditLog::log(
            user: $user,
            action: 'review.deleted',
            entity: $review,
            oldValue: ['id' => $reviewId],
            newValue: null,
            reason: 'Moderator removed inappropriate review content',
            request: $request
        );

        return back()->with('success', "Review #{$reviewId} permanently removed.");
    }

    public function mergeListings(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'moderator' && ! $user->isAdmin() && ! $user->hasPermission('properties.merge'))) {
            abort(403, 'Permission denied: properties.merge');
        }

        $validated = $request->validate([
            'primary_property_id' => ['required', 'exists:properties,id'],
            'duplicate_property_id' => ['required', 'exists:properties,id', 'different:primary_property_id'],
        ]);

        $primary = Property::findOrFail($validated['primary_property_id']);
        $duplicate = Property::findOrFail($validated['duplicate_property_id']);

        DB::transaction(function () use ($primary, $duplicate, $user) {
            Inquiry::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);
            PropertyOffer::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);
            Order::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);
            PropertyReport::where('property_id', $duplicate->id)->update(['property_id' => $primary->id]);

            $duplicate->update([
                'status' => 'archived',
                'is_active' => false,
                'title' => $duplicate->title.' [Merged into #'.$primary->id.']',
            ]);

            AuditLog::log(
                user: $user,
                action: 'property.moderator_merged',
                entity: $duplicate,
                oldValue: ['id' => $duplicate->id, 'title' => $duplicate->title],
                newValue: ['merged_into_id' => $primary->id, 'status' => 'archived'],
                reason: "Moderator merged duplicate listing #{$duplicate->id} into primary #{$primary->id}",
            );
        });

        return back()->with('success', "Listing #{$duplicate->id} successfully merged into master listing #{$primary->id}.");
    }
}
