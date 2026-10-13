<?php

namespace App\Notifications;

use App\Models\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommissionGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Commission $commission,
        public string $event // generated, settled, verified
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amountFormatted = 'ETB '.number_format((float) $this->commission->commission_amount, 2);
        $agentName = $this->commission->agent?->name ?: 'Agent';
        $propTitle = $this->commission->property?->title ?: 'Property Deal';

        $title = match ($this->event) {
            'settled' => "💰 Commission Settled: {$amountFormatted}",
            'verified' => "🏦 Commission Verified by Finance Desk: {$amountFormatted}",
            default => "📈 New Commission Generated: {$amountFormatted}",
        };

        $url = $notifiable->isFinanceOfficer() || $notifiable->isAdmin()
            ? route('finance.index')
            : route('agent.portal').'#commissions';

        return [
            'title' => $title,
            'message' => "Commission for {$agentName} on \"{$propTitle}\" ({$amountFormatted}) is {$this->commission->status}.",
            'type' => 'commission',
            'commission_id' => $this->commission->id,
            'amount' => $this->commission->commission_amount,
            'status' => $this->commission->status,
            'url' => $url,
        ];
    }
}
