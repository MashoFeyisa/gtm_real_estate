<?php

namespace App\Notifications;

use App\Models\PropertyOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PropertyOffer $offer,
        public string $action = 'submitted',
        public ?string $customMessage = null
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
        $propertyTitle = $this->offer->property?->title ?? 'Property';

        $title = match ($this->action) {
            'countered' => '💬 Counteroffer Received',
            'accepted' => '🎉 Offer Accepted! / የዋጋ ጥያቄዎ ተቀባይነት አግኝቷል',
            'converted' => '📄 Contract Generated / ውል ተዘጋጅቷል',
            'rejected' => 'Offer Declined',
            default => 'New Offer Submitted',
        };

        $message = match ($this->action) {
            'countered' => 'A counteroffer of '.number_format($this->offer->current_counter_amount, 2)." ETB was proposed for '{$propertyTitle}'.",
            'accepted' => "Your offer for '{$propertyTitle}' was accepted! Official agreement is ready for download.",
            'converted' => "Your offer for '{$propertyTitle}' has been converted to an official deal.",
            'rejected' => "Your offer for '{$propertyTitle}' was declined.",
            default => $this->customMessage ?: 'A new offer of '.number_format($this->offer->offered_price, 2)." ETB was received for '{$propertyTitle}'.",
        };

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'property_offer',
            'offer_id' => $this->offer->id,
            'offer_reference' => $this->offer->offer_reference,
            'property_id' => $this->offer->property_id,
            'action' => $this->action,
            'status' => $this->offer->status,
            'url' => route('client.portal').'#offers',
        ];
    }
}
