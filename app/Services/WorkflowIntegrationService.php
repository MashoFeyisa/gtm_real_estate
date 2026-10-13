<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\FrontDeskShift;
use App\Models\GuestServiceRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\PropertyReport;
use App\Models\PropertyViewing;
use App\Models\User;

class WorkflowIntegrationService
{
    /**
     * Get real-time AI actionable pending tasks for a given platform actor.
     */
    public function getActorActionableTasks(User $user): array
    {
        $role = $user->role;

        return match (true) {
            $user->isAdmin() => $this->getAdminTasks($user),
            $user->isCampaignOfficer() => $this->getCampaignOfficerTasks($user),
            $user->isFinanceOfficer() => $this->getFinanceOfficerTasks($user),
            $user->isReceptionist() => $this->getReceptionistTasks($user),
            (bool) ($user->agentProfile || $role === 'agent') => $this->getAgentTasks($user),
            default => $this->getClientTasks($user),
        };
    }

    protected function getAdminTasks(User $user): array
    {
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingReports = PropertyReport::where('status', 'pending')->count();
        $pendingCampaigns = Campaign::where('status', 'under_review')->count();
        $pendingCommissions = Commission::where('status', 'pending')->count();
        $unverifiedProperties = Property::where('verification_status', 'pending')->count();

        $tasks = [];
        if ($pendingReports > 0) {
            $tasks[] = [
                'type' => 'reports',
                'priority' => 'critical',
                'badge' => '🚨 Trust & Safety',
                'title' => "{$pendingReports} client incident/fraud reports need investigation",
                'url' => route('dashboard', ['section' => 'reports']).'#reports',
            ];
        }
        if ($pendingPayments > 0) {
            $tasks[] = [
                'type' => 'payments',
                'priority' => 'high',
                'badge' => '💳 Financial Audit',
                'title' => "{$pendingPayments} bank deposit slip payments pending verification",
                'url' => route('finance.index'),
            ];
        }
        if ($pendingCampaigns > 0) {
            $tasks[] = [
                'type' => 'campaigns',
                'priority' => 'medium',
                'badge' => '📣 Marketing Review',
                'title' => "{$pendingCampaigns} promotional campaigns awaiting review",
                'url' => route('dashboard'),
            ];
        }
        if ($pendingCommissions > 0) {
            $tasks[] = [
                'type' => 'commissions',
                'priority' => 'medium',
                'badge' => '💰 Agent Settlements',
                'title' => "{$pendingCommissions} agent commissions awaiting settlement",
                'url' => route('finance.index'),
            ];
        }
        if ($unverifiedProperties > 0) {
            $tasks[] = [
                'type' => 'properties',
                'priority' => 'low',
                'badge' => '🏡 Verification',
                'title' => "{$unverifiedProperties} property listings pending legal document audit",
                'url' => route('dashboard', ['section' => 'properties']).'#properties',
            ];
        }

        return $tasks;
    }

    protected function getAgentTasks(User $user): array
    {
        $agent = $user->agentProfile;
        if (! $agent) {
            return [];
        }

        $pendingOffers = PropertyOffer::where('agent_id', $agent->id)->whereIn('status', ['submitted', 'countered'])->count();
        $requestedViewings = PropertyViewing::where('agent_id', $agent->id)->where('status', 'requested')->count();
        $pendingOrders = Order::where('agent_id', $agent->id)->where('status', 'pending')->count();
        $revisionCampaigns = Campaign::where('agent_id', $agent->id)->where('status', 'rejected')->count();

        $tasks = [];
        if ($pendingOffers > 0) {
            $tasks[] = [
                'type' => 'offers',
                'priority' => 'high',
                'badge' => '🤝 Deal Negotiation',
                'title' => "{$pendingOffers} purchase/rental offers waiting for your response or negotiation",
                'url' => route('agent.portal').'#offers',
            ];
        }
        if ($requestedViewings > 0) {
            $tasks[] = [
                'type' => 'viewings',
                'priority' => 'medium',
                'badge' => '📅 Viewing Schedules',
                'title' => "{$requestedViewings} property viewing appointments requested by clients",
                'url' => route('agent.portal').'#viewings',
            ];
        }
        if ($pendingOrders > 0) {
            $tasks[] = [
                'type' => 'orders',
                'priority' => 'medium',
                'badge' => '📝 Direct Orders',
                'title' => "{$pendingOrders} client direct inquiries / callback requests",
                'url' => route('agent.portal').'#orders',
            ];
        }
        if ($revisionCampaigns > 0) {
            $tasks[] = [
                'type' => 'campaigns',
                'priority' => 'medium',
                'badge' => '📣 Campaign Revision',
                'title' => "{$revisionCampaigns} campaigns require revisions as noted by Campaign Officer",
                'url' => route('agent.portal').'#campaigns',
            ];
        }

        return $tasks;
    }

