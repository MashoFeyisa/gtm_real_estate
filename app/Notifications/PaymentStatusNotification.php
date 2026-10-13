<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public string $status = 'completed'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amountFormatted = number_format($this->payment->amount, 2).' ETB';

        $title = match ($this->status) {
            'completed' => '✅ Payment Verified & Receipt Issued / ክፍያዎ ተረጋግጧል',
            'failed' => '⚠️ Payment Verification Issue',
            default => 'Payment Submitted for Verification',
        };

        $message = match ($this->status) {
            'completed' => "Transaction #{$this->payment->transaction_number} of {$amountFormatted} has been verified. View your official digital receipt voucher.",
            'failed' => "Transaction #{$this->payment->transaction_number} of {$amountFormatted} could not be verified. Reason: ".($this->payment->admin_notes ?: 'Please contact support.'),
            default => "Payment #{$this->payment->transaction_number} has been submitted and is currently undergoing audit.",
        };

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'payment',
            'payment_id' => $this->payment->id,
            'transaction_number' => $this->payment->transaction_number,
            'amount' => (float) $this->payment->amount,
            'status' => $this->status,
            'url' => route('payments.receipt', $this->payment->transaction_number),
        ];
    }
}
