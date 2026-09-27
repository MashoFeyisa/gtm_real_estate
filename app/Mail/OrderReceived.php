<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $kind = $this->order->isRental() ? 'Rental' : 'Buy';

        return new Envelope(
            subject: 'New '.$kind.' request: '.$this->order->property?->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.received',
            with: ['order' => $this->order],
        );
    }
}
