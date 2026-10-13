<?php

namespace App\Notifications;

use App\Models\PropertyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClientReportSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PropertyReport $report
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
        $reporter = $this->report->user?->name ?: ($this->report->contact_email ?: 'Client');
        $subject = $this->report->subject ?: ($this->report->property?->title ?: 'Direct Platform Report');

        return [
            'title' => '🚨 Client Report #'.$this->report->report_number,
            'message' => "Client {$reporter} sent a report [{$this->report->reason_label}] regarding \"{$subject}\".",
            'type' => 'client_report',
            'report_id' => $this->report->id,
            'report_number' => $this->report->report_number,
            'reason' => $this->report->reason,
            'status' => $this->report->status,
            'url' => route('dashboard', ['section' => 'reports']).'#reports',
        ];
    }
}
