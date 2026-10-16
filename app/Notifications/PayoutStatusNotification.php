<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayoutStatusNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $event  requested|approved|rejected|paid
     */
    public function __construct(
        public PayoutRequest $payout,
        public string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $gross = 'ETB '.number_format((float) $this->payout->gross_amount, 2);
        $net = 'ETB '.number_format((float) $this->payout->net_amount, 2);

        $title = match ($this->event) {
            'approved' => "✅ Payout Approved: {$gross}",
            'rejected' => "⛔ Payout Rejected: {$gross}",
            'paid' => "💸 Payout Paid: {$net}",
            default => "📤 Payout Requested: {$gross}",
        };

        $message = match ($this->event) {
            'approved' => "Your payout {$this->payout->payout_code} ({$gross}) was approved and is awaiting disbursement.",
            'rejected' => "Your payout {$this->payout->payout_code} ({$gross}) was rejected: ".($this->payout->rejection_reason ?: 'No reason given.'),
            'paid' => "Your payout {$this->payout->payout_code} was paid: {$net} after withholding tax (manual disbursement, ref {$this->payout->transaction_reference}).",
            default => "A payout request {$this->payout->payout_code} for {$gross} was submitted.",
        };

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'payout',
            'payout_id' => $this->payout->id,
            'status' => $this->payout->status,
            'url' => route('finance.index'),
        ];
    }
}
