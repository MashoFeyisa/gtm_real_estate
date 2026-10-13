<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProfessionalServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasProfessionalPerm = $user?->hasAnyPermission([
            'services.jobs_receive',
            'services.jobs_accept',
            'services.profile_manage',
            'properties.view',
        ]);

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['professional', 'service_provider'], true) && ! $user?->isAdmin() && ! $hasProfessionalPerm)) {
            abort(403, 'Permission denied: Professional services desk access required.');
        }

        $requests = ProfessionalServiceRequest::with(['user', 'property'])
            ->where(function ($q) use ($user) {
                $q->where('professional_user_id', $user->id);
                if ($user->isAdmin() || $user->role === 'service_provider') {
                    $q->orWhereNull('professional_user_id');
                }
            })
            ->latest()
            ->get();

        $appointments = $requests->filter(fn ($r) => ! empty($r->appointment_date));
        $reports = $requests->filter(fn ($r) => ! empty($r->report_file_path) || $r->status === 'completed');
        $payments = $requests->filter(fn ($r) => $r->fee > 0);
        $reviews = $requests->filter(fn ($r) => $r->client_rating !== null);

        $totalEarnings = (float) $requests->where('payment_status', 'paid')->sum('fee');

        return view('dashboards.professional', compact(
            'requests',
            'appointments',
            'reports',
            'payments',
            'reviews',
            'totalEarnings'
        ));
    }

    public function updateStatus(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,in_progress,completed,declined'],
        ]);

        $serviceRequest->update([
            'status' => $validated['status'],
            'professional_user_id' => $user->id,
        ]);

        AuditLog::log(
            action: 'professional.service_request_status_updated',
            entityType: 'ProfessionalServiceRequest',
            entityId: $serviceRequest->id,
            newValue: ['status' => $validated['status']],
            user: $user,
            request: $request
        );

        return back()->with('success', "Service request updated to '{$validated['status']}'.");
    }

    public function uploadReport(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'report_title' => ['required', 'string', 'max:255'],
            'findings' => ['required', 'string'],
            'report_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,docx', 'max:10240'],
        ]);

        $filePath = null;
        if ($request->hasFile('report_file')) {
            $filePath = $request->file('report_file')->store('professional-reports', 'public');
        }

        $serviceRequest->update([
            'report_title' => $validated['report_title'],
            'findings' => $validated['findings'],
            'report_file_path' => $filePath ?? $serviceRequest->report_file_path,
            'status' => 'completed',
        ]);

        AuditLog::log(
            action: 'professional.report_submitted',
            entityType: 'ProfessionalServiceRequest',
            entityId: $serviceRequest->id,
            newValue: [
                'report_title' => $validated['report_title'],
                'status' => 'completed',
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', 'Professional report uploaded and request marked completed.');
    }

    public function updateRequestStatus(Request $request, ProfessionalServiceRequest $requestItem): RedirectResponse
    {
        return $this->updateStatus($request, $requestItem);
    }
}
