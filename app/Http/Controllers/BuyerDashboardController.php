<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\OfferNegotiation;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyFavorite;
use App\Models\PropertyLike;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\SavedCollection;
use App\Models\SavedSearch;
use App\Models\UnitReservation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BuyerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual client dashboards.');
        }

        $hasBuyerPerm = $user?->hasAnyPermission([
            'properties.browse',
            'properties.search',
            'searches.save',
            'viewings.create',
            'viewings.book',
            'viewings.view',
            'offers.create',
            'offers.view',
            'properties.view',
            'property.view',
        ]);

        if ($user?->status === 'suspended' || ! $hasBuyerPerm) {
            abort(403, 'Permission denied: Property browsing access required.');
        }

        $likedProperties = PropertyLike::with('property.images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $favoritedProperties = PropertyFavorite::with('property.images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $collections = SavedCollection::with('properties.images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $savedSearches = $user->savedSearches()->latest()->get();

        $inquiries = Inquiry::query()
            ->with(['property.images', 'agent'])
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                    ->orWhere('user_id', $user->id);
            })
            ->latest()
            ->get();

        $viewings = PropertyViewing::query()
            ->with(['property.images', 'agent.user'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $offers = PropertyOffer::query()
            ->with(['property.images', 'negotiations' => fn ($q) => $q->latest()])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $reservations = UnitReservation::query()
            ->with(['project', 'unit'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        $documents = PropertyDocument::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $deals = Order::query()
            ->with(['property.images', 'agent'])
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email);
                if ($user->phone) {
                    $q->orWhere('phone', $user->phone);
                }
            })
            ->latest()
            ->get();

        // Compare items from session
        $comparedIds = array_map('intval', session()->get('compared_property_ids', []));
        $comparedProperties = ! empty($comparedIds)
            ? Property::with(['images', 'agent'])->whereIn('id', $comparedIds)->get()
            : collect();

        return view('dashboards.buyer', compact(
            'likedProperties',
            'favoritedProperties',
            'collections',
            'savedSearches',
            'inquiries',
            'viewings',
            'offers',
            'reservations',
            'documents',
            'deals',
            'comparedProperties'
        ));
    }

    public function storeSavedSearch(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['searches.save', 'properties.save'])) {
            abort(403, 'Permission denied: searches.save required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'property_category' => ['nullable', 'string', 'max:100'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'alert_frequency' => ['required', 'in:instant,daily,weekly,off'],
        ]);

        $search = $user->savedSearches()->create([
            'title' => $validated['title'],
            'criteria' => [
                'city' => $validated['city'] ?? null,
                'property_category' => $validated['property_category'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
            ],
            'alert_frequency' => $validated['alert_frequency'],
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.saved_search_created',
            entity: $search,
            newValue: $search->toArray(),
            request: $request
        );

        return back()->with('success', "Search alert '{$search->title}' saved successfully.");
    }

    public function updateSavedSearch(Request $request, SavedSearch $savedSearch): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['searches.save', 'properties.save'])) {
            abort(403, 'Permission denied: searches.save required.');
        }

        if ($savedSearch->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot modify another user\'s saved search.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'property_category' => ['nullable', 'string', 'max:100'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'alert_frequency' => ['required', 'in:instant,daily,weekly,off'],
        ]);

        $oldValues = $savedSearch->toArray();

        $savedSearch->update([
            'title' => $validated['title'],
            'criteria' => [
                'city' => $validated['city'] ?? null,
                'property_category' => $validated['property_category'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
            ],
            'alert_frequency' => $validated['alert_frequency'],
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.saved_search_updated',
            entity: $savedSearch,
            oldValue: $oldValues,
            newValue: $savedSearch->toArray(),
            request: $request
        );

        return back()->with('success', "Search alert '{$savedSearch->title}' updated successfully.");
    }

    public function destroySavedSearch(Request $request, SavedSearch $savedSearch): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['searches.save', 'properties.save'])) {
            abort(403, 'Permission denied: searches.save required.');
        }

        if ($savedSearch->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot delete another user\'s saved search.');
        }

        AuditLog::log(
            user: $user,
            action: 'buyer.saved_search_deleted',
            entity: $savedSearch,
            oldValue: $savedSearch->toArray(),
            request: $request
        );

        $savedSearch->delete();

        return back()->with('success', 'Search alert removed.');
    }

    public function rescheduleViewing(Request $request, PropertyViewing $viewing): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('viewings.update')) {
            abort(403, 'Permission denied: viewings.update required.');
        }

        if ($viewing->user_id !== $user->id && strtolower((string) $viewing->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot reschedule another user\'s viewing appointment.');
        }

        $validated = $request->validate([
            'viewing_date' => ['required', 'date', 'after_or_equal:today'],
            'viewing_time' => ['required', 'in:morning_09_11,afternoon_14_16,evening_17_19'],
            'buyer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = [
            'date' => $viewing->viewing_date,
            'time' => $viewing->viewing_time,
            'status' => $viewing->status,
        ];

        $viewing->update([
            'viewing_date' => $validated['viewing_date'],
            'viewing_time' => $validated['viewing_time'],
            'buyer_notes' => $validated['buyer_notes'] ?? $viewing->buyer_notes,
            'status' => 'requested',
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.viewing_rescheduled',
            entity: $viewing,
            oldValue: $oldValues,
            newValue: [
                'date' => $viewing->viewing_date,
                'time' => $viewing->viewing_time,
                'status' => $viewing->status,
            ],
            reason: $validated['buyer_notes'] ?? 'Buyer rescheduled inspection date',
            request: $request
        );

        return back()->with('success', "Viewing {$viewing->viewing_reference} rescheduled successfully.");
    }

    public function cancelViewing(Request $request, PropertyViewing $viewing): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('viewings.cancel')) {
            abort(403, 'Permission denied: viewings.cancel required.');
        }

        if ($viewing->user_id !== $user->id && strtolower((string) $viewing->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot cancel another user\'s viewing appointment.');
        }

        $oldStatus = $viewing->status;
        $viewing->update(['status' => 'cancelled']);

        AuditLog::log(
            user: $user,
            action: 'buyer.viewing_cancelled',
            entity: $viewing,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'cancelled'],
            request: $request
        );

        return back()->with('success', "Viewing {$viewing->viewing_reference} cancelled.");
    }

    public function withdrawOffer(Request $request, PropertyOffer $offer): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('offers.cancel')) {
            abort(403, 'Permission denied: offers.cancel required.');
        }

        if ($offer->user_id !== $user->id && strtolower((string) $offer->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot withdraw another user\'s offer.');
        }

        if (in_array($offer->status, ['accepted', 'completed'], true)) {
            return back()->with('error', 'Cannot withdraw an accepted offer.');
        }

        $oldStatus = $offer->status;
        $reason = $request->input('reason', 'Offer withdrawn by buyer.');

        $offer->update(['status' => 'withdrawn']);

        OfferNegotiation::create([
            'property_offer_id' => $offer->id,
            'sender_type' => 'buyer',
            'amount' => $offer->current_counter_amount ?? $offer->offered_price,
            'currency' => 'ETB',
            'message' => $reason,
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.offer_withdrawn',
            entity: $offer,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'withdrawn'],
            reason: $reason,
            request: $request
        );

        return back()->with('success', "Offer {$offer->offer_reference} withdrawn successfully.");
    }

    public function cancelReservation(Request $request, UnitReservation $reservation): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('reservations.cancel')) {
            abort(403, 'Permission denied: reservations.cancel required.');
        }

        if ($reservation->user_id !== $user->id && strtolower((string) $reservation->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot cancel another user\'s reservation.');
        }

        $oldStatus = $reservation->status;
        $reservation->update(['status' => 'cancelled']);

        if ($reservation->unit) {
            $reservation->unit->update(['status' => 'available']);
        }

        AuditLog::log(
            user: $user,
            action: 'buyer.reservation_cancelled',
            entity: $reservation,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'cancelled'],
            request: $request
        );

        return back()->with('success', "Reservation {$reservation->reservation_reference} cancelled.");
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('documents.create')) {
            abort(403, 'Permission denied: documents.create required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:proof_of_funds,national_id,employment_letter,tax_id,pre_approval,other'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
        ]);

        $file = $request->file('document_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $storedPath = $file->store('buyer-documents', 'public');

        $doc = PropertyDocument::create([
            'user_id' => $user->id,
            'property_id' => null,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'access_level' => 'private',
            'status' => 'verified',
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.document_uploaded',
            entity: $doc,
            newValue: ['title' => $doc->title, 'type' => $doc->document_type],
            request: $request
        );

        return back()->with('success', "Document '{$doc->title}' uploaded to your vault.");
    }

    public function destroyDocument(Request $request, PropertyDocument $document): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('documents.delete')) {
            abort(403, 'Permission denied: documents.delete required.');
        }

        if ($document->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot delete another user\'s document.');
        }

        AuditLog::log(
            user: $user,
            action: 'buyer.document_deleted',
            entity: $document,
            oldValue: ['title' => $document->title, 'path' => $document->file_path],
            request: $request
        );

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document removed from your vault.');
    }

    public function replyInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['inquiries.create', 'messages.create'])) {
            abort(403, 'Permission denied: inquiries.create required.');
        }

        if ($inquiry->user_id !== $user->id && strtolower((string) $inquiry->email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot reply to another user\'s inquiry.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $timestamp = Carbon::now()->format('M d, Y H:i');
        $replyEntry = "\n\n[Buyer {$user->name} on {$timestamp}]:\n{$validated['message']}";

        $inquiry->update([
            'message' => $inquiry->message.$replyEntry,
            'status' => 'contacted',
        ]);

        AuditLog::log(
            user: $user,
            action: 'buyer.inquiry_reply_sent',
            entity: $inquiry,
            newValue: ['reply' => $validated['message']],
            request: $request
        );

        return back()->with('success', 'Message sent to agent/seller.');
    }
}
