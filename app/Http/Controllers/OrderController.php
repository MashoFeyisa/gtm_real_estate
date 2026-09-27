<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderReceived;
use App\Models\Order;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Store a client buy or rent request for a property.
     */
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'offer_amount' => ['nullable', 'numeric', 'min:0'],
            'lease_start' => ['nullable', 'date'],
            'lease_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $type = $property->type === 'rent' ? 'rent' : 'sale';

        $order = Order::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'type' => $type,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'offer_amount' => $validated['offer_amount'] ?? null,
            'lease_start' => $type === 'rent' ? ($validated['lease_start'] ?? null) : null,
            'lease_months' => $type === 'rent' ? ($validated['lease_months'] ?? 12) : null,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        $order->setRelation('property', $property);

        $this->notifyOrderParties($order);

        $label = $type === 'rent' ? 'rental' : 'buy';

        return redirect()
            ->route('properties.show', $property->slug)
            ->with('success', "Your {$label} request was sent. The agent will contact you shortly.");
    }

    /**
     * Email the assigned agent and the client about the new request.
     * Failures never block the request — mail is best-effort.
     */
    private function notifyOrderParties(Order $order): void
    {
        try {
            if ($order->agent?->email) {
                Mail::to($order->agent->email)->send(new OrderReceived($order));
            }

            Mail::to($order->email)->send(new OrderConfirmation($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Stream the generated agreement PDF for an order.
     */
    public function downloadAgreement(Request $request, Order $order)
    {
        $user = $request->user();

        $isOwnerAgent = $user && $user->agentProfile && $user->agentProfile->id === $order->agent_id;
        $isAdmin = $user && $user->isAdmin();

        if (! $isOwnerAgent && ! $isAdmin) {
            abort(403, 'You are not allowed to download this agreement.');
        }

        if (! $order->hasAgreement()) {
            return redirect()
                ->route('agent.portal')
                ->with('success', 'Accept the request first to generate the agreement.');
        }

        abort_unless(Storage::disk('local')->exists($order->agreement_path), 404, 'Agreement file not found.');

        return Storage::disk('local')->download($order->agreement_path, $this->agreementFilename($order));
    }

    private function agreementFilename(Order $order): string
    {
        $kind = $order->isRental() ? 'rental' : 'purchase';

        return str_replace(' ', '-', $order->property?->title ?? 'property').'-'.$kind.'-agreement.pdf';
    }
}
