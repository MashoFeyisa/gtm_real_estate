<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\CampaignLead;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Notifications\CampaignStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    /**
     * Display the Campaign Officer & Marketing Review Desk.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->canManageCampaigns()) {
            abort(403, 'Permission denied: Campaign Officer desk access required.');
        }

        $campaigns = Campaign::with(['agent.user', 'property.images', 'leads'])->latest()->get();
        $leads = CampaignLead::with('campaign')->latest()->take(25)->get();

        $stats = [
            'total_campaigns' => $campaigns->count(),
            'under_review' => $campaigns->where('status', 'under_review')->count(),
            'published' => $campaigns->where('status', 'published')->count(),
            'approved' => $campaigns->where('status', 'approved')->count(),
            'rejected' => $campaigns->where('status', 'rejected')->count(),
            'total_budget' => (float) $campaigns->sum('budget'),
            'total_spent' => (float) $campaigns->sum('spent'),
            'total_views' => (int) $campaigns->sum('views_count'),
            'total_clicks' => (int) $campaigns->sum('clicks_count'),
            'total_leads' => $leads->count(),
        ];

        return view('dashboards.campaign_officer', [
            'user' => $user,
            'campaigns' => $campaigns,
            'leads' => $leads,
            'stats' => $stats,
        ]);
    }

    /**
     * Agent submits a new marketing campaign for their property or project.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $agent = $user?->agentProfile;

        if (! $user || (! $user->isAdmin() && ! $agent)) {
            abort(403, 'Only licensed agents or administrators can create campaigns.');
        }

        $validated = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'campaign_type' => ['required', 'string', 'in:featured_listing,diaspora_promo,seasonal_discount,open_house,project_launch'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'marketing_copy' => ['nullable', 'string', 'max:5000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Security boundary: if an agent, ensure property belongs to them
        if ($agent && ! empty($validated['property_id'])) {
            $prop = Property::where('id', $validated['property_id'])->where('agent_id', $agent->id)->first();
            if (! $prop && ! $user->isAdmin()) {
                abort(403, 'You can only promote properties in your own portfolio.');
            }
        }

        $agentId = $agent ? $agent->id : ($validated['property_id'] ? Property::find($validated['property_id'])->agent_id : null);
        if (! $agentId && $user->isAdmin()) {
            $agentId = Agent::first()?->id ?? 1;
        }

        $campaign = Campaign::create([
            'agent_id' => $agentId,
            'user_id' => $user->id,
            'property_id' => $validated['property_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'campaign_type' => $validated['campaign_type'],
            'target_audience' => $validated['target_audience'] ?? 'all',
            'headline' => $validated['headline'],
            'description' => $validated['description'],
            'marketing_copy' => $validated['marketing_copy'] ?? null,
            'budget' => (float) ($validated['budget'] ?? 0),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'under_review',
        ]);

        ActivityLog::record(
            'campaign_submitted',
            "Agent {$user->name} submitted promotional campaign \"{$campaign->title}\" for editorial review.",
            $campaign
        );

        // Notify Campaign Officers & Admins
        $campaignOfficers = User::whereIn('role', ['campaign_officer', 'admin'])->get();
        foreach ($campaignOfficers as $officer) {
            $officer->notify(new CampaignStatusNotification($campaign, 'submitted'));
        }

        return back()->with('success', "Campaign \"{$campaign->title}\" submitted! It is now under review by the Campaign Desk.");
    }

    /**
     * Campaign Officer / Admin reviews and approves/rejects campaign.
     */
    public function updateStatus(Request $request, Campaign $campaign): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->canManageCampaigns()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,published,paused,completed'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'target_audience' => ['nullable', 'string', 'max:255'],
        ]);

        $newStatus = $validated['status'];
        $updateData = [
            'status' => $newStatus,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? $campaign->review_notes,
        ];

        if (isset($validated['budget'])) {
            $updateData['budget'] = (float) $validated['budget'];
        }
        if (! empty($validated['target_audience'])) {
            $updateData['target_audience'] = $validated['target_audience'];
        }

        $campaign->update($updateData);

        // When a campaign is approved or published, ensure its linked property is active and available
        if (in_array($newStatus, ['approved', 'published'], true) && $campaign->property) {
            $prop = $campaign->property;
            $prop->update([
                'is_active' => true,
                'status' => in_array($prop->status, ['draft', 'under_review', null], true) ? 'available' : $prop->status,
            ]);
        }

        ActivityLog::record(
            'campaign_'.$newStatus,
            "Campaign Officer {$user->name} marked campaign \"{$campaign->title}\" as {$newStatus}.",
            $campaign,
            ['notes' => $validated['review_notes'] ?? null]
        );

        // Notify Campaign Owner Agent
        $agentUser = $campaign->agent?->user;
        if ($agentUser) {
            $agentUser->notify(new CampaignStatusNotification($campaign, $newStatus));
        }

        return back()->with('success', "Campaign status updated to: {$newStatus}.");
    }

    /**
     * User/Client interacts with a campaign by sending a lead/inquiry.
     */
    public function recordLead(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead = $campaign->leads()->create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'new',
        ]);

        $campaign->increment('inquiries_count');

        // Also connect to Agent Inquiry so it appears in agent's inbox
        if ($campaign->agent_id) {
            Inquiry::create([
                'agent_id' => $campaign->agent_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? '+251900000000',
                'subject' => "Lead from Campaign: {$campaign->title}",
                'message' => $validated['message'] ?? "Inquired via marketing campaign [{$campaign->title}].",
            ]);
        }

        ActivityLog::record(
            'campaign_lead_received',
            "New prospective client lead received for campaign \"{$campaign->title}\" by {$validated['name']}.",
            $campaign
        );

        return back()->with('success', 'Your inquiry has been connected with the listing agent! They will contact you shortly.');
    }
}
