<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderReceived;
use App\Models\Inquiry;
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
        $isCallback = ($request->input('request_kind') === 'callback');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [$isCallback ? 'nullable' : 'required', 'email', 'max:255'],
            'phone' => [$isCallback ? 'required' : 'nullable', 'string', 'max:50'],
            'offer_amount' => ['nullable', 'numeric', 'min:0'],
            'lease_start' => ['nullable', 'date'],
            'lease_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'request_kind' => ['nullable', 'string', 'in:order,callback'],
        ]);

        $type = $property->type === 'rent' ? 'rent' : 'sale';
        $agentId = $property->agent_id;
        $clientEmail = $validated['email'] ?? (preg_replace('/[^0-9]/', '', (string) ($validated['phone'] ?? '')) ?: 'client').'@callback.gtm';

        $order = Order::create([
            'property_id' => $property->id,
            'agent_id' => $agentId,
            'type' => $type,
            'name' => $validated['name'],
            'email' => $clientEmail,
            'phone' => $validated['phone'] ?? null,
            'offer_amount' => $validated['offer_amount'] ?? null,
            'lease_start' => $type === 'rent' ? ($validated['lease_start'] ?? null) : null,
            'lease_months' => $type === 'rent' ? ($validated['lease_months'] ?? 12) : null,
            'message' => $isCallback ? ('[Request Call Back] '.($validated['message'] ?? 'Please call me regarding this property.')) : ($validated['message'] ?? null),
            'status' => 'pending',
        ]);

        if ($agentId) {
            $actionLabel = $isCallback ? 'call back request' : ($type === 'rent' ? 'rental' : 'buy').' request';
            $inquiryMessage = 'New '.$actionLabel.' for "'.$property->title.'" from '.$validated['name'].'.';
            if (! empty($validated['phone'])) {
                $inquiryMessage .= ' Phone: '.$validated['phone'].'.';
            }
            if (! empty($validated['offer_amount'])) {
                $inquiryMessage .= ' Proposed amount: ETB '.number_format((float) $validated['offer_amount'], 2).'.';
            }
            if (! empty($validated['message'])) {
                $inquiryMessage .= ' Note: '.$validated['message'];
            }

            Inquiry::create([
                'agent_id' => $agentId,
                'name' => $validated['name'],
                'email' => $clientEmail,
                'phone' => $validated['phone'] ?? null,
                'subject' => ($isCallback ? 'Call Back Request: ' : 'New '.($type === 'rent' ? 'Rental' : 'Buy').' Request: ').$property->title,
                'message' => $inquiryMessage,
                'status' => 'pending',
            ]);
        }

        $order->setRelation('property', $property);

        $this->notifyOrderParties($order);

        $label = $type === 'rent' ? 'rental' : 'buy';
        $successMessage = $isCallback
            ? 'Your call back request was sent. The agent will call you shortly.'
            : "Your {$label} request was sent. The agent will contact you shortly.";

        return redirect()
            ->route('properties.show', $property->slug)
            ->with('success', $successMessage);
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
            if ($isAdmin) {
                return redirect()
                    ->route('dashboard', ['section' => 'orders'])
                    ->with('error', 'Generate the agreement first before downloading.');
            }

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