    protected function getFinanceOfficerTasks(User $user): array
    {
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingCommissions = Commission::where('status', 'pending')->count();
        $unsettledShifts = FrontDeskShift::where('status', 'submitted')->count();

        $tasks = [];
        if ($pendingPayments > 0) {
            $tasks[] = [
                'type' => 'payments',
                'priority' => 'critical',
                'badge' => '💳 Bank Slip Audit',
                'title' => "{$pendingPayments} unverified client deposit slips need bank reconciliation",
                'url' => route('finance.index'),
            ];
        }
        if ($pendingCommissions > 0) {
            $tasks[] = [
                'type' => 'commissions',
                'priority' => 'high',
                'badge' => '💰 Commission Payouts',
                'title' => "{$pendingCommissions} agent commissions ready for tax deduction & voucher settlement",
                'url' => route('finance.index'),
            ];
        }
        if ($unsettledShifts > 0) {
            $tasks[] = [
                'type' => 'shifts',
                'priority' => 'medium',
                'badge' => '🛎️ Front Desk Cash Shifts',
                'title' => "{$unsettledShifts} front desk receptionist shift cash reconciliations awaiting audit",
                'url' => route('finance.index'),
            ];
        }

        return $tasks;
    }

    protected function getCampaignOfficerTasks(User $user): array
    {
        return [];
    }

    protected function getReceptionistTasks(User $user): array
    {
        $today = now()->toDateString();
        $expectedArrivals = Booking::whereDate('check_in_date', '<=', $today)->whereNull('checked_in_at')->whereIn('status', ['confirmed', 'paid'])->count();
        $expectedDepartures = Booking::whereDate('check_out_date', '<=', $today)->whereNotNull('checked_in_at')->whereNull('checked_out_at')->count();
        $openServiceReqs = GuestServiceRequest::where('status', 'pending')->count();

        $tasks = [];
        if ($expectedArrivals > 0) {
            $tasks[] = [
                'type' => 'arrivals',
                'priority' => 'high',
                'badge' => '🔑 Guest Check-Ins',
                'title' => "{$expectedArrivals} guests arriving today waiting for room allocation & key issue",
                'url' => route('receptionist.dashboard'),
            ];
        }
        if ($expectedDepartures > 0) {
            $tasks[] = [
                'type' => 'departures',
                'priority' => 'high',
                'badge' => '🚪 Guest Check-Outs',
                'title' => "{$expectedDepartures} guests departing today waiting for room clearance & folio close",
                'url' => route('receptionist.dashboard'),
            ];
        }
        if ($openServiceReqs > 0) {
            $tasks[] = [
                'type' => 'services',
                'priority' => 'medium',
                'badge' => '🧹 Service Requests',
                'title' => "{$openServiceReqs} guest room service / housekeeping requests pending",
                'url' => route('receptionist.dashboard'),
            ];
        }

        return $tasks;
    }

    protected function getClientTasks(User $user): array
    {
        $counteredOffers = PropertyOffer::where('user_id', $user->id)->where('status', 'countered')->count();
        $unpaidBookings = Booking::where('user_id', $user->id)->where('status', 'pending')->where('payment_status', 'unpaid')->count();

        $tasks = [];
        if ($counteredOffers > 0) {
            $tasks[] = [
                'type' => 'counter_offers',
                'priority' => 'high',
                'badge' => '🤝 Counteroffer Received',
                'title' => "{$counteredOffers} offers have received an agent counteroffer waiting for your acceptance",
                'url' => route('client.portal').'#offers',
            ];
        }
        if ($unpaidBookings > 0) {
            $tasks[] = [
                'type' => 'stay_payment',
                'priority' => 'medium',
                'badge' => '💳 Pending Payment',
                'title' => "{$unpaidBookings} stay bookings require deposit slip upload or Telebirr payment",
                'url' => route('client.portal').'#stays',
            ];
        }

        return $tasks;
    }
}
