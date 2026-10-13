<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GuestBookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $event // created, confirmed, checked_in, checked_out
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $guestName = $this->booking->guest_name ?: ($this->booking->user?->name ?: 'Guest');
        $propTitle = $this->booking->property?->title ?: 'Guest House';

        $title = match ($this->event) {
            'created' => "🛎️ New Stay Reservation: {$this->booking->booking_reference}",
            'confirmed' => "✅ Stay Reservation Confirmed: {$this->booking->booking_reference}",
            'arrival_instructions' => "📍 Welcome! Check-in Instructions: {$propTitle}",
            'checked_in' => "🔑 Guest Checked In: Room {$this->booking->room_number}",
            'checked_out' => "🚪 Guest Checked Out: Room {$this->booking->room_number}",
            default => "Stay Update: {$this->booking->booking_reference}",
        };

        $message = $this->event === 'arrival_instructions'
            ? "Welcome! Your check-in is scheduled today at {$propTitle}. Standard check-in time: ".($this->booking->property?->check_in_time ?? '14:00').'. House rules: '.($this->booking->property?->house_rules ?? 'Enjoy your stay and observe quiet hours after 22:00.')
            : "Guest {$guestName} at {$propTitle} ({$this->booking->check_in_date?->format('M d')} - {$this->booking->check_out_date?->format('M d')}).";

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'guest_booking',
            'booking_id' => $this->booking->id,
            'booking_reference' => $this->booking->booking_reference,
            'status' => $this->booking->status,
            'url' => route('receptionist.dashboard'),
        ];
    }
}
