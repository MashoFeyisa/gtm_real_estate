<?php

namespace App\Notifications;

use App\Models\PropertyViewing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ViewingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PropertyViewing $viewing,
        public string $status = 'confirmed'
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
        $propertyTitle = $this->viewing->property?->title ?? 'Property';
        $viewingDateFormatted = $this->viewing->viewing_date ? Carbon::parse($this->viewing->viewing_date)->format('M d, Y') : 'N/A';

        $title = match ($this->status) {
            'confirmed' => '📅 Viewing Appointment Confirmed! / የጉብኝት ቀጠሮ ጸድቋል',
            'cancelled' => 'Viewing Appointment Cancelled',
            'rescheduled' => 'Viewing Appointment Rescheduled',
            'completed' => 'Viewing Completed',
            default => 'New Viewing Requested',
        };

        $message = match ($this->status) {
            'requested' => "{$this->viewing->buyer_name} requested a ".str_replace('_', ' ', $this->viewing->viewing_type)." viewing for '{$propertyTitle}' on {$viewingDateFormatted} at {$this->viewing->formatted_time_slot}.",
            'confirmed' => "The viewing for {$this->viewing->buyer_name} at '{$propertyTitle}' is confirmed for {$viewingDateFormatted} at {$this->viewing->formatted_time_slot}.",
            'cancelled' => "Viewing appointment {$this->viewing->viewing_reference} for {$this->viewing->buyer_name} at '{$propertyTitle}' has been cancelled.",
            default => "Viewing appointment {$this->viewing->viewing_reference} status updated to {$this->status}.",
        };

        $url = route('client.portal').'#viewings';
        if ($notifiable instanceof User && $notifiable->isAdmin()) {
            $url = route('dashboard').'#viewings';
        } elseif ($notifiable instanceof User && $notifiable->agentProfile) {
            $url = route('agent.portal').'#viewings';
        }

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'property_viewing',
            'viewing_id' => $this->viewing->id,
            'viewing_reference' => $this->viewing->viewing_reference,
            'property_id' => $this->viewing->property_id,
            'status' => $this->status,
            'url' => $url,
        ];
    }
}
