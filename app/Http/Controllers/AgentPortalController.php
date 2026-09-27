<?php

namespace App\Http\Controllers;

use App\Mail\OrderStatusUpdate;
use App\Models\Order;
use App\Services\AgreementPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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

        return view('agent.portal', [
            'agent' => $agent,
            'orders' => $orders,
            'pendingOrders' => $orders->where('status', 'pending'),
            'inquiries' => $inquiries,
            'unreadInquiries' => $unreadInquiries,
            'propertiesCount' => $agent->properties()->count(),
            'feedbackCount' => $agent->feedback_count,
            'averageRating' => $agent->average_rating,
        ]);
    }

    /**
     * Accept or reject a buy/rent request. Accepting generates the branded agreement PDF.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $agent = $request->user()->agentProfile;

        abort_unless($order->agent_id === $agent->id, 403, 'This order belongs to another agent.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
            'agent_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'status' => $validated['status'],
            'agent_note' => $validated['agent_note'] ?? null,
        ]);

        if ($validated['status'] === 'accepted') {
            $this->agreements->generate($order);
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
}
