<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Mail\OrderStatusUpdate;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\AgreementPdfService;
use App\Services\AnalyticsService;
use App\Services\WorkflowIntegrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AgentPortalController extends Controller
{
    public function __construct(private AgreementPdfService $agreements) {}

    /**
     * Display the agent portal: client inquiries (notifications) and buy requests.
     */
    public function index(Request $request)
    {
        $agent = $request->user()->ensureAgentProfile();
        $notifications = $request->user()->notifications()->latest()->take(20)->get();
        Booking::completeExpiredReservations();
        $bookings = Booking::query()
            ->where('status', '!=', 'completed')
            ->whereHas('property', fn ($query) => $query->where('agent_id', $agent->id))
            ->with(['property', 'payments'])
            ->latest()
            ->paginate(12, ['*'], 'bookings_page')
            ->withQueryString();

        $orders = $agent->orders()->with(['property', 'commission'])->latest()->paginate(12, ['*'], 'orders_page')->withQueryString();
        $pendingOrders = $agent->orders()->where('status', 'pending')->with(['property', 'commission'])->latest()->get();
        $inquiries = $agent->inquiries()->latest()->paginate(12, ['*'], 'inquiries_page')->withQueryString();

        $unreadInquiries = $agent->inquiries()->whereNull('read_at')->latest()->get();

        if ($request->boolean('mark_read')) {
            $agent->inquiries()->whereNull('read_at')->update(['read_at' => now()]);
            $unreadInquiries = collect();
        }

        $editPropertyId = (int) $request->query('edit_property');
        $propertyToEdit = $editPropertyId
            ? $agent->properties()->whereKey($editPropertyId)->first()
            : null;

        // Agent CRM metrics (SRS: Leads, New Leads, Viewings, Offers, Closed Deals)
        $inquiryTotal = $agent->inquiries()->count();
        $inquiriesResponded = $agent->inquiries()->whereNotNull('read_at')->count();

        $crmStats = [
            'leads' => $inquiryTotal,
            'new_leads' => $agent->inquiries()->whereNull('read_at')->count(),
            'active_viewings' => $agent->viewings()->whereIn('status', ['requested', 'confirmed'])->count(),
            'active_offers' => $agent->offers()->whereNotIn('status', ['rejected', 'expired', 'converted'])->count(),
            'closed_deals' => $agent->orders()->where('status', 'completed')->count() + $agent->leases()->where('status', 'active')->count(),
            'clients' => $agent->orders()->whereNotNull('email')->distinct()->count('email')
                + $agent->leases()->whereNotNull('tenant_user_id')->distinct()->count('tenant_user_id'),
            'response_rate' => $inquiryTotal > 0 ? (int) round(($inquiriesResponded / $inquiryTotal) * 100) : 0,
        ];

        // Listing quality score (SRS: Photos, Description, Location, Price, Amenities, Documents)
        $listingQuality = $agent->properties()->get()->mapWithKeys(
            fn (Property $property) => [$property->id => $this->propertyQualityScore($property)]
        );

        return view('agent.portal', [
            'agent' => $agent,
            'crmStats' => $crmStats,
            'listingQuality' => $listingQuality,
            'orders' => $orders,
            'bookings' => $bookings,
            'pendingOrders' => $pendingOrders,
            'inquiries' => $inquiries,
            'unreadInquiries' => $unreadInquiries,
            'notifications' => $notifications,
            'properties' => $agent->properties()->latest()->paginate(12, ['*'], 'properties_page')->withQueryString(),
            'propertyToEdit' => $propertyToEdit,
            'propertiesCount' => $agent->properties()->count(),
            'publishedCount' => $agent->properties()->publicVisible()->count(),
            'feedbackCount' => $agent->feedback_count,
            'averageRating' => $agent->average_rating,
            'feedbacks' => $agent->feedbacks()->latest()->paginate(12, ['*'], 'feedbacks_page')->withQueryString(),
            'projects' => $agent->projects()->with(['units', 'milestones', 'buildings'])->latest()->paginate(12, ['*'], 'projects_page')->withQueryString(),
            'projectsCount' => $agent->projects()->count(),
            'viewings' => $agent->viewings()->with('property')->latest()->get(),
            'offers' => $agent->offers()->with(['property', 'negotiations'])->latest()->get(),
            'leases' => $agent->leases()->with(['property', 'schedules.payment'])->latest()->get(),
            'campaigns' => $agent->campaigns()->with(['property', 'leads'])->latest()->get(),
            'actionableTasks' => app(WorkflowIntegrationService::class)->getActorActionableTasks($request->user()),
            'commissions' => Commission::where(function ($q) use ($agent) {
                $q->where('agent_id', $agent->id)
                    ->orWhere('listing_agent_id', $agent->id)
                    ->orWhere('selling_agent_id', $agent->id)
                    ->orWhere('referral_agent_id', $agent->id);
            })->with(['property', 'disbursedBy', 'listingAgent', 'sellingAgent', 'referralAgent'])->latest()->get(),
            'agentCharts' => app(AnalyticsService::class)->getAgentChartData($agent),
        ]);
    }

    /**
     * Store a new listing owned by the authenticated agent.
     */
    public function storeProperty(PropertyFormRequest $request)
    {
        $agent = $request->user()->agentProfile;

        $validated = $request->validated();

        $status = in_array($validated['status'], ['published', 'draft'], true) ? $validated['status'] : 'published';

        $property = Property::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage(),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'building_area' => $validated['building_area'] ?? $validated['area'],
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
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? null,
            'subcity' => $validated['subcity'] ?? null,
            'woreda' => $validated['woreda'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : null,
            'walk_score' => ! empty($validated['walk_score']) ? (int) $validated['walk_score'] : null,
            'transit_score' => ! empty($validated['transit_score']) ? (int) $validated['transit_score'] : null,
            'featured' => false,
            'agent_id' => $agent->id,
            'is_active' => $status === 'published',
        ]);

        $request->syncPropertyImages($property);

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" created successfully.');
    }

    /**
     * Update a listing owned by the authenticated agent.
     */
    public function updateProperty(PropertyFormRequest $request, Property $property)
    {
        $agent = $request->user()->agentProfile;

        $this->authorizeListingOwner($agent, $property);

        $validated = $request->validated();

        $status = in_array($validated['status'], ['published', 'draft', 'sold', 'rented', 'available', 'archived'], true)
            ? $validated['status']
            : $property->status;

        $property->update([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title'], $property->id),
            'description' => $validated['description'] ?? null,
            'image_path' => $request->storeImage($property->image_path),
            'price' => $validated['price'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'area' => $validated['area'],
            'building_area' => $validated['building_area'] ?? ($property->building_area ?: $validated['area']),
            'floors' => $validated['floors'] ?? ($property->floors ?: 1),
            'parking_spaces' => $validated['parking_spaces'] ?? ($property->parking_spaces ?: 1),
            'balconies' => $validated['balconies'] ?? ($property->balconies ?: 0),
            'year_built' => $validated['year_built'] ?? $property->year_built,
            'condition' => $validated['condition'] ?? ($property->condition ?: 'excellent'),
            'has_electricity' => (bool) $request->input('has_electricity', false),
            'has_water' => (bool) $request->input('has_water', false),
            'has_generator' => (bool) $request->input('has_generator', false),
            'has_water_tank' => (bool) $request->input('has_water_tank', false),
            'has_solar' => (bool) $request->input('has_solar', false),
            'has_security' => (bool) $request->input('has_security', false),
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? $property->property_category ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? $property->city,
            'subcity' => $validated['subcity'] ?? $property->subcity,
            'woreda' => $validated['woreda'] ?? $property->woreda,
            'neighborhood' => $validated['neighborhood'] ?? $property->neighborhood,
            'address' => $validated['address'] ?? $property->address,
            'latitude' => ! empty($validated['latitude']) ? (float) $validated['latitude'] : $property->latitude,
            'longitude' => ! empty($validated['longitude']) ? (float) $validated['longitude'] : $property->longitude,
            'walk_score' => ! empty($validated['walk_score']) ? (int) $validated['walk_score'] : $property->walk_score,
            'transit_score' => ! empty($validated['transit_score']) ? (int) $validated['transit_score'] : $property->transit_score,
            'agent_id' => $agent->id,
            'is_active' => in_array($status, ['published', 'available', 'sold', 'rented'], true),
        ]);

        $request->syncPropertyImages($property);

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" updated successfully.');
    }

    /**
     * Publish or unpublish a listing owned by the authenticated agent.
     */
    public function togglePropertyPublish(Request $request, Property $property)
    {
        $agent = $request->user()->agentProfile;

        $this->authorizeListingOwner($agent, $property);

        if ($property->status === 'archived') {
            $property->status = 'published';
            $property->is_active = true;
        } else {
            $property->status = $property->is_active ? 'draft' : 'published';
            $property->is_active = ! $property->is_active;
        }

        $property->save();

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" is now '.($property->is_active ? 'published' : 'unpublished').'.');
    }

    /**
     * Mark a listing as sold or rented, or revert it to available.
     * Sale properties toggle to sold; rent properties toggle to rented.
     */
    public function togglePropertySold(Request $request, Property $property)
    {
        $agent = $request->user()->agentProfile;

        $this->authorizeListingOwner($agent, $property);

        $soldStatus = $property->type === 'rent' ? 'rented' : 'sold';

        $property->status = $property->status === $soldStatus ? 'available' : $soldStatus;
        $property->is_active = true;
        $property->save();

        $label = $soldStatus === 'rented' ? 'rented' : 'sold';

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" marked as '.$label.'.');
    }

    /**
     * Archive a listing owned by the authenticated agent (hidden from the site).
     */
    public function archiveProperty(Request $request, Property $property)
    {
        $agent = $request->user()->agentProfile;

        $this->authorizeListingOwner($agent, $property);

        $property->status = 'archived';
        $property->is_active = false;
        $property->save();

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" archived.');
    }

    /**
     * Delete a listing owned by the authenticated agent.
     */
    public function destroyProperty(Request $request, Property $property)
    {
        $agent = $request->user()->agentProfile;

        $this->authorizeListingOwner($agent, $property);

        foreach ($property->images as $img) {
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }

        if ($property->image_path && Storage::disk('public')->exists($property->image_path)) {
            Storage::disk('public')->delete($property->image_path);
        }

        $property->delete();

        return redirect()
            ->route('agent.portal', ['section' => 'listings'])
            ->with('success', 'Listing "'.$property->title.'" deleted.');
    }

    /**
     * Delete an individual gallery image owned by the authenticated agent.
     */
    public function deletePropertyImage(Request $request, Property $property, PropertyImage $image)
    {
        $agent = $request->user()->agentProfile;
        $this->authorizeListingOwner($agent, $property);

        abort_unless($image->property_id === $property->id, 404);

        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        if ($property->image_path === $image->image_path) {
            $next = $property->images()->first();
            $property->update(['image_path' => $next?->image_path]);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Image removed successfully.');
    }

    /**
     * Set a specific gallery image as the primary cover photo for an agent's listing.
     */
    public function setPropertyCoverImage(Request $request, Property $property, PropertyImage $image)
    {
        $agent = $request->user()->agentProfile;
        $this->authorizeListingOwner($agent, $property);

        abort_unless($image->property_id === $property->id, 404);

        $property->update(['image_path' => $image->image_path]);
        $image->update(['sort_order' => 1]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'message' => 'Cover image updated successfully.',
                'image_id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => $image->image_url,
            ]);
        }

        return redirect()
            ->route('agent.portal', ['section' => 'listings', 'edit' => $property->id])
            ->with('success', 'Cover image updated successfully.');
    }

    /**
     * Set a luxury preset background image as the cover for an agent's listing.
     */
    public function setPropertyCoverPreset(Request $request, Property $property)
    {
        $agent = $request->user()->agentProfile;
        $this->authorizeListingOwner($agent, $property);

        $preset = $request->validate([
            'preset' => ['required', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
        ])['preset'];

        $path = 'images/luxury/'.$preset.'.jpg';
        $property->update(['image_path' => $path]);

        $existing = $property->images()->where('image_path', $path)->first();
        if (! $existing) {
            $property->images()->create([
                'image_path' => $path,
                'sort_order' => 1,
            ]);
        } else {
            $existing->update(['sort_order' => 1]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'preset' => $preset,
                'message' => 'Luxury background preset set as cover.',
                'image_path' => $path,
                'image_url' => asset($path),
            ]);
        }

        return redirect()
            ->route('agent.portal', ['section' => 'listings', 'edit' => $property->id])
            ->with('success', 'Luxury background preset set as cover.');
    }

    /**
     * Ensure the agent can only manage their own listings.
     */
    private function authorizeListingOwner($agent, Property $property): void
    {
        abort_unless($property->agent_id === $agent->id, 403, 'You can only manage your own listings.');
    }

    /**
     * Build a unique URL slug for a listing title.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = \Str::slug($title);
        $baseSlug = $slug;
        $counter = 1;

        $query = Property::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        while ($query->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;

            $query = Property::query()->where('slug', $slug);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }
        }

        return $slug;
    }

    /**
     * Accept or reject a buy/rent request. Accepting submits it for admin review.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $agent = $request->user()->agentProfile;

        abort_unless($order->agent_id === $agent->id, 403, 'This order belongs to another agent.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
            'agent_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'agent_note' => $validated['agent_note'] ?? null,
        ];

        if ($validated['status'] === 'accepted') {
            $updateData['submitted_to_admin_at'] = now();
            $updateData['admin_status'] = 'pending';
            $updateData['agreed_at'] = now();
        }

        $order->update($updateData);

        if ($validated['status'] === 'accepted') {
            $this->generateCommission($order, $agent);
            $message = 'The '.($order->isRental() ? 'rental' : 'buy').' request was submitted to Admin for commission review.';
        } else {
            $message = 'The '.($order->isRental() ? 'rental' : 'buy').' request was rejected.';
        }

        $this->notifyClientOfStatus($order);

        return redirect()
            ->route('agent.portal')
            ->with('success', $message);
    }

    /**
     * Mark a client inquiry as read for the authenticated agent.
     */
    public function markInquiryRead(Request $request, Inquiry $inquiry)
    {
        $agent = $request->user()->agentProfile;

        abort_unless($inquiry->agent_id === $agent->id, 403, 'This message belongs to another agent.');

        if (is_null($inquiry->read_at)) {
            $inquiry->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true, 'read_at' => $inquiry->read_at?->toIso8601String()]);
    }

    /**
     * Update the authenticated agent's profile details.
     */
    /**
     * Listing quality score (SRS: completeness of photos, description,
     * location, price, amenities, and documents) — 0 to 100.
     */
    private function propertyQualityScore(Property $property): int
    {
        $checks = [
            'photos' => $property->images()->count() > 0,
            'description' => filled($property->description) && mb_strlen((string) $property->description) >= 40,
            'location' => filled($property->city) && filled($property->address),
            'price' => (float) $property->price > 0,
            'amenities' => filled($property->nearby_amenities) || filled($property->facilities),
            'documents' => $property->documents()->count() > 0 || $property->verification_level > 0,
        ];

        return (int) round((count(array_filter($checks)) / count($checks)) * 100);
    }

    public function updateProfile(Request $request)
    {
        $agent = $request->user()->agentProfile;
        abort_unless($agent, 403, 'Agent profile not found.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:agents,email,'.$agent->id, 'unique:users,email,'.$agent->user_id],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'service_locations' => ['nullable', 'array', 'max:20'],
            'service_locations.*' => ['nullable', 'string', 'max:100'],
            'languages' => ['nullable', 'array', 'max:10'],
            'languages.*' => ['nullable', 'string', 'max:50'],
            'phone_privacy' => ['nullable', 'in:show,hide,messaging_only'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = collect($validated)->except(['photo', 'password'])->all();

        $data['service_locations'] = array_values(array_filter($validated['service_locations'] ?? []));
        $data['languages'] = array_values(array_filter($validated['languages'] ?? []));
        $data['phone_privacy'] = $validated['phone_privacy'] ?? 'show';

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('photo')) {
            if ($agent->photo_path && Storage::disk('public')->exists($agent->photo_path)) {
                Storage::disk('public')->delete($agent->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('agent-photos', 'public');
        }

        $agent->update($data);

        // Keep linked login account in sync
        if ($agent->account) {
            $accountData = [
                'name' => $agent->name,
                'email' => $agent->email,
            ];
            if (filled($validated['password'] ?? null)) {
                $accountData['password'] = $validated['password'];
            }
            $agent->account->update($accountData);
        }

        return redirect()->route('agent.portal')->with('success', 'Your profile was updated successfully.');
    }

    /**
     * Email the client when their request is accepted or rejected.
     * Failures never block the action — mail is best-effort.
     */
    private function notifyClientOfStatus(Order $order): void
    {
        try {
            Mail::to($order->email)->send(new OrderStatusUpdate($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Download the agreement PDF for an order owned by the agent.
     */
    public function downloadAgreement(Request $request, Order $order)
    {
        $agent = $request->user()->agentProfile;

        abort_unless($order->agent_id === $agent->id, 403, 'This order belongs to another agent.');
        abort_unless($order->isAgreementApprovedForGeneration(), 403, 'Admin approval and website commission collection are required before downloading the agreement.');
        abort_unless($order->hasAgreement(), 404, 'No agreement has been generated for this request.');

        return redirect()->route('orders.agreement', $order);
    }

    public function generateAgreement(Request $request, Order $order): RedirectResponse
    {
        $agent = $request->user()->agentProfile;

        abort_unless($agent && $order->agent_id === $agent->id, 403, 'This order belongs to another agent.');
        abort_unless($order->isSubmittedToAdmin() && $order->isAgreementApprovedForGeneration(), 403, 'Admin approval and website commission collection are required before generating the agreement.');

        if (! $order->hasAgreement()) {
            $this->agreements->generate($order);

            if ($order->property && ! in_array($order->property->status, ['sold', 'rented'], true)) {
                $order->property->update([
                    'status' => $order->isRental() ? 'rented' : 'sold',
                    'is_active' => false,
                ]);
            }
        }

        $this->notifyClientOfStatus($order->refresh());

        return redirect()->route('agent.portal')
            ->with('success', 'Admin approved the agreement. The PDF is ready for '.$order->name.'.');
    }

    /**
     * Create a commission record when an agent accepts a buy/rent order.
     * Skips creation if a commission already exists for this order.
     *
     * @param  Agent  $agent
     */
    private function generateCommission(Order $order, $agent): void
    {
        if ($order->commission()->exists()) {
            return;
        }

        $price = $order->offer_amount ?? optional($order->property)->price ?? 0;
        $rate = (float) ($agent->commission_rate ?? 5.00);
        $amount = round($price * $rate / 100, 2);

        Commission::create([
            'agent_id' => $agent->id,
            'order_id' => $order->id,
            'property_id' => $order->property_id,
            'property_price' => $price,
            'commission_rate' => $rate,
            'commission_amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /**
     * Delete a buy/rent request order owned by the authenticated agent.
     */
    public function deleteOrder(Request $request, Order $order): RedirectResponse
    {
        $agent = $request->user()->agentProfile;
        abort_unless($agent && $order->agent_id === $agent->id, 403, 'This order belongs to another agent.');

        if ($order->agreement_path && Storage::disk('local')->exists($order->agreement_path)) {
            Storage::disk('local')->delete($order->agreement_path);
        }

        $order->delete();

        return redirect()->route('agent.portal', ['#buy-requests'])
            ->with('success', 'The request was deleted successfully.');
    }

    /**
     * Delete a client inquiry / message for the authenticated agent.
     */
    public function deleteInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $agent = $request->user()->agentProfile;
        abort_unless($agent && $inquiry->agent_id === $agent->id, 403, 'This message belongs to another agent.');

        $inquiry->delete();

        return redirect()->route('agent.portal', ['#messages'])
            ->with('success', 'The message was deleted successfully.');
    }
}
