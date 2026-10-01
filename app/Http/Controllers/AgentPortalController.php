<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyFormRequest;
use App\Mail\OrderStatusUpdate;
use App\Models\Agent;
use App\Models\Commission;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\AgreementPdfService;
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
        $agent = $request->user()->agentProfile;

        $orders = $agent->orders()->with('property')->latest()->get();
        $inquiries = $agent->inquiries()->latest()->get();

        $unreadInquiries = $inquiries->whereNull('read_at');

        if ($request->boolean('mark_read')) {
            $agent->inquiries()->whereNull('read_at')->update(['read_at' => now()]);
            $unreadInquiries = collect();
        }

        $editPropertyId = (int) $request->query('edit_property');
        $propertyToEdit = $editPropertyId
            ? $agent->properties()->whereKey($editPropertyId)->first()
            : null;

        return view('agent.portal', [
            'agent' => $agent,
            'orders' => $orders,
            'pendingOrders' => $orders->where('status', 'pending'),
            'inquiries' => $inquiries,
            'unreadInquiries' => $unreadInquiries,
            'properties' => $agent->properties()->latest()->get(),
            'propertyToEdit' => $propertyToEdit,
            'propertiesCount' => $agent->properties()->count(),
            'publishedCount' => $agent->properties()->publicVisible()->count(),
            'feedbackCount' => $agent->feedback_count,
            'averageRating' => $agent->average_rating,
            'feedbacks' => $agent->feedbacks()->latest()->get(),
        ]);
    }

    /**
     * Store a new listing owned by the authenticated agent.
     */
    public function storeProperty(PropertyFormRequest $request)
    {
        $agent = $request->user()->agentProfile;

        $validated = $request->validationData();

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
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
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

        $validated = $request->validationData();

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
            'type' => $validated['type'],
            'property_category' => $validated['category'] ?? $property->property_category ?? 'home',
            'status' => $status,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
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
     * Accept or reject a buy/rent request. Accepting sends request to admin and generates agreement.
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
            $this->agreements->generate($order);
            $this->generateCommission($order, $agent);
            $message = 'The '.($order->isRental() ? 'rental' : 'buy').' request was accepted and the agreement was generated.';
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
    public function updateProfile(Request $request)
    {
        $agent = $request->user()->agentProfile;
        abort_unless($agent, 403, 'Agent profile not found.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:agents,email,'.$agent->id, 'unique:users,email,'.$agent->user_id],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = collect($validated)->except(['photo', 'password'])->all();

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
        abort_unless($order->hasAgreement(), 404, 'No agreement has been generated for this request.');

        return redirect()->route('orders.agreement', $order);
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
