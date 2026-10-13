<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Moderation decisions from the listing moderation workflow
 * (Draft -> Submitted -> Moderation -> Approved -> Published, and
 * Rejected -> Correction -> Resubmitted) plus content moderation.
 */
class ModerationController extends Controller
{
    public function listingDecision(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeModerator('listings.review');

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,publish,reject,suspend'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = match ($validated['decision']) {
            'approve' => 'approved',
            'publish' => 'published',
            'reject' => 'rejected',
            'suspend' => 'paused',
        };

        $listing->update([
            'status' => $status,
            'published_at' => $validated['decision'] === 'publish' && ! $listing->published_at ? now() : $listing->published_at,
        ]);

        $labels = [
            'approve' => 'approved',
            'publish' => 'published',
            'reject' => 'rejected and returned for correction',
            'suspend' => 'suspended',
        ];

        return back()->with('success', "Listing '{$listing->title}' {$labels[$validated['decision']]}.");
    }

    public function reviewDecision(Request $request, PropertyReview $propertyReview): RedirectResponse
    {
        $this->authorizeModerator('content.moderate');

        $validated = $request->validate([
            'decision' => ['required', 'in:hide,approve'],
        ]);

        $propertyReview->update([
            'status' => $validated['decision'] === 'hide' ? 'hidden' : 'published',
        ]);

        return back()->with('success', $validated['decision'] === 'hide'
            ? 'Review hidden from public display.'
            : 'Review restored to public display.');
    }

    private function authorizeModerator(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->isAdmin() || ($user->role === 'moderator' && $user->hasPermission($permission))),
            403
        );
    }
}
