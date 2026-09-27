<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $kind = $this->order->isRental() ? 'rental' : 'buy';
        $state = $this->order->status === 'accepted' ? 'accepted' : 'rejected';

        return new Envelope(
            subject: 'Your '.$kind.' request was '.$state.' — GTP Real Estate',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-update',
            with: ['order' => $this->order],
        );
    }
}
