<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\User;
use App\Notifications\RentDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRentSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rents:process-schedules {--notify : Send database notifications to tenants/landlords}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and process rent schedules, escalate overdue installments past grace period, apply late fees, and dispatch reminders (SRS §87, §88)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Ethiopian rent schedule audit and escalation check...');

        $activeLeases = Lease::with(['schedules', 'property', 'tenant', 'landlord', 'agent.user'])
            ->where('status', 'active')
            ->get();

        $processedCount = 0;
        $overdueCount = 0;
        $remindersCount = 0;
        $today = Carbon::now()->startOfDay();

        foreach ($activeLeases as $lease) {
            $gracePeriod = (int) ($lease->grace_period_days ?? 5);
            $lateFeePct = (float) ($lease->late_fee_percentage ?? 0);
            $tenantUser = $lease->tenant ?? User::where('email', $lease->tenant_email)->first();

            foreach ($lease->schedules as $schedule) {
                if ($schedule->status === 'paid') {
                    continue;
                }

                $dueDate = Carbon::parse($schedule->due_date)->startOfDay();
                $graceDeadline = $dueDate->copy()->addDays($gracePeriod);

                // Case 1: Overdue Escalation (past due date + grace period)
                if ($today->gt($graceDeadline)) {
                    $lateFeeAmount = 0.0;

                    if ($schedule->status !== 'overdue') {
                        // Apply late fee if percentage configured and not already applied
                        if ($lateFeePct > 0 && ! str_contains($schedule->notes ?? '', 'Late fee applied')) {
                            $lateFeeAmount = round(((float) $schedule->amount_due) * ($lateFeePct / 100), 2);
                            $schedule->amount_due = (float) $schedule->amount_due + $lateFeeAmount;
                            $lateNote = "Late fee applied: +{$lateFeeAmount} ETB ({$lateFeePct}% after {$gracePeriod}-day grace period)";
                            $schedule->notes = $schedule->notes ? "{$schedule->notes}; {$lateNote}" : $lateNote;
                        }

                        $schedule->status = 'overdue';
                        $schedule->save();
                        $overdueCount++;

                        $this->line("Installment {$schedule->installment_number} ({$schedule->period_label}) for Lease {$lease->lease_code} marked OVERDUE.");

                        if ($tenantUser) {
                            $tenantUser->notify(new RentDueReminderNotification($schedule, isOverdue: true, lateFeeAmount: $lateFeeAmount));
                        }
                    }
                }
                // Case 2: Upcoming Due Reminder (due within next 3 days and currently pending)
                elseif ($schedule->status === 'pending' && $dueDate->gte($today) && $dueDate->diffInDays($today) <= 3) {
                    $remindersCount++;
                    if ($tenantUser) {
                        $tenantUser->notify(new RentDueReminderNotification($schedule, isOverdue: false));
                    }
                }

                $processedCount++;
            }
        }

        $this->info("Completed. Checked {$processedCount} installment(s). Escalated {$overdueCount} overdue installment(s). Dispatched {$remindersCount} upcoming reminder(s).");

        return self::SUCCESS;
    }
}
