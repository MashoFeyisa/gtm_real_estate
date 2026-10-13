<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CampaignStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public string $event // submitted, approved, rejected, published
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'submitted' => 'Awaiting Campaign Officer Review',
            'approved' => 'Approved by Campaign Desk',
            'rejected' => 'Revision Requested',
            'published' => 'Published & Live on Marketplace',
        ];

        $title = match ($this->event) {
            'submitted' => '📣 New Campaign Submitted: '.$this->campaign->title,
            'approved' => '✅ Campaign Approved: '.$this->campaign->title,
            'rejected' => '⚠️ Campaign Revision Needed: '.$this->campaign->title,
            'published' => '🚀 Campaign Published: '.$this->campaign->title,
            default => 'Campaign Update: '.$this->campaign->title,
        };

        $url = match (true) {
            $notifiable->isAdmin() => route('dashboard'),
            default => route('agent.portal').'#campaigns',
        };

        return [
            'title' => $title,
            'message' => "Campaign \"{$this->campaign->title}\" status is now: ".($statusLabels[$this->event] ?? $this->campaign->status),
            'type' => 'campaign_status',
            'campaign_id' => $this->campaign->id,
            'campaign_title' => $this->campaign->title,
            'status' => $this->campaign->status,
            'url' => $url,
        ];
    }
}
