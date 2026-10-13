<?php

namespace App\Notifications;

use App\Models\RentSchedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RentDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RentSchedule $schedule,
        public bool $isOverdue = false,
        public float $lateFeeAmount = 0.0
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
        $dueDateFormatted = $this->schedule->due_date ? Carbon::parse($this->schedule->due_date)->format('M d, Y') : 'N/A';

        $title = $this->isOverdue
            ? '⚠️ Overdue Rent Notice / የኪራይ ክፍያ ማስታወቂያ'
            : '🔔 Upcoming Rent Due / የኪራይ ክፍያ ቀነ-ገደብ';

        $message = $this->isOverdue
            ? "Rent installment for {$this->schedule->period_label} is overdue. Amount: ".number_format($this->schedule->amount_due, 2).' ETB.'.($this->lateFeeAmount > 0 ? ' (includes '.number_format($this->lateFeeAmount, 2).' ETB late fee surcharge)' : '')
            : "Rent installment for {$this->schedule->period_label} is due on {$dueDateFormatted} (".number_format($this->schedule->amount_due, 2).' ETB).';

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'rent_schedule',
            'schedule_id' => $this->schedule->id,
            'lease_id' => $this->schedule->lease_id,
            'amount' => (float) $this->schedule->amount_due,
            'period_label' => $this->schedule->period_label,
            'due_date' => $this->schedule->due_date?->format('Y-m-d'),
            'is_overdue' => $this->isOverdue,
            'late_fee_amount' => $this->lateFeeAmount,
            'url' => route('client.portal').'#leases',
        ];
    }
}
