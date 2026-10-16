<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyImage;
use App\Models\PropertyOffer;
use App\Models\PropertyViewing;
use App\Models\RentSchedule;
use App\Models\User;
use App\Services\ImageSecurityService;
use App\Services\RentPaymentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Display the unified Property Owner and Landlord dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual seller dashboards.');
        }

        if ($user?->status === 'suspended') {
            abort(403, 'Account suspended.');
        }

        if (! in_array($user?->role, ['seller', 'landlord', 'property_owner'], true) && ! $user?->hasAnyPermission(['properties.view', 'properties.create', 'listings.view', 'leases.view', 'rent.view'])) {
            abort(403, 'Permission denied: Property owner or landlord access required.');
        }

        // Properties strictly owned or managed by this user
        $ownedPropertyIds = Property::where('user_id', $user->id)->pluck('id')
            ->merge(PropertyDocument::where('user_id', $user->id)->pluck('property_id'))
            ->merge(Lease::where('landlord_user_id', $user->id)->pluck('property_id'))
            ->merge(Property::where('host_id', $user->id)->pluck('id'))
            ->unique()
            ->filter()
            ->all();

        $properties = Property::with(['images', 'documents', 'listings', 'leases.tenant', 'leases.schedules'])
            ->where(function ($q) use ($ownedPropertyIds, $user) {
                $q->where('user_id', $user->id);
                if (! empty($ownedPropertyIds)) {
                    $q->orWhereIn('id', $ownedPropertyIds);
                }
            })
            ->latest()
            ->get();

        $propertyIds = $properties->pluck('id')->all();

        $listings = Listing::with('property.images')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $leads = PropertyOffer::with(['property', 'negotiations'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $viewings = PropertyViewing::with('property')
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        $inquiries = Inquiry::query()
            ->whereIn('property_id', $propertyIds)
            ->with('property')
            ->latest()
            ->take(15)
            ->get();

        $leases = Lease::with(['property', 'tenant', 'schedules'])
            ->where(function ($q) use ($user, $propertyIds) {
                $q->where('landlord_user_id', $user->id);
                if (! empty($propertyIds)) {
                    $q->orWhereIn('property_id', $propertyIds);
                }
            })
            ->latest()
            ->get();

        $rentSchedules = RentSchedule::whereHas('lease', function ($q) use ($user, $propertyIds) {
            $q->where('landlord_user_id', $user->id);
            if (! empty($propertyIds)) {
                $q->orWhereIn('property_id', $propertyIds);
            }
        })->with('lease.property')->latest()->get();

        $maintenanceTickets = MaintenanceTicket::with(['property', 'user'])
            ->whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        // Listing Quality Score Calculator (0 - 100)
        $scores = [];
        foreach ($properties as $prop) {
            $score = 0;
            if ($prop->images->count() >= 1) {
                $score += 25;
            }
            if ($prop->images->count() >= 3) {
                $score += 15;
            }
            if (strlen((string) $prop->description) > 80) {
                $score += 20;
            }
            if ($prop->documents->count() >= 1) {
                $score += 25;
            }
            if ($prop->price > 0 && ! empty($prop->city)) {
                $score += 15;
            }
            $scores[$prop->id] = min(100, $score);
        }

        return view('dashboards.seller', compact(
            'properties',
            'listings',
            'leads',
            'viewings',
            'inquiries',
            'leases',
            'rentSchedules',
            'maintenanceTickets',
            'scores'
        ));
    }

    /**
     * Create a new property listing.
     */
    public function storeListing(PropertyFormRequest $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $validated = $request->validated();
        $user = $request->user();

        $status = in_array($validated['status'] ?? 'draft', ['published', 'draft', 'pending_review'], true)
            ? ($validated['status'] ?? 'draft')
            : 'draft';

        $property = Property::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage(),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 50,
            'building_area' => $validated['building_area'] ?? ($validated['area'] ?? 50),
            'floors' => $validated['floors'] ?? 1,
            'parking_spaces' => $validated['parking_spaces'] ?? 1,
            'balconies' => $validated['balconies'] ?? 0,
            'year_built' => $validated['year_built'] ?? null,
            'condition' => $validated['condition'] ?? 'excellent',
            'has_electricity' => (bool) $request->input('has_electricity', false),
            'has_water' => (bool) $request->input('has_water', false),
            'has_generator' => (bool) $request->input('has_generator', false),
            'has_water_tank' => (bool) $request->input('has_water_tank', false),
            'has_solar' => (bool) $request->input('has_solar', false),
            'has_security' => (bool) $request->input('has_security', false),
            'type' => $validated['type'] ?? 'sale',
            'property_category' => $validated['category'] ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? 'Addis Ababa',
            'subcity' => $validated['subcity'] ?? null,
            'woreda' => $validated['woreda'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'featured' => false,
            'is_active' => $status === 'published',
        ]);

        $request->syncPropertyImages($property);

        AuditLog::log(
            action: 'seller.property_created',
            entity: $property,
            reason: "Property Owner {$user->name} created property \"{$property->title}\".",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Listing "'.$property->title.'" created successfully.');
    }

    /**
     * Update an existing property.
     */
    public function updateListing(PropertyFormRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validated();
        $oldPrice = (float) $property->price;
        $newPrice = (float) $validated['price'];

        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage($property->image_path),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'] ?? 1,
            'bathrooms' => $validated['bathrooms'] ?? 1,
            'area' => $validated['area'] ?? 50,
            'building_area' => $validated['building_area'] ?? ($validated['area'] ?? 50),
            'type' => $validated['type'] ?? $property->type,
            'property_category' => $validated['category'] ?? $property->property_category,
            'status' => $validated['status'] ?? $property->status,
            'city' => $validated['city'] ?? $property->city,
            'subcity' => $validated['subcity'] ?? $property->subcity,
            'woreda' => $validated['woreda'] ?? $property->woreda,
            'neighborhood' => $validated['neighborhood'] ?? $property->neighborhood,
            'address' => $validated['address'] ?? $property->address,
            'is_active' => in_array($validated['status'] ?? $property->status, ['published', 'available'], true),
        ]);

        if ($oldPrice !== $newPrice) {
            foreach ($property->listings as $listing) {
                $listing->update(['price' => $newPrice]);
            }
        }

        $request->syncPropertyImages($property);

        AuditLog::log(
            action: 'seller.property_updated',
            entity: $property,
            reason: "Property Owner updated property \"{$property->title}\".",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Listing updated successfully.');
    }

    /**
     * Delete a property and all associated records.
     */
    public function destroyProperty(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $user = $request->user();
        $title = $property->title;

        // Clean up images
        foreach ($property->images as $img) {
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
            $img->delete();
        }

        // Clean up documents
        foreach ($property->documents as $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
        }

        $property->delete();

        AuditLog::log(
            action: 'seller.property_deleted',
            reason: "Property Owner {$user->name} deleted property \"{$title}\".",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Property "'.$title.'" was removed successfully.');
    }

    /**
     * Toggle active / published status.
     */
    public function togglePublish(Property $property): RedirectResponse
    {
        $this->authorize('publish', $property);

        $newActive = ! $property->is_active;
        $property->is_active = $newActive;
        $property->status = $newActive ? 'published' : 'draft';
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $newActive ? 'published' : 'draft']);
        }

        AuditLog::log(
            action: 'seller.property_toggle_publish',
            entity: $property,
            reason: 'Property '.($newActive ? 'published' : 'unpublished'),
            user: auth()->user(),
        );

        return redirect()->route('seller.dashboard')->with('success', 'Listing status updated to '.($newActive ? 'Published' : 'Draft').'.');
    }

    /**
     * Transition listing status (published, paused, draft, sold, rented).
     */
    public function updateListingStatus(Property $property, string $status): RedirectResponse
    {
        $this->authorize('update', $property);

        if (! in_array($status, ['draft', 'pending_review', 'published', 'paused', 'sold', 'rented'], true)) {
            abort(422, 'Invalid status.');
        }

        $property->status = $status;
        $property->is_active = in_array($status, ['published', 'sold', 'rented'], true);
        $property->save();

        foreach ($property->listings as $listing) {
            $listing->update(['status' => $status]);
        }

        AuditLog::log(
            action: 'seller.property_status_transition',
            entity: $property,
            reason: "Status transitioned to {$status}",
            user: auth()->user(),
        );

        return redirect()->route('seller.dashboard')->with('success', 'Listing transitioned to '.ucfirst(str_replace('_', ' ', $status)).'.');
    }

    /**
     * Upload additional gallery photos.
     */
    public function uploadPhotos(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $request->validate([
            'photos.*' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        if ($request->hasFile('photos')) {
            $currentMax = $property->images()->max('sort_order') ?? 0;
            foreach ($request->file('photos') as $file) {
                $path = ImageSecurityService::storeAndStrip($file, 'properties/gallery', 'public');
                $property->images()->create([
                    'image_path' => $path,
                    'sort_order' => ++$currentMax,
                ]);
            }
        }

        AuditLog::log(
            action: 'seller.photos_uploaded',
            entity: $property,
            reason: 'Uploaded gallery photos',
            user: $request->user(),
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Photos uploaded successfully.');
    }

    /**
     * Delete an individual photo.
     */
    public function deletePhoto(Request $request, PropertyImage $image): RedirectResponse
    {
        $property = $image->property;
        if (! $property) {
            abort(403, 'Unauthorized photo deletion.');
        }

        $this->authorize('update', $property);

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();

        AuditLog::log(
            action: 'seller.photo_deleted',
            entity: $property,
            reason: 'Deleted gallery photo',
            user: $request->user(),
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Photo removed.');
    }

    /**
     * Set a photo as the primary cover photo.
     */
    public function setCoverPhoto(Request $request, PropertyImage $image): RedirectResponse
    {
        $property = $image->property;
        if (! $property) {
            abort(403, 'Unauthorized.');
        }

        $this->authorize('update', $property);

        $property->update(['image_path' => $image->image_path]);

        AuditLog::log(
            action: 'seller.cover_photo_set',
            entity: $property,
            reason: 'Set primary cover photo',
            user: $request->user(),
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Cover photo updated.');
    }

    /**
     * Upload property ownership/title deed document with access levels and versions.
     */
    public function uploadDocument(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'access_level' => ['required', 'in:public,verified_only,private'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:15360'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('file');
        $path = $file->store('property_documents', 'public');

        $currentVersion = $property->documents()
            ->where('document_type', $validated['document_type'])
            ->max('version') ?? 0;

        $doc = $property->documents()->create([
            'user_id' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'document_number' => 'DOC-'.strtoupper(Str::random(6)),
            'version' => $currentVersion + 1,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'access_level' => $validated['access_level'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        AuditLog::log(
            action: 'seller.document_uploaded',
            entity: $doc,
            reason: "Uploaded document \"{$doc->title}\" (v{$doc->version}) with access level {$doc->access_level}",
            user: $request->user(),
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Document \"{$doc->title}\" uploaded as version {$doc->version}.");
    }

    /**
     * Delete an ownership document.
     */
    public function destroyDocument(Request $request, PropertyDocument $document): RedirectResponse
    {
        $user = $request->user();

        if (! $document->property) {
            abort(403, 'Unauthorized document removal.');
        }

        $this->authorize('update', $document->property);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        AuditLog::log(
            action: 'seller.document_deleted',
            reason: "Deleted document \"{$document->title}\"",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Document removed.');
    }

    /**
     * Reply to buyer or tenant inquiry.
     */
    public function replyInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $user = $request->user();
        if (! $inquiry->property || (int) $inquiry->property->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized inquiry response.');
        }

        $validated = $request->validate([
            'reply_message' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $timestamp = Carbon::now()->format('M d, Y H:i');
        $replyEntry = "\n\n[Owner {$user->name} on {$timestamp}]:\n{$validated['reply_message']}";

        $inquiry->update([
            'message' => $inquiry->message.$replyEntry,
            'status' => 'responded',
        ]);

        AuditLog::log(
            action: 'seller.inquiry_replied',
            entity: $inquiry,
            reason: "Responded to inquiry from {$inquiry->name}",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Response sent to prospective client.');
    }

    /**
     * Landlord: Create a new lease agreement.
     */
    public function createLease(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasAnyPermission(['leases.create', 'leases.update', 'leases.view'])) {
            abort(403, 'Permission denied: Cannot create leases.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_email' => ['required', 'email', 'max:255'],
            'tenant_phone' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['required', 'numeric', 'min:0'],
            'payment_frequency' => ['nullable', 'string', 'in:monthly,quarterly,semi-annual,annual'],
            'terms_and_conditions' => ['nullable', 'string'],
        ]);

        $property = Property::findOrFail($validated['property_id']);
        if ((int) $property->user_id !== (int) $user->id) {
            abort(403, 'You do not own this property.');
        }

        $tenantUser = User::where('email', $validated['tenant_email'])->first();

        $lease = Lease::create([
            'property_id' => $property->id,
            'landlord_user_id' => $user->id,
            'tenant_user_id' => $tenantUser?->id,
            'tenant_name' => $validated['tenant_name'],
            'tenant_email' => $validated['tenant_email'],
            'tenant_phone' => $validated['tenant_phone'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'duration_months' => (int) $validated['duration_months'],
            'monthly_rent' => (float) $validated['monthly_rent'],
            'security_deposit' => (float) $validated['security_deposit'],
            'deposit_status' => 'held',
            'deposit_deductions' => 0,
            'deposit_refunded' => 0,
            'payment_frequency' => $validated['payment_frequency'] ?? 'monthly',
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'status' => 'draft',
        ]);

        AuditLog::log(
            action: 'landlord.lease_created',
            entity: $lease,
            reason: "Landlord created lease agreement {$lease->lease_code} for tenant {$lease->tenant_name}.",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Lease {$lease->lease_code} created as draft.");
    }

    /**
     * Landlord: Renew an existing lease.
     */
    public function renewLease(Request $request, Lease $lease): RedirectResponse
    {
        $user = $request->user();
        if ((int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized lease renewal.');
        }

        $validated = $request->validate([
            'new_start_date' => ['required', 'date'],
            'new_end_date' => ['required', 'date', 'after:new_start_date'],
            'new_monthly_rent' => ['required', 'numeric', 'min:0'],
        ]);

        $lease->update([
            'start_date' => $validated['new_start_date'],
            'end_date' => $validated['new_end_date'],
            'monthly_rent' => $validated['new_monthly_rent'],
            'status' => 'active',
        ]);

        AuditLog::log(
            action: 'landlord.lease_renewed',
            entity: $lease,
            reason: "Landlord renewed lease {$lease->lease_code}.",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Lease {$lease->lease_code} renewed successfully.");
    }

    /**
     * Landlord: Terminate a lease early.
     */
    public function terminateLease(Request $request, Lease $lease): RedirectResponse
    {
        $user = $request->user();
        if ((int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized lease termination.');
        }

        $validated = $request->validate([
            'termination_reason' => ['required', 'string', 'max:1000'],
        ]);

        $lease->update([
            'status' => 'terminated',
            'notes' => ($lease->notes ? $lease->notes."\n" : '').'Terminated: '.$validated['termination_reason'],
        ]);

        AuditLog::log(
            action: 'landlord.lease_terminated',
            entity: $lease,
            reason: "Terminated lease {$lease->lease_code}: {$validated['termination_reason']}",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Lease {$lease->lease_code} has been terminated.");
    }

    /**
     * Landlord: Record offline rent collection payment.
     */
    public function recordRentPayment(Request $request, RentSchedule $schedule): RedirectResponse
    {
        $user = $request->user();
        $lease = $schedule->lease;
        if (! $lease || (int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized rent collection.');
        }

        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,telebirr,cbe_birr,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // The landlord desk records an offline collection through the same
        // writer the tenant desk uses, so it leaves the same payment row and
        // balanced ledger journal as an online payment (SRS §87).
        app(RentPaymentService::class)->record(
            schedule: $schedule,
            recorder: $user,
            amount: (float) $validated['amount_paid'],
            paymentMethod: $validated['payment_method'],
            notes: $validated['notes'] ?? 'Offline collection recorded by landlord',
            idempotencyKey: $request->header('Idempotency-Key'),
        );

        return redirect()->route('seller.dashboard')->with('success', "Rent installment #{$schedule->installment_number} recorded as paid.");
    }

    /**
     * Landlord: Mark a rent installment as late/overdue.
     */
    public function markInstallmentLate(Request $request, RentSchedule $schedule): RedirectResponse
    {
        $user = $request->user();
        $lease = $schedule->lease;
        if (! $lease || (int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized.');
        }

        $schedule->update(['status' => 'overdue']);

        AuditLog::log(
            action: 'landlord.rent_marked_late',
            entity: $schedule,
            reason: "Marked installment #{$schedule->installment_number} as overdue",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Installment #{$schedule->installment_number} marked overdue.");
    }

    /**
     * Landlord: Deduct an amount from security deposit with itemized reason.
     */
    public function deductDeposit(Request $request, Lease $lease): RedirectResponse
    {
        $user = $request->user();
        if ((int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized deposit deduction.');
        }

        $validated = $request->validate([
            'deduction_amount' => ['required', 'numeric', 'min:0.01'],
            'deduction_reason' => ['required', 'string', 'max:1000'],
        ]);

        $maxDeductible = max(0.0, (float) $lease->security_deposit - (float) $lease->deposit_deductions);
        $amount = min($maxDeductible, (float) $validated['deduction_amount']);

        $lease->update([
            'deposit_deductions' => (float) $lease->deposit_deductions + $amount,
            'deposit_status' => 'partially_deducted',
            'deposit_deduction_reason' => ($lease->deposit_deduction_reason ? $lease->deposit_deduction_reason."\n" : '').$validated['deduction_reason'],
        ]);

        AuditLog::log(
            action: 'landlord.deposit_deducted',
            entity: $lease,
            reason: 'Deducted ETB '.number_format($amount, 2)." from deposit: {$validated['deduction_reason']}",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'ETB '.number_format($amount, 2).' deducted from security deposit.');
    }

    /**
     * Landlord: Return remaining security deposit to tenant.
     */
    public function returnDeposit(Request $request, Lease $lease): RedirectResponse
    {
        $user = $request->user();
        if ((int) $lease->landlord_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized deposit return.');
        }

        $remaining = max(0.0, (float) $lease->security_deposit - (float) $lease->deposit_deductions);

        $lease->update([
            'deposit_refunded' => $remaining,
            'deposit_status' => 'returned',
            'deposit_returned_at' => now(),
        ]);

        AuditLog::log(
            action: 'landlord.deposit_returned',
            entity: $lease,
            reason: 'Returned remaining security deposit of ETB '.number_format($remaining, 2),
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', 'Security deposit of ETB '.number_format($remaining, 2).' marked returned.');
    }

    /**
     * Landlord: Assign maintenance ticket to contractor.
     */
    public function assignMaintenance(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        if (! $ticket->property || (int) $ticket->property->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized maintenance assignment.');
        }

        $validated = $request->validate([
            'assigned_contractor' => ['required', 'string', 'max:255'],
            'location_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $ticket->update([
            'status' => 'in_progress',
            'location_notes' => ($ticket->location_notes ? $ticket->location_notes."\n" : '').'Assigned to: '.$validated['assigned_contractor'],
        ]);

        AuditLog::log(
            action: 'landlord.maintenance_assigned',
            entity: $ticket,
            reason: "Assigned maintenance ticket #{$ticket->ticket_number} to {$validated['assigned_contractor']}",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Ticket #{$ticket->ticket_number} assigned to contractor.");
    }

    /**
     * Landlord: Mark maintenance ticket complete.
     */
    public function completeMaintenance(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        if (! $ticket->property || (int) $ticket->property->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized maintenance completion.');
        }

        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        $ticket->update([
            'status' => 'completed',
            'resolved_at' => now(),
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        AuditLog::log(
            action: 'landlord.maintenance_completed',
            entity: $ticket,
            reason: "Completed maintenance ticket #{$ticket->ticket_number}",
            user: $user,
            request: $request
        );

        return redirect()->route('seller.dashboard')->with('success', "Ticket #{$ticket->ticket_number} marked completed.");
    }
}
