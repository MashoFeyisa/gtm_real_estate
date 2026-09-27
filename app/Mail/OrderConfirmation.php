<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $kind = $this->order->isRental() ? 'rental' : 'buy';

        return new Envelope(
            subject: 'We received your '.$kind.' request — GTP Real Estate',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
            with: ['order' => $this->order],
        );
    }
}
