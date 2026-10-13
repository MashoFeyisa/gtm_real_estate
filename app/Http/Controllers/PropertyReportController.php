<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\PropertyReport;
use App\Models\User;
use App\Notifications\ClientReportSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyReportController extends Controller
{
    /**
     * Submit a trust & safety scam/misleading listing report from a property page.
     */
    public function store(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'in:scam,fake_property,duplicate,wrong_price,unavailable,misleading,suspicious_documents,inappropriate_content,service_complaint,agent_misconduct,other'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $report = $property->reports()->create([
            'user_id' => auth()->id(),
            'subject' => 'Property Listing Report: '.$property->title,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'contact_email' => $validated['contact_email'] ?? auth()->user()?->email,
            'status' => 'pending',
        ]);

        // Send direct notification to all administrator and company executive users
        $recipients = User::whereIn('role', ['admin', 'company'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new ClientReportSubmittedNotification($report));
        }

        // Also notify responsible actor based on issue categorization:
        // agent_misconduct, wrong_price, duplicate -> notify property agent
        if ($property->agent?->user) {
            $property->agent->user->notify(new ClientReportSubmittedNotification($report));
        }

        ActivityLog::record(
            'issue_reported',
            "Client reported issue [{$report->reason_label}] on \"{$property->title}\".",
            $report
        );

        return back()->with('success', 'Report #'.$report->report_number.' submitted to the GTM Admin HQ & Company Trust team. Thank you for protecting the marketplace.');
    }

    /**
     * Submit a direct incident/complaint/report to Admin from client portal or global form.
     */
    public function storeDirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'reason' => ['required', 'string', 'in:scam,fake_property,duplicate,wrong_price,unavailable,misleading,suspicious_documents,inappropriate_content,service_complaint,agent_misconduct,other'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $report = PropertyReport::create([
            'property_id' => $validated['property_id'] ?? null,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'] ?? 'Client Direct Incident & Issue Report',
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'contact_email' => $validated['contact_email'] ?? auth()->user()?->email,
            'status' => 'pending',
        ]);

        // Send direct notification to all administrator and company executive users
        $recipients = User::whereIn('role', ['admin', 'company'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new ClientReportSubmittedNotification($report));
        }

        // Responsible actor assignment by issue category:
        // payment issues -> Finance Officer
        if (str_contains(strtolower($report->reason), 'payment') || str_contains(strtolower($report->description), 'payment')) {
            $financeStaff = User::where('role', 'finance_officer')->get();
            foreach ($financeStaff as $staff) {
                $staff->notify(new ClientReportSubmittedNotification($report));
            }
        }
        // guest house / operational issues -> Receptionist
        if ($report->property && in_array($report->property->property_category, ['guest_house', 'hotel', 'guesthouse'])) {
            $receptionStaff = User::where('role', 'receptionist')->get();
            foreach ($receptionStaff as $staff) {
                $staff->notify(new ClientReportSubmittedNotification($report));
            }
        }

        ActivityLog::record(
            'direct_report_submitted',
            "Client submitted incident report #{$report->report_number} [{$report->reason_label}].",
            $report
        );

        return back()->with('success', 'Report #'.$report->report_number.' dispatched directly to the GTM Admin & Company Executive team.');
    }

    /**
     * Admin resolve, investigate, or dismiss report.
     */
    public function update(Request $request, PropertyReport $report): RedirectResponse
    {
        $reportUser = auth()->user();

        $canResolveReport = $reportUser && (
            $reportUser->isAdmin()
            || $reportUser->isCompany()
            || ($reportUser->role === 'moderator' && $reportUser->hasPermission('reports.handle'))
            || ($reportUser->role === 'customer_support' && $reportUser->hasPermission('disputes.handle'))
        );

        abort_unless($canResolveReport, 403, 'Unauthorized.');

        $validated = $request->validate([
            'status' => ['required', 'in:pending,under_investigation,resolved,dismissed'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'suspend_property' => ['nullable', 'boolean'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? $report->resolution_notes,
            'resolved_by' => auth()->id(),
        ]);

        if (! empty($validated['suspend_property']) && $report->property) {
            $report->property->update([
                'status' => 'archived',
                'is_active' => false,
            ]);
        }

        return back()->with('success', 'Report #'.$report->report_number.' marked as '.$validated['status'].'.');
    }

    /**
     * Admin delete report.
     */
    public function destroy(PropertyReport $report): RedirectResponse
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isCompany()) {
            abort(403, 'Unauthorized.');
        }

        $report->delete();

        return back()->with('success', 'Report #'.$report->report_number.' removed successfully.');
    }
}
